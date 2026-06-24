<?php
namespace Tests\Feature;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
class ContentSettingsTest extends TestCase {
 use RefreshDatabase;
 public function test_admin_can_replace_home_slideshow_images(): void {
  Storage::fake('public');
  $admin=User::create(['name'=>'Admin','username'=>'content-admin','email'=>'content@utb.ci','role'=>'admin','password'=>Hash::make('secret')]);
  $response=$this->actingAs($admin)->put(route('admin.settings.update'),['hero_title'=>'Titre UTB','hero_text'=>'Texte accueil','catalog_title'=>'Catalogue UTB','phone'=>'+2250700000000','email'=>'location@utb-ci.net','whatsapp'=>'2250700000000','hero_images'=>[new UploadedFile(public_path('assets/utb-embarquement-hero.jpg'),'hero-1.jpg','image/jpeg',null,true),UploadedFile::fake()->image('hero-2.jpg',1200,700)]]);
  $response->assertRedirect();
  $paths=json_decode(SiteSetting::where('key','hero_images')->value('value'),true);
  $this->assertCount(2,$paths);
  foreach($paths as $path) Storage::disk('public')->assertExists($path);
  $this->assertSame($paths[0],SiteSetting::where('key','hero_image')->value('value'));
 }
}
