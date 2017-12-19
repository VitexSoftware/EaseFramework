all: build install

fresh:
	git pull
	PACKAGE=`cat debian/composer.json | grep '"name"' | head -n 1 |  awk -F'"' '{print $4}'`; \
	VERSION=`cat debian/composer.json | grep version | awk -F'"' '{print $4}'`; \
	dch -b -v "${VERSION}" --package ${PACKAGE} "$CHANGES" \
	
install:
	echo install
	
build: doc
	echo build;	

clean:
	rm -rf vendor composer.lock
	rm -rf debian/ease-framework
	rm -rf debian/ease-framework-doc
	rm -rf debian/*.log
	rm -rf docs/*

doc:
	VERSION=`cat debian/composer.json | grep version | awk -F'"' '{print $4}'`; \
	apigen generate --source src --destination docs --title "Ease PHP Framework ${VERSION}" --charset UTF-8 --access-levels public --access-levels protected --php --tree


composer:
	composer update

test:
	echo sudo service postgresql start ; sudo service postgresql start
	phpunit --bootstrap tests/Bootstrap.php

deb:
	dch -i "`git log -1 --pretty=%B`"
	debuild -i -us -uc -b

.PHONY : install build
	
