@extends('layouts.admin')

@section('title', $title.' - Admin ICLEH 2026')

@section('content')
    <div class="mb-4 d-flex flex-wrap justify-content-between align-items-end gap-3">
        <div>
            <h1 class="fs-3 mb-1">{{ $title }}</h1>
            <p class="text-secondary mb-0">{{ $description }}</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @isset($createUrl)
                <a class="btn btn-primary" href="{{ $createUrl }}">
                    <i class="ti ti-plus me-1"></i>Create
                </a>
            @endisset
            <button class="btn btn-outline-secondary" type="button" onclick="document.querySelector('[data-admin-table]')._dtInstance.ajax.reload(null, false)">
                <i class="ti ti-refresh me-1"></i>Reload
            </button>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table
                    class="table table-hover align-middle w-100"
                    data-admin-table
                    data-ajax-url="{{ $ajaxUrl }}"
                    data-columns='@json($columns)'
                    data-reload-ms="45000"
                ></table>
            </div>
        </div>
    </div>
@endsection
