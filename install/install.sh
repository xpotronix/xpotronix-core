#!/bin/bash


# requerimientos
# apache2
# php7.4-fpm o posterior
# mysql8 una base de datos que sea xpay_mpf

# freetds.conf para poder contectarse al MSSQL

# disposicion de achivos en el sistema

XPATH=/usr/share/xpotronix
EXT_VERSION="3.4.0"
PHP_VERSION="7.4"
UG_PERMS=www-data.www-data
UG_MODE=775
CONFIG_PATH=/etc/xpotronix
INIT_PATH=/etc/init.d
APP_DOMAIN=jusbaires.gob.ar

#http_proxy=http://username:password@host:port/
#export http_proxy

if [ `id -u $USERNAME` != 0 ]; then
   echo "$0 must be run as root"
   exit 1
fi

if [ ! -e install.sh ]; then
   echo "Im not in the install directory!!"
   exit 1
fi

echo "In which path did you defined your web server DOCUMENT_ROOT?"
echo "If you dont know, please press CTRL+C and check your apache2 installation"
echo "or press [ENTER] to use /var/www/sites"
read DOCUMENT_ROOT

if [ -z $DOCUMENT_ROOT ]; then
	DOCUMENT_ROOT="/var/www/sites"
fi

mkdir -p $XPATH

# directorios y permisos
mkdir -p $CONFIG_PATH
chown $UG_PERMS $CONFIG_PATH

mkdir -p $DOCUMENT_ROOT
chown $UG_PERMS  $DOCUMENT_ROOT

mkdir -p $DOCUMENT_ROOT/tmp
chown $UG_PERMS $DOCUMENT_ROOT/tmp

cp -Rp ../* $XPATH

# configuracion apache2

echo $DOCUMENT_ROOT
sed -s "s#DOCUMENT_ROOT#$DOCUMENT_ROOT#g" xpotronix.ini >$CONFIG_PATH/xpotronix.ini
sed -s "s#DOCUMENT_ROOT#$DOCUMENT_ROOT#g" $XPATH/install/apache2/xpotronix.conf | \
sed -s "s#XPATH#$XPATH#g" $XPATH/install/apache2/xpotronix.conf | \
sed -s "s#APP_DOMAIN#$APP_DOMAIN#g" $XPATH/install/apache2/xpotronix.conf > /etc/apache2/sites-enabled/xpotronix.conf

>/etc/apache2/sites-enabled/xpotronix.conf


# utilidades del bash para la transformacion

ln -sf $XPATH/util/xpotronix /usr/bin/xpotronix
ln -sf $XPATH/util/xpotronize /usr/bin/xpotronize
ln -sf $XPATH/util/xpdumpbase-mysql /usr/bin/xpdumpbase-mysql
ln -sf $XPATH/util/xputil /usr/bin/xputil


# javscript y maquina virtual 

# javascript

wget -c http://justamente.net/downloads/ext-3.4.0.zip -O $XPATH/lib
wget -c http://justamente.net/downloads/ext-4.2.2-gpl.zip -O $XPATH/lib


# wrapper java
wget -c http://justamente.net/downloads/JavaBridgeTemplate.war $XPATH/lib


# la entrada init.d para el servicio de php-java-bridge

sudo cp $XPATH/php-java-bridge/php-java-bridge $INIT_PATH/
systemctl enable --now php-java-bridge


