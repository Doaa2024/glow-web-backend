<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\User;
use App\Models\Cart;
class OrderController extends Controller
{
        public function getAllOrders()
        {
             $orders = Order::paginate(10); // eager load relationship
            return response()->json($orders);
        }
        public function getOrderByID($id)
        {
            $order = Order::find($id)->load('orderDetails'); // eager load relationship
            if ($order) {
                return response()->json($order);
            } else {
                return response()->json(['message' => 'Order not found'], 404);
            }
        }  
        public function createOrder(Request $request)
        {
            $user_id = auth()->user()->id;
            $validated = $request->validate([
                'total_price' => 'required|numeric',
                'full_name' => 'required|string',
                'email' => 'required|email',
             'phone' => ['required', 'regex:/^[0-9+\-\s]{8,15}$/'],
                'address' => 'required|string',
                'city' => 'required|string',
                'zip_code' => 'required|string',
                'payment_method' => 'required|in:cash,credit_card',
                'order_details' => 'required|array',
                'order_details.*.product_id' => 'required|exists:products,id',
                'order_details.*.quantity' => 'required|integer|min:1',
                'order_details.*.price' => 'required|numeric|min:0',
            ]);

            $order = Order::create([
                'user_id' => $user_id,
                'total_price' => $validated['total_price'],
                'full_name' => $validated['full_name'],
                'email' => $validated['email'], 
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'city' => $validated['city'],
                'zip_code' => $validated['zip_code'],
                'payment_method' => $validated['payment_method'],
            ]);

            foreach ($validated['order_details'] as $detail) {
                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_id' => $detail['product_id'],
                    'quantity' => $detail['quantity'],
                    'price' => $detail['price'],
                ]);
            }
            Cart::where('user_id', $user_id)->delete();
            return response()->json(['message' => 'Order created successfully', 'order' => $order->load('orderDetails')]);
        }
        public function updateOrder(Request $request, $id)
        {
            $order = Order::find($id);
            if (!$order) {
                return response()->json(['message' => 'Order not found'], 404);
            }

            $validated = $request->validate([
                'user_id' => 'sometimes|required|exists:users,id',
                'total_price' => 'sometimes|required|numeric',
                'order_details' => 'sometimes|required|array',
                'order_details.*.product_id' => 'required_with:order_details|exists:products,id',
                'order_details.*.quantity' => 'required_with:order_details|integer|min:1',
                'order_details.*.price' => 'required_with:order_details|numeric|min:0',
            ]);

            $order->update($validated);

            if (isset($validated['order_details'])) {
                // Delete existing details
                OrderDetail::where('order_id', $order->id)->delete();

                // Create new details
                foreach ($validated['order_details'] as $detail) {
                    OrderDetail::create([
                        'order_id' => $order->id,
                        'product_id' => $detail['product_id'],
                        'quantity' => $detail['quantity'],
                        'price' => $detail['price'],
                    ]);
                }
            }

            return response()->json(['message' => 'Order updated successfully', 'order' => $order->load('orderDetails')]);
        }
        public function deleteOrder($id)
        {
            $order = Order::find($id);
            if ($order) {
                $order->delete();
                return response()->json(['message' => 'Order deleted successfully']);
            } else {
                return response()->json(['message' => 'Order not found'], 404);
            }
        }
        public function getOrdersByUserLoginID()
        {
            $user_id = auth()->user()->id;
            $orders = Order::where('user_id', $user_id)->with('orderDetails')->get(); // eager load relationship
            return response()->json($orders);
        }
        public function getOrdersOnlyByUserLoginID()
        {
            $user_id = auth()->user()->id;
            $orders = Order::where('user_id', $user_id)->paginate(8); // eager load relationship
            return response()->json($orders);
        }
        public function getOrderDetails($id)
        {
            $items = OrderDetail::where('order_id', $id)->with('product')->paginate(2); // eager load relationship
            return response()->json($items);
        }
        public function getAllOrdersOfUsers(){
            $orders=User::with('orders.orderDetails')->get();
            return response()->json($orders);
        }
}
