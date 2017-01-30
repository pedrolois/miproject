<?php
session_start();
require_once(dirname((dirname(__FILE__))).'/class/entity.php');
require_once(dirname((dirname(__FILE__))).'/class/fields.php');
$list_types=$error=$error_in_add=$list_objects_to_add='';
$user = new Entity();
$data_fields = Field::get_field_instance();
$user_capability = new Capability();
$access_customization = $user_capability->get_admin();
$page = 'customization';
if (!isset($_SESSION['user_log_id'])  || ($access_customization == false)) 
{
    header("Location: ../log/signIn.php");
}
///types of columns to add
$array_types = array('Text', 'Integer', 'Boolean','Textarea','Checkbox','Radio', 'Select');
$list_persons = $CONFIG->entities;
$list_types .= "<option value=''>Choose one</option>";
foreach ($array_types as $type)
{
    $list_types .= "<option value='$type'>$type</option>";
}
//BUTTON TO SAVE a new field
if(isset($_POST['btn-submit_field_details']))
{
    if ($_POST['name_column']!='')
    {
        foreach ($entities as $entity){
            if ($entity==$_POST['object_to_add'])
            {
                 $_SESSION['field_parameters_added'] += $_POST;
                 $data_fields->create_field($entity); 
            }
        }       
    }else{
        $error_in_add = ("You must to introduce a name column");
    }
}

?>
<html>
<head>
<?php $link="../"; require_once('../navbar/navbar.html'); ?>
    <script src='../js/load_select_options.js'></script>    
</head>
<body>
<?php require_once('../navbar/index.html'); ?>
<div class="container-fluid">
    <div class="col-sm-5 col-md-6">
        <p class='h1 container-fluid'><?php echo("Entities"); ?></p>

        <table class='table table-striped table-bordered table-hover table-sm table-responsive'>
            <tr>
                <td>
                    <?php require_once(dirname(__FILE__). '/order_table.php');?>
                </td>
            </tr>
         </table>

     </div>
    <div class="col-sm-5 col-sm-offset-2 col-md-6 col-md-offset-0">
        <p class='h1 container-fluid'><?php echo("Add column"); ?></p>
        <table class="table">
            <tr>
                <td>
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                <h1 class="'"><?php echo($error_in_add); ?></h1>
                <?php echo($list_objects_to_add);?>
                Add Column:
                <select name='field_type' required>
                    <?php echo ($list_types);?>
                </select>
                <button type='submit' name='btn-submit_field'>Submit</button>
            </form>

            <?php
            //BUTTON TO ADD THE TYPE FIELD
            if(isset($_POST['btn-submit_field']))
            {
                    unset ($_SESSION['field_parameters_edit']);
                    $_SESSION['field_parameters_added'] = $_POST;
                    $data_fields = Field::get_field_instance();
                    echo("<fieldset  style='width: 350px;' align='center'>
                        <legend>POSSIBLE VALUES TO ADD IN " . strtoupper($_POST['field_type']) . ": </legend>");
                    echo("<form action='" . $_SERVER['PHP_SELF'] . "'  method='post'>");
                    echo($data_fields->display_HTML('client')); //because is client by default
                    echo("<button name='btn-submit_field_details' type='submit'>Save</button>");
                    echo("</form>");
                    echo("</fieldset>");
            }
            ?>

        </td>
    </tr>

</div>
</div>
</body>
</html>
