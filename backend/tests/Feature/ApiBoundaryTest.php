<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiBoundaryTest extends TestCase
{
    public function test_allowed_preflight_works_without_session_or_csrf()
    {
        $this->withHeaders(['Origin' => 'http://localhost:8080', 'Access-Control-Request-Method' => 'POST',
            'Access-Control-Request-Headers' => 'content-type,x-xsrf-token'])
            ->options('/api/v1/auth/login')->assertNoContent()
            ->assertHeader('Access-Control-Allow-Origin', 'http://localhost:8080')
            ->assertHeader('Access-Control-Allow-Credentials', 'true');
    }

    public function test_disallowed_origin_and_preflight_headers_are_rejected()
    {
        $this->withHeaders(['Origin' => 'https://untrusted.example'])->get('/api/v1/auth/csrf')
            ->assertForbidden()->assertHeaderMissing('Access-Control-Allow-Origin');
        $this->withHeaders(['Origin' => 'http://localhost:8080', 'Access-Control-Request-Method' => 'POST',
            'Access-Control-Request-Headers' => 'x-unexpected'])->options('/api/v1/auth/login')->assertForbidden();
    }

    public function test_auth_errors_include_cors_headers_and_no_redirect()
    {
        $this->withHeaders(['Origin' => 'http://localhost:8080'])->get('/api/v1/auth/session')
            ->assertUnauthorized()->assertHeader('Access-Control-Allow-Origin', 'http://localhost:8080')
            ->assertJsonStructure(['message'])->assertHeaderMissing('Location');
    }

    public function test_real_csrf_check_rejects_missing_token_and_accepts_matching_token()
    {
        $this->app['env'] = 'local';
        $this->post('/api/v1/auth/login', [])->assertStatus(419);
        $this->withHeaders(['X-XSRF-TOKEN' => 'invalid-encrypted-token'])->post('/api/v1/auth/login', [])->assertStatus(419);
        $this->withSession(['_token' => 'test-token'])->post('/api/v1/auth/login', ['_token' => 'test-token'])
            ->assertStatus(422)->assertJsonValidationErrors(['email', 'password']);
    }
}
