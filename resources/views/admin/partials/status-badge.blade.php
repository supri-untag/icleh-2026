@php
    $badgeClass = match ($tone) {
        'verified', 'confirmed', 'accept', 'success' => 'text-bg-success',
        'submitted', 'waiting', 'warning' => 'text-bg-warning',
        'rejected', 'danger' => 'text-bg-danger',
        default => 'text-bg-primary',
    };
@endphp

<span class="badge {{ $badgeClass }}">{{ $status }}</span>
