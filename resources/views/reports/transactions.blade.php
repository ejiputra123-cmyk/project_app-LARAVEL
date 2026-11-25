@extends('templates.layoutApp')

@push('style')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tabulator-tables@6.6.1/dist/css/tabulator_bootstrap5.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer">
    <style>
        .report-summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
        }

        .report-filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
            gap: 0.75rem;
            align-items: end;
        }

        .report-table-region {
            overflow-x: auto;
            max-inline-size: 100%;
        }

        .report-table-region:focus-visible {
            outline: 2px solid var(--bs-primary);
            outline-offset: 2px;
        }
    </style>
@endpush

@section('content')
    <!--begin::App Content Header-->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="mb-0 fs-3">Report Transactions</h1>
                </div>
                <div class="col-sm-6">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item">Reports</li>
                            <li class="breadcrumb-item active" aria-current="page">Transactions</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <!--end::App Content Header-->

    <!--begin::App Content-->
    <div class="app-content">
        <div class="container-fluid">
            <!-- Kartu Ringkasan -->
            <div class="report-summary-grid mb-3">
                <div class="card mb-0">
                    <div class="card-body d-flex align-items-center justify-content-between gap-3">
                        <div class="min-w-0">
                            <div class="text-body-secondary small">Jumlah Transaksi</div>
                            <div class="fs-4 fw-bold" id="summary-count">0</div>
                        </div>
                        <span class="badge text-bg-primary p-3 fs-5">
                            <i class="bi bi-receipt" aria-hidden="true"></i>
                        </span>
                    </div>
                </div>
                <div class="card mb-0">
                    <div class="card-body d-flex align-items-center justify-content-between gap-3">
                        <div class="min-w-0">
                            <div class="text-body-secondary small">Total Pendapatan <span class="text-body-tertiary">(non-cancelled)</span></div>
                            <div class="fs-4 fw-bold text-success" id="summary-revenue">Rp 0</div>
                        </div>
                        <span class="badge text-bg-success p-3 fs-5">
                            <i class="bi bi-cash-coin" aria-hidden="true"></i>
                        </span>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2 class="card-title fs-5 mb-0" id="report-table-heading">Laporan Transaksi</h2>
                </div>
                <div class="card-body">
                    <search>
                        <form id="report-filter-form" class="report-filter-grid mb-3" aria-label="Filter laporan transaksi">
                            <div>
                                <label for="date_from" class="form-label small fw-semibold mb-1">Dari Tanggal</label>
                                <input type="date" id="date_from" name="date_from" class="form-control">
                            </div>
                            <div>
                                <label for="date_to" class="form-label small fw-semibold mb-1">Sampai Tanggal</label>
                                <input type="date" id="date_to" name="date_to" class="form-control">
                            </div>
                            <div>
                                <label for="payment_method" class="form-label small fw-semibold mb-1">Metode Bayar</label>
                                <select id="payment_method" name="payment_method" class="form-select">
                                    <option value="">Semua Metode</option>
                                    <option value="cash">Cash</option>
                                    <option value="qris">QRIS</option>
                                    <option value="transfer">Transfer</option>
                                </select>
                            </div>
                            <div>
                                <label for="status" class="form-label small fw-semibold mb-1">Status</label>
                                <select id="status" name="status" class="form-select">
                                    <option value="">Semua Status</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary flex-grow-1">Terapkan</button>
                                <button type="button" id="btn-reset-filter" class="btn btn-outline-secondary">Reset</button>
                            </div>
                        </form>
                    </search>

                    <div id="filter-error" class="alert alert-danger py-2 d-none" role="alert"></div>
                    <div id="report-status" role="status" aria-live="polite" class="visually-hidden"></div>

                    <div class="report-table-region" role="region" tabindex="0" aria-labelledby="report-table-heading">
                        <div id="report-transactions-table"></div>
                    </div>
                    <div id="table-pagination" class="d-flex flex-wrap gap-2 justify-content-end mt-2"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="https://cdn.jsdelivr.net/npm/tabulator-tables@6.6.1/dist/js/tabulator.min.js" crossorigin="anonymous"
        referrerpolicy="no-referrer"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const apiUrl = @json(route('reports.transactions.api'));
            const form = document.getElementById('report-filter-form');
            const errorEl = document.getElementById('filter-error');
            const statusEl = document.getElementById('report-status');
            const countEl = document.getElementById('summary-count');
            const revenueEl = document.getElementById('summary-revenue');

            const formatRupiah = (amount) => 'Rp ' + Number(amount || 0).toLocaleString('id-ID');

            const formatDateId = (value) => {
                if (!value) return '-';
                return new Date(value).toLocaleString('id-ID', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            };

            const escapeHtml = (str) => String(str ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');

            const paymentBadge = (method) => {
                const map = {
                    cash: ['Cash', 'text-bg-success'],
                    qris: ['QRIS', 'text-bg-info'],
                    transfer: ['Transfer', 'text-bg-primary']
                };
                const [label, cls] = map[method] || [method || '-', 'text-bg-secondary'];
                return `<span class="badge ${cls}">${escapeHtml(label)}</span>`;
            };

            const statusBadge = (status) => {
                if (status === 'completed') return '<span class="badge text-bg-success">Completed</span>';
                if (status === 'cancelled') return '<span class="badge text-bg-danger">Cancelled</span>';
                return `<span class="badge text-bg-secondary">${escapeHtml(status || '-')}</span>`;
            };

            const tableEl = document.getElementById('report-transactions-table');
            const table = new Tabulator(tableEl, {
                data: [],
                layout: 'fitColumns',
                pagination: 'local',
                paginationElement: document.getElementById('table-pagination'),
                paginationSize: 10,
                paginationSizeSelector: [10, 25, 50, 100],
                placeholder: 'Belum ada transaksi pada rentang filter ini.',
                columns: [
                    { title: 'No. Invoice', field: 'invoice_number', minWidth: 160 },
                    {
                        title: 'Tanggal',
                        field: 'created_at',
                        minWidth: 155,
                        formatter: (cell) => formatDateId(cell.getValue())
                    },
                    {
                        title: 'Kasir',
                        field: 'user.name',
                        minWidth: 120,
                        formatter: (cell) => escapeHtml(cell.getValue() || '-')
                    },
                    {
                        title: 'Member',
                        field: 'member.name',
                        minWidth: 120,
                        formatter: (cell) => escapeHtml(cell.getValue() || 'Umum')
                    },
                    {
                        title: 'Metode Bayar',
                        field: 'payment_method',
                        width: 130,
                        hozAlign: 'center',
                        formatter: (cell) => paymentBadge(cell.getValue())
                    },
                    {
                        title: 'Total',
                        field: 'total_amount',
                        sorter: 'number',
                        minWidth: 130,
                        hozAlign: 'right',
                        formatter: (cell) => `<strong>${formatRupiah(cell.getValue())}</strong>`
                    },
                    {
                        title: 'Status',
                        field: 'status',
                        width: 115,
                        hozAlign: 'center',
                        formatter: (cell) => statusBadge(cell.getValue())
                    }
                ]
            });

            table.on('tableBuilt', () => {
                tableEl.removeAttribute('aria-owns');
                tableEl.querySelector('.tabulator-tableHolder').setAttribute('role', 'rowgroup');
                tableEl.querySelector('.tabulator-table').setAttribute('role', 'presentation');
            });

            table.on('renderComplete', () => {
                document.querySelectorAll('#table-pagination button').forEach(btn =>
                    btn.classList.add('btn', 'btn-sm', 'btn-outline-secondary')
                );
            });

            const loadReport = async () => {
                errorEl.classList.add('d-none');
                errorEl.textContent = '';

                const params = new URLSearchParams();
                for (const [key, val] of new FormData(form).entries()) {
                    if (val) params.set(key, val);
                }

                const url = params.toString() ? `${apiUrl}?${params}` : apiUrl;

                try {
                    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                    const json = await res.json();

                    if (!res.ok) {
                        const firstError = json.errors
                            ? Object.values(json.errors).flat()[0]
                            : (json.message || 'Filter tidak valid.');
                        errorEl.textContent = firstError;
                        errorEl.classList.remove('d-none');
                        if (json.errors?.date_to) document.getElementById('date_to').focus();
                        return;
                    }

                    countEl.textContent = Number(json.summary.total_transactions).toLocaleString('id-ID');
                    revenueEl.textContent = formatRupiah(json.summary.total_revenue);
                    await table.replaceData(json.data);
                    statusEl.textContent = `Menampilkan ${json.summary.total_transactions} transaksi dengan total pendapatan ${formatRupiah(json.summary.total_revenue)}.`;
                } catch {
                    errorEl.textContent = 'Gagal memuat laporan transaksi.';
                    errorEl.classList.remove('d-none');
                }
            };

            form.addEventListener('submit', (e) => {
                e.preventDefault();
                loadReport();
            });

            document.getElementById('btn-reset-filter').addEventListener('click', () => {
                form.reset();
                loadReport();
            });

            table.on('tableBuilt', loadReport);
        });
    </script>
@endpush
