#!/bin/bash
cd /var/www/skillsregistry
sudo mysqldump skillsregistry > skillsregistry.sql
composer update drupal/core-recommended --with-all-dependencies
cp web/.htaccess.saved web/.htaccess
cp web/.htaccess web/core/assets/scaffold/files/htaccess
