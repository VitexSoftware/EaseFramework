all: build install

fresh:
	git pull
	composer update

install:
	echo install
	
build:
	echo build

clean:
	rm -rf debian/ease-framework
	rm -rf debian/ease-framework-doc
	rm -rf debian/*.log
	rm -rf docs/*

doc:
	debian/apigendoc.sh

test:
	composer update
	echo sudo service postgresql start ; sudo service postgresql start
	phpunit --bootstrap tests/Bootstrap.php

deb:
	debuild -i -us -uc -b

.PHONY : install
	
