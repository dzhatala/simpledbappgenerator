#!/usr/bin/env php
<?php

/***
*
dzulqarnaenhatala@gmail.com

*/

echo "test Set 01 \n";

require_once __DIR__."/../web/vendor_01/auth.php";

$info=TO_CRUD_info("/create/user_login");
//var_dump($info);

if ($info[0]=="create") {
	echo "create in table ". $info[1]."\n";
}

$info=TO_CRUD_info("/delete/user_login/1");
//var_dump($info);

if ($info[0]=="delete") {
	echo "delete id:".$info[2]." in table ". $info[1]."\n";
}