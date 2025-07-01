# Plateforme de diffusion externe POS Bazin


## Installation

> [!IMPORTANT]
> Before starting, read modules instructions.

1. Clone repository
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
6. Install dependencies: `docker exec -i syncer-bazin-php-1 composer install --no-dev`
7. Initialise project: `docker exec -i syncer-bazin-php-1 ./vendor/bin/syncer init`
8. Initialise Opisto module: `docker exec -i syncer-bazin-php-1 ./vendor/bin/syncer opisto:db:init`
9. Sync parts from Opisto: `docker exec -i syncer-bazin-php-1 ./vendor/bin/syncer opisto:parts:sync --from 2000-01-01 --allow-in-parc`

### Ovoko module
1. Initialise module: `docker exec -i syncer-bazin-php-1 ./vendor/bin/syncer ovoko:init`
2. Fill the color matching table `ovoko_opisto_matching_color` using `opisto_color` and `ovoko_color` tables
3. Test sync:
    - add `LIMIT` in `SolutionsCVHU\Syncer\Ovoko\Manager\VehicleManager` at line 51
    - `docker exec -i syncer-bazin-php-1 ./vendor/bin/syncer ovoko:vehicles:sync`
    - `docker exec -i syncer-bazin-php-1 ./vendor/bin/syncer ovoko:parts:sync`
    - check that everything is ok in Ovoko interface and in logs
5. When sync is validated:
    - revert `LIMIT`
    - remove everything from Ovoko interface
    - truncate `ovoko_opisto_matching_part` and `ovoko_opisto_matching_vehicle` tables

### TotalParts module
1. Initialise module: `docker exec -i syncer-bazin-php-1 ./vendor/bin/syncer totalparts:init`
2. Test sync:
    - add `LIMIT` in `Bazin\Syncer\TotalParts\Manager\PartManager` at line 65
    - `docker exec -i syncer-bazin-php-1 ./vendor/bin/syncer totalparts:parts:sync`
    - check that everything is ok in logs
3. When sync is validated:
    - revert `LIMIT`
    - remove everything from TotalParts
    - truncate `totalparts_opisto_matching_part` table


## Deployment in production

### VPS installation
1. Follow instructions: https://gist.github.com/ybouilhac/6fb5fa4951281092742c816dcfc2c828 (without nginx-proxy section)
2. Install htpasswd: `sudo apt install apache2-utils`

### Syncer installation
1. Clone project in `/home/docker/syncer/`
2. Create htpasswd file: `htpasswd -c /home/docker/syncer/files/nginx/.htpasswd <username>`
3. Dump local database
4. Copy dump: `scp dump.sql docker@bazin.sync.solutions-cvhu.fr:~/`
5. Copy local configuration file: `scp config.php docker@bazin.sync.solutions-cvhu.fr:/home/docker/syncer/config.php`
6. Connect to Portainer interface and create a new stack using `files/stack.yml`
7. Import dump: `docker exec -it syncer-db-1 -u<DB_USER> -p<DB_PASS> <DB_NAME> < dump.sql`
8. Install dependencies: `docker exec -i syncer-php-1 composer install --no-dev`

#### Ovoko
1. Sync vehicles: `docker exec -i syncer-php-1 ./vendor/bin/syncer ovoko:vehicles:sync`
2. Sync parts: `docker exec -i syncer-php-1 ./vendor/bin/syncer ovoko:parts:sync`

#### TotalParts
1. Sync parts: `docker exec -i syncer-php-1 ./vendor/bin/syncer totalparts:parts:sync`

### Cron
In Portainer, edit stack and uncomment line to enable cron tasks.
