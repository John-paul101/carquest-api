<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendBuyerOrderNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;
    protected $isNewOrder;

    public function __construct(Order $order, $isNewOrder = false)
    {
        $this->order = $order;
        $this->isNewOrder = $isNewOrder;
    }

    public function handle()
    {
        // Get the buyer for the order
        $buyer = $this->order->buyer;
        Log::info('Retrieved buyer: ' . $buyer->name);

        // Determine the notification message based on the order status or if it's a new order
        if ($this->isNewOrder) {
            $message = "Hi {$buyer->name}, a new Car with VIN {$this->order->car_vin} and (Order #{$this->order->id}) has been created for you. Your order is currently in the {$this->order->status} status.";
        } else {
            $message = "Hi {$buyer->name}, your Car with VIN {$this->order->car_vin} and (Order #{$this->order->id}) status has been updated to {$this->order->status}.";
        }

        // Send notification to the buyer
        $this->sendNotification($buyer->phone, $message);
    }

    private function sendNotification($phoneNumber, $message)
    {
        try {
            // Set the API endpoint URL
            $url = 'https://smsclone.com/api/sms/sendsms';

            // Set the API parameters
            $params = [
                'username' => 'remindme',
                'password' => 'mydzaf-dakbyg-0foxsY',
                'sender' => 'REMINDME',
                'recipient' => $phoneNumber,
                'message' => $message,
            ];

            // Make the API request
            $response = Http::get($url, $params);

            // Check the response status
            if ($response->successful()) {
                // Handle successful response
                $responseData = $response->json();
                Log::info('SMS notification sent successfully.', [
                    'phone_number' => $phoneNumber,
                    'message' => $message,
                    'response' => $responseData,
                ]);
                // Process the response data as needed
            } else {
                // Handle error response
                $errorMessage = $response->body();
                Log::error('Failed to send SMS notification.', [
                    'phone_number' => $phoneNumber,
                    'message' => $message,
                    'error' => $errorMessage,
                ]);
                // Log the error or take appropriate action
            }
        } catch (\Exception $e) {
            // Handle and log any exceptions
            Log::error('Exception occurred while sending SMS notification.', [
                'phone_number' => $phoneNumber,
                'message' => $message,
                'exception' => $e->getMessage(),
            ]);
            // You can also choose to re-throw the exception if needed
            // throw $e;
        }
    }
}
