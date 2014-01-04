#!/bin/bash
rm -rfv /var/tmp/EaseFramework
cd /var/tmp
git clone git@github.com:Vitexus/EaseFramework.git
cd EaseFramework

ls -la 

VERSION=`cat version | perl -ne 'chomp; print join(".", splice(@{[split/\./,$_]}, 0, -1), map {++$_} pop @{[split/\./,$_]}), "\n";'`


CHANGES=`git log -n 1 | tail -n+5`
dch -b -v $VERSION --package ease-framework 

echo $VERSION > ~/Projects/VitexSoftware/Ease/version

debuild -i -us -uc -b

cd ..
ls *.deb

~/bin/publish-deb-packages.sh
