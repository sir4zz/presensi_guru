<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_root_redirects_to_the_guru_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('guru.login'));
    }
}
