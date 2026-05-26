<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use App\Jobs\ProcessWebhookJob;
use Tests\TestCase;

class WebhookTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_accepts_webhook_and_dispatches_job()
    {
        Queue::fake();

        $response = $this->postJson('/api/webhooks/fedex', [
            'tracking_number' => 'TEST123',
            'status' => 'pending',
            'customer_name' => 'Test User'
        ]);

        $response->assertStatus(202)
                 ->assertJson(['status' => 'accepted']);

        Queue::assertPushed(ProcessWebhookJob::class, function ($job) {
            return $job->carrierCode === 'fedex';
        });
    }

    /** @test */
    public function it_returns_400_for_empty_payload()
    {
        $response = $this->postJson('/api/webhooks/fedex', []);

        $response->assertStatus(400)
                 ->assertJson(['error' => 'Empty payload']);
    }
}
