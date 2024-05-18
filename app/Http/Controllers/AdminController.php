<?php

namespace App\Http\Controllers;
use App\Models\Car;
use App\Models\Order;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use App\Jobs\SendBuyerOrderNotifications;
use App\Models\Buyer;
use App\Models\ShippingLocation;
use App\Models\ShippingMethod;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class AdminController extends Controller
{

    /**
     * Display the admin dashboard with summary statistics.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function adminDashboard()
    {
        $totalCars = Car::count();
        $totalOrders = Order::count();
        $totalTransactions = Order::sum('total_amount');
        $totalBuyers = Buyer::count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_cars' => $totalCars,
                'total_orders' => $totalOrders,
                'total_transactions' => $totalTransactions,
                'total_buyers' => $totalBuyers,
            ],
        ], 200);
    }

    /**
     * Update an admin
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function adminUpdate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:admins,email,'.$id,
            'password' => 'sometimes|required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $admin = Admin::findOrFail($id);
        $admin->name = $request->input('name', $admin->name);
        $admin->email = $request->input('email', $admin->email);
        $admin->password = $request->input('password') ? Hash::make($request->input('password')) : $admin->password;
        $admin->save();

        return response()->json([
            'success' => true,
            'message' => 'Admin updated successfully',
            'admin' => $admin,
        ], 200);
    }

    /**
     * Add a new car
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addCar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'brand' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'colors' => 'required|array',
            'pictures' => 'required|array',
            'year' => 'required|integer|min:1900|max:2100',
            'price' => 'required|numeric|min:0',
            'customs_price' => 'required|numeric|min:0',
            'available_quantity' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $car = new Car();
        $car->brand = $request->input('brand');
        $car->title = $request->input('title');
        $car->description = $request->input('description');
        $car->colors = json_encode($request->input('colors'));
        $car->pictures = json_encode($request->input('pictures'));
        $car->year = $request->input('year');
        $car->price = $request->input('price');
        $car->customs_price = $request->input('customs_price');
        $car->available_quantity = $request->input('available_quantity');
        $car->save();

        return response()->json([
            'success' => true,
            'message' => 'Car added successfully',
            'car' => $car,
        ], 201);
    }

    /**
     * View all cars
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function viewAllCars()
    {
        $cars = Car::all();

        return response()->json([
            'success' => true,
            'cars' => $cars,
        ], 200);
    }

    /**
     * Update a car
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCar(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'brand' => 'sometimes|required|string|max:255',
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'colors' => 'sometimes|required|array',
            'pictures' => 'sometimes|required|array',
            'year' => 'sometimes|required|integer|min:1900|max:2100',
            'price' => 'sometimes|required|numeric|min:0',
            'customs_price' => 'sometimes|required|numeric|min:0',
            'available_quantity' => 'sometimes|required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $car = Car::findOrFail($id);
        $car->brand = $request->input('brand', $car->brand);
        $car->title = $request->input('title', $car->title);
        $car->description = $request->input('description', $car->description);
        $car->colors = json_encode($request->input('colors', json_decode($car->colors, true)));
        $car->pictures = json_encode($request->input('pictures', json_decode($car->pictures, true)));
        $car->year = $request->input('year', $car->year);
        $car->price = $request->input('price', $car->price);
        $car->customs_price = $request->input('customs_price', $car->customs_price);
        $car->available_quantity = $request->input('available_quantity', $car->available_quantity);
        $car->save();

        return response()->json([
            'success' => true,
            'message' => 'Car updated successfully',
            'car' => $car,
        ], 200);
    }

    /**
     * Delete a car
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteCar($id)
    {
        $car = Car::findOrFail($id);
        $car->delete();

        return response()->json([
            'success' => true,
            'message' => 'Car deleted successfully',
        ], 200);
    }


    /**
     * View all buyers
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function viewAllBuyers()
    {
        $buyers = Buyer::all();

        return response()->json([
            'success' => true,
            'buyers' => $buyers,
        ], 200);
    }

    /**
     * Delete a buyer
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteBuyer($id)
    {
        $buyer = Buyer::findOrFail($id);
        $buyer->delete();

        return response()->json([
            'success' => true,
            'message' => 'Buyer deleted successfully',
        ], 200);
    }

    /**
     * Update a buyer
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateBuyer(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:buyers,email,'.$id,
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
        $buyer->name = $request->input('name', $buyer->name);
        $buyer->email = $request->input('email', $buyer->email);
        $buyer->password = $request->input('password') ? bcrypt($request->input('password')) : $buyer->password;
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
 * Update the status of an order
 *
 * @param \Illuminate\Http\Request $request
 * @param int $id
 * @return \Illuminate\Http\JsonResponse
 */
public function updateOrderStatus(Request $request, $id)
{
    $validator = Validator::make($request->all(), [
        'status' => 'required|string|in:Pending,Processing,Shipped,Delivered,Cancelled',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors(),
        ], 422);
    }

    $order = Order::findOrFail($id);
    $order->status = $request->input('status');
    $order->save();

    SendBuyerOrderNotifications::dispatch($order);

    return response()->json([
        'success' => true,
        'message' => 'Order status updated successfully',
        'order' => $order,
    ], 200);
}
   /**
 * View all orders
 *
 * @return \Illuminate\Http\JsonResponse
 */
public function viewAllOrders()
{
    $orders = Order::with(['buyer:id,name', 'car:id,brand,title'])
        ->select('id', 'payment_reference', 'car_vin', 'total_amount', 'buyer_id', 'car_id', 'status')
        ->get();

    $orders->transform(function ($order) {
        return [
            'id' => $order->id,
            'payment_reference' => $order->payment_reference,
            'car_vin' => $order->car_vin,
            'total_amount' => $order->total_amount,
            'car_name' => $order->car->brand . ' ' . $order->car->title,
            'buyer_name' => $order->buyer->name,
            'status' => $order->status
        ];
    });

    return response()->json([
        'success' => true,
        'orders' => $orders,
    ], 200);
}

    /**
     * Delete an order
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteOrder($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();

        return response()->json([
            'success' => true,
            'message' => 'Order deleted successfully',
        ], 200);
    }

    /**
     * Update an order
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateOrder(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'buyer_id' => 'sometimes|required|exists:buyers,id',
            'car_id' => 'sometimes|required|exists:cars,id',
            'total_amount' => 'sometimes|required|numeric|min:0',
            'car_vin' => 'sometimes|required|string|max:255',
            'payment_reference' => 'sometimes|required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $order = Order::findOrFail($id);
        $order->buyer_id = $request->input('buyer_id', $order->buyer_id);
        $order->car_id = $request->input('car_id', $order->car_id);
        $order->total_amount = $request->input('total_amount', $order->total_amount);
        $order->car_vin = $request->input('car_vin', $order->car_vin);
        $order->payment_reference = $request->input('payment_reference', $order->payment_reference);
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Order updated successfully',
            'order' => $order,
        ], 200);
    }

    /**
     * View all payments
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function viewAllPayments()
    {
        $orders = Order::with('buyer', 'car')->get();

        return response()->json([
            'success' => true,
            'payments' => $orders,
        ], 200);
    }

    /**
     * Add a new shipping location
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addShippingLocation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $shippingLocation = ShippingLocation::create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Shipping location added successfully',
            'shipping_location' => $shippingLocation,
        ], 201);
    }

    /**
     * View all shipping locations
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function viewAllShippingLocations()
    {
        $shippingLocations = ShippingLocation::all();

        return response()->json([
            'success' => true,
            'shipping_locations' => $shippingLocations,
        ], 200);
    }

    /**
     * Delete a shipping location
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteShippingLocation($id)
    {
        $shippingLocation = ShippingLocation::findOrFail($id);
        $shippingLocation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Shipping location deleted successfully',
        ], 200);
    }

    /**
     * Update a shipping location
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateShippingLocation(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $shippingLocation = ShippingLocation::findOrFail($id);
        $shippingLocation->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Shipping location updated successfully',
            'shipping_location' => $shippingLocation,
        ], 200);
    }

    /**
     * Add a new shipping method
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addShippingMethod(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'shipping_location_id' => 'required|exists:shipping_locations,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $shippingMethod = ShippingMethod::create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Shipping method added successfully',
            'shipping_method' => $shippingMethod,
        ], 201);
    }

    /**
     * View all shipping methods
     *
     * @param int $locationId
     * @return \Illuminate\Http\JsonResponse
     */
    public function viewAllShippingMethods($locationId = null)
    {
        $shippingMethods = $locationId
            ? ShippingMethod::where('shipping_location_id', $locationId)->get()
            : ShippingMethod::with('shippingLocation')->get();

        return response()->json([
            'success' => true,
            'shipping_methods' => $shippingMethods,
        ], 200);
    }

    /**
     * Delete a shipping method
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteShippingMethod($id)
    {
        $shippingMethod = ShippingMethod::findOrFail($id);
        $shippingMethod->delete();

        return response()->json([
            'success' => true,
            'message' => 'Shipping method deleted successfully',
        ], 200);
    }

    /**
     * Update a shipping method
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateShippingMethod(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|numeric|min:0',
            'shipping_location_id' => 'sometimes|required|exists:shipping_locations,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $shippingMethod = ShippingMethod::findOrFail($id);
        $shippingMethod->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Shipping method updated successfully',
            'shipping_method' => $shippingMethod,
        ], 200);
    }
}
