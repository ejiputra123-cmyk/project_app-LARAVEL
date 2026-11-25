@extends('templates.layoutApp')

@push('style')
    <style>
        .pos-product-card {
            cursor: pointer;
            transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
        }

        .pos-product-card:hover:not(:disabled) {
            transform: translateY(-2px);
            border-color: var(--bs-primary) !important;
            box-shadow: var(--bs-box-shadow-sm);
        }

        .pos-product-card:disabled {
            cursor: not-allowed;
            opacity: 0.6;
        }

        .pos-product-thumb {
            height: 115px;
            width: 100%;
            object-fit: cover;
        }

        .pos-cart-list {
            max-height: 320px;
            overflow-y: auto;
        }
    </style>
@endpush

@section('content')
    <!--begin::App Content Header-->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="mb-0 fs-3">Order</h1>
                </div>
                <div class="col-sm-6">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item">Transactions</li>
                            <li class="breadcrumb-item active" aria-current="page">Order</li>
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
            <div class="row g-3">
                <!-- Kolom Kiri: Katalog Produk -->
                <div class="col-lg-7">
                    <div class="card h-100">
                        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <h2 class="card-title mb-0 fs-5">
                                <i class="bi bi-grid me-1" aria-hidden="true"></i>
                                Katalog Produk
                            </h2>
                            <span class="badge text-bg-secondary" id="product-count">0 Produk</span>
                        </div>
                        <div class="card-body">
                            <search>
                                <label for="product-search" class="visually-hidden">Cari nama produk</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text"><i class="bi bi-search" aria-hidden="true"></i></span>
                                    <input type="search" id="product-search" class="form-control"
                                        placeholder="Cari nama produk..." autocomplete="off" spellcheck="false">
                                </div>
                            </search>

                            <div id="product-grid" class="row row-cols-2 row-cols-md-3 row-cols-xl-4 g-3"></div>
                            <div id="product-empty" class="text-center text-body-secondary py-5 d-none">
                                <i class="bi bi-inbox fs-1 d-block mb-2" aria-hidden="true"></i>
                                Produk tidak ditemukan.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan: Keranjang & Pembayaran -->
                <div class="col-lg-5">
                    <div class="card sticky-lg-top" style="top: 1rem;">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h2 class="card-title mb-0 fs-5">
                                <i class="bi bi-cart3 me-1" aria-hidden="true"></i>
                                Keranjang Belanja
                            </h2>
                            <span class="badge text-bg-primary" id="cart-item-count">0 Item</span>
                        </div>
                        <div class="card-body">
                            <!-- Pilihan Member -->
                            <div class="mb-3">
                                <label for="member_id" class="form-label fw-semibold">Member</label>
                                <select id="member_id" class="form-select">
                                    <option value="">Umum / Non-Member</option>
                                    @foreach ($members as $member)
                                        <option value="{{ $member->id }}">
                                            {{ $member->member_code }} - {{ $member->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Daftar Item Keranjang -->
                            <div class="border rounded p-2 mb-3 bg-body-tertiary pos-cart-list" id="cart-container">
                                <div id="cart-empty" class="text-center text-body-secondary py-4">
                                    <i class="bi bi-cart-x fs-2 d-block mb-1" aria-hidden="true"></i>
                                    Keranjang masih kosong. Klik produk untuk menambahkan.
                                </div>
                                <div id="cart-items" class="d-flex flex-column gap-2"></div>
                            </div>

                            <!-- Ringkasan Total -->
                            <div class="d-flex justify-content-between align-items-center py-2 px-3 rounded bg-body-secondary mb-3">
                                <span class="fw-semibold">Total Bayar</span>
                                <span class="fs-4 fw-bold text-primary" id="total-display">Rp 0</span>
                            </div>

                            <!-- Metode Pembayaran -->
                            <fieldset class="mb-3">
                                <legend class="form-label fw-semibold fs-6 mb-2">Metode Pembayaran</legend>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="payment_method" id="pay_cash" value="cash" checked>
                                    <label class="btn btn-outline-primary" for="pay_cash">
                                        <i class="bi bi-cash-stack me-1" aria-hidden="true"></i>Cash
                                    </label>

                                    <input type="radio" class="btn-check" name="payment_method" id="pay_qris" value="qris">
                                    <label class="btn btn-outline-primary" for="pay_qris">
                                        <i class="bi bi-qr-code me-1" aria-hidden="true"></i>QRIS
                                    </label>

                                    <input type="radio" class="btn-check" name="payment_method" id="pay_transfer" value="transfer">
                                    <label class="btn btn-outline-primary" for="pay_transfer">
                                        <i class="bi bi-bank me-1" aria-hidden="true"></i>Transfer
                                    </label>
                                </div>
                            </fieldset>

                            <!-- Nominal Bayar -->
                            <div class="mb-3">
                                <label for="paid_amount" class="form-label fw-semibold">Nominal Bayar</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" inputmode="numeric" pattern="[0-9]*" id="paid_amount"
                                        class="form-control" placeholder="0" autocomplete="off"
                                        aria-describedby="paid-amount-error">
                                    <button type="button" id="btn-exact-amount" class="btn btn-outline-secondary">
                                        Uang Pas
                                    </button>
                                </div>
                                <div id="paid-amount-error" class="invalid-feedback d-block small d-none" role="alert"></div>
                            </div>

                            <!-- Kembalian -->
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="fw-semibold">Kembalian</span>
                                <span class="fs-5 fw-bold" id="change-display">Rp 0</span>
                            </div>

                            <!-- Catatan -->
                            <div class="mb-3">
                                <label for="notes" class="form-label fw-semibold">Catatan <small class="text-body-secondary fw-normal">(opsional)</small></label>
                                <textarea id="notes" class="form-control" rows="2" placeholder="Tambahkan catatan transaksi..."></textarea>
                            </div>

                            <!-- Aksi -->
                            <div class="d-flex gap-2">
                                <button type="button" id="btn-reset" class="btn btn-outline-secondary w-50">
                                    <i class="bi bi-arrow-counterclockwise me-1" aria-hidden="true"></i>
                                    Reset
                                </button>
                                <button type="button" id="btn-checkout" class="btn btn-primary w-50">
                                    <i class="bi bi-check2-circle me-1" aria-hidden="true"></i>
                                    Bayar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <template id="cart-item-template">
        <div class="p-2 rounded border bg-body cart-row">
            <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                <div>
                    <div class="fw-semibold small cart-item-name"></div>
                    <div class="text-body-secondary cart-item-price" style="font-size: 0.78rem;"></div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1 btn-remove-item">
                    <i class="bi bi-trash" aria-hidden="true"></i>
                </button>
            </div>
            <div class="d-flex justify-content-between align-items-center gap-2">
                <div class="input-group input-group-sm" style="width: 115px;">
                    <button type="button" class="btn btn-outline-secondary btn-qty-dec">-</button>
                    <input type="text" inputmode="numeric" pattern="[0-9]*"
                        class="form-control text-center cart-qty-input">
                    <button type="button" class="btn btn-outline-secondary btn-qty-inc">+</button>
                </div>
                <span class="fw-bold small cart-item-subtotal"></span>
            </div>
        </div>
    </template>
@endsection

@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const products = @json($products);
            const storeUrl = @json(route('transactions.store'));
            const csrfToken = @json(csrf_token());

            const cart = new Map();
            let searchQuery = '';
            let isSubmitting = false;

            const productGrid = document.getElementById('product-grid');
            const productEmpty = document.getElementById('product-empty');
            const productCount = document.getElementById('product-count');
            const productSearch = document.getElementById('product-search');

            const cartItemsEl = document.getElementById('cart-items');
            const cartEmptyEl = document.getElementById('cart-empty');
            const cartItemCountEl = document.getElementById('cart-item-count');
            const cartTemplate = document.getElementById('cart-item-template');
            const totalDisplay = document.getElementById('total-display');
            const changeDisplay = document.getElementById('change-display');
            const paidInput = document.getElementById('paid_amount');
            const paidErrorEl = document.getElementById('paid-amount-error');
            const memberSelect = document.getElementById('member_id');
            const notesInput = document.getElementById('notes');
            const btnExact = document.getElementById('btn-exact-amount');
            const btnReset = document.getElementById('btn-reset');
            const btnCheckout = document.getElementById('btn-checkout');

            const formatRupiah = (amount) => 'Rp ' + Number(amount || 0).toLocaleString('id-ID');

            const escapeHtml = (str) => String(str ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');

            const resolveImageUrl = (image) => {
                if (!image) return null;
                if (/^https?:\/\//i.test(image)) return image;
                return '/storage/' + image.replace(/^\/+/, '');
            };

            const createThumbPlaceholder = () => {
                const div = document.createElement('div');
                div.className = 'pos-product-thumb bg-body-secondary d-flex align-items-center justify-content-center text-body-secondary';
                const icon = document.createElement('i');
                icon.className = 'bi bi-box-seam fs-1';
                icon.setAttribute('aria-hidden', 'true');
                div.appendChild(icon);
                return div;
            };

            const parsePaidAmount = () => {
                const raw = paidInput.value.trim();
                if (raw === '') return 0;
                return /^\d+$/.test(raw) ? Number(raw) : NaN;
            };

            const clearPaidError = () => {
                paidInput.classList.remove('is-invalid');
                paidInput.removeAttribute('aria-invalid');
                paidErrorEl.textContent = '';
                paidErrorEl.classList.add('d-none');
            };

            const showPaidError = (message) => {
                paidInput.classList.add('is-invalid');
                paidInput.setAttribute('aria-invalid', 'true');
                paidErrorEl.textContent = message;
                paidErrorEl.classList.remove('d-none');
            };

            const getTotalAmount = () => {
                let total = 0;
                cart.forEach((item) => {
                    total += Number(item.product.price) * item.quantity;
                });
                return total;
            };

            const syncProductBadges = () => {
                productGrid.querySelectorAll('.pos-product-card').forEach((card) => {
                    const id = Number(card.dataset.productId);
                    const qty = cart.get(id)?.quantity || 0;
                    const badge = card.querySelector('.pos-cart-badge');
                    if (!badge) return;
                    if (qty > 0) {
                        badge.textContent = String(qty);
                        badge.classList.remove('d-none');
                    } else {
                        badge.textContent = '';
                        badge.classList.add('d-none');
                    }
                });
            };

            const renderProducts = () => {
                const keyword = searchQuery.trim().toLowerCase();
                const filtered = products.filter((p) => p.name.toLowerCase().includes(keyword));

                productCount.textContent = `${filtered.length} Produk`;
                productGrid.replaceChildren();

                if (filtered.length === 0) {
                    productEmpty.classList.remove('d-none');
                    return;
                }

                productEmpty.classList.add('d-none');
                const fragment = document.createDocumentFragment();

                filtered.forEach((product) => {
                    const outOfStock = Number(product.stock) <= 0;
                    const inCartQty = cart.get(product.id)?.quantity || 0;
                    const imgUrl = resolveImageUrl(product.image);
                    const stockBadgeClass = outOfStock
                        ? 'text-bg-danger'
                        : (Number(product.stock) <= 5 ? 'text-bg-warning' : 'text-bg-success');

                    const col = document.createElement('div');
                    col.className = 'col';

                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'card h-100 w-100 text-start p-0 pos-product-card border';
                    btn.dataset.productId = String(product.id);
                    btn.disabled = outOfStock;

                    const thumbWrap = document.createElement('div');
                    thumbWrap.className = 'position-relative w-100 overflow-hidden rounded-top';

                    if (imgUrl) {
                        const img = document.createElement('img');
                        img.src = imgUrl;
                        img.alt = product.name;
                        img.loading = 'lazy';
                        img.className = 'card-img-top pos-product-thumb bg-body-secondary';
                        thumbWrap.appendChild(img);
                    } else {
                        thumbWrap.appendChild(createThumbPlaceholder());
                    }

                    const qtyBadge = document.createElement('span');
                    qtyBadge.className = 'position-absolute top-0 end-0 m-2 badge rounded-pill text-bg-primary pos-cart-badge' + (inCartQty > 0 ? '' : ' d-none');
                    qtyBadge.textContent = inCartQty > 0 ? String(inCartQty) : '';
                    thumbWrap.appendChild(qtyBadge);

                    const body = document.createElement('div');
                    body.className = 'card-body p-2 w-100 d-flex flex-column justify-content-between';

                    const title = document.createElement('span');
                    title.className = 'card-title fw-semibold small mb-1 text-truncate d-block';
                    title.title = product.name;
                    title.textContent = product.name;

                    const meta = document.createElement('div');
                    meta.className = 'd-flex justify-content-between align-items-center gap-1 mt-1';

                    const priceEl = document.createElement('span');
                    priceEl.className = 'fw-bold text-primary small';
                    priceEl.textContent = formatRupiah(product.price);

                    const stockEl = document.createElement('span');
                    stockEl.className = `badge ${stockBadgeClass}`;
                    stockEl.textContent = outOfStock ? 'Habis' : `Stok: ${product.stock}`;

                    meta.append(priceEl, stockEl);
                    body.append(title, meta);
                    btn.append(thumbWrap, body);
                    col.appendChild(btn);
                    fragment.appendChild(col);
                });

                productGrid.appendChild(fragment);
            };

            const updatePaymentSummary = () => {
                const total = getTotalAmount();
                const paid = parsePaidAmount();

                totalDisplay.textContent = formatRupiah(total);

                if (cart.size === 0) {
                    changeDisplay.textContent = formatRupiah(0);
                    changeDisplay.className = 'fs-5 fw-bold text-body-secondary';
                } else if (Number.isNaN(paid)) {
                    changeDisplay.textContent = 'Input tidak valid';
                    changeDisplay.className = 'fs-5 fw-bold text-danger';
                } else if (paid < total) {
                    changeDisplay.textContent = `Kurang ${formatRupiah(total - paid)}`;
                    changeDisplay.className = 'fs-5 fw-bold text-danger';
                } else {
                    changeDisplay.textContent = formatRupiah(paid - total);
                    changeDisplay.className = 'fs-5 fw-bold text-success';
                }
            };

            const renderCart = () => {
                let totalQty = 0;
                cart.forEach((item) => {
                    totalQty += item.quantity;
                });
                cartItemCountEl.textContent = `${totalQty} Item`;
                cartItemsEl.replaceChildren();

                if (cart.size === 0) {
                    cartEmptyEl.classList.remove('d-none');
                } else {
                    cartEmptyEl.classList.add('d-none');
                    const fragment = document.createDocumentFragment();

                    cart.forEach(({ product, quantity }) => {
                        const clone = cartTemplate.content.cloneNode(true);
                        const row = clone.querySelector('.cart-row');
                        row.dataset.cartId = String(product.id);

                        clone.querySelector('.cart-item-name').textContent = product.name;
                        clone.querySelector('.cart-item-price').textContent = `${formatRupiah(product.price)} / pcs`;
                        clone.querySelector('.cart-item-subtotal').textContent = formatRupiah(Number(product.price) * quantity);

                        const removeBtn = clone.querySelector('.btn-remove-item');
                        removeBtn.dataset.id = String(product.id);
                        removeBtn.setAttribute('aria-label', `Hapus ${product.name}`);

                        const decBtn = clone.querySelector('.btn-qty-dec');
                        decBtn.dataset.id = String(product.id);
                        decBtn.setAttribute('aria-label', `Kurangi jumlah ${product.name}`);

                        const incBtn = clone.querySelector('.btn-qty-inc');
                        incBtn.dataset.id = String(product.id);
                        incBtn.setAttribute('aria-label', `Tambah jumlah ${product.name}`);

                        const qtyInput = clone.querySelector('.cart-qty-input');
                        qtyInput.dataset.id = String(product.id);
                        qtyInput.value = String(quantity);
                        qtyInput.setAttribute('aria-label', `Jumlah ${product.name}`);

                        fragment.appendChild(clone);
                    });

                    cartItemsEl.appendChild(fragment);
                }

                updatePaymentSummary();
                syncProductBadges();
            };

            const addToCart = (productId) => {
                const product = products.find((p) => Number(p.id) === Number(productId));
                if (!product || Number(product.stock) <= 0) return;

                const existing = cart.get(product.id);
                const nextQty = existing ? existing.quantity + 1 : 1;

                if (nextQty > Number(product.stock)) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Stok Terbatas',
                        text: `Stok ${product.name} hanya tersisa ${product.stock}.`,
                        timer: 1800,
                        showConfirmButton: false
                    });
                    return;
                }

                cart.set(product.id, { product, quantity: nextQty });
                clearPaidError();
                renderCart();
            };

            const setCartQty = (productId, newQty) => {
                const item = cart.get(Number(productId));
                if (!item) return;

                if (newQty <= 0) {
                    cart.delete(item.product.id);
                } else if (newQty > Number(item.product.stock)) {
                    item.quantity = Number(item.product.stock);
                    Swal.fire({
                        icon: 'warning',
                        title: 'Stok Terbatas',
                        text: `Maksimal stok ${item.product.name} adalah ${item.product.stock}.`,
                        timer: 1800,
                        showConfirmButton: false
                    });
                } else {
                    item.quantity = newQty;
                }

                clearPaidError();
                renderCart();
            };

            const resetOrder = () => {
                cart.clear();
                memberSelect.value = '';
                notesInput.value = '';
                paidInput.value = '';
                document.getElementById('pay_cash').checked = true;
                clearPaidError();
                renderCart();
            };

            // Delegated fallback untuk gambar produk gagal dimuat (tanpa inline onerror/outerHTML).
            productGrid.addEventListener('error', (e) => {
                if (e.target instanceof HTMLImageElement) {
                    e.target.replaceWith(createThumbPlaceholder());
                }
            }, true);

            productSearch.addEventListener('input', (e) => {
                searchQuery = e.target.value;
                renderProducts();
            });

            productGrid.addEventListener('click', (e) => {
                const card = e.target.closest('.pos-product-card');
                if (!card || card.disabled) return;
                addToCart(Number(card.dataset.productId));
            });

            cartItemsEl.addEventListener('click', (e) => {
                const decBtn = e.target.closest('.btn-qty-dec');
                if (decBtn) {
                    const id = Number(decBtn.dataset.id);
                    const item = cart.get(id);
                    if (item) setCartQty(id, item.quantity - 1);
                    return;
                }

                const incBtn = e.target.closest('.btn-qty-inc');
                if (incBtn) {
                    const id = Number(incBtn.dataset.id);
                    const item = cart.get(id);
                    if (item) setCartQty(id, item.quantity + 1);
                    return;
                }

                const removeBtn = e.target.closest('.btn-remove-item');
                if (removeBtn) {
                    cart.delete(Number(removeBtn.dataset.id));
                    renderCart();
                }
            });

            cartItemsEl.addEventListener('change', (e) => {
                const input = e.target.closest('.cart-qty-input');
                if (!input) return;
                const raw = input.value.trim();
                const val = /^\d+$/.test(raw) ? Number(raw) : 1;
                setCartQty(Number(input.dataset.id), val);
            });

            document.querySelectorAll('input[name="payment_method"]').forEach((radio) => {
                radio.addEventListener('change', () => {
                    if (radio.value !== 'cash' && cart.size > 0 && paidInput.value.trim() === '') {
                        paidInput.value = String(getTotalAmount());
                        clearPaidError();
                        updatePaymentSummary();
                    }
                });
            });

            paidInput.addEventListener('input', () => {
                clearPaidError();
                updatePaymentSummary();
            });

            btnExact.addEventListener('click', () => {
                paidInput.value = String(getTotalAmount());
                clearPaidError();
                updatePaymentSummary();
            });

            btnReset.addEventListener('click', resetOrder);

            btnCheckout.addEventListener('click', async () => {
                if (isSubmitting) return;

                if (cart.size === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Keranjang Kosong',
                        text: 'Pilih minimal satu produk sebelum memproses pembayaran.'
                    });
                    productSearch.focus();
                    return;
                }

                const total = getTotalAmount();
                const paid = parsePaidAmount();

                if (Number.isNaN(paid) || paidInput.value.trim() === '') {
                    showPaidError('Masukkan nominal bayar berupa angka bulat.');
                    paidInput.focus();
                    return;
                }

                if (paid < total) {
                    showPaidError(`Nominal bayar kurang ${formatRupiah(total - paid)}.`);
                    paidInput.focus();
                    return;
                }

                clearPaidError();
                const paymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value || 'cash';
                const payload = {
                    member_id: memberSelect.value ? Number(memberSelect.value) : null,
                    payment_method: paymentMethod,
                    paid_amount: paid,
                    notes: notesInput.value.trim() || null,
                    items: Array.from(cart.values()).map(({ product, quantity }) => ({
                        product_id: product.id,
                        quantity
                    }))
                };

                isSubmitting = true;
                btnCheckout.disabled = true;
                btnCheckout.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Memproses...';

                try {
                    const response = await fetch(storeUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify(payload)
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        const firstError = data.errors
                            ? Object.values(data.errors).flat()[0]
                            : (data.message || 'Gagal memproses transaksi.');
                        Swal.fire({
                            icon: 'error',
                            title: 'Transaksi Gagal',
                            text: firstError
                        });
                        return;
                    }

                    payload.items.forEach(({ product_id, quantity }) => {
                        const p = products.find((item) => Number(item.id) === Number(product_id));
                        if (p) p.stock = Math.max(0, Number(p.stock) - quantity);
                    });

                    resetOrder();
                    renderProducts();

                    const order = data.order;
                    const result = await Swal.fire({
                        icon: 'success',
                        title: 'Transaksi Berhasil!',
                        html: `
                            <div class="text-start small border rounded p-3 bg-body-tertiary">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>No. Invoice</span>
                                    <strong>${escapeHtml(order.invoice_number)}</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Total Belanja</span>
                                    <strong>${formatRupiah(order.total_amount)}</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Dibayar</span>
                                    <strong>${formatRupiah(order.paid_amount)}</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Kembalian</span>
                                    <strong class="text-success">${formatRupiah(order.change_amount)}</strong>
                                </div>
                            </div>`,
                        showCancelButton: true,
                        confirmButtonText: '<i class="bi bi-printer me-1"></i>Cetak Struk',
                        cancelButtonText: 'Transaksi Baru',
                        confirmButtonColor: '#0d6efd',
                        cancelButtonColor: '#6c757d'
                    });

                    if (result.isConfirmed && data.receipt_url) {
                        window.open(data.receipt_url, '_blank');
                    }
                } catch (err) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Terjadi Kesalahan',
                        text: 'Tidak dapat terhubung ke server.'
                    });
                } finally {
                    isSubmitting = false;
                    btnCheckout.disabled = false;
                    btnCheckout.innerHTML = '<i class="bi bi-check2-circle me-1" aria-hidden="true"></i>Bayar';
                    updatePaymentSummary();
                }
            });

            renderProducts();
            renderCart();
        });
    </script>
@endpush
