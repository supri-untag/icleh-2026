<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Conference;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SubmissionDecisionTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_admin_can_read_abstract_save_review_and_accept(): void
    {
        Queue::fake();
        Storage::fake();
        $admin = User::factory()->create();
        $admin->assignRole(UserRole::Admin);
        $conference = Conference::query()->where('slug', 'icleh-2026')->firstOrFail();
        $submission = Submission::factory()->for($conference)->create(['abstract_text' => 'Research evidence for review.', 'abstract_file' => 'abstract.pdf']);
        Storage::put('abstract.pdf', 'abstract');

        $this->actingAs($admin)->get(route('admin.submissions.review', $submission))->assertOk()->assertSee('Research evidence for review.');
        $this->get(route('admin.submissions.file', $submission))->assertDownload('abstract.pdf');
        $payload = ['scores' => ['originality' => 4, 'relevance' => 5, 'quality' => 4], 'recommendation' => 'accept', 'comments_for_author' => 'Clear contribution.'];
        $this->post(route('admin.submissions.review.store', $submission), $payload)->assertSessionHasNoErrors()->assertRedirect();
        $assignment = $submission->reviewAssignments()->with('review.scores')->firstOrFail();
        $this->assertSame($admin->id, $assignment->reviewer_id);
        $this->assertSame('Clear contribution.', $assignment->review->comments_for_author);
        $this->assertCount(3, $assignment->review->scores);
        $this->get(route('admin.submissions.review', $submission))->assertOk()->assertSee('Clear contribution.');
        $this->postJson(route('admin.submissions.decision', $submission), ['status' => 'abstract_accepted', 'notes' => 'Approved by committee.'])->assertOk();
        $this->assertSame('abstract_accepted', $submission->fresh()->status->value);
        $this->assertNotNull($submission->fresh()->loaDocument);
        $this->post(route('admin.submissions.review.store', $submission), $payload)->assertSessionHasNoErrors();
        $this->assertSame(1, $submission->reviewAssignments()->count());
        $this->assertSame('abstract_accepted', $submission->fresh()->status->value);
        $this->actingAs($submission->user)->get(route('admin.submissions.file', $submission))->assertForbidden();
    }
}
