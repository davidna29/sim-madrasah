<?php

namespace Tests\Feature\Dashboard;

use Tests\TestCase;

class SuperAdminDashboardSummaryTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
