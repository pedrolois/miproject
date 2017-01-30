<?php

function display($array){
    echo "<pre>";
    print_r($array);
    //var_dump($array);
}

global $db_connect;

require_once dirname(__FILE__).'/db/lib.php';
$db_driver = database::get_driver_instance();
$db_connect = $db_driver->connect();
require_once dirname(__FILE__).'/db/install.php';
//display($tables);
$db_driver->create_tables($tables);
require_once dirname(__FILE__).'/class/capability.php';
$super_admin = new Capability();
$super_admin->create_super_admin_capability();
//require_once dirname(__FILE__).'/db/queries_test.php';

