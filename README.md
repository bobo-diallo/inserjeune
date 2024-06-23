# Inserjeune V2

## Setup app
1. Install dependencies with: ````composer install````
2. Install database with (for new installation): ````php bin/console d:d:c ```` 
3. Excecute migrations with: ````php bin/console d:m:m -n````
4. Add fixtures with (for test): ````php bin/console d:f:l ````
5. Install npm packages: ````npm install ````
6. Generate public assets: ````npm run build ````
7. Install asset for extra bundle: ````symfony console asset:install public````
8. Run server (if server is not configured): ````symfony server:start ````
9. Add directories for upload files `````mdkir -p public/uploads/brochures`````

### Simple installation
1. Setup project fixtures: ````make install````
2. Setup front: `````make install-front`````


### Setup with docker
1. Run server app with docker: `make start`
2. Install packages: `make composer-install`
3. Clear cache: `make cache`
4. Creation database and migrations: `make schema-update`
5. Load fixtures: `make run-fixtures`
6. Run app: `make server-run`
7. Run webpack: `make server-watch`

### Run app with one command
`make run-app`

## Send mail
1. To send emails asynchronously 
``php bin/console messenger:consume async``
2. To send emails synchronously, go to `messenger.yaml` and comment line
``Symfony\Component\Mailer\Messenger\SendEmailMessage: async`` 
3. To lunch enqueue worker at bacground, run command: `php bin/console messenger:consume async > /dev/null 2>&1 &`
4. To check if enqueue worker is running: `ps aux | grep "php bin/console messenger:consume"`
4. To kill processus: `pkill -f "php bin/console messenger:consume async"`
   `

 ## Config mailer with sendmail  
 On peut utiliser sendmail pour envoyer des mail. 
 Pour ce faire, il faut configurer le php.ini de apache (vérifier que la commande sendmail est installée)
1. Dans le fichier rechercher 'mail function'
2. dans SMTP = localhost, remplacer localhost par le IP du server de mail
3. smtp_port = 25, remplacer le bon port si ce n'est pas 25
4. Pour tester avec la console, taper: echo testing | mail -s mons_message monemail@gmail.com
5. Dans .env de symfony; mettre MAILER_DSN=sendmail://default ou MAILER_DSN=smtp://IP_SERVEUR:25 (forte chance que cette conf fonctionne plus)


## Last branch deployed in OIF
- IFEF-081


## Note sur CKeditor
ckeditor est devenu payant à partir de la version 4.23

Donc après installation du bundle par ` composer require friendsofsymfony/ckeditor-bundle`, il faut preciser la version js à installer avec 

`php bin/console ckeditor:install --tag=4.22.1 ` et faire `php bin/console assets:install public `

