![EasePHP Framework Logo](https://raw.githubusercontent.com/VitexSoftware/EaseFramework/master/project-logo.png "Project Logo")

EasePHP Framework
=================

Object oriented PHP Framework for easy&fast writing small/middle sized apps.

[![Source Code](http://img.shields.io/badge/source-VitexSoftware/EaseFramework-blue.svg?style=flat-square)](https://github.com/VitexSoftware/EaseFramework)
[![Latest Version](https://img.shields.io/github/release/VitexSoftware/EaseFramework.svg?style=flat-square)](https://github.com/VitexSoftware/EaseFramework/releases)
[![Software License](https://img.shields.io/badge/license-GPL-brightgreen.svg?style=flat-square)](https://github.com/VitexSoftware/EaseFramework/blob/master/LICENSE)
[![Build Status](https://img.shields.io/travis/VitexSoftware/EaseFramework/master.svg?style=flat-square)](https://travis-ci.org/VitexSoftware/EaseFramework)
[![Coverage Status](https://img.shields.io/coveralls/VitexSoftware/EaseFramework/master.svg?style=flat-square)](https://coveralls.io/r/VitexSoftware/EaseFramework?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/vitexsoftware/ease-framework.svg?style=flat-square)](https://packagist.org/packages/vitexsoftware/ease-framework)
[![Docker pulls](https://img.shields.io/docker/pulls/vitexsoftware/easephpframework.svg)](https://hub.docker.com/r/vitexsoftware/easephpframework/)

---

Installation
------------

To get Docker image:

    docker pull vitexsoftware/easephpframework

For Debian, Ubuntu & friends please use repo:

    wget -O - http://v.s.cz/info@vitexsoftware.cz.gpg.key|sudo apt-key add -
    echo deb http://v.s.cz/ stable main > /etc/apt/sources.list.d/ease.list
    aptitude update
    aptitude install ease-framework

In this case please add this to your app composer.json:

    "require": {
        "ease-framework": "*"
    },
    "repositories": [
        {
            "type": "path",
            "url": "/usr/share/php/Ease",
            "options": {
                "symlink": true
            }
        }
    ]



Docker:
-------

    pull vitexus/ease-framework

Composer:
---------
    composer require vitexsoftware/ease-framework


Framework Constants
===================

  * EASE_APPNAME - common name of application. Mainly used in logs.
  * EASE_LOGGER  - one of memory,console,file,syslog or combination "console|syslog"
  * EASE_SMTP    - Custom SMTP Settings (JSON Encoded) 
  * DB_TYPE      - pgsql|mysql|sqlite|...
  * DB_HOST      - localhost is default 
  * DB_PORT      - database port 
  * DB_DATABASE  - database schema name
  * DB_USERNAME  - database user login name
  * DB_PASSWORD  - database user password



Example
=======

Twitter Bootstrap page with simple Form
----------------------

```php
$oPage = new \Ease\TWB\WebPage('Page title');
$form = $oPage->addItem( new \Ease\TWB\Form('ExampleForm') );
$form->addItem(new \Ease\Html\InputTextTag('Name'));
$form->addItem(new \Ease\TWB\SubmitButton('OK', 'success') );
$oPage->draw();
```

Testing
-------

At first you need initialise create sql user & database with login and password 
from testing/phinx.yml and initialise testing database by **phinx migrate** 
command:

```
composer update
cd tests
mysqladmin -u root -p create easetest
mysql -u root -p -e "GRANT ALL PRIVILEGES ON easetest.* TO easetest@localhost IDENTIFIED BY 'easetest'"
sudo -u postgres bash -c "psql -c \"CREATE USER easetest WITH PASSWORD 'easetest';\""
sudo -u postgres bash -c "psql -c \"create database easetest with owner easetest encoding='utf8' template template0;\""
../vendor/bin/phinx migrate -e development 
../vendor/bin/phinx migrate -e testing  
```

Building
--------

Simply run debian/deb-package.sh

For Docker:

    docker build -t vitexus/ease-framework .
    docker push vitexus/ease-framework


Links
=====

Homepage: https://www.vitexsoftware.cz/ease.php

GitHub: https://github.com/VitexSoftware/EaseFramework

Apigen Docs: https://www.vitexsoftware.cz/ease-framework/

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/4900ce8c-8619-4007-b2d6-0ac830064963/big.png)](https://insight.sensiolabs.com/projects/4900ce8c-8619-4007-b2d6-0ac830064963)
