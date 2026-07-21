<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Drop all football-domain tables and columns that are no longer needed.
 * The hospital tables (departments, doctors, patients, appointments)
 * are created in the preceding migration.
 */
return new class extends Migration
{
    /** Football tables to remove */
    private array $tables = [
        'internal_match_goals',
        'member_balances',
        'availabilities',
        'match_teams',
        'payments',
        'standings',
        'matches',
        'league_teams',
        'stadiums',
        'expenses',
        'announcement_reads',
        'announcements',
        'settings',
    ];

    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        // Clean up legacy football columns from users table if present
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'league_team_id')) {
                try {
                    $table->dropForeign(['league_team_id']);
                } catch (\Throwable $e) {}
                $table->dropColumn('league_team_id');
            }
            $columnsToDrop = array_filter(
                ['jersey_number', 'position', 'nationality', 'billing_type', 'emergency_contact', 'emergency_phone', 'date_joined'],
                fn($col) => Schema::hasColumn('users', $col)
            );
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });

        foreach ($this->tables as $table) {
            Schema::dropIfExists($table);
        }

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // These tables and columns are intentionally not restored.
    }
};
