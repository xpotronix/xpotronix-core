# 1. Download and unzip the latest php-java-bridge sources
# $ wget https://github.com/belgattitude/php-java-bridge/archive/7.1.3.zip -O pjb.zip
# $ unzip pjb.zip && cd php-java-bridge-7.1.3
# 2. Customize and build your own bridge:
#    Example below contains some pre-made gradle init scripts
#    to include jasperreports and mysql-connector libraries to the
#    build. They are optional, remove the (-I) parts or provide
#    your own.
./gradlew war \
         -I init-scripts/init.jasperreports.gradle \
         -I init-scripts/init.mysql.gradle \
         -I init-scripts/init.saxon.gradle \
         -I init-scripts/init.fop.gradle
# The build files are generated in the '/build/libs' folder.

