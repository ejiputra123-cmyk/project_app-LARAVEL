@extends('templates.layoutApp')

@push('style')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tabulator-tables@6.6.1/dist/css/tabulator_bootstrap5.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer">
    <style>
        .report-filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
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
                    <h1 class="mb-0 fs-3">Report Members</h1>
                </div>
                <div class="col-sm-6">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item">Reports</li>
                            <li class="breadcrumb-item active" aria-current="page">Members</li>
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
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title fs-5 mb-0" id="report-table-heading">Laporan Belanja Member</h2>
                </div>
                <div class="card-body">
                    <search>
                        <form id="report-filter-form" class="report-filter-grid mb-3" aria-label="Filter laporan member">
                            <div>
                                <label for="date_from" class="form-label small fw-semibold mb-1">Dari Tanggal</label>
                                <input type="date" id="date_from" name="date_from" class="form-control">
                            </div>
                            <div>
                                <label for="date_to" class="form-label small fw-semibold mb-1">Sampai Tanggal</label>
                                <input type="date" id="date_to" name="date_to" class="form-control">
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
                        <div id="report-members-table"></div>
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
            const apiUrl = @json(route('reports.members.api'));
            const form = document.getElementById('report-filter-form');
            const errorEl = document.getElementById('filter-error');
            const statusEl = document.getElementById('report-status');

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

            const tableEl = document.getElementById('report-members-table');
            const table = new Tabulator(tableEl, {
                data: [],
                layout: 'fitColumns',
                pagination: 'local',
                paginationElement: document.getElementById('table-pagination'),
                paginationSize: 10,
                paginationSizeSelector: [10, 25, 50, 100],
                initialSort: [{ column: 'total_spent', dir: 'desc' }],
                placeholder: 'Belum ada data member.',
                columns: [
                    {
                        title: 'Kode Member',
                        field: 'member_code',
                        width: 150,
                        formatter: (cell) => escapeHtml(cell.getValue())
                    },
                    {
                        title: 'Nama',
                        field: 'name',
                        minWidth: 160,
                        formatter: (cell) => escapeHtml(cell.getValue())
                    },
                    {
                        title: 'Jumlah Transaksi',
                        field: 'transactions_count',
                        sorter: 'number',
                        width: 155,
                        hozAlign: 'right',
                        formatter: (cell) => Number(cell.getValue() || 0).toLocaleString('id-ID')
                    },
                    {
                        title: 'Total Belanja',
                        field: 'total_spent',
                        sorter: 'number',
                        minWidth: 150,
                        hozAlign: 'right',
                        formatter: (cell) => `<strong>${formatRupiah(cell.getValue())}</strong>`
                    },
                    {
                        title: 'Transaksi Terakhir',
                        field: 'last_transaction_at',
                        minWidth: 165,
                        formatter: (cell) => formatDateId(cell.getValue())
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

                    await table.replaceData(json.data);
                    statusEl.textContent = `Menampilkan laporan ${json.data.length} member.`;
                } catch {
                    errorEl.textContent = 'Gagal memuat laporan member.';
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
