.ONESHELL: # Applies to every targets in the file!

DOCKER_COMPOSE = docker-compose -f ./docker/docker-compose.yaml --env-file ./docker/.env

up:
	${DOCKER_COMPOSE} up -d --remove-orphans

down:
	${DOCKER_COMPOSE} down -v --rmi=all --remove-orphans

build:
	${DOCKER_COMPOSE} build

##################

validate-cs:
	${DOCKER_COMPOSE} exec -u www-data php vendor/bin/phpcs ./

validate-psalm:
	${DOCKER_COMPOSE} exec -u www-data php vendor/bin/psalm --no-cache

##################

.SILENT: add-user
add-user:
	if [ "${userName}" = "" ]; then \
	    echo "\033[0;31m"userName ARG must be provided; \
 		exit 1;
	fi
	${DOCKER_COMPOSE} exec nginx sh -c "echo -n '${userName}:' >> /etc/nginx/.htpasswd"
	${DOCKER_COMPOSE} exec nginx sh -c 'openssl passwd -apr1 >> /etc/nginx/.htpasswd'
