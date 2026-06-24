# Mise en ligne de UTB Location

## Serveur recommande

- Ubuntu 24.04 LTS
- Nginx ou Apache
- PHP 8.3 avec `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `gd`, `mbstring`, `mysql`, `openssl`, `pdo`, `tokenizer` et `xml`
- MySQL 8 ou MariaDB recent
- Composer 2 et Node.js LTS
- Certificat HTTPS Let's Encrypt
- Au moins 2 Go de RAM, 2 vCPU et 40 Go SSD

Le domaine doit pointer vers l'adresse IP du serveur. La racine web doit etre le dossier `public`, jamais la racine complete du projet.

## Installation

```bash
cd /var/www/utb-location
composer install --no-dev --prefer-dist --optimize-autoloader
npm ci
npm run build
cp .env.production.example .env
php artisan key:generate
```

Renseigner ensuite les identifiants MySQL, SMTP et SMS dans `.env`.

```bash
php artisan migrate --force
php artisan storage:link
php artisan optimize
```

## Permissions

```bash
sudo chown -R www-data:www-data /var/www/utb-location
sudo chmod -R 775 storage bootstrap/cache
```

## Taches automatiques

Ajouter dans `crontab -e` :

```cron
* * * * * cd /var/www/utb-location && php artisan schedule:run >> /dev/null 2>&1
```

Si des traitements sont places en file d'attente, lancer un worker gere par Supervisor :

```ini
[program:utb-location-worker]
command=php /var/www/utb-location/artisan queue:work --sleep=3 --tries=3 --timeout=900
directory=/var/www/utb-location
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/utb-location/storage/logs/worker.log
```

## Limites pour les fichiers de 300 Mo

Configurer PHP :

```ini
upload_max_filesize = 310M
post_max_size = 320M
max_execution_time = 900
max_input_time = 900
memory_limit = 512M
```

Avec Nginx :

```nginx
client_max_body_size 320M;
```

Avec Apache :

```apache
LimitRequestBody 335544320
```

Attention : la plupart des serveurs de messagerie refusent une piece jointe de 300 Mo. L'application doit conserver son fonctionnement par lien securise pour les fichiers volumineux.

## Verification apres publication

```bash
php artisan about
php artisan migrate:status
php artisan schedule:list
curl -I https://location.utb-ci.net/up
```

Verifier ensuite :

1. Page publique et diaporamas.
2. Formulaire de devis.
3. Connexion administration.
4. Envoi d'un email de test.
5. Envoi SMS de test.
6. Telechargement d'une piece jointe.
7. Creation et affichage d'un message defilant.

## Sauvegardes

Sauvegarder quotidiennement :

- la base MySQL ;
- `storage/app` ;
- le fichier `.env`, dans un coffre chiffre ;
- les certificats et la configuration du serveur.

Conserver au moins une copie hors du serveur.
