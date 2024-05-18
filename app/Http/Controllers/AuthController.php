<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use App\Models\Admin;
use App\Models\Buyer;
use Tymon\JWTAuth\Facades\JWTAuth;


class AuthController extends Controller
{

    /**
 * Buyer login
 *
 * @param \Illuminate\Http\Request $request
 * @return \Illuminate\Http\JsonResponse
 */
public function buyerLogin(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email' => 'required|exists:buyers,email',
        'password' => 'required|string|min:6',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials',
            'errors' => $validator->errors(),
        ], 422);
    }

    $credentials = $request->only('email', 'password');

    if (Auth::guard('buyer')->attempt($credentials)) {
        $buyer = Auth::guard('buyer')->user();
        $token = $this->generateBuyerToken($buyer);

        return response()->json([
            'success' => true,
            'message' => 'Buyer logged in successfully',
            'token' => $token,
            'role' => 'buyer',
            'buyer' => $buyer,
        ], 200);
    } else {
        $buyer = $request->input('buyer');
        $buyer = Buyer::where('email', $buyer)->first();

        if (!$buyer) {
            return response()->json([
                'success' => false,
                'message' => 'Buyer does not exist',
            ], 401);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Incorrect password',
            ], 401);
        }
    }
}

/**
 * Buyer registration
 *
 * @param \Illuminate\Http\Request $request
 * @return \Illuminate\Http\JsonResponse
 */
public function buyerRegister(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'email' => 'required|unique:buyers,email',
        'password' => 'required|string|min:6',
        'nin' => 'required|numeric|digits:11|unique:buyers,nin',
        'address' => 'required|string|max:255',
        'phone' => 'required|string|max:255|unique:buyers,phone',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors(),
        ], 422);
    }


    $buyer = new Buyer();
    $buyer->name = $request->input('name');
    $buyer->email = $request->input('email');
    $buyer->password = Hash::make($request->input('password'));
    $buyer->nin = $request->input('nin');
    $buyer->address = $request->input('address');
    $buyer->phone = $request->input('phone');
    $buyer->save();

    $token = $this->generateBuyerToken($buyer);

    return response()->json([
        'success' => true,
        'message' => 'Buyer registered successfully',
        'token' => $token,
        'buyer' => $buyer,
    ], 200);
}

 /**
     * Buyer logout
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function buyerLogout(Request $request)
    {
        $buyer = Auth::guard('buyer')->user();

        if ($buyer) {
            Auth::guard('buyer')->logout();

            return response()->json([
                'success' => true,
                'message' => 'Buyer logged out successfully',
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }
    }



       /**
 * Admin login
 *
 * @param \Illuminate\Http\Request $request
 * @return \Illuminate\Http\JsonResponse
 */
public function adminLogin(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
        'password' => 'required|string|min:6',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials',
            'errors' => $validator->errors(),
        ], 422);
    }

    $credentials = $request->only('email', 'password');

    if (Auth::guard('admin')->attempt($credentials)) {
        $admin = Auth::guard('admin')->user();
        $token = $this->generateAdminToken($admin);

        return response()->json([
            'success' => true,
            'role' => 'admin',
            'message' => 'Admin logged in successfully',
            'token' => $token,
            'admin' => $admin,
        ], 200);
    } else {
        $email = $request->input('email');
        $admin = Admin::where('email', $email)->first();

        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Email does not exist',
            ], 401);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Incorrect password',
            ], 401);
        }
    }
}


    /**
     * Admin logout
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function adminLogout(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        if ($admin) {
            Auth::guard('admin')->logout();

            return response()->json([
                'success' => true,
                'message' => 'Admin logged out successfully',
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }
    }

 /**
     * Generate JWT token for the admin user
     *
     * @param  \App\Models\Admin  $admin
     * @return string
     */
    private function generateAdminToken(Admin $admin)
    {
        $token = JWTAuth::fromUser($admin);
        return $token;
    }


 /**
     * Generate JWT token for the buyer user
     *
     * @param  \App\Models\Buyer  $buyer
     * @return string
     */
    private function generateBuyerToken(Buyer $buyer)
    {
        $token = JWTAuth::fromUser($buyer);
        return $token;
    }

}
