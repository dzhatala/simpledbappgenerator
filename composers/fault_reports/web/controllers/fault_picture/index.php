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

$app->match('/fault_picture/list', function (Symfony\Component\HttpFoundation\Request $request) use ($app) {  
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
		'fault_picture_id', 
		'lamp_fault_report_id', 
		'path_picture', 
		'gps_info_exist', 
		'gps_info', 

    );
    
    $table_columns_type = array(
		'int(11)', 
		'int(11)', 
		'varchar(1024)', 
		'tinyint(1)', 
		'varchar(1024)', 

    );    
    
	
    $whereClause = "";
    $transform_text_to_key=""; /**  ... to enable search for externals ...**/
	
	/** find externals fields for search key and passing it to like as and id **/
	
	/**Creating enabler .. for  fault_picture:lamp_fault_report_id  **/

	if ($searchValue!==""){
	    $search_sql = "SELECT `lamp_fault_report_id` FROM `lamp_fault_report` WHERE `email` LIKE '%". $searchValue . "%'" ; 
	    $search_rows = array(); $search_rows = $app['db']->fetchAll($search_sql);
	    //error_log("#####lamp_fault_report_id=>lamp_fault_report######".count($search_row));
	    if(count($search_rows)>0){
	      foreach($search_rows as $search_row)  { 
	         $transform_text_to_key .= " OR  lamp_fault_report_id=".$search_row['lamp_fault_report_id']; 
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
		$whereClause =  $whereClause . "   fault_picture.".$col . " LIKE '%". $searchValue ."%' ".$transform_text_to_key;
        
		//non external search version ...
		//$whereClause =  $whereClause . "   fault_picture.".$col . " LIKE '%". $searchValue ."%' ";
        
        $i = $i + 1;
    }
	$whereClause .= " ) ";
    
	
    
	
	
	
    $recordsTotal = $app['db']->executeQuery("SELECT * FROM `fault_picture`" . $whereClause . $orderClause)->rowCount();
    
    $find_sql = "SELECT * FROM `fault_picture`". $whereClause . $orderClause . " LIMIT ". $index . "," . $rowsPerPage;
    $rows_sql = $app['db']->fetchAll($find_sql, array());

    foreach($rows_sql as $row_key => $row_sql){
        for($i = 0; $i < count($table_columns); $i++){

			if($table_columns[$i] == 'lamp_fault_report_id'){
			    $findexternal_sql = 'SELECT `email` FROM `lamp_fault_report` WHERE `lamp_fault_report_id` = ?';
			    $findexternal_row = $app['db']->fetchAssoc($findexternal_sql, array($row_sql[$table_columns[$i]]));
			    $rows[$row_key][$table_columns[$i]] = $findexternal_row['email'];
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
$app->match('/fault_picture/download', function (Symfony\Component\HttpFoundation\Request $request) use ($app) { 
    
    // menu
    $rowid = $request->get('id');
    $idfldname = $request->get('idfld');
    $fieldname = $request->get('fldname');
    
    if( !$rowid || !$fieldname ) die("Invalid data");
    
    $find_sql = "SELECT " . $fieldname . " FROM " . fault_picture . " WHERE ".$idfldname." = ?";
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



$app->match('/fault_picture', function () use ($app) {
    
	$table_columns = array(
		'fault_picture_id', 
		'lamp_fault_report_id', 
		'path_picture', 
		'gps_info_exist', 
		'gps_info', 

    );
	
	/**translating here ...**/
	$tr_table_columns=array();
	foreach ($table_columns as $col){
		//var_dump($col) ;die;
		//array_push($tr_table_columns,$app['translator']->trans("fault_picture".$col));
		array_push($tr_table_columns,$col);
	}
	$table_columns=$tr_table_columns;
	/****/

    $primary_key = "fault_picture_id";	

    return $app['twig']->render('fault_picture/list.html.twig', array(
    	"table_columns" => $table_columns,
        "primary_key" => $primary_key
    ));
        
})
->bind('fault_picture_list');



$app->match('/fault_picture/create', function () use ($app) {
    
    $initial_data = array(
		'lamp_fault_report_id' => '', 
		'path_picture' => '', 
		'gps_info_exist' => '', 
		'gps_info' => '', 

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
	$findexternal_sql = 'SELECT lamp_fault_report.lamp_fault_report_id, lamp_fault_report.email FROM lamp_fault_report  '  . $limiter ;
	$findexternal_rows = $app['db']->fetchAll($findexternal_sql, array());
	foreach($findexternal_rows as $findexternal_row){
	    $options[$findexternal_row['lamp_fault_report_id']] = $findexternal_row['email'];
	}
	if(count($options) > 0){
	    $form = $form->add('lamp_fault_report_id', 'choice', array_merge($field_default_ro,array(
	        'required' => $field_nullable,
	        'choices' => $options,
	        'expanded' => false,
	        'constraints' => new Assert\Choice(array_keys($options))
	    )));
	}
	else{
	    $form = $form->add('lamp_fault_report_id', 'text', array_merge(array('required' => true),$field_default_ro));
	}



	$field_default_ro=array('required' => true,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$field_default_ro=array('required' => true,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('path_picture', 'text', array_merge(array('required' => true),$field_default_ro));
	$form = $form->add('path_picture_UPLOAD', 'file', array_merge(array('required' => true),$field_default_ro));
	$field_default_ro=array('required' => true,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('gps_info_exist', 'text', array_merge(array('required' => true),$field_default_ro));
	$field_default_ro=array('required' => false,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('gps_info', 'text', array_merge(array('required' => false),$field_default_ro));


    $form = $form->getForm();

    if("POST" == $app['request']->getMethod()){

        $form->handleRequest($app["request"]);

        if ($form->isValid()) {
            $data = $form->getData();
			
			if($data['path_picture_UPLOAD']){
				$forig=$app['credentials']['login']."__".date("Y_m_d_h_m_s__").$data['path_picture_UPLOAD']->getClientOriginalName();
				$data['path_picture_UPLOAD']->move($app['uploaded_dir']."/".$app['credentials']['login']."/fault_picture",$forig);
				$data['path_picture']=$forig ; 
			}
            $update_query = "INSERT INTO `fault_picture` (`lamp_fault_report_id`, `path_picture`, `gps_info_exist`, `gps_info`) VALUES (?, ?, ?, ?)";
            $app['db']->executeUpdate($update_query, array($data['lamp_fault_report_id'], $data['path_picture'], $data['gps_info_exist'], $data['gps_info']));            


            $app['session']->getFlashBag()->add(
                'success',
                array(
                    'message' => 'fault_picture created!',
                )
            );
            return $app->redirect($app['url_generator']->generate('fault_picture_list'));

        }
    }

    return $app['twig']->render('fault_picture/create.html.twig', array(
        "form" => $form->createView()
    ));
        
})
->bind('fault_picture_create');



$app->match('/fault_picture/edit/{id}', function ($id) use ($app) {

    $find_sql = "SELECT * FROM `fault_picture` WHERE `fault_picture_id` = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($id));

    if(!$row_sql){
        $app['session']->getFlashBag()->add(
            'danger',
            array(
                'message' => 'Row not found!',
            )
        );        
        return $app->redirect($app['url_generator']->generate('fault_picture_list'));
    }

    
    $initial_data = array(
		'lamp_fault_report_id' => $row_sql['lamp_fault_report_id'], 
		'path_picture' => $row_sql['path_picture'], 
		'gps_info_exist' => $row_sql['gps_info_exist'], 
		'gps_info' => $row_sql['gps_info'], 

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
	$findexternal_sql = 'SELECT lamp_fault_report.lamp_fault_report_id, lamp_fault_report.email FROM lamp_fault_report  '  . $limiter ;
	$findexternal_rows = $app['db']->fetchAll($findexternal_sql, array());
	foreach($findexternal_rows as $findexternal_row){
	    $options[$findexternal_row['lamp_fault_report_id']] = $findexternal_row['email'];
	}
	if(count($options) > 0){
	    $form = $form->add('lamp_fault_report_id', 'choice', array_merge($field_default_ro,array(
	        'required' => $field_nullable,
	        'choices' => $options,
	        'expanded' => false,
	        'constraints' => new Assert\Choice(array_keys($options))
	    )));
	}
	else{
	    $form = $form->add('lamp_fault_report_id', 'text', array_merge(array('required' => true),$field_default_ro));
	}


	$field_default_ro=array('required' => true,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$field_default_ro=array('required' => true,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('path_picture', 'text', array_merge(array('required' => true),$field_default_ro));
	$form = $form->add('path_picture_UPLOAD', 'file', array_merge(array('required' => true),$field_default_ro));
	$field_default_ro=array('required' => true,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('gps_info_exist', 'text', array_merge(array('required' => true),$field_default_ro));
	$field_default_ro=array('required' => false,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('gps_info', 'text', array_merge(array('required' => false),$field_default_ro));


    $form = $form->getForm();

    if("POST" == $app['request']->getMethod()){

        $form->handleRequest($app["request"]);

        if ($form->isValid()) {
            $data = $form->getData();
			
			if($data['path_picture_UPLOAD']){
				$forig=$app['credentials']['login']."__".date("Y_m_d_h_m_s__").$data['path_picture_UPLOAD']->getClientOriginalName();
				$data['path_picture_UPLOAD']->move($app['uploaded_dir']."/".$app['credentials']['login']."/fault_picture",$forig);
				$data['path_picture']=$forig ; 
			}

            $update_query = "UPDATE `fault_picture` SET `lamp_fault_report_id` = ?, `path_picture` = ?, `gps_info_exist` = ?, `gps_info` = ? WHERE `fault_picture_id` = ?";
            $app['db']->executeUpdate($update_query, array($data['lamp_fault_report_id'], $data['path_picture'], $data['gps_info_exist'], $data['gps_info'], $id));            

	
            $app['session']->getFlashBag()->add(
                'success',
                array(
                    'message' => 'fault_picture edited!',
                )
            );
            return $app->redirect($app['url_generator']->generate('fault_picture_edit', array("id" => $id)));

        }
    }

    return $app['twig']->render('fault_picture/edit.html.twig', array(
        "form" => $form->createView(),
        "id" => $id
    ));
        
})
->bind('fault_picture_edit');



$app->match('/fault_picture/delete/{id}', function ($id) use ($app) {

    $find_sql = "SELECT * FROM `fault_picture` WHERE `fault_picture_id` = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($id));

    if($row_sql){
        $delete_query = "DELETE FROM `fault_picture` WHERE `fault_picture_id` = ?";
        $app['db']->executeUpdate($delete_query, array($id));

        $app['session']->getFlashBag()->add(
            'success',
            array(
                'message' => 'fault_picture deleted!',
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

    return $app->redirect($app['url_generator']->generate('fault_picture_list'));

})
->bind('fault_picture_delete');






