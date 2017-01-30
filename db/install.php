<?php
//arrays that will be passed to define the tables depending of each database connection
$sql_table =array();
$tables = array();
global $db_driver;

$tables['user_log'] = array(
    'id' => array(
        'type' => 'int',
        'index' => 'primary',
        'autoincrement' => true
    ),
    'email' => array(
        'type' => 'char',
        'length' => 50,

        'index' => 'unique'
    ),
    'password' => array(
        'type' => 'char',
        'index' => 'btree',
        'length' => 150
    ),
    'email_validated' => array(
        'type' => 'boolean',
        'default' => false
    ),
    'validate_url' => array(
        'type' => 'char',
        'length' => 150
    ),
    'validate_url_date' => array(
        'type' => 'int',
        'length' => 50
    ),

);
$tables['user_capabilities'] = array(
    'id' => array(
        'type' => 'int',
        'index' => 'primary',
        'autoincrement' => true
    ),
    'log_id' => array(
        'type' => 'int',
        'length' => 5
    ),
    'permission' =>array(
        'type' => 'char',
        'length' => 30
    )
);
global $CONFIG;
$entities = $CONFIG->entities;
//var_dump($entities);
foreach ($entities as $entity_name)
{
    $tables[$entity_name.'_data'] = array
    (
        'id' => array(
            'type' => 'int',
            'index' => 'primary',
            'autoincrement' => true
        ),
        'log_id' => array(
            'type' => 'int',
            'length' => 5
        ),
        'field_id' => array(
            'type' => 'int',
            'length' => 5
        ),
        'value_field' => array(
            'type' => 'char',
            'length' => 100
        ),
        'entity_row_id' => array(
            'type' => 'char',
            'length' => 100
        )
    );
    $tables[$entity_name.'_fields'] = array(
        'id' => array(
            'type' => 'int',
            'index' => 'primary',
            'autoincrement' => true
        ),
        'name' => array(
            'type' => 'char',
            'length' => 30,
            'index'=>'unique'
        ),
        'field_type' => array(
            'type' => 'char',
            'length' => 30
        ),
        'displayed_report' => array(
            'type' => 'boolean',
            'default'=>false
        ),
        'displayed_form' => array(
            'type' => 'boolean',
            'default'=>false
        ),
        'displayed_export' => array(
            'type' => 'boolean',
            'default'=>false
        ),
        'other_values' =>array(
            'type'=> 'char',
            'length' => 255
        ),
        'sortorder' =>array(
            'type'=> 'int',
            'length' => 3
        ),
        'removed' =>array(
            'type'=> 'boolean',
            'default'=>false
        )
    );
}
;
?>