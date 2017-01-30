<?php
//<?php
global $CONFIG;
$CONFIG = new stdClass();
$CONFIG->admin_email = 'pedroljaen@gmail.com'; //used for the super_admin role as well
$CONFIG->no_rows_to_show_in_table=3;
$CONFIG->debug = true;
$CONFIG->db_name = 'miofficeDB';
$CONFIG->site = 'http://mioffice.local';
//$CONFIG->site = 'http://localhost/mioffice';
$CONFIG->host = 'localhost';
//-------------postgreSQL---------------------------///
//$CONFIG->type_database = 'pgsql';
//$CONFIG->user = 'postgres';
//$CONFIG->psw = 'kineo2323';
//$CONFIG->database_instance = 'postgreSQL';
//$CONFIG->port = '5433';

//-----------------mySQL---------------------------///
$CONFIG->type_database = 'mysql';
$CONFIG->user = 'root';
$CONFIG->psw = '';
$CONFIG->database_instance = 'mySQL';
$CONFIG->entities = array(0=>'user', 1=>'client', 2=>'product', 3=>'purchase');
//$CONFIG->port = '3306';	
require_once dirname(__FILE__).'/setup.php';





