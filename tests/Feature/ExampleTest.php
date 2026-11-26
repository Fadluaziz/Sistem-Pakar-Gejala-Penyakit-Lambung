<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_tamu_di_halaman_utama_diarahkan_ke_login(): void
    {
        $response = $this->get('/');

        // Rute '/' memang me-redirect tamu ke halaman masuk (lihat routes/web.php).
        $response->assertRedirect(route('login'));
    }
}
