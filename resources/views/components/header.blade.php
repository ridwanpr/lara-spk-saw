<header class="navbar navbar-expand-sm d-print-none">
    <div class="container-xl">
        <button class="navbar-toggler collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu"
            aria-controls="navbar-menu" aria-expanded="false" aria-label="Toggle primary navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <h1 class="navbar-brand navbar-brand-autodark pe-0 pe-md-3">
            <a href="{{ url('/') }}">SIAset</a>
        </h1>
        <div class="navbar-nav flex-row order-md-last">
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
