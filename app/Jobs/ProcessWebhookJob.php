<?php

namespace App\Jobs;

use App\Models\Carrier;
use App\Models\Shipment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900];

    protected string $carrierCode;
    protected array $payload;

    public function __construct(string $carrierCode, array $payload)
    {
        $this->carrierCode = $carrierCode;
        $this->payload = $payload;
    }

    public function handle(): void
    {
        try {
            $carrier = Carrier::firstOrCreate(
                ['code' => $this->carrierCode],
                ['name' => 'Carrier ' . ucfirst($this->carrierCode)]
            );

            $trackingNumber = $this->payload['tracking_number'] ?? null;

            if (!$trackingNumber) {
                Log::warning("Webhook payload missing tracking number for carrier {$this->carrierCode}", $this->payload);
                return;
            }

            Shipment::updateOrCreate(
                [
                    'tracking_number' => $trackingNumber,
                    'carrier_id' => $carrier->id
                ],
                [
                    'metadata' => $this->payload
                ]
            );

        } catch (\Exception $e) {
            Log::error("Error processing webhook for carrier {$this->carrierCode}: " . $e->getMessage());
            throw $e;
        }
    }
}
