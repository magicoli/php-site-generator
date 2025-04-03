# PLAN

Générateur de site statique PHP pour projet GitHub

## Dependencies
```
composer require erusev/parsedown
```

## Objectif
Générer un site statique basé sur un dépôt GitHub contenant une librairie PHP, sans alourdir le dépôt lui-même.

## Fonctionnalités

- [x] Page d’accueil à partir de `README.md`
- [x] Pages supplémentaires à partir de fichiers `.md` (INSTALLATION.md, TROUBLESHOOTING.md…)
- [ ] Page "Downloads" (infos sur la dernière release stable + changelog)
- [ ] Page "Issues" (affichage simplifié des issues ouvertes)
- [ ] Page "Donations" avec liens Stripe / PayPal

## Arborescence

/php-backend/
├── config.php             # Configuration simple du site et des pages
├── generate.php           # Script principal pour générer le site statique
├── template.php           # Gabarit HTML des pages
├── Parsedown.php          # Parseur Markdown → HTML
├── /assets/               # CSS, images
└── /pages/                # Fichiers Markdown locaux (si besoin)

/www/                      # Résultat du site statique généré, séparé du backend
├── index.html
├── installation/
├── troubleshooting/
...

## Exemple de configuration

```php
return [
  'github_user' => 'magicoli',
  'repo' => 'opensim-helpers',
  'menu' => [
    ['title' => 'OpenSim Helpers', 'file' => 'README.md'],
    ['title' => 'Installation', 'file' => 'INSTALLATION.md'],
    ['title' => 'Dépannage', 'file' => 'TROUBLESHOOTING.md'],
    ['title' => 'Téléchargements', 'file' => 'downloads'],
    ['title' => 'Problèmes connus', 'file' => 'issues'],
    ['title' => 'Faire un don', 'file' => 'donate'],
  ],
];
```

## Étapes du script `generate.php`

1. Charger la config
2. Télécharger les fichiers Markdown depuis GitHub (via raw.githubusercontent.com)
3. Convertir Markdown → HTML avec Parsedown
4. Injecter dans un template HTML commun
5. Générer les pages spéciales :
   - `downloads`: appel à l'API GitHub Releases
   - `issues`: appel à l'API GitHub Issues
   - `donate`: HTML statique basé sur config
6. Sauvegarder dans `/../www/`

## Tâches à faire

- [ ] Écrire le fetcher Markdown
- [ ] Gérer la mise en cache locale (optionnel)
- [ ] Créer le template HTML
- [ ] Générer navigation/menu dynamique
- [ ] Ajouter pages spéciales
- [ ] Prévoir personnalisation minimale (favicon, nom du projet, etc.)
