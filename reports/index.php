<?php
///INIZIALICE
session_start();
require_once(dirname((dirname(__FILE__))).'/class/capability.php');
require_once(dirname((dirname(__FILE__))).'/class/entity.php');

global $user_logged;
$table_users=$pagination=$error=$data_form_filtered='';
$total_users_inThis_page=0;
$page='reports';
$entity = 'user';
$userFields = new Entity();
$Allowed_Fields =$userFields->get_Allfields_allowed('user','displayed_form');
$_SESSION['user_form_logged'] = $userFields->get_Allfields_data_of_userLogged('displayed_form');

if(!isset($_SESSION['report_form_search'])){$_SESSION['report_form_search']='';}
global $CONFIG;
$no_rows_to_show_in_table = $CONFIG->no_rows_to_show_in_table;

//WHO USER HAVE ACCESS TO REPORTS
$user_capability = new Capability();
$access_reports = false;
$access_reports = $user_capability->get_reporter();
if (!isset($_SESSION['user_log_id'])  || $access_reports==false) {
    header("Location: ../log/signIn.php");
}

//TO CONTROL THE BUTTON PAGINATION ACTIONS AND INTRODUCE THE SQL LIMIT ROWS IN SESSION
if(isset($_GET['pagination']))
{
    //$limit_rows = $db_driver->set_limit_rows($no_rows_to_show_in_table, $_GET['pagination']);
    $_SESSION['pagination']=   $_GET['pagination'];
}else{
    //$limit_rows = $db_driver->set_limit_rows($no_rows_to_show_in_table , 0);
    $_GET['pagination']=0;
    $_SESSION['pagination']=   $_GET['pagination'];
}
//$_SESSION['limit_rows']= $limit_rows;
//echo $limit_rows;
if(isset($_POST['btn-user_submit']))
{
    if (!$error) {
        //var_dump($_SESSION);
        foreach ($_POST as $name_field => $value_field)
        {
            if ($value_field != '')
            {
                $data_form_filtered [$name_field] = $value_field;
            }
        }
        $_SESSION['report_form_search'] = $data_form_filtered;
        //var_dump($_SESSION);
        unset($_SESSION['user_form_logged']);
    }
}

if(isset($_POST['btn-clear_report']))
{
    unset($_SESSION['user_form_logged']);
}

$data_report = $db_driver->get_data_report($userFields, "displayed_report=true");

/////////////////////////////////TABLE///////////////////////
require ('../tools/pagination.php');


?>
<html>
<head>
    <style>
        .verticalLine {
            border-left: thick solid black;
            border-left-width: 2px;
        }
    </style>
<?php $link='../'; require_once('../navbar/navbar.html'); ?>
</head>
<body>
<?php require_once ('../navbar/index.html'); ?>
<table>
    <tr>
        <td>
            <?php
                echo $db_driver->print_out_table_report($data_report);
                echo ("<br>");
                echo ($pagination);
                echo ($totals);
            ?>
        </td>
        <td class="verticalLine">
            <?php

            if ($data_report['users_total']!=0) {
                $array_form_errors = array('first_name' => '', 'last_name' => '', 'sex' => '', 'address1' => '', 'city' => '', 'county' => '', 'country' => '', 'phone1' => '', 'phone2' => '');
                require_once(dirname((dirname(__FILE__))) . '/tools/form.php');
                ?>
                <form id="form-clear_report# action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post"  >
                <button name='btn-clear_report' type="submit">Clear</button>
                </form>
                Export as :
                <form name="form-export_document" action="../export/index.php" method="post">
                    <select name="select-export_report">
                        <option name=""></option>
                        <option name="CSV" value="CSV">CSV</option>
                        <option name="XLS" value="XLS">XLS</option>
                        <option name="DOC" value="DOC">DOC</option>
                        <option name="PDF_portrait" value="PDF_portrait">PDF (Portrait)</option>
                        <option name="PDF_lasndscape" value="PDF_landscape">PDF (Landsacape)</option>
                        <option name="XML" value="XML">XML</option>
                    </select>
                    <button type="submit" name="btn-export_document">Export</button>
                </form>
                <?php
            }else{
                echo ("You must to have at least one field and data ");
            }
            ?>
        </td>
    </tr>
</table>
</body>
</html>
