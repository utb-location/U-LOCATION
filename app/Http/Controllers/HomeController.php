<?php
namespace App\Http\Controllers;
use App\Models\AnnouncementMessage;
use App\Models\SiteSetting;
use App\Models\Vehicle;
use Illuminate\View\View;
class HomeController extends Controller {
 public function index(): View {
  $settings=SiteSetting::values();
  $heroImages=json_decode($settings['hero_images']??'[]',true)?:[];
  if(!$heroImages&&!empty($settings['hero_image']))$heroImages=[$settings['hero_image']];
  return view('home',['vehicles'=>Vehicle::with('images')->where('active',true)->orderBy('position')->get(),'settings'=>$settings,'heroImages'=>$heroImages,'announcements'=>AnnouncementMessage::visible()->orderBy('position')->latest()->get()]);
 }
}
