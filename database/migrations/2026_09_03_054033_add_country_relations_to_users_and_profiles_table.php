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
        if (! Schema::hasColumn('users', 'country_id')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->foreignId('country_id')->nullable()->after('country')->constrained()->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('profiles', 'country_id')) {
            Schema::table('profiles', function (Blueprint $table): void {
                $table->foreignId('country_id')->nullable()->after('country')->constrained()->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('registrations', 'country_id')) {
            Schema::table('registrations', function (Blueprint $table): void {
                $table->foreignId('country_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['registrations', 'profiles', 'users'] as $tableName) {
            if (Schema::hasColumn($tableName, 'country_id')) {
                Schema::table($tableName, function (Blueprint $table): void {
                    $table->dropConstrainedForeignId('country_id');
                });
            }
        }
    }
};
