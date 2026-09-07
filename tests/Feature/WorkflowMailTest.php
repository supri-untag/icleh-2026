<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Jobs\SendConferenceMailJob;
use App\Models\Conference;
use App\Models\EmailTemplate;
use App\Models\MailLog;
use App\Models\Submission;
use App\Models\User;
use App\Services\Mail\MailService;
use App\Services\Mail\MailTemplateService;
use App\Services\Mail\WorkflowMailService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use RuntimeException;
use Tests\TestCase;

class WorkflowMailTest extends TestCase
{
    public function test_mail_sender_delivers_and_records_success_without_resending_completed_job(): void
    {
        Queue::fake();
        config(['mail.default' => 'array', 'mail.from.address' => 'committee@example.test', 'mail.from.name' => 'ICLEH Committee']);
        $user = User::factory()->create();
        app(WorkflowMailService::class)->user($user, 'account_registered', 'Account created');
        $job = Queue::pushed(SendConferenceMailJob::class)->first();

        $job->handle(app(MailService::class));
        $job->handle(app(MailService::class));

        $messages = Mail::mailer('array')->getSymfonyTransport()->messages();
        $this->assertCount(1, $messages);
        $message = $messages->first()->getOriginalMessage();
        $this->assertSame('committee@example.test', $message->getFrom()[0]->getAddress());
        $this->assertSame($user->email, $message->getTo()[0]->getAddress());
        $this->assertStringContainsString('Account created', $message->getHtmlBody());
        $this->assertStringContainsString('Account created', $message->getTextBody());
        $this->assertSame('sent', MailLog::query()->findOrFail($job->mailLogId)->status->value);
    }

    public function test_review_emails_reach_main_author_and_coauthor_without_confidential_comments(): void
    {
        Queue::fake();
        $conference = Conference::query()->where('slug', 'icleh-2026')->firstOrFail();
        $submission = Submission::factory()->for($conference)->create();
        $coauthor = User::factory()->create();
        $submission->authors()->create(['name' => $coauthor->name, 'email' => $coauthor->email, 'user_id' => $coauthor->id]);
        $admin = User::factory()->create();
        $admin->assignRole(UserRole::Admin);

        $this->actingAs($admin)->post(route('admin.submissions.review.store', $submission), [
            'scores' => ['quality' => 4],
            'recommendation' => 'minor_revision',
            'comments_for_author' => 'Please clarify your methodology.',
            'confidential_comments' => 'Private committee discussion.',
        ])->assertSessionHasNoErrors();

        $logs = MailLog::query()->where('template_code', 'review_result')->get();
        $this->assertEqualsCanonicalizing([$submission->user->email, $coauthor->email], $logs->pluck('recipient')->all());
        foreach (Queue::pushed(SendConferenceMailJob::class) as $queued) {
            $data = $queued->data;
            $rendered = app(MailTemplateService::class)->render($data->template, $data->subject, $data->data, $data->conferenceId);
            $this->assertStringContainsString('Please clarify your methodology.', $rendered['html']);
            $this->assertStringNotContainsString('Private committee discussion.', $rendered['html']);
        }
    }

    public function test_revision_and_acceptance_email_all_authors_once_per_status_change(): void
    {
        Queue::fake();
        $conference = Conference::query()->where('slug', 'icleh-2026')->firstOrFail();
        $submission = Submission::factory()->for($conference)->create();
        $admin = User::factory()->create();
        $admin->assignRole(UserRole::Admin);

        $this->actingAs($admin)->postJson(route('admin.submissions.decision', $submission), ['status' => 'revision_required', 'notes' => 'Revise the abstract.'])->assertOk();
        $this->postJson(route('admin.submissions.decision', $submission), ['status' => 'abstract_accepted'])->assertOk();
        $this->postJson(route('admin.submissions.decision', $submission), ['status' => 'abstract_accepted'])->assertOk();

        $this->assertSame(1, MailLog::query()->where('template_code', 'revision_requested')->count());
        $this->assertSame(1, MailLog::query()->where('template_code', 'abstract_accepted')->count());
        $this->assertSame(1, MailLog::query()->where('template_code', 'loa_issued')->count());
    }

    public function test_rollback_removes_notification_log_and_custom_templates_are_preserved(): void
    {
        Queue::fake();
        $user = User::factory()->create();
        try {
            DB::transaction(function () use ($user): void {
                app(WorkflowMailService::class)->user($user, 'profile_updated', 'Profile updated');
                throw new RuntimeException('Rollback');
            });
        } catch (RuntimeException) {
        }
        $this->assertSame(0, MailLog::query()->where('user_id', $user->id)->count());

        $template = EmailTemplate::query()->where('code', 'review_result')->firstOrFail();
        $template->forceFill(['body_html' => '<p>Custom {{ participant_name }}</p>', 'body_text' => 'Custom {{ participant_name }}'])->save();
        $rendered = app(MailTemplateService::class)->render('review_result', 'Review', ['participant_name' => '<script>bad</script>'], $template->conference_id);
        $this->assertSame('<p>Custom &lt;script&gt;bad&lt;/script&gt;</p>', $rendered['html']);
    }
}
