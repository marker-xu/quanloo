#!/usr/bin/env sh

# decompression
cd ~/snda-php/
tar zcf videosearch_bak.tar.gz videosearch
rm -rf videosearch
tar zxf videosearch.tar.gz
# copy config
cp videosearch/application/config/production/* videosearch/application/config/
cp videosearch/application/views/conf/production/* videosearch/application/views/conf/
# clear smarty cache
cd ~
rm -rf data/_smarty/tpl_c/*
# restart php-fpm
kill `cat ~/php5/var/run/php-fpm.pid`
sleep 3
php5/sbin/php-fpm