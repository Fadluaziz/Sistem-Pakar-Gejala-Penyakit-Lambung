<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HttpsProxyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Saat request datang lewat proxy/tunnel https, header X-Forwarded-Proto
     * harus dipercaya sehingga URL yang di-generate memakai https.
     */
    public function test_url_memakai_https_saat_header_proxy_https(): void
    {
        $response = $this->withHeaders(['X-Forwarded-Proto' => 'https'])->get('/');

        $location = $response->headers->get('Location');

        $this->assertNotNull($location);
        $this->assertStringStartsWith('https://', $location);
    }

    /**
     * Tanpa header proxy, pemakaian lokal lewat http tetap berjalan normal.
     */
    public function test_url_tetap_http_tanpa_header_proxy(): void
    {
        $response = $this->get('/');

        $location = $response->headers->get('Location');

        $this->assertNotNull($location);
        $this->assertStringStartsWith('http://', $location);
    }
}
