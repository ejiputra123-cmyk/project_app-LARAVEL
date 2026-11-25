<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Struk {{ $order->invoice_number }} | ZieMarket</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
        crossorigin="anonymous" />
    <link rel="stylesheet" href="{{ asset('assets/css/adminlte.css') }}" />
    <style>
        body {
            background-color: var(--bs-tertiary-bg);
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        }

        .receipt-paper {
            max-width: 380px;
            margin: 1.5rem auto;
            background-color: var(--bs-body-bg);
            color: var(--bs-body-color);
            border: 1px dashed var(--bs-border-color);
            padding: 1.25rem;
        }

        .receipt-divider {
            border-top: 1px dashed var(--bs-border-color);
            margin: 0.75rem 0;
        }

        @media print {
            body {
                background: #fff !important;
                color: #000 !important;
            }

            .receipt-paper {
                margin: 0 auto;
                border: none;
                padding: 0;
                max-width: 80mm;
                background: #fff !important;
                color: #000 !important;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    <div class="receipt-paper shadow-sm">
        <div class="text-center">
            <h5 class="fw-bold mb-0">ZieMarket</h5>
            <small class="text-body-secondary">Struk Pembayaran</small>
            @if ($order->status === 'cancelled')
                <div class="mt-1">
                    <span class="badge text-bg-danger">DIBATALKAN</span>
                </div>
            @endif
        </div>

        <div class="receipt-divider"></div>

        <div class="small">
            <div class="d-flex justify-content-between">
                <span>No. Invoice</span>
                <strong>{{ $order->invoice_number }}</strong>
            </div>
            <div class="d-flex justify-content-between">
                <span>Tanggal</span>
                <span>{{ $order->created_at->format('d/m/Y H:i') }}</span>
            </div>
            <div class="d-flex justify-content-between">
                <span>Kasir</span>
                <span>{{ $order->user?->name ?? '-' }}</span>
            </div>
            <div class="d-flex justify-content-between">
                <span>Member</span>
                <span>{{ $order->member ? $order->member->name : 'Umum' }}</span>
            </div>
        </div>

        <div class="receipt-divider"></div>

        <div class="small">
            @foreach ($order->items as $item)
                <div class="mb-2">
                    <div class="fw-semibold">{{ $item->product_name }}</div>
                    <div class="d-flex justify-content-between">
                        <span>{{ $item->quantity }} x Rp {{ number_format($item->price, 0, ',', '.') }}</span>
                        <span>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="receipt-divider"></div>

        <div class="small">
            <div class="d-flex justify-content-between fw-bold fs-6">
                <span>TOTAL</span>
                <span>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
            </div>
            <div class="d-flex justify-content-between mt-1">
                <span>Bayar ({{ strtoupper($order->payment_method) }})</span>
                <span>Rp {{ number_format($order->paid_amount, 0, ',', '.') }}</span>
            </div>
            <div class="d-flex justify-content-between">
                <span>Kembalian</span>
                <span>Rp {{ number_format($order->change_amount, 0, ',', '.') }}</span>
            </div>
        </div>

        @if ($order->notes)
            <div class="receipt-divider"></div>
            <div class="small">
                <strong>Catatan:</strong> {{ $order->notes }}
            </div>
        @endif

        <div class="receipt-divider"></div>

        <div class="text-center small text-body-secondary">
            Terima kasih telah berbelanja di ZieMarket!
        </div>

        <div class="d-flex gap-2 mt-3 no-print">
            <button type="button" onclick="window.print()" class="btn btn-primary btn-sm w-100">
                <i class="bi bi-printer me-1" aria-hidden="true"></i>Cetak Struk
            </button>
            <button type="button" onclick="window.close()" class="btn btn-outline-secondary btn-sm">
                Tutup
            </button>
        </div>
    </div>
</body>

</html>
