<?php
session_start();
if (!isset($_SESSION['user_log_id'])) 
{
    header("Location: /index.php");
}
$entity=$page=$_GET['ent'];
if (!isset($entity))
{
    $entity=$page='client';
}
$link="../";
require_once dirname(dirname(__FILE__)).'/class/entity.php';
require_once dirname(dirname(__FILE__)).'/class/fields.php';
$client = new Entity();
// when submit the form is creating a new user object
if(isset($_POST[$entity.'_btn-submit']))
{
    require_once dirname(dirname(__FILE__)).'/save_entity.php';
    if (!$error)
    {
        $client->insert_entity_data($entity.'_data',$array_form_person);
        echo ("<h3>".ucfirst($entity)." added<h3>");
    }
}
?>
<html>
<head>
    <?php $link='../'; require_once('../navbar/navbar.html'); ?>
</head>
<body>
<?php require_once('../navbar/index.html'); ?>
<div class="row container-fluid">

    <div class="col-sm-5 col-md-6">
        <p class='h1 container-fluid'><?php echo("New ".ucfirst($page)); ?></p>
        <table class='table table-striped table-bordered table-hover table-sm table-responsive'>
            <tr>
                <td>
                    <?php
                    $Allowed_Fields = $client->get_Allfields_allowed($entity,'displayed_form');
                    require_once dirname(dirname(__FILE__)).'/tools/form.php';
                    ?>
                </td>
            </tr>
        </table>
    </div>

    <div class="col-sm-5 col-sm-offset-2 col-md-6 col-md-offset-0">
        <p class='h1 container-fluid'><?php echo(ucfirst(results)); ?></p>
        <table class='table table-striped table-bordered table-hover table-sm table-responsive'>
            <tr>
                <td>
                    <?php
                    // var_dump($_SESSION);
                    unset($_SESSION['report_form_search']);
                    $data_report = $db_driver->get_data_report($entity, "displayed_report=true");
                    echo $db_driver->print_out_table_report($entity, $data_report);
                    //var_dump($data_report);
                    ?>
                </td>
            </tr>
        </table>
    </div>
</div>
</body>
</html>



