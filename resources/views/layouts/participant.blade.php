<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <title>@yield('title', 'Participant Dashboard - ICLEH 2026')</title>
    @vite(['resources/css/app.css', 'resources/scss/admin.scss', 'resources/js/admin.js'])
</head>
<body class="admin-shell">
    @php
        $user = auth()->user();
        $participantMenu = [
            ['route' => 'participant.dashboard', 'active' => 'participant.dashboard', 'icon' => 'ti-layout-dashboard', 'label' => 'Dashboard'],
            ['route' => 'participant.profile', 'active' => 'participant.profile', 'icon' => 'ti-user-circle', 'label' => 'My Profile'],
            ['route' => 'participant.registration', 'active' => 'participant.registration', 'icon' => 'ti-clipboard-list', 'label' => 'Registration'],
            ['route' => 'participant.payment', 'active' => 'participant.payment', 'icon' => 'ti-credit-card', 'label' => 'Payment'],
            ['route' => 'participant.submissions', 'active' => 'participant.submissions*', 'icon' => 'ti-file-text', 'label' => 'My Submission'],
            ['route' => 'participant.loa', 'active' => 'participant.loa*', 'icon' => 'ti-file-certificate', 'label' => 'Letter of Acceptance'],
            ['route' => 'participant.program', 'active' => 'participant.program', 'icon' => 'ti-calendar-event', 'label' => 'Conference Program'],
            ['route' => 'participant.attendance', 'active' => 'participant.attendance', 'icon' => 'ti-qrcode', 'label' => 'Attendance / QR'],
            ['route' => 'participant.certificates', 'active' => 'participant.certificates', 'icon' => 'ti-certificate', 'label' => 'Certificate'],
            ['route' => 'participant.notifications', 'active' => 'participant.notifications', 'icon' => 'ti-bell', 'label' => 'Notifications'],
        ];
    @endphp

    <div id="overlay" class="overlay"></div>

    <nav id="topbar" class="navbar bg-white border-bottom fixed-top topbar px-3">
        <button id="toggleBtn" class="d-none d-lg-inline-flex btn btn-light btn-icon btn-sm" title="Toggle sidebar">
            <i class="ti ti-layout-sidebar-left-expand"></i>
        </button>
        <button id="mobileBtn" class="btn btn-light btn-icon btn-sm d-lg-none me-2" title="Open sidebar">
            <i class="ti ti-layout-sidebar-left-expand"></i>
        </button>

        <div class="ms-auto">
            <ul class="list-unstyled d-flex align-items-center mb-0 gap-1">
                <li>
                    <a href="{{ route('home') }}" class="btn btn-sm btn-light rounded-circle" title="Public site">
                        <i class="ti ti-world fs-5"></i>
                    </a>
                </li>
                @if ($user?->canAccessAdmin())
                    <li>
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-light rounded-circle" title="Admin dashboard">
                            <i class="ti ti-settings fs-5"></i>
                        </a>
                    </li>
                @endif
                <li class="ms-3 dropdown">
                    <a href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="{{ asset('images/admin/avatar/avatar-1.jpg') }}" alt="" class="avatar avatar-sm rounded-circle">
                    </a>
                    <div class="dropdown-menu dropdown-menu-end p-0" style="min-width: 220px;">
                        <div class="d-flex gap-3 align-items-center border-bottom px-3 py-3">
                            <img src="{{ asset('images/admin/avatar/avatar-1.jpg') }}" alt="" class="avatar avatar-md rounded-circle">
                            <div>
                                <h4 class="mb-0 small">{{ $user->name }}</h4>
                                <p class="mb-0 small text-secondary">{{ $user->email }}</p>
                            </div>
                        </div>
                        <div class="p-2">
                            <a class="dropdown-item rounded" href="{{ route('participant.profile') }}"><i class="ti ti-user-circle me-2"></i>My Profile</a>
                            <a class="dropdown-item rounded" href="{{ route('home') }}"><i class="ti ti-world me-2"></i>Public Site</a>
                            @if ($user->canAccessAdmin())
                                <a class="dropdown-item rounded" href="{{ route('admin.dashboard') }}"><i class="ti ti-settings me-2"></i>Admin Dashboard</a>
                            @endif
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="dropdown-item rounded"><i class="ti ti-logout me-2"></i>Logout</button>
                            </form>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </nav>

    <aside id="sidebar" class="sidebar">
        <div class="logo-area">
            <a href="{{ route('participant.dashboard') }}" class="d-inline-flex align-items-center text-decoration-none">
                <img src="{{ asset('favicon.ico') }}" alt="" width="24" height="24">
                <span class="logo-text ms-2 fw-bold text-dark">ICLEH Portal</span>
            </a>
        </div>
        <ul class="nav flex-column">
            <li class="px-4 py-2"><small class="nav-text text-uppercase text-secondary">Participant</small></li>
            @foreach ($participantMenu as $item)
                <li>
                    <a class="nav-link {{ request()->routeIs($item['active']) ? 'active' : '' }}" href="{{ route($item['route']) }}">
                        <i class="ti {{ $item['icon'] }}"></i>
                        <span class="nav-text">{{ $item['label'] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </aside>

    <main id="content" class="content py-10">
        <div class="container-fluid">
            <div class="mb-4">
                <p class="text-uppercase text-primary fw-semibold small mb-1">Participant Portal</p>
                <h1 class="fs-3 mb-0">@yield('heading', 'Dashboard')</h1>
            </div>
            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif
            @yield('content')
        </div>
    </main>
</body>
</html>
