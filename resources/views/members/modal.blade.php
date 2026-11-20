<!-- Modal Create Member -->
<div class="modal fade" id="createMemberModal" tabindex="-1" aria-labelledby="createMemberModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('members.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="createMemberModalLabel">Tambah Member</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3 row">
                    <label for="member_code" class="col-sm-3 col-form-label">Kode Member</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="member_code" name="member_code" placeholder="Otomatis jika dikosongkan (misal: MBR-0001)">
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="name" class="col-sm-3 col-form-label">Nama Member</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="name" name="name" placeholder="Nama Lengkap" required autofocus>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="phone" class="col-sm-3 col-form-label">No. Telepon</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="phone" name="phone" placeholder="08xxxxxxxxxx" required>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="address" class="col-sm-3 col-form-label">Alamat</label>
                    <div class="col-sm-9">
                        <textarea class="form-control" id="address" name="address" rows="3" placeholder="Alamat member (opsional)"></textarea>
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

<!-- Modal Edit Member -->
<div class="modal fade" id="editMemberModal" tabindex="-1" aria-labelledby="editMemberModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="editMemberForm" method="POST" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title" id="editMemberModalLabel">Edit Member</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body d-flex flex-column gap-3">
                <div>
                    <label for="edit_member_code" class="form-label">Kode Member</label>
                    <input type="text" id="edit_member_code" name="member_code" class="form-control" placeholder="Kode Member" required>
                </div>
                <div>
                    <label for="edit_name" class="form-label">Nama Member</label>
                    <input type="text" id="edit_name" name="name" class="form-control" placeholder="Nama Lengkap" required>
                </div>
                <div>
                    <label for="edit_phone" class="form-label">No. Telepon</label>
                    <input type="text" id="edit_phone" name="phone" class="form-control" placeholder="No. Telepon" required>
                </div>
                <div>
                    <label for="edit_address" class="form-label">Alamat</label>
                    <textarea id="edit_address" name="address" class="form-control" rows="3" placeholder="Alamat member (opsional)"></textarea>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="submit" class="btn btn-primary w-100">Update</button>
            </div>
        </form>
    </div>
</div>
