<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SpeakerPhotoControllerTest extends TestCase
{
    public function test_serves_speaker_photo_from_public_storage(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('speakers/stefan-koos.jpg', 'speaker image');

        $this->get('/storage/speakers/stefan-koos.jpg')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_returns_not_found_for_missing_speaker_photo(): void
    {
        Storage::fake('public');

        $this->get('/storage/speakers/missing-speaker.jpg')
            ->assertNotFound();
    }

    public function test_returns_not_found_for_non_image_speaker_file(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('speakers/readme.txt', 'not an image');

        $this->get('/storage/speakers/readme.txt')
            ->assertNotFound();
    }
}
