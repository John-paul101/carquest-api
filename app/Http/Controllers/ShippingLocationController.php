<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShippingLocation;
use App\Models\ShippingMethod;

class ShippingLocationController extends Controller
{
    /**
     * Get all available shipping locations
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $shippingLocations = ShippingLocation::all();

        return response()->json([
            'success' => true,
            'shipping_locations' => $shippingLocations,
        ], 200);
    }

    /**
     * Get all available shipping methods for a location
     *
     * @param int $locationId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getShippingMethodsByLocation($locationId)
    {
        $shippingMethods = ShippingMethod::where('shipping_location_id', $locationId)->get();

        return response()->json([
            'success' => true,
            'shipping_methods' => $shippingMethods,
        ], 200);
    }

    /**
     * Get all available shipping methods
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllShippingMethods()
    {
        $shippingMethods = ShippingMethod::with('shippingLocation')->get();

        return response()->json([
            'success' => true,
            'shipping_methods' => $shippingMethods,
        ], 200);
    }

    /**
     * Get the shipping price for a location and method
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getShippingPrice(Request $request)
    {
        $locationId = $request->input('location_id');
        $methodName = $request->input('method_name');

        $shippingMethod = ShippingMethod::where('shipping_location_id', $locationId)
            ->where('name', $methodName)
            ->first();

        if (!$shippingMethod) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid shipping location or method',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'shipping_price' => $shippingMethod->price,
        ], 200);
    }
}
