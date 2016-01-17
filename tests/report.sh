#!/bin/bash

phpunit --colors --log-junit /tmp/nb-phpunit-log.xml --bootstrap bootstrap.php --coverage-clover /tmp/nb-phpunit-coverage.xml /usr/local/netbeans-8.1beta/php/phpunit/NetBeansSuite.php 
