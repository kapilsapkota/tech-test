<?php

namespace Tests\Feature;

use App\Models\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ApplicationApiTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
    public function testPaginatedApplicationList()
    {
        Application::factory()->count(15)->create();

        $response = $this->getJson('/api/applications');

        $response->assertOk();

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'customer_full_name',
                    'address',
                    'plan_type',
                    'plan_name',
                    'state',
                    'plan_monthly_cost',
                    'order_id',
                ]
            ],
            'links' => [
                'first',
                'last',
                'prev',
                'next',
            ],
            'meta' => [
                'current_page',
                'from',
                'last_page',
                'path',
                'per_page',
                'to',
                'total',
            ],
        ]);

        $response->assertJsonCount(10, 'data');

        $response->assertJson([
            'data' => Application::orderBy('created_at', 'asc')->take(10)->get()->toArray(),
        ]);

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'plan_monthly_cost',
                ],
            ],
        ]);
        $responseData = $response->json('data');
        foreach ($responseData as $application) {
            $this->assertRegExp('/^\d+\.\d{2}$/', $application['plan_monthly_cost']);
        }
    }
}
