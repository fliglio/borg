#!/bin/bash

docker ps -aq | xargs docker kill
docker ps -aq | xargs docker rm



docker run -d \
	-p 5672:5672 -p 15672 \
	--name borg-test-rabbitmq \
	rabbitmq:3.6-management
	
docker run -d \
	-p 8500:8500 -p 172.20.20.1:8600:8600/udp \
	--name=borg-test-consul \
	gliderlabs/consul-server:latest -bootstrap -advertise=172.20.20.1
	

