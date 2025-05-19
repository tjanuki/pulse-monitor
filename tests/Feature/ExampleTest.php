<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        // The root route is now the dashboard, which needs the database
        // Ensure the migration has run before accessing the route
        $this->artisan('migrate');
        
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
