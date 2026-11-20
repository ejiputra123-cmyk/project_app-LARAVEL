@extends('templates.layoutApp')

@push('style')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tabulator-tables@6.6.1/dist/css/tabulator_bootstrap5.min.css"
        crossorigin="anonymous">
@endpush

@section('modal')
    @include('products.modal')
@endsection

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

            <!-- Form Hapus Produk -->
            <form id="deleteProductForm" method="POST" class="d-none">
                @csrf
                @method('DELETE')
            </form>

            <!--begin::Row-->
            <div class="row">
                <div class="col-12">
                    <!--begin::Card-->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Data Produk</h3>

                            <br>
                            <!-- Tombol Modal Create Product -->
                            <button type="button" class="btn btn-primary mt-2" data-bs-toggle="modal"
                                data-bs-target="#createProductModal">
                                <i class="bi bi-plus-circle me-1" aria-hidden="true"></i>
                                PRODUCT
                            </button>

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
        // Fungsi Hapus Produk
        function deleteProduct(id) {
            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('deleteProductForm');
                    form.action = `/products/${id}`;
                    form.submit();
                }
            });
        }

        // Fungsi Edit (Menampilkan Modal dan Mengisi Form)
        function editProduct(encodedData) {
            const product = JSON.parse(decodeURIComponent(encodedData));

            // Isi nilai form modal
            document.getElementById('edit_name').value = product.name;
            document.getElementById('edit_price').value = product.price;
            document.getElementById('edit_stock').value = product.stock;
            document.getElementById('edit_description').value = product.description || '';

            // Ubah action form ke endpoint update yang sesuai dengan id produk
            document.getElementById('editProductForm').action = `/products/${product.id}`;

            // Tampilkan modal edit
            new bootstrap.Modal(document.getElementById('editProductModal')).show();
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
                        headerSort: true
                    },
                    {
                        title: 'Image',
                        field: 'image',
                        formatter: 'image',
                        formatterParams: {
                            height: '50px',
                            width: '50px',
                            urlPrefix: '/storage/',
                        },
                        width: 100,
                        hozAlign: 'center',
                    },
                    {
                        title: 'Product Name',
                        field: 'name'
                    },
                    {
                        title: 'Price',
                        field: 'price'
                    },
                    {
                        title: 'Stock',
                        field: 'stock',
                        width: 120
                    },
                    {
                        title: 'Aksi',
                        field: 'id',
                        headerSort: false,
                        hozAlign: 'center',
                        formatter: (cell) => {
                            const rowData = cell.getRow().getData();
                            // Konversi objek row data ke string dan encode agar aman disisipkan ke attribut HTML
                            const dataStr = encodeURIComponent(JSON.stringify(rowData));
                            return `
                            <button onclick="editProduct('${dataStr}')" class="btn btn-sm btn-success">Edit</button>
                            <button onclick="deleteProduct(${rowData.id})" class="btn btn-sm btn-danger">Hapus</button>`;
                        }
                    }
                ],
            });

            table.on('tableBuilt', () => {
                const grid = document.getElementById('product-table');
                grid.removeAttribute('aria-owns');
                grid.querySelector('.tabulator-tableHolder').setAttribute('role', 'rowgroup');
                grid.querySelector('.tabulator-table').setAttribute('role', 'presentation');
            });

            table.on('renderComplete', () => {
                document.querySelectorAll('#table-pagination button').forEach(btn => btn.classList.add(
                    'btn', 'btn-sm', 'btn-outline-secondary'));
            });

            document.getElementById('table-filter').addEventListener('input', (e) => {
                e.target.value ? table.setFilter('name', 'like', e.target.value) : table.clearFilter();
            });

            document.getElementById('export-csv').addEventListener('click', () => table.download('csv',
                'products.csv'));
            document.getElementById('export-json').addEventListener('click', () => table.download('json',
                'products.json'));
            document.getElementById('print-table').addEventListener('click', () => table.print(false, true));
        });
    </script>
@endpush
