<?php

namespace App\Http\Controllers;

use App\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Midtrans\Config;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->input('user_id');
        $orders = Order::query();

        $orders->when($userId, function ($query) use ($userId) {
            return $query->where('user_id', '=', $userId);
        });
        return response()->json([
            'status' => 'success',
            'data' => $orders->get()
        ]);
    }
    public function create(Request $request)
    {
        $user = $request->input('user');
        $course = $request->input('course');
        $order = Order::create([
            'user_id' => $user['id'],
            'course_id' => $course['id']
        ]);
        $transactionDetails = [
            'order_id' => $order->id . '-' . Str::random(5),
            'gross_amount' => $course['price']
        ];
        $itemDetails = [
            [
                'id' => $course['id'],
                'price' => $course['price'],
                'quantity' => 1,
                'name' => $course['name'],
                'brand' => 'BuildWithUlza',
                'category' => 'Online Course',
                'merchant_name' => "Ulza"
            ]
        ];

        $customerDetails = [
            'first_name' => $user['name'],
            'email' => $user['email']
        ];

        $midtransParams = [
            'transaction_details' => $transactionDetails,
            'item_details' => $itemDetails,
            'customer_details' => $customerDetails
        ];
        $midtransSnapUrl = $this->getMidtransSnapUrl($midtransParams);
        $order->snap_url = $midtransSnapUrl;
        $order->metadata = [
            'course_id' => $course['id'],
            'course_price' => $course['price'],
            'course_name' => $course['name'],
            'course_thumbnail' => $course['thumbnail'],
            'course_level' => $course['level']
        ];
        $order->save();
        return response()->json([
            'status' => 'success',
            'data' => $order
        ]);
    }

    // private function getMidtransSnapUrl($params)
    // {
    //     // Set your Merchant Server Key
    //     Config::$serverKey = env('MIDTRANS_SERVER_KEY');
    //     // Set to Development/Sandbox Environment (default). Set to true for Production Environment (accept real transaction).
    //     Config::$isProduction = (bool) env('MIDTRANS_PRODUCTION');
    //     // Set sanitization on (default)
    //     Config::$isSanitized = (bool) env('MIDTRANS_SANITIZED');
    //     // Set 3DS transaction for credit card to true
    //     Config::$is3ds = (bool) env('MIDTRANS_3DS');

    //     $snapUrl = \Midtrans\Snap::createTransaction($params)->redirect_url;

    //     return $snapUrl;
    // }

    private function getMidtransSnapUrl($params)
    {
        \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        \Midtrans\Config::$isProduction = (bool) env('MIDTRANS_PRODUCTION');
        \Midtrans\Config::$is3ds = (bool) env('MIDTRANS_3DS');

        $snapUrl = \Midtrans\Snap::createTransaction($params)->redirect_url;
        return $snapUrl;
    }
}
