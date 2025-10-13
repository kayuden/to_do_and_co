# ToDo & Co

Project 8 : Améliorez une application existante de ToDo & Co - Formation OC : Développeur d'application - PHP/Symfony

Ce projet consiste à développer une application de gestion de tâches collaborative à partir du framework Symfony.

---

## Prérequis

Avant d'installer le projet, assurez-vous d'avoir les éléments suivants :

- [**PHP >= 8.1**](https://www.php.net/downloads.php)  
- [**Composer**](https://getcomposer.org/download/)  
- [**Symfony CLI**](https://symfony.com/download) (facultatif mais recommandéd)  
- [**PostgreSQL**](https://www.postgresql.org/download) ou un autre moteur de BDD compatible avec Doctrine ORM
- [**Git**](https://git-scm.com/downloads) pour cloner le dépôt
- Un navigateur web moderne

---

## Installation

Suivez ces étapes pour installer le projet en local :

### 1. Clôner le dépôt
```bash
git clone https://github.com/kayuden/to_do_and_co.git
cd to_do_and_co
```

### 2. Installer les dépendances
```bash
composer install
```

### 3. Configurer les variables d'environnement
Dupliquez le fichier `.env` pour créer un fichier `.env.local`.

Modifiez `.env.local` pour configurer votre connexion à la base de donnée (remplacez par vos propres identifiants):
```
DATABASE_URL="mysql://utilisateur:motdepasse@127.0.0.1:3306/todoandco?serverVersion=8.0.32&charset=utf8mb4"
```

### 4. Créer la base de données
```bash
php bin/console doctrine:database:create
```

### 5. Exécuter les migrations
```bash
php bin/console doctrine:migrations:migrate
```

### 6. Charger les fixtures
```bash
php bin/console doctrine:fixtures:load
```

### 7. Démarrer le seveur local
Avec Symfony CLI:
```bash
symfony serve
```

L'application sera accessible à l'adresse :  
[http://127.0.0.1:8000](http://127.0.0.1:8000)

---

## Contact

Pour toute question ou remarque, n'hésitez pas à contacter **kbartholomot@gmail.com**
