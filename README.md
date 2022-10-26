# Inserjeune V2

## Setup app
1. Install dependencies with: ````composer install````
2. Install database with: ````php bin/console d:d:c ````
3. Excecute migrations with: ````php bin/console d:m:m ````
4. Add fixtures with: ````php bin/console d:f:l ````
5. Install npm packages: ````npm install ````
6. Generate public assets: ````npm run build ````
7. Run server: ````symfony server:start ````

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

## Consume send mail
``php bin/console messenger:consume async``
