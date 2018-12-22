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
/** if not shown in chrome, shtudown /restart chrome **/

$app->match('/renavigated_students', function () use ($app) {
    
	$table_columns = array(
		'STUDENT_ID', 
		'ADMISSION_NO', 
		'FIRST_NAME', 
		'MIDDLE_NAME', 
		'LAST_NAME', 
		'ADDRESS', 
		'PHONE_NO', 
		'REGISTRATION_DATE', 

    );

    $primary_key = "STUDENT_ID";	

    return $app['twig']->render('renavigated/students_list.html.twig', array(
    	"table_columns" => $table_columns,
        "primary_key" => $primary_key
    ));
        
})
->bind('renavigated_students');



$app->match('/renavigated', function (Symfony\Component\HttpFoundation\Request $request) use ($app) {  
	
	
	//test ajax
	$testData = array(
		'MESSAGE' => 'your ajax is success ', 
	);
	
	// check wether this is ajax or not ....
	if ($request->isXmlHttpRequest()){
		return new Symfony\Component\HttpFoundation\Response(json_encode($testData), 200);
		//return new Symfony\Component\HttpFoundation\Response(json_encode($request), 200);

	}
	
	/** FORM STEP 0 ***/
	
	$initial_data = array(
		'CLASS_SECTION_ID' => '', 
		'STUDENT_ID' => '', 
		'STATUS' => '', 
		'ACADEMIC_CALENDAR_ID' =>'',

    );
	/** FORM STEP 1 ***/
    $form = $app['form.factory']->createBuilder('form', $initial_data);
	
	$options['1']="xyz"; 
	
	/** FORM STEP 2 ***/

	$form = $form->add('ACADEMIC_CALENDAR_ID', 'choice', array(
	        'required' => false, /* if false none is initially selected */
	        'choices' => $options,
	        'expanded' => false,
	        'constraints' => new Assert\Choice(array_keys($options))
	));

	
	
	/** FORM STEP 3 ***/
	$form = $form->getForm();
	
	return $app['twig']->render('renavigated/list.html.twig', array(
    	"table_columns" => "hello",
        "primary_key" => " world"
		, "h" => "hello world"
		, "form" => $form->createView() 	/** FORM STEP 4 ***/

    ));
});


/***
	this will be used by twig template with ajax call : 
*/
$app->match('/renavigated/csdata_selected/{cs_id}', function (Symfony\Component\HttpFoundation\Request $request, $cs_id) use ($app) {  
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
	/*	'STUDENT_ID', */
		'ADMISSION_NO', 
		'FIRST_NAME', 
		'MIDDLE_NAME', 
		'LAST_NAME', 
		'ADDRESS', 
		'PHONE_NO', 
		'REGISTRATION_DATE', 
		'STATUS',
    );
    
    $table_columns_type = array(
	/*	'int(11)', */
		'varchar(50)', 
		'varchar(100)', 
		'varchar(50)', 
		'varchar(50)', 
		'text', 
		'varchar(50)', 
		'datetime', 
		'varchar(30)', 

    );    
    
    $whereClause = "";
    
    $i = 0;
    foreach($table_columns as $col){
        
        if ($i == 0) {
           $whereClause = " WHERE (";
        }
        
        if ($i > 0) {
            $whereClause =  $whereClause . " OR"; 
        }
        
        $whereClause =  $whereClause . " " . $col . " LIKE '%". $searchValue ."%'";
        
        $i = $i + 1;
    }
    
	if ($i>1)$whereClause .= " ) ";
    
    $navClause = " AND  per_class_active_students.STUDENT_ID=students.STUDENT_ID ". 
				 " AND per_class_active_students.CLASS_SECTION_ID=". $cs_id  ; 
	//$recordsTotal = $app['db']->executeQuery("SELECT per_class_active_students.CLASS_SECTION_ID, per_class_active_students.STATUS ". 
		//" FROM per_class_active_students, `students`" . $whereClause . $navClause . $orderClause)->rowCount();
    //$navClause="";
    $f0sql = "SELECT students.*, per_class_active_students.CLASS_SECTION_ID, per_class_active_students.STATUS ".
				" FROM per_class_active_students, `students`". $whereClause .$navClause .$orderClause ; 
	$recordsTotal = $app['db']->executeQuery($f0sql)->rowCount();
	
	//echo $f0sql ; echo "<br>" ; 	echo $recordsTotal ; die;
	
	$find_sql = $f0sql.  " LIMIT ". $index . "," . $rowsPerPage;
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
	//return new Symfony\Component\HttpFoundation\Response(json_encode($find_sql), 200);
})
->value('cs_id','-1')
;

/* end csdata_selected */
$app->match('/renavigated/csdata_selectable/{cs_id}', function (Symfony\Component\HttpFoundation\Request $request,$cs_id) use ($app) {  
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
		//'STUDENT_ID', 
		'ADMISSION_NO', 
		'FIRST_NAME', 
		'MIDDLE_NAME', 
		'LAST_NAME', 
		'ADDRESS', 
		'PHONE_NO', 
		'REGISTRATION_DATE', 

    );
    
    $table_columns_type = array(
		//'int(11)', 
		'varchar(50)', 
		'varchar(100)', 
		'varchar(50)', 
		'varchar(50)', 
		'text', 
		'varchar(50)', 
		'datetime', 

    );    
    
    $whereClause = "";
    
    $i = 0;
    foreach($table_columns as $col){
        
        if ($i == 0) {
           $whereClause = " WHERE  (";
        }
        
        if ($i > 0) {
            $whereClause =  $whereClause . " OR"; 
        }
        
        $whereClause =  $whereClause . " " . $col . " LIKE '%". $searchValue ."%'";
        
        $i = $i + 1;
    }
    
	if ($i>1)$whereClause .= " ) ";
	
	/*@TODO SUBSTRACT QUERY, we substract using 'not in' from mysql */
	/*@todo it must be not in  batch , not just class section ..*/
	
    /*$navClause = " AND  students.STUDENT_ID not in (select per_class_active_students.STUDENT_ID ". 
					" FROM per_class_active_students  ".
				 " WHERE  per_class_active_students.CLASS_SECTION_ID=". $cs_id .")" .
				 " ";
	*/

	/**/
	$find_sql = "select class_sections.BATCH_ID  from class_sections where class_section_id=".$cs_id;
	//error_log ("find sql : ".$find_sql);
	$rows_id = $app['db']->fetchAll($find_sql,array());
	$b_id=-1;
	foreach($rows_id as $row){
		$b_id=$row['BATCH_ID']; 
	}

	$navClause = " AND  students.STUDENT_ID not in ( ". 
				"select per_class_active_students.STUDENT_ID ". /**not in must SINGLE columns **/
				" from per_class_active_students,class_sections,batches  ".
				" where 1 ".
				" AND per_class_active_students.class_section_id=class_sections.class_section_id ".
				" and class_sections.batch_id=batches.batch_id ".
				" and batches.batch_id=". $b_id .
				" ) " ;
				
	
	
	$f0sql = "SELECT students.* FROM  `students`". $whereClause .$navClause .$orderClause ; 
	$recordsTotal = $app['db']->executeQuery($f0sql)->rowCount();
	
	//echo $f0sql ; echo "<br>" ; 	echo $recordsTotal ; die; // for debugging 
	
	$find_sql = $f0sql.  " LIMIT ". $index . "," . $rowsPerPage;
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
})
->value('cs_id','-1')
;
/*end selectable */

$app->match('/nav_per_class_active_students', function () use ($app) {
	
	
	/** FORM STEP 0 ***/
	
	$initial_data = array(
		'ACADEMIC_CALENDAR_ID' =>$app['ACADEMIC_CALENDAR_ID'],
    );
	
	$form = $app['form.factory']->createBuilder('form', $initial_data);
	
	$ACADEMIC_CALENDAR_ID_sql = "SELECT * FROM ACADEMIC_CALENDARS ";
    $rows_sql = $app['db']->fetchAll($ACADEMIC_CALENDAR_ID_sql, array());

	foreach($rows_sql as $row_key){
			$options[$row_key['ACADEMIC_CALENDAR_ID']]=$row_key['YEAR_START']."-".$row_key['YEAR_END'];
	}
	
	/** FORM STEP 2 ***/

	$form = $form->add('ACADEMIC_CALENDAR_ID', 'choice', array(
	        'required' => false, /* if false none is initially selected */
	        'choices' => $options,
	        'expanded' => false,
	        'constraints' => new Assert\Choice(array_keys($options))
	));
	
	/** FORM STEP 3 ***/
	$form = $form->getForm();
    
	$table_columns = array(
		/*'STUDENT_ID', */
		'ADMISSION_NO', 
		'FIRST_NAME', 
		'MIDDLE_NAME', 
		'LAST_NAME', 
		'ADDRESS', 
		'PHONE_NO', 
		'REGISTRATION_DATE', 

    );

    /*$primary_key = "STUDENT_ID";*/
    $primary_key = "ADMISSION_NO";	

	
    return $app['twig']->render('renavigated/per_class_active_students_list.html.twig', array(
    	"table_columns" => $table_columns,
        "primary_key" => $primary_key,
		"form" => $form ->createView(),
    ));
        
})
->bind('nav_per_class_active_students_001');

$app->match('/test_001/{d_id},{m_id}', function ($d_id,$m_id) use ($app) {
	
	$ids = array(
		'ACADEMIC_CALENDAR_ID' =>$d_id,
		'MAJOR_ID' =>$m_id,
    );
	return new Symfony\Component\HttpFoundation\Response(json_encode($ids), 200);
	//return json($ids);
})
->value('d_id','-1')
->value('m_id','-1');


$app->match('/renavigated/cs_001/{d_id},{m_id},{b_id},{cs_id}', function ($d_id,$m_id,$b_id,$cs_id) use ($app) {
	
	$ids = array(
		'DEPARTMENT_ID' =>$d_id,
		'MAJOR_ID' =>$m_id,
		'BATCH_ID' =>$b_id,
		'CLASS_SECTION_ID' =>$cs_id,
    );
	
	$form = $app['form.factory']->createBuilder('form', $ids);
	
	$ACADEMIC_CALENDAR_ID_sql = "SELECT * FROM ACADEMIC_CALENDARS ";
    $rows_sql = $app['db']->fetchAll($ACADEMIC_CALENDAR_ID_sql, array());

	foreach($rows_sql as $row_key){
			$options[$row_key['ACADEMIC_CALENDAR_ID']]=$row_key['YEAR_START']."-".$row_key['YEAR_END'];
	}
	
	/** FORM STEP 2 ***/

	$form = $form->add('ACADEMIC_CALENDAR_ID', 'choice', array(
	        'required' => false, /* if false none is initially selected */
	        'choices' => $options,
	        'expanded' => false,
	        'constraints' => new Assert\Choice(array_keys($options))
	));
	
	/** FORM STEP 3 ***/
	$form = $form->getForm();

	$table_columns = array(
		//'STUDENT_ID', 
		'ADMISSION_NO', 
		'FIRST_NAME', 
		'MIDDLE_NAME', 
		'LAST_NAME', 
		'ADDRESS', 
		'PHONE_NO', 
		'REGISTRATION_DATE', 

    );

    //$primary_key = "STUDENT_ID";
    $primary_key = "ADMISSION_NO";	
	
    return $app['twig']->render('renavigated/nav_001.twig.html', array(
	  	"table_columns" => $table_columns,
        "primary_key" => $primary_key,
		"form" =>$form->createView(),
	));
	
    /*return $app['twig']->render('renavigated/nav_001.twig.html', array(
    	"table_columns" => $table_columns,
        "primary_key" => $primary_key,
		"form" => $form ->createView(),
    ));*/	
	
	//return new Symfony\Component\HttpFoundation\Response(json_encode($ids), 200);
})
/**  default values for routes **/
->value('d_id','-1')
->value('m_id','-1')
->value('b_id','-1')
->value('cs_id','-1')
;
/** action routes 
	MUST USE at least single 002/-1 call with cs_002 without leading / will error
	
	**/
$app->match('/cs_002/{d_id},{mg_id},{m_id},{b_id},{ac_id},{cs_id}', function ($d_id,$mg_id,$m_id,$b_id,$ac_id,$cs_id) use ($app) {
	
	
	
	$nav_data = array(
		'DEPARTMENT_ID' =>$d_id,
		'MAJOR_GRADE_ID' =>$mg_id,
		'ACADEMIC_CALENDAR_ID' =>$ac_id,
		'MAJOR_ID' =>$m_id,
		'BATCH_ID' =>$b_id,
		'ACADEMIC_CALENDAR_ID' =>$ac_id,
		'CLASS_SECTION_ID' =>$cs_id,
    );

	/** from steps **/
	$form = $app['form.factory']->createBuilder('form', $nav_data);
	
	/** department **/
	$sql = "SELECT * FROM `DEPARTMENTS` ";
    $rows_sql = $app['db']->fetchAll($sql, array());

	$options = array();
	foreach($rows_sql as $row_key){
			$options[$row_key['DEPARTMENT_ID']]=$row_key['NAME'];
	}
	
	
	$form = $form->add('DEPARTMENT_ID', 'choice', array(
	        'required' => false, // if false none is initially selected
	        'choices' => $options,
	        'expanded' => false,
	        'constraints' => new Assert\Choice(array_keys($options))
	));
	
	/** major_grade **/

	$sql = "SELECT * FROM MAJOR_GRADES ";
    $rows_sql = $app['db']->fetchAll($sql, array());

	$options = array();
	foreach($rows_sql as $row_key){
			$options[$row_key['MAJOR_GRADE_ID']]=$row_key['GRADE']."-".$row_key['NAME'];
	}
	
	$form = $form->add('MAJOR_GRADE_ID', 'choice', array(
	        'required' => false, // if false none is initially selected
	        'choices' => $options,
	        'expanded' => false,
	        'constraints' => new Assert\Choice(array_keys($options))
	));
	
	/** majors **/
	
	$sql = "SELECT MAJORS.MAJOR_ID, MAJORS.NAME  FROM MAJORS,DEPARTMENTS, MAJOR_GRADES WHERE MAJORS.DEPARTMENT_ID=DEPARTMENTS.DEPARTMENT_ID ".
	        " AND MAJORS.MAJOR_GRADE_ID=MAJOR_GRADES.MAJOR_GRADE_ID ".
			" AND MAJORS.DEPARTMENT_ID=".$d_id .
			" AND MAJORS.MAJOR_GRADE_ID=".$mg_id ;
	$joe_debug = $sql;
    $rows_sql = $app['db']->fetchAll($sql, array());

	$options = array();
	foreach($rows_sql as $row_key){
			$options[$row_key['MAJOR_ID']]=$row_key['NAME'];
	}
	
	$form = $form->add('MAJOR_ID', 'choice', array(
	        'required' => false, // if false none is initially selected
	        'choices' => $options,
	        'expanded' => false,
	        'constraints' => new Assert\Choice(array_keys($options))
	));
	

	/** batches **/
	
	$sql = "SELECT batches.BATCH_ID, batches.NAME from batches,majors WHERE batches.MAJOR_ID = majors.MAJOR_ID" .
			" AND batches.MAJOR_ID=".$m_id ;
	$joe_debug = $sql;
    $rows_sql = $app['db']->fetchAll($sql, array());

	$options = array();
	foreach($rows_sql as $row_key){
			$options[$row_key['BATCH_ID']]=$row_key['NAME'];
	}
	
	$form = $form->add('BATCH_ID', 'choice', array(
	        'required' => false, // if false none is initially selected
	        'choices' => $options,
	        'expanded' => false,
	        'constraints' => new Assert\Choice(array_keys($options))
	));
	
	
	
	/** academic calendar */
	$sql = "SELECT * FROM ACADEMIC_CALENDARS ORDER BY YEAR_START";
    $rows_sql = $app['db']->fetchAll($sql, array());
	$options = array();
	foreach($rows_sql as $row_key){
			$options[$row_key['ACADEMIC_CALENDAR_ID']]=$row_key['YEAR_START']."-".$row_key['YEAR_END'];
	}
	

	$form = $form->add('ACADEMIC_CALENDAR_ID', 'choice', array(
	        'required' => false, // if false none is initially selected
	        'choices' => $options,
	        'expanded' => false,
	        'constraints' => new Assert\Choice(array_keys($options))
	));
	

	/** academic calendar */
	$sql =  "SELECT CLASS_SECTIONS.CLASS_SECTION_ID, CLASS_SECTIONS.NAME,BATCHES.NAME as BCNAME ".
			" FROM CLASS_SECTIONS,BATCHES, ACADEMIC_CALENDARS ".
			" WHERE CLASS_SECTIONS.BATCH_ID=BATCHES.BATCH_ID ".
			" AND CLASS_SECTIONS.ACADEMIC_CALENDAR_ID=ACADEMIC_CALENDARS.ACADEMIC_CALENDAR_ID".
			" AND CLASS_SECTIONS.BATCH_ID=".$b_id .
			" AND CLASS_SECTIONS.ACADEMIC_CALENDAR_ID=".$ac_id ;
	$joe_debug = $sql;
    $rows_sql = $app['db']->fetchAll($sql, array());
	$options = array();
	foreach($rows_sql as $row_key){
			$options[$row_key['CLASS_SECTION_ID']]=$row_key['BCNAME']."-".$row_key['NAME'] ; 
	}
	$form = $form->add('CLASS_SECTION_ID', 'choice', array(
	        'required' => false, // if false none is initially selected
	        'choices' => $options,
	        'expanded' => false,
	        'constraints' => new Assert\Choice(array_keys($options))
	));
	
	
	
	
		//rendering ...
	$form = $form->getForm();
    
	$table_columns = array(
		/*'STUDENT_ID', */
		'ADMISSION_NO', 
		'FIRST_NAME', 
		'MIDDLE_NAME', 
		'LAST_NAME', 
		//'ADDRESS', 
		//'PHONE_NO', 
		'REGISTRATION_DATE', 

    );
	
	$table_columns_chosen = array(
		/*'STUDENT_ID', */
		'ADMISSION_NO', 
		'FIRST_NAME', 
		'MIDDLE_NAME', 
		'LAST_NAME', 
		//'ADDRESS', 
		//'PHONE_NO', 
		'REGISTRATION_DATE', 
		'STATUS',

    );


    //$primary_key = "STUDENT_ID";
    $primary_key = "ADMISSION_NO";	

	
    return $app['twig']->render('renavigated/nav_002.twig.html', array(
    	"table_columns_chosen" => $table_columns_chosen,
        "table_columns" => $table_columns,
        "primary_key" => $primary_key,
		"form" => $form ->createView(),
		"nav_data" => $nav_data,  /*  navigation data*/
		"php_debug" => $joe_debug , 
    ));
        
})
->value('d_id','-1')
->value('mg_id','-1')
->value('m_id','-1')
->value('ac_id','-1')
->value('b_id','-1')
->value('cs_id','-1')
->bind('cs_002');

/*inside {...} must equal following $...  */
$app->match('/renavigated/migrate/{cs_id}', function (Symfony\Component\HttpFoundation\Request $request, $cs_id) use ($app) {  

		$debug="not post";
		error_log("cs_id => ".$cs_id);
		
		if("POST" == $request->getMethod()){
				$debug =" method = post";
				
				if ($request->request){
					$debug ="-> request not empty\n". var_export($request->request,true);
					$migrations=$request->request->get("migrations");
					if ($migrations){
							//error_log ("migrations.length=".count($migrations));
							/*for ($migrations as $migrate){
								echo $migrations[$o]
							}*/
							$c=count($migrations);
							if($c>0){
										
								for($i=0; $i< $c ; $i++){
								$exploded=explode(" ",$migrations[$i].split()[0]);
									
									if(strstr($migrations[$i],"#insert#")!=false){
										$sql_pre = " insert into per_class_active_students (class_section_id, student_id,status)  values ";
										//error_log (" entry ".$i ."=> ".$migrations[$i]);
										$find_sql = "select students.STUDENT_ID  from students where ADMISSION_NO=".$exploded[0];
										error_log ("find sql : ".$find_sql);
										$rows_id = $app['db']->fetchAll($find_sql,array());
										$s_id=-1;
										foreach($rows_id as $row){
												$s_id=$row['STUDENT_ID']; 
										}
										
										$sql_insert= $sql_pre." ( " .$cs_id  . "," . $s_id. ", 'active' )";//first insert must active
										error_log ($sql_insert);
										$app['db']->executeQuery($sql_insert);
									}else if(strstr($migrations[$i],"#leave#")!=false){
										//error_log (" entry ".$i ."=> ".$migrations[$i]);
										$find_sql = "select students.STUDENT_ID  from students where ADMISSION_NO=".$exploded[0];
										error_log ("find sql : ".$find_sql);
										$rows_id = $app['db']->fetchAll($find_sql,array());
										$s_id=-1;
										foreach($rows_id as $row){
												$s_id=$row['STUDENT_ID']; 
										}
										
										$sql_update = " update  per_class_active_students set STATUS='leave' where STUDENT_ID=".$s_id.
														" AND class_section_id=" . $cs_id ;
										error_log ($sql_update);
										$app['db']->executeQuery($sql_update);
										
										
									}else if(strstr($migrations[$i],"#dropped_out#")!=false){
										//error_log (" entry ".$i ."=> ".$migrations[$i]);
										$find_sql = "select students.STUDENT_ID  from students where ADMISSION_NO=".$exploded[0];
										error_log ("find sql : ".$find_sql);
										$rows_id = $app['db']->fetchAll($find_sql,array());
										$s_id=-1;
										foreach($rows_id as $row){
												$s_id=$row['STUDENT_ID']; 
										}
										
										$sql_update = " update  per_class_active_students set STATUS='dropped_out' where STUDENT_ID=".$s_id.
													  " AND class_section_id=" . $cs_id ;
										error_log ($sql_update);
										$app['db']->executeQuery($sql_update);
										
										
									}else if(strstr($migrations[$i],"#active#")!=false){
										//error_log (" entry ".$i ."=> ".$migrations[$i]);
										$find_sql = "select students.STUDENT_ID  from students where ADMISSION_NO=".$exploded[0];
										error_log ("find sql : ".$find_sql);
										$rows_id = $app['db']->fetchAll($find_sql,array());
										$s_id=-1;
										foreach($rows_id as $row){
												$s_id=$row['STUDENT_ID']; 
										}
										
										$sql_update = " update  per_class_active_students set STATUS='active' where STUDENT_ID=".$s_id.
													  " AND class_section_id=" . $cs_id ;
										error_log ($sql_update);
										$app['db']->executeQuery($sql_update);
										
										
									}else if(strstr($migrations[$i],"#remove#")!=false){
										//error_log (" entry ".$i ."=> ".$migrations[$i]);
										$find_sql = "select students.STUDENT_ID  from students where ADMISSION_NO=".$exploded[0];
										error_log ("find sql : ".$find_sql);
										$rows_id = $app['db']->fetchAll($find_sql,array());
										$s_id=-1;
										foreach($rows_id as $row){
												$s_id=$row['STUDENT_ID']; 
										}
										
										$sql_remove = " delete from  per_class_active_students  where STUDENT_ID=".$s_id.
														" AND class_section_id=" . $cs_id ;
										error_log ($sql_remove);
										$app['db']->executeQuery($sql_remove);
										
										
									}
									
								}
							}
					}
				}
				
		}
		error_log("debug: " .$debug);
		//echo "migrating ...." ; die  ;
		return new Symfony\Component\HttpFoundation\Response(json_encode("MIGRATIONS ADDED"), 200);
		//return new Symfony\Component\HttpFoundation\Response(json_encode($debug), 200);
		
})
->value('cs_id',-1);
;
