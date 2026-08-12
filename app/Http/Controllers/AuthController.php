<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Affiche le formulaire de connexion (page de login)
    public function showLoginForm()
    {
        return view('Auth.login');
    }

    // Traite la tentative de connexion
    public function login(Request $request)
    {
       // dd('Formulaire soumis !', $request->all());

        $credentials = $request->validate([
            'pseudo' => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect('/dashboard');
        }

        return back()->withErrors([
            'pseudo' => 'Identifiants incorrects.',
        ]);
    }

    // Deconnecte l'utilisateur
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    // Indiquer a laravel d'utiliser 'pseudo'
    public function username()
    {
        return 'pseudo';
    }
}
