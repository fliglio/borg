NAME=borg

LOCAL_DEV_PORT=8000
LOCAL_DEV_IMAGE=fliglio/rabbitmq


clean: clean-localdev clean-test
	rm -rf build

#
# Local Dev
#

clean-localdev:
	@ID=$$(docker ps | grep "$(NAME)" | awk '{ print $$1 }') && \
		if test "$$ID" != ""; then docker kill $$ID; fi
	@ID=$$(docker ps -a | grep "$(NAME)" | awk '{ print $$1 }') && \
		if test "$$ID" != ""; then docker rm $$ID; fi

run: clean-localdev
	docker run -p $(LOCAL_DEV_PORT):80 -p 8508:8500 -p 15678:15672 -v $(CURDIR)/:/var/www/  -e "DOC_ROOT=/var/www/src/test/httpdocs/" --name $(NAME) $(LOCAL_DEV_IMAGE) 

configure:
	docker run -v $(CURDIR)/:/var/www/ --link $(NAME):consul $(LOCAL_DEV_IMAGE) /var/www/vendor/bin/chinchilla

#
# Test
#
# Removing component tests from test until we can resolve this running on multiple PHP versions
#

test: unit-test

unit-test:
	php ./vendor/bin/phpunit -c phpunit.xml --testsuite unit

component-test: clean-test component-test-setup component-test-run component-test-teardown

clean-test:
	@ID=$$(docker ps | grep "$(NAME)-test" | awk '{ print $$1 }') && \
		if test "$$ID" != ""; then docker kill $$ID; fi
	@ID=$$(docker ps -a | grep "$(NAME)-test" | awk '{ print $$1 }') && \
		if test "$$ID" != ""; then docker rm $$ID; fi
	rm -rf build/test

component-test-setup:
	@echo "Bootstrapping component tests..."
	@mkdir -p build/test/log
	@docker run -t -d -p 80 -v $(CURDIR)/:/var/www/ -v $(CURDIR)/build/test/log/:/var/log/nginx/ -e "DOC_ROOT=/var/www/src/test/httpdocs/" --name $(NAME)-test $(LOCAL_DEV_IMAGE)
	@sleep 5
	@echo "Configuring chinchilla..."
	@docker run -v $(CURDIR)/:/var/www/ --link $(NAME)-test:consul $(LOCAL_DEV_IMAGE) /var/www/vendor/bin/chinchilla
	@sleep 10

component-test-run:
	docker run -v $(CURDIR)/:/var/www/ --link $(NAME)-test:localdev $(LOCAL_DEV_IMAGE) /var/www/vendor/bin/phpunit -c /var/www/phpunit.xml --testsuite component

component-test-teardown:
	@ID=$$(docker ps | grep "$(NAME)-test" | awk '{ print $$1 }') && \
		if test "$$ID" != ""; then docker kill $$ID > /dev/null; fi
	@ID=$$(docker ps -a | grep "$(NAME)-test" | awk '{ print $$1 }') && \
		if test "$$ID" != ""; then docker rm $$ID > /dev/null; fi