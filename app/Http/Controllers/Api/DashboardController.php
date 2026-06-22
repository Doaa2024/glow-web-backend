<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\OrderDetail;

class DashboardController extends Controller
{
    public function getDashboardData()
    {
        $totalUsers = User::count();
        $totalProducts = Product::count();
        $totalOrders = Order::count();
        $totalRevenue = Order::sum('total_price');

        return response()->json([
            'total_users' => $totalUsers,
            'total_products' => $totalProducts,
            'total_orders' => $totalOrders,
            'total_revenue' => $totalRevenue,
        ]);
    }  
    public function getTopSellingProducts()
    {
       
       $topProducts = Product::with('orderDetails')->get();
       $topProducts = $topProducts->map(function ($product) {
                $product->total_sales = $product->orderDetails->sum('quantity');
                $product->total_revenue = $product->orderDetails->sum(function($detail) {
    return $detail->quantity * $detail->price;
});
                 return [
        'id' => $product->id,
        'name' => $product->name,
        'total_sales' => $product->total_sales,
        'total_revenue' => $product->total_revenue,
    ];
          })->sortByDesc('total_sales')->take(5)->values();

        return response()->json($topProducts);
    }   
    public function getMonthlyRevenue()
{
    // Month names array
    $months = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
        5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug',
        9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec'
    ];

    // 1️⃣ Get revenue from DB
    $dbRevenue = Order::selectRaw('MONTH(created_at) as month, SUM(total_price) as revenue')
        ->groupBy('month')
        ->pluck('revenue', 'month'); // key = month number, value = revenue

    // 2️⃣ Fill all months
    $monthlyRevenue = [];
    foreach ($months as $num => $name) {
        $monthlyRevenue[] = [
            'month' => $name,
            'revenue' => $dbRevenue->get($num, 0), // 0 if no revenue
        ];
    }

    return response()->json($monthlyRevenue);
}
    function getTopOrders()
    {
        $topOrders = Order::select('id', 'full_name', 'total_price', 'created_at')->orderBy('total_price', 'desc')->take(5)->get();
        return response()->json($topOrders);
    }
}
