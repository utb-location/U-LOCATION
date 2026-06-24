<?php
namespace App\Http\Controllers;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
class SiteSettingController extends Controller {
 public function update(Request $request): RedirectResponse {
  $data=$request->validate(['hero_title'=>'required|string|max:250','hero_text'=>'required|string|max:500','catalog_title'=>'required|string|max:250','phone'=>'required|string|max:40','email'=>'required|email|max:150','whatsapp'=>'required|string|max:30','hero_images'=>'nullable|array|max:10','hero_images.*'=>'image|mimes:jpg,jpeg,png,webp|max:20480']);
  $images=$request->file('hero_images',[]);unset($data['hero_images']);
  foreach($data as $key=>$value) SiteSetting::updateOrCreate(['key'=>$key],['value'=>$value]);
  if($images){
   $newPaths=[];
   try{foreach($images as $image)$newPaths[]=$this->storeOptimizedHero($image);}
   catch(\Throwable $exception){foreach($newPaths as $path)Storage::disk('public')->delete($path);throw $exception;}
   $oldPaths=json_decode((string)SiteSetting::where('key','hero_images')->value('value'),true)?:[];
   $legacy=SiteSetting::where('key','hero_image')->value('value');if($legacy&&!in_array($legacy,$oldPaths,true))$oldPaths[]=$legacy;
   SiteSetting::updateOrCreate(['key'=>'hero_images'],['value'=>json_encode($newPaths,JSON_UNESCAPED_SLASHES)]);
   SiteSetting::updateOrCreate(['key'=>'hero_image'],['value'=>$newPaths[0]]);
   foreach($oldPaths as $oldPath)if(str_starts_with((string)$oldPath,'hero/')&&!in_array($oldPath,$newPaths,true))Storage::disk('public')->delete($oldPath);
  }
  return back()->with('admin_success','Contenus publics et image d accueil mis a jour.');
 }
 private function storeOptimizedHero(UploadedFile $file): string {
  $dimensions=@getimagesize($file->getRealPath());
  if(!$dimensions)throw new RuntimeException('Impossible de lire l image chargee.');
  [$width,$height]=$dimensions;
  if(($width*$height*6)>70*1024*1024){return $file->store('hero','public');}
  $source=match($file->getMimeType()){'image/jpeg'=>@imagecreatefromjpeg($file->getRealPath()),'image/png'=>@imagecreatefrompng($file->getRealPath()),'image/webp'=>@imagecreatefromwebp($file->getRealPath()),default=>false};
  if(!$source)throw new RuntimeException('Impossible de lire l image chargee.');
  $scale=min(1,1920/$width);$targetWidth=max(1,(int)round($width*$scale));$targetHeight=max(1,(int)round($height*$scale));$target=imagecreatetruecolor($targetWidth,$targetHeight);$white=imagecolorallocate($target,255,255,255);imagefill($target,0,0,$white);imagecopyresampled($target,$source,0,0,0,0,$targetWidth,$targetHeight,$width,$height);
  Storage::disk('public')->makeDirectory('hero');$relative='hero/home-'.date('Ymd-His').'-'.bin2hex(random_bytes(4)).'.jpg';$absolute=Storage::disk('public')->path($relative);if(!imagejpeg($target,$absolute,82)){imagedestroy($source);imagedestroy($target);throw new RuntimeException('Impossible d enregistrer l image.');}imagedestroy($source);imagedestroy($target);return $relative;
 }
}