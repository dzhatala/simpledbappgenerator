<?php
/**************************************************************/
/*              phpSecurePages version 0.43 beta               */
/*              Copyright 2015 Circlex.com, Inc.              */
/*                                                            */
/*          ALWAYS CHECK FOR THE LATEST RELEASE AT            */
/*              http://www.phpSecurePages.com                 */
/*                                                            */
/*              Free for non-commercial use only.             */
/*               If you are using commercially,               */
/*         or using to secure your clients' web sites,        */
/*   please purchase a license at http://phpsecurepages.com   */
/*                                                            */
/**************************************************************/
/*      There are no user-configurable items on this page     */
/**************************************************************/

// check login with Database

// Check if secure.php has been loaded correctly
if ( !defined("LOADED_PROPERLY") || isset($_GET['cfgProgDir']) || isset($_POST['cfgProgDir'])) {
        echo "Parsing of phpSecurePages has been halted!";
        exit();
        }

// contact database
if ( empty($cfgServerPort) ) {
        mysql_connect($cfgServerHost, $cfgServerUser, $cfgServerPassword) or die($strNoConnection);
        }
else {
        mysql_connect($cfgServerHost . ":" . $cfgServerPort, $cfgServerUser, $cfgServerPassword) or die($strNoConnection);
        }

mysql_select_db($cfgDbDatabase) or die(mysql_error());

if (phpversion() >= 4.3) {
        $login=mysql_real_escape_string($login);
        }
else {
        $login=mysql_escape_string($login);
        }

$userQuery = mysql_query("SELECT * FROM $cfgDbTableUsers WHERE $cfgDbLoginfield = '$login'") or die($strNoDatabase);

// check user and password
if (mysql_num_rows($userQuery) != 0) {
        // user exist --> continue
        $userArray = mysql_fetch_array($userQuery);
        
        if ($login != $userArray[$cfgDbLoginfield]) {
                // Case sensative user not present in database
                $phpSP_message = $strUserNotExist;
                // include($cfgProgDir . "objects/logout.php");
                include($cfgProgDir . "interface.php");
                exit;
                }
        }
		/*if(!isset($_SESSION['login'])){
			error_log("checklogin db register session ".$login);
			$_SESSION['login']='$login';
		}*/
else {
        // user not present in database
        $phpSP_message = $strUserNotExist;
        include($cfgProgDir . "interface.php");
        exit;
        }

if (!$userArray[$cfgDbPasswordfield]) {
        // password not present in database for this user
        $phpSP_message = $strPwNotFound;
        include($cfgProgDir . "interface.php");
        exit;
        }

if (stripslashes($userArray["$cfgDbPasswordfield"]) != $password) {
        // password is wrong
        $phpSP_message = $strPwFalse;
        include($cfgProgDir . "interface.php");
        exit;
        }

if ( isset($userArray["$cfgDbUserLevelfield"]) && !empty($cfgDbUserLevelfield) ) {
        $userLevel = stripslashes($userArray["$cfgDbUserLevelfield"]);
		/*if(is_numeric($userLevel)) {
			$userLevel=intval($userLevel);
			var_dump($userLevel);			
			//die;
		}*/
}

if ( ( isset($requiredUserLevel) && !empty($requiredUserLevel[0]) ) || isset($minUserLevel) ) {
        // check for required user level and minimum user level
        if ( !isset($userArray["$cfgDbUserLevelfield"]) ) {
                // check if column (as entered in the configuration file) exist in database
                $phpSP_message = $strNoUserLevelColumn;
                include($cfgProgDir . "interface.php");
                exit;
                }
        if ( empty($cfgDbUserLevelfield) || ( !is_in_array($userLevel, @$requiredUserLevel) 
		         && ( !isset($minUserLevel) || empty($minUserLevel) || $userLevel < $minUserLevel ) ) ) {
                // this user does not have the required user level
                $phpSP_message = $strUserNotAllowed;
				if(1==0){
				//var_dump($userArray); 
					echo "<br> userlevel : " ;  var_dump ($userLevel);
					echo "<br> minuserlevel : " ;  var_dump ($minUserLevel);
					echo "<br> userArray : " ;  var_dump ($userArray);
					echo "<br> compar. : " ;  var_dump ($userLevel<$minUserLevel);
					//die;
					
				}
                include($cfgProgDir . "interface.php");
                exit;
                }
        }
if ( isset($userArray["$cfgDbUserIDfield"]) && !empty($cfgDbUserIDfield) ) {
        $ID = stripslashes($userArray["$cfgDbUserIDfield"]);
        }
		error_log("check_login_db ok\n");
		error_log("sessesion ... ".$_SESSION['login']);
?>
