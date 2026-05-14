# 🐔 Ferme Avicole — Version PHP

Application de gestion de ferme avicole (poulets de chair).
Backend PHP, données stockées dans un fichier JSON.

## Structure du projet

```
ferme-avicole-php/
├── index.html         # Interface web (HTML/CSS/JS)
├── api.php            # API PHP (lecture/écriture des données)
├── .htaccess          # Configuration Apache + sécurité
├── README.md          # Ce fichier
└── data/
    ├── .htaccess      # Bloque l'accès direct aux données
    ├── data.json      # Données (créé automatiquement)
    └── data.json.bak  # Sauvegarde automatique
```

## Prérequis

- PHP 7.4 ou supérieur
- Apache avec mod_rewrite (ou Nginx)
- Le dossier `data/` doit être accessible en écriture par PHP

## Installation

### 1. Hébergement mutualisé (OVH, Hostinger, LWS, o2switch...)

1. Connectez-vous à votre panneau d'hébergement
2. Accédez au gestionnaire de fichiers (ou utilisez FileZilla/FTP)
3. Uploadez tous les fichiers dans le dossier `www/` ou `public_html/`
4. C'est prêt ! Accédez à `https://votredomaine.com`

### 2. Test en local

```bash
cd ferme-avicole-php
php -S localhost:8000
```

Puis ouvrez **http://localhost:8000** dans votre navigateur.

### 3. VPS (Ubuntu/Debian)

```bash
# Installer PHP + Apache
sudo apt update
sudo apt install -y apache2 php libapache2-mod-php

# Copier les fichiers
sudo cp -r ferme-avicole-php/* /var/www/html/

# Donner les droits d'écriture au dossier data/
sudo chown -R www-data:www-data /var/www/html/data/
sudo chmod 755 /var/www/html/data/

# Redémarrer Apache
sudo systemctl restart apache2
```

## API PHP

| URL | Méthode | Description |
|-----|---------|-------------|
| `api.php?action=read` | GET | Lire toutes les données |
| `api.php?action=save` | POST | Sauvegarder les données |
| `api.php?action=export` | GET | Télécharger une sauvegarde |
| `api.php?action=import` | POST | Importer un fichier JSON |
| `api.php?action=reset` | POST | Réinitialiser |

## Sécurité

- Le dossier `data/` est protégé par `.htaccess` (accès direct bloqué)
- Les fichiers `.json` ne sont pas accessibles directement depuis le navigateur
- Seul `api.php` peut lire/écrire les données
- Une sauvegarde `data.json.bak` est créée automatiquement avant chaque écriture

### Recommandations supplémentaires

- Ajoutez un **certificat SSL** (HTTPS) — gratuit avec Let's Encrypt
- Pour protéger l'accès à l'application, ajoutez une authentification :

```php
// En haut de api.php et dans un fichier auth.php inclus dans index.html
session_start();
if (!isset($_SESSION['logged_in'])) {
    // Rediriger vers une page de login
}
```

## Sauvegardes automatiques (VPS)

```bash
# Cron : sauvegarde quotidienne à 2h du matin
0 2 * * * cp /var/www/html/data/data.json /var/www/backups/data_$(date +\%Y\%m\%d).json
```

## Nginx (alternative à Apache)

```nginx
server {
    listen 80;
    server_name ferme.votredomaine.com;
    root /var/www/ferme-avicole;
    index index.html;

    # Bloquer l'accès au dossier data/
    location /data/ {
        deny all;
        return 403;
    }

    # PHP
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```
