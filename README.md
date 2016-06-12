EaseFramework
=============

Object oriented PHP Framework for easy&fast writing small/middle sized apps.

[![Source Code](http://img.shields.io/badge/source-Vitexus/EaseFramework-blue.svg?style=flat-square)](https://github.com/Vitexus/EaseFramework)
[![Latest Version](https://img.shields.io/github/release/Vitexus/EaseFramework.svg?style=flat-square)](https://github.com/Vitexus/EaseFramework/releases)
[![Software License](https://img.shields.io/badge/license-GPL-brightgreen.svg?style=flat-square)](https://github.com/Vitexus/EaseFramework/blob/master/LICENSE)
[![Build Status](https://img.shields.io/travis/Vitexus/EaseFramework/master.svg?style=flat-square)](https://travis-ci.org/Vitexus/EaseFramework)
[![Coverage Status](https://img.shields.io/coveralls/Vitexus/EaseFramework/master.svg?style=flat-square)](https://coveralls.io/r/Vitexus/EaseFramework?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/vitexsoftware/ease-framework.svg?style=flat-square)](https://packagist.org/packages/vitexsoftware/ease-framework)

---


Installation
------------

For Debian please use repo:

    wget -O - http://v.s.cz/info@vitexsoftware.cz.gpg.key|sudo apt-key add -
    echo deb http://v.s.cz/ stable main > /etc/apt/sources.list.d/ease.list
    aptitude update
    aptitude install ease-framework

In this case please add this to your app composer.json:

    "require": {
        "vitexsoftware/ease-framework": "1.0"
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
from testing/phinx.yml and initialise teting database by **phinx migrate** 
command

Links
=====

Homepage: https://www.vitexsoftware.cz/ease.php

GitHub: https://github.com/Vitexus/EaseFramework

Apigen Docs: https://www.vitexsoftware.cz/EaseDoc/

