# Présentation
Ce dépôt est une préparation à un tutoriel.
L'objectif de celui-ci est de permettre d'avoir un controller ici en l'occurence " GeneralController " qui s'occupe de généraliser le CRUD de chaque ressource 
==> Plutôt que de faire X contrôleurs et répéter les mêmes actions, le but de ce contrôleur est de généraliser les actions autour d'un seul contrôleur.

# Présentation plus technique
Le code le plus important se trouve dans :
- src/Controller/GeneralController, et
- src/Utilities/Crud/* => namespace nous permettant de généraliser les actions
- Il faut aussi noter que les entités prennent l'annotation CrudAnnotation permettant de rajouter un peu de personnalisation au CRUD généralisé

# Utilisation
- `git clone ...`
- `bin/console doctrine:migrations:migrate -n` : exécution des migrations
- `bin/console doctrine:fixtures:load -n` : chargement des données de test
- `bin/console server:run` : démarrage du serveur php
- Il faut se rendre sur http://localhost:8000/general/_list pour jouer avec (et se laisser guider)
