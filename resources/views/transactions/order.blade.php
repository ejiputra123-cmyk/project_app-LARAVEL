@extends('templates.layoutApp')

@push('style')
    <style>
        .pos-product-card {
            cursor: pointer;
            transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
        }

        .pos-product-card:hover:not(.pos-out-of-stock) {
            transform: translateY(-2px);
            border-color: var(--bs-primary) !important;
            box-shadow: var(--bs-box-shadow-sm);
        }

        .pos-product-card.pos-out-of-stock {
            cursor: not-allowed;
            opacity: 0.6;
        }

        .pos-product-thumb {
            height: 115px;
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
                            <h3 class="card-title mb-0">
                                <i class="bi bi-grid me-1" aria-hidden="true"></i>
                                Katalog Produk
                            </h3>
                            <span class="badge text-bg-secondary" id="product-count">0 Produk</span>
                        </div>
                        <div class="card-body">
                            <div class="input-group mb-3">
                                <span class="input-group-text"><i class="bi bi-search" aria-hidden="true"></i></span>
                                <input type="text" id="product-search" class="form-control"
                                    placeholder="Cari nama produk..." autocomplete="off">
                            </div>

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
                            <h3 class="card-title mb-0">
                                <i class="bi bi-cart3 me-1" aria-hidden="true"></i>
                                Keranjang Belanja
                            </h3>
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
                            <div class="mb-3">
                                <label class="form-label fw-semibold d-block">Metode Pembayaran</label>
                                <div class="btn-group w-100" role="group" aria-label="Metode Pembayaran">
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
                            </div>

                            <!-- Nominal Bayar -->
                            <div class="mb-3">
                                <label for="paid_amount" class="form-label fw-semibold">Nominal Bayar</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" id="paid_amount" class="form-control" min="0" step="1"
                                        placeholder="0" value="">
                                    <button type="button" id="btn-exact-amount" class="btn btn-outline-secondary">
                                        Uang Pas
                                    </button>
                                </div>
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
                                <button type="button" id="btn-checkout" class="btn btn-primary w-50" disabled>
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
            const totalDisplay = document.getElementById('total-display');
            const changeDisplay = document.getElementById('change-display');
            const paidInput = document.getElementById('paid_amount');
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

            const getTotalAmount = () => {
                let total = 0;
                cart.forEach((item) => {
                    total += Number(item.product.price) * item.quantity;
                });
                return total;
            };

            const renderProducts = () => {
                const keyword = searchQuery.trim().toLowerCase();
                const filtered = products.filter((p) => p.name.toLowerCase().includes(keyword));

                productCount.textContent = `${filtered.length} Produk`;

                if (filtered.length === 0) {
                    productGrid.innerHTML = '';
                    productEmpty.classList.remove('d-none');
                    return;
                }

                productEmpty.classList.add('d-none');
                productGrid.innerHTML = filtered.map((product) => {
                    const outOfStock = Number(product.stock) <= 0;
                    const inCartQty = cart.get(product.id)?.quantity || 0;
                    const imgUrl = resolveImageUrl(product.image);
                    const stockBadgeClass = outOfStock
                        ? 'text-bg-danger'
                        : (Number(product.stock) <= 5 ? 'text-bg-warning' : 'text-bg-success');

                    const thumbHtml = imgUrl
                        ? `<img src="${escapeHtml(imgUrl)}" alt="${escapeHtml(product.name)}" class="card-img-top pos-product-thumb bg-body-secondary" onerror="this.outerHTML='<div class=&quot;pos-product-thumb bg-body-secondary d-flex align-items-center justify-content-center text-body-secondary&quot;><i class=&quot;bi bi-box-seam fs-1&quot;></i></div>'">`
                        : `<div class="pos-product-thumb bg-body-secondary d-flex align-items-center justify-content-center text-body-secondary"><i class="bi bi-box-seam fs-1" aria-hidden="true"></i></div>`;

                    return `
                        <div class="col">
                            <div class="card h-100 pos-product-card border ${outOfStock ? 'pos-out-of-stock' : ''}"
                                data-product-id="${product.id}" role="button" tabindex="${outOfStock ? '-1' : '0'}"
                                aria-disabled="${outOfStock ? 'true' : 'false'}">
                                <div class="position-relative">
                                    ${thumbHtml}
                                    ${inCartQty > 0 ? `<span class="position-absolute top-0 end-0 m-2 badge rounded-pill text-bg-primary">${inCartQty}</span>` : ''}
                                </div>
                                <div class="card-body p-2 d-flex flex-column justify-content-between">
                                    <h6 class="card-title mb-1 text-truncate" title="${escapeHtml(product.name)}">${escapeHtml(product.name)}</h6>
                                    <div class="d-flex justify-content-between align-items-center gap-1 mt-1">
                                        <span class="fw-bold text-primary small">${formatRupiah(product.price)}</span>
                                        <span class="badge ${stockBadgeClass}">${outOfStock ? 'Habis' : 'Stok: ' + product.stock}</span>
                                    </div>
                                </div>
                            </div>
                        </div>`;
                }).join('');
            };

            const updatePaymentSummary = () => {
                const total = getTotalAmount();
                const paid = parseInt(paidInput.value, 10) || 0;
                const diff = paid - total;

                totalDisplay.textContent = formatRupiah(total);

                if (cart.size === 0) {
                    changeDisplay.textContent = formatRupiah(0);
                    changeDisplay.className = 'fs-5 fw-bold text-body-secondary';
                } else if (diff < 0) {
                    changeDisplay.textContent = `Kurang ${formatRupiah(Math.abs(diff))}`;
                    changeDisplay.className = 'fs-5 fw-bold text-danger';
                } else {
                    changeDisplay.textContent = formatRupiah(diff);
                    changeDisplay.className = 'fs-5 fw-bold text-success';
                }

                btnCheckout.disabled = isSubmitting || cart.size === 0 || paid < total;
            };

            const renderCart = () => {
                let totalQty = 0;
                cart.forEach((item) => {
                    totalQty += item.quantity;
                });
                cartItemCountEl.textContent = `${totalQty} Item`;

                if (cart.size === 0) {
                    cartItemsEl.innerHTML = '';
                    cartEmptyEl.classList.remove('d-none');
                } else {
                    cartEmptyEl.classList.add('d-none');
                    const rows = [];
                    cart.forEach(({ product, quantity }) => {
                        const subtotal = Number(product.price) * quantity;
                        rows.push(`
                            <div class="p-2 rounded border bg-body" data-cart-id="${product.id}">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                    <div>
                                        <div class="fw-semibold small">${escapeHtml(product.name)}</div>
                                        <div class="text-body-secondary" style="font-size: 0.78rem;">${formatRupiah(product.price)} / pcs</div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1 btn-remove-item"
                                        data-id="${product.id}" aria-label="Hapus item">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                                <div class="d-flex justify-content-between align-items-center gap-2">
                                    <div class="input-group input-group-sm" style="width: 115px;">
                                        <button type="button" class="btn btn-outline-secondary btn-qty-dec" data-id="${product.id}">-</button>
                                        <input type="number" class="form-control text-center cart-qty-input"
                                            data-id="${product.id}" value="${quantity}" min="1" max="${product.stock}">
                                        <button type="button" class="btn btn-outline-secondary btn-qty-inc" data-id="${product.id}">+</button>
                                    </div>
                                    <span class="fw-bold small">${formatRupiah(subtotal)}</span>
                                </div>
                            </div>`);
                    });
                    cartItemsEl.innerHTML = rows.join('');
                }

                updatePaymentSummary();
                renderProducts();
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

                renderCart();
            };

            const resetOrder = () => {
                cart.clear();
                memberSelect.value = '';
                notesInput.value = '';
                paidInput.value = '';
                document.getElementById('pay_cash').checked = true;
                renderCart();
            };

            productSearch.addEventListener('input', (e) => {
                searchQuery = e.target.value;
                renderProducts();
            });

            productGrid.addEventListener('click', (e) => {
                const card = e.target.closest('.pos-product-card');
                if (!card || card.classList.contains('pos-out-of-stock')) return;
                addToCart(Number(card.dataset.productId));
            });

            productGrid.addEventListener('keydown', (e) => {
                if (e.key !== 'Enter' && e.key !== ' ') return;
                const card = e.target.closest('.pos-product-card');
                if (!card || card.classList.contains('pos-out-of-stock')) return;
                e.preventDefault();
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
                const val = parseInt(input.value, 10);
                setCartQty(Number(input.dataset.id), Number.isNaN(val) ? 1 : val);
            });

            paidInput.addEventListener('input', updatePaymentSummary);

            btnExact.addEventListener('click', () => {
                paidInput.value = getTotalAmount();
                updatePaymentSummary();
            });

            btnReset.addEventListener('click', resetOrder);

            btnCheckout.addEventListener('click', async () => {
                if (isSubmitting || cart.size === 0) return;

                const total = getTotalAmount();
                const paid = parseInt(paidInput.value, 10) || 0;
                if (paid < total) return;

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

                    // Kurangi stok lokal agar tampilan katalog langsung sinkron.
                    payload.items.forEach(({ product_id, quantity }) => {
                        const p = products.find((item) => Number(item.id) === Number(product_id));
                        if (p) p.stock = Math.max(0, Number(p.stock) - quantity);
                    });

                    resetOrder();

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
                    btnCheckout.innerHTML = '<i class="bi bi-check2-circle me-1" aria-hidden="true"></i>Bayar';
                    updatePaymentSummary();
                }
            });

            renderCart();
        });
    </script>
@endpush
