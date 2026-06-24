@extends('admin.layout')
@section('title','Utilisateurs et acces')
@php($active='users')
@section('content')
<div class="admin-page-heading"><div><p class="eyebrow">Securite</p><h1>Utilisateurs et acces</h1><p>Chaque collaborateur dispose d'un compte personnel et de droits adaptes a sa fonction.</p></div><div class="form-actions"><a class="btn secondary" href="{{ route('admin.roles.index') }}">Gerer les roles</a><a class="btn primary" href="{{ route('admin.users.create') }}">Creer un utilisateur</a></div></div>
<section class="admin-section"><div class="request-table-wrap"><table class="request-table"><thead><tr><th>Utilisateur</th><th>Identifiant</th><th>Role</th><th>Statut</th><th>Mot de passe</th><th>Derniere connexion</th><th>Action</th></tr></thead><tbody>@foreach($users as $user)<tr><td><strong>{{ $user->name }}</strong><br><small>{{ $user->email }}</small></td><td>{{ $user->username }}</td><td>{{ $user->roleLabel() }}</td><td><span class="account-status {{ $user->active?'is-active':'is-suspended' }}">{{ $user->active?'Actif':'Suspendu' }}</span></td><td>{{ $user->must_change_password?'Temporaire':'Personnel' }}</td><td>{{ $user->last_login_at?->format('d/m/Y H:i')??'Jamais' }}</td><td><a class="btn secondary table-action" href="{{ route('admin.users.edit',$user) }}">Gerer</a></td></tr>@endforeach</tbody></table></div></section>
@endsection
