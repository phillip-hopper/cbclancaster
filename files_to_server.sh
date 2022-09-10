#!/usr/bin/env bash

echo "Copying images to the server"
rsync -avz --chmod=D2775,F664 -e 'ssh' /var/www/projects/cbclancaster/joomla/images cbc.skeeterbait.com:/var/www/sites/cbclancaster/shared/

echo "Copying files to the server"
rsync -avz --chmod=D2775,F664 -e 'ssh' /var/www/projects/cbclancaster/joomla/files cbc.skeeterbait.com:/var/www/sites/cbclancaster/shared/

echo "Finished"
