<?PHP
		$cfgProgDir = $_SERVER["DOCUMENT_ROOT"]. "/phpSecurePages/"; 
		$cfgUserdir = $_SERVER["DOCUMENT_ROOT"]. "/"; 
		include($INC_DIR. "common.php"); 
		
        include($cfgProgDir . "secure.php");
?>

<?php
	echo "<BR>".$_SERVER["DOCUMENT_ROOT"]."<BR>\n";
	echo "<BR> <BR>  KODE RAHASIA <br><br>\n";
	echo "<BR> KODE1 : ".$login."<br>\n";
	echo "KODE2  : ". $_SERVER['REMOTE_ADDR']."<br>\n";
	$src3=$login.$_SERVER['REMOTE_ADDR'];
	$md51=md5($src3);
	$kode3=substr($md51,1,4);
	echo "KODE3  : ". $kode3."<br>\n";
	
	
	
?>

<a href="/logout.php"> LOG OUT </a>
