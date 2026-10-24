@php
    $icons = [
        'home' =>
            '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon" aria-hidden="true" focusable="false"><path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M5 12l-2 0l9 -9l9 9l-2 0" /><path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" /><path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" /></svg>',
        'box' =>
            '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon" aria-hidden="true" focusable="false"><path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5" /><path d="M12 12l8 -4.5" /><path d="M12 12l0 9" /><path d="M12 12l-8 -4.5" /></svg>',
        'clipboard-list' =>
            '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon" aria-hidden="true" focusable="false"><path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2" /><path d="M9 5a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2" /><path d="M9 12l.01 0" /><path d="M13 12l2 0" /><path d="M9 16l.01 0" /><path d="M13 16l2 0" /></svg>',
        'users' =>
            '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon" aria-hidden="true" focusable="false"><path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M5 7a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /><path d="M21 21v-2a4 4 0 0 0 -3 -3.85" /></svg>',
        'settings' =>
            '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon" aria-hidden="true" focusable="false"><path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065" /><path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" /></svg>',
        'file-type-doc' =>
            '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon" aria-hidden="true" focusable="false"><path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M5 12v-7a2 2 0 0 1 2 -2h7l5 5v4" /><path d="M5 15v6h1a2 2 0 0 0 2 -2v-2a2 2 0 0 0 -2 -2h-1" /><path d="M20 16.5a1.5 1.5 0 0 0 -3 0v3a1.5 1.5 0 0 0 3 0" /><path d="M12.5 15a1.5 1.5 0 0 1 1.5 1.5v3a1.5 1.5 0 0 1 -3 0v-3a1.5 1.5 0 0 1 1.5 -1.5" /></svg>',
    ];

    $primaryNav = [
        [
            'title' => 'Beranda',
            'url' => route('dashboard'),
            'icon' => $icons['home'],
            'active' => request()->routeIs('dashboard'),
        ],
    ];

    if (auth()->check() && auth()->user()->isAdmin()) {
        $primaryNav[] = [
            'title' => 'Master Aset',
            'url' => 'javascript:void(0)',
            'icon' => $icons['box'],
            'active' => request()->routeIs('admin.assets.*', 'admin.asset-categories.*', 'admin.rooms.*'),
            'children' => [
                [
                    'title' => 'Data Aset',
                    'url' => route('admin.assets.index'),
                    'active' => request()->routeIs('admin.assets.*'),
                ],
                [
                    'title' => 'Kategori Aset',
                    'url' => route('admin.asset-categories.index'),
                    'active' => request()->routeIs('admin.asset-categories.*'),
                ],
                [
                    'title' => 'Data Ruangan',
                    'url' => route('admin.rooms.index'),
                    'active' => request()->routeIs('admin.rooms.*'),
                ],
            ],
        ];
    }

    $primaryNav[] = [
        'title' => 'Tiket Perbaikan',
        'url' => route('repair-tickets.index'),
        'icon' => $icons['clipboard-list'],
        'active' => request()->routeIs('repair-tickets.*'),
    ];

    if (auth()->check() && auth()->user()->isAdmin()) {
        $primaryNav[] = [
            'title' => 'Data User',
            'url' => route('admin.users.index'),
            'icon' => $icons['users'],
            'active' => request()->routeIs('admin.users.*'),
        ];
    }

    $sideNav = [
        [
            'title' => 'Pengaturan SPK',
            'url' => route('admin.criterias.index'),
            'icon' => $icons['settings'],
            'active' => request()->routeIs('admin.criterias.*'),
        ],
        [
            'title' => 'Dokumentasi',
            'url' => '#',
            'icon' => $icons['file-type-doc'],
            'active' => false,
        ],
    ];
@endphp

<aside class="navbar navbar-vertical navbar-expand-lg d-print-none">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu"
            aria-controls="sidebar-menu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <h1 class="navbar-brand navbar-brand-autodark">
            <a href="{{ url('/') }}" class="d-flex align-items-center gap-2 text-decoration-none">
                <span class="avatar avatar-sm bg-primary text-white rounded">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon" aria-hidden="true" focusable="false">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5" />
                        <path d="M12 12l8 -4.5" />
                        <path d="M12 12l0 9" />
                        <path d="M12 12l-8 -4.5" />
                    </svg>
                </span>
                <span class="d-flex flex-column text-start">
                    <span class="fw-bold tracking-tight text-body lh-1">SIAset</span>
                    <span class="text-secondary font-monospace" style="font-size: 0.65rem;">SPK SAW</span>
                </span>
            </a>
        </h1>

        <div class="navbar-nav flex-row order-lg-last d-lg-none">
            <div class="d-flex me-2 align-items-center">
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

        <nav class="collapse navbar-collapse" id="sidebar-menu">
            <ul class="navbar-nav pt-lg-3">
                @foreach ($primaryNav as $item)
                    @if (!empty($item['children']))
                        <li class="nav-item dropdown {{ $item['active'] ? 'active' : '' }}">
                            <a class="nav-link dropdown-toggle {{ $item['active'] ? 'show' : '' }}"
                                href="{{ $item['url'] }}" data-bs-toggle="dropdown"
                                data-bs-auto-close="outside" role="button"
                                aria-expanded="{{ $item['active'] ? 'true' : 'false' }}">
                                <span class="nav-link-icon">
                                    {!! $item['icon'] !!}
                                </span>
                                <span class="nav-link-title">{{ $item['title'] }}</span>
                            </a>
                            <div class="dropdown-menu {{ $item['active'] ? 'show' : '' }}">
                                @foreach ($item['children'] as $child)
                                    <a class="dropdown-item {{ $child['active'] ? 'active' : '' }}"
                                        href="{{ $child['url'] }}">
                                        {{ $child['title'] }}
                                    </a>
                                @endforeach
                            </div>
                        </li>
                    @else
                        <li class="nav-item {{ $item['active'] ? 'active' : '' }}">
                            <a class="nav-link" href="{{ $item['url'] }}">
                                <span class="nav-link-icon">
                                    {!! $item['icon'] !!}
                                </span>
                                <span class="nav-link-title">{{ $item['title'] }}</span>
                            </a>
                        </li>
                    @endif
                @endforeach
            </ul>

            <div class="mt-auto">
                <div class="nav-divider my-2"></div>
                <ul class="navbar-nav">
                    @foreach ($sideNav as $item)
                        <li class="nav-item {{ $item['active'] ? 'active' : '' }}">
                            <a class="nav-link" href="{{ $item['url'] }}">
                                <span class="nav-link-icon">
                                    {!! $item['icon'] !!}
                                </span>
                                <span class="nav-link-title">{{ $item['title'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </nav>
    </div>
</aside>
