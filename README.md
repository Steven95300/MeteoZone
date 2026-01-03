# 🌤️ MétéoZone

**Projet Universitaire - Licence d'Informatique (L2)**
Réalisé par : **Steven BASKARA** & **Zakariya GOARA**

## 📝 Présentation
MétéoZone est un site web dynamique développé en PHP dans le cadre de l'UE Web à CY Cergy Paris Université. Il permet aux utilisateurs de consulter les prévisions météorologiques des villes françaises via une interface intuitive, tout en intégrant des fonctionnalités de géolocalisation et des données spatiales de la NASA.

## ✨ Fonctionnalités principales
- **Recherche de ville multi-mode** : Saisie manuelle, sélection par menus déroulants (Région/Département/Ville) ou via une carte de France interactive.
- **Prévisions météorologiques** : Affichage des conditions actuelles et des prévisions à 3 jours (températures, humidité, vent).
- **Géolocalisation automatique** : Détection de la position de l'utilisateur par adresse IP dès la connexion pour afficher la météo locale.
- **Interface Personnalisée** : Mode Jour/Nuit commutable manuellement et mémorisé par cookies.
- **Page Technique & NASA** : Intégration de l'image astronomique du jour (APOD) via l'API de la NASA.
- **Statistiques** : Suivi des villes les plus consultées par les utilisateurs.

## 🛠️ Technologies utilisées
- **Backend** : PHP 7.4+ (Architecture sans framework externe).
- **Frontend** : HTML5, CSS3 (Feuille de style unifiée `style.css`).
- **APIs exploitées** : 
  - `prevision-meteo.ch` : Données météorologiques en temps réel.
  - `IPInfo / GeoPlugin` : Services de géolocalisation par IP.
  - `NASA APOD` : Image astronomique du jour.
- **Stockage** : Utilisation de fichiers **CSV** pour la gestion des référentiels géographiques et des statistiques (sans base de données SQL).

## 📚 Documentation
- **Rapport de projet** : Le dossier `/docs` contient le rapport final détaillé au format PDF.
- **Documentation technique** : Le dossier `/phpdoc` regroupe la documentation complète des fonctions PHP générée par PHPDoc.

## 🚀 Installation et Utilisation
1. Clonez le dépôt dans votre répertoire serveur local (ex: `www` pour WAMP ou `htdocs` pour XAMPP).
2. Assurez-vous que le dossier `/csv` possède les droits d'écriture pour l'enregistrement des statistiques.
3. Lancez votre navigateur et accédez à `index.php`.
4. Une connexion internet est requise pour l'appel des différentes APIs.

## 📊 Structure du Projet
- `index.php` : Page d'accueil et module principal de météo.
- `stat.php` : Page dédiée aux statistiques de consultation.
- `tech.php` : Page technique regroupant la géolocalisation et l'API NASA.
- `include/` : Regroupe les fichiers d'inclusion (`header`, `footer`) et les fonctions PHP (`functions.inc.php`).
- `csv/` : Contient les données des régions, départements et villes au format CSV.
- `img/` : Ressources graphiques du site (logo, fonds, images aléatoires).
