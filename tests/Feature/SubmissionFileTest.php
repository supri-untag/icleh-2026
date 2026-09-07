<?php

namespace Tests\Feature;

use App\Models\Conference;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SubmissionFileTest extends TestCase
{
    public function test_owner_can_view_and_download_uploaded_file(): void
    {
        Storage::fake();
        $submission = Submission::factory()->for(Conference::where('slug', 'icleh-2026')->firstOrFail())->create();
        Storage::put('abstract.pdf', "%PDF-1.4\nExample abstract");
        $file = $submission->files()->create([
            'uploaded_by' => $submission->user_id,
            'type' => 'abstract',
            'version' => 1,
            'original_filename' => 'My abstract.pdf',
            'storage_path' => 'abstract.pdf',
            'mime' => 'application/pdf',
            'size' => 25,
            'uploaded_at' => now(),
        ]);
        $url = route('participant.submissions.file', [$submission, $file]);
        $this->get($url)->assertRedirect(route('login'));
        $this->actingAs($submission->user)->get(route('participant.submissions.show', $submission))
            ->assertOk()->assertSee('My abstract.pdf')->assertSee($url, false);
        $response = $this->get($url)->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('inline;', $response->headers->get('Content-Disposition'));
        $this->get($url.'?download=1')->assertDownload('My abstract.pdf');
        $this->actingAs(User::factory()->create())->get($url)->assertNotFound();
        $other = Submission::factory()->for($submission->conference)->create();
        $this->actingAs($other->user)->get(route('participant.submissions.file', [$other, $file]))->assertNotFound();
        Storage::delete('abstract.pdf');
        $this->actingAs($submission->user)->get($url)->assertNotFound();
    }

    public function test_legacy_abstract_can_be_downloaded_without_file_record(): void
    {
        Storage::fake();
        $submission = Submission::factory()->for(Conference::where('slug', 'icleh-2026')->firstOrFail())
            ->create(['abstract_file' => 'legacy.doc']);
        Storage::put('legacy.doc', 'Legacy document');
        $this->actingAs($submission->user)->get(route('participant.submissions.file', $submission))
            ->assertDownload('legacy.doc');
    }
}
