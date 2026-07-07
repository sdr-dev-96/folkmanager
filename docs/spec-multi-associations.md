# Spécification — outil générique de gestion d'association (multi-tenant)

Ce document prend la suite de `passation-symfony-kimsufi.md`. Il adapte le modèle de données pour qu'un même déploiement Symfony puisse servir **plusieurs associations** (pas seulement Estrelas do Norte), avec des groupes de membres configurables plutôt que codés en dur. Les étapes d'installation Symfony et de déploiement Kimsufi du premier document restent valables telles quelles.

Nom de l'appli : à confirmer (placeholder utilisé ici : **[NomAppli]**).

## 1. Les 4 modules à couvrir

1. **Présences des membres aux événements**
2. **Planning des événements**
3. **CRUD des membres**
4. **Réseaux sociaux (minimal)** : carrousel des dernières publications

## 2. Pourquoi le modèle précédent doit changer

Dans la version "Estrelas do Norte only", les groupes (danseur / choriste / musicien) étaient des booléens fixes sur `Membre` (`estDanseur`, `estChoriste`, `estMusicien`). Ça fonctionne pour une seule association qui a toujours les mêmes 3 catégories. Mais une autre association pourrait vouloir des groupes complètement différents ("Bureau", "Bénévoles", "Section jeunes"...), avec ou sans logique de paire, avec ou sans instrument. Il faut donc que les groupes deviennent **des données**, pas du code.

## 3. Modèle de données multi-tenant

### Association (nouvelle entité racine)

- `id`
- `nom`
- `slug` (utilisé dans l'URL, ex. `estrelas-do-norte`)
- `couleurPrincipale`, `couleurAccent` (hex, pour le thème visuel)
- `logoPath` (fichier uploadé)
- `villeCreation`, `anneeCreation` (facultatif, pour un badge type "DESDE 1993" générique)

Toutes les autres entités métier (`Membre`, `Evenement`, `Presence`, `GroupeType`) portent désormais une clé étrangère `association_id`. C'est la base du multi-tenant : chaque requête est filtrée par l'association courante (déterminée par le sous-domaine, un slug dans l'URL, ou l'utilisateur connecté).

### GroupeType (remplace les booléens fixes)

- `id`
- `association_id`
- `nom` (ex. "Danseurs", "Chœur", "Musiciens", ou "Bénévoles" pour une autre asso)
- `couleur` (hex)
- `comportement` (enum) — détermine quels champs supplémentaires afficher dans le formulaire :
  - `simple` : aucune donnée supplémentaire (juste l'appartenance au groupe)
  - `duo` : gère un partenaire/binôme (danse, mais utilisable pour n'importe quel groupe en paires)
  - `instrument` : gère une liste d'instruments + indicateur "back up"
  - `choeur_lead` : gère un indicateur "chanteur / voix principale"
- `ordreAffichage` (int, pour l'ordre des sections dans l'écran Membres)

### Membre

- `id`
- `association_id`
- `nom`
- relations vers `MembreGroupe` (un membre peut appartenir à plusieurs `GroupeType`, exactement comme aujourd'hui un membre peut être danseur ET musicien)

### MembreGroupe (table pivot avec attributs)

- `id`
- `membre_id`
- `groupe_type_id`
- `genre` (nullable — utilisé si comportement = `duo`)
- `partenaire_membre_id` (nullable, auto-référence — utilisé si comportement = `duo`)
- `estEnfant` (bool, nullable)
- `estLead` (bool, nullable — utilisé si comportement = `choeur_lead`)
- `instruments` (relation vers `InstrumentJoue`, utilisée si comportement = `instrument`)

### InstrumentJoue (inchangé)

- `id`, `membre_groupe_id`, `nomInstrument`, `estBackup`

### PlanningConfig (rend le planning configurable, plutôt que figé samedi/dimanche)

- `id`
- `association_id`
- `jourSemaine` (1-7)
- `typesDisponibles` (JSON — ex. `["Entraînement", "Rusga"]` pour samedi chez Estrelas do Norte, mais une autre asso peut avoir des jours et types différents)

### Evenement

- `id`, `association_id`, `date`, `jour`, `type`, `detail`, `lieu`
- `nom` (ex. "Festival de Gondoriz 2026") — repris du document fourni, absent de ma première version
- `affiche` (image uploadée du flyer de l'événement)
- `statut` (`brouillon` / `confirmé` / `annulé`) — repris du document fourni

`type` n'est plus limité par un enum codé en dur : il vient de `PlanningConfig` pour cette association et ce jour.

### Presence (inchangé)

- `id`, `evenement_id`, `membre_id`, `statut` (`present`/`absent`), `updatedAt`

## 3 bis. Répertoire de danses & programme d'événement (repris de ton document existant)

Ton PDF apporte une brique que ma première version n'avait pas : un **répertoire de danses**, et la notion de **programme** pour un événement (quelles danses, dans quel ordre, qui les exécute). C'est distinct de la présence globale (`Presence` = "je viens / je ne viens pas à l'événement") : ça descend au niveau plus fin de "qui danse/joue/chante quelle danse précisément".

### Danse (répertoire, propre à chaque association)

- `id`, `association_id`, `nom`, `paroles` (texte, nullable), `nombreMaxParticipants` (int, nullable), `description` (nullable)

### DanseMembre (qui sait exécuter cette danse — vivier de compétences)

- `id`, `danse_id`, `membre_id`

### DanseEvenement (le programme : quelles danses, dans quel ordre, pour cet événement)

- `id`, `evenement_id`, `danse_id`, `ordre` (int), `heurePrevue` (nullable)

### ParticipationDanse (qui participe effectivement, pour cette danse, à cet événement précis)

- `id`, `danse_evenement_id`, `membre_id`, `statut` (`confirmé` / `absent` / `remplaçant`), `instrumentJoue` (nullable — utile si un musicien change d'instrument selon la danse)

Ça donne deux niveaux de suivi complémentaires : `Presence` répond à "qui vient à l'événement ?", `ParticipationDanse` répond à "qui fait quoi, danse par danse, une fois sur place ?" — utile par exemple pour reformer les couples si quelqu'un se désiste au dernier moment sur une danse précise.

## 4. Module réseaux sociaux — ce qui est réaliste techniquement

Un point important à anticiper : **on ne peut pas afficher un carrousel Instagram en scrapant la page publique**. Instagram bloque explicitement l'accès automatisé (c'est exactement ce que j'ai rencontré en essayant d'ouvrir le profil du groupe), et un scraping non autorisé serait de toute façon contraire à leurs conditions d'utilisation et cassable à tout moment par Meta.

La voie fiable est l'**API officielle Instagram Graph** :

1. Le compte Instagram de l'association doit être un compte **Professionnel (Business ou Créateur)**, lié à une **Page Facebook**.
2. Créer une "app" sur [developers.facebook.com](https://developers.facebook.com), demander la permission `instagram_basic` (et `pages_show_list`).
3. Le responsable de l'association fait un **login OAuth une fois** (bouton "Connecter Instagram" dans l'admin de l'appli) → l'appli reçoit un `access_token` longue durée à stocker chiffré en base.
4. Une commande Symfony planifiée (cron, ex. toutes les 6h) appelle l'endpoint `GET /{ig-user-id}/media` et met en cache les derniers posts (image, légende, lien permalien) dans une table locale.
5. Le carrousel affiché sur le site lit **ce cache local**, jamais l'API en direct à chaque visite (limite de quota + lenteur).

### Entités correspondantes

**ReseauSocialCompte**
- `id`, `association_id`, `plateforme` (`instagram`/`facebook`/`tiktok`), `identifiantExterne`, `accessTokenChiffre`, `derniereSynchronisation`

**PublicationCache**
- `id`, `compte_id`, `urlMedia`, `type` (`image`/`video`/`carousel`), `legende`, `urlPermalien`, `publieeLe`, `recupereeLe`

### Alternative plus rapide à mettre en place (sans développement d'API)

Si le développement OAuth complet est trop lourd pour démarrer, deux solutions "boîte noire" existent, à intégrer en `<iframe>` ou script embed, moyennant un abonnement payant modeste : **SnapWidget** ou **LightWidget**. Elles gèrent l'authentification Instagram à ta place et fournissent un widget carrousel prêt à coller dans le template Twig. C'est une bonne option pour un MVP, avec migration vers l'API Graph officielle plus tard si le produit est destiné à être revendu à d'autres associations (éviter la dépendance à un widget tiers payant par asso).

## 5. Impact sur le CRUD membres et l'écran de présence

Fonctionnellement, rien ne change pour l'utilisateur final : il voit toujours des sections "Danseurs / Chœur / Musiciens" avec les mêmes sous-champs (partenaire, instrument, chanteur principal). Ce qui change, c'est que ces sections et leurs comportements sont maintenant **lus depuis `GroupeType`** au lieu d'être écrits en dur dans le code — un administrateur d'une nouvelle association crée ses propres `GroupeType` (nom + couleur + comportement) depuis un écran de configuration, sans qu'un développeur touche au code.

## 6. Sécurité et accès (repris du document fourni)

Le PDF prévoyait le composant **Security** de Symfony avec deux niveaux : administrateurs avec accès complet, membres avec accès limité. Dans le modèle multi-association, ça se précise ainsi :

- `ROLE_SUPER_ADMIN` : gère la création des associations elles-mêmes (réservé à toi / l'équipe qui exploite l'outil)
- `ROLE_ADMIN` : gère une association précise (CRUD membres, événements, danses, configuration des groupes) — le "responsable" évoqué dès le départ de la conversation
- `ROLE_MEMBRE` : peut seulement répondre présent/absent pour lui-même et consulter le planning — correspond à ce qu'on avait appelé "les deux" (membres répondent, responsable supervise)

Techniquement : `composer require symfony/security-bundle`, une entité `Utilisateur` (implémentant `UserInterface`), liée à `Membre` par une relation optionnelle (un membre n'a pas forcément de compte de connexion — beaucoup ne se connecteront que pour cocher présent/absent via un lien simple, sans mot de passe, comme dans le prototype actuel). À trancher : accès par compte classique (email + mot de passe) ou lien magique par membre (plus proche de l'esprit "simple" voulu initialement).

## 7. Évolutions possibles (reprises du document fourni)

Le PDF liste plusieurs pistes d'évolution qui restent pertinentes et compatibles avec ce modèle :

- **Gestion des costumes** : entité `Costume` liée à `Membre` ou `GroupeType` (taille, dernière révision, prêté/rendu)
- **Gestion des répétitions** : proche de `PlanningConfig`/`Evenement`, pourrait être un `TypeEvenement` dédié plutôt qu'une entité séparée
- **Gestion documentaire** : partitions, paroles, statuts associatifs — table `Document` liée à `Association`
- **Calendrier partagé** : export iCal du planning (`Evenement`) pour Google Calendar/Outlook
- **Export PDF des programmes** : générer le déroulé d'un événement (`DanseEvenement` ordonné) en PDF pour les membres le jour J
- **Statistiques de participation** : taux de présence par membre/groupe sur une saison, à partir de `Presence` et `ParticipationDanse`

## 8. Prochaines étapes suggérées

1. Valider le nom définitif de l'appli (placeholder `[NomAppli]` à remplacer partout : titre, badge, sous-domaine).
2. Décider du modèle d'accès admin (compte classique vs lien simple par membre, cf. section 6).
3. Choisir entre API Graph officielle vs widget tiers pour le module réseaux sociaux, selon le temps disponible.
4. Décider si le répertoire de danses (`Danse`/`DanseEvenement`/`ParticipationDanse`) est prioritaire pour la V1, ou une évolution V2 — c'est un module à part entière, plus gros que les 4 points de départ.
5. Une fois validé, je peux soit adapter le prototype React actuel à ce modèle multi-association pour continuer à itérer visuellement, soit passer directement à la génération des entités Doctrine Symfony sur cette base.

---

*Ce document fusionne deux sources : le premier jet de spécification produit dans cette conversation, et le document PDF "Projet Symfony : Gestion d'un Groupe Folklorique" fourni ensuite — d'où les entités `Danse`, `DanseMembre`, `DanseEvenement`, `ParticipationDanse`, ainsi que les champs `nom`/`affiche`/`statut` sur `Evenement` et la section Sécurité, qui en sont directement inspirés.*
