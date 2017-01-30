<?php
require_once(dirname((dirname(__FILE__))).'/class/fields.php');
if (!empty($_SESSION['report_form_search']))
{
    $_POST=$_SESSION['report_form_search'];
}
//echo ("---------------------------------------------------------------------------");
$html='';
$counter=0;
foreach ($Allowed_Fields as $field){
    $json = '{'.$field['other_values'].'}';
    $other_parameters = json_decode($json, true);
    $required = $length = $options_radio=$options_checkbox=$options_select='';
    if (isset($other_parameters) || !empty($other_parameters))
    {
        foreach ($other_parameters as $name_parameter=>$parameter)
        {
            switch ($name_parameter)
            {
                case 'required':
                    // no required fields in reports page (avoid problems in the filters)
                    if (strstr($_SERVER['REQUEST_URI'],'reports')){
                        $required = " ";
                    }else{
                        $required = " required ";
                    }
                    break;
                case 'length':
                    if ($parameter!=0) {
                        $length = " maxlength='" . $parameter . "' ";
                    }
                    break;
                case 'table_link':    
                case 'options':
                    //var_dump($field['field_type']);
                    if($field['field_type']=='radio')
                    {
                        $options_radio .="<fieldset class='table-bordered form-group'><legend  class='border'>".$field['name']."</legend>";

                            foreach($parameter as $radio_name=>$default_radio)
                            {
                                if (!empty($_SESSION['user_form_logged'][$counter]['value_field']) 
                                        || isset($_SESSION['user_form_logged'][$counter]['value_field'])){
                                    $fill_if_field_has_data_radio = ((($_SESSION['user_form_logged'][$counter]['field_id'])==$field['id'] 
                                            && strtolower($_SESSION['user_form_logged'][$counter]['value_field'])==strtolower($radio_name))?'checked':'');
                                }else{
                                    $fill_if_field_has_data_radio='';
                                }
                                $options_radio .="<div class='radio-inline'>";
                                $options_radio .="<label><input  type='radio' name='".$field['id']."' value='$radio_name' "
                                        . " $fill_if_field_has_data_radio>".$radio_name."</input></label>";
                                $options_radio .="</div>";
                            }

                        $options_radio.="</fieldset><br>";

                        
                    }elseif($field['field_type']=='checkbox')
                    {
                        $options_checkbox .="<fieldset><legend>".$field['name']."</legend>";
                        
                        foreach($parameter as $checkbox_name=>$default_checkbox)
                        {
                            if (!empty($_SESSION['user_form_logged'][$counter]['value_field']) 
                                    && isset($_SESSION['user_form_logged'][$counter]['value_field'])){
                                $fill_if_field_has_data_box = ((
                                    ($_SESSION['user_form_logged'][$counter]['field_id'])==$field['id']
                                    && strstr (strtolower($_SESSION['user_form_logged'][$counter]['value_field']),
                                    strtolower($checkbox_name))?'checked':''));
                            }else{
                                $fill_if_field_has_data_box='';
                            }
                            $options_checkbox .="<label><input type='checkbox' name='".$field['id']."[]' value='$checkbox_name' $fill_if_field_has_data_box>".$checkbox_name."</input></label><br>";
                        }
                        $options_checkbox.="</fieldset>";
                    }elseif($field['field_type']=='select')
                    {
                        $options_select .="<label>".ucfirst($field['name']).":</label><select name='".$field['id']."' class='form-control'>";
                        $values_linked = $db_driver->get_records(key($parameter)."_data","*",
                                        array("field_id"=>$parameter[key($parameter)]));
                        foreach($parameter as $option_name=>$default_option)
                        {
                                //This is a normal select 
                            if ($name_parameter=='options')
                            {
                                $options_select .="<option name='".$field['id']."' value='' ></option>";
                                if (!empty($_SESSION['user_form_logged'][$counter]['value_field']) 
                                    && isset($_SESSION['user_form_logged'][$counter]['value_field'])){
                                    $fill_if_field_has_data_select = ((($_SESSION['user_form_logged'][$counter]['field_id'])==$field['id'] 
                                    && strtolower($_SESSION['user_form_logged'][$counter]['value_field'])==strtolower($option_name))?'selected':'');
                                }else{
                                    $fill_if_field_has_data_select = '';
                                }

                                $options_select .="<option name='".$field['id']."' value='".$option_name."' "
                                        . "$fill_if_field_has_data_select>".$option_name."</option>";
                              //This is a link selected to other table field
                            }elseif ($name_parameter=='table_link'){
                            
                                $all_data_table_of_table_linked = "";
                                 //get values of the table linked
                                $options_select .="<option value=''>Select</option>";
                                foreach ($values_linked as $value){
                                    $options_select .="<option name='".$field['id']."' value='".$value['value_field']."' "
                                        . ">".$value['value_field']."</option>";
                                }                            
                            }
                        }
                        $options_select.="</select><br>";
                    }
                    break;
            }
        }
    }
    //////////To fill data in (if have them) when is in Home page
    $fill_if_field_has_data='';
    if (!empty($_SESSION['user_form_logged'][$counter]['value_field']) && isset($_SESSION['user_form_logged'][$counter]['value_field']))
    {
        $fill_if_field_has_data = (($_SESSION['user_form_logged'][$counter]['field_id'] == $field['id']) && ($page=='home')? $_SESSION['user_form_logged'][$counter]['value_field'] : "");

    }else{
        if ($fill_if_field_has_data=='' && $field['field_type']=='integer')
        {
            $fill_if_field_has_data=0;
        }
    }
    ////////////////////////
    switch ($field['field_type']){
        case 'text':
            $html .="<label class='' >".ucfirst($field['name']).": <input class='form-control' name='".$field['id']."' type='text' "
                .$required .$length."  value='$fill_if_field_has_data'></label>";
            If($page=='reports')
            {
               $html .= "<label><input name=comparison[".$field['id']."]' type='checkbox'  value='content' > contain</label>";
                $html .= "<label><input name=comparison[".$field['id']."]' type='checkbox'  value='not_contain' > not contain</label>";
            }
            break;
        case 'integer':
            $html .="<label>".ucfirst($field['name']).": <input  class='form-group' name='".$field['id']."' type='number' min='0' value='$fill_if_field_has_data' ></label>";
            break;
        case 'boolean':
            $fill_if_field_has_data_true = $fill_if_field_has_data_false = '';
            if (!empty($_SESSION['user_form_logged'][$counter]['value_field']) && isset($_SESSION['user_form_logged'][$counter]['value_field']))
            {
                (($_SESSION['user_form_logged'][$counter]['field_id'] == $field['id'] &&
                    $_SESSION['user_form_logged'][$counter]['value_field']=='true') ? $fill_if_field_has_data_true='checked' : $fill_if_field_has_data_false='checked');
            }
            $html .="<label>".$field['name'].": <input name='".$field['id']."' type='radio' value='true' $fill_if_field_has_data_true>true</label>";
            $html .="<label><input name='".$field['id']."' type='radio' value='false' $fill_if_field_has_data_false>false</label>";
            break;
        case 'radio':
            $html .= $options_radio;
            break;
        case 'checkbox':
            $html .= $options_checkbox;
            break;
        case 'textarea':
            $html .="<label>".$field['name'].": <textarea name='".$field['id']."' rows='$length' cols='$length' ".$required."  value='$fill_if_field_has_data'>$fill_if_field_has_data</textarea></label>";
            break;
        case 'select':
            $html .= $options_select;
            break;
    }
    $counter++;
}
if (!empty($Allowed_Fields)) //IF THERE ARE NOT FIELDS TO SHOW, IT DOESN'T SHOW THE BUTTON SUBMIT
{
    $html .= " <button class='form-control btn btn-default  btn-sm' type='submit' name='".$entity."_btn-submit' >".ucfirst($entity)." Submit</button>";
}
?>
<form class="form-group" id="user_form" action="<?php echo($_SERVER['PHP_SELF']); ?>" method="post"  >
    <?php echo ($html); ?>
</form>