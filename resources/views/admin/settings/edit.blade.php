@extends('admin.layout')
@section('title','Contenus publics')
@php($active='settings')
@section('content')
<div class="admin-page-heading"><div><p class="eyebrow">Communication</p><h1>Contenus publics</h1><p>Modifiez les informations visibles par les clients.</p></div><a class="btn secondary" href="{{ route('home') }}" target="_blank">Previsualiser</a></div>
@if($errors->any())<div class="login-alert"><strong>Modification impossible</strong><span>{{ $errors->first() }}</span></div>@endif
<section class="admin-section">
<form id="contentSettingsForm" class="content-form" method="post" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">@csrf @method('PUT')
<div class="form-grid">
<label class="field-wide">Images du diaporama d'accueil<input id="heroImagesInput" name="hero_images[]" type="file" accept="image/jpeg,image/png,image/webp" multiple><small>Selectionnez de 1 a 10 images. Une nouvelle selection remplacera le diaporama actuel. Maximum 20 Mo par image.</small></label>
<div class="hero-admin-gallery field-wide" id="heroImagesPreview">@forelse($heroImages as $image)<figure><img src="{{ asset('storage/'.$image) }}" alt="Image du diaporama"><figcaption>Image {{ $loop->iteration }}</figcaption></figure>@empty<figure><img src="{{ asset('assets/utb-embarquement-hero.jpg') }}" alt="Image actuelle"><figcaption>Image par defaut</figcaption></figure>@endforelse</div>
<label class="field-wide">Titre accueil<textarea name="hero_title" rows="3" required>{{ old('hero_title',$settings['hero_title']??'') }}</textarea></label>
<label class="field-wide">Texte accueil<textarea name="hero_text" rows="4" required>{{ old('hero_text',$settings['hero_text']??'') }}</textarea></label>
<label class="field-wide">Titre catalogue<textarea name="catalog_title" rows="2" required>{{ old('catalog_title',$settings['catalog_title']??'') }}</textarea></label>
<label>Telephone<input name="phone" value="{{ old('phone',$settings['phone']??'') }}" required></label><label>Email<input name="email" type="email" value="{{ old('email',$settings['email']??'') }}" required></label><label>WhatsApp<input name="whatsapp" value="{{ old('whatsapp',$settings['whatsapp']??'') }}" required></label>
</div><button class="btn primary" type="submit">Enregistrer les modifications</button></form></section>
@endsection
@push('scripts')
<script>
const settingsForm=document.querySelector('#contentSettingsForm');
const imagesInput=document.querySelector('#heroImagesInput');
const preview=document.querySelector('#heroImagesPreview');
imagesInput?.addEventListener('change',event=>{const files=[...event.target.files];if(files.length>10){alert('Selectionnez au maximum 10 images.');event.target.value='';return;}preview.innerHTML='';files.forEach((file,index)=>{const url=URL.createObjectURL(file),figure=document.createElement('figure');figure.innerHTML=`<img src="${url}" alt="Apercu"><figcaption>Image ${index+1}</figcaption>`;figure.querySelector('img').onload=()=>URL.revokeObjectURL(url);preview.appendChild(figure);});});
settingsForm?.addEventListener('submit',async event=>{const files=[...imagesInput.files];if(!files.length||settingsForm.dataset.optimized==='true')return;event.preventDefault();const submit=settingsForm.querySelector('button[type="submit"]');submit.disabled=true;const transfer=new DataTransfer();try{for(let index=0;index<files.length;index++){submit.textContent=`Optimisation ${index+1}/${files.length}...`;const bitmap=await createImageBitmap(files[index]),scale=Math.min(1,1920/bitmap.width),canvas=document.createElement('canvas');canvas.width=Math.round(bitmap.width*scale);canvas.height=Math.round(bitmap.height*scale);canvas.getContext('2d').drawImage(bitmap,0,0,canvas.width,canvas.height);bitmap.close();const blob=await new Promise(resolve=>canvas.toBlob(resolve,'image/jpeg',0.82));transfer.items.add(new File([blob],`hero-${index+1}.jpg`,{type:'image/jpeg'}));}imagesInput.files=transfer.files;settingsForm.dataset.optimized='true';settingsForm.requestSubmit();}catch(error){submit.disabled=false;submit.textContent='Enregistrer les modifications';alert('Impossible d optimiser une des images.');}});
</script>
@endpush