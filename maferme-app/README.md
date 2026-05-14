# 🐔 Ferme Avicole — Application de Gestion

Application web de gestion complète pour ferme avicole (poulets de chair).
Backend Node.js + Express, données stockées dans un fichier JSON.

## Fonctionnalités

- **Tableau de bord** avec KPIs en temps réel
- **Achats** : poussins, aliments, médicaments, matériel
- **Dépenses** : électricité, salaires, transport, etc.
- **Ventes** : suivi clients, quantités, paiements
- **Mortalité** : relevés par bande et par cause
- **Stock** : suivi avec alertes de seuil + recherche
- **Bandes** : rentabilité par cycle d'élevage
- **Paramètres** : catégories personnalisables
- **Export/Import JSON** pour sauvegardes
- **Responsive** : optimisé téléphone, tablette, PC

## Installation

```bash
# 1. Cloner ou copier le projet
cd ferme-avicole-app

# 2. Installer les dépendances
npm install

# 3. Démarrer le serveur
npm start
```

L'application sera accessible sur **http://localhost:3000**

## Structure du projet

```
ferme-avicole-app/
├── server.js          # Serveur Express + API REST
├── package.json       # Dépendances Node.js
├── data.json          # Données (créé automatiquement au 1er lancement)
├── data.json.bak      # Sauvegarde automatique avant chaque écriture
├── README.md          # Ce fichier
└── public/
    └── index.html     # Interface web (HTML/CSS/JS)
```

## API REST

| Méthode | Route | Description |
|---------|-------|-------------|
| GET | `/api/data` | Lire toutes les données |
| PUT | `/api/data` | Sauvegarder toutes les données |
| GET | `/api/data/export` | Télécharger une sauvegarde JSON |
| POST | `/api/data/import` | Importer un fichier JSON |
| POST | `/api/data/reset` | Réinitialiser les données |

## Déploiement en production

### Sur un VPS (Ubuntu/Debian)

```bash
# Installer Node.js
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Copier le projet
scp -r ferme-avicole-app/ user@serveur:/home/user/

# Sur le serveur
cd /home/user/ferme-avicole-app
npm install

# Lancer avec PM2 (recommandé pour la production)
npm install -g pm2
pm2 start server.js --name ferme-avicole
pm2 save
pm2 startup
```

### Variables d'environnement

| Variable | Défaut | Description |
|----------|--------|-------------|
| `PORT` | `3000` | Port d'écoute |

Exemple : `PORT=8080 npm start`

### Avec Nginx (reverse proxy)

```nginx
server {
    listen 80;
    server_name ferme.votredomaine.com;

    location / {
        proxy_pass http://127.0.0.1:3000;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    }
}
```

## Sauvegardes

Le fichier `data.json` contient toutes vos données. Pour sauvegarder :

```bash
# Sauvegarde manuelle
cp data.json data_backup_$(date +%Y%m%d).json

# Ou via cron (tous les jours à 2h du matin)
0 2 * * * cp /home/user/ferme-avicole-app/data.json /home/user/backups/data_$(date +\%Y\%m\%d).json
```

L'application crée aussi automatiquement un `data.json.bak` avant chaque écriture.

## Devise

L'application utilise le **FCFA (XAF)** par défaut.
