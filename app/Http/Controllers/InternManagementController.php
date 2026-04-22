<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;

class InternManagementController extends Controller
{
    public function index()
    {
        $this->authorize('viewAnyIntern', User::class);

        $interns = User::role('intern')
            ->latest()
            ->get(['id', 'intern_name']);
        
        return Inertia::render('Admin/Interns', [
            'interns' => $interns
        ]);
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('updateIntern', $user);

        $validated = $request->validate([
            'intern_name' => 'required|string|max:255',
        ]);

        $user->update([
            'intern_name' => $validated['intern_name'],
            'name' => $validated['intern_name'],
        ]);

        return redirect()->back()->with('success', 'Intern updated successfully!');
    }

    public function store(Request $request)
    {
        $this->authorize('createIntern', User::class);

        $validated = $request->validate([
            'intern_name' => 'required|string|max:255',
        ]);

        $intern = User::create([
            'name' => $validated['intern_name'],
            'intern_name' => $validated['intern_name'],
            // Interns do not log in; keep a unique placeholder email for DB integrity.
            'email' => 'intern-'.Str::uuid().'@local.invalid',
            'password' => Hash::make(Str::random(40)),
        ]);

        $intern->assignRole('intern');

        return redirect()->back()->with('success', 'Intern added successfully!');
    }

    public function destroy(User $user)
    {
        $this->authorize('deleteIntern', $user);

        $user->delete();

        return redirect()->back()->with('success', 'Intern removed successfully!');
    }
}
