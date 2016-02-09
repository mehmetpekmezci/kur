## 
## Mount operating systems
##
WWW_DIR=/var/www/html

cd $WWW_DIR

ln -s $KUR_DIR

systemctl enable httpd
systemctl start httpd

