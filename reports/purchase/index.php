<?php
///INIZIALICE
session_start();
require_once(dirname(dirname((dirname(__FILE__)))).'/class/capability.php');
require_once(dirname(dirname((dirname(__FILE__)))).'/class/entity.php');
require_once(dirname(dirname((dirname(__FILE__)))).'/class/export.php');

global $user_logged;
$table_person=$pagination=$error=$data_form_filtered='';
$total_person_inThis_page=0;
$page='reports';
$entity = 'purchase';
$Fields = new Entity();
$export = new Export();
$Allowed_Fields =$Fields->get_Allfields_allowed($entity,'displayed_form');
$_SESSION['user_form_logged'] = $Fields->get_Allfields_data_of_userLogged('displayed_form');
if(!isset($_SESSION['report_form_search'])){$_SESSION['report_form_search']='';}
global $CONFIG;
$no_rows_to_show_in_table = $CONFIG->no_rows_to_show_in_table;

//WHO USER HAVE ACCESS TO REPORTS
$user_capability = new Capability();
$access_reports = false;
$access_reports = $user_capability->get_reporter();
if (!isset($_SESSION['user_log_id'])  || $access_reports==false) {
    header("Location: ../../log/signIn.php");
}

//TO CONTROL THE BUTTON PAGINATION ACTIONS AND INTRODUCE THE SQL LIMIT ROWS IN SESSION
if(isset($_GET['pagination']))
{
  //  $limit_rows = $db_driver->set_limit_rows($no_rows_to_show_in_table, $_GET['pagination']);
    $_SESSION['pagination']=   $_GET['pagination'];
}else{
   // $limit_rows = $db_driver->set_limit_rows($no_rows_to_show_in_table , 0);
    $_SESSION['pagination']=   0;
}
if(isset($_POST[$entity.'_btn-submit']))
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
    unset($_SESSION['report_form_search']);
}
$data_report = $db_driver->get_data_report($entity, "displayed_report=true");
//var_dump($data_report);
/////////////////////////////////TABLE///////////////////////
//require ('../../tools/pagination.php');


?>
<html>
<head>
    <style>
        .verticalLine {
            border-left: thick solid black;
            border-left-width: 2px;
        }
    </style>
<?php $link='../../'; require_once('../../navbar/navbar.html'); ?>
</head>
<body>
<?php require_once ('../../navbar/index.html'); ?>
<div class="container-fluid">

    <div class="col-sm-5 col-md-6">
        <p class='h1 container-fluid'><?php echo("Entities"); ?></p>
        <table class='table table-striped table-bordered table-hover table-sm table-responsive'>
            <tr>
                <td>
                    <?php
                        echo $db_driver->print_out_table_report($entity, $data_report);
                        echo ("<br>");
                        echo ($pagination);
                       // echo ($totals);
                    ?>
                </td>
            </tr>
        </table>
    </div>

    <div class="col-sm-5 col-sm-offset-2 col-md-6 col-md-offset-0">
        <p class='h1 container-fluid'><?php echo("Filters"); ?></p>
        <table class='table table-striped table-bordered table-hover table-sm table-responsive'>
            <tr>
                <td class="verticalLine">
                    <?php
                    //var_dump($_SESSION);
                    if ($data_report[$entity.'_total']!=0) {
                        $array_form_errors = array('first_name' => '', 'last_name' => '', 'sex' => '', 'address1' => '', 'city' => '', 'county' => '', 'country' => '', 'phone1' => '', 'phone2' => '');
                        require_once(dirname(dirname(dirname(__FILE__))) . '/tools/form.php');
                        ?>
                        <form id="form-clear_report' action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post"  >
                            <button class="form-control btn btn-default  btn-sm" name='btn-clear_report' type="submit">Clear</button>
                        </form>

                       <?php echo ($export->html_display_export_form()); ?>

                        <?php
                    }else{
                        echo ("You must to have at least one field and data ");
                    }
                    ?>
                </td>
            </tr>
        </table>
        </div>

    </div>
</body>
</html>
