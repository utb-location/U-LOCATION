@extends('admin.layout')
@section('title','Gestion du parc')
@php($active='vehicles')
@section('content')
<div class="admin-page-heading"><div><p class="eyebrow">Exploitation</p><h1>Gestion du parc</h1><p>Vehicules, disponibilites et galeries photos.</p></div><a class="btn primary" href="{{ route('admin.vehicles.create') }}">Ajouter un vehicule</a></div>
<div class="admin-card-grid">@forelse($vehicles as $vehicle)<article class="admin-card">@if($vehicle->images->first())<img class="admin-fleet-thumb" src="{{ asset('storage/'.$vehicle->images->first()->path) }}" alt="">@endif<div class="admin-card-head"><span class="badge">{{ $vehicle->category }}</span><span class="fleet-status">{{ $vehicle->status }}</span></div><h3>{{ $vehicle->name }}</h3><p>{{ $vehicle->capacity }} places &middot; {{ $vehicle->images->count() }} photo(s)</p><a class="btn secondary" href="{{ route('admin.vehicles.edit',$vehicle) }}">Modifier</a></article>@empty<p>Aucun vehicule.</p>@endforelse</div>
@endsection