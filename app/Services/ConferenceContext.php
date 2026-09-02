<?php

namespace App\Services;

use App\Models\Conference;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ConferenceContext
{
    public function current(): Conference
    {
        $conference = Conference::query()
            ->active()
            ->latest('start_date')
            ->first();

        if (! $conference) {
            throw new ModelNotFoundException('No active conference has been configured.');
        }

        return $conference;
    }

    public function currentWithPublicRelations(): Conference
    {
        return $this->current()->load([
            'dates' => fn ($query) => $query->where('visible', true)->orderBy('display_order'),
            'topics' => fn ($query) => $query->active()->orderBy('display_order'),
            'registrationFees' => fn ($query) => $query->active()->orderBy('amount'),
            'speakers' => fn ($query) => $query->active()->orderBy('display_order'),
            'venue',
            'days' => fn ($query) => $query->orderBy('display_order'),
            'days.schedules' => fn ($query) => $query->published()->with(['chamber', 'speaker', 'submission'])->orderBy('start_time'),
            'faqs' => fn ($query) => $query->where('active', true)->orderBy('display_order'),
            'partners' => fn ($query) => $query->where('active', true)->orderBy('display_order'),
            'announcements' => fn ($query) => $query->published()->latest('published_at'),
        ]);
    }
}
