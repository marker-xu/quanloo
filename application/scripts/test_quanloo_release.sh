#!/usr/bin/env sh

# replace code
cd ~/snda-php/
tar zcf videosearch_bak.tar.gz videosearch
unzip web.zip
rm -rf videosearch
mv web videosearch
# copy config
cp videosearch/application/config/test/* videosearch/application/config/
cp videosearch/application/views/conf/test/* videosearch/application/views/conf/
# combine js
cd ~/snda-php/videosearch/resource/js/combo/
python combo.py
python combo.py
python release.py
cd ../../
mv js/combo/_release _js
mv js jsbak
mv _js js
# combine css
cd ~/snda-php/videosearch/resource/css/
cp combo/release.py combo/yuicompressor.jar ./
python ./release.py 
cd ..
mv css/ cssbak/
mv _css/ css
# clear smarty cache
cd ~
rm -rf data/_smarty/tpl_c/*
# restart php-fpm
kill `cat ~/php5/var/run/php-fpm.pid`
sleep 3
php5/sbin/php-fpm