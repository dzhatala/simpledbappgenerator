<?php

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
if(isset($_SERVER['HTTP_HOST']))
$cfgIndexpage=$_SERVER['HTTP_HOST'];
$cfgDefaultPolicy= FALSE ; // TRUE/1/ACCEPT,   FALSE/0=DENY
/**return sql rows */


/** guest credentials **/
function default_credentials(){
	//echo "dc "; die;
	$credentials=array();
	$roles=array();
	array_push($roles,"Guest");
	$credentials['login']="guest";
	//$credentials['userlogin_id']=-1;//admin id always 1
	
	/*f*@TODO, cek for requesting roles here .....*/
	//error_log("@TODO, assign current role without, checking requesting roles ...");
	
	
	$credentials['current_role']=$roles[0];
	$credentials['roles']=$roles;
	return $credentials;
}

function user_credentials($login){
	//echo "dc "; die;
	$credentials=array();
	$roles=array();
	array_push($roles,"Registered");
	$credentials['login']=$login;
	//$credentials['userlogin_id']=-1;//admin id always 1
	
	/*f*@TODO, cek for requesting roles here .....*/
	//error_log("@TODO, assign current role without, checking requesting roles ...");
	
	
	$credentials['current_role']=$roles[0];
	$credentials['roles']=$roles;
	return $credentials;
}

function getRoles($app, $login){
	
	$sql="select user_login.user_login_id, user_login.login,user_role_type.role_name ".
	" from user_login, user_role,user_role_type ".
	" where  user_login.user_login_id=user_role.user_login_id ".
	" and user_role_type.user_role_type_id=user_role.user_role_type_id ".
	" AND user_login.login='".$login."'";

	return $app['db']->fetchAll($sql, array());
}

function startsWith($haystack, $needle)
{
	 $length = strlen($needle);
	 return (substr($haystack, 0, $length) === $needle);
	 
}

function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
}

function deny($login,$cfgIndexpage, $uri,$strLogout){
	
	//@todo can't throw exception ...
	//throw new AccessDeniedException($login. "! Anda tidak berhak ke  #".$uri ."#\n" ); 
	//
	
	echo "<center><H3> \n";
	//echo "Hi <font color=\"red\"> '".$login. "'</font> !!!. Anda tidak berhak ke  #".$uri ."#\n" ; 
	echo "Hi '<font color=\"blue\">".$login. "'</font> !!!. Anda <font color=\"red\"> tidak berhak </font> ke  #".$uri ."#\n" ; 
	echo "<br><a href=\"javascript:history.back()\">Kembali</a> \n";
	echo "<br><a href=\"http://".$cfgIndexpage."\">\n".
	"Halaman Awal"."</a>";
	echo "<br><a href=\"http://".$cfgIndexpage."/vendor_01/logout.php\">\n".
	$strLogout."</a>\n";
	echo "</H3></center>\n";
		

	die;
}

//decomposed creation record from uri
function TO_CRUD_info($uri){

	//var_dump($uri);
	$parts = explode('/',$uri);
	//var_dump($parts);
	$info=array();
	$start=FALSE;
	$i=0;
	foreach ($parts as $p ){
		//var_dump($p);
		if(strlen($p)>0) $start=TRUE;
		if($start) {
		//	$info[$i]=$p;
			array_push($info, $p);
			$i++;
		}
	}
	//var_dump($info);
	return $info;

}

function is_CREATE_authorized($login,$create_uri){

	return FALSE;
}

function is_CRUD_authorized($login,$uri){
	$create_uri = TO_CRUD_info($uri);
	if($create_uri!=NULL){
		return is_CREATE_authorized($login,$create_uri);
	}
	return FALSE;
}

/***
	authorization and creating necessary credentials ....
	
*/


function is_authorized($app, $login,$cfgIndexpage,$strLogout,$uri) { 
	
	
	
	if(strlen($_SERVER['REQUEST_URI'])>1    
		& $uri !="/?do_login" 
		& $uri !="?do_login" 
		& $uri !="/"
		& $uri !="/?"
		& $login !="admin"
		& $uri !="/?"
		& !( isset($login)&  startsWith($uri,'/profile_edit')  )  /** login exist that must be Registered role***/
	) 
	if (!is_CRUD_authorized($login,$uri)){
		error_log("deny. ".$uri);
		deny($login,$cfgIndexpage,$uri,$strLogout);
		
	}
	
	//echo "pass 0" ; die;
	/**admin all allow */
	//var_dump($_POST) ;die;
	//return true; //test only
//	if($login=="admin"|TRUE) {  // uncomment this for admin test / maintenance
	if($login=="admin") {
	
		$credentials=array();
		$roles=array();
		array_push($roles,"Administrator");
		$credentials['login']=$login;
		$credentials['userlogin_id']=1;//admin id always 1
		
		/**@TODO, cek for requesting roles here .....*/
		//error_log("@TODO, assign current role without, checking requesting roles ...");
		$credentials['current_role']=$roles[0];
		if(isset($_SESSION['request_role']))
		if(
		    $_SESSION['request_role']==$roles[0]
			||$_SESSION['request_role']==$roles[1]
		    ||$_SESSION['request_role']==$roles[2]
		){
				$credentials['current_role']=$_SESSION['request_role'];
				$request_role_match=true;
				//var_dump ($credentials['current_role']); die;
		} 	
		$credentials['roles']=$roles;
		$app['credentials']=$credentials;
		return true;
	
	} // : if (login==admin)
	
	//var_dump($_SESSION) ; die;
	
	/**not admin allow **/
	
	
	//if ($uri==$GLOBALS['$www_root_uri'] ) $uri ="Halaman Awal";
	//echo "pass 1" ; die;
	$roles_row=getRoles($app,$login);
	$roles=array();
	
	if(isset($login)){
		//var_dump($login);
		//var_dump(sizeof($roles)); die;
		$app['credentials']=user_credentials($login);
		return true;
	}
	//var_dump ($roles); die;
	$request_role_match=false;
	//$request_role_idx=-1;
	if($roles_row) {
		echo "pass b" ; die;
		$credentials=array();
		//error_log($roles_row);
		//error_log(var_dump($roles_row));
		foreach ($roles_row as $row){
			//error_log($row['userlogin_id']." ".$row['name']); 
			//var_dump($_SESSION); die;
			//error_log("row=>".$row['name']);
			//error_log("session=>".$_SESSION['request_role']);
			if(isset($_SESSION['request_role']))	
			if($_SESSION['request_role']==$row['name']){
				$credentials['current_role']=$row['name'];
				$request_role_match=true;
				//var_dump ($credentials['current_role']); die;
			}
			array_push($roles,$row['name']);
			$credentials['userlogin_id']=$row['userlogin_id'];
		}
		
		$credentials['roles']=$roles;
		$credentials['login']=$login;
		if(!$request_role_match){
			$credentials['current_role']=$roles[0];
		}
		$app['credentials']=$credentials;
		
		
		
		
		//return ; uncomment to ALLOW 
	}
	/** block **/
	
	deny($login,$cfgIndexpage,$uri,$strLogout);
}


?>
