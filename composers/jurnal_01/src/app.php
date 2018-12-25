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

 
/*is_authorized($login,$cfgIndexpage,$strLogout,$_SERVER['REQUEST_URI']);
*/

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\MessageSelector;

 
class queryData {
	public $start;
	public $recordsTotal;
	public $recordsFiltered;
	public $data;

	function queryData() {
	}
}

use Silex\Application;
//use Silex\Provider ; 

$app = new Application();


$app->register(new Silex\Provider\TwigServiceProvider(), array(
	'twig.path' => __DIR__.'/../web/views',
));
$app->register(new Silex\Provider\FormServiceProvider());
$app->register(new Silex\Provider\TranslationServiceProvider(), array(
	'translator.messages' => array(),
));
$app->register(new Silex\Provider\ValidatorServiceProvider());
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(

		'dbs.options' => array(
			'db' => array(
				'driver'   => 'pdo_mysql',
				'dbname'   => 'registration',
				'host'     => '127.0.0.1',
				'user'     => 'root',
				'password' => '',
				'charset'  => 'utf8',
			),
		)
));



/*must uncomment this */
if(isset($login)){
	require_once __DIR__.'/../web/vendor_01/auth.php';
	is_authorized($app,$login,$cfgIndexpage,$strLogout,$_SERVER['REQUEST_URI']);
}



//$x=new Silex\Provider\SecurityServiceProvider();
//$app->register($x);


//$app['asset_path'] = '/resources';
//@todo, #changeme
$app['asset_path'] = '/resources';
$app['debug'] = true;
	// array of REGEX column name to display for foreigner key insted of ID
	// default used :'name','title','e?mail','username'
$app['usr_search_names_foreigner_key'] = array('name','description', 'info','login');

//renavigated vars

//$app['ACADEMIC_CALENDAR_ID']='1';
//$app['simlitabmas_role']='guest';
//$app['simlitabmas_username']='Guest';
$app['www_root'] = $_SERVER['SERVER_NAME']; /** symfony web dir on browsers address **/
$app['uploaded_dir']="f:/RESEARCHS/silex_windows_/uploads/jurnal_01";  /** permanent client uploaded files location **/
$app['www_uploaded']="/getfile_jurnal_01"; /** temporary directory to download by client ...   
                                                   this directory must be created in doc root apache												   
												   ***/
												   
												   
					;							   
if(isset($login) && $login){
	$app['simlitabmas_username']=$login;
	
};

$app->simlitabmas_hasRole=function ($role){
	
	return false;
};

$app['date_crud']=false; // for datepicker

/*
$app->before(function(){ 

	if(isset($login) && $login){
		echo $login ;die ;
	}
	
	//die;
	//echo "before filter "; die ; 

});*/


$app->match($app['www_uploaded'].'/{f1}/{f2}', function ($f1,$f2) use ($app) {

	$filename=$app['uploaded_dir']."/".$f1."/".$f2;
    //echo "request to get file "."$f1  $f2  ==> $filename" ; die;
    if(file_exists($filename)){
		$response= new Symfony\Component\HttpFoundation\BinaryFileResponse($filename);
		$response->setContentDisposition(Symfony\Component\HttpFoundation\ResponseHeaderBag::DISPOSITION_ATTACHMENT);
		return $response;
	}    else {
		throw new NotFoundHttpException("file $f2 not found !");
	
	}
})
->bind('getfile_jurnal_01');


$app->match('/navchangerole', function () use ($app) {

    echo "request to change role ......." ; 
	//die;
	if("POST" == $app['request']->getMethod()){
		    
			$r = Symfony\Component\HttpFoundation\Request::createFromGlobals();
			//var_dump (get_class($r));die;
			
			//var_dump($r->request); die;
			
			$class=get_class($r->request);
			
			$rr=$r->request;
			
			//$class=get_class($rr);
			$a_all=$rr->all();
			//var_dump($a_all);die;
			
			//$methods = get_class_methods($class);
			//$methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);
			//var_dump ($methods);die;
			//var_dump ($r->getQueryString());die;
			
			//die;
			//var_dump($this);
			//var_dump($app['request']->request);
			//var_dump($app['request']->class);die;	
			//var_dump($app['request']->request->parameters);
	
			//	$class = new ReflectionClass('Apple');
			//$class=$app['request']->request->get_class();
			//$methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);
			//var_dump($methods);			die ();
			//$pb=$app['request']->request;
			//$rl=$pb.all();
			
			if(!isset($a_all['role_selector']['request_role'])) {
				var_dump($a_all); 
				echo "can't get role #". $a_all["request_role"]."#" ;die;
 			}
			
			$rq_rl=$a_all['role_selector']['request_role'];
			
			if($rq_rl!=="Administrator"
			&& $rq_rl!=="Pengusul"
			&& $rq_rl!=="Reviewer"
			){
				throw 	 new AccessDeniedException("Simlitabmas Exception : "."Bad Role requested " . $rq_rl );
			}
			
			/*$session=$app ['request'] ->getSession();
			if(!isset($session)) {
				throw 	 new AccessDeniedException("Simlitabmas Exception : "."No Session" );
			}
			
			$session->set("request_role",$a_all['role_selector']['request_role']);
			*/
			
			$_SESSION['request_role']=$a_all['role_selector']['request_role'];
			return $app->redirect($app['www_root']);

			
	} else{
		die("no post in role change") ;
	}
        
})
->bind('navchangerole');

$translator = new Translator('in_ID', new MessageSelector());
$translator->addLoader('array', new \Symfony\Component\Translation\Loader\ArrayLoader());

require_once __DIR__.'/lang.php';

$translator->addResource('array', get_words(), 'in_ID');

//$Bonjour = $translator->trans('Hello World!');
//echo $Bonjour ; die;

$app['translator']=$translator;

if (!isset($app['credentials'])) 
{
	
	require_once __DIR__.'/../web/vendor_01/auth.php';
	$credentials=default_credentials();
	$app['credentials']=$credentials;
}
$app['title'] = "Simple DB APP";
$app['login_url']="/?do_login" ;
$app['logout_url']="/vendor_01/logout.php" ;
return $app;
