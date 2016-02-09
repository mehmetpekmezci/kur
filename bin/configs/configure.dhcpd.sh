## 
## Mount operating systems
##
DHCPDCONF_DIR=/etc/dhcp

if [ ! -f $DHCPDCONF_DIR/dhcpd.conf.kur.back ]
then
 cp $DHCPDCONF_DIR/dhcpd.conf  $DHCPDCONF_DIR/dhcpd.conf.kur.back
fi

echo '
#
# DHCP Server Configuration file.
#   see /usr/share/doc/dhcp*/dhcpd.conf.sample
#   see "man 5 dhcpd.conf"
#

ddns-update-style interim;
ddns-domainname domain001;
ddns-hostname dhcpclient;
ignore client-updates;
authoritative;
ping-check true;
do-forward-updates false;


# Feature indicators
option space ipxe;
option ipxe-encap-opts code 175 = encapsulate ipxe;
option ipxe.priority code 1 = signed integer 8;
option ipxe.keep-san code 8 = unsigned integer 8;
option ipxe.skip-san-boot code 9 = unsigned integer 8;
option ipxe.syslogs code 85 = string;
option ipxe.cert code 91 = string;
option ipxe.privkey code 92 = string;
option ipxe.crosscert code 93 = string;
option ipxe.no-pxedhcp code 176 = unsigned integer 8;
option ipxe.bus-id code 177 = string;
option ipxe.bios-drive code 189 = unsigned integer 8;
option ipxe.username code 190 = string;
option ipxe.password code 191 = string;
option ipxe.reverse-username code 192 = string;
option ipxe.reverse-password code 193 = string;
option ipxe.version code 235 = string;
option iscsi-initiator-iqn code 203 = string;
option ipxe.http code 19 = unsigned integer 8;
option ipxe.https code 20 = unsigned integer 8;
option ipxe.menu code 39 = unsigned integer 8;
option ipxe.pxeext code 16 = unsigned integer 8;
option ipxe.iscsi code 17 = unsigned integer 8;
option ipxe.aoe code 18 = unsigned integer 8;
option ipxe.tftp code 21 = unsigned integer 8;
option ipxe.ftp code 22 = unsigned integer 8;
option ipxe.dns code 23 = unsigned integer 8;
option ipxe.bzimage code 24 = unsigned integer 8;
option ipxe.multiboot code 25 = unsigned integer 8;
option ipxe.slam code 26 = unsigned integer 8;
option ipxe.srp code 27 = unsigned integer 8;
option ipxe.nbi code 32 = unsigned integer 8;
option ipxe.pxe code 33 = unsigned integer 8;
option ipxe.elf code 34 = unsigned integer 8;
option ipxe.comboot code 35 = unsigned integer 8;
option ipxe.efi code 36 = unsigned integer 8;
option ipxe.fcoe code 37 = unsigned integer 8;
option ipxe.vlan code 38 = unsigned integer 8;
option ipxe.sdi code 40 = unsigned integer 8;
option ipxe.nfs code 41 = unsigned integer 8;
' > $DHCPDCONF_DIR/dhcpd.conf

IPLIST=`ifconfig -a| grep 'inet '| awk '{print \$2}' | grep '1\.0\.' | sed -e 's/addr://'| sort | uniq | grep -v 127.0.`
#IPLIST=`ifconfig -a| grep 'inet '| awk '{print \$2}' |  sed -e 's/addr://'| sort | uniq | grep -v 127.0.`

for ip in $(echo $IPLIST)
do
 netbase=$(echo $ip | cut -d. -f1,2,3)
 netaddr=$netbase.0
 echo "
  subnet $netaddr netmask 255.255.255.0 {
    option subnet-mask          255.255.255.0;
    option nis-domain           \"domain001\";
    option domain-name          \"domain001\";
    option domain-name-servers  $ip;
    option routers              $ip;
    next-server  		$ip;
    default-lease-time  	300;
    max-lease-time 		300;
    if exists user-class and option user-class = \"iPXE\" {
        filename \"http://$ip/kur/ipxe/ipxe.php?mac=\${net0/mac}\"; }
    else { filename \"undionly.kpxe\"; }
    pool { range $netbase.1 $netbase.254; }
  }
 " >>  $DHCPDCONF_DIR/dhcpd.conf
done



systemctl enable dhcpd.service
systemctl stop dhcpd.service
systemctl start dhcpd.service


