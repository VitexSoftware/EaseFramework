EaseFramework
=============

Object oriented PHP Framework for easy&fast writing small/middle sized apps.

[![Source Code](http://img.shields.io/badge/source-Vitexus/EaseFramework-blue.svg?style=flat-square)](https://github.com/Vitexus/EaseFramework)
[![Latest Version](https://img.shields.io/github/release/Vitexus/EaseFramework.svg?style=flat-square)](https://github.com/Vitexus/EaseFramework/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://github.com/Vitexus/EaseFramework/blob/master/LICENSE)
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
$oPage = new EaseTWBWebPage('Page title');
$form = $oPage->addItem( new EaseTWBForm('ExampleForm') );
$form->addItem(new EaseHtmlInputText('Name'));
$form->addItem(new EaseTWSubmitButton('OK', 'success') );
$oPage->draw();
```

Testing
-------

PostgreSQL:
```sql
CREATE USER easetest WITH PASSWORD 'easetest';
CREATE DATABASE easetest OWNER easetest;
```

Links
=====

Homepage: https://www.vitexsoftware.cz/ease.php

GitHub: https://github.com/Vitexus/EaseFramework

Apigen Docs: https://www.vitexsoftware.cz/EaseDoc/

