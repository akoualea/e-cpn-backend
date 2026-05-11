# Guide de Déploiement de l'API Laravel e-CPN

## Introduction
Ce document décrit les étapes concrètes pour déployer l'API Laravel de votre projet e-CPN en production. Le fichier `.env` est déjà configuré pour la production (APP_ENV=production, APP_DEBUG=false, APP_URL=https://api-e-cpn.iwajutech.com/). Vérifiez et ajustez si nécessaire.

## Fichiers à Vérifier/Modifier

### 1. Fichier : `.env` (à la racine du projet)
- **Statut** : Déjà configuré pour production. Vérifiez les valeurs sensibles (mots de passe, clés API) pour l'environnement de production.
- **Changements potentiels** :
  - Assurez-vous que `APP_URL=https://api-e-cpn.iwajutech.com/` (HTTPS recommandé).
  - `DB_CONNECTION=pgsql` avec les credentials Supabase pour production.
  - `GOOGLE_REDIRECT_URI` et `FRONTEND_URL` pointent vers production.
- **Important** : Ne commitez jamais ce fichier. Copiez-le manuellement sur le serveur.

**Contenu actuel (masqué pour sécurité) :**
- APP_ENV=production
- APP_DEBUG=false
- APP_URL=https://api-e-cpn.iwajutech.com/
- DB_* : Configuré pour Supabase
- Etc.

### 2. Fichier : `config/app.php`
- **Statut** : Correct, utilise les variables d'environnement.
- **Aucun changement nécessaire.**

### 3. Fichier : `config/database.php`
- **Statut** : Correct, utilise les variables d'environnement.
- **Aucun changement nécessaire.**

### 4. Fichier : `routes/api.php`
- **Statut** : Vérifié, aucune URL hardcodée. Utilise des routes relatives.
- **Aucun changement nécessaire.**

### 5. Fichier : `config/mail.php`
- **Statut** : Utilise les variables d'environnement (défaut : 'log').
- **Si vous utilisez l'email en production** : Ajoutez dans `.env` :
  - MAIL_MAILER=smtp
  - MAIL_HOST=votre-smtp-host
  - MAIL_PORT=587
  - MAIL_USERNAME=votre-email
  - MAIL_PASSWORD=votre-mot-de-passe
  - MAIL_ENCRYPTION=tls

### 6. Fichier : `public/.htaccess` (pour Apache)
- **Statut** : Présent et standard pour Laravel.
- **Si vous utilisez Nginx** : Ignorez ce fichier et utilisez la config ci-dessous.

## Configuration du Serveur Web

### Pour Nginx (recommandé pour API)
Ajoutez ce bloc dans `/etc/nginx/sites-available/api-e-cpn.iwajutech.com` :
```
server {
    listen 80;
    server_name api-e-cpn.iwajutech.com;
    root /chemin/vers/projet/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location ~ /\.ht {
        deny all;
    }
}
```
- Activez avec : `sudo ln -s /etc/nginx/sites-available/api-e-cpn.iwajutech.com /etc/nginx/sites-enabled/`
- Redémarrez : `sudo systemctl reload nginx`

### Pour Apache (si utilisé)
- Assurez-vous que `mod_rewrite` est activé.
- Le `.htaccess` gère les rewrites.

## Étapes de Déploiement
1. **Upload des fichiers** : Envoyez tout le projet sur le serveur (sauf `vendor/` si vous installez sur place).
2. **Installer dépendances** : `composer install --no-dev --optimize-autoloader`
3. **Permissions** : `sudo chown -R www-data:www-data /chemin/vers/projet` et `chmod -R 775 storage/ bootstrap/cache/`
4. **Base de données** : `php artisan migrate` (si nécessaire).
5. **Cache** : `php artisan config:cache` et `php artisan route:cache`
6. **SSL** : Installez un certificat Let's Encrypt : `sudo certbot --nginx -d api-e-cpn.iwajutech.com`
7. **Test** : Accédez à `https://api-e-cpn.iwajutech.com/api` et vérifiez les logs.

## Dépannage
- **Erreur 404** : Vérifiez la config serveur et les permissions.
- **Erreur DB** : Vérifiez les credentials dans `.env`.
- **Logs** : `tail -f storage/logs/laravel.log`
- **Test local** : `php artisan serve` avant déploiement.

Contactez pour assistance supplémentaire.</content>
<parameter name="filePath">c:\Users\HP\Desktop\projetsoutenance\e-cpn-backendvf\deployment_guide.md