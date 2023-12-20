# Todo & Co
Project 8 - OC

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

MAILER_DSN=smtp://user:pass@smtp.example.com:port
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

```symfony console doctrine:fixtures:load```