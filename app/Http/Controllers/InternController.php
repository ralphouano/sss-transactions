<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InternController extends Controller
{
    public function dashboard()
    {
        $interns = User::role('intern')->get(['id', 'intern_name']);
        
        return Inertia::render('Intern/Dashboard', [
            'interns' => $interns
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'intern_id' => 'required|exists:users,id',
            'member_name' => 'required|string|max:255',
            'signature' => ['required', 'string', 'regex:/^data:image\/[a-zA-Z]+;base64,/'],
            'transactions' => 'required|array|min:1',
            'transactions.*' => 'required|string|in:maternity_benefit,unemployment_benefit,sickness_benefit,disability_claim,retirement_claim,funeral_claim,death_claim,salary_loan,calamity_emergency,pension_loan,consoloan,mysss_card,employment_history,contribution_details,generate_prn',
        ]);

        Transaction::create($validated);

        return redirect()->back()->with('success', 'Transaction recorded successfully!');
    }
}
