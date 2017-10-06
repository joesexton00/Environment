#!/usr/bin/env bash

# Install Dependendencies

# Ask for the administrator password upfront
sudo -v

# Install Composer
curl -ksS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# Composer Packages
composer global require phing/phing
composer global require phpunit/phpunit
composer global require phpmd/phpmd
composer global require squizlabs/php_codesniffer
composer global require sebastian/phpcpd
composer global require phploc/phploc

cd "$(dirname "${BASH_SOURCE}")/../bin/JmsCommand";
composer install

# Set symlink for Sublime Text
ln -s "/Applications/Sublime Text.app/Contents/SharedSupport/bin/subl" /usr/local/bin/subl

