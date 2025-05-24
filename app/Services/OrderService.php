<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderService
{
    protected Order $model;

    public function __construct(Order $model)
    {
        $this->model = $model;
    }

    /**
     * Get all orders for the authenticated user
     */
    public function indexOrders($perPage = 10)
    {
        return $this->model->where('user_id', Auth::id())->with('items.product')->paginate($perPage);
    }

    /**
     * Store a new order with items
     */
    public function storeOrder(array $data)
    {
        DB::beginTransaction();
        try {
            $orderData = Arr::only($data, ['user_id', 'total_amount', 'status', 'notes']);
            $orderData['user_id'] = Auth::id();
            $orderData['order_number'] = $this->generateOrderNumber();
            $order = $this->model->create($orderData);

            foreach ($data['items'] as $item) {
                $itemData = Arr::only($item, ['product_id', 'quantity', 'unit_price', 'subtotal']);
                $itemData['order_id'] = $order->id;
                OrderItem::create($itemData);
            }

            DB::commit();
            return [
                'status' => true,
                'message' => 'تم إنشاء الطلب بنجاح',
                'data' => $order->load('items.product'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order creation failed: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'حدث خطأ أثناء إنشاء الطلب',
            ];
        }
    }

    /**
     * Show order details
     */
    public function showOrder($id)
    {
        return $this->model->with('items.product')->find($id);
    }

    /**
     * Generate a unique order number
     */
    protected function generateOrderNumber(): string
    {
        return 'ORD-' . strtoupper(uniqid());
    }

    /**
     * Update an order with items
     */
    public function updateOrder(array $data, $orderId)
    {
        DB::beginTransaction();
        try {
            $order = $this->model->with('items')->find($orderId);
            if (!$order) {
                return [
                    'status' => false,
                    'message' => 'Order not found',
                ];
            }
            $orderData = Arr::only($data, ['total_amount', 'status', 'notes']);
            $order->update($orderData);
            if (isset($data['items'])) {
                $order->items()->delete();
                foreach ($data['items'] as $item) {
                    $itemData = Arr::only($item, ['product_id', 'quantity', 'unit_price', 'subtotal']);
                    $itemData['order_id'] = $order->id;
                    OrderItem::create($itemData);
                }
            }
            DB::commit();
            return [
                'status' => true,
                'message' => 'تم تحديث الطلب بنجاح',
                'data' => $order->load('items.product'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order update failed: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'حدث خطأ أثناء تحديث الطلب',
            ];
        }
    }
}