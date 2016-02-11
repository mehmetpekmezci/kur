<?php
header("Content-Type:text/plain");

$host=$_SERVER['HTTP_HOST'];
$os=$_GET['os'];
$HOSTNAME="localhost";
if(isset($_GET['hostname'])){
  $HOSTNAME=$_GET['hostname'];
}
$rpmdir="";
if(isset($_GET['rpmdir'])){
  $rpmdir=$_GET['rpmdir'];
}
$mac="";
if(isset($_GET['mac'])){
  $mac=$_GET['mac'];
}

$rpmdirContent=scandir("/opt/kur/rpms/$rpmdir/");


echo "
logging --level=debug
install
#graphical
text
keyboard 'trq'
url --url=http://$host/kur/mnt/$os
zerombr
clearpart --all --initlabel
part /boot --asprimary --fstype=\"ext4\" --size=200
part swap --fstype=\"swap\" --recommended
part / --label=ROOT00 --fstype=\"ext4\" --grow --size=1
# GRUB Password is qwe123 , Change it!
#bootloader --location=mbr --md5pass=$1$goht70$4a51x5BVk25Z1x3PE8WL./ --append=\"vga=771 selinux=0\"
bootloader --location=mbr --append=\"nomodeset rdblocklist=nouveau selinux=0 net.ifnames=0 biosdevname=0\"
network --bootproto=dhcp --onboot=on --hostname=$HOSTNAME --nodns --noipv6 --activate --device=mac
timezone --isUtc Etc/UTC
eula --agreed
lang en_US.UTF-8
rootpw --plaintext qwe123
authconfig --enableshadow --passalgo=sha512
firewall --disabled
selinux --disabled
firstboot --disable
xconfig --defaultdesktop=GNOME --startxonboot
#services --enabled network,sshd,rc-local,spice-vdagentd
#services --disabled sendmail,NetworkManager-wait-online,NetworkManager
%packages
@^developer-workstation-environment
@base
@core
@debugging
@desktop-debugging
@development
@dial-up
@directory-client
@file-server
@fonts
@gnome-apps
@gnome-desktop
@guest-desktop-agents
@input-methods
@internet-applications
@internet-browser
@java-platform
@multimedia
@network-file-system-client
@performance
@perl-runtime
@print-client
@ruby-runtime
@virtualization-client
@virtualization-hypervisor
@virtualization-tools
@web-server
@x11
dhcp
httpd
php
php-common
php-cli
libzip
syslinux-tftpboot
tftp
tftp-server
-gnome-initial-setup


%end
%addon com_redhat_kdump --disable --reserve-mb='auto'

%end
%post
mkdir -p /opt/kur/rpms
cd /opt/kur/rpms
";

if($rpmdir!==""){
 foreach( $rpmdirContent as  $file){
  if($file != "." && $file!= ".." ){
    echo "wget http://$host/kur/rpms/$rpmdir/$file\n";
  }
 }
 echo "rpm -ivh *sysconfig*.rpm \n";
}

echo "

%end
";
?>
