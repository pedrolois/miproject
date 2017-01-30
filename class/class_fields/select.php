<?php
class Select extends Field {
    function display_HTML($entity, $show_radio=true, $edit_form=false)
    {   
        $html = $this->basic_html_type_column($entity, $show_radio);
        if ($edit_form==true) {
            $html .=  $this->load_field_options ($entity);
        }else {
            $html = $this->basic_html_type_column($entity);
            //$html.= $this->general_parameters();
            $html .= "<label><input type='radio' name='options_to_load' value='list_options' checked>
                          List options: <input name='group_with' type='button' onclick='add_optionsORgroups()' value='Add'> 
                          <input id='amount_options' type='text' value='0' maxlength='2' style='width: 50px'>
                          <input name='clear_group_with' type ='button' onclick='clear_options()' value='Clear'>
                    </input></label><br>";
            
            $html .= "<label><input type='radio' name='options_to_load' value='linked_to_table_value'>
                          Link to: 
                    </input>".  $this->load_column_linked_to_field()."</label><br>";
            $html .= "";
            $html .= "<div id='div-options_groups'></div>";
        }
        return $html;
    }

    function create_field($entity)
    {        
        global $db_driver;
        //var_dump($_SESSION);
        if($this->check_if_field_exist($entity)){
            echo ("There is a field with same name already.");

        }else{
            if ($_SESSION['field_parameters_added']['options_to_load']=='list_options')
            {
                $other_values = '"options":{';
                foreach ($_SESSION['field_parameters_added']['options'] as $option_name)
                {
                    $other_values .= '"'.$option_name.'":"checked",';
                }
                $other_values = substr($other_values, 0, -2);
            }else{
                $other_values = '"table_link":{';
                $options = $_SESSION['field_parameters_added']['options'];
                $other_values .= '"'.$options.'';
            }            
            $other_values .= '"}';
            $last_sortorder_number_inTable = $this ->get_last_field_order($entity)+1;
            $db_driver->add_record
            (
                $entity.'_fields',array('name'=>$_SESSION['field_parameters_added']['name_column'],
                    'field_type'=>$_SESSION['field_parameters_added']['field_type'],
                    'displayed_report'=>$_SESSION['field_parameters_added']['displayed_reports'],
                    'displayed_form'=>$_SESSION['field_parameters_added']['displayed_userForm'],
                    'displayed_export'=>$_SESSION['field_parameters_added']['displayed_export'],
                    'other_values'=>$other_values,'sortorder'=>$last_sortorder_number_inTable)
            );
            $this->add_Data_table($entity);
            echo("The field has been inserted");
        }
    }
    function edit_field($entity)
    {
        global $db_driver;
        //var_dump($_SESSION);
        $id_field_with_same_name = $this->check_if_field_exist($entity);       
        $parameters_toEdit = $this->get_type_field($_SESSION['field_parameters_edit']['edit_field'], $entity);
        if ((($id_field_with_same_name['id'] != $parameters_toEdit['id']) && ($id_field_with_same_name == false)
            || ($id_field_with_same_name['id'] == $parameters_toEdit['id']))) { //IF there is already a name in OTHER field then ...
                if (isset($_SESSION['field_parameters_edit']['options'])) {
                    $other_values = '"options":{';
                    foreach ($_SESSION['field_parameters_edit']['options'] as $option_name) {
                        $other_values .= '"' . $option_name . '":"checked",';
                    }
                    $other_values = substr($other_values, 0, -2);
                    $other_values .= '"}';
                } else {
                    $other_values = '';
                }

                /*var_dump($_SESSION);
                        var_dump($other_values);*/
                $db_driver->update_record($entity."_fields",
                    array('name' => $_SESSION['field_parameters_edit']['name_column'],
                        'displayed_report' => $_SESSION['field_parameters_edit']['displayed_reports'],
                        'displayed_form' => $_SESSION['field_parameters_edit']['displayed_userForm']
                    , 'displayed_export' => $_SESSION['field_parameters_edit']['displayed_export'],
                        'other_values' => $other_values),
                    array('id' => $parameters_toEdit['id']));

        }else{
            echo("There is a field with same name already.");
        }
    }

}