<?php
namespace App\Http\Controllers;
use App\Models\Vehicle;
use App\Models\VehicleImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
class VehicleController extends Controller {
    public function create(): View { return view('admin.vehicles.form',['vehicle'=>new Vehicle()]); }
    public function edit(Vehicle $vehicle): View { $vehicle->load('images'); return view('admin.vehicles.form',compact('vehicle')); }
    private function validated(Request $request): array { return $request->validate(['name'=>'required|string|max:180','category'=>'required|string|max:80','capacity'=>'required|integer|min:1|max:80','status'=>'required|in:Disponible,Indisponible,En maintenance,Sur demande','description'=>'required|string|max:3000','equipment'=>'nullable|string|max:2000','active'=>'nullable|boolean']); }
    public function store(Request $request): JsonResponse|RedirectResponse { $data=$this->validated($request);$data['slug']=Str::slug($data['name']).'-'.Str::lower(Str::random(5));$data['equipment']=array_values(array_filter(array_map('trim',explode(',',$data['equipment']??''))));$data['active']=$request->boolean('active');$vehicle=Vehicle::create($data);return $request->expectsJson()?response()->json($this->payload($vehicle),201):redirect()->route('admin.vehicles.edit',$vehicle)->with('admin_success','Vehicule cree. Ajoutez maintenant ses photos.'); }
    public function update(Request $request, Vehicle $vehicle): JsonResponse|RedirectResponse { $data=$this->validated($request);$data['equipment']=array_values(array_filter(array_map('trim',explode(',',$data['equipment']??''))));$data['active']=$request->boolean('active');$vehicle->update($data);return $request->expectsJson()?response()->json($this->payload($vehicle)):redirect()->route('admin.vehicles.edit',$vehicle)->with('admin_success','Vehicule mis a jour.'); }
    private function payload(Vehicle $vehicle): array { return ['vehicle_id'=>$vehicle->id,'upload_url'=>route('admin.vehicles.images.store',$vehicle),'redirect_url'=>route('admin.vehicles.edit',$vehicle)]; }
    public function uploadImage(Request $request, Vehicle $vehicle): JsonResponse { if($vehicle->images()->count()>=60){return response()->json(['message'=>'Maximum de 60 images atteint.'],422);} $request->validate(['image'=>'required|image|mimes:jpg,jpeg,png,webp|max:8192']);$path=$request->file('image')->store('vehicles/'.$vehicle->id,'public');$image=$vehicle->images()->create(['path'=>$path,'alt_text'=>$vehicle->name,'position'=>(int)$vehicle->images()->max('position')+1]);return response()->json(['id'=>$image->id,'url'=>Storage::url($image->path)],201); }
    public function destroyImage(Vehicle $vehicle, VehicleImage $image): RedirectResponse { abort_unless($image->vehicle_id===$vehicle->id,404);Storage::disk('public')->delete($image->path);$image->delete();return back()->with('admin_success','Photo supprimee.'); }
}