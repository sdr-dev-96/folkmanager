# Passation technique — Estrelas do Norte : de l'app React vers Symfony + Kimsufi

Ce document décrit comment reprendre le prototype (app React avec stockage clé-valeur) et le transformer en site Symfony hébergé sur un serveur dédié Kimsufi. Il est destiné à un développeur qui prend le relais.

## 1. Vue d'ensemble

- **Aujourd'hui** : prototype React (artifact Claude), données stockées en JSON dans un système clé-valeur (`members`, `events`, `attendance`).
- **Cible** : application Symfony (PHP), base de données MySQL/MariaDB, hébergée sur un serveur Kimsufi (Debian/Ubuntu).
- **Portée fonctionnelle à conserver** : gestion des membres (danseurs/couples, chœur, musiciens — un membre peut cumuler plusieurs rôles), gestion du planning (samedi entraînement/rusga, dimanche sortie/festival/JLP), présences par événement.

## 2. Modèle de données cible

### Entités Doctrine

**Membre** (`Membre`)
- `id` (int, PK)
- `nom` (string)
- `estDanseur` (bool)
- `genreDanse` (string, nullable : `homme`/`femme`)
- `partenaireId` (relation OneToOne vers `Membre`, nullable — auto-référencée)
- `estEnfant` (bool, défaut false)
- `estChoriste` (bool)
- `estChanteurPrincipal` (bool, défaut false)
- `estMusicien` (bool)

**InstrumentJoue** (`InstrumentJoue`) — car un musicien peut jouer plusieurs instruments
- `id` (int, PK)
- `membre` (ManyToOne vers `Membre`)
- `nomInstrument` (string)
- `estBackup` (bool, défaut false)

**Evenement** (`Evenement`)
- `id` (int, PK)
- `date` (date)
- `jour` (string : `samedi`/`dimanche`/`autre`, dérivable de la date mais stocké pour simplicité de requête)
- `type` (string : `Entraînement`, `Rusga`, `Sortie`, `Festival`, `Événement JLP`, ou libre)
- `detail` (string, nullable — ex. "Sardines")
- `lieu` (string, nullable)

**Presence** (`Presence`)
- `id` (int, PK)
- `evenement` (ManyToOne vers `Evenement`)
- `membre` (ManyToOne vers `Membre`)
- `statut` (string : `present`/`absent`, absence de ligne = sans réponse)
- `repondantId` (nullable, si on veut tracer qui a modifié la réponse d'un tiers)
- `updatedAt` (datetime)
- Contrainte unique `(evenement_id, membre_id)`

### Schéma relationnel (résumé)

```
Membre 1───N InstrumentJoue
Membre 1───1 Membre (partenaire, auto-référence nullable)
Evenement 1───N Presence N───1 Membre
```

## 3. Création du projet Symfony

```bash
composer create-project symfony/skeleton estrelas-do-norte
cd estrelas-do-norte
composer require webapp doctrine orm-pack maker-bundle twig

# entités
php bin/console make:entity Membre
php bin/console make:entity InstrumentJoue
php bin/console make:entity Evenement
php bin/console make:entity Presence

# base de données locale (adapter DATABASE_URL dans .env.local)
php bin/console doctrine:database:create
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

Recommandations :
- **API Platform** (`composer require api`) est le plus simple pour exposer les entités en API JSON et réutiliser un front proche de l'existant (React/Vue) sans réécrire toute la logique côté client.
- Si le site doit rester rendu côté serveur (Twig), prévoir des formulaires Symfony (`make:form`) pour l'ajout de membres/événements et une vue par événement pour cocher les présences.
- Ajouter une contrainte d'unicité applicative pour n'autoriser qu'un partenaire de danse de genre opposé et disponible (logique déjà présente côté React, à porter dans un `EventSubscriber` ou un validateur Symfony custom).

## 4. Migration des données existantes

Le prototype expose ses données via le stockage clé-valeur de l'artifact (`members`, `events`, `attendance`). Avant la bascule :

1. Ouvrir l'app et exporter le contenu JSON (ajouter temporairement un bouton "Exporter" qui fait `JSON.stringify` des trois clés, ou lire les valeurs directement si tu as accès à la console).
2. Créer une commande Symfony d'import :

```bash
php bin/console make:command app:import-donnees
```

```php
// src/Command/ImportDonneesCommand.php (extrait)
$data = json_decode(file_get_contents($input->getArgument('fichier')), true);
foreach ($data['members'] as $m) {
    $membre = new Membre();
    $membre->setNom($m['name']);
    if (!empty($m['danseur'])) {
        $membre->setEstDanseur(true);
        $membre->setGenreDanse($m['danseur']['genre']);
        $membre->setEstEnfant($m['danseur']['isEnfant'] ?? false);
        // partenaireId à relier dans une seconde passe, une fois tous les membres créés
    }
    if (!empty($m['choriste'])) {
        $membre->setEstChoriste(true);
        $membre->setEstChanteurPrincipal($m['choriste']['isLead'] ?? false);
    }
    if (!empty($m['musicien'])) {
        $membre->setEstMusicien(true);
        foreach ($m['musicien']['instruments'] as $i) {
            $instrument = new InstrumentJoue();
            $instrument->setMembre($membre);
            $instrument->setNomInstrument($i['name']);
            $instrument->setEstBackup($i['isBackup'] ?? false);
            $em->persist($instrument);
        }
    }
    $em->persist($membre);
}
$em->flush(); // puis reboucler pour recâbler les partenaireId à partir des anciens id JSON
```

3. Faire de même pour `events` → `Evenement`, et `attendance` → `Presence`.

## 5. Déploiement sur un serveur Kimsufi

Hypothèse : serveur Kimsufi avec Debian 12 ou Ubuntu 22.04/24.04 fraîchement installé, accès root en SSH.

### 5.1 Préparation du serveur

```bash
apt update && apt upgrade -y
apt install -y curl git unzip ufw fail2ban nginx mariadb-server \
  php8.3-fpm php8.3-cli php8.3-xml php8.3-mbstring php8.3-mysql php8.3-intl php8.3-curl php8.3-zip

# pare-feu minimal
ufw allow OpenSSH
ufw allow 'Nginx Full'
ufw enable
```

### 5.2 Composer et Symfony CLI

```bash
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
curl -sS https://get.symfony.com/cli/installer | bash
mv ~/.symfony5/bin/symfony /usr/local/bin/symfony
```

### 5.3 Base de données

```bash
mysql_secure_installation
mysql -u root -p -e "CREATE DATABASE estrelas_do_norte CHARACTER SET utf8mb4;
CREATE USER 'estrelas'@'localhost' IDENTIFIED BY 'CHANGE_MOI';
GRANT ALL PRIVILEGES ON estrelas_do_norte.* TO 'estrelas'@'localhost';
FLUSH PRIVILEGES;"
```

### 5.4 Déploiement du code

```bash
mkdir -p /var/www/estrelas-do-norte
cd /var/www/estrelas-do-norte
git clone <url-du-repo> .
composer install --no-dev --optimize-autoloader

cat > .env.local <<'EOF'
APP_ENV=prod
APP_SECRET=CHANGE_MOI_SECRET_ALEATOIRE
DATABASE_URL="mysql://estrelas:CHANGE_MOI@127.0.0.1:3306/estrelas_do_norte?serverVersion=10.11-MariaDB&charset=utf8mb4"
EOF

php bin/console doctrine:migrations:migrate --no-interaction
php bin/console cache:clear --env=prod
chown -R www-data:www-data /var/www/estrelas-do-norte
```

### 5.5 Configuration Nginx

```nginx
# /etc/nginx/sites-available/estrelas-do-norte
server {
    listen 80;
    server_name estrelasdonorte.exemple.com;
    root /var/www/estrelas-do-norte/public;

    location / {
        try_files $uri /index.php$is_args$args;
    }

    location ~ ^/index\.php(/|$) {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $document_root;
        internal;
    }

    location ~ \.php$ {
        return 404;
    }

    error_log /var/log/nginx/estrelas_error.log;
    access_log /var/log/nginx/estrelas_access.log;
}
```

```bash
ln -s /etc/nginx/sites-available/estrelas-do-norte /etc/nginx/sites-enabled/
nginx -t && systemctl reload nginx
```

### 5.6 HTTPS (Let's Encrypt)

```bash
apt install -y certbot python3-certbot-nginx
certbot --nginx -d estrelasdonorte.exemple.com
```

Certbot configure le renouvellement automatique (`certbot renew` via un timer systemd déjà installé par le paquet).

### 5.7 Déploiements suivants

Solution simple sans outil supplémentaire :

```bash
cd /var/www/estrelas-do-norte
git pull
composer install --no-dev --optimize-autoloader
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console cache:clear --env=prod
systemctl reload php8.3-fpm
```

Pour aller plus loin : un script `deploy.sh` reprenant ces étapes, déclenché en SSH, ou l'outil `deployer.org` si plusieurs personnes déploient.

### 5.8 Sauvegardes

```bash
# /etc/cron.daily/backup-estrelas
#!/bin/bash
mysqldump -u estrelas -p'CHANGE_MOI' estrelas_do_norte | gzip > /var/backups/estrelas-$(date +%F).sql.gz
find /var/backups -name "estrelas-*.sql.gz" -mtime +30 -delete
```

```bash
chmod +x /etc/cron.daily/backup-estrelas
```

Penser à rapatrier régulièrement ces sauvegardes hors du serveur Kimsufi lui-même (stockage externe, autre machine).

## 6. Checklist avant mise en production

- [ ] `APP_ENV=prod` et `APP_DEBUG=0` dans `.env.local`
- [ ] Mots de passe DB et `APP_SECRET` uniques, non versionnés
- [ ] HTTPS actif et redirection HTTP → HTTPS
- [ ] `ufw` actif, `fail2ban` configuré pour SSH
- [ ] Sauvegardes quotidiennes de la base testées (restauration vérifiée au moins une fois)
- [ ] Migration des données existantes vérifiée (compter les membres/événements/présences avant/après)
- [ ] Accès admin (ajout/suppression de membres et d'événements) protégé — le prototype React était ouvert à tous ; décider si Symfony doit introduire une authentification a minima (ex. mot de passe partagé du bureau de l'association) avant la mise en ligne publique
