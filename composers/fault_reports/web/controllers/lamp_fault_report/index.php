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

$app->match('/lamp_fault_report/list', function (Symfony\Component\HttpFoundation\Request $request) use ($app) {  
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
		'lamp_fault_report_id', 
		'email', 
		'fault_street_address', 
		'google_map_address', 
		'fault_detail', 
		'report_date', 
		'follow_up', 

    );
    
    $table_columns_type = array(
		'int(11)', 
		'varchar(1024)', 
		'text', 
		'varchar(1024)', 
		'text', 
		'datetime', 
		'text', 

    );    
    
	
    $whereClause = "";
    $transform_text_to_key=""; /**  ... to enable search for externals ...**/
	
	/** find externals fields for search key and passing it to like as and id **/
	

    $i = 0;
    foreach($table_columns as $col){
        
        if ($i == 0) {
           $whereClause = " WHERE ( 1 AND ";
        }
        
        if ($i > 0) {
            $whereClause =  $whereClause . " OR"; 
        }
        
        //external search  version
		$whereClause =  $whereClause . "   lamp_fault_report.".$col . " LIKE '%". $searchValue ."%' ".$transform_text_to_key;
        
		//non external search version ...
		//$whereClause =  $whereClause . "   lamp_fault_report.".$col . " LIKE '%". $searchValue ."%' ";
        
        $i = $i + 1;
    }
	$whereClause .= " ) ";
    
	
    
	
	
	
    $recordsTotal = $app['db']->executeQuery("SELECT * FROM `lamp_fault_report`" . $whereClause . $orderClause)->rowCount();
    
    $find_sql = "SELECT * FROM `lamp_fault_report`". $whereClause . $orderClause . " LIMIT ". $index . "," . $rowsPerPage;
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




/* Download blob img */
$app->match('/lamp_fault_report/download', function (Symfony\Component\HttpFoundation\Request $request) use ($app) { 
    
    // menu
    $rowid = $request->get('id');
    $idfldname = $request->get('idfld');
    $fieldname = $request->get('fldname');
    
    if( !$rowid || !$fieldname ) die("Invalid data");
    
    $find_sql = "SELECT " . $fieldname . " FROM " . lamp_fault_report . " WHERE ".$idfldname." = ?";
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



$app->match('/lamp_fault_report', function () use ($app) {
    
	$table_columns = array(
		'lamp_fault_report_id', 
		'email', 
		'fault_street_address', 
		'google_map_address', 
		'fault_detail', 
		'report_date', 
		'follow_up', 

    );
	
	/**translating here ...**/
	$tr_table_columns=array();
	foreach ($table_columns as $col){
		//var_dump($col) ;die;
		//array_push($tr_table_columns,$app['translator']->trans("lamp_fault_report".$col));
		array_push($tr_table_columns,$col);
	}
	$table_columns=$tr_table_columns;
	/****/

    $primary_key = "lamp_fault_report_id";	

    return $app['twig']->render('lamp_fault_report/list.html.twig', array(
    	"table_columns" => $table_columns,
        "primary_key" => $primary_key
    ));
        
})
->bind('lamp_fault_report_list');



$app->match('/lamp_fault_report/create', function () use ($app) {
    
    $initial_data = array(
		'email' => '', 
		'fault_street_address' => '', 
		'google_map_address' => '', 
		'fault_detail' => '', 
		'report_date' => '', 
		'follow_up' => '', 

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
	$form = $form->add('email', 'text', array_merge(array('required' => true),$field_default_ro));
	$field_default_ro=array('required' => true,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('fault_street_address', 'textarea', array_merge(array('required' => true),$field_default_ro));
	$field_default_ro=array('required' => false,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('google_map_address', 'text', array_merge(array('required' => false),$field_default_ro));
	$field_default_ro=array('required' => true,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('fault_detail', 'textarea', array_merge(array('required' => true),$field_default_ro));
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
	$form = $form->add('report_date', 'text', 
 		  $field_default_ro);
	$field_default_ro=array('required' => false,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('follow_up', 'textarea', array_merge(array('required' => false),$field_default_ro));


    $form = $form->getForm();

    if("POST" == $app['request']->getMethod()){

        $form->handleRequest($app["request"]);

        if ($form->isValid()) {
            $data = $form->getData();
			
            $update_query = "INSERT INTO `lamp_fault_report` (`email`, `fault_street_address`, `google_map_address`, `fault_detail`, `report_date`, `follow_up`) VALUES (?, ?, ?, ?, ?, ?)";
            $app['db']->executeUpdate($update_query, array($data['email'], $data['fault_street_address'], $data['google_map_address'], $data['fault_detail'], $data['report_date'], $data['follow_up']));            


            $app['session']->getFlashBag()->add(
                'success',
                array(
                    'message' => 'lamp_fault_report created!',
                )
            );
            return $app->redirect($app['url_generator']->generate('lamp_fault_report_list'));

        }
    }

    return $app['twig']->render('lamp_fault_report/create.html.twig', array(
        "form" => $form->createView()
    ));
        
})
->bind('lamp_fault_report_create');



$app->match('/lamp_fault_report/edit/{id}', function ($id) use ($app) {

    $find_sql = "SELECT * FROM `lamp_fault_report` WHERE `lamp_fault_report_id` = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($id));

    if(!$row_sql){
        $app['session']->getFlashBag()->add(
            'danger',
            array(
                'message' => 'Row not found!',
            )
        );        
        return $app->redirect($app['url_generator']->generate('lamp_fault_report_list'));
    }

    
    $initial_data = array(
		'email' => $row_sql['email'], 
		'fault_street_address' => $row_sql['fault_street_address'], 
		'google_map_address' => $row_sql['google_map_address'], 
		'fault_detail' => $row_sql['fault_detail'], 
		'report_date' => $row_sql['report_date'], 
		'follow_up' => $row_sql['follow_up'], 

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
	$form = $form->add('email', 'text', array_merge(array('required' => true),$field_default_ro));
	$field_default_ro=array('required' => true,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('fault_street_address', 'textarea', array_merge(array('required' => true),$field_default_ro));
	$field_default_ro=array('required' => false,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('google_map_address', 'text', array_merge(array('required' => false),$field_default_ro));
	$field_default_ro=array('required' => true,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('fault_detail', 'textarea', array_merge(array('required' => true),$field_default_ro));
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
	$form = $form->add('report_date', 'text', 
 		  $field_default_ro);
	$field_default_ro=array('required' => false,'disabled' =>true)  ; 
	if($app['credentials']['current_role']=="Administrator"){
	unset($field_default_ro['disabled']);
	}
	$form = $form->add('follow_up', 'textarea', array_merge(array('required' => false),$field_default_ro));


    $form = $form->getForm();

    if("POST" == $app['request']->getMethod()){

        $form->handleRequest($app["request"]);

        if ($form->isValid()) {
            $data = $form->getData();
			

            $update_query = "UPDATE `lamp_fault_report` SET `email` = ?, `fault_street_address` = ?, `google_map_address` = ?, `fault_detail` = ?, `report_date` = ?, `follow_up` = ? WHERE `lamp_fault_report_id` = ?";
            $app['db']->executeUpdate($update_query, array($data['email'], $data['fault_street_address'], $data['google_map_address'], $data['fault_detail'], $data['report_date'], $data['follow_up'], $id));            

	
            $app['session']->getFlashBag()->add(
                'success',
                array(
                    'message' => 'lamp_fault_report edited!',
                )
            );
            return $app->redirect($app['url_generator']->generate('lamp_fault_report_edit', array("id" => $id)));

        }
    }

    return $app['twig']->render('lamp_fault_report/edit.html.twig', array(
        "form" => $form->createView(),
        "id" => $id
    ));
        
})
->bind('lamp_fault_report_edit');



$app->match('/lamp_fault_report/delete/{id}', function ($id) use ($app) {

    $find_sql = "SELECT * FROM `lamp_fault_report` WHERE `lamp_fault_report_id` = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($id));

    if($row_sql){
        $delete_query = "DELETE FROM `lamp_fault_report` WHERE `lamp_fault_report_id` = ?";
        $app['db']->executeUpdate($delete_query, array($id));

        $app['session']->getFlashBag()->add(
            'success',
            array(
                'message' => 'lamp_fault_report deleted!',
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

    return $app->redirect($app['url_generator']->generate('lamp_fault_report_list'));

})
->bind('lamp_fault_report_delete');






