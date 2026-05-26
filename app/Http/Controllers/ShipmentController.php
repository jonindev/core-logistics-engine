<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShipmentController extends Controller
{
    /**
     * Display a listing of shipments with optimized queries.
     */
    public function index(Request $request): View
    {
        $query = Shipment::with('carrier')->latest();

        // Filtro dinámico sobre JSONB metadata
        if ($request->has('status')) {
            $query->where('metadata->status', $request->status);
        }

        if ($request->has('q')) {
            $q = $request->q;
            $query->where(function ($query) use ($q) {
                $query->where('tracking_number', 'LIKE', "%{$q}%")
                      ->orWhere('metadata->customer_name', 'LIKE', "%{$q}%");
            });
        }

        $shipments = $query->paginate(20);

        return view('shipments.index', compact('shipments'));
    }
}
