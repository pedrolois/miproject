<?php
require_once(dirname(dirname(__FILE__)).'/class/entity.php');


//INSERT RAMDOM DATA

$LOG_USER = new Entity();/*
$LOG_USER->insert_new_log_user ("pedroljaen@gmail.com", "2858426273496395786786advhjsfkadhjsgf567", "auyfgasyudgfyai5632735jhfgabdzsfljg");
$LOG_USER->insert_new_log_user ("pedroljaen2@gmail.com", "2858426273496395786786advhjsfkadhjsgfqeqew567", "auyfgasyudgfyai2342345632735jhfgabdzsfljg");*/
/*
$ARRAY_FORM['first_name']="PEDRO5";
$ARRAY_FORM['middle_name']="Luis";
$ARRAY_FORM['last_name']="gARCIA";
$ARRAY_FORM['sex']="male";
$ARRAY_FORM['address1']="Real";
$ARRAY_FORM['address2']="Real 3";
$ARRAY_FORM['city']="Brighton";
$ARRAY_FORM['county']="otro";
$ARRAY_FORM['country']="Spain";
$ARRAY_FORM['phone1']="07460460064";
$ARRAY_FORM['phone2']="074604600643";
$_SESSION['user_log_id']="1";
$LOG_USER->create_user($ARRAY_FORM);
$ARRAY_FORM['first_name']="Manolo5";
$ARRAY_FORM['middle_name']="Garleiva";
$ARRAY_FORM['last_name']="leiva";
$ARRAY_FORM['sex']="female";
$ARRAY_FORM['address1']="Moral";
$ARRAY_FORM['address2']="Real 3asd";
$ARRAY_FORM['city']="London";
$ARRAY_FORM['county']="otro";
$ARRAY_FORM['country']="UK";
$ARRAY_FORM['phone1']="07460463450064";
$ARRAY_FORM['phone2']="0746044600643";
$_SESSION['user_log_id']="2";

$LOG_USER->create_user($ARRAY_FORM);
$ARRAY_FORM['first_name']="Manolo10";
$ARRAY_FORM['middle_name']="Garleiva";
$ARRAY_FORM['last_name']="leiva";
$ARRAY_FORM['sex']="female";
$ARRAY_FORM['address1']="Moral";
$ARRAY_FORM['address2']="Real 3asd";
$ARRAY_FORM['city']="London";
$ARRAY_FORM['county']="otro";
$ARRAY_FORM['country']="UK";
$ARRAY_FORM['phone1']="63456456456";
$ARRAY_FORM['phone2']="0746044600643";
$_SESSION['user_log_id']="2";

$LOG_USER->create_user($ARRAY_FORM);
*/

//CREATING A QUERY WITHOUT CHECK ANYTHING

/*$SQL = $db_driver->create_sql_query("SELECT * FROM users");
display($SQL);*/


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//EXAMPLES OF QUERIES
//----------------------------------------------------------------------------------------------------------//


//SIMPLE ONE WITH PARAMETER
//$q1 = $db_driver->get_record('users_log', array('*'=>'*'), array('email'=>'PeDroljaen@gmail.com'));


//CONCATENATION EXAMPLE
//$db_driver->get_record('users', array($db_driver->concatenation("first_name, middle_name, last_name"," ")=>'user_full_name'), array("first_name"=>'Pedro'));


//CONCATENATION WITH JOINS
/*$tablesToJOin[] = array('LEFT JOIN'=>(array( 'users'=>'log_id', 'users_log'=>'id' )));
$tablesToJOin[] = array('LEFT JOIN'=>(array('users'=>'extradata_id', 'users_extradata'=>'id')));
$db_driver->get_record($tablesToJOin, array($db_driver->concatenation("first_name, middle_name, last_name"," ")=>'user_full_name', "extradata"=>'greeting'), array("first_name"=>'Pedro'));*/
/*
$tablesToJOin[] = array('LEFT JOIN'=>(array( 'users'=>'log_id', 'users_log'=>'id' )));
$tablesToJOin[] = array('LEFT JOIN'=>(array('users'=>'extradata_id', 'users_extradata'=>'id')));
$db_driver->get_record($tablesToJOin, array("*"=>'*', "extradata"=>'greeting'), array("first_name"=>'Pedro'));*/

//MORE THAN ONE ROW
//$q2 = $db_driver->get_records('users',array('*'=>'*'), array('first_name'=>'pedro'));


//UPDATE A ROW WITH PARAMETERS
//$db_driver->update_record('users_log', array("email_validated"=>'true'),  array("email"=>'pedroljaen@gmail.com'));

//UPDATE A ROW WITH MORE THAN ONE PARAMETER WITH MORE THAN A TABLE
/*$tablesToJOin[] = array('LEFT JOIN'=>(array( 'users'=>'log_id', 'users_log'=>'id' )));
$tablesToJOin[] = array('LEFT JOIN'=>(array('users'=>'extradata_id', 'users_extradata'=>'id')));
$db_driver->update_record($tablesToJOin, array("email_validated"=>'true'),  array("email"=>'pedroljaen@gmail.com'), 'users_log');*/

//REMOVING A ROW WITH A PARAMETER
//$db_driver->delete_record('users_log', array('email'=>'pedroljaen@gmail.com'));



//REMOVING A ROW with MORE THAN A PARAMETER IN 2 TABLES (CASCADE REMOVE)
/*$tablesToJOin[] = array('LEFT JOIN'=>(array( 'users'=>'log_id', 'users_log'=>'id' )));
$tablesToJOin[] = array('LEFT JOIN'=>(array('users'=>'extradata_id', 'users_extradata'=>'id')));
$db_driver->delete_records_cascade($tablesToJOin, array('email'=>'pedroljaen@gmail.com', 'first_name'=>'pedro'));*/


//ALTER TABLES
//$db_driver->alter_table('alter_column','re_set', 'users', 'first_name', 'NOT NULL');

//$db_driver->alter_table('alter_column','re_type', 'users', 'first_name', 'varchar(80)');
//$db_driver->alter_table('alter_column','re_type', 'users', 'first_name', 'character varying');

//$db_driver->alter_table('alter_column','re_name', 'users', 'first_name', 'first_nameeeee');
//$db_driver->alter_table('alter_column','re_name', 'users', 'first_nameeeee', 'first_name');






?>