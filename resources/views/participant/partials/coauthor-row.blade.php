<fieldset class="border rounded p-3" data-coauthor-row>
    <legend class="float-none w-auto px-2 h6 mb-0">Co-author</legend>
    <div class="row g-3">
        @foreach (['name' => 'Name', 'email' => 'Email', 'affiliation' => 'Affiliation'] as $field => $label)
            <div class="col-12 col-md-6">
                <label class="form-label" for="coauthor-{{ $index }}-{{ $field }}">{{ $label }}</label>
                <input class="form-control @error('authors.'.$index.'.'.$field) is-invalid @enderror" id="coauthor-{{ $index }}-{{ $field }}" name="authors[{{ $index }}][{{ $field }}]" type="{{ $field === 'email' ? 'email' : 'text' }}" value="{{ $author[$field] ?? '' }}" @if ($field === 'email') data-coauthor-email @endif>
                @error('authors.'.$index.'.'.$field)<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        @endforeach
        <div class="col-12 col-md-6">
            <label class="form-label" for="coauthor-{{ $index }}-country">Country</label>
            <select class="form-select js-select2 @error('authors.'.$index.'.country') is-invalid @enderror" id="coauthor-{{ $index }}-country" name="authors[{{ $index }}][country]">
                <option value="">Select country</option>
                @foreach ($countries as $country)
                    <option value="{{ $country->name }}" @selected(($author['country'] ?? 'Indonesia') === $country->name)>{{ $country->name }}</option>
                @endforeach
            </select>
            @error('authors.'.$index.'.country')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
        <div class="col-12 d-flex align-items-center justify-content-between gap-3">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="coauthor-{{ $index }}-participant" name="authors[{{ $index }}][participant]" value="1" data-coauthor-participant @checked($author['participant'] ?? false)>
                <label class="form-check-label" for="coauthor-{{ $index }}-participant">Participant — will be registered automatically, and the invoice will be generated automatically.</label>
            </div>
            <button type="button" class="btn btn-outline-danger btn-sm" data-remove-coauthor>Remove</button>
        </div>
    </div>
</fieldset>
