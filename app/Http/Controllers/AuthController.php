<?php
namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
class AuthController extends Controller {
    public function showLogin(): View|RedirectResponse { return Auth::check() ? redirect()->route('admin.dashboard') : view('auth.login'); }
    public function login(Request $request): RedirectResponse {
        $data=$request->validate(['username'=>['required','string'],'password'=>['required','string']]);
        $user=User::where('username',$data['username'])->first();
        if(!$user || !$user->active || !Hash::check($data['password'],$user->password)){ return back()->withErrors(['username'=>'Identifiant ou mot de passe incorrect, ou compte suspendu.'])->onlyInput('username'); }
        Auth::login($user);$user->update(['last_login_at'=>now()]);$request->session()->regenerate();return $user->must_change_password?redirect()->route('password.change'):redirect()->intended(route('admin.dashboard'));
    }
    public function logout(Request $request): RedirectResponse { Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken(); return redirect()->route('login'); }
    public function showChangePassword(): View { return view('auth.change-password'); }
    public function updatePassword(Request $request): RedirectResponse {$data=$request->validate(['current_password'=>'required|current_password','password'=>'required|string|min:10|confirmed']);$request->user()->update(['password'=>Hash::make($data['password']),'must_change_password'=>false]);$request->session()->regenerate();return redirect()->route('admin.dashboard')->with('admin_success','Votre mot de passe personnel est maintenant actif.');}
}
