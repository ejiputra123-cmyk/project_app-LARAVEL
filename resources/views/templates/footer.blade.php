 <!--begin::Footer-->
      <footer class="app-footer">
        <!--begin::To the end-->
        <div class="float-end d-none d-sm-inline">.</div>
        <!--end::To the end-->
        <!--begin::Copyright-->
        <strong>
          Copyright &copy; 2026&nbsp;
          <a href="https://adminlte.io" class="text-decoration-none">ZieMarket</a>.
        </strong>
        All rights reserved.
        <!--end::Copyright-->
      </footer>
      <!--end::Footer-->
    </div>
    <!--end::App Wrapper-->
    @yield('modal')
    <!--begin::Script-->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <script
      src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.16.0/browser/overlayscrollbars.browser.es6.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Required Plugin(popperjs for Bootstrap 5)-->
    <script
      src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(popperjs for Bootstrap 5)--><!--begin::Required Plugin(Bootstrap 5)-->
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(Bootstrap 5)--><!--begin::Required Plugin(AdminLTE)-->
    <script src="{{ asset('assets/js/adminlte.js') }}"></script>
    <!--end::Required Plugin(AdminLTE)-->
    <!--begin::OverlayScrollbars Configure-->
    <script>
      const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
      const Default = {
        scrollbarTheme: 'os-theme-light',
        scrollbarAutoHide: 'leave',
        scrollbarClickScroll: true,
      };
      document.addEventListener('DOMContentLoaded', function () {
        const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);

        // Disable OverlayScrollbars on mobile devices to prevent touch interference
        const isMobile = window.innerWidth <= 992;

        if (
          sidebarWrapper &&
          globalThis.OverlayScrollbarsGlobal?.OverlayScrollbars !== undefined &&
          !isMobile
        ) {
          OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
            scrollbars: {
              theme: Default.scrollbarTheme,
              autoHide: Default.scrollbarAutoHide,
              clickScroll: Default.scrollbarClickScroll,
            },
          });
        }
      });
    </script>
    <!--end::OverlayScrollbars Configure-->

    <!--begin::Sidebar Dropdown State Persistence-->
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const STORAGE_KEY = 'ziemarket-open-menus';
        let openMenus = [];
        try {
          openMenus = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
        } catch {
          openMenus = [];
        }

        // Pulihkan dropdown yang sebelumnya dibuka
        document.querySelectorAll('#navigation > li[data-menu-id]').forEach((item) => {
          const id = item.getAttribute('data-menu-id');
          if (openMenus.includes(id) && !item.classList.contains('menu-open')) {
            item.classList.add('menu-open');
          }

          // Simpan perubahan status saat dropdown diklik
          const trigger = item.querySelector(':scope > a.nav-link');
          if (trigger) {
            trigger.addEventListener('click', () => {
              setTimeout(() => {
                const currentOpen = Array.from(
                  document.querySelectorAll('#navigation > li[data-menu-id].menu-open')
                ).map((el) => el.getAttribute('data-menu-id'));
                try {
                  localStorage.setItem(STORAGE_KEY, JSON.stringify(currentOpen));
                } catch {}
              }, 50);
            });
          }
        });
      });
    </script>
    <!--end::Sidebar Dropdown State Persistence-->

    <!--begin::SweetAlert2-->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @if (session('success'))
      <script>
        document.addEventListener('DOMContentLoaded', function () {
          Swal.fire({
            title: "Success!",
            text: @json(session('success')),
            icon: "success"
          });
        });
      </script>
    @endif
    <!--end::SweetAlert2-->

    <!--begin::Color Mode Toggle-->
    @stack('script')
    <!--end::Color Mode Toggle-->
    <!--end::Script-->
  </body>
  <!--end::Body-->
</html>
