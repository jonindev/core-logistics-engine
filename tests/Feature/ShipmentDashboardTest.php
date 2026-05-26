<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Shipment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShipmentDashboardTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_renders_shipments_dashboard()
    {
        // We must be authenticated to access /shipments
        $user = \App\Models\User::factory()->create();
        
        $carrier = Carrier::create(['name' => 'FedEx', 'code' => 'fedex']);
        Shipment::create([
            'carrier_id' => $carrier->id,
            'tracking_number' => 'TRK123',
            'metadata' => ['status' => 'pending', 'customer_name' => 'John Doe']
        ]);

        $response = $this->actingAs($user)->get('/shipments');

        $response->assertStatus(200)
                 ->assertSee('TRK123')
                 ->assertSee('FedEx')
                 ->assertSee('John Doe');
    }

    /** @test */
    public function it_filters_shipments_by_status()
    {
        $user = \App\Models\User::factory()->create();
        $carrier = Carrier::create(['name' => 'FedEx', 'code' => 'fedex']);
        
        Shipment::create([
            'carrier_id' => $carrier->id,
            'tracking_number' => 'T1',
            'metadata' => ['status' => 'delivered', 'customer_name' => 'C1']
        ]);
        Shipment::create([
            'carrier_id' => $carrier->id,
            'tracking_number' => 'T2',
            'metadata' => ['status' => 'pending', 'customer_name' => 'C2']
        ]);

        $response = $this->actingAs($user)->get('/shipments?status=delivered');

        $response->assertSee('T1');
        $response->assertDontSee('T2');
    }
}
