<?php
return [
 'labels'=>['super_admin'=>'Super administrateur','commercial'=>'Commercial','fleet'=>'Gestionnaire du parc','quality'=>'Responsable qualite','communication'=>'Communication','viewer'=>'Consultation'],
 'permission_catalog'=>[
  'dashboard'=>['name'=>'Tableau de bord','description'=>'Acceder a la synthese administrative.'],
  'quotes'=>['name'=>'Demandes de devis','description'=>'Consulter, traiter, supprimer et repondre aux demandes de devis.'],
  'vehicles'=>['name'=>'Gestion du parc','description'=>'Creer, modifier les vehicules et leurs galeries.'],
  'feedback'=>['name'=>'Retours clients','description'=>'Consulter, traiter et supprimer les retours clients.'],
  'sms'=>['name'=>'Campagnes SMS, email et WhatsApp','description'=>'Composer, tester et lancer les campagnes de communication.'],
  'settings'=>['name'=>'Contenus publics','description'=>'Modifier les textes, coordonnees et images du site public.'],
  'users'=>['name'=>'Utilisateurs, roles et permissions','description'=>'Creer les utilisateurs, roles et gerer les permissions.'],
 ],
 'permissions'=>[
  'dashboard'=>['super_admin','commercial','fleet','quality','communication','viewer'],
  'quotes'=>['super_admin','commercial'],
  'vehicles'=>['super_admin','fleet'],
  'feedback'=>['super_admin','quality'],
  'sms'=>['super_admin','commercial','communication'],
  'settings'=>['super_admin','communication'],
  'users'=>['super_admin'],
 ],
];
