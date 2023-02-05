args = $(foreach a,$($(subst -,_,$1)_args),$(if $(value $a),$a="$($a)"))

.ONESHELL: # Applies to every targets in the file!
.PHONY:

DOCKER_COMPOSE = docker-compose -f ./docker/docker-compose.yaml --env-file ./docker/.env

up:
	${DOCKER_COMPOSE} up -d --remove-orphans --build

down:
	${DOCKER_COMPOSE} down

destroy:
	${DOCKER_COMPOSE} down -v --rmi=all --remove-orphans

build:
	make validate-cs validate-psalm &&
	${DOCKER_COMPOSE} build

.SILENT:release
release:
	helm upgrade -i "hurma-automation" .helm \
		-f .helm/values.local.yaml \
		--namespace=hurma \
		--create-namespace \
		--atomic \
		--history-max=3

##################

validate-cs:
	${DOCKER_COMPOSE} exec -u www-data php vendor/bin/phpcs ./

validate-psalm:
	${DOCKER_COMPOSE} exec -u www-data php vendor/bin/psalm --no-cache

##################

.SILENT:user-show
user-show:
	${DOCKER_COMPOSE} exec nginx sh -c "cat /etc/nginx/.htpasswd"

.SILENT:user-add
user-add:
	if [ "${user}" = "" ]; then \
	    echo "\033[0;31m"user must be provided: user=%user%; \
 		exit 1;
	fi

	${DOCKER_COMPOSE} exec nginx sh -c "echo -n '${user}:' >> /etc/nginx/.htpasswd"
	${DOCKER_COMPOSE} exec nginx sh -c 'openssl passwd -apr1 >> /etc/nginx/.htpasswd'
