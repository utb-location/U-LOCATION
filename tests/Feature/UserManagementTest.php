<?php
namespace Tests\Feature;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
class UserManagementTest extends TestCase {
 use RefreshDatabase;
 private function user(string $role='super_admin',array $extra=[]): User{return User::create(array_merge(['name'=>'Utilisateur Test','username'=>$role.'-test','email'=>$role.'@example.com','role'=>$role,'active'=>true,'must_change_password'=>false,'password'=>Hash::make('Password123!')],$extra));}
 public function test_super_admin_can_create_user_with_temporary_password(): void {
  $admin=$this->user();$response=$this->actingAs($admin)->post(route('admin.users.store'),['name'=>'Agent Commercial','username'=>'agent-commercial','email'=>'agent@example.com','role'=>'commercial']);
  $created=User::where('username','agent-commercial')->first();$response->assertRedirect(route('admin.users.edit',$created))->assertSessionHas('temporary_password');$this->assertTrue($created->must_change_password);$this->assertSame('commercial',$created->role);
 }
 public function test_commercial_can_access_quotes_but_not_users_or_vehicles(): void {
  $commercial=$this->user('commercial');$this->actingAs($commercial)->get(route('admin.quotes.index'))->assertOk();$this->actingAs($commercial)->get(route('admin.vehicles.index'))->assertForbidden();$this->actingAs($commercial)->get(route('admin.users.index'))->assertForbidden();
 }
 public function test_suspended_user_cannot_login(): void {
  $user=$this->user('viewer',['active'=>false]);$this->post(route('login.store'),['username'=>$user->username,'password'=>'Password123!'])->assertSessionHasErrors('username');$this->assertGuest();
 }
 public function test_temporary_password_forces_password_change(): void {
  $user=$this->user('viewer',['must_change_password'=>true]);$this->actingAs($user)->get(route('admin.dashboard'))->assertRedirect(route('password.change'));
  $this->actingAs($user)->put(route('password.update'),['current_password'=>'Password123!','password'=>'NouveauPass123!','password_confirmation'=>'NouveauPass123!'])->assertRedirect(route('admin.dashboard'));$this->assertFalse($user->fresh()->must_change_password);
 }
 public function test_super_admin_can_create_custom_role_with_permissions(): void {
  $admin=$this->user();
  $vehicles=Permission::where('slug','vehicles')->first();
  $response=$this->actingAs($admin)->post(route('admin.roles.store'),['name'=>'Chef Parc','slug'=>'chef_parc','description'=>'Gestion limitee au parc','permissions'=>[$vehicles->id]]);
  $role=Role::where('slug','chef_parc')->first();
  $response->assertRedirect(route('admin.roles.edit',$role));
  $this->assertTrue($role->permissions()->where('slug','vehicles')->exists());
  $agent=$this->user('chef_parc',['username'=>'chef-parc','email'=>'chef-parc@example.com']);
  $this->actingAs($agent)->get(route('admin.vehicles.index'))->assertOk();
  $this->actingAs($agent)->get(route('admin.quotes.index'))->assertForbidden();
 }
}
