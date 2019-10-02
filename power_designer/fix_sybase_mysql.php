<?php
//fixing auto increment from sybase generated 

$mysql_hostname="localhost";
$mysql_user = "root";
$mysql_password = "";
$mysql_database = "lamp_reporting";

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
	$fixsql="ALTER TABLE `".$tablename."` CHANGE `".$tablename."_id` `".$tablename."_id` INT(11) NOT NULL AUTO_INCREMENT";
	echo $fixsql."\n";
	mysql_query($fixsql) or die (mysql_error());
	
	$tablerow = mysql_fetch_row($results);
	
}
	
$query=" DELETE FROM user_login" ;
echo $query."\n" ; mysql_query($query) or die (mysql_error());

$query ="INSERT INTO `user_login` (`user_login_ID`, `LOGIN`, " ;
$query.="	`PLAIN_PASSWORD`, `HASHED_PASSWORD`, `USER_LEVEL`) " ;
$query.="	VALUES (1, 'admin', 'admin', 'admin', '1');  " ; 
echo $query."\n" ; mysql_query($query) or die (mysql_error());

$query ="INSERT INTO `user_login` (`user_login_ID`, `LOGIN`, " ;
$query.="	`PLAIN_PASSWORD`, `HASHED_PASSWORD`, `USER_LEVEL`) " ;
$query.="	VALUES (2, 'joesmart', 'joesmart', 'joesmart', '1');  " ; 
echo $query."\n" ; mysql_query($query) or die (mysql_error());


$query=" DELETE FROM user_role_type" ;
echo $query."\n" ; mysql_query($query) or die (mysql_error());

$query ="INSERT INTO `user_role_type` (`user_role_type_id`,`role_name`) VALUES (1,'Administrator') " ; 
echo $query."\n" ; mysql_query($query) or die (mysql_error());


$query ="INSERT INTO `user_role_type` (`role_name`) VALUES ('Guest') " ; 
echo $query."\n" ; mysql_query($query) or die (mysql_error());

$query ="INSERT INTO `user_role_type` (`role_name`) VALUES ('Registered') " ; 
echo $query."\n" ; mysql_query($query) or die (mysql_error());


$query=" DELETE FROM user_role" ;
echo $query."\n" ; mysql_query($query) or die (mysql_error());

$query ="INSERT INTO `user_role` (`user_login_id`,`user_role_type_id`) VALUES (1,1) " ; 
echo $query."\n" ; mysql_query($query) or die (mysql_error());




$query=" DELETE FROM crud_table" ;
echo $query."\n" ; mysql_query($query) or die (mysql_error());


$query ="INSERT INTO `crud_table` (`name`) VALUES ('applicant') " ; 
echo $query."\n" ; mysql_query($query) or die (mysql_error());



$query = "SET FOREIGN_KEY_CHECKS=1";
echo $query."\n" ; mysql_query($query) or die (mysql_error());

?>
