<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Display a listing of the user's orders.
     */
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $orders = $this->orderService->indexOrders($perPage);
        return response()->json($orders);
    }

    /**
     * Store a newly created order in storage.
     */
    public function store(OrderRequest $request)
    {
        $result = $this->orderService->storeOrder($request->validated());
        if ($result['status']) {
            return response()->json($result, Response::HTTP_CREATED);
        }
        return response()->json($result, Response::HTTP_BAD_REQUEST);
    }

    /**
     * Display the specified order.
     */
    public function show($id)
    {
        $order = $this->orderService->showOrder($id);
        if ($order) {
            return response()->json($order);
        }
        return response()->json(['status' => false, 'message' => 'الطلب غير موجود'], Response::HTTP_NOT_FOUND);
    }

    /**
     * Update the specified order in storage.
     */
    public function update(OrderRequest $request, $id)
    {
        $result = $this->orderService->updateOrder($request->validated(), $id);
        if ($result['status']) {
            return response()->json($result);
        }
        return response()->json($result, Response::HTTP_BAD_REQUEST);
    }
}