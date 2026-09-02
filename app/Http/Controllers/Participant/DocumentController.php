<?php

namespace App\Http\Controllers\Participant;

use App\Http\Controllers\Controller;
use App\Models\LoaDocument;
use App\Services\ConferenceContext;
use Illuminate\View\View;

class DocumentController extends Controller
{
    public function __construct(private ConferenceContext $conferenceContext) {}

    public function index(): View
    {
        $conference = $this->conferenceContext->current();
        $user = request()->user()->load([
            'registrations' => fn ($query) => $query->whereBelongsTo($conference)->with('payment'),
            'submissions' => fn ($query) => $query->whereBelongsTo($conference)->with('loaDocument'),
        ]);

        return view('participant.documents', [
            'conference' => $conference,
            'registration' => $user->registrations->first(),
            'submissions' => $user->submissions,
        ]);
    }

    public function loa(LoaDocument $loaDocument): View
    {
        $loaDocument->loadMissing(['submission.conference', 'submission.user', 'submission.authors']);

        abort_unless((int) $loaDocument->submission->user_id === (int) request()->user()->id, 404);

        return view('documents.loa', [
            'loa' => $loaDocument,
        ]);
    }
}
