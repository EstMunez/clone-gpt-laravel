# MiniGPT

MiniGPT est une application web permettant d’interagir avec une intelligence artificielle via une interface inspirée de ChatGPT. Ce projet a été réalisé avec Laravel 12, Inertia.js, Vue.js 3 (Composition API) et TailwindCSS.

## ✨ Fonctionnalités principales

- Sélection d’un modèle IA via OpenRouter
- Interface de discussion fluide avec affichage Markdown et coloration de code
- Historique des conversations intégré à la page d’accueil
- Génération automatique de titres de conversations
- Conservation de l’état de la session
- Tests automatisés avec Laravel

## 🔧 Technologies utilisées

- **Laravel 12** – Backend API, logique métier, sécurité
- **Inertia.js** – Pont entre Laravel et Vue.js
- **Vue.js 3 (Composition API)** – Composants frontend réactifs
- **TailwindCSS** – Design moderne et responsive
- **OpenRouter API** – Accès aux modèles d’intelligence artificielle

## ⚙️ Lancement du projet

```bash
# Installer les dépendances PHP et JavaScript
composer install
npm install

# Copier le fichier d'environnement
cp .env.example .env

# Générer la clé de l'application
php artisan key:generate

# Configurer votre base de données dans le .env puis...
php artisan migrate

# Compiler les assets
npm run dev

# Lancer le serveur
php artisan serve

## 📚 Réalisé par

**Esther Munezero Mwiseneza**


