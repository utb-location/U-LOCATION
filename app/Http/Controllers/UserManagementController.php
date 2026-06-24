<?php
namespace App\Http\Controllers;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
class UserManagementController extends Controller {
 public function index(): View{return view('admin.users.index',['users'=>User::with('accessRole')->orderBy('name')->get(),'roles'=>Role::orderByDesc('protected')->orderBy('name')->get()]);}
 public function create(): View{return view('admin.users.form',['managedUser'=>new User,'roles'=>Role::where('active',true)->orderByDesc('protected')->orderBy('name')->get(),'temporaryPassword'=>null]);}
 public function store(Request $request): RedirectResponse {
  $data=$request->validate(['name'=>'required|string|max:150','username'=>'required|string|max:80|alpha_dash|unique:users,username','email'=>'required|email|max:150|unique:users,email','role'=>['required',Rule::exists('roles','slug')->where('active',true)]]);$temporary=$this->temporaryPassword();
  $user=User::create($data+['password'=>Hash::make($temporary),'active'=>true,'must_change_password'=>true]);
  return redirect()->route('admin.users.edit',$user)->with('admin_success','Utilisateur cree avec succes.')->with('temporary_password',$temporary);
 }
 public function edit(User $user): View{return view('admin.users.form',['managedUser'=>$user,'roles'=>Role::where(function($query)use($user){$query->where('active',true)->orWhere('slug',$user->role);})->orderByDesc('protected')->orderBy('name')->get(),'temporaryPassword'=>session('temporary_password')]);}
 public function update(Request $request,User $user): RedirectResponse {
  $data=$request->validate(['name'=>'required|string|max:150','username'=>['required','string','max:80','alpha_dash',Rule::unique('users')->ignore($user)],'email'=>['required','email','max:150',Rule::unique('users')->ignore($user)],'role'=>['required',Rule::exists('roles','slug')],'active'=>'nullable|boolean']);$data['active']=(bool)($data['active']??false);
  $selectedRole=Role::where('slug',$data['role'])->first();
  $usersPermissionId=Permission::where('slug','users')->value('id');
  if($request->user()->is($user)&&(!$data['active']||!$selectedRole||($selectedRole->slug!=='super_admin'&&!$selectedRole->permissions()->where('permissions.id',$usersPermissionId)->exists())))return back()->withErrors(['role'=>'Vous ne pouvez pas retirer vos propres droits de gestion des utilisateurs.']);
  if($user->role==='super_admin'&&($data['role']!=='super_admin'||!$data['active'])&&User::where('role','super_admin')->where('active',true)->count()<=1)return back()->withErrors(['role'=>'Au moins un Super administrateur actif doit etre conserve.']);
  $user->update($data);return back()->with('admin_success','Compte utilisateur mis a jour.');
 }
 public function resetPassword(Request $request,User $user): RedirectResponse {$temporary=$this->temporaryPassword();$user->update(['password'=>Hash::make($temporary),'must_change_password'=>true]);return back()->with('admin_success','Mot de passe temporaire genere.')->with('temporary_password',$temporary);}
 private function temporaryPassword(): string{return Str::upper(Str::random(4)).'-'.random_int(100000,999999);}
}
