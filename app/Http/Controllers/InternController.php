<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\SystemSetting;
use App\Models\TransactionType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Inertia\Inertia;

class InternController extends Controller
{
    public function dashboard()
    {
        return Inertia::render('Intern/Dashboard', [
            'transactionTypes' => Schema::hasTable('transaction_types')
                ? TransactionType::query()
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('label')
                    ->get(['id', 'key', 'label'])
                : collect(),
        ]);
    }

    public function store(Request $request)
    {
        $allowedTransactionTypes = Schema::hasTable('transaction_types')
            ? TransactionType::query()
                ->where('is_active', true)
                ->pluck('key')
                ->all()
            : [];
        $pinHash = Schema::hasTable('system_settings')
            ? SystemSetting::query()->where('key', 'transaction_submit_pin_hash')->value('value')
            : null;

        $validated = $request->validate([
            'assistor_name' => 'required|string|max:255',
            'member_name' => 'required|string|max:255',
            'signature' => ['required', 'string', 'regex:/^data:image\/[a-zA-Z]+;base64,/'],
            'transactions' => 'required|array|min:1',
            'transactions.*' => 'required|string|in:' . implode(',', $allowedTransactionTypes),
            'submit_pin' => ['required', 'string', 'min:4', 'max:6', 'regex:/^[0-9]+$/'],
        ]);

        $validated['member_name'] = Str::of($validated['member_name'])
            ->lower()
            ->squish()
            ->title()
            ->toString();
        $validated['assistor_name'] = Str::of($validated['assistor_name'])
            ->lower()
            ->squish()
            ->title()
            ->toString();

        $matchedUserId = User::query()
            ->whereRaw('LOWER(COALESCE(intern_name, name)) = ?', [Str::lower($validated['assistor_name'])])
            ->value('id');
        $fallbackUserId = User::query()->value('id');
        $internId = $matchedUserId ?? $fallbackUserId;

        if (! $internId) {
            return redirect()->back()->withErrors([
                'assistor_name' => 'No valid account is available to map this transaction. Please contact admin.',
            ]);
        }
        $validated['intern_id'] = $internId;

        if (! $pinHash || ! Hash::check($validated['submit_pin'], $pinHash)) {
            return redirect()->back()->withErrors([
                'submit_pin' => 'Invalid submission PIN. Please contact admin.',
            ]);
        }

        if (strlen($validated['signature']) > 500000) {
            return redirect()->back()->withErrors([
                'signature' => 'Signature payload is too large. Please redraw and try again.',
            ]);
        }

        unset($validated['submit_pin']);

        Transaction::create($validated);
        Cache::forget('admin_transaction_type_counts');

        return redirect()->back()->with('success', 'Transaction recorded successfully!');
    }
}
