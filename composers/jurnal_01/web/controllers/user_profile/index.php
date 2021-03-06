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


require_once __DIR__.'/../../../vendor/autoload.php';
require_once __DIR__.'/../../../src/app.php';

use Symfony\Component\Validator\Constraints as Assert;
/*zoel
$app->match('/user_login/list', function (Symfony\Component\HttpFoundation\Request $request) use ($app) {  
    $start = 0;
    $vars = $request->query->all();
    $qsStart = (int)$vars["start"];
    $search = $vars["search"];
    $order = $vars["order"];
    $columns = $vars["columns"];
    $qsLength = (int)$vars["length"];    
    
    if($qsStart) {
        $start = $qsStart;
    }    
	
    $index = $start;   
    $rowsPerPage = $qsLength;
       
    $rows = array();
    
    $searchValue = $search['value'];
    $orderValue = $order[0];
    
    $orderClause = "";
    if($orderValue) {
        $orderClause = " ORDER BY ". $columns[(int)$orderValue['column']]['data'] . " " . $orderValue['dir'];
    }
    
    $table_columns = array(
		'user_login_id', 
		'login', 
		'plain_password', 
		'hashed_password', 
		'user_level', 
		'email', 
		'phone', 

    );
    
    $table_columns_type = array(
		'int(11)', 
		'varchar(255)', 
		'varchar(255)', 
		'varchar(255)', 
		'int(11)', 
		'varchar(255)', 
		'varchar(32)', 

    );    
    
	
    $whereClause = "";
    $transform_text_to_key=""; /**  ... to enable search for externals ...**/
	
	/** find externals fields for search key and passing it to like as and id **/
	
/*zoel
    $i = 0;
    foreach($table_columns as $col){
        
        if ($i == 0) {
           $whereClause = " WHERE ( 1 AND ";
        }
        
        if ($i > 0) {
            $whereClause =  $whereClause . " OR"; 
        }
        
        //external search  version
		$whereClause =  $whereClause . "   user_login.".$col . " LIKE '%". $searchValue ."%' ".$transform_text_to_key;
        
		//non external search version ...
		//$whereClause =  $whereClause . "   user_login.".$col . " LIKE '%". $searchValue ."%' ";
        
        $i = $i + 1;
    }
	$whereClause .= " ) ";
    
	
    
	
	
	
    $recordsTotal = $app['db']->executeQuery("SELECT * FROM `user_login`" . $whereClause . $orderClause)->rowCount();
    
    $find_sql = "SELECT * FROM `user_login`". $whereClause . $orderClause . " LIMIT ". $index . "," . $rowsPerPage;
    $rows_sql = $app['db']->fetchAll($find_sql, array());

    foreach($rows_sql as $row_key => $row_sql){
        for($i = 0; $i < count($table_columns); $i++){

		if( $table_columns_type[$i] != "blob") {
				$rows[$row_key][$table_columns[$i]] = $row_sql[$table_columns[$i]];
		} else {				if( !$row_sql[$table_columns[$i]] ) {
						$rows[$row_key][$table_columns[$i]] = "0 Kb.";
				} else {
						$rows[$row_key][$table_columns[$i]] = " <a target='__blank' href='menu/download?id=" . $row_sql[$table_columns[0]];
						$rows[$row_key][$table_columns[$i]] .= "&fldname=" . $table_columns[$i];
						$rows[$row_key][$table_columns[$i]] .= "&idfld=" . $table_columns[0];
						$rows[$row_key][$table_columns[$i]] .= "'>";
						$rows[$row_key][$table_columns[$i]] .= number_format(strlen($row_sql[$table_columns[$i]]) / 1024, 2) . " Kb.";
						$rows[$row_key][$table_columns[$i]] .= "</a>";
				}
		}

        }
    }    
    
    $queryData = new queryData();
    $queryData->start = $start;
    $queryData->recordsTotal = $recordsTotal;
    $queryData->recordsFiltered = $recordsTotal;
    $queryData->data = $rows;
    
    return new Symfony\Component\HttpFoundation\Response(json_encode($queryData), 200);
});




/* Download blob img */ /*zoel
$app->match('/user_login/download', function (Symfony\Component\HttpFoundation\Request $request) use ($app) { 
    
    // menu
    $rowid = $request->get('id');
    $idfldname = $request->get('idfld');
    $fieldname = $request->get('fldname');
    
    if( !$rowid || !$fieldname ) die("Invalid data");
    
    $find_sql = "SELECT " . $fieldname . " FROM " . user_login . " WHERE ".$idfldname." = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($rowid));

    if(!$row_sql){
        $app['session']->getFlashBag()->add(
            'danger',
            array(
                'message' => 'Row not found!',
            )
        );        
        return $app->redirect($app['url_generator']->generate('menu_list'));
    }

    header('Content-Description: File Transfer');
    header('Content-Type: image/jpeg');
    header("Content-length: ".strlen( $row_sql[$fieldname] ));
    header('Expires: 0');
    header('Cache-Control: public');
    header('Pragma: public');
    ob_clean();    
    echo $row_sql[$fieldname];
    exit();
   
    
});



$app->match('/user_login', function () use ($app) {
    
	$table_columns = array(
		'user_login_id', 
		'login', 
		'plain_password', 
		'hashed_password', 
		'user_level', 
		'email', 
		'phone', 

    );
	
	/**translating here ...**/ /*zoel
	$tr_table_columns=array();/*
	foreach ($table_columns as $col){
		//var_dump($col) ;die;
		//array_push($tr_table_columns,$app['translator']->trans("user_login".$col));
		array_push($tr_table_columns,$col);
	}
	$table_columns=$tr_table_columns;
	/****/
/*zoel
    $primary_key = "user_login_id";	

    return $app['twig']->render('user_login/list.html.twig', array(
    	"table_columns" => $table_columns,
        "primary_key" => $primary_key
    ));
        
})
->bind('user_login_list');



$app->match('/user_login/create', function () use ($app) {
    
    $initial_data = array(
		'login' => '', 
		'plain_password' => '', 
		'hashed_password' => '', 
		'user_level' => '', 
		'email' => '', 
		'phone' => '', 

    );

    $form = $app['form.factory']->createBuilder('form', $initial_data);



	$field_default_ro=array('required' => true,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$field_default_ro=array('required' => true,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('login', 'text', array_merge(array('required' => true),$field_default_ro));
	$field_default_ro=array('required' => false,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('plain_password', 'text', array_merge(array('required' => false),$field_default_ro));
	$field_default_ro=array('required' => true,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('hashed_password', 'text', array_merge(array('required' => true),$field_default_ro));
	$field_default_ro=array('required' => true,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('user_level', 'text', array_merge(array('required' => true),$field_default_ro));
	$field_default_ro=array('required' => true,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('email', 'text', array_merge(array('required' => true),$field_default_ro));
	$field_default_ro=array('required' => false,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('phone', 'text', array_merge(array('required' => false),$field_default_ro));


    $form = $form->getForm();

    if("POST" == $app['request']->getMethod()){

        $form->handleRequest($app["request"]);

        if ($form->isValid()) {
            $data = $form->getData();
			
            $update_query = "INSERT INTO `user_login` (`login`, `plain_password`, `hashed_password`, `user_level`, `email`, `phone`) VALUES (?, ?, ?, ?, ?, ?)";
            $app['db']->executeUpdate($update_query, array($data['login'], $data['plain_password'], $data['hashed_password'], $data['user_level'], $data['email'], $data['phone']));            


            $app['session']->getFlashBag()->add(
                'success',
                array(
                    'message' => 'user_login created!',
                )
            );
            return $app->redirect($app['url_generator']->generate('user_login_list'));

        }
    }

    return $app['twig']->render('user_login/create.html.twig', array(
        "form" => $form->createView()
    ));
        
})
->bind('user_login_create');


zoel*/
$app->match('/profile_edit', function () use ($app) {
	
	$id=$app['credentials']['login'];
    $find_sql = "SELECT * FROM `user_login` WHERE `login` = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($id));

    if(!$row_sql){
        $app['session']->getFlashBag()->add(
            'danger',
            array(
                'message' => 'Your data not found!',
            )
        );        
        return $app->redirect($app['url_generator']->generate('profile_edit'));
    }

    
    $initial_data = array(
		'login' => $row_sql['login'], 
		'plain_password' => $row_sql['plain_password'], 
		'hashed_password' => $row_sql['hashed_password'], 
		'user_level' => $row_sql['user_level'], 
		'email' => $row_sql['email'], 
		'phone' => $row_sql['phone'], 

    );


    $form = $app['form.factory']->createBuilder('form', $initial_data);


	$field_default_ro=array('required' => true,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$field_default_ro=array('required' => true,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('login', 'text', array_merge(array('required' => true),$field_default_ro));
	$field_default_ro=array('required' => false,'disabled' =>true)  ; 
	//if($app['credentials']['current_role']=="Administrator"){
	if(TRUE){
		unset($field_default_ro['disabled']);
	}
	$form = $form->add('plain_password', 'text', array_merge(array('required' => false),$field_default_ro));
	$field_default_ro=array('required' => true,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	//if(1){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('hashed_password', 'text', array_merge(array('required' => true),$field_default_ro));
	$field_default_ro=array('required' => true,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('user_level', 'text', array_merge(array('required' => true),$field_default_ro));
	$field_default_ro=array('required' => true,'disabled' =>true)  ; 
	//if($app['credentials']['current_role']=="Administrator"){
	if(TRUE){
		unset($field_default_ro['disabled']);
	}
	$form = $form->add('email', 'text', array_merge(array('required' => true),$field_default_ro));
	$field_default_ro=array('required' => false,'disabled' =>true)  ; 
	//if($app['credentials']['current_role']=="Administrator"){
	if(TRUE){
		unset($field_default_ro['disabled']);
	}
	$form = $form->add('phone', 'text', array_merge(array('required' => false),$field_default_ro));


    $form = $form->getForm();

    if("POST" == $app['request']->getMethod()){

        $form->handleRequest($app["request"]);

        if ($form->isValid()) {
            $data = $form->getData();
			

//            $update_query = "UPDATE `user_login` SET `login` = ?, `plain_password` = ?, `hashed_password` = ?, `user_level` = ?, `email` = ?, `phone` = ? WHERE `user_login_id` = ?";
 //           $app['db']->executeUpdate($update_query, array($data['login'], $data['plain_password'], $data['hashed_password'], $data['user_level'], $data['email'], $data['phone'], $id));            

           $update_query = "UPDATE `user_login` SET  `plain_password` = ?,  `email` = ?, `phone` = ? WHERE `login` = ?";
           $app['db']->executeUpdate($update_query, array($data['plain_password'], $data['email'], $data['phone'], $id));            
	
            $app['session']->getFlashBag()->add(
                'success',
                array(
                    'message' => 'Profile edited!',
                )
            );
            return $app->redirect($app['url_generator']->generate('profile_edit'));

        }
    }

    return $app['twig']->render('user_profile/edit.html.twig', array(
        "form" => $form->createView(),
        "id" => $id
    ));
        
})
->bind('profile_edit');

/*zoel

$app->match('/user_login/delete/{id}', function ($id) use ($app) {

    $find_sql = "SELECT * FROM `user_login` WHERE `user_login_id` = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($id));

    if($row_sql){
        $delete_query = "DELETE FROM `user_login` WHERE `user_login_id` = ?";
        $app['db']->executeUpdate($delete_query, array($id));

        $app['session']->getFlashBag()->add(
            'success',
            array(
                'message' => 'user_login deleted!',
            )
        );
    }
    else{
        $app['session']->getFlashBag()->add(
            'danger',
            array(
                'message' => 'Row not found!',
            )
        );  
    }

    return $app->redirect($app['url_generator']->generate('user_login_list'));

})
->bind('user_login_delete');
*/





