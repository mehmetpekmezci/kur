cp -f /opt/kur/ipxe/undionly.kpxe  /var/lib/tftpboot/
perl -pi -e 's/disable\s*=\s*yes/disable=no/' /etc/xinetd.d/tftp 
systemctl enable tftp
systemctl start tftp
