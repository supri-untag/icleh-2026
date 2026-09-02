<?php

use App\Http\Controllers\Admin\AdminCrudController;
use App\Http\Controllers\Admin\AdminTableController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\PaymentVerificationController;
use App\Http\Controllers\Admin\SubmissionDecisionController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Participant\DashboardController as ParticipantDashboardController;
use App\Http\Controllers\Participant\DocumentController;
use App\Http\Controllers\Participant\PaymentController;
use App\Http\Controllers\Participant\ProfileController;
use App\Http\Controllers\Participant\RegistrationController;
use App\Http\Controllers\Participant\SubmissionController;
use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\SpeakerPhotoController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicPageController::class, 'home'])->name('home');
Route::get('/storage/speakers/{filename}', SpeakerPhotoController::class)
    ->where('filename', '[A-Za-z0-9._-]+')
    ->name('speaker.photo');

foreach ([
    'about',
    'speakers',
    'topics',
    'important-dates',
    'registration',
    'guide-for-authors',
    'program',
    'publication',
    'venue',
    'contact',
    'faq',
] as $publicPage) {
    Route::get('/'.$publicPage, [PublicPageController::class, 'page'])
        ->defaults('slug', $publicPage)
        ->name($publicPage);
}

Route::get('/announcements', [PublicPageController::class, 'announcements'])->name('announcements.index');
Route::get('/announcements/{announcement:slug}', [PublicPageController::class, 'announcement'])->name('announcements.show');
Route::get('/verify/loa/{code}', [PublicPageController::class, 'verifyLoa'])->name('verify.loa');
Route::get('/verify/certificate/{code}', [PublicPageController::class, 'verifyCertificate'])->name('verify.certificate');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:login');
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/dashboard', ParticipantDashboardController::class)->name('participant.dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('participant.profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('participant.profile.update');
    Route::get('/participant/registration', [RegistrationController::class, 'show'])->name('participant.registration');
    Route::post('/participant/registration', [RegistrationController::class, 'store'])->name('participant.registration.store');
    Route::get('/payment', [PaymentController::class, 'show'])->name('participant.payment');
    Route::post('/payment', [PaymentController::class, 'store'])->name('participant.payment.store');
    Route::get('/submissions', [SubmissionController::class, 'index'])->name('participant.submissions');
    Route::get('/submissions/create', [SubmissionController::class, 'create'])->name('participant.submissions.create');
    Route::post('/submissions', [SubmissionController::class, 'store'])->name('participant.submissions.store');
    Route::get('/submissions/{submission}', [SubmissionController::class, 'show'])->name('participant.submissions.show');
    Route::get('/loa', [DocumentController::class, 'index'])->name('participant.loa');
    Route::get('/loa/{loaDocument}', [DocumentController::class, 'loa'])->name('participant.loa.show');
    Route::get('/participant/program', [DocumentController::class, 'index'])->name('participant.program');
    Route::get('/attendance', [DocumentController::class, 'index'])->name('participant.attendance');
    Route::get('/certificates', [DocumentController::class, 'index'])->name('participant.certificates');
    Route::get('/notifications', [DocumentController::class, 'index'])->name('participant.notifications');
});

Route::middleware(['auth', 'verified', 'role:super_admin,admin,scientific_committee,finance,event_committee'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/', AdminDashboardController::class)->name('dashboard');

        Route::get('/participants/registrations', [AdminTableController::class, 'view'])
            ->defaults('table', 'registrations')
            ->name('participants.registrations');
        Route::get('/participants/payments', [AdminTableController::class, 'view'])
            ->defaults('table', 'payments')
            ->name('participants.payments');
        Route::get('/payments', [AdminTableController::class, 'view'])
            ->defaults('table', 'payments')
            ->name('payments.index');
        Route::get('/submissions/abstracts', [AdminTableController::class, 'view'])
            ->defaults('table', 'submissions')
            ->name('submissions.abstracts');
        Route::get('/conference/topics', [AdminTableController::class, 'view'])
            ->defaults('table', 'topics')
            ->name('conference.topics');
        Route::get('/conference/registration-fees', [AdminTableController::class, 'view'])
            ->defaults('table', 'fees')
            ->name('conference.fees');
        Route::get('/conference/important-dates', [AdminTableController::class, 'view'])
            ->defaults('table', 'dates')
            ->name('conference.dates');
        Route::get('/conference/speakers', [AdminTableController::class, 'view'])
            ->defaults('table', 'speakers')
            ->name('conference.speakers');
        Route::get('/content/pages', [AdminTableController::class, 'view'])
            ->defaults('table', 'pages')
            ->name('content.pages');
        Route::get('/content/page-sections', [AdminTableController::class, 'view'])
            ->defaults('table', 'sections')
            ->name('content.sections');
        Route::get('/content/announcements', [AdminTableController::class, 'view'])
            ->defaults('table', 'announcements')
            ->name('content.announcements');
        Route::get('/content/faqs', [AdminTableController::class, 'view'])
            ->defaults('table', 'faqs')
            ->name('content.faqs');
        Route::get('/content/partners', [AdminTableController::class, 'view'])
            ->defaults('table', 'partners')
            ->name('content.partners');
        Route::get('/system/users', [AdminTableController::class, 'view'])
            ->defaults('table', 'users')
            ->name('system.users');
        Route::get('/system/email-logs', [AdminTableController::class, 'view'])
            ->defaults('table', 'mail-logs')
            ->name('system.mail_logs');
        Route::get('/system/audit-logs', [AdminTableController::class, 'view'])
            ->defaults('table', 'audit-logs')
            ->name('system.audit_logs');

        Route::get('/tables/{table}/data', [AdminTableController::class, 'data'])->name('tables.data');
        Route::get('/crud/{resource}/create', [AdminCrudController::class, 'create'])->name('crud.create');
        Route::post('/crud/{resource}', [AdminCrudController::class, 'store'])->name('crud.store');
        Route::get('/crud/{resource}/{record}/edit', [AdminCrudController::class, 'edit'])->name('crud.edit');
        Route::put('/crud/{resource}/{record}', [AdminCrudController::class, 'update'])->name('crud.update');
        Route::delete('/crud/{resource}/{record}', [AdminCrudController::class, 'destroy'])->name('crud.destroy');
        Route::post('/payments/{payment}/verify', [PaymentVerificationController::class, 'verify'])->name('payments.verify');
        Route::post('/payments/{payment}/reject', [PaymentVerificationController::class, 'reject'])->name('payments.reject');
        Route::post('/submissions/{submission}/decision', [SubmissionDecisionController::class, 'update'])->name('submissions.decision');
    });
