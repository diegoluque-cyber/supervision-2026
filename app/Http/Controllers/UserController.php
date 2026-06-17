<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        $total = User::count();

        return Inertia::render('Supervisores/Index', [
            'supervisores' => User::orderBy('name')->get()->map(fn (User $u) => [
                'id'         => $u->id,
                'name'       => $u->name,
                'username'   => $u->username,
                'email'      => $u->email,
                'avatar'     => $u->avatar,
                'created_at' => $u->created_at->format('d/m/Y'),
            ]),
            'stats' => ['total' => $total],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', Password::defaults(), 'confirmed'],
            'avatar'   => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $uploadPath = public_path('uploads/avatars');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            $file = $request->file('avatar');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadPath, $filename);
            $avatarPath = 'uploads/avatars/' . $filename;
        }

        User::create([
            'name'     => $data['name'],
            'username' => $data['username'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'avatar'   => $avatarPath,
        ]);

        return redirect()->route('supervisores.index')->with('success', 'Supervisor creado correctamente.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username,'.$user->id],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', Password::defaults(), 'confirmed'],
            'avatar'   => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        $user->update([
            'name'     => $data['name'],
            'username' => $data['username'],
            'email'    => $data['email'],
        ]);

        if ($request->hasFile('avatar')) {
            // Eliminar avatar anterior si existe
            if ($user->avatar) {
                $oldPath = public_path($user->avatar);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }

            $uploadPath = public_path('uploads/avatars');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            $file = $request->file('avatar');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadPath, $filename);
            $user->update(['avatar' => 'uploads/avatars/' . $filename]);
        }

        if ($data['password'] ?? false) {
            $user->update(['password' => Hash::make($data['password'])]);
        }

        return redirect()->route('supervisores.index')->with('success', 'Supervisor actualizado correctamente.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'No puedes eliminarte a ti mismo.']);
        }

        if ($user->avatar) {
            $oldPath = public_path($user->avatar);
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
        }

        $user->delete();

        return redirect()->route('supervisores.index')->with('success', 'Supervisor eliminado.');
    }

    public function destroyMultiple(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:users,id'],
        ]);

        $currentId = auth()->id();

        User::whereIn('id', $data['ids'])
            ->where('id', '!=', $currentId)
            ->get()
            ->each(function ($user) {
                if ($user->avatar) {
                    $oldPath = public_path($user->avatar);
                    if (file_exists($oldPath)) {
                        @unlink($oldPath);
                    }
                }
                $user->delete();
            });

        $count = count($data['ids']);
        return redirect()->route('supervisores.index')->with('success', "{$count} supervisor(es) eliminado(s).");
    }
}
