@extends('layouts.admin')

@section('title', $title.' - Admin ICLEH 2026')

@section('content')
    <div class="mb-4 d-flex flex-wrap justify-content-between align-items-end gap-3">
        <div>
            <h1 class="fs-3 mb-1">{{ $title }}</h1>
            <p class="text-secondary mb-0">{{ $description }}</p>
        </div>
        <a class="btn btn-outline-secondary" href="{{ $indexUrl }}">
            <i class="ti ti-arrow-left me-1"></i>Back
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            Please check the highlighted fields.
        </div>
    @endif

    <form method="POST" action="{{ $action }}" class="card border-0 shadow-sm" enctype="multipart/form-data">
        @csrf
        @if ($method !== 'POST')
            @method($method)
        @endif

        <div class="card-body">
            <div class="row g-3">
                @foreach ($fields as $field)
                    @php
                        $name = $field['name'];
                        $type = $field['type'] ?? 'text';
                        $value = old($name, data_get($values, $name));
                        $inputId = 'field-'.str_replace(['[', ']', '_'], '-', $name);
                    @endphp

                    <div class="{{ $field['col'] ?? 'col-12' }}">
                        @if ($type === 'checkbox')
                            <input type="hidden" name="{{ $name }}" value="0">
                            <div class="form-check border rounded p-3 h-100">
                                <input
                                    class="form-check-input @error($name) is-invalid @enderror"
                                    type="checkbox"
                                    id="{{ $inputId }}"
                                    name="{{ $name }}"
                                    value="1"
                                    @checked((bool) $value)
                                >
                                <label class="form-check-label fw-semibold" for="{{ $inputId }}">
                                    {{ $field['label'] }}
                                </label>
                                @isset($field['help'])
                                    <div class="form-text">{{ $field['help'] }}</div>
                                @endisset
                                @error($name)
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        @else
                            <label class="form-label fw-semibold" for="{{ $inputId }}">
                                {{ $field['label'] }}
                                @if ($field['required'] ?? false)
                                    <span class="text-danger">*</span>
                                @endif
                            </label>

                            @switch($type)
                                @case('textarea')
                                    <textarea
                                        class="form-control @error($name) is-invalid @enderror"
                                        id="{{ $inputId }}"
                                        name="{{ $name }}"
                                        rows="{{ $field['rows'] ?? 4 }}"
                                        @required($field['required'] ?? false)
                                    >{{ $value }}</textarea>
                                    @break

                                @case('select')
                                    <select
                                        class="form-select @error($name) is-invalid @enderror"
                                        id="{{ $inputId }}"
                                        name="{{ $name }}"
                                        @required($field['required'] ?? false)
                                    >
                                        @isset($field['placeholder'])
                                            <option value="">{{ $field['placeholder'] }}</option>
                                        @endisset
                                        @foreach (($field['options'] ?? []) as $optionValue => $optionLabel)
                                            <option value="{{ $optionValue }}" @selected((string) $value === (string) $optionValue)>
                                                {{ $optionLabel }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @break

                                @case('multiselect')
                                    @php
                                        $selectedValues = collect((array) $value)
                                            ->map(fn ($selectedValue) => (string) $selectedValue)
                                            ->all();
                                    @endphp
                                    <select
                                        class="form-select js-select2 @error($name) is-invalid @enderror"
                                        id="{{ $inputId }}"
                                        name="{{ $name }}[]"
                                        multiple
                                    >
                                        @foreach (($field['options'] ?? []) as $optionValue => $optionLabel)
                                            <option value="{{ $optionValue }}" @selected(in_array((string) $optionValue, $selectedValues, true))>
                                                {{ $optionLabel }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @break

                                @case('file')
                                    <input
                                        class="form-control @error($name) is-invalid @enderror"
                                        id="{{ $inputId }}"
                                        type="file"
                                        name="{{ $name }}"
                                        @isset($field['accept']) accept="{{ $field['accept'] }}" @endisset
                                        @required($field['required'] ?? false)
                                    >
                                    @if (data_get($values, $name.'_url'))
                                        <div class="mt-3 d-flex align-items-center gap-3">
                                            <img
                                                src="{{ data_get($values, $name.'_url') }}"
                                                alt="{{ $field['label'] }}"
                                                class="rounded object-fit-cover"
                                                style="width: 84px; height: 84px;"
                                            >
                                            <div class="small text-secondary">
                                                Current file: {{ data_get($values, $name) }}
                                            </div>
                                        </div>
                                    @endif
                                    @break

                                @default
                                    <input
                                        class="form-control @error($name) is-invalid @enderror"
                                        id="{{ $inputId }}"
                                        type="{{ $type }}"
                                        name="{{ $name }}"
                                        value="{{ $type === 'password' ? '' : $value }}"
                                        @required($field['required'] ?? false)
                                    >
                            @endswitch

                            @isset($field['help'])
                                <div class="form-text">{{ $field['help'] }}</div>
                            @endisset
                            @error($name)
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card-footer bg-white d-flex justify-content-end gap-2">
            <a class="btn btn-light" href="{{ $indexUrl }}">Cancel</a>
            <button class="btn btn-primary" type="submit">
                <i class="ti ti-device-floppy me-1"></i>{{ $submitLabel }}
            </button>
        </div>
    </form>
@endsection
