<?php

namespace App\DTOs;

use Illuminate\Http\UploadedFile;

class SubmissionData
{
    /**
     * @param  array<int, string>  $keywords
     * @param  array<int, array<string, mixed>>  $authors
     */
    public function __construct(
        public readonly int $conferenceTopicId,
        public readonly string $title,
        public readonly ?string $abstractText,
        public readonly array $keywords,
        public readonly ?string $correspondingAuthor,
        public readonly ?string $affiliations,
        public readonly ?string $country,
        public readonly ?UploadedFile $abstractFile,
        public readonly ?string $notes,
        public readonly array $authors,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $keywords = collect(explode(',', (string) ($data['keywords'] ?? '')))
            ->map(fn (string $keyword): string => trim($keyword))
            ->filter()
            ->values()
            ->all();

        return new self(
            conferenceTopicId: (int) $data['conference_topic_id'],
            title: (string) $data['title'],
            abstractText: $data['abstract_text'] ?? null,
            keywords: $keywords,
            correspondingAuthor: $data['corresponding_author'] ?? null,
            affiliations: $data['affiliations'] ?? null,
            country: $data['country'] ?? null,
            abstractFile: $data['abstract_file'] ?? null,
            notes: $data['notes'] ?? null,
            authors: $data['authors'] ?? [],
        );
    }
}
