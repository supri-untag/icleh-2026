<?php

namespace Database\Seeders;

use App\Enums\DateMilestoneStatus;
use App\Enums\ProgramSessionType;
use App\Enums\UserRole;
use App\Models\Announcement;
use App\Models\Chamber;
use App\Models\Conference;
use App\Models\ConferenceDate;
use App\Models\ConferenceDay;
use App\Models\ConferenceSetting;
use App\Models\ConferenceTopic;
use App\Models\DocumentTemplate;
use App\Models\EmailTemplate;
use App\Models\Faq;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\Partner;
use App\Models\Permission;
use App\Models\ProgramSchedule;
use App\Models\ProgramSession;
use App\Models\RegistrationFee;
use App\Models\Role;
use App\Models\Speaker;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class IclehSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = $this->seedRoles();
        $this->seedPermissions($roles);

        $adminPassword = config('icleh.admin.password') ?: 'pass';
        $admin = User::query()->updateOrCreate(
            ['email' => config('icleh.admin.email')],
            [
                'name' => 'ICLEH Super Admin',
                'whatsapp' => '+6280000000000',
                'institution' => 'Faculty of Law, Universitas 17 Agustus 1945 Semarang',
                'country' => 'Indonesia',
                'email_verified_at' => now(),
                'password' => Hash::make($adminPassword),
            ],
        );
        $admin->roles()->syncWithoutDetaching([$roles[UserRole::SuperAdmin->value]->id]);

        if (! config('icleh.admin.password') && ! app()->runningUnitTests() && $this->command) {
            $this->command->warn('Generated ICLEH admin password: '.$adminPassword);
        }

        $conference = Conference::query()->updateOrCreate(
            ['slug' => 'icleh-2026'],
            [
                'name' => '5th International Conference on Law, Economy and Health',
                'edition' => '5th ICLEH 2026',
                'theme' => 'Reimagining Law, Economy and Health in the Age of Artificial Intelligence: Advancing Human Dignity, Justice and Sustainable Governance',
                'description' => 'ICLEH 2026 is an international academic forum organized by the Faculty of Law, Universitas 17 Agustus 1945 Semarang.',
                'start_date' => '2026-11-11',
                'end_date' => '2026-11-12',
                'timezone' => 'Asia/Jakarta',
                'mode' => 'hybrid',
                'venue_name' => 'Gedung Pemuda Fakultas Hukum Universitas 17 Agustus 1945 Semarang',
                'location' => 'Semarang, Indonesia',
                'registration_requires_verified_payment' => false,
                'meta_title' => 'ICLEH 2026 - International Conference on Law, Economy, and Health',
                'meta_description' => 'Official website and conference management system for ICLEH 2026.',
                'active' => true,
            ],
        );

        $this->seedSettings($conference);
        $this->seedDates($conference);
        $this->seedTopics($conference);
        $this->seedFees($conference);
        $this->seedSpeakers($conference);
        $this->seedVenueAndProgram($conference);
        $this->seedCms($conference);
        $this->seedEmailTemplates($conference);
    }

    /**
     * @return array<string, Role>
     */
    private function seedRoles(): array
    {
        return collect(UserRole::cases())
            ->mapWithKeys(fn (UserRole $role): array => [
                $role->value => Role::query()->updateOrCreate(
                    ['name' => $role->value],
                    ['label' => $role->label(), 'description' => $role->label().' access'],
                ),
            ])
            ->all();
    }

    /**
     * @param  array<string, Role>  $roles
     */
    private function seedPermissions(array $roles): void
    {
        $permissions = collect([
            'conference.manage',
            'participants.manage',
            'payments.verify',
            'submissions.manage',
            'reviews.manage',
            'program.manage',
            'documents.manage',
            'cms.manage',
            'reports.export',
            'system.manage',
        ])->mapWithKeys(fn (string $name): array => [
            $name => Permission::query()->updateOrCreate(
                ['name' => $name],
                ['label' => Str::headline($name)],
            ),
        ]);

        $roles[UserRole::SuperAdmin->value]->permissions()->sync($permissions->pluck('id'));
        $roles[UserRole::Admin->value]->permissions()->sync($permissions->pluck('id'));
        $roles[UserRole::Finance->value]->permissions()->sync($permissions->only(['participants.manage', 'payments.verify', 'reports.export'])->pluck('id'));
        $roles[UserRole::ScientificCommittee->value]->permissions()->sync($permissions->only(['submissions.manage', 'reviews.manage', 'documents.manage', 'reports.export'])->pluck('id'));
        $roles[UserRole::EventCommittee->value]->permissions()->sync($permissions->only(['program.manage', 'participants.manage', 'reports.export'])->pluck('id'));
    }

    private function seedSettings(Conference $conference): void
    {
        foreach ([
            'payment_bank' => config('icleh.payment.bank_name'),
            'payment_account' => config('icleh.payment.account_number'),
            'payment_account_name' => config('icleh.payment.account_name'),
            'contact_email' => 'icleh@untagsmg.ac.id',
            'contact_whatsapp' => '+62 812 0000 2026',
        ] as $key => $value) {
            ConferenceSetting::query()->updateOrCreate(
                ['conference_id' => $conference->id, 'key' => $key],
                ['value' => ['value' => $value], 'type' => 'string', 'public' => true],
            );
        }
    }

    private function seedDates(Conference $conference): void
    {
        foreach ([
            ['Registration', '2026-09-14', '2026-10-14', DateMilestoneStatus::Upcoming],
            ['Abstract Submission', '2026-09-14', '2026-10-14', DateMilestoneStatus::Upcoming],
            ['Conference', '2026-11-11', '2026-11-12', DateMilestoneStatus::Upcoming],
            ['Full Paper Submission', '2026-11-16', '2026-11-27', DateMilestoneStatus::Upcoming],
        ] as $index => [$name, $start, $end, $status]) {
            ConferenceDate::query()->updateOrCreate(
                ['conference_id' => $conference->id, 'name' => $name],
                [
                    'starts_at' => $start,
                    'ends_at' => $end,
                    'status' => $status,
                    'display_order' => $index + 1,
                    'visible' => true,
                ],
            );
        }
    }

    private function seedTopics(Conference $conference): void
    {
        $topics = [
            'AI, Constitutionalism and Governance' => ['AI governance', 'constitutional law', 'public policy', 'digital government', 'regulatory innovation', 'rule of law'],
            'Human Rights, Human Dignity and Digital Society' => ['human rights', 'privacy', 'personal data protection', 'digital citizenship', 'equality', 'digital inclusion'],
            'AI, Justice and the Future of Legal Systems' => ['law enforcement', 'judiciary', 'legal technology', 'cybercrime', 'digital evidence', 'dispute resolution'],
            'Digital Economy, Business and Sustainable Development' => ['fintech', 'digital trade', 'taxation', 'blockchain', 'ESG', 'sustainable economy'],
            'AI, Health and Human Well-Being' => ['medical law', 'health technology', 'telemedicine', 'patient safety', 'bioethics', 'public health'],
            'Cybersecurity, Data Governance and Digital Sovereignty' => ['cybersecurity', 'cyber resilience', 'critical infrastructure', 'data governance', 'digital sovereignty', 'cross-border data flow'],
            'Ethics, Responsible Innovation and the Future of Humanity' => ['AI ethics', 'algorithmic accountability', 'responsible innovation', 'philosophy of technology', 'future society'],
            'Pancasila, Global Justice and Human-Centered Governance' => ['Pancasila studies', 'constitutional values', 'comparative governance', 'global justice', 'Global South perspective', 'sustainable governance'],
        ];

        foreach ($topics as $index => $titleAndKeywords) {
            $title = (string) $index;
            $keywords = $titleAndKeywords;

            ConferenceTopic::query()->updateOrCreate(
                ['conference_id' => $conference->id, 'slug' => Str::slug($title)],
                [
                    'title' => $title,
                    'keywords' => $keywords,
                    'display_order' => array_search($title, array_keys($topics), true) + 1,
                    'active' => true,
                ],
            );
        }
    }

    private function seedFees(Conference $conference): void
    {
        foreach ([
            ['Internal Participant / Student', 'Participant category for internal participants and students.', 'internal_student', null, 300000],
            ['General Participant', 'General non-presenter participant category.', 'general', null, 450000],
            ['Presenter - Online / Offline + ISBN Proceedings', 'Presenter package for online or offline presentation with ISBN proceedings.', 'presenter', 'hybrid', 1250000],
        ] as [$name, $description, $type, $mode, $amount]) {
            RegistrationFee::query()->updateOrCreate(
                ['conference_id' => $conference->id, 'name' => $name],
                [
                    'description' => $description,
                    'participant_type' => $type,
                    'attendance_mode' => $mode,
                    'amount' => $amount,
                    'currency' => 'IDR',
                    'active' => true,
                    'registration_start' => '2026-09-14',
                    'registration_end' => '2026-10-14',
                ],
            );
        }
    }

    private function seedSpeakers(Conference $conference): void
    {
        $speakers = [
            ['keynote', 'Rector, Universitas 17 Agustus 1945 Semarang', null, 'Universitas 17 Agustus 1945 Semarang', 'Indonesia'],
            ['keynote', 'Dean, Faculty of Law, Universitas 17 Agustus 1945 Semarang', null, 'Universitas 17 Agustus 1945 Semarang', 'Indonesia'],
            ['speaker', 'Prof. Stefan Koos', null, 'Universität der Bundeswehr', 'Germany'],
            ['speaker', 'Prof. Kumaralingam Amirthalingam', null, 'National University of Singapore', 'Singapore'],
            ['speaker', 'Prof. Albert LEE', null, 'Chinese University of Hong Kong', 'Hong Kong'],
            ['speaker', 'Prof. Dr. Anggraeni Endah Kusumaningrum, S.H., M.Hum', null, 'Universitas 17 Agustus 1945 Semarang', 'Indonesia'],
            ['speaker', 'Siti Farahiya, PhD', null, 'Universiti Kebangsaan Malaysia', 'Malaysia'],
            ['speaker', 'Dr. Eugenia Brandao da Silva, S.H., M.H', null, "Universidade Oriental Timor Lorosa'e", 'Timor-Leste'],
            ['speaker', 'Dr. Ahmed Kheir Osman, LL.B., LL.M', null, 'Somali National University', 'Somalia'],
            ['speaker', 'Jeremy Balang', null, 'MahWengKwai & Associates', 'Malaysia'],
            ['speaker', 'Jerry G Tambun, S.H., LLB., LLM., SJD', null, 'ICHLaS', 'Indonesia'],
        ];

        foreach ($speakers as $index => [$type, $name, $title, $affiliation, $country]) {
            Speaker::query()->updateOrCreate(
                ['conference_id' => $conference->id, 'name' => $name],
                [
                    'type' => $type,
                    'title' => $title,
                    'affiliation' => $affiliation,
                    'country' => $country,
                    'attendance_mode' => 'hybrid',
                    'display_order' => $index + 1,
                    'active' => true,
                ],
            );
        }
    }

    private function seedVenueAndProgram(Conference $conference): void
    {
        Venue::query()->updateOrCreate(
            ['conference_id' => $conference->id, 'name' => 'Gedung Pemuda Fakultas Hukum UNTAG Semarang'],
            [
                'address' => 'Faculty of Law, Universitas 17 Agustus 1945 Semarang, Semarang, Indonesia',
                'description' => 'Hybrid conference venue for plenary and parallel sessions.',
                'active' => true,
            ],
        );

        $chamber = Chamber::query()->updateOrCreate(
            ['conference_id' => $conference->id, 'name' => 'Chamber 1'],
            ['room' => 'Hybrid Room 1', 'capacity' => 40, 'moderator' => 'To be assigned', 'operator' => 'To be assigned'],
        );

        foreach ([['2026-11-11', 'Day 1'], ['2026-11-12', 'Day 2']] as $index => [$date, $label]) {
            $day = ConferenceDay::query()->updateOrCreate(
                ['conference_id' => $conference->id, 'date' => $date],
                ['label' => $label, 'display_order' => $index + 1],
            );

            $opening = ProgramSession::query()->updateOrCreate(
                ['conference_day_id' => $day->id, 'name' => $index === 0 ? 'Opening and Keynote' : 'Plenary and Parallel Session'],
                [
                    'type' => $index === 0 ? ProgramSessionType::Opening : ProgramSessionType::Plenary,
                    'start_time' => '08:00',
                    'end_time' => '10:00',
                    'display_order' => 1,
                ],
            );

            ProgramSchedule::query()->updateOrCreate(
                ['conference_day_id' => $day->id, 'title' => $opening->name],
                [
                    'program_session_id' => $opening->id,
                    'chamber_id' => $chamber->id,
                    'start_time' => $opening->start_time,
                    'end_time' => $opening->end_time,
                    'type' => $opening->type,
                    'published' => true,
                ],
            );
        }
    }

    private function seedCms(Conference $conference): void
    {
        foreach ([
            'home' => 'Home',
            'about' => 'About ICLEH',
            'guide-for-authors' => 'Guide for Authors',
            'publication' => 'Publication',
            'contact' => 'Contact',
        ] as $slug => $title) {
            $page = Page::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'conference_id' => $conference->id,
                    'title' => $title,
                    'meta_title' => $title.' - ICLEH 2026',
                    'meta_description' => 'Official '.$title.' page for ICLEH 2026.',
                    'published' => true,
                ],
            );

            PageSection::query()->updateOrCreate(
                ['page_id' => $page->id, 'key' => 'main'],
                [
                    'title' => $title,
                    'body' => 'This page is managed from the ICLEH CMS and seeded as an editable default.',
                    'display_order' => 1,
                    'published' => true,
                ],
            );
        }

        foreach ([
            ['How do I submit an abstract?', 'Register as a presenter, complete registration, then open My Submission from the dashboard.'],
            ['Is the conference online or offline?', 'ICLEH 2026 is a hybrid conference. Presenters can choose online or offline mode.'],
            ['Where can I download LoA?', 'Accepted presenters can download LoA from the participant dashboard.'],
        ] as $index => [$question, $answer]) {
            Faq::query()->updateOrCreate(
                ['conference_id' => $conference->id, 'question' => $question],
                ['answer' => $answer, 'display_order' => $index + 1, 'active' => true],
            );
        }

        Partner::query()->updateOrCreate(
            ['conference_id' => $conference->id, 'name' => 'Faculty of Law, Universitas 17 Agustus 1945 Semarang'],
            ['type' => 'organizer', 'display_order' => 1, 'active' => true],
        );

        Announcement::query()->updateOrCreate(
            ['conference_id' => $conference->id, 'slug' => 'call-for-papers-icleh-2026'],
            [
                'title' => 'Call for Papers ICLEH 2026',
                'excerpt' => 'Abstract submission opens from 14 September to 14 October 2026.',
                'body' => 'The ICLEH 2026 committee invites academics, researchers, and practitioners to submit abstracts aligned with the conference theme and scopes.',
                'published' => true,
                'published_at' => now(),
            ],
        );

        DocumentTemplate::query()->updateOrCreate(
            ['conference_id' => $conference->id, 'type' => 'loa'],
            [
                'name' => 'Default LoA Template',
                'body_html' => '<p>Letter of Acceptance for {{ participant_name }} and {{ submission_title }}</p>',
                'active' => true,
            ],
        );
    }

    private function seedEmailTemplates(Conference $conference): void
    {
        foreach ([
            'email_verification' => 'Email Verification',
            'registration_success' => 'Registration Received',
            'payment_submitted' => 'Payment Submitted',
            'payment_verified' => 'Payment Verified',
            'payment_rejected' => 'Payment Rejected',
            'abstract_submitted' => 'Abstract Submitted',
            'review_result' => 'Review Result',
            'revision_requested' => 'Revision Requested',
            'abstract_accepted' => 'Abstract Accepted',
            'abstract_rejected' => 'Abstract Rejected',
            'loa_issued' => 'LoA Issued',
            'full_paper_reminder' => 'Full Paper Reminder',
            'schedule_published' => 'Schedule Published',
            'schedule_changed' => 'Schedule Changed',
            'certificate_available' => 'Certificate Available',
        ] as $code => $name) {
            EmailTemplate::query()->updateOrCreate(
                ['conference_id' => $conference->id, 'code' => $code],
                [
                    'name' => $name,
                    'subject' => $name.' - ICLEH 2026',
                    'body_html' => '<p>Dear {{ participant_name }},</p><p>{{ conference_name }} update: '.$name.'.</p><p>{{ submission_title }}</p><p>{{ rejection_reason }}</p>',
                    'body_text' => "Dear {{ participant_name }},\n{{ conference_name }} update: ".$name.".\n{{ submission_title }}\n{{ rejection_reason }}",
                    'from_name' => 'ICLEH 2026 Committee',
                    'active' => true,
                ],
            );
        }
    }
}
