<?php

namespace Tests\Feature;

use Tests\TestCase;

class LandingPageTest extends TestCase
{
    public function test_landing_page_renders(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Laporku');
        $response->assertSee('images/landing/hero-neighborhood.jpg', false);
        $response->assertSee('nav-cta', false);
    }

    public function test_login_page_renders(): void
    {
        $this->get('/login')->assertOk()->assertSee('Masuk');
    }

    public function test_register_page_renders(): void
    {
        $this->get('/register')->assertOk()->assertSee('Daftar');
    }
}
