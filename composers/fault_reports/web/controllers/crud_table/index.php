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

$app->match('/crud_table/list', function (Symfony\Component\HttpFoundation\Request $request) use ($app) {  
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
		'crud_table_id', 
		'name', 

    );
    
    $table_columns_type = array(
		'int(11)', 
		'varchar(1024)', 

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
		$whereClause =  $whereClause . "   crud_table.".$col . " LIKE '%". $searchValue ."%' ".$transform_text_to_key;
        
		//non external search version ...
		//$whereClause =  $whereClause . "   crud_table.".$col . " LIKE '%". $searchValue ."%' ";
        
        $i = $i + 1;
    }
	$whereClause .= " ) ";
    
	
    
	
	
	
    $recordsTotal = $app['db']->executeQuery("SELECT * FROM `crud_table`" . $whereClause . $orderClause)->rowCount();
    
    $find_sql = "SELECT * FROM `crud_table`". $whereClause . $orderClause . " LIMIT ". $index . "," . $rowsPerPage;
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
$app->match('/crud_table/download', function (Symfony\Component\HttpFoundation\Request $request) use ($app) { 
    
    // menu
    $rowid = $request->get('id');
    $idfldname = $request->get('idfld');
    $fieldname = $request->get('fldname');
    
    if( !$rowid || !$fieldname ) die("Invalid data");
    
    $find_sql = "SELECT " . $fieldname . " FROM " . crud_table . " WHERE ".$idfldname." = ?";
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



$app->match('/crud_table', function () use ($app) {
    
	$table_columns = array(
		'crud_table_id', 
		'name', 

    );
	
	/**translating here ...**/
	$tr_table_columns=array();
	foreach ($table_columns as $col){
		//var_dump($col) ;die;
		//array_push($tr_table_columns,$app['translator']->trans("crud_table".$col));
		array_push($tr_table_columns,$col);
	}
	$table_columns=$tr_table_columns;
	/****/

    $primary_key = "crud_table_id";	

    return $app['twig']->render('crud_table/list.html.twig', array(
    	"table_columns" => $table_columns,
        "primary_key" => $primary_key
    ));
        
})
->bind('crud_table_list');



$app->match('/crud_table/create', function () use ($app) {
    
    $initial_data = array(
		'name' => '', 

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
	$form = $form->add('name', 'text', array_merge(array('required' => true),$field_default_ro));


    $form = $form->getForm();

    if("POST" == $app['request']->getMethod()){

        $form->handleRequest($app["request"]);

        if ($form->isValid()) {
            $data = $form->getData();
			
            $update_query = "INSERT INTO `crud_table` (`name`) VALUES (?)";
            $app['db']->executeUpdate($update_query, array($data['name']));            


            $app['session']->getFlashBag()->add(
                'success',
                array(
                    'message' => 'crud_table created!',
                )
            );
            return $app->redirect($app['url_generator']->generate('crud_table_list'));

        }
    }

    return $app['twig']->render('crud_table/create.html.twig', array(
        "form" => $form->createView()
    ));
        
})
->bind('crud_table_create');



$app->match('/crud_table/edit/{id}', function ($id) use ($app) {

    $find_sql = "SELECT * FROM `crud_table` WHERE `crud_table_id` = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($id));

    if(!$row_sql){
        $app['session']->getFlashBag()->add(
            'danger',
            array(
                'message' => 'Row not found!',
            )
        );        
        return $app->redirect($app['url_generator']->generate('crud_table_list'));
    }

    
    $initial_data = array(
		'name' => $row_sql['name'], 

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
	$form = $form->add('name', 'text', array_merge(array('required' => true),$field_default_ro));


    $form = $form->getForm();

    if("POST" == $app['request']->getMethod()){

        $form->handleRequest($app["request"]);

        if ($form->isValid()) {
            $data = $form->getData();
			

            $update_query = "UPDATE `crud_table` SET `name` = ? WHERE `crud_table_id` = ?";
            $app['db']->executeUpdate($update_query, array($data['name'], $id));            

	
            $app['session']->getFlashBag()->add(
                'success',
                array(
                    'message' => 'crud_table edited!',
                )
            );
            return $app->redirect($app['url_generator']->generate('crud_table_edit', array("id" => $id)));

        }
    }

    return $app['twig']->render('crud_table/edit.html.twig', array(
        "form" => $form->createView(),
        "id" => $id
    ));
        
})
->bind('crud_table_edit');



$app->match('/crud_table/delete/{id}', function ($id) use ($app) {

    $find_sql = "SELECT * FROM `crud_table` WHERE `crud_table_id` = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($id));

    if($row_sql){
        $delete_query = "DELETE FROM `crud_table` WHERE `crud_table_id` = ?";
        $app['db']->executeUpdate($delete_query, array($id));

        $app['session']->getFlashBag()->add(
            'success',
            array(
                'message' => 'crud_table deleted!',
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

    return $app->redirect($app['url_generator']->generate('crud_table_list'));

})
->bind('crud_table_delete');






