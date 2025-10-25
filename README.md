# P47 - Script de diagnostic Prestashop

![left 100%](https://raw.githubusercontent.com/thierry-laval/archives/master/images/logo-portfolio.png "Logo Thierry Laval")

## Auteur

👤 &nbsp; **Thierry LAVAL** — [Contact](mailto:contact@thierrylaval.dev)  
* Github: [@Thierry Laval](https://github.com/thierry-laval)  
* LinkedIn: [Thierry Laval](https://www.linkedin.com/in/thierry-laval)  
* Site Web: https://thierrylaval.dev

---

Projet : Création d’un outil de diagnostic complet pour installations PrestaShop (1.7 → 9.x).  
Début du projet : 27/01/2023 — Version du script : 1.0.0 (25/10/2025)

![Assistant de téléchargement PrestaShop](img/generateur-prestashop-thierrylaval.dev.jpg)

## Description

Ce dépôt contient un script PHP autonome destiné à réaliser un diagnostic complet d'une installation PrestaShop. Il vérifie la configuration PHP, les extensions, la connexion à la base de données, les modules, les thèmes, les permissions, les fichiers critiques, les overrides, le core (/vendor) et propose un rapport HTML téléchargeable.

Le script n'effectue pas de modifications non sollicitées hors de l'activation temporaire du mode debug si possible, et propose des actions sûres (vidage du cache, rapport téléchargeable).

## Fonctionnalités principales

- Détection et affichage de la version PrestaShop (multiples méthodes : settings, vendor, composer, console).
- Vérification de la version PHP et des extensions requises (pdo_mysql, gd, curl, intl, zip, xml, mbstring, openssl, fileinfo...).
- Tests de connexion à la base de données et lecture des modules dans la DB.
- Comparaison modules DB vs modules physiques (signalement des modules manquants).
- Vérification des thèmes (présence de config/theme.yml).
- Vérification des dossiers critiques et des permissions.
- Recherche de fichiers d'installation exposés et avertissements de sécurité.
- Analyse des overrides et détection de classes dupliquées.
- Tentative d'affichage des privilèges SQL (SHOW GRANTS) si autorisé.
- Vidage automatique des caches (/var/cache/prod, /var/cache/dev) avec fonction récursive.
- Génération d'un rapport HTML téléchargeable contenant l'intégralité du diagnostic.
- Boutons d'action simples (téléchargement du rapport).

## Installation & utilisation

1. Copier le fichier `diagnostic-prestashop.php` (le script de diagnostic) à la racine de l'installation PrestaShop.
2. Accéder à la page via un navigateur (ex. https://votre-site.tld/diagnostic-prestashop.php).
3. Lire les avertissements.
4. Utiliser le bouton "Télécharger le rapport HTML" pour récupérer un export complet.
5. Après usage, supprimer immédiatement le script du serveur (sécurité).

Remarque : pour certaines vérifications (connexion DB, SHOW GRANTS, lecture des fichiers vendor/composer), le script nécessite d'avoir les bonnes informations d'accès et les permissions de lecture/exécution appropriées.

## Avertissements de sécurité

- Ne laissez jamais ce script (ou tout script contenant vos identifiants DB) sur un serveur public après utilisation.
- Masquez ou retirez toute information sensible avant de partager un rapport.
- L'activation du mode debug est réalisée uniquement si le script peut modifier `config/defines.inc.php`. Conservez une sauvegarde avant modification.

## Pour les développeurs

Le script contient des fonctions utiles :
- check($condition, $message_ok, $message_fail, $class_ok = 'ok', $class_fail = 'fail') — affichage conditionnel.
- rmdir_recursive($dir) — suppression récursive pour vider le cache.

Idées d'amélioration :
- Installation automatique d'une base de données pour tests locaux.
- Ajout d'un mode "safe" qui n'écrit jamais sur le disque.
- Export JSON/CSV des résultats pour intégration CI/CD.

## Contribution

Contributions bienvenues :
- Fork → nouvelle branche → commit → pull request.  
- Respectez les bonnes pratiques, tests et sécurité (ne pas committer les credentials).

## Licence

Ce projet est distribué sous licence MIT — voir le fichier LICENCE pour les détails.

Copyright © 2023–2025 Thierry Laval — https://thierrylaval.dev

## Soutien

Si ce projet vous aide, vous pouvez soutenir l'auteur :

<a href="https://paypal.me/thierrylaval01?country.x=FR&locale.x=fr_FR" target="_blank"><img src="https://www.paypalobjects.com/digitalassets/c/website/logo/full-text/pp_fc_hl.svg" alt="Soutiens-moi !" height="35" width="150"></a>

---

Donnez une étoile ⭐ si ce projet vous plaît !
