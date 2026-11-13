@extends('templates.layoutApp')

@push('style')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tabulator-tables@6.6.1/dist/css/tabulator_bootstrap5.min.css"
        crossorigin="anonymous">
@endpush

@section('content')
    <!--begin::App Content Header-->
    <div class="app-content-header">
        <!--begin::Container-->
        <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="mb-0 fs-3">Products</h1>
                </div>
                <div class="col-sm-6">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Products</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <!--end::Row-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::App Content Header-->
    <!--begin::App Content-->
    <div class="app-content">
        <!--begin::Container-->
        <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
                <div class="col-12">
                    <!--begin::Card-->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Data Produk</h3>

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
                        <!-- /.card-header -->
                        <div class="card-body">
                            <div class="d-flex gap-2 mb-3">
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

                            <!-- Input Pencarian Data -->
                            <input type="text" id="table-filter" class="form-control mb-3"
                                placeholder="Cari nama produk...">

                            <!-- Elemen Tempat Tabulator Ditampilkan -->
                            <div id="product-table" aria-label="Products"></div>

                            <!-- Elemen Tempat Pagination Ditampilkan -->
                            <div id="table-pagination" class="d-flex flex-wrap gap-2 justify-content-end mt-2"></div>
                        </div>
                        <!-- /.card-body -->
                        <div class="card-footer">Tabel Data Produk</div>
                        <!-- /.card-footer -->
                    </div>
                    <!--end::Card-->
                </div>
                <!-- /.col -->
            </div>
            <!--end::Row-->
        </div>
        <!--end::Container-->
    </div>
@endsection

@push('script')
    <script src="https://cdn.jsdelivr.net/npm/tabulator-tables@6.6.1/dist/js/tabulator.min.js" crossorigin="anonymous">
    </script>
    <script>
        // Fungsi Hapus Sementara (Tinggal Anda sesuaikan dengan logic AJAX/Form ke controller)
        function deleteProduct(id) {
            if (confirm('Apakah Anda yakin ingin menghapus produk ini?')) {
                console.log('Menjalankan proses hapus untuk produk dengan ID:', id);
                // Nanti Anda bisa gunakan fetch/axios ke endpoint destroy produk di sini
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const table = new Tabulator('#product-table', {
                ajaxURL: "{{ route('products.api') }}",
                layout: 'fitColumns',
                filterMode: 'local',
                pagination: 'local',
                paginationElement: document.getElementById('table-pagination'),
                paginationSize: 10,
                paginationSizeSelector: [10, 25, 50, 100],
                movableColumns: true,
                columns: [{
                        title: 'ID',
                        field: 'id',
                        width: 80,
                        headerSort: true,
                    },
                    {
                        title: 'Product Name',
                        field: 'name',
                    },
                    {
                        title: 'Price',
                        field: 'price',
                    },
                    {
                        title: 'Stock',
                        field: 'stock',
                        width: 120,
                    },
                    {
                        title: 'Aksi',
                        field: 'id',
                        formatter: function(cell) {
                            var id = cell.getValue();
                            var editUrl = "{{ url('products') }}/" + id + "/edit";

                            // membuat tombol edit dan delete manual di dalam tabulator
                            return `
                            <a href="${editUrl}" class="btn btn-sm btn-success">Edit</a>
                            <button onclick="deleteProduct(${id})" class="btn btn-sm btn-danger">Hapus</button>`;
                        },
                        headerSort: false,
                        borderless: true,
                        width: 200,
                        hozAlign: 'center',
                    },
                ],
            });

            table.on('tableBuilt', () => {
                const grid = document.getElementById('product-table');
                grid.removeAttribute('aria-owns');
                grid.querySelector('.tabulator-tableHolder').setAttribute('role', 'rowgroup');
                grid.querySelector('.tabulator-table').setAttribute('role', 'presentation');
            });

            table.on('renderComplete', () => {
                document.querySelectorAll('#table-pagination button').forEach((button) => {
                    button.classList.add('btn', 'btn-sm', 'btn-outline-secondary');
                });
            });

            document.getElementById('table-filter').addEventListener('input', (e) => {
                const value = e.target.value;
                if (value) {
                    table.setFilter(
                        [{
                            field: 'name',
                            type: 'like',
                            value: value
                        }]);
                } else {
                    table.clearFilter();
                }
            });

            document
                .getElementById('export-csv')
                .addEventListener('click', () => table.download('csv', 'products.csv'));
            document
                .getElementById('export-json')
                .addEventListener('click', () => table.download('json', 'products.json'));
            document
                .getElementById('print-table')
                .addEventListener('click', () => table.print(false, true));
        });
    </script>
@endpush
