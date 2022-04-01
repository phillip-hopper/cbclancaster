# CBC Lancaster Website


## Prerequisites
1. PHP 8.1
2. Node >=16.0
3. PHP-LDAP

## Install or Update Node
```bash
npm cache clean -f
sudo npm install -g n
sudo n stable
```


## Install PHP-LDAP
```bash
sudo apt-get install php8.1-ldap
sudo apt-get install php8.1-intl
```


## Need to be writable

`joomla/cache`
`joomla/administrator/cache`
`joomla/templates/rt_orion/custom`
`joomla/tmp`

`sudo chmod -R 2775 joomla`


## Link resource files
```bash
ln -s `pwd`/config/configuration.php `pwd`/joomla/configuration.php
ln -s `pwd`/resources/favicon.ico `pwd`/joomla/templates/rt_orion/favicon.ico
```
