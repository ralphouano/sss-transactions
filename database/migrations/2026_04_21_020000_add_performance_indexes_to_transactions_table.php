<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->index('created_at', 'transactions_created_at_idx');
            $table->index(['intern_id', 'created_at'], 'transactions_intern_created_at_idx');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('transactions_created_at_idx');
            $table->dropIndex('transactions_intern_created_at_idx');
        });
    }
};

