<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessWebhookJob;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WebhookController extends Controller
{
    /**
     * Handle the incoming webhook from a carrier.
     */
    public function handle(Request $request, string $carrierCode): JsonResponse
    {
        if (!$request->all()) {
            return response()->json(['error' => 'Empty payload'], 400);
        }

        ProcessWebhookJob::dispatch($carrierCode, $request->all());

        return response()->json([
            'status' => 'accepted',
            'message' => 'Webhook received and queued for processing'
        ], 202);
    }
}
