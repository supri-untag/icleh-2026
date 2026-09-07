<?php

namespace App\Http\Requests\Participant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmissionRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $authors = $this->input('authors');

        if (! is_array($authors)) {
            return;
        }

        $authors = array_filter($authors, function (mixed $author): bool {
            if (! is_array($author)) {
                return true;
            }

            return filled($author['name'] ?? null) || filled($author['email'] ?? null)
                || filled($author['affiliation'] ?? null) || ! empty($author['participant']);
        });

        foreach ($authors as &$author) {
            if (is_array($author) && is_string($author['email'] ?? null)) {
                $author['email'] = mb_strtolower(trim($author['email']));
            }
        }

        $this->merge(['authors' => array_values($authors)]);
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'conference_topic_id' => ['required', Rule::exists('conference_topics', 'id')->where('active', true)],
            'title' => ['required', 'string', 'max:255'],
            'abstract_text' => ['nullable', 'string', 'max:10000'],
            'keywords' => ['nullable', 'string', 'max:500'],
            'corresponding_author' => ['nullable', 'string', 'max:255'],
            'affiliations' => ['nullable', 'string', 'max:1000'],
            'country' => ['nullable', 'string', 'max:120'],
            'abstract_file' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'authors' => ['nullable', 'array', 'max:10'],
            'authors.*' => ['array'],
            'authors.*.name' => ['required_with:authors', 'string', 'max:255'],
            'authors.*.email' => ['required_with:authors', 'email', 'max:255', 'distinct:ignore_case'],
            'authors.*.affiliation' => ['nullable', 'string', 'max:255'],
            'authors.*.country' => ['required_if:authors.*.participant,1', 'nullable', 'string', 'max:120', Rule::exists('countries', 'name')->where('active', true)],
            'authors.*.participant' => ['nullable', 'boolean'],
            'authors.*.corresponding_author' => ['nullable', 'boolean'],
            'authors.*.presenter' => ['nullable', 'boolean'],
        ];
    }
}
