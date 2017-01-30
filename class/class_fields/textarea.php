<?php
class Textarea extends Field {
    function display_HTML($entity, $show_radio=true)
    {   
        $html = $this->basic_html_type_column($entity, $show_radio);
        $html.= $this->general_parameters($entity);
        return $html;
    }
    function create_field($entity)
    {
        global $db_driver;
        /*var_dump($_SESSION);
        var_dump($_POST);*/
        if($this->check_if_field_exist($entity)){
            echo ("There is a field with same name already.");
        }else{
                    $other_values = '"length":"'.$_SESSION['field_parameters_added']['length'].'", '.'"unique":"'.$_SESSION['field_parameters_added']['unique_column'].'"';
                    $other_values .=', '.'"required":"'.$_SESSION['field_parameters_added']['null'].'"';
            $last_sortorder_number_inTable = $this ->get_last_field_order($entity)+1;
            $db_driver->add_record(
                $entity.'_fields',array('name'=>$_SESSION['field_parameters_added']['name_column'],'field_type'=>$_SESSION['field_parameters_added']['field_type'],
                    'displayed_report'=>$_SESSION['field_parameters_added']['displayed_reports'], 'displayed_form'=>$_SESSION['field_parameters_added']['displayed_userForm']
                , 'displayed_export'=>$_SESSION['field_parameters_added']['displayed_export'],
                    'other_values'=>$other_values, 'sortorder'=>$last_sortorder_number_inTable)
            );
            $this->add_Data_table($entity);
            echo("The field has been inserted");
            //unset($_SESSION['field_parameters_added']);
        }
    }

    function edit_field($entity)
    {
        global $db_driver;
        //var_dump($this->check_if_field_exist('user'));
        $id_field_with_same_name = $this->check_if_field_exist($entity);
        $parameters_toEdit = $this->get_type_field($_SESSION['field_parameters_edit']['edit_field'], $entity);
        if ((($id_field_with_same_name['id'] != $parameters_toEdit['id']) && ($id_field_with_same_name == false)
            || ($id_field_with_same_name['id'] == $parameters_toEdit['id']))) { //IF there is already a name in OTHER field then ...
                $other_values = '"length":"' . $_SESSION['field_parameters_edit']['length'] . '", ' . '"unique":"' . $_SESSION['field_parameters_edit']['unique_column'] . '"';
                $other_values .= ', ' . '"required":"' . $_SESSION['field_parameters_edit']['null'] . '"';
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