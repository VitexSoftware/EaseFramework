#!/bin/sh
#./reset-db.sh
phpunit --config ./configuration.xml \
--bootstrap ./bootstrap.php \
--whitelist src/Ease \
/usr/share/netbeans/php/phpunit/NetBeansSuite.php -- \
--run=./


exit

--colors --log-junit /tmp/nb-phpunit-log.xml \
--config ./configuration.xml \
