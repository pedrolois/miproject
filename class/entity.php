<?php

require_once(dirname(dirname(__FILE__)).'/config.php');

class Entity {

    function __construct(){

    }

    public function upsert_entity($table, $columns) {
        global $db_driver;
    //if there is already a user form created, just update him/her
          $db_driver->upsert($table, $columns);
    }
    public function insert_entity_data($entity, $parameters)
    {
        global $db_driver;
        $max_actual_entity =  $db_driver->get_record($entity, array('max(entity_row_id)'=>'max_id'));
        if (!isset($max_actual_entity) || empty($max_actual_entity))
        {
            $max_actual_entity = 0;
        }
        foreach ($parameters as $field_id=>$value_field)
        {           
            $parameters_sql ['log_id'] = $_SESSION['user_log_id'];
            $parameters_sql ['field_id'] = $field_id;
            $parameters_sql ['value_field'] = $value_field;
            $parameters_sql ['entity_row_id'] = $max_actual_entity['max_id']+1; //this is to add a new entity row of data eg: a product row
            $db_driver->add_record($entity, $parameters_sql);
        }
        
       
    }
    //get user data form
    public function get_user_data()
    {
        global $db;

        $stmt = $db->prepare("SELECT * FROM user_log 
        LEFT JOIN user_data ON (user_data.log_id = user_log.id)       
        WHERE user_log.id = :log_id");
        //var_dump( $_SESSION['user_log_id']);
        $stmt->bindValue(':log_id', $_SESSION['user_log_id']);
        //var_dump($stmt);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
/*
        echo "<pre>";
        print_r($row);*/
        if ($row) {
            foreach ($row as $key=>$r) {
                $_POST[$key]=$r;
            }
        }
        //var_dump($row);
        return $row;
    }
    //get user data log base on an unique email
    public function get_user_data_with_email($email){
        global $db;

        $stmt = $db->prepare("SELECT * FROM user_log   WHERE email = :email");
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = $stmt->rowCount();

        $array_data['row'] = $row;
        $array_data['count'] = $count;

        return $array_data;

    }
    //create a new user account
    function insert_new_log_user ($email, $password, $email_hashed){
        global $db;

        $stmt = $db->prepare("INSERT INTO user_log (email, password, validate_url, validate_url_date) VALUES (:email, :pass, :email_hashed, :timestamped)");
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':pass', $password, PDO::PARAM_STR);
        $stmt->bindValue(':email_hashed', $email_hashed, PDO::PARAM_STR);
        $stmt->bindValue(':timestamped', time(), PDO::PARAM_INT);

        $if_executed = $stmt->execute()? true:false;
        return $if_executed;
    }
    //get all fields of a user
    public function get_Allfields_allowed($entity, $site_to_show)
    {
        global $db;
        
        $stmt = $db->prepare("SELECT * FROM ".$entity."_fields "
                . "WHERE ".$site_to_show."=:".$site_to_show." "
                . "AND removed=false "
                . "ORDER BY ".$entity."_fields.sortorder ASC");
        $stmt->bindValue(':'.$site_to_show, true);
       // var_dump($stmt);
        $stmt->execute();
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $row;
    }
    public function get_Allfields_data_of_userLogged($site_to_show)
    {
        global $db;

        $stmt = $db->prepare("SELECT user_data.* "
                . "FROM user_data "
                . "RIGHT JOIN user_fields ON (user_data.field_id = user_fields.id)  "
                . "WHERE ".$site_to_show."=:".$site_to_show." "
                . "AND (log_id=:log_id or log_id IS NULL) "
                . "ORDER BY user_fields.sortorder ASC");

        $stmt->bindValue(':'.$site_to_show, true);
        $stmt->bindValue(':log_id', $_SESSION['user_log_id']);
        //ECHO $site_to_show;
        $stmt->execute();
         //var_dump($stmt);
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);

        //var_dump($row);
        return $row;
    }

}