# Todo & Co
## Présentation du projet
Application permettant de gérer ses tâches quotidiennes, réalisée avec Symfony 6. Projet réalisé dans le cadre de la formation développeur d'application PHP/symfony d'OpenClassrooms.

[![Codacy Badge](https://app.codacy.com/project/badge/Grade/e4bb65b178d04ff6917bf666d4e4a0a4)](https://app.codacy.com/gh/kseb49/Todo-Co/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)

## Prè-requis

PHP
[**PHP 8.1**](https://www.php.net/downloads) ou supèrieur

MySQL
**MySQL 8.0** ou supèrieur.

Composer
[**Composer 2.4**](https://getcomposer.org/download/) ou supèrieur.

## Installation

Cloner le projet

```https://github.com/kseb49/Todo-Co.git```

Installer les dépendances

 ```composer install```

 Configurer un fichier .env.local avec vos valeurs:
 ```Dotenv
DATABASE_URL="mysql://app:!ChangeMe!@127.0.0.1:3306/app?serverVersion=8.0.32&charset=utf8mb4"
```
Pour une mise en production :

```Dotenv
APP_ENV=prod
APP_SECRET=!new32characterskey!
```

 Créez la base de données et les tables:

```symfony console doctrine:database:create```

```symfony console doctrine:migrations:migrate```

Charger les données initiales

```symfony console doctrine:fixtures:load --group=dev```

## Tests
Le code  est couvert par des tests unitaires et fonctionnels implémentés avec [PHPUnit](https://docs.phpunit.de/en/10.5/index.html).

Configurez le fichier .env.test
```Dotenv
DATABASE_URL="mysql://USERNAME:PASSWORD@127.0.0.1:3306/todoco?serverVersion=8.0.32&charset=utf8mb4"
```
Créez la base de données

```symfony console --env=test doctrine:database:create```

```symfony console doctrine:migrations:migrate -n --env=test ```

Chargez les fixtures pour les tests

```
symfony console --env=test doctrine:fixtures:load --group=test      
```

Pour lancer les tests

```
php bin/phpunit --coverage-html public/test-coverage 
```
Le rapport est disponible dans le dossier **/public/test-coverage/index.html**