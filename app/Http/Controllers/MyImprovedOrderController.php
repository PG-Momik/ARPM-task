<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Order;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;

class MyImprovedOrderController extends Controller
{
    public function index(): View
    {
        try {
            $orders = Order::with([
                'customer:id,name',
                'items:id,order_id,product_id,price,quantity',
                'items.product:id,name'
            ])
                ->select(['id', 'customer_id', 'status', 'created_at', 'completed_at'])
                ->get();

            $orderIds = $orders->pluck('id');

            $lastCartItems = CartItem::whereIn('order_id', $orderIds)
                ->select('order_id', 'created_at')
                ->orderByDesc('created_at')
                ->get()
                ->unique('order_id')
                ->keyBy('order_id');

            $completedOrders = Order::whereIn('id', $orderIds)
                ->where('status', 'completed')
                ->select(['id', 'completed_at'])
                ->get()
                ->keyBy('id');

            $orderData = $orders->map(function ($order) use ($lastCartItems, $completedOrders) {
                $totalAmount = $order->items->sum(function ($item) {
                    return $item->price * $item->quantity;
                });

                $orderId = $order->id;

                return [
                    'order_id'               => $orderId,
                    'customer_name'          => $order->customer->name,
                    'total_amount'           => $totalAmount,
                    'items_count'            => $order->items->count(),
                    'last_added_to_cart'     => Arr::get($lastCartItems, "$orderId.last_added_at"),
                    'completed_order_exists' => Arr::has($completedOrders, $orderId),
                    'completed_at'           => Arr::get($completedOrders, "$orderId.completed_at"),
                    'created_at'             => $order->created_at,
                ];
            });

            $orderData = $orderData->sortByDesc(function ($order) {
                return Arr::get($order, 'completed_at', '');
            })->values();

            return view('orders.index', ['orders' => $orderData]);
        }catch (Exception $exception){
            logger()->error($exception);

            return view('errors.500');
        }
    }
}



