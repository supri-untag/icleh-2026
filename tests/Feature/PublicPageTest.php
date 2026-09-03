<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicPageTest extends TestCase
{
    public function test_venue_page_renders_successfully(): void
    {
        $this->get('/venue')
            ->assertOk()
            ->assertSee('Gedung Pemuda Fakultas Hukum UNTAG Semarang')
            ->assertSee('Hybrid conference venue for plenary and parallel sessions.');
    }
}
