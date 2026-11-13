<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AccountController extends Controller
{
    public function login(Request $request): RedirectResponse
    {
        $this->normalizeEmail($request);

        $credentials = $request->validate([
            'email' => ['required', 'email', 'max:254'],
            'password' => ['required', 'string', 'bail', 'max:72', $this->passwordByteLimit()],
        ]);

        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => 'El correo o la contraseña no coinciden.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('tasks.index'));
    }

    public function register(Request $request): RedirectResponse
    {
        $this->normalizeEmail($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:254', 'unique:users,email'],
            'password' => ['required', 'string', 'bail', 'min:8', 'max:72', $this->passwordByteLimit()],
        ]);

        Auth::login(User::create($data));
        $request->session()->regenerate();

        return to_route('tasks.index');
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->endSession($request);

        return to_route('login');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validate(['confirmed' => ['accepted']]);

        $request->user()->delete();
        $this->endSession($request);

        return to_route('login')->with('status', 'Tu cuenta y tus tareas se eliminaron.');
    }

    private function passwordByteLimit(): \Closure
    {
        return function (string $attribute, string $password, \Closure $fail): void {
            if (strlen($password) > 72) {
                $fail('La contraseña es demasiado larga.');
            }
        };
    }

    private function normalizeEmail(Request $request): void
    {
        if (is_string($request->input('email'))) {
            $request->merge(['email' => mb_strtolower($request->input('email'))]);
        }
    }

    private function endSession(Request $request): void
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
