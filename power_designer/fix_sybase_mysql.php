<?php
//fixing auto increment from sybase generated 

$mysql_hostname="localhost";
$mysql_user = "root";
$mysql_password = "";
$mysql_database = "registration";

//ALTER TABLE `cr_permission` CHANGE `CR_PERMISSION_ID` `CR_PERMISSION_ID` INT(11) NOT NULL AUTO_INCREMENT;

$bd = mysql_connect($mysql_hostname, $mysql_user, $mysql_password) or die("Could not connect database");
mysql_select_db($mysql_database, $bd) or die("Could not select database");


$query = "SET FOREIGN_KEY_CHECKS=0";
	mysql_query($query) or die (mysql_error());
$query = "SHOW TABLES ";
$results=mysql_query($query);
$tablerow = mysql_fetch_row($results);
while($tablerow!==FALSE){

	echo $tablerow[0]."\n";
	//var_dump($tablename);
	$tablename=$tablerow[0];
	$fixsql="ALTER TABLE `".$tablename."` CHANGE `".$tablename."_ID` `".$tablename."_ID` INT(11) NOT NULL AUTO_INCREMENT";
	echo $fixsql."\n";
	mysql_query($fixsql) or die (mysql_error());
	
	$tablerow = mysql_fetch_row($results);
	
}
$query = "SET FOREIGN_KEY_CHECKS=1";
mysql_query($query) or die (mysql_error());
	
$query=" DELETE FROM user_login" ;
echo $query ; 
mysql_query($query) or die (mysql_error());

$query ="INSERT INTO `user_login` (`user_login_ID`, `LOGIN`, " ;
$query.="	`PLAIN_PASSWORD`, `HASHED_PASSWORD`, `USER_LEVEL`) " ;
$query.="	VALUES (NULL, 'admin', 'admin', 'admin', '1');  " ; 
echo $query ; 
mysql_query($query) or die (mysql_error());

$query ="INSERT INTO `user_role_type` (`role_name`) VALUES ('Administrator') " ; 
echo $query ; 
mysql_query($query) or die (mysql_error());
$query ="INSERT INTO `user_role_type` (`role_name`) VALUES ('Guest') " ; 
echo $query ; 
mysql_query($query) or die (mysql_error());

$query ="INSERT INTO `crud_table` (`table_name`) VALUES ('registration') " ; 
echo $query ; 
mysql_query($query) or die (mysql_error());


?>
