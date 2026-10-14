<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_csrf_initialization_issues_cookie()
    {
        $this->get('/api/v1/auth/csrf')->assertNoContent()->assertCookie('XSRF-TOKEN');
    }
}
