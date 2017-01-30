<?php
session_start();
if (!isset($_SESSION['user_log_id'])) {
    header("Location: /index.php");
}
$page='home';
$entity = 'user';
$link='';
require_once(dirname(__FILE__).'/class/entity.php');
require_once(dirname(__FILE__).'/class/fields.php');
$user = new Entity();
unset($_SESSION['field_parameters_added']);
unset($_SESSION['field_parameters_edit']);

//
//$array_form_errors = array('first_name'=>'', 'last_name'=>'','sex'=>'', 'address1'=>'', 'city'=>'', 'county'=>'', 'country'=>'', 'phone1'=>'', 'phone2'=>'' );
// when submit the form is creating a new user object
if(isset($_POST[$entity.'_btn-submit']))
{
    require_once(dirname(__FILE__).'/save_entity.php');
 if (!$error)
 {
  $user->upsert_entity('user_data',$array_form_person);
 }
}
//global $user_logged;
$user_logged = $user ->get_user_data(); //calling to this function is loading the $_POST variable to show it in the form (just in case have data in)
/*echo "<pre>";
print_r($_POST);*/

?>
<html>
<head>
<?php require_once(dirname(__FILE__).'/navbar/navbar.html'); ?>
</head>
<body>
<?php require_once(dirname(__FILE__).'/navbar/index.html'); ?>
    
    <h2>Profile</h2>
<table>
    <tr>
        <td class="verticalLine">
            <?php $Allowed_Fields = $user->get_Allfields_allowed('user', 'displayed_form');
            $_SESSION['user_form_logged'] = $user->get_Allfields_data_of_userLogged('displayed_form');
            require_once(dirname(__FILE__) . '/tools/form.php');?>
        </td>
    </tr>
</table>
</body>

</html>

