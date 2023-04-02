
# Hackathon 2023 groupe 3

Client : Jaji

Dans le cadre du Hackathon, nous avons repondue à la demande du client en faisant un "Back Office Content" en utilisant Symfony 6.


## Authors

- [@Ayman-BEDDA](https://github.com/Ayman-BEDDA)
- [@Maxime-Lao](https://github.com/Maxime-Lao)
- [@muthuxv](https://github.com/muthuxv)
- [@senex127](https://github.com/senex127)
- [@SimonBTSSio](https://github.com/SimonBTSSio)


## Installation du projet
Avant de commencer, veuillez mettre le .env présent dans le dossier technique, dans le dossier app/

Pour installer notre projet vous aurez besoin de :
 - docker / docker-compose
 - npm / node
 - composer

#### Lancer le projet
```bash
  docker-compose up -d
  composer install
  composer update   
```
Dans le dosier app/
```bash
  composer install
  composer update   
```
#### Base de données
```bash
  docker-compose exec php bin/console make:migration  
  docker-compose exec php bin/console d:m:m    
```

#### Génerer le css (Dans le dosier app/)
```bash
  npm install
  npm update  
  npm run watch
```