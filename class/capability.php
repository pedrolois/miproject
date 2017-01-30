<?php
require_once(dirname(dirname(__FILE__)).'/config.php');
require_once((dirname(__FILE__)).'/entity.php');
require_once(dirname(dirname(__FILE__)).'/db/lib.php');

class Capability
{

    function __construct()
    {

    }
    public function create_super_admin_capability()
    {
        //this is to create a super_admin capability with full access to the system
    global $db_driver; global $CONFIG;global $db;
        $user_log_id = $db_driver->get_record('user_log', 'id', array('email'=>$CONFIG->admin_email));
        if(empty($user_log_id['id']))
        {
            echo ("Error NO SUPER ADMIN: The system don't have any log account created with this email yet-> ".$CONFIG->admin_email.". Please sign Up an account with this email");
            //header("Location: ../log/SignIn.php");
        }else
        {
            $super_admin_capability = $this->get_superadmin(); //check if there is one user already with super_admin capability
            if (empty($super_admin_capability))
            { //if there is not one, then createa  a super_admin_user
                    $stmt = $db->prepare("INSERT INTO user_capabilities (log_id, permission) VALUES (:log_id, :permission) ");
                    $stmt->bindValue(':log_id', $user_log_id['id']);
                    $stmt->bindValue(':permission', 'super_admin');
                    $stmt->execute();
            }
        }

    }
    public function get_superadmin()
    {
        global $db_driver;
        $row_superadmin_is_created = $db_driver->get_records('user_capabilities', '*', array('permission'=>'super_admin'));
        return $row_superadmin_is_created;
    }
    public function get_admin()
    {
        global $db_driver;
        $access = false;
        $permissions = $db_driver->get_records('user_capabilities', 'permission', array('log_id'=>$_SESSION['user_log_id']));
        //var_dump($permissions);
        foreach ($permissions as $capability)
        {
            //var_dump($capability);
            if($capability['permission'] =='admin' || $capability['permission'] =='super_admin')
            {
                $access= true;
            }
        }
     //   var_dump($access);
        return $access;
    }
    public function get_reporter()
    {
        global $db_driver;
        $access = false;
        $permissions = $db_driver->get_records('user_capabilities', 'permission', array('log_id'=>$_SESSION['user_log_id']));

        foreach ($permissions as $capability)
        {
            if($capability['permission'] =='reporter' || $capability['permission'] =='super_admin')
            {
                $access= true;
            }
        }
        return $access;
    }

}