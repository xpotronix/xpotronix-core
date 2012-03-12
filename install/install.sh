#!/bin/bash

XPATH=/usr/share/xpotronix
EXT_VERSION="3.4.0"
UG_PERMS=www-data.www-data

#http_proxy=http://username:password@host:port/
#export http_proxy

if [ `id -u $USERNAME` != 0 ]; then
   echo "$0 must be run as root"
   exit 1
fi

test ! -x /usr/bin/wget && apt-get install wget
test ! -x /usr/bin/unzip && apt-get install unzip

if [ ! -e install.sh ]; then
   echo "Im not in the install directory!!"
   exit 1
fi

echo "In which path did you defined your web server DOCUMENT_ROOT?"
echo "If you dont know, please press CTRL+C and check your apache2 installation"
echo "or press [ENTER] to use /var/www"
read DOCUMENT_ROOT

if [ -z $DOCUMENT_ROOT ]; then
	DOCUMENT_ROOT="/var/www"
fi

# these are mandatory
apt-get install php5 php5-cli php5-mysql php5-xsl php5-curl php-pear
apt-get install libphp-adodb php-cache-lite
apt-get install libsaxonb-java

# these are optional
apt-get install php5-gd php5-imagick
#apt-get install php5-ldap
apt-get install apg

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

rm ext-$EXT_VERSION.zip*
wget http://extjs.cachefly.net/ext-$EXT_VERSION.zip
rm -rf $XPATH/lib/ext-$EXT_VERSION
unzip ext-$EXT_VERSION.zip -d $XPATH/lib 
ln -sf $XPATH/lib/ext-$EXT_VERSION $XPATH/lib/ext
