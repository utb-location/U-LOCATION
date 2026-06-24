<?php
namespace Tests\Feature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
class UtbWorkflowTest extends TestCase {
 use RefreshDatabase;
 public function test_guest_is_redirected_from_administration(): void { $this->get(route('admin.dashboard'))->assertRedirect(route('login')); }
 public function test_admin_can_log_in(): void { User::create(['name'=>'Admin','username'=>'admin','email'=>'admin@utb.ci','role'=>'admin','password'=>Hash::make('secret')]);$this->post(route('login.store'),['username'=>'admin','password'=>'secret'])->assertRedirect(route('admin.dashboard'));$this->assertAuthenticated(); }
 public function test_client_can_submit_quote_request(): void { $this->post(route('quotes.store'),['name'=>'Client Test','phone'=>'+2250700000000','departure_date'=>'2026-07-10','origin'=>'Abidjan','destination'=>'Yamoussoukro','passengers'=>30,'service_type'=>'Voyage touristique','driver_required'=>1])->assertSessionHas('quote_success');$this->assertDatabaseHas('quote_requests',['name'=>'Client Test','status'=>'Nouvelle demande']); }
 public function test_admin_pages_are_separate(): void {
  $user=User::create(['name'=>'Admin','username'=>'manager','email'=>'manager@utb.ci','role'=>'admin','password'=>Hash::make('secret')]);
  $this->actingAs($user);
  $this->get(route('admin.dashboard'))->assertOk();
  $this->get(route('admin.quotes.index'))->assertOk();
  $this->get(route('admin.vehicles.index'))->assertOk();
  $this->get(route('admin.settings.edit'))->assertOk();
 }}