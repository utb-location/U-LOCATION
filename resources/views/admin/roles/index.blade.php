@extends('admin.layout')
@section('title','Roles et permissions')
@php($active='roles')
@section('content')
<div class="admin-page-heading"><div><p class="eyebrow">Securite</p><h1>Roles et permissions</h1><p>Definissez les profils d'acces selon les responsabilites de chaque equipe.</p></div><div class="form-actions"><a class="btn secondary" href="{{ route('admin.users.index') }}">Utilisateurs</a><a class="btn primary" href="{{ route('admin.roles.create') }}">Creer un role</a></div></div>
@if($errors->any())<div class="login-alert"><strong>Action impossible</strong><span>{{ $errors->first() }}</span></div>@endif
<section class="admin-section"><div class="request-table-wrap"><table class="request-table"><thead><tr><th>Role</th><th>Permissions</th><th>Statut</th><th>Type</th><th>Action</th></tr></thead><tbody>@foreach($roles as $role)<tr><td><strong>{{ $role->name }}</strong><br><small>{{ $role->slug }}</small></td><td>{{ $role->permissions_count }} permission(s)</td><td><span class="account-status {{ $role->active?'is-active':'is-suspended' }}">{{ $role->active?'Actif':'Suspendu' }}</span></td><td>{{ $role->protected?'Systeme':'Personnalise' }}</td><td><a class="btn secondary table-action" href="{{ route('admin.roles.edit',$role) }}">Configurer</a></td></tr>@endforeach</tbody></table></div></section>
@endsection
