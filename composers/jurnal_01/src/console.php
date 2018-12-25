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

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
//use Doctrine\DBAL\Schema\Table;

$console = new Application('CRUD Admin Generator command instalation', '1.0');

$console
	->register('generate:admin')
	->setDefinition(array())
	->setDescription("Generate administrator")
	->setCode(function (InputInterface $input, OutputInterface $output) use ($app) {

		$getTablesQuery = "SHOW TABLES";
		
		//limiting tables here .....
		//$getTablesQuery = "show tables where not tables_in_simlitabmas like \"document%\" and not tables_in_simlitabmas like \"dokumen%\" " ;
		
		$getTablesResult = $app['db']->fetchAll($getTablesQuery, array());

		$_dbTables = array();
		$dbTables = array();

		foreach($getTablesResult as $getTableResult){

			$_dbTables[] = reset($getTableResult);

			$dbTables[] = array(
				"name" => reset($getTableResult),
				"columns" => array()
			);
		}
		/** or define  tables to be generated */
		if(1==0){
			$dbTables=array();
			
			$aTable[] = array(
				"name" => "user_login",
				"columns" => array()
			);
			/*$dbTables=array_merge($dbTables,$aTable);
			
			$aTable[] = array(
				"name" => "statususulan",
				"columns" => array()
			);
			$dbTables=array_merge($dbTables,$aTable);
			
			$aTable[] = array(
				"name" => "tipeusulan",
				"columns" => array()
			);
			$dbTables=array_merge($dbTables,$aTable);
			
			
			$dbTables=array_merge($dbTables,$aTable);
			$aTable[] = array(
				"name" => "user_role_types",
				"columns" => array()
			);
			$dbTables=array_merge($dbTables,$aTable);
			
			
			
			$aTable[] = array(
				"name" => "userlogin",
				"columns" => array()
			);
			$dbTables=array_merge($dbTables,$aTable);
			
			
			$aTable[] = array(
				"name" => "userroles",
				"columns" => array()
			);
			
			$dbTables=array_merge($dbTables,$aTable);
			
			
			$aTable[] = array(
				"name" => "tahun_usulan",
				"columns" => array()
			);
			$aTable[] = array(
				"name" => "usulandibuka",
				"columns" => array()
			);
			$dbTables=array_merge($dbTables,$aTable);

			$aTable[] = array(
				"name" => "usulan",
				"columns" => array()
			);
			$dbTables=array_merge($dbTables,$aTable);*/
		}
					
		/*end table by table**/
		
		foreach($dbTables as $dbTableKey => $dbTable){
			$getTableColumnsQuery = "SHOW COLUMNS FROM `" . $dbTable['name'] . "`";
			$getTableColumnsResult = $app['db']->fetchAll($getTableColumnsQuery, array());

			foreach($getTableColumnsResult as $getTableColumnResult){
				$dbTables[$dbTableKey]['columns'][] = $getTableColumnResult;
			}

		}

		$tables = array();
		foreach($dbTables as $dbTable){
			//echo "processing table ".$dbTable['name']."\n";

			if(count($dbTable['columns']) <= 1){
				continue;
			}

			$table_name = $dbTable['name'];
			$table_columns = array();
			$primary_key = false;

			$primary_keys = 0;
			$primary_keys_auto = 0;
			foreach($dbTable['columns'] as $column){
				if($column['Key'] == "PRI"){
					$primary_keys++;
				}
				if($column['Extra'] == "auto_increment"){
					$primary_keys_auto++;
				}
			}

			if($primary_keys === 1 || ($primary_keys > 1 && $primary_keys_auto === 1)){

				//echo " Looking up  externals ...".".\n";
				foreach($dbTable['columns'] as $column){
					$stb=strtolower($table_name);
					$scf=strtolower($column['Field']);
					
					$external_table = false;

					if($primary_keys > 1 && $primary_keys_auto == 1){
						if($column['Extra'] == "auto_increment"){
							$primary_key = $column['Field'];
						}
					}
					else if($primary_keys == 1){
						if($column['Key'] == "PRI"){
							$primary_key = $column['Field'];
						}
					}
					else{
						continue 2;
					}

				//echo " check  ".$column['Field'].".\n";
					if(strtolower(substr($column['Field'], -3)) == "_id"){
						//echo "   suspect ".$column['Field']."  ".$column['Key'] . "\n";
						$_table_name = substr($column['Field'], 0, -3);
	
						//if(in_array(strtolower($_table_name), $_dbTables)){
						if($column['Key']=="MUL"){
							$external_table = strtolower($_table_name);
							//if ($external_table=="user") $external_table ="users";
							//if ($external_table=="user_role_type") $external_table ="user_role_types";
							//if ($external_table=="tipe_usulan") $external_table ="tipeusulan";
							//echo "     found external ".$external_table."\n";
						}
					}
					
					/*special table here*/
					
					if($stb=="usulan"){
					   if($scf=="pengusul" |
					      $scf=="reviewer1" |
					      $scf=="reviewer2" 
					      )
							$external_table="userlogin";
						if($scf=="status"    )
							$external_table="statususulan";
							
					}
					
					if($stb=="usulandibuka"){
					   if($scf=="status") 
						   $external_table="optionsstatususulandibuka";
					}
					
					
					
					$table_columns[] = array(
						"name" => $column['Field'],
						"primary" => $column['Field'] == $primary_key ? true : false,
						"nullable" => $column['Null'] == "NO" ? true : false,
						"auto" => $column['Extra'] == "auto_increment" ? true : false,
						"external" => $column['Field'] != $primary_key ? $external_table : false,
						"type" => $column['Type']
					);
				}

			}
			else{
				continue;
			}


			$tables[$table_name] = array(
				"primary_key" => $primary_key,
				"columns" => $table_columns
			);

		}

		$MENU_OPTIONS = "";
		$BASE_INCLUDES = "";
		
		//non generated bases controllers ...
		$BASE_INCLUDES = "//require_once __DIR__.'/" . "custom" . "/test_001.php';" . "\n";
		$BASE_INCLUDES .= "require_once __DIR__.'/" . "user_profile" . "/index.php';" . "\n";

		$CRUD_WORDS_HERE="\n";
		foreach($tables as $table_name => $table){
			echo "processing table 2 ".$table_name."\n";

			$table_columns = $table['columns'];

			$TABLENAME = $table_name;
			$TABLE_PRIMARYKEY = $table['primary_key'];

			$TABLECOLUMNS_ARRAY = "";
			$TABLECOLUMNS_TYPE_ARRAY = "";			
			$TABLECOLUMNS_INITIALDATA_EMPTY_ARRAY = "";
			$TABLECOLUMNS_INITIALDATA_ARRAY = "";

			$EXTERNALS_FOR_LIST = "";
			$EXTERNALSFIELDS_FOR_FORM = "";
			$FIELDS_FOR_FORM = "";

			$INSERT_QUERY_FIELDS = array();
			$INSERT_EXECUTE_FIELDS = array();
			$UPDATE_QUERY_FIELDS = array();
			$UPDATE_EXECUTE_FIELDS = array();

			$EDIT_FORM_TEMPLATE = "";

			$MENU_OPTIONS .= "" .
			"<li class=\"treeview {% if option is defined and (option == '" . $TABLENAME . "_list' or option == '" . $TABLENAME . "_create' or option == '" . $TABLENAME . "_edit') %}active{% endif %}\">" . "\n" .
			"    <a href=\"#\">" . "\n" .
			"        <i class=\"fa fa-folder-o\"></i>" . "\n" .
			"        <span> {{app['translator'].trans('" . $TABLENAME . "'|lower)}}</span>" . "\n" .
			"        <i class=\"fa pull-right fa-angle-right\"></i>" . "\n" .
			"    </a>" . "\n" .
			"    <ul class=\"treeview-menu\" style=\"display: none;\">" . "\n" .
			"        <li {% if option is defined and option == '" . $TABLENAME . "_list' %}class=\"active\"{% endif %}><a href=\"{{ path('" . $TABLENAME . "_list') }}\" style=\"margin-left: 10px;\"><i class=\"fa fa-angle-double-right\"></i> Tabular</a></li>" . "\n" .
			"        <li {% if option is defined and option == '" . $TABLENAME . "_create' %}class=\"active\"{% endif %}><a href=\"{{ path('" . $TABLENAME . "_create') }}\" style=\"margin-left: 10px;\"><i class=\"fa fa-angle-double-right\"></i> Buat</a></li>" . "\n" .
			"    </ul>" . "\n" .
			"</li>" . "\n\n";

			$BASE_INCLUDES .= "require_once __DIR__.'/" . $TABLENAME . "/index.php';" . "\n";

			$count_externals = 0;
			
			//zoel
			$date_crud_exist=false;
			$date_columns=array();
			$stb=strtolower($table_name);
			
			$__WHERE_CLAUSE_LIMITER__="";
			//limiter for a tables */
			//this will limit usulan to only specific PENGUSUL or specific reviewers
			
			$__BEFORE_EXECUTE__UPDATE_CREATE__="";
			$__BEFORE_EXECUTE__UPDATE_EDIT__="";
			$TRANSFORM_TEXT_TO_KEY_SEARCH="";
			
			if($stb=="usulan"){
			$__WHERE_CLAUSE_LIMITER__="\n".
			"\tif (\$app['credentials']['current_role']==\"Pengusul\"){\n".
				"\t\t  \$whereClause .= \" And Pengusul=\"."."\$app['credentials']['userlogin_id'] ; \n".
			"\t} ;\n";

			//reviewer can see usulan in any status but
			// can change/review only in review status
			$__WHERE_CLAUSE_LIMITER__.="\n".
			"\tif (\$app['credentials']['current_role']==\"Reviewer\"){\n".
				"\t\t  \$whereClause .= \" And (REVIEWER1=\"."."\$app['credentials']['userlogin_id'].  \n".
				"\t\t   \" OR REVIEWER2=\"."."\$app['credentials']['userlogin_id'].\" )  \"    ;\n".
			"\t} ;\n";
			

			}
			
			// file upload handlers
			foreach($table_columns as $table_column){
				$col=strtolower($table_column['name']);
				if(startsWith($col,"path_")){
					echo "Fixing upload handler for ".$stb." ".$col."\n";
					$code_01="\n".
					"\t\t\tif(\$data['".$col."_UPLOAD']){\n".
					"\t\t\t	\$forig=\$app['credentials']['login'].\"__\".date(\"Y_m_d_h_m_s__\").\$data['".$col."_UPLOAD']->getClientOriginalName();\n".
					"\t\t\t	\$data['".$col."_UPLOAD']->move(\$app['uploaded_dir'].\"/\".\$app['credentials']['login'],\$forig);\n".
					"\t\t\t	\$data['".$col."']=\$forig ; \n".
					"\t\t\t}";
					
					$__BEFORE_EXECUTE__UPDATE_CREATE__.=$code_01;
					$__BEFORE_EXECUTE__UPDATE_EDIT__.=$code_01;
				
				
				}
			
			}
			
			if($stb=="usulan"){
				/**0.check if file uploaded 
				   1. 
				**/
				$__BEFORE_EXECUTE__UPDATE_CREATE__.="\n".
				"\t\t\tif(\$data['PATH_PROPOSAL_UPLOAD']){\n".
				"\t\t\t	\$forig=\$app['credentials']['userlogin_id'].\"__\".date(\"Y_m_d_h_m_s__\").\$data['PATH_PROPOSAL_UPLOAD']->getClientOriginalName();\n".
				"\t\t\t	\$data['PATH_PROPOSAL_UPLOAD']->move(\$app['uploaded_dir'].\"/\".\$app['userlogin_id']['login'],\$forig);\n".
				"\t\t\t	\$data['PATH_PROPOSAL']=\$forig ; \n".
				"\t\t\t}";
				
				$__BEFORE_EXECUTE__UPDATE_EDIT__.="\n".
				"\tif(\$data['PATH_PROPOSAL_UPLOAD']){\n".
				"\t\t\t	\$forig=\$app['credentials']['userlogin_id'].\"__\".date(\"Y_m_d_h_m_s__\").\$data['PATH_PROPOSAL_UPLOAD']->getClientOriginalName();\n".
				"\t\t	\$data['PATH_PROPOSAL_UPLOAD']->move(\$app['uploaded_dir'].\"/\".\$app['credentials']['userlogin_id'],\$forig);\n".
				"\t\t\t	\$data['PATH_PROPOSAL']=\$forig ; \n".
				"\t}";
				
				
				//path_review_1
				$col="PATH_REVIEW_1";
				$__BEFORE_EXECUTE__UPDATE_CREATE__.="\n".
				"\t\t\tif(\$data['".$col."_UPLOAD']){\n".
				"\t\t\t	\$forig=\$app['credentials']['userlogin_id'].\"__\".date(\"Y_m_d_h_m_s__\").\$data['".$col."_UPLOAD']->getClientOriginalName();\n".
				"\t\t\t	\$data['".$col."_UPLOAD']->move(\$app['uploaded_dir'].\"/\".\$app['userlogin_id']['login'],\$forig);\n".
				"\t\t\t	\$data['".$col."']=\$forig ; \n".
				"\t\t\t}";
				
				$__BEFORE_EXECUTE__UPDATE_EDIT__.="\n".
				"\tif(\$data['".$col."_UPLOAD']){\n".
				"\t\t\t	\$forig=\$app['credentials']['userlogin_id'].\"__\".date(\"Y_m_d_h_m_s__\").\$data['".$col."_UPLOAD']->getClientOriginalName();\n".
				"\t\t	\$data['".$col."_UPLOAD']->move(\$app['uploaded_dir'].\"/\".\$app['credentials']['userlogin_id'],\$forig);\n".
				"\t\t\t	\$data['".$col."']=\$forig ; \n".
				"\t}";

				//path_review_2
				$col="PATH_REVIEW_2";
				$__BEFORE_EXECUTE__UPDATE_CREATE__.="\n".
				"\t\t\tif(\$data['".$col."_UPLOAD']){\n".
				"\t\t\t	\$forig=\$app['credentials']['userlogin_id'].\"__\".date(\"Y_m_d_h_m_s__\").\$data['".$col."_UPLOAD']->getClientOriginalName();\n".
				"\t\t\t	\$data['".$col."_UPLOAD']->move(\$app['uploaded_dir'].\"/\".\$app['userlogin_id']['login'],\$forig);\n".
				"\t\t\t	\$data['".$col."']=\$forig ; \n".
				"\t\t\t}";
				
				$__BEFORE_EXECUTE__UPDATE_EDIT__.="\n".
				"\tif(\$data['".$col."_UPLOAD']){\n".
				"\t\t\t	\$forig=\$app['credentials']['userlogin_id'].\"__\".date(\"Y_m_d_h_m_s__\").\$data['".$col."_UPLOAD']->getClientOriginalName();\n".
				"\t\t	\$data['".$col."_UPLOAD']->move(\$app['uploaded_dir'].\"/\".\$app['credentials']['userlogin_id'],\$forig);\n".
				"\t\t\t	\$data['".$col."']=\$forig ; \n".
				"\t}";

				
				
			}
			$CRUD_WORDS_HERE.="\t\"".$stb."\" => \"".$stb."\" ,\n";
			foreach($table_columns as $table_column){
				$scf=strtolower($table_column['name']);
				
				$CRUD_WORDS_HERE.="\t\t\"".$stb."_".$scf."\" => \"".$scf."\" ,\n";
				
				$TABLECOLUMNS_ARRAY .= "\t\t" . "'". $table_column['name'] . "', \n";
				$TABLECOLUMNS_TYPE_ARRAY .= "\t\t" . "'". $table_column['type'] . "', \n";				
				if(!$table_column['primary'] || ($table_column['primary'] && !$table_column['auto'])){
					$TABLECOLUMNS_INITIALDATA_EMPTY_ARRAY .= "\t\t" . "'". $table_column['name'] . "' => '', \n";
					$TABLECOLUMNS_INITIALDATA_ARRAY .= "\t\t" . "'". $table_column['name'] . "' => \$row_sql['".$table_column['name']."'], \n";

					$INSERT_QUERY_FIELDS[] = "`" . $table_column['name'] . "`";
					$INSERT_EXECUTE_FIELDS[] = "\$data['" . $table_column['name'] . "']";
					$UPDATE_QUERY_FIELDS[] = "`" . $table_column['name'] . "` = ?";
					$UPDATE_EXECUTE_FIELDS[] = "\$data['" . $table_column['name'] . "']";

					
					if(strtolower($table_column['name'])== 'dibuka') {
						echo "di_buka found ".$table_column['type']. "\n";
					}

					
					if(strpos($table_column['type'], 'text') !== false){
						$EDIT_FORM_TEMPLATE .= "" .
						"\t\t\t\t\t\t\t\t\t" . "<div class='form-group'>" . "\n" .
						"\t\t\t\t\t\t\t\t\t" . "    {{ form_label(form." . $table_column['name'] . ") }}" . "\n" .
						"\t\t\t\t\t\t\t\t\t" . "    {{ form_widget(form." . $table_column['name'] . ", { attr: { 'class': 'form-control textarea', 'style': 'width: 100%; height: 200px; font-size: 14px; line-height: 18px; border: 1px solid #dddddd; padding: 10px;' }}) }}" . "\n" .
						"\t\t\t\t\t\t\t\t\t" . "</div>" . "\n\n";
					} else {
						if(strpos($table_column['type'], 'date') !== false){
							//echo "\tdate found ".$table_column['name']. "\n";
							
							//if(!(($stb=="usulan" && $scf =="tanggal_usul"))){
								array_push($date_columns,$table_column);
								$date_crud_exist=true;
							//}
						}
						$EDIT_FORM_TEMPLATE .= "" .
						"\t\t\t\t\t\t\t\t\t" . "<div class='form-group'>" . "\n" .
						"\t\t\t\t\t\t\t\t\t" . "    {{ form_label(form." . $table_column['name'] . ") }}" . "\n" .
						"\t\t\t\t\t\t\t\t\t" . "    {{ form_widget(form." . $table_column['name'] . ", { attr: { 'class': 'form-control' }}) }}" . "\n" .
						"\t\t\t\t\t\t\t\t\t" . "</div>" . "\n\n";
						if(startsWith($table_column['name'],"path_")!==FALSE){
							
							
							//echo "TODO FIX 299 p. 299 {%set uploader_userlogin_id \n";
							/** provide download/unduh button here ... 
							'|first' in code is to grep logged name from document name
							**/
							$EDIT_FORM_TEMPLATE .= "\n".
							"\t\t\t\t\t\t\t\t\t" . "{%if form.vars.value.".$table_column['name']."!=\"\" %}" . "\n" .
							"\t\t\t\t\t\t\t\t\t" . "		<div class='form-group'>" . "\n" .
							"\t\t\t\t\t\t\t\t\t" . "			{%set uploader_userlogin_id=form.vars.value.".$table_column['name']."|split('__')|first%}" . "\n" .
							"\t\t\t\t\t\t\t\t\t" . "		   <a href=\"{{app['www_uploaded']}}/{{uploader_userlogin_id}}/{{form.vars.value.".$table_column['name']."}}\">Unduh </a> " . "\n" .
							"\t\t\t\t\t\t\t\t\t" . "		</div>" . "\n" .
							"\t\t\t\t\t\t\t\t\t" . "{%endif%}" . "\n" ;
			
							$EDIT_FORM_TEMPLATE .= "" .
							"\t\t\t\t\t\t\t\t\t" . "<div class='form-group'>" . "\n" .
							"\t\t\t\t\t\t\t\t\t" . "    {{ form_widget(form." . $table_column['name'] ."_UPLOAD" .", { attr: { 'class': 'form-control' }}) }}" . "\n" .
							"\t\t\t\t\t\t\t\t\t" . "</div>" . "\n\n";
						}
						
					}
				}

				$field_nullable = $table_column['nullable'] ? "true" : "false";
							
				if($table_column['external']){
					//echo "\$table_column['external']=".$table_column['external']."\n";
					
					$external_table = $tables[strtolower($table_column['external'])];
					//var_dump($tables); die;
					$external_primary_key = $external_table['primary_key'];
					$external_select_field = false;
					//$search_names_foreigner_key = array('name','title','e?mail','username');
					$search_names_foreigner_key = array('login','name');

					if(!empty($app['usr_search_names_foreigner_key'])){
						$search_names_foreigner_key = array_merge(
							$app['usr_search_names_foreigner_key'],
							$search_names_foreigner_key);
					}

						// pattern to match a name column, with or whitout a 3 to 4 Char prefix
					$search_names_foreigner_key = '#^(.{3,4}_)?('.implode('|',$search_names_foreigner_key).')$#i';

					foreach($external_table['columns'] as $external_column){
						if( preg_match($search_names_foreigner_key, $external_column['name'])){
							$external_select_field = $external_column['name'];
						}
					}

					/*zoel*/
					//echo "##zzz ".$external_primary_key." \n";
							
					if(strtolower($external_primary_key)=="user_login_id"){
							echo "## user_login --> login \n";
							$external_field="LOGIN";
					}


					if(!$external_select_field){
						$external_select_field = $external_primary_key;
					}
					

					$external_cond = $count_externals > 0 ? "else if" : "if";

					/**enable external search here */
					
					//if($scf=="pengusul"){ /**enabler for column pengusul **/
						echo "\t ### creating external search enabler .. for  ". $stb. ":". $scf ."  \n" ;
						$TRANSFORM_TEXT_TO_KEY_SEARCH .="\n".
						"\t/**Creating enabler .. for  ". $stb. ":". $scf ."  **/\n" ;
						
							//$searchfield=strtolower($external_column['name']);
							echo "\t\t ### adding .. for  ".$external_select_field."\n" ;
							
							//echo "\t\t\t ### creating enabler .. for  ".$scf ;
							$TRANSFORM_TEXT_TO_KEY_SEARCH .="\n".
							"\t" ."if (\$searchValue!==\"\"){\n".
							"\t" . "    \$search_sql = \"SELECT `" . $external_primary_key . "` FROM `" . $table_column['external'] . "` WHERE `" . $external_select_field . "` LIKE '%\". \$searchValue . \"%'\" ; \n" .
							"\t" . "    \$search_rows = array(); \$search_rows = \$app['db']->fetchAll(\$search_sql);" . "\n" .
							"\t" . "    //error_log(\"#####".$scf."=>".$table_column['external']."######\".count(\$search_row));" . "\n" .
							"\t" . "    if(count(\$search_rows)>0){\n" .
							"\t" . "      foreach(\$search_rows as \$search_row)  { \n" .
							"\t" . "         \$transform_text_to_key .= \" OR  ".$scf."=\".\$search_row['".$external_primary_key."']; \n" .
							"\t" . "      } \n" .
    
							"\t" . "    }\n" .
							"\t" . " } \n";
							
						
					
					//}
					
					
					$EXTERNALS_FOR_LIST .= "" .
					"\t\t\t" . $external_cond . "(\$table_columns[\$i] == '" . $table_column['name'] . "'){" . "\n" .
					"\t\t\t" . "    \$findexternal_sql = 'SELECT `" . $external_select_field . "` FROM `" . $table_column['external'] . "` WHERE `" . $external_primary_key . "` = ?';" . "\n" .
					"\t\t\t" . "    \$findexternal_row = \$app['db']->fetchAssoc(\$findexternal_sql, array(\$row_sql[\$table_columns[\$i]]));" . "\n" .
					"\t\t\t" . "    \$rows[\$row_key][\$table_columns[\$i]] = \$findexternal_row['" . $external_select_field . "'];" . "\n" .
					"\t\t\t" . "}" . "\n";

					
					//echo "###TODO#### limit options for ".$stb." : ".$scf."\n";
					//1. limit but not empty here ...
					
					$EXTERNALSFIELDS_FOR_FORM .= "" .
					"\t\$field_nullable= ".$field_nullable."  ; \n".
					"\tif (\$app['credentials']['current_role']!=\"Administrator\"){\n".
					"\t\t  \$field_default_ro =array('read_only' => true ) ; \n".
					"\t}else { \n".
					"\t\t  \$field_default_ro =array('read_only' => false ) ; \n".
					"\t} \n";
					$EXTERNALSFIELDS_FOR_FORM .= 
					"\t\$limiter=\"\" ;\n" ;
					if($stb=="usulan"){
						if($scf=="pengusul"){
					
							//for pengusul no matter edit nor create
							//limit to userlogin_id
							$EXTERNALSFIELDS_FOR_FORM .= 
							"\t". "if(\$app['credentials']['current_role']==\"Pengusul\"){\n".		
							"\t\t\t". "\$limiter=' WHERE userlogin.userlogin_id ='.\$app['credentials']['userlogin_id']  ;\n".
							"\t". "}\n";
							
							$EXTERNALSFIELDS_FOR_FORM .= 
							"\t". "if(\$app['credentials']['current_role']==\"Reviewer\"){\n".		
							"\t\t\t". "\$limiter=' WHERE userlogin.userlogin_id ='.\$row_sql['PENGUSUL']  ;\n".
							"\t". "}\n";
												
						}
																
						
						//pengusul can't alter statususulan_id
						if($scf=="statususulan_id"){
					
							$EXTERNALSFIELDS_FOR_FORM .= 
							"\t". "if(\$app['credentials']['current_role']!=\"Administrator\"){\n".		
							"\t\tif (strpos(\$app['request']->getRequestUri(),\"create\")!==false){\n".
							"\t\t\t". "\$limiter=' WHERE statususulan_id =1'  ;\n".
							"\t\t". "}\n".
							"\t\telse if (strpos(\$app['request']->getRequestUri(),\"edit\")!==false){\n".
							"\t\t\t". "\$limiter=' WHERE statususulan_id ='.\$row_sql['STATUSUSULAN_ID']  ;\n".
							"\t\t". "}\n".
							"\t". "}\n";
						}
						
						//in edit mode pengusul and reviewer can't alter reviewer1
						//on create reviewer1 is emptied below ....
						if($scf=="reviewer1"){
					
							$EXTERNALSFIELDS_FOR_FORM .= 
							"\t". "if(\$app['credentials']['current_role']!=\"Administrator\"){\n".		
							"\t\tif (strpos(\$app['request']->getRequestUri(),\"edit\")!==false){\n".
							"\t\t\t". "\$limiter=\" WHERE userlogin.userlogin_id ='\".\$row_sql['REVIEWER1']. \"'\"  ;\n".
							"\t\t". "}\n".
							"\t". "}\n";
							
							/*can't select alternative when status != diusulkan*/
							$EXTERNALSFIELDS_FOR_FORM .= 
							"\t". "if(\$app['credentials']['current_role']!=\"Administrator\"){\n".		
							"\t\tif (strpos(\$app['request']->getRequestUri(),\"create\")===false){\n".
							"\t". "if(\$row_sql['STATUSUSULAN_ID']!=1){\n".		
							"\t\t\t". "\$limiter=\" WHERE userlogin.userlogin_id ='\".\$row_sql['REVIEWER1']. \"'\"  ;\n".
							"\t\t". "}\n".
							"\t\t". "}\n".
							"\t". "}\n";
							
							
						}
						
						//same as reviewer1
						if($scf=="reviewer2"){
					
							$EXTERNALSFIELDS_FOR_FORM .= 
							"\t". "if(\$app['credentials']['current_role']!=\"Administrator\"){\n".		
							"\t\tif (strpos(\$app['request']->getRequestUri(),\"edit\")!==false){\n".
							"\t\t\t". "\$limiter=\" WHERE userlogin.userlogin_id ='\".\$row_sql['REVIEWER2']. \"'\"  ;\n".
							"\t\t". "}\n".
							"\t". "}\n";
							
							/*can't select alternative when status != diusulkan*/
							$EXTERNALSFIELDS_FOR_FORM .= 
							"\t". "if(\$app['credentials']['current_role']!=\"Administrator\"){\n".		
							"\t\tif (strpos(\$app['request']->getRequestUri(),\"create\")===false){\n".
							"\t". "if(\$row_sql['STATUSUSULAN_ID']!=1){\n".		
							"\t\t\t". "\$limiter=\" WHERE userlogin.userlogin_id ='\".\$row_sql['REVIEWER2']. \"'\"  ;\n".
							"\t\t". "}\n".
							"\t\t". "}\n".
							"\t". "}\n";
							
						}
						
						//reviewer can't edit 
						if($scf=="usulandibuka_id"){
					
							$EXTERNALSFIELDS_FOR_FORM .= 
							"\t". "if(\$app['credentials']['current_role']!=\"Administrator\"){\n".		
							"\t\tif (strpos(\$app['request']->getRequestUri(),\"edit\")!==false){\n".
							"\t\t\t". "\$limiter=\" WHERE USULANDIBUKA_ID ='\".\$row_sql['USULANDIBUKA_ID']. \"'\"  ;\n".
							"\t\t". "}\n".
							"\t". "}\n";
							
							
							/*"\tif (strpos(\$app['request']->getRequestUri(),\"create\")!==false){\n".
							"\t\t\t". "\$limiter=\" WHERE USULANDIBUKA_ID ='\".\$row_sql['USULANDIBUKA_ID']. \"'\"  ;\n".
							"\t". "}\n".*/
							
							
							/*can't select alternative when status != diusulkan*/
							/*$EXTERNALSFIELDS_FOR_FORM .= 
							"\t". "if(\$row_sql['USULANDIBUKA_ID']!=1){\n".		
							"\t\t\t". "\$limiter=\" WHERE USULANDIBUKA_ID ='\".\$row_sql['USULANDIBUKA_ID']. \"'\"  ;\n".
							"\t". "}\n";*/
						}
						
						//gen debug
						//$EXTERNALSFIELDS_FOR_FORM .= " /* debug_01 */ var_dump(\$row_sql) ; die; " ;
						
					}
					
					
					/**modifying before externals retrived by sql ...**/
					$table_column_ext ="";
					
					if($stb=="usulan"){
						//echo "extending tables " .$scf." \n";
								
						if($scf=="reviewer1"||$scf=="reviewer2"){
								//echo "extending tables \n";
								$table_column_ext=" , userroles " ;
								
								$EXTERNALSFIELDS_FOR_FORM .= "\n" .
								"if((strpos(strtolower(\$limiter),\"where\"))===false){ \n".
								"\$limiter .= \" where  1 \" ; \n".
								"}\n".
								"\$limiter .= \" AND userroles.userlogin_id=userlogin.userlogin_id and userroles.user_role_type_id=3\" ; 	\n";
								
						}
						
						if($scf=="pengusul"){
								//echo "extending tables \n";
								$table_column_ext=" , userroles " ;
								
								$EXTERNALSFIELDS_FOR_FORM .= "\n" .
								"if((strpos(strtolower(\$limiter),\"where\"))===false){ \n".
								"\$limiter .= \" where  1 \" ; \n".
								"}\n".	
								"\$limiter .= \" AND userroles.userlogin_id=userlogin.userlogin_id and userroles.user_role_type_id=2\" ; 	\n";
								
						}

						if($scf=="usulandibuka_id"){
								//echo "extending tables \n";
								$table_column_ext=" , optionsstatususulandibuka " ;
								
								$EXTERNALSFIELDS_FOR_FORM .= "\n" .
								"if((strpos(strtolower(\$limiter),\"where\"))===false){ \n".
								"\$limiter .= \" where  1 \" ; \n".
								"}\n".	
								"\$limiter .= \" AND usulandibuka.status= optionsstatususulandibuka. ".
								"Optionsstatususulandibuka_id and Optionsstatususulandibuka_id=1\" ; 	\n"; /*1 : dibuka*/
								
						}
						
						
						
					}
					
					
					$f01= $table_column['external'].".".$external_primary_key . ", " .$table_column['external'].".". $external_select_field . " FROM " . $table_column['external'] .  $table_column_ext . "  '  . \$limiter ;" . "\n" ;
					
					$EXTERNALSFIELDS_FOR_FORM .= "" .
					"\t" . "\$options = array();" . "\n" .
					"\t" . "\$findexternal_sql = 'SELECT " .$f01.
					"\t" . "\$findexternal_rows = \$app['db']->fetchAll(\$findexternal_sql, array());" . "\n" .
					"\t" . "foreach(\$findexternal_rows as \$findexternal_row){" . "\n" .
					"\t" . "    \$options[\$findexternal_row['" . $external_primary_key . "']] = \$findexternal_row['" . $external_select_field . "'];" . "\n" .
					"\t" . "}" . "\n" ;
					
					//2. emptying optionsss, by set null array to $options
				
					if($stb=="usulan"){
						if($scf=="reviewer1"||$scf=="reviewer2"){
							echo "####must limit null allow value for external field ".$scf ."\n";
						$EXTERNALSFIELDS_FOR_FORM .= "" .
						"\t". "if(\$app['credentials']['current_role']!=\"Administrator\"){\n".		
						"\tif (strpos(\$app['request']->getRequestUri(),\"edit\")!==false){\n".
						"\t\t\$field_nullable = true;\n".
						"\t} else { \n".
						"\t\t\$options = array();\n".
						"\t} \n".
						"\t} \n";
						
						}
						
						/*admin free or not free ?*/
						/*if($scf=="reviewer1"
						||$scf=="reviewer2"
						||$scf=="usulandibuka_id"
						||$scf=="statususulan_id"
						){
						$EXTERNALSFIELDS_FOR_FORM .= 
							"\t". "if(\$row_sql['USULANDIBUKA_ID']!=1){\n".		
							"\t\t\t\$field_nullable = true;\n".
							"\t". "}\n";

						}*/
					}
					
					$EXTERNALSFIELDS_FOR_FORM .= "" .
					"\t" . "if(count(\$options) > 0){" . "\n" .
					"\t" . "    \$form = \$form->add('" . $table_column['name'] . "', 'choice', array_merge(\$field_default_ro,array(" . "\n" .
					"\t" . "        'required' => " . "\$field_nullable" . "," . "\n" .
					"\t" . "        'choices' => \$options," . "\n" .
					"\t" . "        'expanded' => false," . "\n" .
					"\t" . "        'constraints' => new Assert\Choice(array_keys(\$options))" . "\n" .
					"\t" . "    )));" . "\n" .
					"\t" . "}" . "\n" .
					"\t" . "else{" . "\n" .
					"\t" . "    \$form = \$form->add('" . $table_column['name'] . "', 'text', array_merge(array('required' => " . $field_nullable . "),\$field_default_ro));"  . "\n" .
					"\t" . "}" . "\n\n";

					$count_externals++;
				}
				else{
					$FIELDS_FOR_FORM .= "" .
							"\t\$field_default_ro=array('required' => ".$field_nullable.",'disabled' =>true)  ; \n".
					"\t". "if(\$app['credentials']['current_role']==\"Administrator\"){\n".		
								"\tunset(\$field_default_ro['disabled']);\n".
					"\t". "}\n";
					
						
					//remove read only
					if($stb=="usulan"){
							if($scf=="judul"
							||$scf=="ringkasan"
							||$scf=="biaya"
							){
								//Pengusul can't alter status after reviewed
							$FIELDS_FOR_FORM .= "" .
							"\tif (strpos(\$app['request']->getRequestUri(),\"create\")!==false){\n".
								"\t\tunset(\$field_default_ro['disabled']);\n".
							"\t} \n".
							"\telse if (strpos(\$app['request']->getRequestUri(),\"edit\")!==false){\n".
							"\t\tif (\$row_sql['STATUSUSULAN_ID']==\"1\"){\n".
								"\t\t\tunset(\$field_default_ro['disabled']);\n".
							"\t\t} \n".
							"\t} \n";
							}
							
							//echo "TODO #000019 enabled fields for specific reviewer ...\n";
							/**each reviever */
							if($scf=="nilai_review_1"){
							$FIELDS_FOR_FORM .= "" .
							"\telse if (strpos(\$app['request']->getRequestUri(),\"edit\")!==false){\n".
							"\t\tif (\$row_sql['REVIEWER1']==\$app['credentials']['userlogin_id']){\n".
							"\t\tif (\$row_sql['STATUSUSULAN_ID']==\"2\")\n". /***/
							"\t\t\tunset(\$field_default_ro['disabled']);\n".
							"\t\t} \n".
							"\t\t} \n";
							
							}
							
							if($scf=="nilai_review_2"){
							$FIELDS_FOR_FORM .= "" .
							"\telse if (strpos(\$app['request']->getRequestUri(),\"edit\")!==false){\n".
							"\t\tif (\$row_sql['REVIEWER2']==\$app['credentials']['userlogin_id']){\n".
							"\t\tif (\$row_sql['STATUSUSULAN_ID']==\"2\")\n".
							"\t\t\tunset(\$field_default_ro['disabled']);\n".
							"\t\t} \n".
							"\t\t} \n";
							
							}
							
							
							
					}
					
					/**this field can't be set disabled ..
					disabled will passing null to sql state...
					**/
					if($stb=="usulan"){
							if($scf=="tanggal_usul"
							||$scf=="nilai_review_1"
							||$scf=="nilai_review_2"
							){
								//Pengusul can't alter status after reviewed
							$FIELDS_FOR_FORM .= "" .
							"\tif (strpos(\$app['request']->getRequestUri(),\"create\")!==false){\n".
								"\t\tunset(\$field_default_ro['disabled']);\n".
								"\t\tunset(\$field_default_ro['read_only']);\n".
								"\t\t\$field_default_ro=array_merge(\$field_default_ro,array('read_only'=>true));\n". /*replace disabled with read_only*/
								
							"\t} \n" ;
							}
					}
					
					

					if(!$table_column['primary']){

						if(strpos($table_column['type'], 'text') !== false){
							$FIELDS_FOR_FORM .= "" .
							"\t" . "\$form = \$form->add('" . $table_column['name'] . "', 'textarea', array_merge(array('required' => " . $field_nullable . "),\$field_default_ro));" . "\n";
						}else if(strpos($table_column['type'], 'date') !== false){
							
							//default field values ..
							$field_default="";
							//echo "==>date $stb   $scf\n";
							//echo $table_column['name']." ".$table_column['type']."\n";
								
							$field_default="";
							if($stb=="usulan"){
								$str_now="date(\"Y-m-d\")";
								if($scf=="tanggal_usul") $field_default=", 'data' => ".$str_now." ,'read_only' => true ";
							}
							if($stb=="usulandibuka"){
								$str_now="date(\"Y-m-d\")";
								
								if($scf=="tanggal_buka") $field_default=", 'data' => ".$str_now."   ";
							}
							
							$FIELDS_FOR_FORM .= "" .
							"\tif (strpos(\$app['request']->getRequestUri(),\"create\")!==false){\n".
							"\t\t  \$field_default_ro =array_merge(\$field_default_ro,array('data' => date(\"Y-m-d\"))) ; \n".
							"\t} \n";
							
							
							$FIELDS_FOR_FORM .= "" .
							"\tif (\$app['credentials']['current_role']!=\"Administrator\"){\n".
							"\t\t  \$field_default_ro =array_merge(\$field_default_ro,array('read_only' => true )) ; \n".
							"\t} \n";
							
							$FIELDS_FOR_FORM .= "" .
								"\t" . "\$form = \$form->add('" . $table_column['name'] . "', 'text', \n " . 
								"\t\t  \$field_default_ro". ");" . "\n";
								
						}
						else{
							$FIELDS_FOR_FORM .= "" .
							"\t" . "\$form = \$form->add('" . $table_column['name'] . "', 'text', array_merge(array('required' => " . $field_nullable . "),\$field_default_ro));" . "\n";
							if(strpos($table_column['name'],"path_")!==false){
								
								//filter file uploading ....
								if($scf=="path_proposal"){
								$FIELDS_FOR_FORM .= "\n".
								"\t". "if(\$app['credentials']['current_role']==\"Pengusul\"){\n".		
								"\t\tif (strpos(\$app['request']->getRequestUri(),\"create\")!==false){\n".
								"\t\t\tunset(\$field_default_ro['disabled']);\n".
								"\t\t". "}\n".
								"\t\telse if (strpos(\$app['request']->getRequestUri(),\"edit\")!==false){\n".
								"\t\t"."if(\$row_sql['STATUSUSULAN_ID']==1){\n".		
								"\t\t\t unset(\$field_default_ro['disabled']);\n".
								"\t\t". "}\n".
								"\t\t"."}\n".
								"\t". "}\n";			
								}
								
								//for reviewer
								if($scf=="path_review_1"){
								$FIELDS_FOR_FORM .= "\n".
								"\t". "if(\$app['credentials']['current_role']==\"Reviewer\"){\n".		
								"\t\t"."if(\$row_sql['STATUSUSULAN_ID']==2){\n".		
								"\t\tif (\$row_sql['REVIEWER1']==\$app['credentials']['userlogin_id']){\n".
								"\t\t\tunset(\$field_default_ro['disabled']);\n".
								"\t\t". "}\n".
								"\t\t". "}\n".
								"\t". "}\n";			
								}
								
								//for reviewer 2
								if($scf=="path_review_2"){
								$FIELDS_FOR_FORM .= "\n".
								"\t". "if(\$app['credentials']['current_role']==\"Reviewer\"){\n".		
								"\t\t"."if(\$row_sql['STATUSUSULAN_ID']==2){\n".		
								"\t\tif (\$row_sql['REVIEWER2']==\$app['credentials']['userlogin_id']){\n".
								"\t\t\tunset(\$field_default_ro['disabled']);\n".
								"\t\t". "}\n".
								"\t\t". "}\n".
								"\t". "}\n";			
								}
								
								
								
							
								$FIELDS_FOR_FORM .= "" .
								"\t" . "\$form = \$form->add('" . $table_column['name']."_UPLOAD" . "', 'file', array_merge(array('required' => " . $field_nullable . "),\$field_default_ro));" . "\n";
							}
						}
					}
					else if($table_column['primary'] && !$table_column['auto']){
							$FIELDS_FOR_FORM .= "" .
							"\t" . "\$form = \$form->add('" . $table_column['name'] . "', 'text', array_merge(array('required' => " . $field_nullable . "),\$field_default_ro));"  . "\n";
					}
				}
			}

			if($count_externals > 0){
				$EXTERNALS_FOR_LIST .= "" .
				"\t\t\t" . "else{" . "\n" .
				"\t\t\t" . "    \$rows[\$row_key][\$table_columns[\$i]] = \$row_sql[\$table_columns[\$i]];" . "\n" .
				"\t\t\t" . "}" . "\n";
			}

			if($EXTERNALS_FOR_LIST == ""){
				$EXTERNALS_FOR_LIST .= "" . 
				"\t\t" . "if( \$table_columns_type[\$i] != \"blob\") {" . "\n" .
				"\t\t\t\t" . "\$rows[\$row_key][\$table_columns[\$i]] = \$row_sql[\$table_columns[\$i]];" . "\n" . 
				"\t\t" . "} else {" .
				
				"\t\t\t\t" . "if( !\$row_sql[\$table_columns[\$i]] ) {" . "\n" .
				"\t\t\t\t\t\t" . "\$rows[\$row_key][\$table_columns[\$i]] = \"0 Kb.\";" . "\n" .
				"\t\t\t\t" . "} else {" . "\n" .
				   
				"\t\t\t\t\t\t" . "\$rows[\$row_key][\$table_columns[\$i]] = \" <a target='__blank' href='menu/download?id=\" . \$row_sql[\$table_columns[0]];" . "\n" .
				"\t\t\t\t\t\t" . "\$rows[\$row_key][\$table_columns[\$i]] .= \"&fldname=\" . \$table_columns[\$i];" . "\n" . 
				"\t\t\t\t\t\t" . "\$rows[\$row_key][\$table_columns[\$i]] .= \"&idfld=\" . \$table_columns[0];" . "\n" .
				"\t\t\t\t\t\t" . "\$rows[\$row_key][\$table_columns[\$i]] .= \"'>\";" . "\n" .
				"\t\t\t\t\t\t" . "\$rows[\$row_key][\$table_columns[\$i]] .= number_format(strlen(\$row_sql[\$table_columns[\$i]]) / 1024, 2) . \" Kb.\";" . "\n" .
				"\t\t\t\t\t\t" . "\$rows[\$row_key][\$table_columns[\$i]] .= \"</a>\";" . "\n" .
				    
				"\t\t\t\t" . "}" . "\n" .
				
				"\t\t" . "}";
			}


			$INSERT_QUERY_VALUES = array();
			foreach($INSERT_QUERY_FIELDS as $INSERT_QUERY_FIELD){
				$INSERT_QUERY_VALUES[] = "?";
			}
			$INSERT_QUERY_VALUES = implode(", ", $INSERT_QUERY_VALUES);
			$INSERT_QUERY_FIELDS = implode(", ", $INSERT_QUERY_FIELDS);
			$INSERT_EXECUTE_FIELDS = implode(", ", $INSERT_EXECUTE_FIELDS);

			$UPDATE_QUERY_FIELDS = implode(", ", $UPDATE_QUERY_FIELDS);
			$UPDATE_EXECUTE_FIELDS = implode(", ", $UPDATE_EXECUTE_FIELDS);

			$_controller = file_get_contents(__DIR__.'/../gen/controller.php');
			$_controller = str_replace("__TABLENAME__", $TABLENAME, $_controller);
			$_controller = str_replace("__TABLE_PRIMARYKEY__", $TABLE_PRIMARYKEY, $_controller);
			$_controller = str_replace("__TABLECOLUMNS_ARRAY__", $TABLECOLUMNS_ARRAY, $_controller);
			$_controller = str_replace("__TABLECOLUMNS_TYPE_ARRAY__", $TABLECOLUMNS_TYPE_ARRAY, $_controller);			
			$_controller = str_replace("__TABLECOLUMNS_INITIALDATA_EMPTY_ARRAY__", $TABLECOLUMNS_INITIALDATA_EMPTY_ARRAY, $_controller);
			$_controller = str_replace("__TABLECOLUMNS_INITIALDATA_ARRAY__", $TABLECOLUMNS_INITIALDATA_ARRAY, $_controller);
			$_controller = str_replace("__EXTERNALS_FOR_LIST__", $EXTERNALS_FOR_LIST, $_controller);
			$_controller = str_replace("__EXTERNALSFIELDS_FOR_FORM__", $EXTERNALSFIELDS_FOR_FORM, $_controller);
			$_controller = str_replace("__FIELDS_FOR_FORM__", $FIELDS_FOR_FORM, $_controller);

			$_controller = str_replace("__INSERT_QUERY_FIELDS__", $INSERT_QUERY_FIELDS, $_controller);
			$_controller = str_replace("__INSERT_QUERY_VALUES__", $INSERT_QUERY_VALUES, $_controller);
			$_controller = str_replace("__INSERT_EXECUTE_FIELDS__", $INSERT_EXECUTE_FIELDS, $_controller);

			$_controller = str_replace("__UPDATE_QUERY_FIELDS__", $UPDATE_QUERY_FIELDS, $_controller);
			$_controller = str_replace("__UPDATE_EXECUTE_FIELDS__", $UPDATE_EXECUTE_FIELDS, $_controller);
			$_controller = str_replace("/*__WHERE_CLAUSE_LIMITER__*/", $__WHERE_CLAUSE_LIMITER__, $_controller);
			
			//beforess
			$_controller = str_replace("/**__BEFORE_EXECUTE__UPDATE_EDIT__**/", $__BEFORE_EXECUTE__UPDATE_EDIT__, $_controller);
			$_controller = str_replace("/**__BEFORE_EXECUTE__UPDATE_CREATE__**/", $__BEFORE_EXECUTE__UPDATE_CREATE__, $_controller);
			
			
			//external field search enabler
			
			//$TRANSFORM_TEXT_TO_KEY_SEARCH="test###########";
			$_controller = str_replace("/**__TRANSFORM_TEXT_TO_KEY_SEARCH__**/", $TRANSFORM_TEXT_TO_KEY_SEARCH, $_controller);
			
			
			$_list_template = file_get_contents(__DIR__.'/../gen/list.html.twig');
			$_list_template = str_replace("__TABLENAME__", $TABLENAME, $_list_template);
			$_list_template = str_replace("__TABLENAMEUP__", ucfirst(strtolower($TABLENAME)), $_list_template);

			$_create_template = file_get_contents(__DIR__.'/../gen/create.html.twig');
			$_create_template = str_replace("__TABLENAME__", $TABLENAME, $_create_template);
			$_create_template = str_replace("__TABLENAMEUP__", ucfirst(strtolower($TABLENAME)), $_create_template);
			$_create_template = str_replace("__EDIT_FORM_TEMPLATE__", $EDIT_FORM_TEMPLATE, $_create_template);

		

			
			$_edit_template = file_get_contents(__DIR__.'/../gen/edit.html.twig');
			$_edit_template = str_replace("__TABLENAME__", $TABLENAME, $_edit_template);
			$_edit_template = str_replace("__TABLENAMEUP__", ucfirst(strtolower($TABLENAME)), $_edit_template);
			$_edit_template = str_replace("__EDIT_FORM_TEMPLATE__", $EDIT_FORM_TEMPLATE, $_edit_template);

			$_lang_template = file_get_contents(__DIR__.'/../gen/lang.php');
			$_lang_template = str_replace("/**__CRUD_WORDS_HERE__**/", $CRUD_WORDS_HERE, $_lang_template);

			
			
			if($date_crud_exist){
				$CSS_DATE_CRUD__INCLUDE=
				"{% block stylesheets %}\n".
				"{{parent()}}\n".
				"    <link href=\"{{ app['asset_path']}}/datepicker/css/jquery.datepick.css\" rel=\"stylesheet\">\n".
				"{% endblock %}\n";
				$_create_template = str_replace("<!--__CSS_DATE_CRUD__INCLUDE__-->", $CSS_DATE_CRUD__INCLUDE, $_create_template);
				$_edit_template = str_replace("<!--__CSS_DATE_CRUD__INCLUDE__-->", $CSS_DATE_CRUD__INCLUDE, $_edit_template);
			
			}else {
				$_create_template = str_replace("<!--__CSS_DATE_CRUD__INCLUDE__-->", "", $_create_template);
				$_edit_template = str_replace("<!--__CSS_DATE_CRUD__INCLUDE__-->", "", $_edit_template);
			
			}
			
			if($date_crud_exist){
				$datepicks=
					"      var dpopts= {dateFormat: 'yyyy-mm-dd'   \n".
					"                  };\n";
				echo "@todo on datepicks based on column and table and role \n";
				foreach ($date_columns as $dc){
					
					if ($stb=="usulan"&&strtolower($dc['name'])=="tanggal_usul")
					$datepicks.="\t{% if app['credentials']['current_role']=='Administrator'%}\n";
					$datepicks.="\t\t"."		$('#form_".$dc['name']."').datepick(dpopts);\n";
					if ($stb=="usulan"&&strtolower($dc['name'])=="tanggal_usul")
					$datepicks.="\t{% endif %}\n";
					
				}
				$JAVA_SCRIPT_DATE_CRUD__INCLUDE=
					"{% block javascripts %}\n".
					"	{{ parent() }}\n".
					"   <script src=\"{{ app['asset_path']}}/datepicker/js/jquery.plugin.min.js\"></script>\n".
					"	<script src=\"{{ app['asset_path']}}/datepicker/js/jquery.datepick.js\"></script>\n".
					"	<script src=\"{{ app['asset_path']}}/datepicker/js/jquery.datepick-id.js\"></script>\n".
					"	<script>\n".
					"	$(function() {\n".
					$datepicks.
					"   });\n".
					"	</script>\n".
					"{% endblock %}\n";

					$_create_template = str_replace("<!--__JAVA_SCRIPT_DATE_CRUD__INCLUDE__-->", $JAVA_SCRIPT_DATE_CRUD__INCLUDE, $_create_template);
					$_edit_template = str_replace("<!--__JAVA_SCRIPT_DATE_CRUD__INCLUDE__-->", $JAVA_SCRIPT_DATE_CRUD__INCLUDE, $_edit_template);
			
			}else {
				$_create_template = str_replace("<!--__JAVA_SCRIPT_DATE_CRUD__INCLUDE__-->", "", $_create_template);
				$_edit_template = str_replace("<!--__JAVA_SCRIPT_DATE_CRUD__INCLUDE__-->", "", $_edit_template);
			
			}
			
			
			
			$_menu_template = file_get_contents(__DIR__.'/../gen/menu.html.twig');
			$_menu_template = str_replace("__MENU_OPTIONS__", $MENU_OPTIONS, $_menu_template);

			$_base_file = file_get_contents(__DIR__.'/../gen/base.php');
			$_base_file = str_replace("__BASE_INCLUDES__", $BASE_INCLUDES, $_base_file);

			@mkdir(__DIR__."/../web/controllers/".$TABLENAME, 0755);
			@mkdir(__DIR__."/../web/views/".$TABLENAME, 0755);

			$fp = fopen(__DIR__."/../web/controllers/".$TABLENAME."/index.php", "w+");
			fwrite($fp, $_controller);
			fclose($fp);

			$fp = fopen(__DIR__."/../web/views/".$TABLENAME."/create.html.twig", "w+");
			fwrite($fp, $_create_template);
			fclose($fp);

			$fp = fopen(__DIR__."/../web/views/".$TABLENAME."/edit.html.twig", "w+");
			fwrite($fp, $_edit_template);
			fclose($fp);

			$fp = fopen(__DIR__."/../web/views/".$TABLENAME."/list.html.twig", "w+");
			fwrite($fp, $_list_template);
			fclose($fp);

			$fp = fopen(__DIR__."/../web/controllers/base.php", "w+");
			fwrite($fp, $_base_file);
			fclose($fp);

			$fp = fopen(__DIR__."/../web/views/menu.html.twig", "w+");
			fwrite($fp, $_menu_template);
			fclose($fp);
			
			/**uncommet to generate language template 
			   all words manually wriiten will lost
			**/
			/*$fp = fopen(__DIR__."/lang.php", "w+");
			fwrite($fp, $_lang_template);
			fclose($fp);
			*/

		}

});

return $console;
