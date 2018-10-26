#!/bin/bash
VERSTR=`dpkg-parsechangelog --show-field Version`
sed -i -e '/\"version\"/c\    \"version\": \"'${VERSTR}'",' debian/ease-framework/usr/share/php/Ease/composer.json
sed -i -e "/static public \$frameworkVersion/c\    static public \$frameworkVersion = '${VERSTR}';" debian/ease-framework/usr/share/php/Ease/Atom.php
sed -i -e "/static public \$frameworkVersion/c\    static public \$frameworkVersion = '${VERSTR}';" src/Ease/Atom.php
