<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicPageTest extends TestCase
{
    public function test_home_page_renders_conference_content_and_submission_dates(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('5th ICLEH 2026')
            ->assertSee('Conference Agenda')
            ->assertSee('Abstract Submission')
            ->assertSee('14 Sep')
            ->assertSee('Gedung Pemuda Fakultas Hukum UNTAG Semarang')
            ->assertSee(route('participant.submissions.create'));
    }

    public function test_venue_page_renders_successfully(): void
    {
        $this->get('/venue')
            ->assertOk()
            ->assertSee('Gedung Pemuda Fakultas Hukum UNTAG Semarang')
            ->assertSee('Hybrid conference venue for plenary and parallel sessions.');
    }
}
