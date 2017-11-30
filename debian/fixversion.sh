#!/bin/bash
VERSION=`cat debian/composer.json | grep version | awk -F'"' '{print $4}'`
DEBVERSION=`cat debian/changelog | head -1 |  cut -d "(" -f2 | cut -d ")" -f1`
REVISION=`echo $DEBVERSION | awk -F- '{print $2}'`
echo ${VERSION}.${REVISION}
sed -i -e '/\"version\"/c\    \"version\": \"'${VERSION}'.'${REVISION}'",' debian/ease-framework/usr/share/php/Ease/composer.json

sed -i -e "/static public \$frameworkVersion/c\    static public \$frameworkVersion = '${VERSION}.${REVISION}';" debian/ease-framework/usr/share/php/Ease/Atom.php
sed -i -e "/static public \$frameworkVersion/c\    static public \$frameworkVersion = '${VERSION}.${REVISION}';" src/Ease/Atom.php
