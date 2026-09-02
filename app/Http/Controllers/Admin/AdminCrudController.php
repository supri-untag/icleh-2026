<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DateMilestoneStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\ConferenceDate;
use App\Models\ConferenceTopic;
use App\Models\Faq;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\Partner;
use App\Models\RegistrationFee;
use App\Models\Role;
use App\Models\Speaker;
use App\Models\User;
use App\Services\ConferenceContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rules\Unique;
use Illuminate\View\View;

class AdminCrudController extends Controller
{
    public function __construct(private ConferenceContext $conferenceContext) {}

    public function create(string $resource): View
    {
        $config = $this->resourceConfig($resource);

        return $this->formView($resource, $config);
    }

    public function store(Request $request, string $resource): RedirectResponse
    {
        $config = $this->resourceConfig($resource);
        $data = $this->validatedData($request, $resource);
        $this->handleUploads($request, $resource, null, $data);
        $relationData = $this->extractRelationData($request, $resource, $data);
        $this->applyConferenceScope($config, $data);

        /** @var class-string<Model> $modelClass */
        $modelClass = $config['model'];
        $record = $modelClass::query()->create($data);

        $this->syncRelations($resource, $record, $relationData);

        return redirect()
            ->route($config['indexRoute'])
            ->with('status', $config['singular'].' created successfully.');
    }

    public function edit(string $resource, string $record): View
    {
        $config = $this->resourceConfig($resource);
        $model = $this->findRecord($resource, $record, $config);

        return $this->formView($resource, $config, $model);
    }

    public function update(Request $request, string $resource, string $record): RedirectResponse
    {
        $config = $this->resourceConfig($resource);
        $model = $this->findRecord($resource, $record, $config);
        $data = $this->validatedData($request, $resource, $model);
        $this->handleUploads($request, $resource, $model, $data);
        $relationData = $this->extractRelationData($request, $resource, $data);
        $this->applyConferenceScope($config, $data);

        $model->update($data);
        $this->syncRelations($resource, $model, $relationData);

        return redirect()
            ->route($config['indexRoute'])
            ->with('status', $config['singular'].' updated successfully.');
    }

    public function destroy(Request $request, string $resource, string $record): RedirectResponse|JsonResponse
    {
        $config = $this->resourceConfig($resource);
        $model = $this->findRecord($resource, $record, $config);

        if ($model instanceof User && $request->user() instanceof User && $model->is($request->user())) {
            $message = 'You cannot delete your own account.';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => $message], 422);
            }

            return back()->withErrors(['record' => $message]);
        }

        $this->deleteRecordFiles($model);
        $model->delete();
        $message = $config['singular'].' deleted successfully.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['message' => $message]);
        }

        return redirect()
            ->route($config['indexRoute'])
            ->with('status', $message);
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function formView(string $resource, array $config, ?Model $record = null): View
    {
        return view('admin.crud-form', [
            'title' => ($record ? 'Edit ' : 'Create ').$config['singular'],
            'description' => $config['description'],
            'resource' => $resource,
            'record' => $record,
            'fields' => $config['fields'],
            'values' => $this->valuesFor($resource, $record),
            'action' => $record
                ? route('admin.crud.update', [$resource, $record->getRouteKey()])
                : route('admin.crud.store', $resource),
            'method' => $record ? 'PUT' : 'POST',
            'indexUrl' => route($config['indexRoute']),
            'submitLabel' => $record ? 'Save Changes' : 'Create '.$config['singular'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function findRecord(string $resource, string $record, array $config): Model
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = $config['model'];
        $model = new $modelClass;
        $query = $modelClass::query();

        if ($resource === 'sections') {
            $conference = $this->conferenceContext->current();
            $query->whereHas('page', fn ($builder) => $builder->whereBelongsTo($conference));
        } elseif ($config['conferenceScoped'] ?? false) {
            $query->whereBelongsTo($this->conferenceContext->current());
        }

        return $query->where($model->getRouteKeyName(), $record)->firstOrFail();
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, string $resource, ?Model $record = null): array
    {
        $this->prepareSlug($request, $resource);

        $data = $request->validate($this->rules($request, $resource, $record));

        foreach ($data as $key => $value) {
            if ($value === '') {
                $data[$key] = null;
            }
        }

        foreach ($this->resourceConfig($resource)['fields'] as $field) {
            if (($field['type'] ?? null) === 'checkbox') {
                $data[$field['name']] = $request->boolean($field['name']);
            }
        }

        if ($resource === 'topics') {
            $data['keywords'] = collect(preg_split('/[\r\n,]+/', (string) ($data['keywords'] ?? '')) ?: [])
                ->map(fn (string $keyword): string => trim($keyword))
                ->filter()
                ->values()
                ->all();
        }

        if ($resource === 'fees' && isset($data['currency'])) {
            $data['currency'] = Str::upper((string) $data['currency']);
        }

        if ($resource === 'users' && blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function handleUploads(Request $request, string $resource, ?Model $record, array &$data): void
    {
        if ($resource !== 'speakers') {
            return;
        }

        $removePhoto = (bool) ($data['remove_photo'] ?? false);
        unset($data['photo_file'], $data['remove_photo']);

        if ($request->hasFile('photo_file')) {
            $path = $request->file('photo_file')->store('speakers', 'public');
            abort_if($path === false, 500, 'Photo could not be uploaded.');

            if ($record instanceof Speaker) {
                $this->deleteStoredPublicFile($record->photo);
            }

            $data['photo'] = $path;

            return;
        }

        if ($removePhoto && $record instanceof Speaker) {
            $this->deleteStoredPublicFile($record->photo);
            $data['photo'] = null;
        }
    }

    /**
     * @return array<string, array<int, mixed>|bool>
     */
    private function extractRelationData(Request $request, string $resource, array &$data): array
    {
        if ($resource !== 'users') {
            return [];
        }

        $relationData = [
            'role_ids' => $data['role_ids'] ?? [],
            'email_verified' => $request->boolean('email_verified'),
        ];

        unset($data['role_ids'], $data['email_verified']);

        return $relationData;
    }

    /**
     * @param  array<string, mixed>  $config
     * @param  array<string, mixed>  $data
     */
    private function applyConferenceScope(array $config, array &$data): void
    {
        if ($config['conferenceScoped'] ?? false) {
            $data['conference_id'] = $this->conferenceContext->current()->id;
        }
    }

    /**
     * @param  array<string, array<int, mixed>|bool>  $relationData
     */
    private function syncRelations(string $resource, Model $record, array $relationData): void
    {
        if ($resource !== 'users' || ! $record instanceof User) {
            return;
        }

        $record->roles()->sync($relationData['role_ids'] ?? []);
        $record->forceFill([
            'email_verified_at' => $relationData['email_verified'] ? ($record->email_verified_at ?? now()) : null,
        ])->save();
    }

    private function deleteRecordFiles(Model $record): void
    {
        if ($record instanceof Speaker) {
            $this->deleteStoredPublicFile($record->photo);
        }
    }

    private function deleteStoredPublicFile(?string $path): void
    {
        if (blank($path) || Str::startsWith($path, ['http://', 'https://', '/', 'images/', 'assets/'])) {
            return;
        }

        $storagePath = Str::startsWith($path, 'storage/')
            ? Str::after($path, 'storage/')
            : $path;

        Storage::disk('public')->delete($storagePath);
    }

    private function prepareSlug(Request $request, string $resource): void
    {
        $slugSource = match ($resource) {
            'topics', 'pages', 'announcements' => 'title',
            default => null,
        };

        if (! $slugSource) {
            return;
        }

        $source = filled($request->input('slug'))
            ? (string) $request->input('slug')
            : (string) $request->input($slugSource);

        $request->merge(['slug' => Str::slug($source)]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(Request $request, string $resource, ?Model $record = null): array
    {
        return match ($resource) {
            'users' => [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', $this->uniqueRule('users', 'email', $record)],
                'whatsapp' => ['nullable', 'string', 'max:255'],
                'institution' => ['nullable', 'string', 'max:255'],
                'country' => ['nullable', 'string', 'max:255'],
                'password' => [$record ? 'nullable' : 'required', 'confirmed', Password::defaults()],
                'email_verified' => ['nullable', 'boolean'],
                'role_ids' => ['nullable', 'array'],
                'role_ids.*' => ['integer', Rule::exists('roles', 'id')],
            ],
            'topics' => [
                'title' => ['required', 'string', 'max:255'],
                'slug' => ['required', 'string', 'max:255', $this->uniqueRule('conference_topics', 'slug', $record, true)],
                'description' => ['nullable', 'string'],
                'keywords' => ['nullable', 'string'],
                'display_order' => ['nullable', 'integer', 'min:0'],
                'active' => ['nullable', 'boolean'],
            ],
            'fees' => [
                'name' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'participant_type' => ['required', 'string', 'max:255'],
                'attendance_mode' => ['nullable', 'string', 'max:255'],
                'amount' => ['required', 'integer', 'min:0'],
                'currency' => ['required', 'string', 'size:3'],
                'quota' => ['nullable', 'integer', 'min:0'],
                'registration_start' => ['nullable', 'date'],
                'registration_end' => ['nullable', 'date', 'after_or_equal:registration_start'],
                'active' => ['nullable', 'boolean'],
            ],
            'dates' => [
                'name' => ['required', 'string', 'max:255'],
                'starts_at' => ['required', 'date'],
                'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
                'status' => [
                    'required',
                    'string',
                    Rule::in(array_map(fn (DateMilestoneStatus $status): string => $status->value, DateMilestoneStatus::cases())),
                ],
                'display_order' => ['nullable', 'integer', 'min:0'],
                'visible' => ['nullable', 'boolean'],
            ],
            'speakers' => [
                'type' => ['required', 'string', 'max:255'],
                'name' => ['required', 'string', 'max:255'],
                'title' => ['nullable', 'string', 'max:255'],
                'affiliation' => ['nullable', 'string', 'max:255'],
                'country' => ['nullable', 'string', 'max:255'],
                'biography' => ['nullable', 'string'],
                'topic_title' => ['nullable', 'string', 'max:255'],
                'photo_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'remove_photo' => ['nullable', 'boolean'],
                'attendance_mode' => ['nullable', 'string', 'max:255'],
                'display_order' => ['nullable', 'integer', 'min:0'],
                'active' => ['nullable', 'boolean'],
            ],
            'pages' => [
                'title' => ['required', 'string', 'max:255'],
                'slug' => ['required', 'string', 'max:255', $this->uniqueRule('pages', 'slug', $record)],
                'meta_title' => ['nullable', 'string', 'max:255'],
                'meta_description' => ['nullable', 'string'],
                'published' => ['nullable', 'boolean'],
            ],
            'sections' => [
                'page_id' => [
                    'required',
                    'integer',
                    Rule::exists('pages', 'id')->where(
                        fn (QueryBuilder $query): QueryBuilder => $query->where('conference_id', $this->conferenceContext->current()->id),
                    ),
                ],
                'key' => ['required', 'string', 'max:255', $this->uniqueSectionKeyRule($request, $record)],
                'title' => ['nullable', 'string', 'max:255'],
                'body' => ['nullable', 'string'],
                'display_order' => ['nullable', 'integer', 'min:0'],
                'published' => ['nullable', 'boolean'],
            ],
            'announcements' => [
                'title' => ['required', 'string', 'max:255'],
                'slug' => ['required', 'string', 'max:255', $this->uniqueRule('announcements', 'slug', $record)],
                'excerpt' => ['nullable', 'string', 'max:255'],
                'body' => ['required', 'string'],
                'published_at' => ['nullable', 'date'],
                'published' => ['nullable', 'boolean'],
            ],
            'faqs' => [
                'question' => ['required', 'string', 'max:255'],
                'answer' => ['required', 'string'],
                'display_order' => ['nullable', 'integer', 'min:0'],
                'active' => ['nullable', 'boolean'],
            ],
            'partners' => [
                'name' => ['required', 'string', 'max:255'],
                'type' => ['required', 'string', 'max:255'],
                'logo' => ['nullable', 'string', 'max:255'],
                'url' => ['nullable', 'url', 'max:255'],
                'display_order' => ['nullable', 'integer', 'min:0'],
                'active' => ['nullable', 'boolean'],
            ],
            default => abort(404),
        };
    }

    private function uniqueRule(string $table, string $column, ?Model $record = null, bool $scopeToConference = false): Unique
    {
        $rule = Rule::unique($table, $column);

        if ($scopeToConference) {
            $rule->where(fn (QueryBuilder $query): QueryBuilder => $query->where('conference_id', $this->conferenceContext->current()->id));
        }

        if ($record) {
            $rule->ignore($record->getKey());
        }

        return $rule;
    }

    private function uniqueSectionKeyRule(Request $request, ?Model $record = null): Unique
    {
        $rule = Rule::unique('page_sections', 'key')
            ->where(fn (QueryBuilder $query): QueryBuilder => $query->where('page_id', $request->integer('page_id')));

        if ($record) {
            $rule->ignore($record->getKey());
        }

        return $rule;
    }

    /**
     * @return array<string, mixed>
     */
    private function valuesFor(string $resource, ?Model $record = null): array
    {
        $values = $record ? $record->attributesToArray() : [];

        if ($record instanceof User) {
            $record->loadMissing('roles');
            $values['role_ids'] = $record->roles->pluck('id')->all();
            $values['email_verified'] = filled($record->email_verified_at);
            unset($values['password']);
        }

        if ($record instanceof ConferenceTopic) {
            $values['keywords'] = collect($record->keywords ?? [])->join(', ');
        }

        if ($record instanceof ConferenceDate) {
            $values['starts_at'] = $record->starts_at?->toDateString();
            $values['ends_at'] = $record->ends_at?->toDateString();
            $values['status'] = $record->status->value;
        }

        if ($record instanceof RegistrationFee) {
            $values['registration_start'] = $record->registration_start?->toDateString();
            $values['registration_end'] = $record->registration_end?->toDateString();
        }

        if ($record instanceof Announcement) {
            $values['published_at'] = $record->published_at?->format('Y-m-d\TH:i');
        }

        if ($record instanceof Speaker) {
            $values['photo_file'] = $record->photo;
            $values['photo_file_url'] = $record->photoUrl();
            $values['remove_photo'] = false;
        }

        return array_merge($this->defaultValues($resource), $values);
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultValues(string $resource): array
    {
        return match ($resource) {
            'users' => [
                'email_verified' => true,
                'role_ids' => $this->defaultUserRoleIds(),
            ],
            'topics', 'speakers', 'faqs', 'partners' => [
                'active' => true,
                'display_order' => 0,
            ],
            'fees' => [
                'currency' => 'IDR',
                'active' => true,
            ],
            'dates' => [
                'status' => DateMilestoneStatus::Upcoming->value,
                'visible' => true,
                'display_order' => 0,
            ],
            'pages', 'announcements', 'sections' => [
                'published' => true,
                'display_order' => 0,
            ],
            default => [],
        };
    }

    /**
     * @return array<int, int>
     */
    private function defaultUserRoleIds(): array
    {
        $roleId = Role::query()->where('name', UserRole::Participant->value)->value('id');

        return $roleId ? [(int) $roleId] : [];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function resourceConfig(string $resource): array
    {
        return match ($resource) {
            'users' => [
                'model' => User::class,
                'singular' => 'User',
                'description' => 'Manage accounts and role assignments.',
                'indexRoute' => 'admin.system.users',
                'fields' => [
                    ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true, 'col' => 'col-md-6'],
                    ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true, 'col' => 'col-md-6'],
                    ['name' => 'whatsapp', 'label' => 'WhatsApp', 'type' => 'text', 'col' => 'col-md-4'],
                    ['name' => 'institution', 'label' => 'Institution', 'type' => 'text', 'col' => 'col-md-4'],
                    ['name' => 'country', 'label' => 'Country', 'type' => 'text', 'col' => 'col-md-4'],
                    ['name' => 'password', 'label' => 'Password', 'type' => 'password', 'col' => 'col-md-6', 'help' => 'Leave blank on edit to keep the current password.'],
                    ['name' => 'password_confirmation', 'label' => 'Confirm Password', 'type' => 'password', 'col' => 'col-md-6'],
                    ['name' => 'role_ids', 'label' => 'Roles', 'type' => 'multiselect', 'options' => $this->roleOptions(), 'col' => 'col-md-8'],
                    ['name' => 'email_verified', 'label' => 'Email verified', 'type' => 'checkbox', 'col' => 'col-md-4'],
                ],
            ],
            'topics' => [
                'model' => ConferenceTopic::class,
                'singular' => 'Topic',
                'description' => 'Manage conference scope topics shown on the public site.',
                'indexRoute' => 'admin.conference.topics',
                'conferenceScoped' => true,
                'fields' => [
                    ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'required' => true, 'col' => 'col-md-7'],
                    ['name' => 'slug', 'label' => 'Slug', 'type' => 'text', 'required' => true, 'col' => 'col-md-5', 'help' => 'Leave blank to generate it from the title.'],
                    ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'rows' => 4, 'col' => 'col-12'],
                    ['name' => 'keywords', 'label' => 'Keywords', 'type' => 'textarea', 'rows' => 3, 'col' => 'col-md-8', 'help' => 'Separate keywords with commas or new lines.'],
                    ['name' => 'display_order', 'label' => 'Display Order', 'type' => 'number', 'col' => 'col-md-2'],
                    ['name' => 'active', 'label' => 'Active', 'type' => 'checkbox', 'col' => 'col-md-2'],
                ],
            ],
            'fees' => [
                'model' => RegistrationFee::class,
                'singular' => 'Registration Fee',
                'description' => 'Manage participant and presenter fee categories.',
                'indexRoute' => 'admin.conference.fees',
                'conferenceScoped' => true,
                'fields' => [
                    ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true, 'col' => 'col-md-6'],
                    ['name' => 'participant_type', 'label' => 'Participant Type', 'type' => 'select', 'required' => true, 'options' => $this->participantTypeOptions(), 'col' => 'col-md-3'],
                    ['name' => 'attendance_mode', 'label' => 'Attendance Mode', 'type' => 'select', 'options' => $this->attendanceModeOptions(), 'placeholder' => 'Any mode', 'col' => 'col-md-3'],
                    ['name' => 'amount', 'label' => 'Amount', 'type' => 'number', 'required' => true, 'col' => 'col-md-3'],
                    ['name' => 'currency', 'label' => 'Currency', 'type' => 'text', 'required' => true, 'col' => 'col-md-2'],
                    ['name' => 'quota', 'label' => 'Quota', 'type' => 'number', 'col' => 'col-md-2'],
                    ['name' => 'registration_start', 'label' => 'Registration Start', 'type' => 'date', 'col' => 'col-md-2'],
                    ['name' => 'registration_end', 'label' => 'Registration End', 'type' => 'date', 'col' => 'col-md-2'],
                    ['name' => 'active', 'label' => 'Active', 'type' => 'checkbox', 'col' => 'col-md-1'],
                    ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'rows' => 4, 'col' => 'col-12'],
                ],
            ],
            'dates' => [
                'model' => ConferenceDate::class,
                'singular' => 'Important Date',
                'description' => 'Manage visible timeline milestones.',
                'indexRoute' => 'admin.conference.dates',
                'conferenceScoped' => true,
                'fields' => [
                    ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true, 'col' => 'col-md-5'],
                    ['name' => 'starts_at', 'label' => 'Starts At', 'type' => 'date', 'required' => true, 'col' => 'col-md-2'],
                    ['name' => 'ends_at', 'label' => 'Ends At', 'type' => 'date', 'col' => 'col-md-2'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'required' => true, 'options' => $this->dateStatusOptions(), 'col' => 'col-md-2'],
                    ['name' => 'display_order', 'label' => 'Order', 'type' => 'number', 'col' => 'col-md-1'],
                    ['name' => 'visible', 'label' => 'Visible', 'type' => 'checkbox', 'col' => 'col-md-2'],
                ],
            ],
            'speakers' => [
                'model' => Speaker::class,
                'singular' => 'Speaker',
                'description' => 'Manage keynote and invited speaker CMS data.',
                'indexRoute' => 'admin.conference.speakers',
                'conferenceScoped' => true,
                'fields' => [
                    ['name' => 'type', 'label' => 'Type', 'type' => 'select', 'required' => true, 'options' => ['keynote' => 'Keynote', 'speaker' => 'Speaker', 'panelist' => 'Panelist'], 'col' => 'col-md-3'],
                    ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true, 'col' => 'col-md-5'],
                    ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'col' => 'col-md-4'],
                    ['name' => 'affiliation', 'label' => 'Affiliation', 'type' => 'text', 'col' => 'col-md-5'],
                    ['name' => 'country', 'label' => 'Country', 'type' => 'text', 'col' => 'col-md-3'],
                    ['name' => 'attendance_mode', 'label' => 'Attendance Mode', 'type' => 'select', 'options' => $this->attendanceModeOptions(), 'col' => 'col-md-2'],
                    ['name' => 'display_order', 'label' => 'Order', 'type' => 'number', 'col' => 'col-md-1'],
                    ['name' => 'active', 'label' => 'Active', 'type' => 'checkbox', 'col' => 'col-md-1'],
                    ['name' => 'topic_title', 'label' => 'Topic Title', 'type' => 'text', 'col' => 'col-md-6'],
                    ['name' => 'photo_file', 'label' => 'Photo', 'type' => 'file', 'accept' => 'image/png,image/jpeg,image/webp', 'col' => 'col-md-4', 'help' => 'JPG, PNG, or WEBP. Max 2 MB.'],
                    ['name' => 'remove_photo', 'label' => 'Remove current photo', 'type' => 'checkbox', 'col' => 'col-md-2'],
                    ['name' => 'biography', 'label' => 'Biography', 'type' => 'textarea', 'rows' => 5, 'col' => 'col-12'],
                ],
            ],
            'pages' => [
                'model' => Page::class,
                'singular' => 'Page',
                'description' => 'Manage page metadata and publication status.',
                'indexRoute' => 'admin.content.pages',
                'conferenceScoped' => true,
                'fields' => [
                    ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'required' => true, 'col' => 'col-md-7'],
                    ['name' => 'slug', 'label' => 'Slug', 'type' => 'text', 'required' => true, 'col' => 'col-md-5', 'help' => 'Leave blank to generate it from the title.'],
                    ['name' => 'meta_title', 'label' => 'Meta Title', 'type' => 'text', 'col' => 'col-md-8'],
                    ['name' => 'published', 'label' => 'Published', 'type' => 'checkbox', 'col' => 'col-md-4'],
                    ['name' => 'meta_description', 'label' => 'Meta Description', 'type' => 'textarea', 'rows' => 4, 'col' => 'col-12'],
                ],
            ],
            'sections' => [
                'model' => PageSection::class,
                'singular' => 'Page Section',
                'description' => 'Manage editable content blocks for CMS pages.',
                'indexRoute' => 'admin.content.sections',
                'fields' => [
                    ['name' => 'page_id', 'label' => 'Page', 'type' => 'select', 'required' => true, 'options' => $this->pageOptions(), 'col' => 'col-md-5'],
                    ['name' => 'key', 'label' => 'Key', 'type' => 'text', 'required' => true, 'col' => 'col-md-3'],
                    ['name' => 'display_order', 'label' => 'Order', 'type' => 'number', 'col' => 'col-md-2'],
                    ['name' => 'published', 'label' => 'Published', 'type' => 'checkbox', 'col' => 'col-md-2'],
                    ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'col' => 'col-12'],
                    ['name' => 'body', 'label' => 'Body', 'type' => 'textarea', 'rows' => 8, 'col' => 'col-12'],
                ],
            ],
            'announcements' => [
                'model' => Announcement::class,
                'singular' => 'Announcement',
                'description' => 'Manage public announcements.',
                'indexRoute' => 'admin.content.announcements',
                'conferenceScoped' => true,
                'fields' => [
                    ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'required' => true, 'col' => 'col-md-7'],
                    ['name' => 'slug', 'label' => 'Slug', 'type' => 'text', 'required' => true, 'col' => 'col-md-5', 'help' => 'Leave blank to generate it from the title.'],
                    ['name' => 'excerpt', 'label' => 'Excerpt', 'type' => 'text', 'col' => 'col-md-7'],
                    ['name' => 'published_at', 'label' => 'Published At', 'type' => 'datetime-local', 'col' => 'col-md-3'],
                    ['name' => 'published', 'label' => 'Published', 'type' => 'checkbox', 'col' => 'col-md-2'],
                    ['name' => 'body', 'label' => 'Body', 'type' => 'textarea', 'rows' => 8, 'required' => true, 'col' => 'col-12'],
                ],
            ],
            'faqs' => [
                'model' => Faq::class,
                'singular' => 'FAQ',
                'description' => 'Manage public FAQ entries.',
                'indexRoute' => 'admin.content.faqs',
                'conferenceScoped' => true,
                'fields' => [
                    ['name' => 'question', 'label' => 'Question', 'type' => 'text', 'required' => true, 'col' => 'col-md-8'],
                    ['name' => 'display_order', 'label' => 'Order', 'type' => 'number', 'col' => 'col-md-2'],
                    ['name' => 'active', 'label' => 'Active', 'type' => 'checkbox', 'col' => 'col-md-2'],
                    ['name' => 'answer', 'label' => 'Answer', 'type' => 'textarea', 'rows' => 5, 'required' => true, 'col' => 'col-12'],
                ],
            ],
            'partners' => [
                'model' => Partner::class,
                'singular' => 'Partner',
                'description' => 'Manage public partners and organizers.',
                'indexRoute' => 'admin.content.partners',
                'conferenceScoped' => true,
                'fields' => [
                    ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true, 'col' => 'col-md-6'],
                    ['name' => 'type', 'label' => 'Type', 'type' => 'select', 'required' => true, 'options' => ['organizer' => 'Organizer', 'partner' => 'Partner', 'sponsor' => 'Sponsor'], 'col' => 'col-md-2'],
                    ['name' => 'display_order', 'label' => 'Order', 'type' => 'number', 'col' => 'col-md-2'],
                    ['name' => 'active', 'label' => 'Active', 'type' => 'checkbox', 'col' => 'col-md-2'],
                    ['name' => 'logo', 'label' => 'Logo Path', 'type' => 'text', 'col' => 'col-md-6'],
                    ['name' => 'url', 'label' => 'URL', 'type' => 'url', 'col' => 'col-md-6'],
                ],
            ],
            default => abort(404),
        };
    }

    /**
     * @return array<int, string>
     */
    private function roleOptions(): array
    {
        return Role::query()->orderBy('label')->pluck('label', 'id')->all();
    }

    /**
     * @return array<int, string>
     */
    private function pageOptions(): array
    {
        return Page::query()
            ->whereBelongsTo($this->conferenceContext->current())
            ->orderBy('title')
            ->pluck('title', 'id')
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function dateStatusOptions(): array
    {
        return collect(DateMilestoneStatus::cases())
            ->mapWithKeys(fn (DateMilestoneStatus $status): array => [$status->value => $status->label()])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function participantTypeOptions(): array
    {
        return [
            'internal_student' => 'Internal Participant / Student',
            'general' => 'General Participant',
            'presenter' => 'Presenter',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function attendanceModeOptions(): array
    {
        return [
            'online' => 'Online',
            'offline' => 'Offline',
            'hybrid' => 'Hybrid',
        ];
    }
}
