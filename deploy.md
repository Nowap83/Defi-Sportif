# Documentation de Déploiement AgoraFit

## Vue d'ensemble

Ce document décrit le processus de déploiement automatisé de l'application AgoraFit sur une VM Proxmox via GitHub Actions.

## Architecture

### Infrastructure
- **Hébergement** : VM Proxmox
- **Chemin de déploiement** : `/var/www/Defi-Sportif`
- **Port SSH** : 2222
- **CI/CD** : GitHub Actions

### Stack Technique
- **Frontend** : Node.js 20
- **Backend** : PHP 8.2 avec Symfony
- **Extensions PHP** : mbstring, pdo, pdo_mysql, intl, zip

## Configuration Préalable

### 1. Configuration de la VM Proxmox

#### Installation des dépendances système sur système Debian

```bash
# Mise à jour du système
sudo apt update && sudo apt upgrade -y

# Installation de PHP 8.2 et extensions
sudo apt install php8.2 php8.2-mbstring php8.2-pdo php8.2-mysql php8.2-intl php8.2-zip php8.2-fpm -y

# Installation de Node.js 20
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install nodejs -y

# Installation de Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Installation de Git
sudo apt install git -y
```

#### Configuration SSH

```bash
# Éditer la configuration SSH pour utiliser le port 2222
sudo nano /etc/ssh/sshd_config

# Ajouter ou modifier la ligne :
Port 2222

# Redémarrer le service SSH
sudo systemctl restart sshd
```

#### Création du répertoire de déploiement

```bash
# Créer le répertoire
sudo mkdir -p /var/www/Defi-Sportif

# Définir les permissions
sudo chown -R xander:www-data /var/www/Defi-Sportif
sudo chmod -R 755 /var/www/Defi-Sportif
```

#### Configuration Git

```bash
cd /var/www/Defi-Sportif
git init
git remote add origin https://github.com/votre-username/AgoraFit.git
git pull origin main
```

### 2. Configuration des Secrets GitHub

Ajouts des secrets via : Settings > Secrets and variables > Actions dans le repository Defi-Sportif :

SERVER_HOST 	
SERVER_USER 	
SSH_PRIVATE_KEY 	

#### Sur votre machine locale

```bash
ssh-keygen -t rsa -b 4096 -C "github-deploy" -f ~/.ssh/agorafit_deploy
```
#### Copie de la clé publique sur le serveur

```bash
ssh-copy-id -i ~/.ssh/agorafit_deploy.pub -p 2222 user@votre-serveur
```

#### Copie du  contenu de la clé privée pour GitHub

```bash
cat ~/.ssh/agorafit_deploy
```

### 3. Configuration Backend (Symfony)
Fichier .env

#### Sur le serveur

```bash
cd /var/www/Defi-Sportif/backend
nano .env.local
```

#### Ajout de :
APP_ENV=prod
APP_DEBUG=0
DATABASE_URL="mysql://user:password@127.0.0.1:3306/agorafit"

#### Permissions

```bash
cd /var/www/Defi-Sportif/backend
sudo chown -R www-data:www-data var/
sudo chmod -R 775 var/
```

## Workflow de Déploiement

### Déclenchement

Le déploiement se déclenche automatiquement à chaque push sur la branche main.

Étapes du Pipeline

#### 1. Checkout du Code

- name: Checkout code
  uses: actions/checkout@v3

Récupère le code source depuis le repository.

#### 2. Configuration Node.js

- name: Setup Node.js
  uses: actions/setup-node@v3
  with:
    node-version: '20'

Installe Node.js version 20 pour le build du frontend.

#### 3. Build du Frontend

- name: Install frontend dependencies & build
  working-directory: frontend
  run: |
    npm ci
    npm run build

npm ci : Installation propre des dépendances
npm run build : Compilation du frontend pour la production

#### 4. Configuration PHP

- name: Setup PHP
  uses: shivammathur/setup-php@v2
  with:
    php-version: '8.2'
    extensions: mbstring, pdo, pdo_mysql, intl, zip

Configure PHP 8.2 avec les extensions nécessaires.

#### 5. Installation des Dépendances Backend

- name: Install backend dependencies
  working-directory: backend
  run: |
    rm -rf var/cache/*
    composer install --no-dev --optimize-autoloader --no-scripts
    composer dump-autoload --optimize

Nettoie le cache
Installe les dépendances PHP sans les packages de développement
Optimise l'autoloader pour la production

#### 6. Déploiement sur le Serveur

- name: Deploy to server
  uses: appleboy/ssh-action@v1.0.3
  with:
    host: ${{ secrets.SERVER_HOST }}
    username: ${{ secrets.SERVER_USER }}
    key: ${{ secrets.SSH_PRIVATE_KEY }}
    port: 2222
    script: |
      cd /var/www/Defi-Sportif
      git pull origin main
      cd frontend && npm ci && npm run build
      cd ../backend && composer install --no-dev --optimize-autoloader
      php bin/console cache:clear --env=prod

Connexion SSH au serveur et exécution des commandes de déploiement.

Maintenu par : Alexandre BROZZU
Dernière mise à jour : 03/09/25
Version : 1.0