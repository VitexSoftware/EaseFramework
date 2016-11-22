#!/bin/sh
#./reset-db.sh
sudo php5dismod xdebug
../vendor/bin/phinx  migrate -e testing
../vendor/bin/phpunit -c configuration.xml src
sudo php5enmod xdebug
