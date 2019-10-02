<?php

/*
 * This file is part of the CRUD Admin Generator project.
 *
 * Author: Jon Segador <jonseg@gmail.com>
 * Web: http://crud-admin-generator.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
// phpinfo(); die;
//var_dump($_GET) ;die;
//	var_dump($_SERVER) ;die;

$GLOBALS['$www_root_uri'] = "" ; /** set with  url matching to virtual hosts config in apache ***/
$cfgProgDir = $_SERVER["DOCUMENT_ROOT"]. $GLOBALS['$www_root_uri'] ."/vendor_01/phpSecurePages/"; 
$cfgUserdir = $_SERVER["DOCUMENT_ROOT"]. $GLOBALS['$www_root_uri']."/vendor_01/"; 
$minUserLevel = 100; 	
$requiredUserLevel = array(1,3,100);

//@TODO fix this .. after login ???
//@todo when called user/role appeared $app['credential'] filled

//echo $login; die; 
//var_dump($_SESSION); die;
//error_log(isset($_SESSION));
if(!isset($_SESSION)) session_start();
if(isset($_GET['do_login'])  | isset($_SESSION['login']) |strlen($_SERVER['REQUEST_URI'])>1 ) 
{
	//var_dump($_SESSION);
	//var_dump($_SESSION['login']); 	die;

	include($cfgProgDir . "secure.php");
	require_once __DIR__.'/vendor_01/auth.php';
}

require_once __DIR__.'/controllers/base.php';

//@TODO .... check is user set by phpSPSecurepages ...
		
		


?>

