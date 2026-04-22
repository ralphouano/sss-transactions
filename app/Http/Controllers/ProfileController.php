<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\SystemSetting;
use App\Models\TransactionType;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'canManageTransactionTypes' => $request->user()?->hasRole('admin') ?? false,
            'transactionTypes' => Schema::hasTable('transaction_types')
                ? TransactionType::query()
                    ->orderBy('sort_order')
                    ->orderBy('label')
                    ->get(['id', 'key', 'label', 'is_active', 'sort_order'])
                : [],
            'submissionPinConfigured' => Schema::hasTable('system_settings')
                ? SystemSetting::query()->where('key', 'transaction_submit_pin_hash')->whereNotNull('value')->exists()
                : false,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit');
    }

    public function storeTransactionType(Request $request): RedirectResponse
    {
        $this->authorize('create', TransactionType::class);
        abort_unless(Schema::hasTable('transaction_types'), 500, 'Transaction types table is missing.');

        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $key = $this->generateUniqueTransactionTypeKey($validated['label']);
        $maxOrder = (int) TransactionType::max('sort_order');

        TransactionType::create([
            'label' => $validated['label'],
            'key' => $key,
            'is_active' => (bool) ($validated['is_active'] ?? true),
            'sort_order' => $maxOrder + 1,
        ]);

        return Redirect::route('profile.edit')->with('success', 'Transaction type added.');
    }

    public function updateTransactionType(Request $request, TransactionType $transactionType): RedirectResponse
    {
        $this->authorize('update', $transactionType);
        abort_unless(Schema::hasTable('transaction_types'), 500, 'Transaction types table is missing.');

        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'is_active' => 'required|boolean',
            'sort_order' => 'nullable|integer|min:1',
        ]);

        $transactionType->update([
            'label' => $validated['label'],
            'key' => $this->generateUniqueTransactionTypeKey($validated['label'], $transactionType->id),
            'is_active' => (bool) $validated['is_active'],
            'sort_order' => $validated['sort_order'] ?? $transactionType->sort_order,
        ]);

        return Redirect::route('profile.edit')->with('success', 'Transaction type updated.');
    }

    public function updateSubmissionPin(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasRole('admin'), 403);
        abort_unless(Schema::hasTable('system_settings'), 500, 'System settings table is missing.');

        $validated = $request->validate([
            'pin' => ['required', 'string', 'min:4', 'max:12', 'confirmed', 'regex:/^[0-9]+$/'],
        ]);

        SystemSetting::query()->updateOrCreate(
            ['key' => 'transaction_submit_pin_hash'],
            ['value' => Hash::make($validated['pin'])]
        );

        return Redirect::route('profile.edit')->with('success', 'Submission PIN updated.');
    }

    private function generateUniqueTransactionTypeKey(string $label, ?int $ignoreId = null): string
    {
        $base = Str::of($label)
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_')
            ->toString();

        if ($base === '') {
            $base = 'transaction_type';
        }

        $candidate = $base;
        $counter = 2;

        while (
            TransactionType::query()
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->where('key', $candidate)
                ->exists()
        ) {
            $candidate = "{$base}_{$counter}";
            $counter++;
        }

        return $candidate;
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
