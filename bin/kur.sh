if [ -z $KUR_DIR ]
then
 KUR_DIR=/opt/kur
fi

if [ ! -d $KUR_DIR ]
then
 echo "Can Not Find KUR directory $KUR_DIR"
 exit 1
fi

export KUR_DIR

$KUR_DIR/bin/configs/configure.firewalld.sh
$KUR_DIR/bin/configs/configure.network.sh
$KUR_DIR/bin/configs/configure.dhcpd.sh
$KUR_DIR/bin/configs/configure.httpd.sh
$KUR_DIR/bin/configs/configure.mounts.sh
$KUR_DIR/bin/configs/configure.tftp.sh


