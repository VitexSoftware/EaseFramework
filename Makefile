DESTDIR ?= /usr/local
libdir  ?= /share/php
docdir  ?= /doc/ease-framework/html

all: build install

fresh:
	git pull origin master
	PACKAGE=`cat debian/composer.json | grep '"name"' | head -n 1 |  awk -F'"' '{print $4}'`; \
	VERSION=`cat debian/composer.json | grep version | awk -F'"' '{print $4}'`; \
	dch -b -v "${VERSION}" --package ${PACKAGE} "$CHANGES" \
	
install:
	mkdir -p $(DESTDIR)$(libdir)
	cp -r src/Ease/ $(DESTDIR)$(libdir)
	mkdir -p $(DESTDIR)$(docdir)
	cp -r docs $(DESTDIR)$(docdir)
	
build: doc
	echo build;	

clean:
	rm -rf vendor composer.lock
	rm -rf debian/ease-framework
	rm -rf debian/ease-framework-doc
	rm -rf debian/*.log debian/tmp
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

rpm:
	rpmdev-bumpspec --comment="`git log -1 --pretty=%B`" --userstring="Vítězslav Dvořák <info@vitexsoftware.cz>" rpm.spec
	rpmbuild -ba rpm.spec

release: fresh deb
	

.PHONY : install build
	
