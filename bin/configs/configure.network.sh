INTERFACES=`ifconfig -a| grep "mtu\|Link" | grep -v ':1' |grep -v inet6| awk '{print $1}'| cut -d: -f1| grep -v lo`

netdigit=0

for interface in `echo $INTERFACES`
do
  ifconfig $interface:1 1.0.$netdigit.1 netmask 255.255.255.0
  netdigit=$(($netdigit+1));
done
