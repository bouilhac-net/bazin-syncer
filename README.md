# Syncer template

Template for sync platform (https://github.com/solutions-cvhu/syncer)


## Available modules

- Ovoko: solutions-cvhu/syncer-ovoko
- TotalParts: solutions-cvhu/syncer-totalparts
- Recambiofacil: coming soon
- France Casse: coming soon
- Vroomly: maybe someday


## Installation

> [!IMPORTANT]
> Before starting, read modules instructions.

1. Create a new repository from this template.
2. Copy `.env.dist` to `.env` and edit it
3. Edit `composer.json`:
   - add modules to `require` section (see [Available modules](#available-modules))
   - adjust PSR-4 namespace according to customer's name
4. Copy `config.php.dist` to `config.php` and edit it:
   - set database parameters (line 6)
   - set Telegram channel if needed (line 13)
   - set Opisto's API parameters (lines 16-19)
   - add config for each module (see module's documentation)
5. Start docker stack: `docker compose up`
6. Install dependencies: `docker exec -i syncer-CUSTOMER-php-1 composer install --no-dev`
7. Initialise project: `./vendor/bin/syncer init`
8. Initialise Opisto module: `./vendor/bin/syncer opisto:db:init`
9. Sync parts from Opisto: `./vendor/bin/syncer opisto:parts:sync --from 2000-01-01`

### Ovoko module
1. Initialise module: `./vendor/bin/syncer ovoko:init`
2. Fill the color matching table `ovoko_opisto_matching_color` using `opisto_color` and `ovoko_color` tables
3. Test sync:
    - add `LIMIT` in `SolutionsCVHU\Syncer\Ovoko\Manager\VehicleManager` at line 51
    - `./vendor/bin/syncer ovoko:vehicles:sync`
    - `./vendor/bin/syncer ovoko:parts:sync`
    - check that everything is ok in Ovoko interface and in logs
5. When sync is validated:
    - revert `LIMIT`
    - remove everything from Ovoko interface
    - truncate `ovoko_opisto_matching_part` and `ovoko_opisto_matching_vehicle` tables


## Deployment in production

### VPS installation
1. Follow instructions: https://gist.github.com/ybouilhac/6fb5fa4951281092742c816dcfc2c828 (without nginx-proxy section)
2. Install htpasswd: `sudo apt install apache2-utils`

### Syncer installation
1. Clone project in `/home/docker/syncer/`
2. Create htpasswd file: `htpasswd -c /home/docker/syncer/files/nginx/.htpasswd <username>`
3. Dump local database
4. Copy dump: `scp dump.sql docker@<customer>.sync.solutions-cvhu.fr:~/`
5. Copy local configuration file: `scp config.php docker@<customer>.sync.solutions-cvhu.fr:/home/docker/syncer/config.php`
6. Connect to Portainer interface and create a new stack using `files/stack.yml`
7. Import dump: `docker exec -it syncer-db-1 -u<DB_USER> -p<DB_PASS> <DB_NAME> < dump.sql`
8. Install dependencies: `docker exec -i syncer-php-1 composer install --no-dev`

#### Ovoko
1. Sync vehicles: `docker exec -i syncer-php-1 ./vendor/bin/syncer ovoko:vehicles:sync`
2. Sync parts: `docker exec -i syncer-php-1 ./vendor/bin/syncer ovoko:parts:sync`

### Cron
In Portainer, edit stack and uncomment line to enable cron tasks.
