<?php

$host=$_SERVER['HTTP_HOST'];
$mac="";
if(isset($_GET['mac']))$mac=$_GET['mac'];
$selectedCategory="";
if(isset($_GET['category']))$selectedCategory=$_GET['category'];
$selectedType="";
if(isset($_GET['type']))$selectedType=$_GET['type'];

$MOUNTSDIR=dirname(__FILE__).'/../mnt';
$DISTRODIRS=scandir($MOUNTSDIR);


$generic_items="";
$generic_installations="";
$NODEFILE=dirname(__FILE__).'/../etc/nodes.cfg';
if(is_readable($NODEFILE)){
 $i=1;
 $handle = fopen($NODEFILE, "r");
 if ($handle) {
     while(($line = fgets($handle)) !== false) {
      if(strpos($line, '#') === false){ 
        $sections=explode(":",$line);
        $category=$sections[0];
        $iso=$sections[1];
        $isodir=str_replace(".iso","",$iso);
        $hosts=explode(",",$sections[2]);
        $hosttypes=array();
        foreach($hosts as $hostname){
          $hostname=str_replace("\n","",$hostname);
          $hosttype=preg_replace("/[^A-Za-z].*/", '', $hostname);
          if(!in_array($hosttype,$hosttypes))array_push($hosttypes,$hosttype);
        }
        if($selectedCategory===""){
            $generic_items=$generic_items."item --key $i $category [$i] $category \n";
            $generic_installations=$generic_installations."\n:$category \n".
                          "chain http://$host/kur/ipxe/ipxe.php?category=$category&mac=$mac\n";
            $i=$i+1;
        }else if($selectedType===""){
         if($category!==$selectedCategory)continue;
         $generic_items="item --key $i .. [$i] .. \n";
         $generic_installations=$generic_installations."\n:.. \n".
                       "chain http://$host/kur/ipxe/ipxe.php\n";
         $i=$i+1;
         foreach($hosttypes as $type){
           $generic_items=$generic_items."item --key $i $type [$i]  $type \n";
           $generic_installations=$generic_installations."\n:$type \n".
                       "chain http://$host/kur/ipxe/ipxe.php?category=$category&type=$type&mac=$mac\n";
           $i=$i+1;
         }
        }else{ 
         if($category!==$selectedCategory)continue;
         foreach($hosttypes as $type){
          if($type!==$selectedType)continue;
          $generic_items="item --key $i .. [$i] .. \n";
          $generic_installations=$generic_installations."\n:.. \n".
                       "chain http://$host/kur/ipxe/ipxe.php?category=$category&mac=$mac\n";
          $i=$i+1;
          foreach($hosts as $hostname){
            $hostname=str_replace("\n","",$hostname);
            $hosttype=preg_replace("/[^A-Za-z].*/", '', $hostname);
            if($hosttype!==$type)continue;
            $generic_items=$generic_items."item --key $i $hostname [$i]  $hostname \n";
            $generic_installations=$generic_installations."\n:$hostname \n".
                          "echo Installing $isodir ...\n".
                          "kernel -n vmlinuz http://$host/kur/mnt/$isodir/images/pxeboot/vmlinuz \n".
                          "initrd  http://$host/kur/mnt/$isodir/images/pxeboot/initrd.img \n".
                          "imgargs vmlinuz ipv6.disabled=1 ip=dhcp ksdevice=link  ks=http://$host/kur/ks/ks.cfg.php?os=$isodir&hostname=$hostname&rpmdir=$category&mac=$mac text nomodeset edd=off selinux=0  \n".
                          "boot vmlinuz \n".
                          "exit \n";
            $i=$i+1;
          }
         }
        }
      }
     }
     fclose($handle);
 }
}else{
$i=1;
foreach( $DISTRODIRS as  $distrodir){
 if($distrodir != "." && $distrodir!= ".." ){
   $generic_items=$generic_items."item --key $i $distrodir [$i] Install $host : $distrodir\n";
   $generic_installations=$generic_installations."\n:$distrodir \n".
                          "echo Installing $distrodir ...\n".
                          "kernel -n vmlinuz http://$host/kur/mnt/$distrodir/images/pxeboot/vmlinuz \n".
                          "initrd  http://$host/kur/mnt/$distrodir/images/pxeboot/initrd.img \n".
                          "imgargs vmlinuz ipv6.disabled=1 ip=dhcp ksdevice=link  ks=http://$host/kur/ks/ks.cfg.php?os=$distrodir&mac=$mac text nomodeset edd=off selinux=0  \n".
                          "boot vmlinuz \n".
                          "exit \n";
   $i=$i+1;
 } 
}
}


$NODESFILE=dirname(__FILE__).'/../etc/nodes';

if(is_readable($NODEFILE)){

$action="Select Category";

if($selectedCategory!==""){
 $action="Select Type";
}else if($selectedType!==""){
 $action="Select Hostname";
}

$menu = "#!ipxe
	    menu $action
	    item --gap System ID: \${uuid} [\${asset}]
	    item --gap
	    item --gap	Full automated OS installations
            $generic_items	
	    choose --timeout 180000 --default bios target
	    goto \${target}
	
            $generic_installations
	    ";

}else{
$menu = "#!ipxe
	    menu Choose your OS option for generic installation
	    item --gap System ID: \${uuid} [\${asset}]
	    item --gap
	    item --gap	Generic full automated OS installations
            $generic_items	
	    choose --timeout 180000 --default bios target
	    goto \${target}
	
            $generic_installations
	    ";
}


header("Content-Type:text/plain");
print $menu;

?>
