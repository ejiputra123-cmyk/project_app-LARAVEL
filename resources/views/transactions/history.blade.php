@extends('templates.layoutApp')

@push('style')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tabulator-tables@6.6.1/dist/css/tabulator_bootstrap5.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer">
@endpush

@section('modal')
    @include('transactions.modal')
@endsection

@section('content')
    <!--begin::App Content Header-->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="mb-0 fs-3">Transaction History</h1>
                </div>
                <div class="col-sm-6">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item">Transactions</li>
                            <li class="breadcrumb-item active" aria-current="page">History</li>
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
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Form Batalkan Transaksi (khusus admin) -->
            <form id="cancelOrderForm" method="POST" class="d-none">
                @csrf
                @method('PATCH')
            </form>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title fs-5">Riwayat Transaksi</h2>

                            <br>
                            <a href="{{ route('transactions.order') }}" class="btn btn-primary mt-2">
                                <i class="bi bi-plus-circle me-1" aria-hidden="true"></i>
                                ORDER BARU
                            </a>

                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse"
                                    aria-label="Collapse card">
                                    <i data-lte-icon="expand" class="bi bi-plus-lg"></i>
                                    <i data-lte-icon="collapse" class="bi bi-dash-lg"></i>
                                </button>
                                <button type="button" class="btn btn-tool" data-lte-toggle="card-remove"
                                    aria-label="Remove card">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <button id="export-csv" type="button" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-filetype-csv me-1" aria-hidden="true"></i>
                                    Export CSV
                                </button>
                                <button id="export-json" type="button" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-filetype-json me-1" aria-hidden="true"></i>
                                    Export JSON
                                </button>
                                <button id="print-table" type="button" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-printer me-1" aria-hidden="true"></i>
                                    Print
                                </button>
                            </div>

                            <!-- Baris Filter -->
                            <search>
                                <div class="row g-2 mb-3">
                                    <div class="col-md-4">
                                        <label for="table-filter" class="visually-hidden">Cari invoice, member, atau kasir</label>
                                        <input type="search" id="table-filter" class="form-control"
                                            placeholder="Cari invoice, member, atau kasir..." autocomplete="off">
                                    </div>
                                    <div class="col-md-3">
                                        <input type="date" id="filter-date-from" class="form-control" aria-label="Dari tanggal"
                                            title="Dari tanggal">
                                    </div>
                                    <div class="col-md-3">
                                        <input type="date" id="filter-date-to" class="form-control" aria-label="Sampai tanggal"
                                            title="Sampai tanggal">
                                    </div>
                                    <div class="col-md-2">
                                        <select id="filter-status" class="form-select" aria-label="Filter status">
                                            <option value="">Semua Status</option>
                                            <option value="completed">Completed</option>
                                            <option value="cancelled">Cancelled</option>
                                        </select>
                                    </div>
                                </div>
                            </search>

                            <!-- Tabel Tabulator -->
                            <div id="transaction-table" aria-label="Transaction History"></div>

                            <!-- Pagination -->
                            <div id="table-pagination" class="d-flex flex-wrap gap-2 justify-content-end mt-2"></div>
                        </div>

                        <div class="card-footer">Tabel Riwayat Transaksi</div>
                    </div>
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
            const isAdmin = @json(auth()->user()->can('admin'));

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

            const createBadge = (label, cls) => {
                const span = document.createElement('span');
                span.className = `badge ${cls}`;
                span.textContent = label;
                return span;
            };

            const paymentMeta = (method) => ({
                cash: ['Cash', 'text-bg-success'],
                qris: ['QRIS', 'text-bg-info'],
                transfer: ['Transfer', 'text-bg-primary']
            }[method] || [method || '-', 'text-bg-secondary']);

            const statusMeta = (status) => ({
                completed: ['Completed', 'text-bg-success'],
                cancelled: ['Cancelled', 'text-bg-danger']
            }[status] || [status || '-', 'text-bg-secondary']);

            async function showDetail(orderId) {
                try {
                    const res = await fetch(`/transactions/${orderId}`, {
                        headers: { 'Accept': 'application/json' }
                    });
                    if (!res.ok) throw new Error('Gagal memuat detail transaksi.');

                    const order = await res.json();

                    document.getElementById('detail-invoice').textContent = order.invoice_number;
                    document.getElementById('detail-date').textContent = formatDateId(order.created_at);
                    document.getElementById('detail-cashier').textContent = order.user?.name || '-';
                    document.getElementById('detail-member').textContent = order.member
                        ? `${order.member.member_code} - ${order.member.name}`
                        : 'Umum';

                    const [payLabel, payCls] = paymentMeta(order.payment_method);
                    document.getElementById('detail-payment').replaceChildren(createBadge(payLabel, payCls));

                    const [stLabel, stCls] = statusMeta(order.status);
                    document.getElementById('detail-status').replaceChildren(createBadge(stLabel, stCls));

                    const tbody = document.getElementById('detail-items');
                    tbody.replaceChildren();
                    (order.items || []).forEach((item) => {
                        const tr = document.createElement('tr');
                        const tdName = document.createElement('td');
                        tdName.textContent = item.product_name;

                        const tdPrice = document.createElement('td');
                        tdPrice.className = 'text-end';
                        tdPrice.textContent = formatRupiah(item.price);

                        const tdQty = document.createElement('td');
                        tdQty.className = 'text-center';
                        tdQty.textContent = String(item.quantity);

                        const tdSub = document.createElement('td');
                        tdSub.className = 'text-end';
                        tdSub.textContent = formatRupiah(item.subtotal);

                        tr.append(tdName, tdPrice, tdQty, tdSub);
                        tbody.appendChild(tr);
                    });

                    document.getElementById('detail-total').textContent = formatRupiah(order.total_amount);
                    document.getElementById('detail-paid').textContent = formatRupiah(order.paid_amount);
                    document.getElementById('detail-change').textContent = formatRupiah(order.change_amount);

                    const notesWrapper = document.getElementById('detail-notes-wrapper');
                    if (order.notes) {
                        document.getElementById('detail-notes').textContent = order.notes;
                        notesWrapper.classList.remove('d-none');
                    } else {
                        notesWrapper.classList.add('d-none');
                    }

                    document.getElementById('detail-receipt-link').href = `/transactions/${order.id}/receipt`;

                    bootstrap.Modal.getOrCreateInstance(document.getElementById('detailTransactionModal')).show();
                } catch (err) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Tidak dapat memuat detail transaksi.'
                    });
                }
            }

            function cancelOrder(orderId, invoiceNumber) {
                Swal.fire({
                    title: 'Batalkan Transaksi?',
                    text: `Transaksi ${invoiceNumber} akan dibatalkan dan stok produk akan dikembalikan.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Batalkan!',
                    cancelButtonText: 'Tidak'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.getElementById('cancelOrderForm');
                        form.action = `/transactions/${orderId}/cancel`;
                        form.submit();
                    }
                });
            }

            const tableEl = document.getElementById('transaction-table');

            // Delegated click handler untuk tombol Detail & Batalkan (tanpa inline onclick).
            tableEl.addEventListener('click', (e) => {
                const btn = e.target.closest('button[data-action]');
                if (!btn) return;
                const id = Number(btn.dataset.id);
                if (btn.dataset.action === 'detail') {
                    showDetail(id);
                } else if (btn.dataset.action === 'cancel') {
                    cancelOrder(id, btn.dataset.invoice || '');
                }
            });

            const table = new Tabulator('#transaction-table', {
                ajaxURL: "{{ route('transactions.api') }}",
                layout: 'fitColumns',
                filterMode: 'local',
                pagination: 'local',
                paginationElement: document.getElementById('table-pagination'),
                paginationSize: 10,
                paginationSizeSelector: [10, 25, 50, 100],
                movableColumns: true,
                columns: [
                    {
                        title: 'No. Invoice',
                        field: 'invoice_number',
                        minWidth: 160
                    },
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
                        width: 125,
                        hozAlign: 'center',
                        formatter: (cell) => {
                            const [label, cls] = paymentMeta(cell.getValue());
                            return `<span class="badge ${cls}">${escapeHtml(label)}</span>`;
                        }
                    },
                    {
                        title: 'Total',
                        field: 'total_amount',
                        minWidth: 125,
                        hozAlign: 'right',
                        formatter: (cell) => `<strong>${formatRupiah(cell.getValue())}</strong>`
                    },
                    {
                        title: 'Status',
                        field: 'status',
                        width: 115,
                        hozAlign: 'center',
                        formatter: (cell) => {
                            const [label, cls] = statusMeta(cell.getValue());
                            return `<span class="badge ${cls}">${escapeHtml(label)}</span>`;
                        }
                    },
                    {
                        title: 'Aksi',
                        field: 'id',
                        minWidth: 210,
                        headerSort: false,
                        hozAlign: 'center',
                        download: false,
                        print: false,
                        formatter: (cell) => {
                            const row = cell.getRow().getData();
                            const inv = escapeHtml(row.invoice_number);
                            const cancelBtn = isAdmin && row.status === 'completed'
                                ? `<button type="button" data-action="cancel" data-id="${row.id}" data-invoice="${inv}" aria-label="Batalkan ${inv}" class="btn btn-sm btn-danger">Batalkan</button>`
                                : '';
                            return `
                                <div class="d-inline-flex gap-1">
                                    <button type="button" data-action="detail" data-id="${row.id}" aria-label="Detail ${inv}" class="btn btn-sm btn-info text-white">Detail</button>
                                    <a href="/transactions/${row.id}/receipt" target="_blank" rel="noopener" aria-label="Cetak struk ${inv}" class="btn btn-sm btn-secondary">Struk</a>
                                    ${cancelBtn}
                                </div>`;
                        }
                    }
                ],
            });

            table.on('tableBuilt', () => {
                tableEl.removeAttribute('aria-owns');
                tableEl.querySelector('.tabulator-tableHolder').setAttribute('role', 'rowgroup');
                tableEl.querySelector('.tabulator-table').setAttribute('role', 'presentation');
            });

            table.on('renderComplete', () => {
                document.querySelectorAll('#table-pagination button').forEach(btn => btn.classList.add(
                    'btn', 'btn-sm', 'btn-outline-secondary'));
            });

            const searchInput = document.getElementById('table-filter');
            const dateFromInput = document.getElementById('filter-date-from');
            const dateToInput = document.getElementById('filter-date-to');
            const statusSelect = document.getElementById('filter-status');

            const applyFilters = () => {
                const keyword = searchInput.value.trim().toLowerCase();
                const dateFrom = dateFromInput.value;
                const dateTo = dateToInput.value;
                const status = statusSelect.value;

                if (!keyword && !dateFrom && !dateTo && !status) {
                    table.clearFilter();
                    return;
                }

                table.setFilter((data) => {
                    if (keyword) {
                        const invoice = (data.invoice_number || '').toLowerCase();
                        const cashier = (data.user?.name || '').toLowerCase();
                        const member = (data.member?.name || 'umum').toLowerCase();
                        if (!invoice.includes(keyword) && !cashier.includes(keyword) && !member.includes(keyword)) {
                            return false;
                        }
                    }

                    if (status && data.status !== status) {
                        return false;
                    }

                    if (dateFrom || dateTo) {
                        const d = new Date(data.created_at);
                        const localDate = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
                        if (dateFrom && localDate < dateFrom) return false;
                        if (dateTo && localDate > dateTo) return false;
                    }

                    return true;
                });
            };

            searchInput.addEventListener('input', applyFilters);
            dateFromInput.addEventListener('change', applyFilters);
            dateToInput.addEventListener('change', applyFilters);
            statusSelect.addEventListener('change', applyFilters);

            document.getElementById('export-csv').addEventListener('click', () => table.download('csv',
                'transactions.csv'));
            document.getElementById('export-json').addEventListener('click', () => table.download('json',
                'transactions.json'));
            document.getElementById('print-table').addEventListener('click', () => table.print(false, true));
        });
    </script>
@endpush
