# 🧭 Projet Symfony Groupe 6

> Application web pour gérer des sorties entre membres d’un site

---

## 🚀 Présentation

Ce projet a été développé dans le cadre d’un projet de groupe avec **Symfony**.  
Il s’agit d’une plateforme permettant aux utilisateurs de créer, consulter et s’inscrire à des sorties,  
tout en offrant une interface d’administration complète pour la gestion des utilisateurs, des lieux et des sites.

---

## 🧩 Fonctionnalités principales

- 🗓️ **CRUD des sorties** : création, modification, suppression et affichage.
- 👥 **CRUD des utilisateurs** : gestion des membres et de leurs informations.
- ✅ **Inscription / désinscription** à une sortie.
- 🕐 **Affichage des différents états** d’une sortie (créée, ouverte, clôturée, en cours, passée, annulée...).
- ⭐ **Notation des sorties** terminées (visible uniquement pour les inscrits).
- 🌦️ **Affichage de la météo** du jour de la sortie via une API externe.
- 📍 **Gestion des lieux et sites** (villes, adresses, latitude/longitude).
- 🔐 **Interface administrateur** pour gérer les comptes utilisateurs et le contenu global.
- 💬 **Affichage clair et responsive** avec **TailwindCSS**.

---

## 🛠️ Technologies utilisées

| Technologie | Description |
|--------------|-------------|
| **Symfony 5.15.1 (CLI)** | Framework backend PHP |
| **Twig** | Moteur de templates |
| **TailwindCSS** | Framework CSS utilitaire |
| **MySQL** | Base de données relationnelle |
| **WampServer** | Environnement local de développement PHP/MySQL |
| **Composer** | Gestionnaire de dépendances PHP |

---

## ⚙️ Installation et exécution

### 1️⃣ Cloner le projet
```bash
git clone https://github.com/votre-utilisateur/projet-symfony-groupe6.git
cd projet-symfony-groupe6
```
### 2️⃣ Installer les dépendances
```bash
symfony composer install
```
### 3️⃣ Configurer l’environnement
Créer un fichier .env.local à la racine du projet :
```bash
DATABASE_URL="mysql://root:@127.0.0.1:3306/projet"
APP_ENV=dev
APP_DEBUG=true
```
⚠️ Adapter root et le mot de passe selon votre configuration locale WampServer.

### 4️⃣ Créer la base de données et exécuter les migrations
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```
### 5️⃣ Lancer le serveur local
```bash
symfony serve
```

---

## 🧪 Jeu de données de démonstration
Si vous souhaitez préremplir la base de données avec des données fictives (sites, sorties, utilisateurs, etc.) :

```bash
php bin/console doctrine:fixtures:load
```

---

## 🧭 Navigation dans le site
| Route | Description |
|--------|--------------|
| `/` | Accueil |
| `/login`  `/logout` | Connexion et déconnexion |
| `/register` | Inscription |
| `/participant` | Liste des utilisateurs |
| `/participant/{id}` | Détail d’un utilisateur |
| `/participant/new` | Créer un nouvel utilisateur |
| `/participant/{id}/edit` | Modifier un utilisateur |
| `/sortie` | Liste des sorties |
| `/sortie/new` | Créer une nouvelle sortie |
| `/sortie/{id}` | Détail d’une sortie |
| `/sortie/{id}/edit` | Modifier une sortie |
| `/site` | Liste des sites |
| `/site/new` | Créer un nouveau site |
| `/site/{id}/edit` | Modifier un site |
| `/lieu` | Liste des lieux |
| `/lieu/new` | Créer un nouveau lieu |
| `/lieu/{id}/edit` | Modifier un lieu |
| `/ville` | Liste des villes |
| `/ville/new` | Créer une nouvelle ville |
| `/ville/{id}/edit` | Modifier une ville |
---

## 👨‍💻 Auteurs
### Projet réalisé par :
- Johann DEGENNES
- Gabriel LANDRY
- Mathis DELAHAIS
