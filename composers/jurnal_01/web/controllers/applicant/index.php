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

$app->match('/applicant/list', function (Symfony\Component\HttpFoundation\Request $request) use ($app) {  
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
		'applicant_ID', 
		'USER_LOGIN_ID', 
		'ADDRESS', 
		'PRODI_1', 
		'PRODI_2', 
		'HIGH_SCHOOL', 
		'PICTURE_PATH', 
		'EMAIL_2', 
		'PHONE_2', 

    );
    
    $table_columns_type = array(
		'int(11)', 
		'int(11)', 
		'text', 
		'varchar(1024)', 
		'varchar(1024)', 
		'varchar(1024)', 
		'varchar(1024)', 
		'char(10)', 
		'varchar(255)', 

    );    
    
	
    $whereClause = "";
    $transform_text_to_key=""; /**  ... to enable search for externals ...**/
	
	/** find externals fields for search key and passing it to like as and id **/
	
	/**Creating enabler .. for  applicant:user_login_id  **/

	if ($searchValue!==""){
	    $search_sql = "SELECT `user_login_ID` FROM `user_login` WHERE `LOGIN` LIKE '%". $searchValue . "%'" ; 
	    $search_rows = array(); $search_rows = $app['db']->fetchAll($search_sql);
	    //error_log("#####user_login_id=>user_login######".count($search_row));
	    if(count($search_rows)>0){
	      foreach($search_rows as $search_row)  { 
	         $transform_text_to_key .= " OR  user_login_id=".$search_row['user_login_ID']; 
	      } 
	    }
	 } 


    $i = 0;
    foreach($table_columns as $col){
        
        if ($i == 0) {
           $whereClause = " WHERE ( 1 AND ";
        }
        
        if ($i > 0) {
            $whereClause =  $whereClause . " OR"; 
        }
        
        //external search  version
		$whereClause =  $whereClause . "   applicant.".$col . " LIKE '%". $searchValue ."%' ".$transform_text_to_key;
        
		//non external search version ...
		//$whereClause =  $whereClause . "   applicant.".$col . " LIKE '%". $searchValue ."%' ";
        
        $i = $i + 1;
    }
	$whereClause .= " ) ";
    
	
    
	
	
	
    $recordsTotal = $app['db']->executeQuery("SELECT * FROM `applicant`" . $whereClause . $orderClause)->rowCount();
    
    $find_sql = "SELECT * FROM `applicant`". $whereClause . $orderClause . " LIMIT ". $index . "," . $rowsPerPage;
    $rows_sql = $app['db']->fetchAll($find_sql, array());

    foreach($rows_sql as $row_key => $row_sql){
        for($i = 0; $i < count($table_columns); $i++){

			if($table_columns[$i] == 'USER_LOGIN_ID'){
			    $findexternal_sql = 'SELECT `LOGIN` FROM `user_login` WHERE `user_login_ID` = ?';
			    $findexternal_row = $app['db']->fetchAssoc($findexternal_sql, array($row_sql[$table_columns[$i]]));
			    $rows[$row_key][$table_columns[$i]] = $findexternal_row['LOGIN'];
			}
			else{
			    $rows[$row_key][$table_columns[$i]] = $row_sql[$table_columns[$i]];
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




/* Download blob img */
$app->match('/applicant/download', function (Symfony\Component\HttpFoundation\Request $request) use ($app) { 
    
    // menu
    $rowid = $request->get('id');
    $idfldname = $request->get('idfld');
    $fieldname = $request->get('fldname');
    
    if( !$rowid || !$fieldname ) die("Invalid data");
    
    $find_sql = "SELECT " . $fieldname . " FROM " . applicant . " WHERE ".$idfldname." = ?";
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



$app->match('/applicant', function () use ($app) {
    
	$table_columns = array(
		'applicant_ID', 
		'USER_LOGIN_ID', 
		'ADDRESS', 
		'PRODI_1', 
		'PRODI_2', 
		'HIGH_SCHOOL', 
		'PICTURE_PATH', 
		'EMAIL_2', 
		'PHONE_2', 

    );
	
	/**translating here ...**/
	$tr_table_columns=array();
	foreach ($table_columns as $col){
		//var_dump($col) ;die;
		//array_push($tr_table_columns,$app['translator']->trans("applicant".$col));
		array_push($tr_table_columns,$col);
	}
	$table_columns=$tr_table_columns;
	/****/

    $primary_key = "applicant_ID";	

    return $app['twig']->render('applicant/list.html.twig', array(
    	"table_columns" => $table_columns,
        "primary_key" => $primary_key
    ));
        
})
->bind('applicant_list');



$app->match('/applicant/create', function () use ($app) {
    
    $initial_data = array(
		'USER_LOGIN_ID' => '', 
		'ADDRESS' => '', 
		'PRODI_1' => '', 
		'PRODI_2' => '', 
		'HIGH_SCHOOL' => '', 
		'PICTURE_PATH' => '', 
		'EMAIL_2' => '', 
		'PHONE_2' => '', 

    );

    $form = $app['form.factory']->createBuilder('form', $initial_data);

	$field_nullable= true  ; 
	if ($app['credentials']['current_role']!="Administrator"){
		  $field_default_ro =array('read_only' => true ) ; 
	}else { 
		  $field_default_ro =array('read_only' => false ) ; 
	} 
	$limiter="" ;
	$options = array();
	$findexternal_sql = 'SELECT user_login.user_login_ID, user_login.LOGIN FROM user_login  '  . $limiter ;
	$findexternal_rows = $app['db']->fetchAll($findexternal_sql, array());
	foreach($findexternal_rows as $findexternal_row){
	    $options[$findexternal_row['user_login_ID']] = $findexternal_row['LOGIN'];
	}
	if(count($options) > 0){
	    $form = $form->add('USER_LOGIN_ID', 'choice', array_merge($field_default_ro,array(
	        'required' => $field_nullable,
	        'choices' => $options,
	        'expanded' => false,
	        'constraints' => new Assert\Choice(array_keys($options))
	    )));
	}
	else{
	    $form = $form->add('USER_LOGIN_ID', 'text', array_merge(array('required' => true),$field_default_ro));
	}



	$field_default_ro=array('required' => true,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$field_default_ro=array('required' => true,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('ADDRESS', 'textarea', array_merge(array('required' => true),$field_default_ro));
	$field_default_ro=array('required' => true,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('PRODI_1', 'text', array_merge(array('required' => true),$field_default_ro));
	$field_default_ro=array('required' => true,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('PRODI_2', 'text', array_merge(array('required' => true),$field_default_ro));
	$field_default_ro=array('required' => true,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('HIGH_SCHOOL', 'text', array_merge(array('required' => true),$field_default_ro));
	$field_default_ro=array('required' => true,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('PICTURE_PATH', 'text', array_merge(array('required' => true),$field_default_ro));
	$field_default_ro=array('required' => false,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('EMAIL_2', 'text', array_merge(array('required' => false),$field_default_ro));
	$field_default_ro=array('required' => false,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('PHONE_2', 'text', array_merge(array('required' => false),$field_default_ro));


    $form = $form->getForm();

    if("POST" == $app['request']->getMethod()){

        $form->handleRequest($app["request"]);

        if ($form->isValid()) {
            $data = $form->getData();
			
            $update_query = "INSERT INTO `applicant` (`USER_LOGIN_ID`, `ADDRESS`, `PRODI_1`, `PRODI_2`, `HIGH_SCHOOL`, `PICTURE_PATH`, `EMAIL_2`, `PHONE_2`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $app['db']->executeUpdate($update_query, array($data['USER_LOGIN_ID'], $data['ADDRESS'], $data['PRODI_1'], $data['PRODI_2'], $data['HIGH_SCHOOL'], $data['PICTURE_PATH'], $data['EMAIL_2'], $data['PHONE_2']));            


            $app['session']->getFlashBag()->add(
                'success',
                array(
                    'message' => 'applicant created!',
                )
            );
            return $app->redirect($app['url_generator']->generate('applicant_list'));

        }
    }

    return $app['twig']->render('applicant/create.html.twig', array(
        "form" => $form->createView()
    ));
        
})
->bind('applicant_create');



$app->match('/applicant/edit/{id}', function ($id) use ($app) {

    $find_sql = "SELECT * FROM `applicant` WHERE `applicant_ID` = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($id));

    if(!$row_sql){
        $app['session']->getFlashBag()->add(
            'danger',
            array(
                'message' => 'Row not found!',
            )
        );        
        return $app->redirect($app['url_generator']->generate('applicant_list'));
    }

    
    $initial_data = array(
		'USER_LOGIN_ID' => $row_sql['USER_LOGIN_ID'], 
		'ADDRESS' => $row_sql['ADDRESS'], 
		'PRODI_1' => $row_sql['PRODI_1'], 
		'PRODI_2' => $row_sql['PRODI_2'], 
		'HIGH_SCHOOL' => $row_sql['HIGH_SCHOOL'], 
		'PICTURE_PATH' => $row_sql['PICTURE_PATH'], 
		'EMAIL_2' => $row_sql['EMAIL_2'], 
		'PHONE_2' => $row_sql['PHONE_2'], 

    );


    $form = $app['form.factory']->createBuilder('form', $initial_data);

	$field_nullable= true  ; 
	if ($app['credentials']['current_role']!="Administrator"){
		  $field_default_ro =array('read_only' => true ) ; 
	}else { 
		  $field_default_ro =array('read_only' => false ) ; 
	} 
	$limiter="" ;
	$options = array();
	$findexternal_sql = 'SELECT user_login.user_login_ID, user_login.LOGIN FROM user_login  '  . $limiter ;
	$findexternal_rows = $app['db']->fetchAll($findexternal_sql, array());
	foreach($findexternal_rows as $findexternal_row){
	    $options[$findexternal_row['user_login_ID']] = $findexternal_row['LOGIN'];
	}
	if(count($options) > 0){
	    $form = $form->add('USER_LOGIN_ID', 'choice', array_merge($field_default_ro,array(
	        'required' => $field_nullable,
	        'choices' => $options,
	        'expanded' => false,
	        'constraints' => new Assert\Choice(array_keys($options))
	    )));
	}
	else{
	    $form = $form->add('USER_LOGIN_ID', 'text', array_merge(array('required' => true),$field_default_ro));
	}


	$field_default_ro=array('required' => true,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$field_default_ro=array('required' => true,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('ADDRESS', 'textarea', array_merge(array('required' => true),$field_default_ro));
	$field_default_ro=array('required' => true,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('PRODI_1', 'text', array_merge(array('required' => true),$field_default_ro));
	$field_default_ro=array('required' => true,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('PRODI_2', 'text', array_merge(array('required' => true),$field_default_ro));
	$field_default_ro=array('required' => true,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('HIGH_SCHOOL', 'text', array_merge(array('required' => true),$field_default_ro));
	$field_default_ro=array('required' => true,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('PICTURE_PATH', 'text', array_merge(array('required' => true),$field_default_ro));
	$field_default_ro=array('required' => false,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('EMAIL_2', 'text', array_merge(array('required' => false),$field_default_ro));
	$field_default_ro=array('required' => false,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('PHONE_2', 'text', array_merge(array('required' => false),$field_default_ro));


    $form = $form->getForm();

    if("POST" == $app['request']->getMethod()){

        $form->handleRequest($app["request"]);

        if ($form->isValid()) {
            $data = $form->getData();
			

            $update_query = "UPDATE `applicant` SET `USER_LOGIN_ID` = ?, `ADDRESS` = ?, `PRODI_1` = ?, `PRODI_2` = ?, `HIGH_SCHOOL` = ?, `PICTURE_PATH` = ?, `EMAIL_2` = ?, `PHONE_2` = ? WHERE `applicant_ID` = ?";
            $app['db']->executeUpdate($update_query, array($data['USER_LOGIN_ID'], $data['ADDRESS'], $data['PRODI_1'], $data['PRODI_2'], $data['HIGH_SCHOOL'], $data['PICTURE_PATH'], $data['EMAIL_2'], $data['PHONE_2'], $id));            

	
            $app['session']->getFlashBag()->add(
                'success',
                array(
                    'message' => 'applicant edited!',
                )
            );
            return $app->redirect($app['url_generator']->generate('applicant_edit', array("id" => $id)));

        }
    }

    return $app['twig']->render('applicant/edit.html.twig', array(
        "form" => $form->createView(),
        "id" => $id
    ));
        
})
->bind('applicant_edit');



$app->match('/applicant/delete/{id}', function ($id) use ($app) {

    $find_sql = "SELECT * FROM `applicant` WHERE `applicant_ID` = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($id));

    if($row_sql){
        $delete_query = "DELETE FROM `applicant` WHERE `applicant_ID` = ?";
        $app['db']->executeUpdate($delete_query, array($id));

        $app['session']->getFlashBag()->add(
            'success',
            array(
                'message' => 'applicant deleted!',
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

    return $app->redirect($app['url_generator']->generate('applicant_list'));

})
->bind('applicant_delete');






