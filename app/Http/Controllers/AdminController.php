<?php
namespace App\Http\Controllers;
use App\Models\QuoteRequest;
use App\Models\SiteSetting;
use App\Models\Vehicle;
use App\Models\CustomerFeedback;
use Illuminate\View\View;
class AdminController extends Controller {
    public function dashboard(): View {
        return view('admin.dashboard',['metrics'=>['total'=>QuoteRequest::count(),'pending'=>QuoteRequest::whereIn('status',['Nouvelle demande','En cours'])->count(),'sent'=>QuoteRequest::where('status','Devis envoye')->count(),'vehicles'=>Vehicle::count(),'feedback'=>CustomerFeedback::count(),'feedback_open'=>CustomerFeedback::whereNotIn('status',['Traite','Clos'])->count()]]);
    }
    public function quotes(): View { return view('admin.quotes.index',['requests'=>QuoteRequest::latest()->take(200)->get()]); }
    public function vehicles(): View { return view('admin.vehicles.index',['vehicles'=>Vehicle::with('images')->orderBy('position')->get()]); }
    public function settings(): View { $settings=SiteSetting::values(); $heroImages=json_decode($settings['hero_images']??'[]',true)?:[]; if(!$heroImages&&!empty($settings['hero_image']))$heroImages=[$settings['hero_image']]; return view('admin.settings.edit',compact('settings','heroImages')); }
}
