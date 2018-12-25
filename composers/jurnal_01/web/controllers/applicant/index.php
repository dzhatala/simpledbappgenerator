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
		'applicant_id', 
		'user_login_id', 
		'birth_date', 
		'birth_place', 
		'address', 
		'prodi_1', 
		'prodi_2', 
		'high_school', 
		'email_2', 
		'phone_2', 
		'path_documents', 
		'path_picture', 

    );
    
    $table_columns_type = array(
		'int(11)', 
		'int(11)', 
		'date', 
		'varchar(1024)', 
		'text', 
		'varchar(1024)', 
		'varchar(1024)', 
		'varchar(1024)', 
		'varchar(1024)', 
		'varchar(255)', 
		'varchar(1024)', 
		'varchar(1024)', 

    );    
    
	
    $whereClause = "";
    $transform_text_to_key=""; /**  ... to enable search for externals ...**/
	
	/** find externals fields for search key and passing it to like as and id **/
	
	/**Creating enabler .. for  applicant:user_login_id  **/

	if ($searchValue!==""){
	    $search_sql = "SELECT `user_login_id` FROM `user_login` WHERE `login` LIKE '%". $searchValue . "%'" ; 
	    $search_rows = array(); $search_rows = $app['db']->fetchAll($search_sql);
	    //error_log("#####user_login_id=>user_login######".count($search_row));
	    if(count($search_rows)>0){
	      foreach($search_rows as $search_row)  { 
	         $transform_text_to_key .= " OR  user_login_id=".$search_row['user_login_id']; 
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

			if($table_columns[$i] == 'user_login_id'){
			    $findexternal_sql = 'SELECT `login` FROM `user_login` WHERE `user_login_id` = ?';
			    $findexternal_row = $app['db']->fetchAssoc($findexternal_sql, array($row_sql[$table_columns[$i]]));
			    $rows[$row_key][$table_columns[$i]] = $findexternal_row['login'];
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
		'applicant_id', 
		'user_login_id', 
		'birth_date', 
		'birth_place', 
		'address', 
		'prodi_1', 
		'prodi_2', 
		'high_school', 
		'email_2', 
		'phone_2', 
		'path_documents', 
		'path_picture', 

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

    $primary_key = "applicant_id";	

    return $app['twig']->render('applicant/list.html.twig', array(
    	"table_columns" => $table_columns,
        "primary_key" => $primary_key
    ));
        
})
->bind('applicant_list');



$app->match('/applicant/create', function () use ($app) {
    
    $initial_data = array(
		'user_login_id' => '', 
		'birth_date' => '', 
		'birth_place' => '', 
		'address' => '', 
		'prodi_1' => '', 
		'prodi_2' => '', 
		'high_school' => '', 
		'email_2' => '', 
		'phone_2' => '', 
		'path_documents' => '', 
		'path_picture' => '', 

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
	$findexternal_sql = 'SELECT user_login.user_login_id, user_login.login FROM user_login  '  . $limiter ;
	$findexternal_rows = $app['db']->fetchAll($findexternal_sql, array());
	foreach($findexternal_rows as $findexternal_row){
	    $options[$findexternal_row['user_login_id']] = $findexternal_row['login'];
	}
	if(count($options) > 0){
	    $form = $form->add('user_login_id', 'choice', array_merge($field_default_ro,array(
	        'required' => $field_nullable,
	        'choices' => $options,
	        'expanded' => false,
	        'constraints' => new Assert\Choice(array_keys($options))
	    )));
	}
	else{
	    $form = $form->add('user_login_id', 'text', array_merge(array('required' => true),$field_default_ro));
	}



	$field_default_ro=array('required' => true,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$field_default_ro=array('required' => true,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	if (strpos($app['request']->getRequestUri(),"create")!==false){
		  $field_default_ro =array_merge($field_default_ro,array('data' => date("Y-m-d"))) ; 
	} 
	if ($app['credentials']['current_role']!="Administrator"){
		  $field_default_ro =array_merge($field_default_ro,array('read_only' => true )) ; 
	} 
	$form = $form->add('birth_date', 'text', 
 		  $field_default_ro);
	$field_default_ro=array('required' => true,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('birth_place', 'text', array_merge(array('required' => true),$field_default_ro));
	$field_default_ro=array('required' => true,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('address', 'textarea', array_merge(array('required' => true),$field_default_ro));
	$field_default_ro=array('required' => true,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('prodi_1', 'text', array_merge(array('required' => true),$field_default_ro));
	$field_default_ro=array('required' => true,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('prodi_2', 'text', array_merge(array('required' => true),$field_default_ro));
	$field_default_ro=array('required' => true,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('high_school', 'text', array_merge(array('required' => true),$field_default_ro));
	$field_default_ro=array('required' => false,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('email_2', 'text', array_merge(array('required' => false),$field_default_ro));
	$field_default_ro=array('required' => false,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('phone_2', 'text', array_merge(array('required' => false),$field_default_ro));
	$field_default_ro=array('required' => false,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('path_documents', 'text', array_merge(array('required' => false),$field_default_ro));
	$form = $form->add('path_documents_UPLOAD', 'file', array_merge(array('required' => false),$field_default_ro));
	$field_default_ro=array('required' => false,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('path_picture', 'text', array_merge(array('required' => false),$field_default_ro));
	$form = $form->add('path_picture_UPLOAD', 'file', array_merge(array('required' => false),$field_default_ro));


    $form = $form->getForm();

    if("POST" == $app['request']->getMethod()){

        $form->handleRequest($app["request"]);

        if ($form->isValid()) {
            $data = $form->getData();
			
            $update_query = "INSERT INTO `applicant` (`user_login_id`, `birth_date`, `birth_place`, `address`, `prodi_1`, `prodi_2`, `high_school`, `email_2`, `phone_2`, `path_documents`, `path_picture`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $app['db']->executeUpdate($update_query, array($data['user_login_id'], $data['birth_date'], $data['birth_place'], $data['address'], $data['prodi_1'], $data['prodi_2'], $data['high_school'], $data['email_2'], $data['phone_2'], $data['path_documents'], $data['path_picture']));            


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

    $find_sql = "SELECT * FROM `applicant` WHERE `applicant_id` = ?";
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
		'user_login_id' => $row_sql['user_login_id'], 
		'birth_date' => $row_sql['birth_date'], 
		'birth_place' => $row_sql['birth_place'], 
		'address' => $row_sql['address'], 
		'prodi_1' => $row_sql['prodi_1'], 
		'prodi_2' => $row_sql['prodi_2'], 
		'high_school' => $row_sql['high_school'], 
		'email_2' => $row_sql['email_2'], 
		'phone_2' => $row_sql['phone_2'], 
		'path_documents' => $row_sql['path_documents'], 
		'path_picture' => $row_sql['path_picture'], 

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
	$findexternal_sql = 'SELECT user_login.user_login_id, user_login.login FROM user_login  '  . $limiter ;
	$findexternal_rows = $app['db']->fetchAll($findexternal_sql, array());
	foreach($findexternal_rows as $findexternal_row){
	    $options[$findexternal_row['user_login_id']] = $findexternal_row['login'];
	}
	if(count($options) > 0){
	    $form = $form->add('user_login_id', 'choice', array_merge($field_default_ro,array(
	        'required' => $field_nullable,
	        'choices' => $options,
	        'expanded' => false,
	        'constraints' => new Assert\Choice(array_keys($options))
	    )));
	}
	else{
	    $form = $form->add('user_login_id', 'text', array_merge(array('required' => true),$field_default_ro));
	}


	$field_default_ro=array('required' => true,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$field_default_ro=array('required' => true,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	if (strpos($app['request']->getRequestUri(),"create")!==false){
		  $field_default_ro =array_merge($field_default_ro,array('data' => date("Y-m-d"))) ; 
	} 
	if ($app['credentials']['current_role']!="Administrator"){
		  $field_default_ro =array_merge($field_default_ro,array('read_only' => true )) ; 
	} 
	$form = $form->add('birth_date', 'text', 
 		  $field_default_ro);
	$field_default_ro=array('required' => true,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('birth_place', 'text', array_merge(array('required' => true),$field_default_ro));
	$field_default_ro=array('required' => true,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('address', 'textarea', array_merge(array('required' => true),$field_default_ro));
	$field_default_ro=array('required' => true,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('prodi_1', 'text', array_merge(array('required' => true),$field_default_ro));
	$field_default_ro=array('required' => true,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('prodi_2', 'text', array_merge(array('required' => true),$field_default_ro));
	$field_default_ro=array('required' => true,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('high_school', 'text', array_merge(array('required' => true),$field_default_ro));
	$field_default_ro=array('required' => false,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('email_2', 'text', array_merge(array('required' => false),$field_default_ro));
	$field_default_ro=array('required' => false,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('phone_2', 'text', array_merge(array('required' => false),$field_default_ro));
	$field_default_ro=array('required' => false,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('path_documents', 'text', array_merge(array('required' => false),$field_default_ro));
	$form = $form->add('path_documents_UPLOAD', 'file', array_merge(array('required' => false),$field_default_ro));
	$field_default_ro=array('required' => false,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('path_picture', 'text', array_merge(array('required' => false),$field_default_ro));
	$form = $form->add('path_picture_UPLOAD', 'file', array_merge(array('required' => false),$field_default_ro));


    $form = $form->getForm();

    if("POST" == $app['request']->getMethod()){

        $form->handleRequest($app["request"]);

        if ($form->isValid()) {
            $data = $form->getData();
			

            $update_query = "UPDATE `applicant` SET `user_login_id` = ?, `birth_date` = ?, `birth_place` = ?, `address` = ?, `prodi_1` = ?, `prodi_2` = ?, `high_school` = ?, `email_2` = ?, `phone_2` = ?, `path_documents` = ?, `path_picture` = ? WHERE `applicant_id` = ?";
            $app['db']->executeUpdate($update_query, array($data['user_login_id'], $data['birth_date'], $data['birth_place'], $data['address'], $data['prodi_1'], $data['prodi_2'], $data['high_school'], $data['email_2'], $data['phone_2'], $data['path_documents'], $data['path_picture'], $id));            

	
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

    $find_sql = "SELECT * FROM `applicant` WHERE `applicant_id` = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($id));

    if($row_sql){
        $delete_query = "DELETE FROM `applicant` WHERE `applicant_id` = ?";
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






