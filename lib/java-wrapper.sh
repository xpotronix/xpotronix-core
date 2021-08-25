#!/bin/bash


XPOTRONIX_JAR_PATH=/usr/share/xpotronix/lib

# java -XshowSettings:properties -version 2>&1 > /dev/null | grep 'java.home'

JAVA_HOME=/usr/lib/jvm/java-11-openjdk-amd64
WORKDIR=/var/www/sites/

JAVA_OPTIONS=" -Xms256m -Xmx512m -server "
APP_OPTIONS=" -c /path/to/app.config -d /path/to/datadir "

cd $WORKDIR

# que corra con www-data no como root, no se si esta bien eso de ahi
"${JAVA_HOME}/bin/java" $JAVA_OPTIONS -jar $XPOTRONIX_JAR_PATH/webapp-runner.jar --access-log $XPOTRONIX_JAR_PATH/JavaBridgeTemplate.war


