      <!--begin::Header-->
      <nav class="app-header navbar navbar-expand bg-body">
        <!--begin::Container-->
        <div class="container-fluid">
          <!--begin::Start Navbar Links-->
          <ul class="navbar-nav">
            <li class="nav-item">
              <a class="nav-link"
                data-lte-toggle="sidebar"
                href="#"
                role="button"
                aria-label="Toggle sidebar">
                <i class="bi bi-list"></i>
              </a>
            </li>
          </ul>
          <!--end::Start Navbar Links-->

          <!--begin::Navbar Search-->
          <form
            class="navbar-search d-none d-xl-block ms-3"
            role="search"
            action="./pages/search-results.html"
          >
            <label for="navbar-search-input" class="visually-hidden">Search</label>
            <div class="navbar-search-field">
              <input
                type="search"
                id="navbar-search-input"
                name="q"
                class="form-control"
                placeholder="Search…"
                autocomplete="off"
              />
              <button class="navbar-search-submit" type="submit" aria-label="Submit search">
                <i class="bi bi-search" aria-hidden="true"></i>
              </button>
            </div>
          </form>
          <!--end::Navbar Search-->

          <!--begin::End Navbar Links-->
          <ul class="navbar-nav ms-auto">
            <!--begin::Search (small screens: the field above is hidden, so link to the search page)-->
            <li class="nav-item d-xl-none">
              <a class="nav-link" href="./pages/search-results.html" aria-label="Search">
                <i class="bi bi-search" aria-hidden="true"></i>
              </a>
            </li>
            <!--end::Search-->

            <!--begin::Fullscreen Toggle-->
            <li class="nav-item">
              <a
                class="nav-link"
                href="#"
                data-lte-toggle="fullscreen"
                aria-label="Toggle fullscreen"
              >
                <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen"></i>
                <i data-lte-icon="minimize" class="bi bi-fullscreen-exit d-none"></i>
              </a>
            </li>
            <!--end::Fullscreen Toggle-->

            <!--begin::Color Mode Toggle (#6010)-->
            <li class="nav-item dropdown">
              <a
                class="nav-link"
                href="#"
                id="bd-theme"
                aria-label="Toggle color scheme"
                data-bs-toggle="dropdown"
                aria-expanded="false"
              >
                <i class="bi bi-sun-fill" data-lte-theme-icon="light"></i>
                <i class="bi bi-moon-fill d-none" data-lte-theme-icon="dark"></i>
                <i class="bi bi-circle-half d-none" data-lte-theme-icon="auto"></i>
              </a>
              <ul
                class="dropdown-menu dropdown-menu-end"
                aria-labelledby="bd-theme"
                style="--bs-dropdown-min-width: 8rem"
              >
                <li>
                  <button
                    type="button"
                    class="dropdown-item d-flex align-items-center"
                    data-bs-theme-value="light"
                    aria-pressed="false"
                  >
                    <i class="bi bi-sun-fill me-2"></i>
                    Light
                    <i class="bi bi-check-lg ms-auto d-none"></i>
                  </button>
                </li>
                <li>
                  <button
                    type="button"
                    class="dropdown-item d-flex align-items-center"
                    data-bs-theme-value="dark"
                    aria-pressed="false"
                  >
                    <i class="bi bi-moon-fill me-2"></i>
                    Dark
                    <i class="bi bi-check-lg ms-auto d-none"></i>
                  </button>
                </li>
                <li>
                  <button
                    type="button"
                    class="dropdown-item d-flex align-items-center active"
                    data-bs-theme-value="auto"
                    aria-pressed="true"
                  >
                    <i class="bi bi-circle-half me-2"></i>
                    Auto
                    <i class="bi bi-check-lg ms-auto d-none"></i>
                  </button>
                </li>
              </ul>
            </li>
            <!--end::Color Mode Toggle-->

            <!--begin::User Menu Dropdown-->
            <li class="nav-item dropdown user-menu">
              <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                <img
                  src="{{ asset('assets/assets/img/user2-160x160.jpg') }}"
                  class="user-image rounded-circle shadow"
                  alt="{{ Auth::user()->name ?? 'User' }}"
                />
                <span class="d-none d-xl-inline">{{ Auth::user()->name ?? 'User' }}</span>
              </a>
              <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                <!--begin::User Image-->
                <li class="user-header text-bg-primary">
                  <img
                    src="{{ asset('assets/assets/img/user2-160x160.jpg') }}"
                    class="rounded-circle shadow"
                    alt="{{ Auth::user()->name ?? 'User' }}"
                  />
                  <p>
                    {{ Auth::user()->name ?? 'User' }}
                    <small>{{ Auth::user()->email ?? '' }}</small>
                  </p>
                </li>
                <!--end::User Image-->
                <!--begin::Menu Body-->
                <li class="user-body">
                  <!--begin::Row-->
                  <div class="row">
                    <div class="col-4 text-center">
                      <a href="#">Followers</a>
                    </div>
                    <div class="col-4 text-center">
                      <a href="#">Sales</a>
                    </div>
                    <div class="col-4 text-center">
                      <a href="#">Friends</a>
                    </div>
                  </div>
                  <!--end::Row-->
                </li>
                <!--end::Menu Body-->
                <!--begin::Menu Footer-->
                <li class="user-footer">
                  <a href="#" class="btn btn-outline-secondary">Profile</a>
                  <form action="{{ route('logout') }}" method="POST" class="d-inline float-end">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger">Sign out</button>
                  </form>
                </li>
                <!--end::Menu Footer-->
              </ul>
            </li>
            <!--end::User Menu Dropdown-->
          </ul>
          <!--end::End Navbar Links-->
        </div>
        <!--end::Container-->
      </nav>
      <!--end::Header-->
      <!--begin::Sidebar-->
      <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
        <!--begin::Sidebar Brand-->
        <div class="sidebar-brand">
          <!--begin::Brand Link-->
          <a href="./index.html" class="brand-link">
            <!--begin::Brand Image-->
            <img
              src="{{ asset('assets/assets/img/AdminLTELogo.png') }}"
              alt="AdminLTE Logo"
              class="brand-image opacity-75 shadow"
            />
            <!--end::Brand Image-->
            <!--begin::Brand Text-->
            <span class="brand-text fw-light">ZieMarket</span>
            <!--end::Brand Text-->
          </a>
          <!--end::Brand Link-->
        </div>
        <!--end::Sidebar Brand-->
        <!--begin::Sidebar Search-->
        <div class="sidebar-search" role="search">
          <label for="sidebar-search-input" class="visually-hidden">Filter menu</label>
          <input
            type="search"
            id="sidebar-search-input"
            class="form-control form-control-sm"
            placeholder="Filter menu…"
            autocomplete="off"
            data-lte-toggle="sidebar-search"
            data-lte-target="#navigation"
          />
          <p class="fs-7 text-secondary mt-2 mb-0" data-lte-search-empty role="status" hidden>
            No matching pages.
          </p>
        </div>
        <!--end::Sidebar Search-->
        <!--begin::Sidebar Wrapper-->
        <div class="sidebar-wrapper">
          <nav class="mt-2" aria-label="Main navigation">
            <!--begin::Sidebar Menu-->
            @php
              $isMasterData = request()->routeIs('members.*', 'categories.*', 'products.*', 'users.*');
              $isTransactions = request()->routeIs('transactions.*');
            @endphp
            <ul
              class="nav sidebar-menu flex-column"
              data-lte-toggle="treeview"
              data-accordion="false"
              id="navigation"
            >
              @can('admin')
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="nav-icon bi bi-speedometer"></i>
                  <p>
                    Dashboard
                  </p>
                </a>
              </li>
              @endcan
              <li class="nav-item {{ $isMasterData ? 'menu-open' : '' }}" data-menu-id="master-data">
                <a href="#" class="nav-link {{ $isMasterData ? 'active' : '' }}">
                  <i class="nav-icon bi bi-box-seam-fill"></i>
                  <p>
                    Master Data
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  @can('admin')
                  <li class="nav-item">
                    <a href="{{ route('members.index') }}" class="nav-link {{ request()->routeIs('members.*') ? 'active' : '' }}" @if(request()->routeIs('members.*')) aria-current="page" @endif>
                      <i class="nav-icon bi {{ request()->routeIs('members.*') ? 'bi-circle-fill' : 'bi-circle' }}"></i>
                      <p>Member</p>
                    </a>
                  </li>
                  @endcan
                  <li class="nav-item">
                    <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}" @if(request()->routeIs('categories.*')) aria-current="page" @endif>
                      <i class="nav-icon bi {{ request()->routeIs('categories.*') ? 'bi-circle-fill' : 'bi-circle' }}"></i>
                      <p>Categories</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}" @if(request()->routeIs('products.*')) aria-current="page" @endif>
                      <i class="nav-icon bi {{ request()->routeIs('products.*') ? 'bi-circle-fill' : 'bi-circle' }}"></i>
                      <p>Products</p>
                    </a>
                  </li>
                  @can('admin')
                  <li class="nav-item">
                    <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" @if(request()->routeIs('users.*')) aria-current="page" @endif>
                      <i class="nav-icon bi {{ request()->routeIs('users.*') ? 'bi-circle-fill' : 'bi-circle' }}"></i>
                      <p>User</p>
                    </a>
                  </li>
                  @endcan
                </ul>
              </li>
              <li class="nav-item {{ $isTransactions ? 'menu-open' : '' }}" data-menu-id="transactions">
                <a href="#" class="nav-link {{ $isTransactions ? 'active' : '' }}">
                  <i class="nav-icon bi bi-shop"></i>
                  <p>
                    Transactions
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="{{ route('transactions.order') }}" class="nav-link {{ request()->routeIs('transactions.order') ? 'active' : '' }}" @if(request()->routeIs('transactions.order')) aria-current="page" @endif>
                      <i class="nav-icon bi {{ request()->routeIs('transactions.order') ? 'bi-circle-fill' : 'bi-circle' }}"></i>
                      <p>Order</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('transactions.history') }}" class="nav-link {{ request()->routeIs('transactions.history') ? 'active' : '' }}" @if(request()->routeIs('transactions.history')) aria-current="page" @endif>
                      <i class="nav-icon bi {{ request()->routeIs('transactions.history') ? 'bi-circle-fill' : 'bi-circle' }}"></i>
                      <p>History</p>
                    </a>
                  </li>
                </ul>
              </li>
              @can('admin')
              <li class="nav-item {{ request()->routeIs('reports.*') ? 'menu-open' : '' }}" data-menu-id="reports">
                <a href="#" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                  <i class="nav-icon bi bi-clipboard"></i>
                  <p>
                    Reports
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="./UI/general.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Transactions</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('reports.products') }}" class="nav-link {{ request()->routeIs('reports.products') ? 'active' : '' }}" @if(request()->routeIs('reports.products')) aria-current="page" @endif>
                      <i class="nav-icon bi {{ request()->routeIs('reports.products') ? 'bi-circle-fill' : 'bi-circle' }}"></i>
                      <p>Products</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./UI/timeline.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Members</p>
                    </a>
                  </li>
                </ul>
              </li>
              @endcan
            </ul>
            <!--end::Sidebar Menu-->

            <!-- Docs CTA (bottom of sidebar) -->
            <div class="p-3 mt-3 border-top border-secondary border-opacity-25">
              <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button
                  type="submit"
                  class="btn btn-sm btn-danger w-100 d-flex align-items-center justify-content-center gap-2"
                >
                  <i class="bi bi-power" aria-hidden="true"></i>
                  Logout
                </button>
              </form>
            </div>
          </nav>
        </div>
        <!--end::Sidebar Wrapper-->
      </aside>
      <!--end::Sidebar-->