EaseFramework
=============

Object oriented PHP Framework for easy&fast writing small/middle sized apps.

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

$oPage = new EaseTWBWebPage('Page title');
$form = $oPage->addItem( new EaseTWBForm('ExampleForm') );
$form->addItem(new EaseHtmlInputText('Name'));
$form->addItem(new EaseTWSubmitButton('OK', 'success') );
$oPage->draw();

Links
=====

Homepage: https://www.vitexsoftware.cz/ease.php

GitHub: https://github.com/Vitexus/EaseFramework

Apigen Docs: https://www.vitexsoftware.cz/EaseDoc/

