<!-- Modal Detail Transaksi -->
<div class="modal fade" id="detailTransactionModal" tabindex="-1" aria-labelledby="detailTransactionModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailTransactionModalLabel">Detail Transaksi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2 mb-3 small">
                    <div class="col-md-6">
                        <div><span class="text-body-secondary">No. Invoice:</span> <strong id="detail-invoice">-</strong></div>
                        <div><span class="text-body-secondary">Tanggal:</span> <strong id="detail-date">-</strong></div>
                        <div><span class="text-body-secondary">Kasir:</span> <strong id="detail-cashier">-</strong></div>
                    </div>
                    <div class="col-md-6">
                        <div><span class="text-body-secondary">Member:</span> <strong id="detail-member">-</strong></div>
                        <div><span class="text-body-secondary">Metode Bayar:</span> <span id="detail-payment">-</span></div>
                        <div><span class="text-body-secondary">Status:</span> <span id="detail-status">-</span></div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-3">
                        <caption class="visually-hidden">Daftar item transaksi</caption>
                        <thead class="table-secondary">
                            <tr>
                                <th scope="col">Produk</th>
                                <th scope="col" class="text-end">Harga</th>
                                <th scope="col" class="text-center">Qty</th>
                                <th scope="col" class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="detail-items"></tbody>
                    </table>
                </div>

                <div class="row justify-content-end">
                    <div class="col-md-6 small d-flex flex-column gap-1">
                        <div class="d-flex justify-content-between">
                            <span class="text-body-secondary">Total</span>
                            <strong id="detail-total">Rp 0</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-body-secondary">Dibayar</span>
                            <span id="detail-paid">Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-body-secondary">Kembalian</span>
                            <span class="fw-semibold text-success" id="detail-change">Rp 0</span>
                        </div>
                    </div>
                </div>

                <div id="detail-notes-wrapper" class="mt-3 p-2 rounded bg-body-tertiary small d-none">
                    <span class="text-body-secondary fw-semibold">Catatan:</span>
                    <span id="detail-notes"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <a href="#" id="detail-receipt-link" target="_blank" rel="noopener" class="btn btn-primary">
                    <i class="bi bi-printer me-1" aria-hidden="true"></i>
                    Cetak Struk
                </a>
            </div>
        </div>
    </div>
</div>
