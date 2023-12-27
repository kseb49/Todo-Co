# Contribuer à ToDo & Co
Ce qui suit est un ensemble de lignes directrices pour contribuer au projet.

Veuillez commencer par lire le [readme.md]("#") à la racine du projet.

# Signaler un bug
Vous pouvez signaler un bug en ouvrant un issue sur le [dépôt github](https://github.com/kseb49/Todo-Co/issues), sous l'étiquette bug.

Préciser les circonstances détaillées dans lequel le bug apparaît et **comment le reproduire**.

Suivez ensuite les [instructions](#instructions) pour faire une pull request.

# Proposer une fonctionnalitée
Pour une nouvelle fonctionnalitée proposer votre travail en ouvrant dabord un issue sur le [dépôt github](https://github.com/kseb49/Todo-Co/issues), sous l'étiquette new features.

Documentez y votre fonctionnalité le plus précisémment possible.

Suivez ensuite les [instructions](#instructions) pour faire une pull request.

# Instructions
-  Fork du [repositorie](https://github.com/kseb49/Todo-Co)
-  Cloner le projet
- Installer le sur votre machine ([readme.md](#))

Créer une nouvelle branche, nommez la avec un nom qui résume la nouvelle fonctionnalité ou bug et travaillez dans cette branche :
```bash
git checkout -b <nomdelabranche>
```
- Tester votre code

Le projet est couvert par des tests unitaires et fonctionnels implémentés avec [PHPUnit](https://docs.phpunit.de/en/10.5/index.html).

- Respecter les [standards de codages](#standards)

- Vérifier la qualité de votre code avec [Codacy](https://app.codacy.com)

- Commitez votre travail `git commit -am '<messagedescriptif>'`

- Pushez la branche `git push origin <nomdelabranche>`

- Sur Github envoyez une [Pull Request ](https://docs.github.com/fr/pull-requests/collaborating-with-pull-requests/proposing-changes-to-your-work-with-pull-requests/about-pull-requests) référencez y l'issue associé.

# Standards à respecter
[PSR1](https://www.php-fig.org/psr/psr-1/)

[PSR2](https://www.php-fig.org/psr/psr-2/)

[Symfony coding Standards](https://symfony.com/doc/current/contributing/code/standards.html)