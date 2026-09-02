<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Page;
use App\Services\ConferenceContext;
use App\Services\DocumentVerificationService;
use Illuminate\View\View;

class PublicPageController extends Controller
{
    public function __construct(
        private ConferenceContext $conferenceContext,
        private DocumentVerificationService $documentVerificationService,
    ) {}

    public function home(): View
    {
        $conference = $this->conferenceContext->currentWithPublicRelations();
        $page = Page::query()->with('sections')->where('slug', 'home')->first();

        return view('public.home', [
            'conference' => $conference,
            'page' => $page,
        ]);
    }

    public function page(string $slug): View
    {
        $conference = $this->conferenceContext->currentWithPublicRelations();
        $page = Page::query()
            ->with('sections')
            ->where('slug', $slug)
            ->where('published', true)
            ->first();

        return view('public.page', [
            'conference' => $conference,
            'page' => $page,
            'slug' => $slug,
        ]);
    }

    public function announcements(): View
    {
        $conference = $this->conferenceContext->current();
        $announcements = Announcement::query()
            ->whereBelongsTo($conference)
            ->published()
            ->latest('published_at')
            ->paginate(10);

        return view('public.announcements', [
            'conference' => $conference,
            'announcements' => $announcements,
        ]);
    }

    public function announcement(Announcement $announcement): View
    {
        $announcement->loadMissing('conference');

        abort_unless($announcement->published, 404);

        return view('public.announcement-show', [
            'conference' => $announcement->conference,
            'announcement' => $announcement,
        ]);
    }

    public function verifyLoa(string $code): View
    {
        return view('public.verify-loa', [
            'loa' => $this->documentVerificationService->loa($code),
            'code' => $code,
        ]);
    }

    public function verifyCertificate(string $code): View
    {
        return view('public.verify-certificate', [
            'certificate' => $this->documentVerificationService->certificate($code),
            'code' => $code,
        ]);
    }
}
