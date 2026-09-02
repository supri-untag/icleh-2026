<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'uuid')) {
                $table->uuid('uuid')->nullable()->unique()->after('id');
            }

            if (! Schema::hasColumn('users', 'whatsapp')) {
                $table->string('whatsapp')->nullable()->after('email');
            }

            if (! Schema::hasColumn('users', 'institution')) {
                $table->string('institution')->nullable()->after('whatsapp');
            }

            if (! Schema::hasColumn('users', 'country')) {
                $table->string('country')->nullable()->after('institution');
            }

            if (! Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('remember_token');
            }
        });

        $this->createTableIfMissing('roles', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name')->unique();
            $table->string('label');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        $this->createTableIfMissing('permissions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name')->unique();
            $table->string('label');
            $table->timestamps();
        });

        $this->createTableIfMissing('permission_role', function (Blueprint $table) {
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->primary(['permission_id', 'role_id']);
        });

        $this->createTableIfMissing('role_user', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->primary(['role_id', 'user_id']);
        });

        $this->createTableIfMissing('conferences', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('edition')->nullable();
            $table->text('theme');
            $table->longText('description')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('timezone')->default('Asia/Jakarta');
            $table->string('mode')->default('hybrid');
            $table->string('venue_name')->nullable();
            $table->string('location')->nullable();
            $table->boolean('registration_requires_verified_payment')->default(false);
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
        });

        $this->createTableIfMissing('conference_settings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('conference_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->json('value')->nullable();
            $table->string('type')->default('string');
            $table->boolean('public')->default(false);
            $table->timestamps();
            $table->unique(['conference_id', 'key'], 'conf_settings_conf_key_unique');
        });

        $this->createTableIfMissing('conference_dates', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('conference_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->date('starts_at');
            $table->date('ends_at')->nullable();
            $table->string('status')->default('upcoming')->index();
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('visible')->default(true);
            $table->timestamps();
            $table->index(['conference_id', 'visible', 'display_order'], 'conf_dates_visible_order_idx');
        });

        $this->createTableIfMissing('conference_topics', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('conference_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->json('keywords')->nullable();
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
            $table->unique(['conference_id', 'slug'], 'conf_topics_conf_slug_unique');
        });

        $this->createTableIfMissing('registration_fees', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('conference_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('participant_type');
            $table->string('attendance_mode')->nullable();
            $table->unsignedBigInteger('amount');
            $table->string('currency', 3)->default('IDR');
            $table->boolean('active')->default(true)->index();
            $table->unsignedInteger('quota')->nullable();
            $table->date('registration_start')->nullable();
            $table->date('registration_end')->nullable();
            $table->timestamps();
            $table->index(['conference_id', 'participant_type', 'attendance_mode'], 'reg_fees_conf_type_mode_idx');
        });

        $this->createTableIfMissing('profiles', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('full_name');
            $table->string('whatsapp')->nullable();
            $table->string('institution')->nullable();
            $table->string('country')->nullable();
            $table->string('participant_type')->nullable();
            $table->string('attendance_mode')->nullable();
            $table->string('status_proof_file')->nullable();
            $table->timestamps();
            $table->unique('user_id');
        });

        $this->createTableIfMissing('registrations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('conference_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('registration_fee_id')->nullable()->constrained()->nullOnDelete();
            $table->string('registration_code')->unique();
            $table->string('participant_type');
            $table->string('attendance_mode')->nullable();
            $table->string('status')->default('draft')->index();
            $table->timestamp('registered_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['conference_id', 'user_id'], 'registrations_conf_user_unique');
            $table->index(['conference_id', 'status', 'created_at'], 'registrations_conf_status_created_idx');
        });

        $this->createTableIfMissing('payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->string('payment_code')->unique();
            $table->string('method')->default('manual_transfer');
            $table->unsignedBigInteger('amount');
            $table->string('currency', 3)->default('IDR');
            $table->string('proof_file')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('waiting')->index();
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['status', 'submitted_at'], 'payments_status_submitted_idx');
        });

        $this->createTableIfMissing('submissions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('conference_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('registration_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('conference_topic_id')->nullable()->constrained()->nullOnDelete();
            $table->string('submission_code')->unique();
            $table->string('title');
            $table->longText('abstract_text')->nullable();
            $table->json('keywords')->nullable();
            $table->string('corresponding_author')->nullable();
            $table->text('affiliations')->nullable();
            $table->string('country')->nullable();
            $table->string('abstract_file')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('draft')->index();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('final_decision_at')->nullable();
            $table->timestamps();
            $table->index(['conference_id', 'status', 'created_at'], 'submissions_conf_status_created_idx');
        });

        $this->createTableIfMissing('submission_authors', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('submission_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('affiliation')->nullable();
            $table->string('country')->nullable();
            $table->boolean('corresponding_author')->default(false);
            $table->boolean('presenter')->default(false);
            $table->unsignedInteger('order')->default(1);
            $table->timestamps();
            $table->index(['submission_id', 'order'], 'submission_authors_submission_order_idx');
        });

        $this->createTableIfMissing('submission_files', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('submission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type')->default('abstract');
            $table->unsignedInteger('version')->default(1);
            $table->string('original_filename');
            $table->string('storage_path');
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->boolean('final')->default(false);
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamps();
            $table->unique(['submission_id', 'type', 'version'], 'submission_files_type_version_unique');
        });

        $this->createTableIfMissing('submission_status_histories', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('submission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        $this->createTableIfMissing('review_assignments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('submission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('due_date')->nullable();
            $table->boolean('blind_review')->default(true);
            $table->string('status')->default('assigned');
            $table->timestamps();
            $table->unique(['submission_id', 'reviewer_id'], 'review_assignments_submission_reviewer_unique');
            $table->index(['reviewer_id', 'status'], 'review_assignments_reviewer_status_idx');
        });

        $this->createTableIfMissing('reviews', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('review_assignment_id')->constrained()->cascadeOnDelete();
            $table->text('comments_for_author')->nullable();
            $table->text('confidential_comments')->nullable();
            $table->string('recommendation')->nullable();
            $table->string('attachment')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });

        $this->createTableIfMissing('review_scores', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('review_id')->constrained()->cascadeOnDelete();
            $table->string('criteria');
            $table->unsignedTinyInteger('score');
            $table->timestamps();
        });

        $this->createTableIfMissing('speakers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('conference_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('speaker');
            $table->string('name');
            $table->string('title')->nullable();
            $table->string('affiliation')->nullable();
            $table->string('country')->nullable();
            $table->longText('biography')->nullable();
            $table->string('topic_title')->nullable();
            $table->string('photo')->nullable();
            $table->string('attendance_mode')->nullable();
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
        });

        $this->createTableIfMissing('venues', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('conference_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('address')->nullable();
            $table->text('description')->nullable();
            $table->string('map_url')->nullable();
            $table->string('photo')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        $this->createTableIfMissing('conference_days', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('conference_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('label');
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();
        });

        $this->createTableIfMissing('program_sessions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('conference_day_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('type')->default('plenary');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();
        });

        $this->createTableIfMissing('chambers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('conference_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('room')->nullable();
            $table->string('meeting_url')->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->string('operator')->nullable();
            $table->string('moderator')->nullable();
            $table->timestamps();
        });

        $this->createTableIfMissing('program_schedules', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('conference_day_id')->constrained()->cascadeOnDelete();
            $table->foreignId('program_session_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('chamber_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('speaker_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('submission_id')->nullable()->constrained()->nullOnDelete();
            $table->time('start_time');
            $table->time('end_time');
            $table->string('type')->default('parallel');
            $table->string('title');
            $table->string('moderator')->nullable();
            $table->string('operator')->nullable();
            $table->unsignedInteger('presentation_order')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('published')->default(false)->index();
            $table->timestamps();
            $table->index(['conference_day_id', 'published', 'start_time'], 'program_schedules_day_published_start_idx');
        });

        $this->createTableIfMissing('attendances', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->foreignId('program_schedule_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('attendance_date');
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('checked_out_at')->nullable();
            $table->string('method')->default('manual');
            $table->timestamps();
            $table->unique(['registration_id', 'program_schedule_id', 'attendance_date'], 'attendances_registration_schedule_date_unique');
        });
        $this->createAttendanceUniqueIndexIfMissing();

        $this->createTableIfMissing('document_templates', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('conference_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('name');
            $table->longText('body_html')->nullable();
            $table->json('settings')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        $this->createTableIfMissing('loa_documents', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('submission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_template_id')->nullable()->constrained()->nullOnDelete();
            $table->string('loa_number')->unique();
            $table->string('verification_code')->unique();
            $table->string('pdf_path')->nullable();
            $table->string('signer_name')->nullable();
            $table->string('signer_title')->nullable();
            $table->string('signature_image')->nullable();
            $table->date('issued_date');
            $table->string('status')->default('issued');
            $table->timestamps();
        });

        $this->createTableIfMissing('certificates', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('conference_id')->constrained()->cascadeOnDelete();
            $table->foreignId('registration_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('submission_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('document_template_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type');
            $table->string('recipient_name');
            $table->string('certificate_number')->unique();
            $table->string('verification_code')->unique();
            $table->string('pdf_path')->nullable();
            $table->date('issued_date')->nullable();
            $table->string('status')->default('draft');
            $table->timestamps();
        });

        $this->createTableIfMissing('pages', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('conference_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->boolean('published')->default(false)->index();
            $table->timestamps();
        });

        $this->createTableIfMissing('page_sections', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->string('title')->nullable();
            $table->longText('body')->nullable();
            $table->json('data')->nullable();
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('published')->default(true);
            $table->timestamps();
            $table->unique(['page_id', 'key'], 'page_sections_page_key_unique');
        });

        $this->createTableIfMissing('announcements', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('conference_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('body');
            $table->string('excerpt')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->boolean('published')->default(false)->index();
            $table->timestamps();
        });

        $this->createTableIfMissing('faqs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('conference_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('question');
            $table->longText('answer');
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        $this->createTableIfMissing('partners', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('conference_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('type')->default('partner');
            $table->string('logo')->nullable();
            $table->string('url')->nullable();
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        $this->createTableIfMissing('media', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->nullableMorphs('mediable');
            $table->string('collection')->default('default');
            $table->string('original_filename');
            $table->string('storage_path');
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->string('visibility')->default('private');
            $table->timestamps();
        });

        $this->createTableIfMissing('email_templates', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('conference_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->string('subject');
            $table->longText('body_html');
            $table->longText('body_text')->nullable();
            $table->string('from_name')->nullable();
            $table->string('reply_to')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->unique(['conference_id', 'code'], 'email_templates_conf_code_unique');
        });

        $this->createTableIfMissing('mail_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('conference_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('recipient');
            $table->text('cc')->nullable();
            $table->text('bcc')->nullable();
            $table->string('template_code')->nullable();
            $table->string('subject');
            $table->string('status')->default('queued')->index();
            $table->string('provider_message_id')->nullable();
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        $this->createTableIfMissing('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        $this->createTableIfMissing('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action')->index();
            $table->string('subject_type')->nullable();
            $table->string('subject_id')->nullable();
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    private function createTableIfMissing(string $tableName, Closure $schema): void
    {
        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, $schema);
    }

    private function createAttendanceUniqueIndexIfMissing(): void
    {
        if (! Schema::hasTable('attendances') || Schema::hasIndex('attendances', 'attendances_registration_schedule_date_unique')) {
            return;
        }

        Schema::table('attendances', function (Blueprint $table) {
            $table->unique(['registration_id', 'program_schedule_id', 'attendance_date'], 'attendances_registration_schedule_date_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('mail_logs');
        Schema::dropIfExists('email_templates');
        Schema::dropIfExists('media');
        Schema::dropIfExists('partners');
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('page_sections');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('certificates');
        Schema::dropIfExists('loa_documents');
        Schema::dropIfExists('document_templates');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('program_schedules');
        Schema::dropIfExists('chambers');
        Schema::dropIfExists('program_sessions');
        Schema::dropIfExists('conference_days');
        Schema::dropIfExists('venues');
        Schema::dropIfExists('speakers');
        Schema::dropIfExists('review_scores');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('review_assignments');
        Schema::dropIfExists('submission_status_histories');
        Schema::dropIfExists('submission_files');
        Schema::dropIfExists('submission_authors');
        Schema::dropIfExists('submissions');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('registrations');
        Schema::dropIfExists('profiles');
        Schema::dropIfExists('registration_fees');
        Schema::dropIfExists('conference_topics');
        Schema::dropIfExists('conference_dates');
        Schema::dropIfExists('conference_settings');
        Schema::dropIfExists('conferences');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['uuid']);
            $table->dropColumn(['uuid', 'whatsapp', 'institution', 'country', 'last_login_at']);
        });
    }
};
