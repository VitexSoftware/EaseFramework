#!/bin/bash
VERSION=`cat debian/composer.json | grep version | awk -F'"' '{print $4}'`
REVISION=`cat debian/revision | perl -ne 'chomp; print join(".", splice(@{[split/\./,$_]}, 0, -1), map {++$_} pop @{[split/\./,$_]}), "\n";'`
echo ${VERSION}.${REVISION}
sed -i -e '/\"version\"/c\    \"version\": \"'${VERSION}'.'${REVISION}'",' debian/ease-framework/usr/share/php/Ease/composer.json

sed -i -e "/static public \$frameworkVersion/c\    static public \$frameworkVersion = '${VERSION}.${REVISION}';" debian/ease-framework/usr/share/php/Ease/Atom.php
sed -i -e "/static public \$frameworkVersion/c\    static public \$frameworkVersion = '${VERSION}.${REVISION}';" src/Ease/Atom.php
