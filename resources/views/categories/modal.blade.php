<!-- Modal Create Category -->
<div class="modal fade" id="createCategoryModal" tabindex="-1" aria-labelledby="createCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('categories.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="createCategoryModalLabel">Tambah Kategori</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3 row">
                    <label for="name" class="col-sm-3 col-form-label">Nama Kategori</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="name" name="name" placeholder="Nama Kategori" required autofocus>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="description" class="col-sm-3 col-form-label">Deskripsi</label>
                    <div class="col-sm-9">
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Deskripsi kategori (opsional)"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Category -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="editCategoryForm" method="POST" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title" id="editCategoryModalLabel">Edit Kategori</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body d-flex flex-column gap-3">
                <div>
                    <label for="edit_name" class="form-label">Nama Kategori</label>
                    <input type="text" id="edit_name" name="name" class="form-control" placeholder="Nama Kategori" required>
                </div>
                <div>
                    <label for="edit_description" class="form-label">Deskripsi</label>
                    <textarea id="edit_description" name="description" class="form-control" rows="3" placeholder="Deskripsi kategori (opsional)"></textarea>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="submit" class="btn btn-primary w-100">Update</button>
            </div>
        </form>
    </div>
</div>
