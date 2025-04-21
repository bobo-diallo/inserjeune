# Installation inserjeune

1.      Concervez votre fichier .env et dézipper l'application inserjeune-v345_xxx.zip, puis dans la console :
2.      Mettre les droits avec le user Apache (chown -R <user>:<group> <dossier inserjeune>
3.      Configurer le fichier webpack.config.js
A la ligne 14 et 16, remarquez qu’on utilise des variables d’environnements `OUTPU_PATH` et `PUBLIC_PATH`
pour configurer respectivement le dossier contenant les fichiers js et css compilés, et l’url d’accès à ces
fichiers. 
5.      Définissez ces variables dans votre fichier .env. Par défaut PUBLIC_PATH=/build et OUTPUT_PATH=/public/build.

Pour les ajouter sur les fichier .env voici un exemple:\
`PUBLIC_PATH=/build`\
`OUTPUT_PATH=/public/build`

* Si l'url d'accès à l'application est : https:<nom_domaine>/fr/login : `PUBLIC_PATH=/build`

* Si l'url d'accès à l'application est : https:<nom_domaine>inserjeune-v345/public/fr/login :
`PUBLIC_PATH=/inserjeune-v342/public/build`

* Si l'url d'accès à l'application est : https:<nom_domaine>/public/fr/login :
`PUBLIC_PATH=/public/build`

6.     Configurer les accès à la base de données dans le fichier .env en définissant la variable DATABASE_URL.
Voici un exemple: \
`DATABASE_URL=”mysql://[db_user]:[db_pass]@[IP_server:PORT]/[db_name]` \
`DATABASE_URL=”mysql://root/motDePass@127.0.0.1:3306/db_inserjeune`


7.     Configurer le mailer pour les envois de mails

Configuration des adresses mails \
Remarquez que dans le fichier config/services.yaml, à la ligne 17 et 18, on utilise des variables
d’environnements pour configurer les adresses mail utilisées pour les envoi de mail.
Définissez ces variables dans votre fichier .env \
Voici un exemple:\
`EMAIL_FROM=nepasrepondre@nomDeDomain.com` \
`EMAIL_REPLY_TO=nepasrepondre@nomDeDomain.com`

Définition des paramètres SMTP \
Définir aussi les paramètres du serveur SMTP sur le fichier .env
Voici un exemple:\

MAILER_DSN=smtp://[adr_mail_postmaster]@[domain_mail]:[mdp_mail_postmaster]@[adr_server_smtp]:[port_server_smtp] \
MAILER_DSN=smtp://nepasrepondre@monDomaine.extension:monMotDePasse@ssl0.ovh.net:587et:587

		a. Installer les librairies php
En ligne de commande, en étant à la racine du projet, exécutez la commande \
`composer install`

Si vous obtenez une erreur avec la librairie omines/datatables-bundle, exécuter la commande suivant \
`composer update omines/datatables-bundle`

		b. Mettre à jour la base de données

En ligne de commande, en étant à la racine du projet, exécutez la commande \
`php bin/console d:m:m -n`

		c. Compilez les fichiers js et css 
En ligne de commande, en étant à la racine du projet, exécutez la commande

`npm install` \
`npm build`

 		d. Personnaliser les logos, liens utiles et contacts
Page d'identification :

* Fichier `templates/base_security.html.twig`

Décommenter ligne 26 : `{{ include ('_customize_header_login.html.twig') }}` \
Commenter les lignes 27 et 28 \

* Fichier templates/_customize_header_login.html.twig \
Remplacer les noms des fichiers images logo_ifef.png et logo_oif.png par les vôtres \
Copier les fichiers images adans le dossier approprié

		e- Hearder commun aux autres vues : fichier templates/_banner_top.html.twig
			remplacer les noms des fichiers images logo_ifef.png et logo_oif.png par les votres
			
		f- Footer commun aux autres pages : fichier templates/_footer_customize.html.twig
			Adapter en fonction des besoins

