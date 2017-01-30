<?php
session_start();
if (!isset($_SESSION['user_log_id'])) {
    header("Location: /log/signIn.php");
}
require_once(dirname((dirname(__FILE__))).'/class/capability.php');
require_once(dirname((dirname(__FILE__))).'/class/entity.php');
global $user_logged;
global $db_driver;
// if there is not as user to introduced in search input field then search with your actual login email
//$user_log_data = $db_driver->get_record('user_log', 'email', array('id'=>$_SESSION['user_log_id']));
//SEARCH BASE ON AN EMAIL
$email_to_search =array("permission"=>'is not null');
$email_to_search['email']=$string_users_capabilities=$error_capability='';
$comparison_database ="!=";
$page='admin';
$userslog_capabilities_Join[] = array('LEFT JOIN'=>(array( 'user_log'=>'id', 'user_capabilities'=>'log_id' )));
if (isset($_POST['btnSubmit-search_email'])){
        $email_to_search = array("email" => $_POST["search_imput_field"]);
        $comparison_database ="=";
}

//REMOVE CAPABILITY
if (isset($_POST['btnSubmit-removeCapability'])){
    $db_driver->delete_record('capabilities',array('id'=>(int)$_POST['btnSubmit-removeCapability']));
    unset($_POST['btnSubmit-removeCapability']);
}
//ADD CAPABILITY
    if (isset($_POST['btnSubmit-add_capability'])) {
        //var_dump($_POST['email_sended']);
        //echo"here";
        $user_log_data_and_capabilities = $db_driver->get_records($userslog_capabilities_Join, array('permission' => 'permission', 'user_log.id' => 'log_users_id'), array('email' => $_POST['email_sended']), '=', false);
        //var_dump($user_log_data_and_capabilities);
        foreach ($user_log_data_and_capabilities as $capability) {
            $string_users_capabilities [$capability['permission']]= $capability['permission'];
        }
        //var_dump($string_users_capabilities);
        if($string_users_capabilities) {
            if (in_array($_POST['select-capability'], $string_users_capabilities)) {
                $error_capability = "ERROR: This user has this capability already<br>";
            } else {
                 $db_driver->add_record('user_capabilities', array('log_id' => $user_log_data_and_capabilities[0]['log_users_id'], 'permission' => $_POST['select-capability']));
            }
        }else{
            echo ("ERROR: with this email");
        }

    }


$table_columns = $db_driver->get_records($userslog_capabilities_Join, array("email"=>'email', "permission"=>'permission', "user_capabilities.id"=>'capability_id'),'', $comparison_database, false);
$error_no_email = $table_columns_html='';

if(!empty($table_columns)) {
    foreach ($table_columns as $key => $column) {
        $table_columns_html .= "<tr>";
        $table_columns_html .= "<form id='form-remove_capability' action='" . $_SERVER['PHP_SELF'] . "' method='POST'>";
        $table_columns_html .= "<td>" . $table_columns[$key]['email'] . "</td>";
        $table_columns_html .= "<td>" . $table_columns[$key]['permission'] . "</td>";
        if ($table_columns[$key]['permission'] != 'super_admin') {
            $table_columns_html .= "<td><button name='btnSubmit-removeCapability' value='" . $table_columns[$key]['capability_id'] . "' >Remove</button></td>";
        }
        $table_columns_html .= "</form>";
        $table_columns_html .= "</tr>";
    }
}else{
    $error_no_email = ("There is not any data with this email ");
}

?>
<html>
<head>
<?php $link='../';require_once('../navbar/navbar.html'); ?>
</head>
<body>
<?php require_once('../navbar/index.html'); ?>
    
    <form id="form-search_user_email" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
        <div> Search for a user by Email:
            <input placeholder="Click to Search" name="search_imput_field" value="<?php echo ((isset($_POST['email_sended']))?$_POST['email_sended']:'');?>"></input>
            <button type="submit" name="btnSubmit-search_email" >Search</button>
            <button type="submit" name="btnSubmit-clear" >Clear</button>
            <?php echo($error_no_email); ?>
        </div>
    </form>
    <?php if (!empty ($email_to_search['email']) && empty($error_no_email)){ ?>
        <form id="form-add_capability" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
            <?php echo($email_to_search['email']); ?> <!-- email to add the capability -->
            <div>Capability:
            <select name='select-capability'>
                <option value="admin" >Admin</option>
                <option value="super_admin">Super Admin</option>
                <option value="reporter">Reporter</option>
            </select>
             <input name='email_sended' type="hidden" value="<?php echo($email_to_search['email']); ?>">
                <button type="submit" name="btnSubmit-add_capability" >Add</button>

            </div>
        </form>
    <?php } ?>
    <?php echo ($error_capability); ?>
    <table>
        <tr><thead>USER CAPABILITIES RESULT</thead></tr>
        <tr>
            <th>Email</th><th>Capability</th><th>Options</th>
        </tr>
           <?php echo ($table_columns_html); ?>
    </table>
</body>
</html>
