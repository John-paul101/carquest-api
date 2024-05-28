<?php

namespace App\Http\Controllers;
use App\Models\Car;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use App\Models\Buyer;
use App\Jobs\SendBuyerOrderNotifications;
use App\Models\ShippingLocation;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class BuyerController extends Controller
{
    /**
 * Display the buyer dashboard with summary statistics.
 *
 * @return \Illuminate\Http\JsonResponse
 */
public function buyerDashboard()
{
    $buyer = Auth::user();
    $orders = $buyer->orders;

    $totalOrders = $orders->count();
    $deliveredOrders = $orders->where('status', 'Delivered')->count();
    $canceledOrders = $orders->where('status', 'Cancelled')->count();

    $totalAmountSpent = $orders->sum('total_amount');

    return response()->json([
        'success' => true,
        'data' => [
            'total_orders' => $totalOrders,
            'delivered_orders' => $deliveredOrders,
            'canceled_orders' => $canceledOrders,
            'total_amount_spent' => $totalAmountSpent,
        ],
    ], 200);
}
     /**
     * Get all available cars
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllCars()
    {
        $cars = Car::all();

        return response()->json([
            'success' => true,
            'cars' => $cars,
        ], 200);
    }

    /**
     * Get two featured cars randomly
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFeaturedCars()
    {
        $featuredCars = Car::inRandomOrder()->take(2)->get();

        return response()->json([
            'success' => true,
            'featured_cars' => $featuredCars,
        ], 200);
    }

    /**
     * Get details of a specific car
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCarDetails($id)
    {
        $car = Car::findOrFail($id);

        return response()->json([
            'success' => true,
            'car' => $car,
        ], 200);
    }

    /**
     * Create a new order
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cars' => 'required|array',
            'cars.*.id' => 'required|exists:cars,id',
            'cars.*.price' => 'required|numeric',
            'shipping_location_id' => 'required|exists:shipping_locations,id',
            'shipping_method_id' => 'required|exists:shipping_methods,id',
            'total_order' => 'required|numeric',
            'payment_ref' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $buyer = Auth::user();
        $orders = [];

        foreach ($request->input('cars') as $carData) {
            $car = Car::findOrFail($carData['id']);

            // Check if the car has available quantity
            if ($car->available_quantity <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Car is out of stock',
                ], 400);
            }

            $order = new Order();
            $order->car_id = $car->id;
            $order->buyer_id = $buyer->id;
            $order->total_amount = $carData['price'];
            $order->car_vin = $this->generateVIN();
            $order->payment_reference = $request->input('payment_ref');
            $order->shipping_location_id = $request->input('shipping_location_id');
            $order->shipping_method_id = $request->input('shipping_method_id');
            $order->save();

            // Reduce the available quantity of the car
            $car->available_quantity -= 1;
            $car->save();

            $orders[] = $order->load('buyer', 'car', 'shippingLocation', 'shippingMethod');
        }

        SendBuyerOrderNotifications::dispatch($order, true);

        return response()->json([
            'success' => true,
            'message' => 'Orders created successfully',
            'orders' => $orders,
        ], 201);
    }


    /**
 * Get all orders placed by the buyer
 *
 * @return \Illuminate\Http\JsonResponse
 */
public function getAllOrders()
{
    $buyer = Auth::user();
    $orders = Order::where('buyer_id', $buyer->id)
        ->with('car:id,brand,title')
        ->select('id', 'payment_reference', 'car_vin', 'total_amount', 'car_id', 'status')
        ->get();

    $orders->transform(function ($order) {
        return [
            'id' => $order->id,
            'payment_reference' => $order->payment_reference,
            'car_vin' => $order->car_vin,
            'total_amount' => $order->total_amount,
            'car_name' => $order->car->brand . ' ' . $order->car->title,
            'status' => $order->status
        ];
    });

    return response()->json([
        'success' => true,
        'orders' => $orders,
    ], 200);
}

    /**
     * Get details of a specific order
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrderDetails($id)
    {
        $order = Order::where('buyer_id', Auth::user()->id)
                    ->with('car')
                    ->findOrFail($id);

        return response()->json([
            'success' => true,
            'order' => $order,
        ], 200);
    }

     /**
     * Update a buyer
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function buyerUpdate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:buyers,email,' . $id,
            'password' => 'sometimes|required|string|min:6',
            'address' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $buyer = Buyer::findOrFail($id);

        // Check if the authenticated buyer is trying to update their own profile
        if ($buyer->id !== auth()->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to update this buyer\'s profile.',
            ], 403);
        }

        $buyer->name = $request->input('name', $buyer->name);
        $buyer->email = $request->input('email', $buyer->email);
        $buyer->password = $request->input('password') ? Hash::make($request->input('password')) : $buyer->password;
        $buyer->address = $request->input('address', $buyer->address);
        $buyer->phone = $request->input('phone', $buyer->phone);
        $buyer->save();

        return response()->json([
            'success' => true,
            'message' => 'Buyer updated successfully',
            'buyer' => $buyer,
        ], 200);
    }


    /**
     * Generate a random VIN (Vehicle Identification Number)
     *
     * @return string
     */
    private function generateVIN()
    {
        $vin = '';
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        for ($i = 0; $i < 17; $i++) {
            $vin .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $vin;
    }
}
