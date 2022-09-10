#!/usr/bin/env bash

echo "Copying images from the server"
rsync -avz --chmod=D2775,F664 -e 'ssh' cbc.skeeterbait.com:/var/www/sites/cbclancaster/shared/images /var/www/projects/cbclancaster/joomla/

echo "Copying files from the server"
rsync -avz --chmod=D2775,F664 -e 'ssh' cbc.skeeterbait.com:/var/www/sites/cbclancaster/shared/files /var/www/projects/cbclancaster/joomla/

echo "Finished"
