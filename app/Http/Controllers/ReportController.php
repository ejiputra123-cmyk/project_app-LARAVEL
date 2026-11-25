<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function transactions()
    {
        return view('reports.transactions');
    }

    public function transactionsData(Request $request)
    {
        $filters = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'payment_method' => ['nullable', 'in:cash,qris,transfer'],
            'status' => ['nullable', 'in:completed,cancelled'],
        ]);

        $query = Order::query()
            ->when($filters['date_from'] ?? null, fn ($q, $d) => $q->where('created_at', '>=', Carbon::parse($d)->startOfDay()))
            ->when($filters['date_to'] ?? null, fn ($q, $d) => $q->where('created_at', '<=', Carbon::parse($d)->endOfDay()))
            ->when($filters['payment_method'] ?? null, fn ($q, $m) => $q->where('payment_method', $m))
            ->when($filters['status'] ?? null, fn ($q, $s) => $q->where('status', $s));

        $summary = (clone $query)
            ->selectRaw("COUNT(*) as total_transactions, COALESCE(SUM(CASE WHEN status != 'cancelled' THEN total_amount ELSE 0 END), 0) as total_revenue")
            ->first();

        $rows = $query
            ->with(['user:id,name', 'member:id,member_code,name'])
            ->latest()
            ->get(['id', 'invoice_number', 'user_id', 'member_id', 'payment_method', 'total_amount', 'status', 'created_at']);

        return response()->json([
            'summary' => [
                'total_transactions' => (int) ($summary->total_transactions ?? 0),
                'total_revenue' => (int) ($summary->total_revenue ?? 0),
            ],
            'data' => $rows,
        ]);
    }

    public function products()
    {
        return view('reports.products');
    }

    public function productsData(Request $request)
    {
        $filters = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $summary = Product::query()
            ->selectRaw('COUNT(*) as total_products, COALESCE(SUM(CASE WHEN stock <= 5 THEN 1 ELSE 0 END), 0) as low_stock_count')
            ->first();

        // Agregasi penjualan di subquery agar lolos ONLY_FULL_GROUP_BY dan mengabaikan order cancelled.
        $salesSub = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', '!=', 'cancelled')
            ->whereNotNull('order_items.product_id')
            ->when($filters['date_from'] ?? null, fn ($q, $d) => $q->where('orders.created_at', '>=', Carbon::parse($d)->startOfDay()))
            ->when($filters['date_to'] ?? null, fn ($q, $d) => $q->where('orders.created_at', '<=', Carbon::parse($d)->endOfDay()))
            ->groupBy('order_items.product_id')
            ->selectRaw('order_items.product_id, SUM(order_items.quantity) as qty_sold, SUM(order_items.subtotal) as revenue');

        $rows = Product::query()
            ->leftJoinSub($salesSub, 'sales', 'sales.product_id', '=', 'products.id')
            ->selectRaw('products.id, products.name, products.stock, COALESCE(sales.qty_sold, 0) as qty_sold, COALESCE(sales.revenue, 0) as revenue')
            ->orderByDesc('qty_sold')
            ->orderByDesc('revenue')
            ->orderBy('products.name')
            ->get()
            ->map(fn ($row) => [
                'id' => (int) $row->id,
                'name' => $row->name,
                'qty_sold' => (int) $row->qty_sold,
                'revenue' => (int) $row->revenue,
                'stock' => (int) $row->stock,
            ]);

        return response()->json([
            'summary' => [
                'total_products' => (int) ($summary->total_products ?? 0),
                'low_stock_count' => (int) ($summary->low_stock_count ?? 0),
            ],
            'data' => $rows,
        ]);
    }

    public function members()
    {
        return view('reports.members');
    }

    public function membersData(Request $request)
    {
        $filters = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $orderFilter = function ($q) use ($filters) {
            $q->where('status', '!=', 'cancelled')
                ->when($filters['date_from'] ?? null, fn ($q, $d) => $q->where('created_at', '>=', Carbon::parse($d)->startOfDay()))
                ->when($filters['date_to'] ?? null, fn ($q, $d) => $q->where('created_at', '<=', Carbon::parse($d)->endOfDay()));
        };

        $rows = Member::query()
            ->select(['id', 'member_code', 'name'])
            ->withCount(['orders as transactions_count' => $orderFilter])
            ->withSum(['orders as total_spent' => $orderFilter], 'total_amount')
            ->withMax(['orders as last_transaction_at' => $orderFilter], 'created_at')
            ->orderByRaw('COALESCE(total_spent, 0) DESC')
            ->orderBy('name')
            ->get()
            ->map(fn ($member) => [
                'id' => (int) $member->id,
                'member_code' => $member->member_code,
                'name' => $member->name,
                'transactions_count' => (int) ($member->transactions_count ?? 0),
                'total_spent' => (int) ($member->total_spent ?? 0),
                'last_transaction_at' => $member->last_transaction_at,
            ]);

        return response()->json([
            'data' => $rows,
        ]);
    }
}
