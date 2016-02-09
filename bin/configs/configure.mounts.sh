## 
## Mount operating systems
##

for i in $KUR_DIR/iso/*.iso
do
 distrodir=$(basename $i | sed -e 's/.iso//') 
 mntdir=$KUR_DIR/mnt/$distrodir
 umount -f $KUR_DIR/mnt/$distrodir
 mkdir -p $KUR_DIR/mnt/$distrodir
 mount -o loop $i  $KUR_DIR/mnt/$distrodir
done


