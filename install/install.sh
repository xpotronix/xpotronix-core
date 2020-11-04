#!/bin/bash

XPATH=/usr/share/xpotronix
EXT_VERSION="3.4.0"
PHP_VERSION="7.2"
UG_PERMS=www-data.www-data

#http_proxy=http://username:password@host:port/
#export http_proxy

if [ `id -u $USERNAME` != 0 ]; then
   echo "$0 must be run as root"
   exit 1
fi

test ! -x /usr/bin/wget && apt-get install wget
test ! -x /bin/gzip && apt-get install gzip

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

# these are mandatory
apt-get install php$PHP_VERSION php$PHP_VERSION-cli php$PHP_VERSION-mysql php$PHP_VERSION-xsl php$PHP_VERSION-curl php$PHP_VERSION-ldap
apt-get install libsaxonb-java

# these are optional
apt-get install php$PHP_VERSION-gd php$PHP_VERSION-imagick
apt-get install apg

# fop
apt-get install fop

mkdir -p $XPATH

mkdir -p /etc/xpotronix
chown $UG_PERMS /etc/xpotronix

mkdir -p $DOCUMENT_ROOT
chown $UG_PERMS  $DOCUMENT_ROOT

mkdir -p $DOCUMENT_ROOT/tmp
chown $UG_PERMS $DOCUMENT_ROOT/tmp

cp -Rp ../* $XPATH

echo $DOCUMENT_ROOT
sed -s "s#DOCUMENT_ROOT#$DOCUMENT_ROOT#g" xpotronix.ini >/etc/xpotronix/xpotronix.ini
sed -s "s#DOCUMENT_ROOT#$DOCUMENT_ROOT#g" xpotronix.conf >/etc/apache2/sites-enabled/xpotronix.conf

cp php-java-bridge /etc/init.d
update-rc.d php-java-bridge defaults
service php-java-bridge start

service apache2 force-reload

ln -sf $XPATH/util/xpotronix /usr/bin/xpotronix
ln -sf $XPATH/util/xpotronize /usr/bin/xpotronize
ln -sf $XPATH/util/xpdumpbase-mysql /usr/bin/xpdumpbase-mysql
ln -sf $XPATH/util/xputil /usr/bin/xputil

rm ext-$EXT_VERSION.tar.gz*
wget https://github.com/bjornharrtell/extjs/archive/$EXT_VERSION.tar.gz
rm -rf $XPATH/lib/$EXT_VERSION
tar xzfp $EXT_VERSION.tar.gz -C $XPATH/lib 
ln -sf $XPATH/lib/ext-$EXT_VERSION $XPATH/lib/ext
