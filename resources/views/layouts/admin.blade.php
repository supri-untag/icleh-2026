<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <title>@yield('title', 'Admin - ICLEH 2026')</title>
    @vite(['resources/scss/admin.scss', 'resources/js/admin.js'])
</head>
<body class="admin-shell">
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
                    <a class="position-relative btn-icon btn-sm btn-light btn rounded-circle" data-bs-toggle="dropdown" aria-expanded="false" href="#" role="button">
                        <i class="ti ti-bell fs-5"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger mt-2 ms-n2">2</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-md p-0">
                        <ul class="list-unstyled p-0 m-0">
                            <li class="p-3 border-bottom">
                                <div class="d-flex gap-3">
                                    <img src="{{ asset('images/admin/avatar/avatar-1.jpg') }}" alt="" class="avatar avatar-sm rounded-circle">
                                    <div class="flex-grow-1 small">
                                        <p class="mb-0 fw-semibold">Payment queue</p>
                                        <p class="mb-1 text-secondary">Waiting for finance verification</p>
                                    </div>
                                </div>
                            </li>
                            <li class="p-3 border-bottom">
                                <div class="d-flex gap-3">
                                    <img src="{{ asset('images/admin/avatar/avatar-4.jpg') }}" alt="" class="avatar avatar-sm rounded-circle">
                                    <div class="flex-grow-1 small">
                                        <p class="mb-0 fw-semibold">Submission review</p>
                                        <p class="mb-1 text-secondary">Scientific committee decision list</p>
                                    </div>
                                </div>
                            </li>
                            <li class="px-4 py-3 text-center">
                                <a href="{{ route('admin.system.mail_logs') }}" class="text-primary">View system logs</a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li>
                    <a href="{{ route('home') }}" class="btn btn-sm btn-light rounded-circle" title="Public site">
                        <i class="ti ti-world fs-5"></i>
                    </a>
                </li>
                <li class="ms-3 dropdown">
                    <a href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="{{ asset('images/admin/avatar/avatar-1.jpg') }}" alt="" class="avatar avatar-sm rounded-circle">
                    </a>
                    <div class="dropdown-menu dropdown-menu-end p-0" style="min-width: 220px;">
                        <div class="d-flex gap-3 align-items-center border-bottom px-3 py-3">
                            <img src="{{ asset('images/admin/avatar/avatar-1.jpg') }}" alt="" class="avatar avatar-md rounded-circle">
                            <div>
                                <h4 class="mb-0 small">{{ auth()->user()->name }}</h4>
                                <p class="mb-0 small text-secondary">{{ auth()->user()->email }}</p>
                            </div>
                        </div>
                        <div class="p-2">
                            <a class="dropdown-item rounded" href="{{ route('participant.dashboard') }}"><i class="ti ti-layout-dashboard me-2"></i>Participant Portal</a>
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
            <a href="{{ route('admin.dashboard') }}" class="d-inline-flex align-items-center text-decoration-none">
                <img src="{{ asset('favicon.ico') }}" alt="" width="24" height="24">
                <span class="logo-text ms-2 fw-bold text-dark">ICLEH Admin</span>
            </a>
        </div>
        @php
            $crudResource = request()->route('resource');
        @endphp
        <ul class="nav flex-column">
            <li class="px-4 py-2"><small class="nav-text text-uppercase text-secondary">Main</small></li>
            <li><a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><i class="ti ti-home"></i><span class="nav-text">Dashboard</span></a></li>

            <li class="px-4 pt-4 pb-2"><small class="nav-text text-uppercase text-secondary">Conference</small></li>
            <li><a class="nav-link {{ request()->routeIs('admin.conference.dates') || $crudResource === 'dates' ? 'active' : '' }}" href="{{ route('admin.conference.dates') }}"><i class="ti ti-calendar-event"></i><span class="nav-text">Important Dates</span></a></li>
            <li><a class="nav-link {{ request()->routeIs('admin.conference.fees') || $crudResource === 'fees' ? 'active' : '' }}" href="{{ route('admin.conference.fees') }}"><i class="ti ti-cash"></i><span class="nav-text">Registration Fees</span></a></li>
            <li><a class="nav-link {{ request()->routeIs('admin.conference.topics') || $crudResource === 'topics' ? 'active' : '' }}" href="{{ route('admin.conference.topics') }}"><i class="ti ti-category"></i><span class="nav-text">Topics</span></a></li>
            <li><a class="nav-link {{ request()->routeIs('admin.conference.speakers') || $crudResource === 'speakers' ? 'active' : '' }}" href="{{ route('admin.conference.speakers') }}"><i class="ti ti-microphone-2"></i><span class="nav-text">Speakers</span></a></li>

            <li class="px-4 pt-4 pb-2"><small class="nav-text text-uppercase text-secondary">Content</small></li>
            <li><a class="nav-link {{ request()->routeIs('admin.content.pages') || $crudResource === 'pages' ? 'active' : '' }}" href="{{ route('admin.content.pages') }}"><i class="ti ti-files"></i><span class="nav-text">Pages</span></a></li>
            <li><a class="nav-link {{ request()->routeIs('admin.content.sections') || $crudResource === 'sections' ? 'active' : '' }}" href="{{ route('admin.content.sections') }}"><i class="ti ti-layout-list"></i><span class="nav-text">Page Sections</span></a></li>
            <li><a class="nav-link {{ request()->routeIs('admin.content.announcements') || $crudResource === 'announcements' ? 'active' : '' }}" href="{{ route('admin.content.announcements') }}"><i class="ti ti-speakerphone"></i><span class="nav-text">Announcements</span></a></li>
            <li><a class="nav-link {{ request()->routeIs('admin.content.faqs') || $crudResource === 'faqs' ? 'active' : '' }}" href="{{ route('admin.content.faqs') }}"><i class="ti ti-help-circle"></i><span class="nav-text">FAQ</span></a></li>
            <li><a class="nav-link {{ request()->routeIs('admin.content.partners') || $crudResource === 'partners' ? 'active' : '' }}" href="{{ route('admin.content.partners') }}"><i class="ti ti-building-community"></i><span class="nav-text">Partners</span></a></li>

            <li class="px-4 pt-4 pb-2"><small class="nav-text text-uppercase text-secondary">Participants</small></li>
            <li><a class="nav-link {{ request()->routeIs('admin.participants.registrations') ? 'active' : '' }}" href="{{ route('admin.participants.registrations') }}"><i class="ti ti-users"></i><span class="nav-text">Registrations</span></a></li>
            <li><a class="nav-link {{ request()->routeIs('admin.participants.payments') || request()->routeIs('admin.payments.index') ? 'active' : '' }}" href="{{ route('admin.participants.payments') }}"><i class="ti ti-credit-card"></i><span class="nav-text">Payments</span></a></li>

            <li class="px-4 pt-4 pb-2"><small class="nav-text text-uppercase text-secondary">Submissions</small></li>
            <li><a class="nav-link {{ request()->routeIs('admin.submissions.abstracts') ? 'active' : '' }}" href="{{ route('admin.submissions.abstracts') }}"><i class="ti ti-file-text"></i><span class="nav-text">Abstracts</span></a></li>

            <li class="px-4 pt-4 pb-2"><small class="nav-text text-uppercase text-secondary">System</small></li>
            <li><a class="nav-link {{ request()->routeIs('admin.system.users') || $crudResource === 'users' ? 'active' : '' }}" href="{{ route('admin.system.users') }}"><i class="ti ti-user-cog"></i><span class="nav-text">Users</span></a></li>
            <li><a class="nav-link {{ request()->routeIs('admin.system.mail_logs') ? 'active' : '' }}" href="{{ route('admin.system.mail_logs') }}"><i class="ti ti-mail"></i><span class="nav-text">Email Logs</span></a></li>
            <li><a class="nav-link {{ request()->routeIs('admin.system.audit_logs') ? 'active' : '' }}" href="{{ route('admin.system.audit_logs') }}"><i class="ti ti-shield-check"></i><span class="nav-text">Audit Logs</span></a></li>
        </ul>
    </aside>

    <main id="content" class="content py-10">
        <div class="container-fluid">
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
