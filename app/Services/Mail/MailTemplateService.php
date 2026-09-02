<?php

namespace App\Services\Mail;

use App\Models\EmailTemplate;
use Illuminate\Support\Str;

class MailTemplateService
{
    /**
     * @param  array<string, mixed>  $data
     * @return array{subject: string, html: string, text: string}
     */
    public function render(string $code, string $subject, array $data = [], ?int $conferenceId = null): array
    {
        $template = EmailTemplate::query()
            ->where('code', $code)
            ->where('active', true)
            ->where(function ($query) use ($conferenceId): void {
                $query->whereNull('conference_id');

                if ($conferenceId) {
                    $query->orWhere('conference_id', $conferenceId);
                }
            })
            ->orderByRaw('conference_id is null')
            ->first();

        $html = $template?->body_html
            ?? '<p>Dear {{ participant_name }},</p><p>{{ conference_name }} has an update for you.</p>';

        $text = $template?->body_text ?? strip_tags(str_replace(['</p>', '<br>', '<br/>', '<br />'], "\n", $html));

        return [
            'subject' => $this->replacePlaceholders($template?->subject ?? $subject, $data, false),
            'html' => $this->replacePlaceholders($html, $data, true),
            'text' => $this->replacePlaceholders($text, $data, false),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function replacePlaceholders(string $content, array $data, bool $escapeHtml): string
    {
        return (string) preg_replace_callback('/{{\s*([A-Za-z0-9_]+)\s*}}/', function (array $matches) use ($data, $escapeHtml): string {
            $value = $this->stringValue(data_get($data, $matches[1], ''));

            return $escapeHtml ? e($value) : $value;
        }, $content);
    }

    private function stringValue(mixed $value): string
    {
        if ($value instanceof \BackedEnum) {
            return (string) $value->value;
        }

        if ($value instanceof \Stringable) {
            return (string) $value;
        }

        if (is_scalar($value) || $value === null) {
            return (string) $value;
        }

        return Str::limit(json_encode($value) ?: '', 500);
    }
}
