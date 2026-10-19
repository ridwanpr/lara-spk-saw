<header class="navbar navbar-expand-md d-none d-lg-flex d-print-none">
    <div class="container-xl">
        <div class="navbar-nav flex-row order-md-last align-items-center ms-auto">
            <div class="d-none d-md-flex me-2">
                <a href="?theme=dark" class="nav-link px-0 hide-theme-dark" title="Aktifkan mode gelap"
                    data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-theme-value="dark"
                    aria-label="Aktifkan mode gelap">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="icon" aria-hidden="true" focusable="false">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M12 3c.132 0 .263 0 .393 0a7.5 7.5 0 0 0 7.92 12.446a9 9 0 1 1 -8.313 -12.454z" />
                    </svg>
                </a>
                <a href="?theme=light" class="nav-link px-0 hide-theme-light" title="Aktifkan mode terang"
                    data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-theme-value="light"
                    aria-label="Aktifkan mode terang">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="icon" aria-hidden="true" focusable="false">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M12 12m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" />
                        <path
                            d="M3 12h1m8 -9v1m8 8h1m-9 8v1m-6.4 -15.4l.7 .7m12.1 -.7l-.7 .7m0 11.4l.7 .7m-12.1 -.7l-.7 .7" />
                    </svg>
                </a>
            </div>

            @auth
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link d-flex lh-1 text-reset p-0 px-2 dropdown-toggle"
                        data-bs-toggle="dropdown">
                        <div class="ps-2">
                            <div>{{ auth()->user()->name }}</div>
                            <div class="mt-1 small text-secondary">
                                {{ auth()->user()->role?->name instanceof \App\Enums\RoleName ? auth()->user()->role->name->label() : auth()->user()->role?->name ?? 'Pengguna' }}
                            </div>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                        <div class="d-md-none">
                            <a href="?theme=dark" class="dropdown-item hide-theme-dark" data-bs-theme-value="dark">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="icon dropdown-item-icon" aria-hidden="true"
                                    focusable="false">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path
                                        d="M12 3c.132 0 .263 0 .393 0a7.5 7.5 0 0 0 7.92 12.446a9 9 0 1 1 -8.313 -12.454z" />
                                </svg>
                                Mode Gelap
                            </a>
                            <a href="?theme=light" class="dropdown-item hide-theme-light" data-bs-theme-value="light">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="icon dropdown-item-icon" aria-hidden="true"
                                    focusable="false">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M12 12m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" />
                                    <path
                                        d="M3 12h1m8 -9v1m8 8h1m-9 8v1m-6.4 -15.4l.7 .7m12.1 -.7l-.7 .7m0 11.4l.7 .7m-12.1 -.7l-.7 .7" />
                                </svg>
                                Mode Terang
                            </a>
                            <div class="dropdown-divider"></div>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">Keluar (Logout)</button>
                        </form>
                    </div>
                </div>
            @else
                <div class="nav-item">
                    <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm">Masuk</a>
                </div>
            @endauth
        </div>
    </div>
</header>
