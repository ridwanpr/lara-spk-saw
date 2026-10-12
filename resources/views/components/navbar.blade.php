@php
    $primaryNav = [
        [
            'title' => 'Beranda',
            'url' => route('dashboard'),
            'icon' => asset('assets/icon/home.svg'),
            'active' => request()->routeIs('dashboard'),
        ],
    ];

    if (auth()->check() && auth()->user()->isAdmin()) {
        $primaryNav[] = [
            'title' => 'Master Aset',
            'url' => 'javascript:void(0)',
            'icon' => asset('assets/icon/box.svg'),
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
        'url' => '#',
        'icon' => asset('assets/icon/clipboard-list.svg'),
        'active' => false,
    ];

    $primaryNav[] = [
        'title' => 'Riwayat',
        'url' => '#',
        'icon' => asset('assets/icon/history.svg'),
        'active' => false,
    ];

    if (auth()->check() && auth()->user()->isAdmin()) {
        $primaryNav[] = [
            'title' => 'Data User',
            'url' => route('admin.users.index'),
            'icon' => asset('assets/icon/users.svg'),
            'active' => request()->routeIs('admin.users.*'),
        ];
    }

    $sideNav = [
        [
            'title' => 'Pengaturan SPK',
            'url' => route('admin.criterias.index'),
            'icon' => asset('assets/icon/settings.svg'),
            'active' => false,
        ],
        [
            'title' => 'Dokumentasi',
            'url' => '#',
            'icon' => asset('assets/icon/file-type-doc.svg'),
            'active' => false,
        ],
    ];
@endphp

<div class="navbar-expand-md">
    <div class="collapse navbar-collapse" id="navbar-menu">
        <div class="navbar">
            <div class="container-xl">
                <div class="row flex-column flex-md-row flex-fill align-items-center">
                    <div class="col">
                        <!-- BEGIN NAVBAR MENU -->
                        <nav aria-label="Primary">
                            <ul class="navbar-nav">
                                @foreach ($primaryNav as $item)
                                    @if (!empty($item['children']))
                                        <li class="nav-item dropdown {{ $item['active'] ?? false ? 'active' : '' }}">
                                            <a class="nav-link dropdown-toggle" href="{{ $item['url'] ?? '#' }}"
                                                data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button"
                                                aria-expanded="false">
                                                <span class="nav-link-icon">
                                                    <img src="{{ $item['icon'] }}" alt="{{ $item['title'] }}"
                                                        class="icon" width="24" height="24">
                                                </span>
                                                <span class="nav-link-title">{{ $item['title'] }}</span>
                                            </a>
                                            <div class="dropdown-menu">
                                                @foreach ($item['children'] as $child)
                                                    <a class="dropdown-item {{ $child['active'] ?? false ? 'active' : '' }}"
                                                        href="{{ $child['url'] ?? '#' }}">
                                                        {{ $child['title'] }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        </li>
                                    @else
                                        <li class="nav-item {{ $item['active'] ?? false ? 'active' : '' }}">
                                            <a class="nav-link" href="{{ $item['url'] ?? '#' }}">
                                                <span class="nav-link-icon">
                                                    <img src="{{ $item['icon'] }}" alt="{{ $item['title'] }}"
                                                        class="icon" width="24" height="24">
                                                </span>
                                                <span class="nav-link-title">{{ $item['title'] }}</span>
                                            </a>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </nav>
                        <!-- END NAVBAR MENU -->
                    </div>

                    <div class="col col-md-auto">
                        <!-- BEGIN NAVBAR SIDE -->
                        <div class="navbar-side">
                            <ul class="navbar-nav">
                                @foreach ($sideNav as $item)
                                    <li class="nav-item {{ $item['active'] ?? false ? 'active' : '' }}">
                                        <a class="nav-link" href="{{ $item['url'] ?? '#' }}">
                                            <span class="nav-link-icon">
                                                <img src="{{ $item['icon'] }}" class="icon" width="24"
                                                    height="24" alt="{{ $item['title'] }}" />
                                            </span>
                                            <span class="nav-link-title">{{ $item['title'] }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <!-- END NAVBAR SIDE -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
