# folkmanager

Multi-association tool: member management, event attendance, scheduling, dance repertoire, and social media carousel. Provisional product name: **Presença** (to be confirmed).

This repository contains the "business logic" part of the project: the 14 Doctrine entities and their repositories, matching the data model discussed (see `docs/spec-multi-associations.md`). It **does not contain** the full Symfony skeleton (public/index.php, bin/console, vendor/...), which must be generated locally — this is more reliable than copying it by hand, since these files change depending on the exact Symfony version installed.

## 1. Generate the Symfony skeleton then merge this repository

```bash
# 1. Scaffold Symfony in a temporary folder
composer create-project symfony/skeleton:"7.1.*" folkmanager-skeleton
cd folkmanager-skeleton

# 2. Copy over the contents of THIS repository (src/, config/, composer.json, .env, .gitignore, README.md)
#    overwriting the files already present (this repository's composer.json lists the
#    required dependencies: doctrine, security-bundle, form, twig, maker-bundle...)
cp -r ../folkmanager/src/* ./src/
cp -r ../folkmanager/config/packages/* ./config/packages/
cp ../folkmanager/composer.json ./composer.json
cp ../folkmanager/.env ./.env
cp ../folkmanager/.gitignore ./.gitignore
cp ../folkmanager/README.md ./README.md

# 3. Install all dependencies (Doctrine, Security, Maker, Twig...)
composer install
```

## 2. Configure the database

```bash
cp .env .env.local
# edit .env.local: DATABASE_URL, APP_SECRET, SOCIAL_TOKEN_ENCRYPTION_KEY

php bin/console doctrine:database:create
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

`make:migration` will generate the migration from the 14 entities already present in `src/Entity/` — no need to run `make:entity` again, they are already hand-written.

## 3. Run the local server

```bash
symfony server:start
# or: php -S 127.0.0.1:8000 -t public/
```

## 4. Push to GitHub

```bash
git init
git add .
git commit -m "Initial commit — multi-association data model"
git branch -M main
git remote add origin https://github.com/<your-account>/folkmanager.git
git push -u origin main
```

(First create the empty `folkmanager` repository on GitHub, without a README/license, to avoid a conflict on the first sync.)

## 5. What remains to be done after this first commit

- [ ] CRUD controllers (`make:crud Membre`, `make:crud Evenement`, etc.) — not yet generated here, to be done once the database is in place
- [ ] Symfony forms for multi-role entry (see the logic already present in the React prototype: a member can hold several `GroupeType` roles)
- [ ] Initial data fixtures (the React prototype already contains the full list of ~45 Estrelas do Norte members, exportable to JSON for an `app:import-donnees` command)
- [ ] Admin screen to create `GroupeType` per association (name, color, behavior)
- [ ] Instagram Graph API integration (`ReseauSocialCompte` / `PublicationCache`) — see section 4 of `docs/spec-multi-associations.md`
- [ ] Authentication: decide between a classic account (email/password, already wired in `security.yaml`) and a simple per-member link for `ROLE_MEMBRE`

## Related documentation

Documents produced upstream of this repository (functional specification, Kimsufi deployment guide, CSS stylesheet) should be stored in a `docs/` folder of this repo:
- `spec-multi-associations.md`
- `passation-symfony-kimsufi.md`
- `estrelas-do-norte.css`
