#!/bin/bash
VERSTR=`dpkg-parsechangelog --show-field Version`
COMPOSER_VERSTR=`echo ${VERSTR}|sed 's/-/./g'`
echo update debian/ease-framework/usr/share/php/Ease/composer.json version to ${COMPOSER_VERSTR}
sed -i -e '/\"version\"/c\    \"version\": \"'${COMPOSER_VERSTR}'",' debian/ease-framework/usr/share/php/Ease/composer.json
echo Update debian/ease-framework/usr/share/php/Ease/Atom.php version to ${VERSTR}
sed -i -e "/static public \$frameworkVersion/c\    static public \$frameworkVersion = '${VERSTR}';" debian/ease-framework/usr/share/php/Ease/Atom.php
echo Update src/Ease/Atom.php version to ${VERSTR}
sed -i -e "/static public \$frameworkVersion/c\    static public \$frameworkVersion = '${VERSTR}';" src/Ease/Atom.php
