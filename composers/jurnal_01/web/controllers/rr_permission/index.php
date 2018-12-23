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

$app->match('/rr_permission/list', function (Symfony\Component\HttpFoundation\Request $request) use ($app) {  
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
		'rr_permission_id', 
		'crud_table_id', 
		'user_login_id', 
		'record_id', 

    );
    
    $table_columns_type = array(
		'int(11)', 
		'int(11)', 
		'int(11)', 
		'int(11)', 

    );    
    
	
    $whereClause = "";
    $transform_text_to_key=""; /**  ... to enable search for externals ...**/
	
	/** find externals fields for search key and passing it to like as and id **/
	
	/**Creating enabler .. for  rr_permission:crud_table_id  **/

	if ($searchValue!==""){
	    $search_sql = "SELECT `crud_table_id` FROM `crud_table` WHERE `name` LIKE '%". $searchValue . "%'" ; 
	    $search_rows = array(); $search_rows = $app['db']->fetchAll($search_sql);
	    //error_log("#####crud_table_id=>crud_table######".count($search_row));
	    if(count($search_rows)>0){
	      foreach($search_rows as $search_row)  { 
	         $transform_text_to_key .= " OR  crud_table_id=".$search_row['crud_table_id']; 
	      } 
	    }
	 } 

	/**Creating enabler .. for  rr_permission:user_login_id  **/

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
		$whereClause =  $whereClause . "   rr_permission.".$col . " LIKE '%". $searchValue ."%' ".$transform_text_to_key;
        
		//non external search version ...
		//$whereClause =  $whereClause . "   rr_permission.".$col . " LIKE '%". $searchValue ."%' ";
        
        $i = $i + 1;
    }
	$whereClause .= " ) ";
    
	
    
	
	
	
    $recordsTotal = $app['db']->executeQuery("SELECT * FROM `rr_permission`" . $whereClause . $orderClause)->rowCount();
    
    $find_sql = "SELECT * FROM `rr_permission`". $whereClause . $orderClause . " LIMIT ". $index . "," . $rowsPerPage;
    $rows_sql = $app['db']->fetchAll($find_sql, array());

    foreach($rows_sql as $row_key => $row_sql){
        for($i = 0; $i < count($table_columns); $i++){

			if($table_columns[$i] == 'crud_table_id'){
			    $findexternal_sql = 'SELECT `name` FROM `crud_table` WHERE `crud_table_id` = ?';
			    $findexternal_row = $app['db']->fetchAssoc($findexternal_sql, array($row_sql[$table_columns[$i]]));
			    $rows[$row_key][$table_columns[$i]] = $findexternal_row['name'];
			}
			else if($table_columns[$i] == 'user_login_id'){
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
$app->match('/rr_permission/download', function (Symfony\Component\HttpFoundation\Request $request) use ($app) { 
    
    // menu
    $rowid = $request->get('id');
    $idfldname = $request->get('idfld');
    $fieldname = $request->get('fldname');
    
    if( !$rowid || !$fieldname ) die("Invalid data");
    
    $find_sql = "SELECT " . $fieldname . " FROM " . rr_permission . " WHERE ".$idfldname." = ?";
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



$app->match('/rr_permission', function () use ($app) {
    
	$table_columns = array(
		'rr_permission_id', 
		'crud_table_id', 
		'user_login_id', 
		'record_id', 

    );
	
	/**translating here ...**/
	$tr_table_columns=array();
	foreach ($table_columns as $col){
		//var_dump($col) ;die;
		//array_push($tr_table_columns,$app['translator']->trans("rr_permission".$col));
		array_push($tr_table_columns,$col);
	}
	$table_columns=$tr_table_columns;
	/****/

    $primary_key = "rr_permission_id";	

    return $app['twig']->render('rr_permission/list.html.twig', array(
    	"table_columns" => $table_columns,
        "primary_key" => $primary_key
    ));
        
})
->bind('rr_permission_list');



$app->match('/rr_permission/create', function () use ($app) {
    
    $initial_data = array(
		'crud_table_id' => '', 
		'user_login_id' => '', 
		'record_id' => '', 

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
	$findexternal_sql = 'SELECT crud_table.crud_table_id, crud_table.name FROM crud_table  '  . $limiter ;
	$findexternal_rows = $app['db']->fetchAll($findexternal_sql, array());
	foreach($findexternal_rows as $findexternal_row){
	    $options[$findexternal_row['crud_table_id']] = $findexternal_row['name'];
	}
	if(count($options) > 0){
	    $form = $form->add('crud_table_id', 'choice', array_merge($field_default_ro,array(
	        'required' => $field_nullable,
	        'choices' => $options,
	        'expanded' => false,
	        'constraints' => new Assert\Choice(array_keys($options))
	    )));
	}
	else{
	    $form = $form->add('crud_table_id', 'text', array_merge(array('required' => true),$field_default_ro));
	}

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
	$form = $form->add('record_id', 'text', array_merge(array('required' => true),$field_default_ro));


    $form = $form->getForm();

    if("POST" == $app['request']->getMethod()){

        $form->handleRequest($app["request"]);

        if ($form->isValid()) {
            $data = $form->getData();
			
            $update_query = "INSERT INTO `rr_permission` (`crud_table_id`, `user_login_id`, `record_id`) VALUES (?, ?, ?)";
            $app['db']->executeUpdate($update_query, array($data['crud_table_id'], $data['user_login_id'], $data['record_id']));            


            $app['session']->getFlashBag()->add(
                'success',
                array(
                    'message' => 'rr_permission created!',
                )
            );
            return $app->redirect($app['url_generator']->generate('rr_permission_list'));

        }
    }

    return $app['twig']->render('rr_permission/create.html.twig', array(
        "form" => $form->createView()
    ));
        
})
->bind('rr_permission_create');



$app->match('/rr_permission/edit/{id}', function ($id) use ($app) {

    $find_sql = "SELECT * FROM `rr_permission` WHERE `rr_permission_id` = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($id));

    if(!$row_sql){
        $app['session']->getFlashBag()->add(
            'danger',
            array(
                'message' => 'Row not found!',
            )
        );        
        return $app->redirect($app['url_generator']->generate('rr_permission_list'));
    }

    
    $initial_data = array(
		'crud_table_id' => $row_sql['crud_table_id'], 
		'user_login_id' => $row_sql['user_login_id'], 
		'record_id' => $row_sql['record_id'], 

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
	$findexternal_sql = 'SELECT crud_table.crud_table_id, crud_table.name FROM crud_table  '  . $limiter ;
	$findexternal_rows = $app['db']->fetchAll($findexternal_sql, array());
	foreach($findexternal_rows as $findexternal_row){
	    $options[$findexternal_row['crud_table_id']] = $findexternal_row['name'];
	}
	if(count($options) > 0){
	    $form = $form->add('crud_table_id', 'choice', array_merge($field_default_ro,array(
	        'required' => $field_nullable,
	        'choices' => $options,
	        'expanded' => false,
	        'constraints' => new Assert\Choice(array_keys($options))
	    )));
	}
	else{
	    $form = $form->add('crud_table_id', 'text', array_merge(array('required' => true),$field_default_ro));
	}

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
	$form = $form->add('record_id', 'text', array_merge(array('required' => true),$field_default_ro));


    $form = $form->getForm();

    if("POST" == $app['request']->getMethod()){

        $form->handleRequest($app["request"]);

        if ($form->isValid()) {
            $data = $form->getData();
			

            $update_query = "UPDATE `rr_permission` SET `crud_table_id` = ?, `user_login_id` = ?, `record_id` = ? WHERE `rr_permission_id` = ?";
            $app['db']->executeUpdate($update_query, array($data['crud_table_id'], $data['user_login_id'], $data['record_id'], $id));            

	
            $app['session']->getFlashBag()->add(
                'success',
                array(
                    'message' => 'rr_permission edited!',
                )
            );
            return $app->redirect($app['url_generator']->generate('rr_permission_edit', array("id" => $id)));

        }
    }

    return $app['twig']->render('rr_permission/edit.html.twig', array(
        "form" => $form->createView(),
        "id" => $id
    ));
        
})
->bind('rr_permission_edit');



$app->match('/rr_permission/delete/{id}', function ($id) use ($app) {

    $find_sql = "SELECT * FROM `rr_permission` WHERE `rr_permission_id` = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($id));

    if($row_sql){
        $delete_query = "DELETE FROM `rr_permission` WHERE `rr_permission_id` = ?";
        $app['db']->executeUpdate($delete_query, array($id));

        $app['session']->getFlashBag()->add(
            'success',
            array(
                'message' => 'rr_permission deleted!',
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

    return $app->redirect($app['url_generator']->generate('rr_permission_list'));

})
->bind('rr_permission_delete');






