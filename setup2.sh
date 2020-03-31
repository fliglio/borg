#!/bin/bash


consul_host="$(docker inspect -f '{{ .NetworkSettings.IPAddress }}' borg-test-consul)"
rabbit_host="$(docker inspect -f '{{ .NetworkSettings.IPAddress }}' borg-test-rabbitmq)"

docker run -d \
	--name borg-test-chinchilla \
	--link borg-test-rabbitmq \
	--link borg-test-consul:consul \
	-e "CONSUL_HTTP_ADDR=${consul_host}:8500" \
	-e "RABBITMQ_HOST=$rabbit_host" \
	-e RABBITMQ_USER=guest \
	-e RABBITMQ_PASSWORD=guest \
	benschw/chinchilla


echo "Configuring chinchilla..."

docker run \
	-v `pwd`/:/var/www/ \
	--link borg-test-consul:consul \
	fliglio/rabbitmq chinchilla

