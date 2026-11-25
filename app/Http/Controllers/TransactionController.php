<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    public function order()
    {
        $products = Product::orderBy('name')->get(['id', 'name', 'price', 'stock', 'image']);
        $members = Member::orderBy('name')->get(['id', 'member_code', 'name', 'phone']);

        return view('transactions.order', compact('products', 'members'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'member_id' => ['nullable', 'integer', 'exists:members,id'],
            'payment_method' => ['required', 'in:cash,qris,transfer'],
            'paid_amount' => ['required', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $order = DB::transaction(function () use ($validated) {
            $productIds = collect($validated['items'])->pluck('product_id')->sort()->values();

            // Lock urutan tetap berdasarkan ID untuk mencegah deadlock antar kasir.
            $products = Product::whereIn('id', $productIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $totalAmount = 0;
            $preparedItems = [];

            foreach ($validated['items'] as $item) {
                $product = $products->get($item['product_id']);
                $qty = (int) $item['quantity'];

                if (! $product || $product->stock < $qty) {
                    $available = $product ? $product->stock : 0;
                    $name = $product ? $product->name : 'Produk';

                    throw ValidationException::withMessages([
                        'items' => "Stok {$name} tidak mencukupi (tersisa {$available}).",
                    ]);
                }

                $price = (int) $product->price;
                $subtotal = $price * $qty;
                $totalAmount += $subtotal;

                $preparedItems[] = [
                    'product' => $product,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $price,
                    'quantity' => $qty,
                    'subtotal' => $subtotal,
                ];
            }

            $paidAmount = (int) $validated['paid_amount'];
            if ($paidAmount < $totalAmount) {
                throw ValidationException::withMessages([
                    'paid_amount' => 'Nominal bayar kurang dari total belanja.',
                ]);
            }

            $prefix = 'INV-' . now()->format('Ymd') . '-';
            $lastInvoice = Order::where('invoice_number', 'like', $prefix . '%')
                ->lockForUpdate()
                ->orderByDesc('invoice_number')
                ->value('invoice_number');
            $nextNumber = $lastInvoice ? ((int) substr($lastInvoice, strlen($prefix))) + 1 : 1;
            $invoiceNumber = $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);

            $order = Order::create([
                'invoice_number' => $invoiceNumber,
                'user_id' => Auth::id(),
                'member_id' => $validated['member_id'] ?? null,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'change_amount' => $paidAmount - $totalAmount,
                'payment_method' => $validated['payment_method'],
                'status' => 'completed',
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($preparedItems as $item) {
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['subtotal'],
                ]);

                $item['product']->decrement('stock', $item['quantity']);
            }

            return $order->load(['user:id,name', 'member:id,member_code,name', 'items']);
        });

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Transaksi berhasil diproses.',
                'order' => $order,
                'receipt_url' => route('transactions.receipt', $order),
            ], 201);
        }

        return redirect()->route('transactions.order')
            ->with('success', "Transaksi {$order->invoice_number} berhasil diproses.");
    }

    public function history()
    {
        return view('transactions.history');
    }

    public function apiData()
    {
        $orders = Order::with(['user:id,name', 'member:id,member_code,name', 'items'])
            ->latest()
            ->get();

        return response()->json($orders);
    }

    public function show(Order $order)
    {
        $order->load(['user:id,name', 'member:id,member_code,name,phone', 'items']);

        return response()->json($order);
    }

    public function receipt(Order $order)
    {
        $order->load(['user:id,name', 'member:id,member_code,name,phone', 'items']);

        return view('transactions.receipt', compact('order'));
    }

    public function cancel(Request $request, Order $order)
    {
        $cancelled = DB::transaction(function () use ($order) {
            $lockedOrder = Order::whereKey($order->id)
                ->lockForUpdate()
                ->with('items')
                ->firstOrFail();

            if ($lockedOrder->status !== 'completed') {
                throw ValidationException::withMessages([
                    'status' => 'Transaksi ini sudah dibatalkan sebelumnya.',
                ]);
            }

            $productIds = $lockedOrder->items->pluck('product_id')->filter()->sort()->values();
            $products = Product::whereIn('id', $productIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($lockedOrder->items as $item) {
                if ($item->product_id && ($product = $products->get($item->product_id))) {
                    $product->increment('stock', $item->quantity);
                }
            }

            $lockedOrder->update(['status' => 'cancelled']);

            return $lockedOrder;
        });

        $message = "Transaksi {$cancelled->invoice_number} berhasil dibatalkan dan stok dikembalikan.";

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'order' => $cancelled,
            ]);
        }

        return redirect()->route('transactions.history')->with('success', $message);
    }
}
