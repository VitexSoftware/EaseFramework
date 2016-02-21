#!/bin/sh
#./reset-db.sh

../vendor/bin/phinx  migrate -e testing

../vendor/bin/phpunit -c configuration.xml src

