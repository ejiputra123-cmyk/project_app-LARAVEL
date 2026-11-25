<!-- Modal Create Product -->
<div class="modal fade" id="createProductModal" tabindex="-1" aria-labelledby="createProductModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="createProductModalLabel">Tambah Produk</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3 row">
                    <label for="name" class="col-sm-3 col-form-label">Product Name</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="name" name="name" required autofocus>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="category_id" class="col-sm-3 col-form-label">Category</label>
                    <div class="col-sm-9">
                        <select class="form-select" id="category_id" name="category_id">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="price" class="col-sm-3 col-form-label">Price</label>
                    <div class="col-sm-9">
                        <input type="number" class="form-control" id="price" name="price" required>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="stock" class="col-sm-3 col-form-label">Stock</label>
                    <div class="col-sm-9">
                        <input type="number" class="form-control" id="stock" name="stock" required>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="description" class="col-sm-3 col-form-label">Description</label>
                    <div class="col-sm-9">
                        <textarea class="form-control" id="description" name="description"></textarea>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="image" class="col-sm-3 col-form-label">Image</label>
                    <div class="col-sm-9">
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Save changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Product -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="editProductForm" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title">Edit Produk</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body d-flex flex-column gap-3">
                <div>
                    <label for="edit_name" class="form-label">Product Name</label>
                    <input type="text" id="edit_name" name="name" class="form-control" placeholder="Product Name" required>
                </div>
                <div>
                    <label for="edit_category_id" class="form-label">Category</label>
                    <select id="edit_category_id" name="category_id" class="form-select">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="edit_price" class="form-label">Price</label>
                    <input type="number" id="edit_price" name="price" class="form-control" placeholder="Price" required>
                </div>
                <div>
                    <label for="edit_stock" class="form-label">Stock</label>
                    <input type="number" id="edit_stock" name="stock" class="form-control" placeholder="Stock" required>
                </div>
                <div>
                    <label for="edit_description" class="form-label">Description</label>
                    <textarea id="edit_description" name="description" class="form-control" placeholder="Description"></textarea>
                </div>
                <div>
                    <label for="edit_image" class="form-label">Image</label>
                    <input type="file" id="edit_image" name="image" class="form-control" accept="image/*">
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="submit" class="btn btn-primary w-100">Update</button>
            </div>
        </form>
    </div>
</div>
