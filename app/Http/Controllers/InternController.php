<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\SystemSetting;
use App\Models\TransactionType;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Spatie\Permission\Exceptions\RoleDoesNotExist;

class InternController extends Controller
{
    public function dashboard()
    {
        try {
            $interns = User::role(['intern', 'admin'])
                ->get(['id', 'intern_name', 'name'])
                ->map(fn (User $user) => [
                    'id' => $user->id,
                    'intern_name' => $user->intern_name ?: $user->name,
                ])
                ->sortBy('intern_name')
                ->values();
        } catch (RoleDoesNotExist $e) {
            // Fallback for mis-seeded or stale role cache in production.
            Log::warning('Intern role missing while loading dashboard, using fallback query.', [
                'error' => $e->getMessage(),
            ]);

            $interns = User::query()
                ->where(function ($query) {
                    $query->whereNotNull('intern_name')
                        ->orWhereNotNull('name');
                })
                ->get(['id', 'intern_name', 'name'])
                ->map(fn (User $user) => [
                    'id' => $user->id,
                    'intern_name' => $user->intern_name ?: $user->name,
                ])
                ->sortBy('intern_name')
                ->values();
        }
        
        return Inertia::render('Intern/Dashboard', [
            'interns' => $interns,
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
            'intern_id' => 'required|exists:users,id',
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
