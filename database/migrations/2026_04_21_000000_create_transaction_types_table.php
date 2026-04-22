<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_types', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $defaults = [
            ['key' => 'maternity_benefit', 'label' => 'Maternity Benefit'],
            ['key' => 'unemployment_benefit', 'label' => 'Unemployment Benefit'],
            ['key' => 'sickness_benefit', 'label' => 'Sickness Benefit'],
            ['key' => 'disability_claim', 'label' => 'Disability Claim'],
            ['key' => 'retirement_claim', 'label' => 'Retirement Claim'],
            ['key' => 'funeral_claim', 'label' => 'Funeral Claim'],
            ['key' => 'death_claim', 'label' => 'Death Claim'],
            ['key' => 'salary_loan', 'label' => 'Salary Loan'],
            ['key' => 'calamity_emergency', 'label' => 'Calamity/Emergency'],
            ['key' => 'pension_loan', 'label' => 'Pension Loan'],
            ['key' => 'consoloan', 'label' => 'Consoloan'],
            ['key' => 'mysss_card', 'label' => 'mySSS Card'],
            ['key' => 'employment_history', 'label' => 'Employment History'],
            ['key' => 'contribution_details', 'label' => 'Contribution Details'],
            ['key' => 'generate_prn', 'label' => 'Generate PRN'],
            ['key' => 'daem_disbursement_account_enrollment_module', 'label' => 'DAEM'],
        ];

        $now = now();
        foreach ($defaults as $index => $item) {
            DB::table('transaction_types')->insert([
                'key' => $item['key'],
                'label' => $item['label'],
                'is_active' => true,
                'sort_order' => $index + 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_types');
    }
};

