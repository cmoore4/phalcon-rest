git clone https://github.com/phalcon/cphalcon.git
cd cphalcon/build
./install
sed -i '$ a\extension=phalcon.so' /etc/php5/apache2/php.ini
service apache2 restart