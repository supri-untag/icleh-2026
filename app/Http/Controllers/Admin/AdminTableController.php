<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\ConferenceDate;
use App\Models\ConferenceTopic;
use App\Models\Faq;
use App\Models\MailLog;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\Partner;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\RegistrationFee;
use App\Models\Speaker;
use App\Models\Submission;
use App\Models\User;
use App\Services\ConferenceContext;
use App\Services\DataTableService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\HtmlString;
use Illuminate\View\View;

class AdminTableController extends Controller
{
    public function __construct(
        private ConferenceContext $conferenceContext,
        private DataTableService $dataTables,
    ) {}

    public function view(string $table): View
    {
        abort_unless(array_key_exists($table, $this->tableConfig()), 404);

        return view('admin.table', $this->tableConfig()[$table]);
    }

    public function data(Request $request, string $table): JsonResponse
    {
        $conference = $this->conferenceContext->current();

        return match ($table) {
            'users' => $this->dataTables->response(
                $request,
                User::query()->with('roles:id,name,label'),
                ['name', 'email', 'institution', 'country'],
                ['id', 'name', 'email', 'created_at'],
                fn (User $user): array => [
                    'id' => $user->id,
                    'name' => e($user->name),
                    'email' => e($user->email),
                    'institution' => e($user->institution ?? '-'),
                    'roles' => e($user->roles->pluck('label')->join(', ') ?: '-'),
                    'created_at' => $user->created_at?->format('d M Y H:i'),
                    'actions' => $this->crudActions($user, 'users')->toHtml(),
                ],
            ),
            'registrations' => $this->dataTables->response(
                $request,
                Registration::query()->whereBelongsTo($conference)->with(['user:id,name,email,institution', 'fee:id,name,amount,currency']),
                ['registration_code', 'participant_type', 'attendance_mode', 'status'],
                ['id', 'registration_code', 'participant_type', 'status', 'created_at'],
                fn (Registration $registration): array => [
                    'id' => $registration->id,
                    'registration_code' => e($registration->registration_code),
                    'participant' => e($registration->user->name),
                    'type' => e(str_replace('_', ' ', $registration->participant_type)),
                    'fee' => e($registration->fee?->name ?? '-'),
                    'status' => view('admin.partials.status-badge', ['status' => $registration->status->label(), 'tone' => 'primary'])->render(),
                    'created_at' => $registration->created_at?->format('d M Y H:i'),
                ],
            ),
            'payments' => $this->dataTables->response(
                $request,
                Payment::query()
                    ->whereHas('registration', fn ($query) => $query->whereBelongsTo($conference))
                    ->with(['registration.user:id,name,email', 'registration.fee:id,name,amount,currency']),
                ['payment_code', 'status', 'method'],
                ['id', 'payment_code', 'status', 'submitted_at', 'created_at'],
                fn (Payment $payment): array => [
                    'id' => $payment->id,
                    'payment_code' => e($payment->payment_code),
                    'participant' => e($payment->registration->user->name),
                    'amount' => e($payment->formattedAmount()),
                    'status' => view('admin.partials.status-badge', ['status' => $payment->status->label(), 'tone' => $payment->status->value])->render(),
                    'submitted_at' => $payment->submitted_at?->format('d M Y H:i') ?? '-',
                    'actions' => $this->paymentActions($payment)->toHtml(),
                ],
            ),
            'submissions' => $this->dataTables->response(
                $request,
                Submission::query()->whereBelongsTo($conference)->with(['user:id,name,email', 'topic:id,title']),
                ['submission_code', 'title', 'status'],
                ['id', 'submission_code', 'title', 'status', 'created_at'],
                fn (Submission $submission): array => [
                    'id' => $submission->id,
                    'submission_code' => e($submission->submission_code),
                    'title' => e($submission->title),
                    'author' => e($submission->user->name),
                    'topic' => e($submission->topic?->title ?? '-'),
                    'status' => view('admin.partials.status-badge', ['status' => $submission->status->label(), 'tone' => 'primary'])->render(),
                    'actions' => $this->submissionActions($submission)->toHtml(),
                ],
            ),
            'speakers' => $this->dataTables->response(
                $request,
                Speaker::query()->whereBelongsTo($conference),
                ['name', 'type', 'affiliation', 'country'],
                ['id', 'display_order', 'name', 'type'],
                fn (Speaker $speaker): array => [
                    'id' => $speaker->id,
                    'photo' => $this->speakerPhoto($speaker)->toHtml(),
                    'name' => e($speaker->name),
                    'type' => e(ucwords(str_replace('_', ' ', $speaker->type))),
                    'affiliation' => e($speaker->affiliation ?? '-'),
                    'country' => e($speaker->country ?? '-'),
                    'active' => $speaker->active ? 'Yes' : 'No',
                    'actions' => $this->crudActions($speaker, 'speakers')->toHtml(),
                ],
            ),
            'topics' => $this->dataTables->response(
                $request,
                ConferenceTopic::query()->whereBelongsTo($conference),
                ['title', 'description'],
                ['id', 'display_order', 'title'],
                fn (ConferenceTopic $topic): array => [
                    'id' => $topic->id,
                    'title' => e($topic->title),
                    'keywords' => e(collect($topic->keywords ?? [])->join(', ')),
                    'active' => $topic->active ? 'Yes' : 'No',
                    'actions' => $this->crudActions($topic, 'topics')->toHtml(),
                ],
            ),
            'fees' => $this->dataTables->response(
                $request,
                RegistrationFee::query()->whereBelongsTo($conference),
                ['name', 'participant_type', 'attendance_mode', 'currency'],
                ['id', 'amount', 'name'],
                fn (RegistrationFee $fee): array => [
                    'id' => $fee->id,
                    'name' => e($fee->name),
                    'type' => e(str_replace('_', ' ', $fee->participant_type)),
                    'mode' => e($fee->attendance_mode ?? '-'),
                    'amount' => e($fee->formattedAmount()),
                    'active' => $fee->active ? 'Yes' : 'No',
                    'actions' => $this->crudActions($fee, 'fees')->toHtml(),
                ],
            ),
            'dates' => $this->dataTables->response(
                $request,
                ConferenceDate::query()->whereBelongsTo($conference),
                ['name', 'status'],
                ['id', 'display_order', 'starts_at'],
                fn (ConferenceDate $date): array => [
                    'id' => $date->id,
                    'name' => e($date->name),
                    'period' => e($date->starts_at->format('d M Y').($date->ends_at ? ' - '.$date->ends_at->format('d M Y') : '')),
                    'status' => e($date->status->label()),
                    'visible' => $date->visible ? 'Yes' : 'No',
                    'actions' => $this->crudActions($date, 'dates')->toHtml(),
                ],
            ),
            'pages' => $this->dataTables->response(
                $request,
                Page::query()->whereBelongsTo($conference),
                ['title', 'slug', 'meta_title'],
                ['id', 'title', 'slug', 'updated_at'],
                fn (Page $page): array => [
                    'id' => $page->id,
                    'title' => e($page->title),
                    'slug' => e($page->slug),
                    'published' => $page->published ? 'Yes' : 'No',
                    'updated_at' => $page->updated_at?->format('d M Y H:i'),
                    'actions' => $this->crudActions($page, 'pages')->toHtml(),
                ],
            ),
            'sections' => $this->dataTables->response(
                $request,
                PageSection::query()
                    ->whereHas('page', fn ($query) => $query->whereBelongsTo($conference))
                    ->with('page:id,title'),
                ['key', 'title', 'body'],
                ['id', 'display_order', 'key', 'title'],
                fn (PageSection $section): array => [
                    'id' => $section->id,
                    'page' => e($section->page?->title ?? '-'),
                    'key' => e($section->key),
                    'title' => e($section->title ?? '-'),
                    'published' => $section->published ? 'Yes' : 'No',
                    'actions' => $this->crudActions($section, 'sections')->toHtml(),
                ],
            ),
            'announcements' => $this->dataTables->response(
                $request,
                Announcement::query()->whereBelongsTo($conference),
                ['title', 'slug', 'excerpt'],
                ['id', 'published_at', 'title'],
                fn (Announcement $announcement): array => [
                    'id' => $announcement->id,
                    'title' => e($announcement->title),
                    'slug' => e($announcement->slug),
                    'published_at' => $announcement->published_at?->format('d M Y H:i') ?? '-',
                    'published' => $announcement->published ? 'Yes' : 'No',
                    'actions' => $this->crudActions($announcement, 'announcements')->toHtml(),
                ],
            ),
            'faqs' => $this->dataTables->response(
                $request,
                Faq::query()->whereBelongsTo($conference),
                ['question', 'answer'],
                ['id', 'display_order', 'question'],
                fn (Faq $faq): array => [
                    'id' => $faq->id,
                    'question' => e($faq->question),
                    'answer' => e($faq->answer),
                    'active' => $faq->active ? 'Yes' : 'No',
                    'actions' => $this->crudActions($faq, 'faqs')->toHtml(),
                ],
            ),
            'partners' => $this->dataTables->response(
                $request,
                Partner::query()->whereBelongsTo($conference),
                ['name', 'type', 'url'],
                ['id', 'display_order', 'name'],
                fn (Partner $partner): array => [
                    'id' => $partner->id,
                    'name' => e($partner->name),
                    'type' => e(ucwords(str_replace('_', ' ', $partner->type))),
                    'url' => $partner->url ? e($partner->url) : '-',
                    'active' => $partner->active ? 'Yes' : 'No',
                    'actions' => $this->crudActions($partner, 'partners')->toHtml(),
                ],
            ),
            'mail-logs' => $this->dataTables->response(
                $request,
                MailLog::query(),
                ['recipient', 'template_code', 'subject', 'status'],
                ['id', 'queued_at', 'status', 'recipient'],
                fn (MailLog $mailLog): array => [
                    'id' => $mailLog->id,
                    'recipient' => e($mailLog->recipient),
                    'template' => e($mailLog->template_code ?? '-'),
                    'subject' => e($mailLog->subject),
                    'status' => e($mailLog->status->label()),
                    'queued_at' => $mailLog->queued_at?->format('d M Y H:i') ?? '-',
                    'error' => e($mailLog->error_message ?? '-'),
                ],
            ),
            'audit-logs' => $this->dataTables->response(
                $request,
                AuditLog::query()->with('actor:id,name'),
                ['action', 'subject_type', 'subject_id'],
                ['id', 'created_at', 'action'],
                fn (AuditLog $auditLog): array => [
                    'id' => $auditLog->id,
                    'actor' => e($auditLog->actor?->name ?? 'System'),
                    'action' => e($auditLog->action),
                    'subject' => e(class_basename((string) $auditLog->subject_type).' #'.$auditLog->subject_id),
                    'created_at' => $auditLog->created_at?->format('d M Y H:i'),
                ],
            ),
            default => abort(404),
        };
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function tableConfig(): array
    {
        return [
            'registrations' => [
                'title' => 'Registrations',
                'description' => 'Participant and presenter registration records.',
                'ajaxUrl' => route('admin.tables.data', 'registrations'),
                'columns' => [
                    ['data' => 'id', 'title' => '#'],
                    ['data' => 'registration_code', 'title' => 'Code'],
                    ['data' => 'participant', 'title' => 'Participant'],
                    ['data' => 'type', 'title' => 'Type'],
                    ['data' => 'fee', 'title' => 'Fee'],
                    ['data' => 'status', 'title' => 'Status', 'className' => 'no-sort'],
                    ['data' => 'created_at', 'title' => 'Created'],
                ],
            ],
            'payments' => [
                'title' => 'Payments',
                'description' => 'Manual transfer proof queue with async verify/reject actions.',
                'ajaxUrl' => route('admin.tables.data', 'payments'),
                'columns' => [
                    ['data' => 'id', 'title' => '#'],
                    ['data' => 'payment_code', 'title' => 'Code'],
                    ['data' => 'participant', 'title' => 'Participant'],
                    ['data' => 'amount', 'title' => 'Amount'],
                    ['data' => 'status', 'title' => 'Status', 'className' => 'no-sort'],
                    ['data' => 'submitted_at', 'title' => 'Submitted'],
                    ['data' => 'actions', 'title' => 'Actions', 'className' => 'no-sort'],
                ],
            ],
            'submissions' => [
                'title' => 'Abstract Submissions',
                'description' => 'Abstracts, topics, authors, and scientific committee decisions.',
                'ajaxUrl' => route('admin.tables.data', 'submissions'),
                'columns' => [
                    ['data' => 'id', 'title' => '#'],
                    ['data' => 'submission_code', 'title' => 'Code'],
                    ['data' => 'title', 'title' => 'Title'],
                    ['data' => 'author', 'title' => 'Author'],
                    ['data' => 'topic', 'title' => 'Topic'],
                    ['data' => 'status', 'title' => 'Status', 'className' => 'no-sort'],
                    ['data' => 'actions', 'title' => 'Actions', 'className' => 'no-sort'],
                ],
            ],
            'users' => [
                'title' => 'Users',
                'description' => 'Accounts and role assignments.',
                'ajaxUrl' => route('admin.tables.data', 'users'),
                'createUrl' => route('admin.crud.create', 'users'),
                'columns' => [
                    ['data' => 'id', 'title' => '#'],
                    ['data' => 'name', 'title' => 'Name'],
                    ['data' => 'email', 'title' => 'Email'],
                    ['data' => 'institution', 'title' => 'Institution'],
                    ['data' => 'roles', 'title' => 'Roles'],
                    ['data' => 'created_at', 'title' => 'Created'],
                    ['data' => 'actions', 'title' => 'Actions', 'className' => 'no-sort'],
                ],
            ],
            'speakers' => [
                'title' => 'Speakers',
                'description' => 'Keynote and invited speaker CMS data.',
                'ajaxUrl' => route('admin.tables.data', 'speakers'),
                'createUrl' => route('admin.crud.create', 'speakers'),
                'columns' => [
                    ['data' => 'id', 'title' => '#'],
                    ['data' => 'photo', 'title' => 'Photo', 'className' => 'no-sort'],
                    ['data' => 'name', 'title' => 'Name'],
                    ['data' => 'type', 'title' => 'Type'],
                    ['data' => 'affiliation', 'title' => 'Affiliation'],
                    ['data' => 'country', 'title' => 'Country'],
                    ['data' => 'active', 'title' => 'Active'],
                    ['data' => 'actions', 'title' => 'Actions', 'className' => 'no-sort'],
                ],
            ],
            'topics' => [
                'title' => 'Topics',
                'description' => 'Conference scopes shown on the public site.',
                'ajaxUrl' => route('admin.tables.data', 'topics'),
                'createUrl' => route('admin.crud.create', 'topics'),
                'columns' => [
                    ['data' => 'id', 'title' => '#'],
                    ['data' => 'title', 'title' => 'Title'],
                    ['data' => 'keywords', 'title' => 'Keywords'],
                    ['data' => 'active', 'title' => 'Active'],
                    ['data' => 'actions', 'title' => 'Actions', 'className' => 'no-sort'],
                ],
            ],
            'fees' => [
                'title' => 'Registration Fees',
                'description' => 'Configurable fee categories.',
                'ajaxUrl' => route('admin.tables.data', 'fees'),
                'createUrl' => route('admin.crud.create', 'fees'),
                'columns' => [
                    ['data' => 'id', 'title' => '#'],
                    ['data' => 'name', 'title' => 'Name'],
                    ['data' => 'type', 'title' => 'Type'],
                    ['data' => 'mode', 'title' => 'Mode'],
                    ['data' => 'amount', 'title' => 'Amount'],
                    ['data' => 'active', 'title' => 'Active'],
                    ['data' => 'actions', 'title' => 'Actions', 'className' => 'no-sort'],
                ],
            ],
            'dates' => [
                'title' => 'Important Dates',
                'description' => 'Timeline milestones from the database.',
                'ajaxUrl' => route('admin.tables.data', 'dates'),
                'createUrl' => route('admin.crud.create', 'dates'),
                'columns' => [
                    ['data' => 'id', 'title' => '#'],
                    ['data' => 'name', 'title' => 'Name'],
                    ['data' => 'period', 'title' => 'Period'],
                    ['data' => 'status', 'title' => 'Status'],
                    ['data' => 'visible', 'title' => 'Visible'],
                    ['data' => 'actions', 'title' => 'Actions', 'className' => 'no-sort'],
                ],
            ],
            'pages' => [
                'title' => 'Pages',
                'description' => 'CMS page metadata and publication status.',
                'ajaxUrl' => route('admin.tables.data', 'pages'),
                'createUrl' => route('admin.crud.create', 'pages'),
                'columns' => [
                    ['data' => 'id', 'title' => '#'],
                    ['data' => 'title', 'title' => 'Title'],
                    ['data' => 'slug', 'title' => 'Slug'],
                    ['data' => 'published', 'title' => 'Published'],
                    ['data' => 'updated_at', 'title' => 'Updated'],
                    ['data' => 'actions', 'title' => 'Actions', 'className' => 'no-sort'],
                ],
            ],
            'sections' => [
                'title' => 'Page Sections',
                'description' => 'Editable content blocks attached to CMS pages.',
                'ajaxUrl' => route('admin.tables.data', 'sections'),
                'createUrl' => route('admin.crud.create', 'sections'),
                'columns' => [
                    ['data' => 'id', 'title' => '#'],
                    ['data' => 'page', 'title' => 'Page'],
                    ['data' => 'key', 'title' => 'Key'],
                    ['data' => 'title', 'title' => 'Title'],
                    ['data' => 'published', 'title' => 'Published'],
                    ['data' => 'actions', 'title' => 'Actions', 'className' => 'no-sort'],
                ],
            ],
            'announcements' => [
                'title' => 'Announcements',
                'description' => 'Public announcement posts.',
                'ajaxUrl' => route('admin.tables.data', 'announcements'),
                'createUrl' => route('admin.crud.create', 'announcements'),
                'columns' => [
                    ['data' => 'id', 'title' => '#'],
                    ['data' => 'title', 'title' => 'Title'],
                    ['data' => 'slug', 'title' => 'Slug'],
                    ['data' => 'published_at', 'title' => 'Published At'],
                    ['data' => 'published', 'title' => 'Published'],
                    ['data' => 'actions', 'title' => 'Actions', 'className' => 'no-sort'],
                ],
            ],
            'faqs' => [
                'title' => 'FAQ',
                'description' => 'Frequently asked questions shown on the public site.',
                'ajaxUrl' => route('admin.tables.data', 'faqs'),
                'createUrl' => route('admin.crud.create', 'faqs'),
                'columns' => [
                    ['data' => 'id', 'title' => '#'],
                    ['data' => 'question', 'title' => 'Question'],
                    ['data' => 'answer', 'title' => 'Answer'],
                    ['data' => 'active', 'title' => 'Active'],
                    ['data' => 'actions', 'title' => 'Actions', 'className' => 'no-sort'],
                ],
            ],
            'partners' => [
                'title' => 'Partners',
                'description' => 'Organizers, partners, and sponsors displayed publicly.',
                'ajaxUrl' => route('admin.tables.data', 'partners'),
                'createUrl' => route('admin.crud.create', 'partners'),
                'columns' => [
                    ['data' => 'id', 'title' => '#'],
                    ['data' => 'name', 'title' => 'Name'],
                    ['data' => 'type', 'title' => 'Type'],
                    ['data' => 'url', 'title' => 'URL'],
                    ['data' => 'active', 'title' => 'Active'],
                    ['data' => 'actions', 'title' => 'Actions', 'className' => 'no-sort'],
                ],
            ],
            'mail-logs' => [
                'title' => 'Mail Logs',
                'description' => 'Queued, sent, and failed conference emails.',
                'ajaxUrl' => route('admin.tables.data', 'mail-logs'),
                'columns' => [
                    ['data' => 'id', 'title' => '#'],
                    ['data' => 'recipient', 'title' => 'Recipient'],
                    ['data' => 'template', 'title' => 'Template'],
                    ['data' => 'subject', 'title' => 'Subject'],
                    ['data' => 'status', 'title' => 'Status'],
                    ['data' => 'queued_at', 'title' => 'Queued'],
                    ['data' => 'error', 'title' => 'Error'],
                ],
            ],
            'audit-logs' => [
                'title' => 'Audit Logs',
                'description' => 'Important domain activity trail.',
                'ajaxUrl' => route('admin.tables.data', 'audit-logs'),
                'columns' => [
                    ['data' => 'id', 'title' => '#'],
                    ['data' => 'actor', 'title' => 'Actor'],
                    ['data' => 'action', 'title' => 'Action'],
                    ['data' => 'subject', 'title' => 'Subject'],
                    ['data' => 'created_at', 'title' => 'Time'],
                ],
            ],
        ];
    }

    private function speakerPhoto(Speaker $speaker): HtmlString
    {
        $photoUrl = $speaker->photoUrl();

        if (! $photoUrl) {
            return new HtmlString('<span class="text-secondary">-</span>');
        }

        return new HtmlString(
            '<img src="'.e($photoUrl).'" alt="'.e($speaker->name).'" class="rounded-circle object-fit-cover" style="width: 42px; height: 42px;">',
        );
    }

    private function crudActions(Model $record, string $resource): HtmlString
    {
        return new HtmlString(
            '<div class="d-flex gap-2">'.
            '<a class="btn btn-sm btn-outline-primary" href="'.e(route('admin.crud.edit', [$resource, $record->getRouteKey()])).'" title="Edit"><i class="ti ti-edit"></i></a>'.
            '<button class="btn btn-sm btn-outline-danger" data-table-action data-method="delete" data-title="Delete record" data-text="This record will be deleted permanently." data-confirm="Delete" data-action-url="'.e(route('admin.crud.destroy', [$resource, $record->getRouteKey()])).'" title="Delete"><i class="ti ti-trash"></i></button>'.
            '</div>',
        );
    }

    private function paymentActions(Payment $payment): HtmlString
    {
        return new HtmlString(
            '<div class="d-flex gap-2">'.
            '<button class="btn btn-sm btn-success" data-table-action data-title="Verify payment" data-confirm="Verify" data-action-url="'.e(route('admin.payments.verify', $payment)).'"><i class="ti ti-check"></i></button>'.
            '<button class="btn btn-sm btn-outline-danger" data-table-action data-prompt="reason" data-title="Reject payment" data-confirm="Reject" data-action-url="'.e(route('admin.payments.reject', $payment)).'"><i class="ti ti-x"></i></button>'.
            '</div>',
        );
    }

    private function submissionActions(Submission $submission): HtmlString
    {
        return new HtmlString(
            '<div class="d-flex gap-2">'.
            '<button class="btn btn-sm btn-success" data-table-action data-status="abstract_accepted" data-title="Accept abstract" data-confirm="Accept" data-action-url="'.e(route('admin.submissions.decision', $submission)).'"><i class="ti ti-file-check"></i></button>'.
            '<button class="btn btn-sm btn-outline-warning" data-table-action data-status="revision_required" data-title="Request revision" data-confirm="Request" data-action-url="'.e(route('admin.submissions.decision', $submission)).'"><i class="ti ti-edit"></i></button>'.
            '<button class="btn btn-sm btn-outline-danger" data-table-action data-status="abstract_rejected" data-title="Reject abstract" data-confirm="Reject" data-action-url="'.e(route('admin.submissions.decision', $submission)).'"><i class="ti ti-file-x"></i></button>'.
            '</div>',
        );
    }
}
