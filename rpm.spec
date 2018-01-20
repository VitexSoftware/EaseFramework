# Copyright (c) 2018 Vítězslav Dvořák <info@vitexsoftware.cz>
#
# Please preserve changelog entries
#

%global github_owner     VitexSoftware
%global github_name      EaseFramework
%global github_version   1.4.2
%global github_commit    8f2b2c8e9aa536cecf6e1efdbe4c6d6293362e81

%global composer_vendor  vitexsoftware
%global composer_project ease-framework

# "php": "^5.6|^7.0"
%global php_min_ver 5.6

# Build using "--without tests" to disable tests
%global with_tests  %{?_without_tests:0}%{!?_without_tests:1}

%{!?phpdir:  %global phpdir  %{_datadir}/php}

Name:          php-%{github_name}
Version:       %{github_version}
Release:       3%{?dist}
Summary:       An PHP Framework for Ease of writing Applications

Group:         Development/Libraries
License:       GPL-2.0+
URL:           https://github.com/%{github_owner}/%{github_name}
Source0:       %{url}/archive/%{github_commit}/%{name}-%{github_version}-%{github_commit}.tar.gz

# remirepo:3
# For test build on all available arch
#global debug_package %{nil}
#global __debug_install_post /bin/true
BuildArch:     noarch
# Tests
%if %{with_tests}
## composer.json
BuildRequires: php(language) >= %{php_min_ver}
BuildRequires: php-composer(phpunit/phpunit)
BuildRequires: php-intl
## phpcompatinfo (computed from version 1.6.0)
BuildRequires: php-ctype
BuildRequires: php-curl
BuildRequires: php-date
BuildRequires: php-dom
BuildRequires: php-hash
BuildRequires: php-json
BuildRequires: php-mbstring
BuildRequires: php-pcre
BuildRequires: php-reflection
BuildRequires: php-spl
## Autoloader
BuildRequires: php-fedora-autoloader-devel
%endif

# composer.json
Requires:      php(language) >= %{php_min_ver}
# phpcompatinfo (computed from version 1.6.0)
Requires:      php-curl
Requires:      php-date
Requires:      php-dom
Requires:      php-hash
Requires:      php-json
Requires:      php-mbstring
Requires:      php-pcre
Requires:      php-reflection
Requires:      php-spl
# Autoloader
Requires:      php-composer(fedora/autoloader)

# php-{COMPOSER_VENDOR}-{COMPOSER_PROJECT}
Provides:      php-%{composer_vendor}-%{composer_project}           = %{version}-%{release}
# Composer
Provides:      php-composer(%{composer_vendor}/%{composer_project}) = %{version}

%description
Object oriented PHP Framework for easy&fast writing small/middle sized apps.


%prep
%setup -qn %{github_name}-%{github_commit}
%patch0 -p1
%patch1 -p1

%if 0%{?el6}
# For old PHPUnit
for test in $(find test -name \*Test.php); do
  sed -e '/assertNotFalse/s/);/, false);/;s/assertNotFalse/assertNotSame/' -i $test
done
%endif

: Create autoloader
cat <<'AUTOLOAD' | tee src/Ease/autoload.php
<?php
/**
 * Autoloader for %{name} and its' dependencies
 * (created by %{name}-%{version}-%{release}).
 *
 * @return \Symfony\Component\ClassLoader\ClassLoader
 */

require_once '%{phpdir}/Fedora/Autoloader/autoload.php';
\Fedora\Autoloader\Autoload::addPsr4('Ease\\', __DIR__);

AUTOLOAD


%build
# Empty build section, nothing to build


%install
mkdir -p %{buildroot}%{phpdir}
cp -rp src/%{github_name} %{buildroot}%{phpdir}/


%check
%if %{with_tests}
mkdir vendor
cat << 'EOF' | tee vendor/autoload.php
<?php
require_once '%{buildroot}%{phpdir}/Ease/autoload.php';
\Fedora\Autoloader\Autoload::addPsr4('Test\\Ease\\', dirname(__DIR__).'/test/Ease');
EOF

ret=0
for cmd in php php56 php70 php71 php72; do
  if which $cmd; then
    $cmd  %{_bindir}/phpunit --verbose || ret=1
  fi
done
exit $ret
%else
: Tests skipped
%endif


%files
%{!?_licensedir:%global license %%doc}
%license LICENSE
%doc *.md
%doc composer.json
%{phpdir}/%{github_name}


%changelog
