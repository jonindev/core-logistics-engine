<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Shipment;
use App\Jobs\ProcessWebhookJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcessWebhookJobTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_processes_webhook_and_creates_shipment()
    {
        $payload = [
            'tracking_number' => 'TRK123',
            'status' => 'pending',
            'customer_name' => 'John Doe'
        ];

        $job = new ProcessWebhookJob('fedex', $payload);
        $job->handle();

        $this->assertDatabaseHas('carriers', ['code' => 'fedex']);
        $this->assertDatabaseHas('shipments', [
            'tracking_number' => 'TRK123',
            'status' => 'pending' // This checks the JSONB column
        ]);
        
        $shipment = Shipment::where('tracking_number', 'TRK123')->first();
        $this->assertEquals('John Doe', $shipment->metadata['customer_name']);
    }

    /** @test */
    public function it_updates_existing_shipment_instead_of_creating_new_one()
    {
        $carrier = Carrier::create(['name' => 'FedEx', 'code' => 'fedex']);
        Shipment::create([
            'carrier_id' => $carrier->id,
            'tracking_number' => 'TRK123',
            'metadata' => ['status' => 'pending', 'customer_name' => 'John Doe']
        ]);

        $newPayload = [
            'tracking_number' => 'TRK123',
            'status' => 'delivered',
            'customer_name' => 'John Doe'
        ];

        $job = new ProcessWebhookJob('fedex', $newPayload);
        $job->handle();

        $this->assertEquals(1, Shipment::count());
        $this->assertDatabaseHas('shipments', ['tracking_number' => 'TRK123']);
        
        $shipment = Shipment::where('tracking_number', 'TRK123')->first();
        $this->assertEquals('delivered', $shipment->metadata['status']);
    }
}
