#!/bin/bash
cd ~
rpm -Uvh https://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm
rpm -Uvh https://mirror.webtatic.com/yum/el7/webtatic-release.rpm
yum -y install fedora-packager  php php-composer php-intl php-ctype php-curl php-date php-dom php-hash php-json php-mbstring php-pcre php-reflection php-spl
rpmdev-setuptree
wget https://github.com/VitexSoftware/EaseFramework/archive/master.tar.gz -O ~/rpmbuild/SOURCES/php-EaseFramework-1.4.2-8f2b2c8e9aa536cecf6e1efdbe4c6d6293362e81.tar.gz
cd /vagrant
make rpm
