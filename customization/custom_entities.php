<?php
$entity_name_order_up=$entity_name_order_down=$entity_name_edit_field=$entity_name_show_field=
            $entity_name_delete_field=$table_fields_sorted_out='';
foreach ($list_persons as $entity_name){
    $edit_container=$form_edit='';
    ///////////BUTTON ACTIONS////////
        if (isset($_POST[$entity_name.'_order_up']))
        {
            $data_fields->change_order($_POST[$entity_name.'_order_up'], 'UP', $entity_name);
        }
        if (isset($_POST[$entity_name.'_order_down']))
        {
            $data_fields->change_order($_POST[$entity_name.'_order_down'], 'DOWN', $entity_name);
        }
        if (isset($_POST[$entity_name.'_delete_field']))
        {
            $data_fields->delete_field($_POST[$entity_name.'_delete_field'], $entity_name);
        }
        if (isset($_POST[$entity_name.'_show_field']))
        {
            $data_fields->show_field($_POST[$entity_name.'_show_field'],$entity_name);
        }
        
        if (isset($_POST[$entity_name.'_edit_field']))
        {
            $edit_container='';
            $_SESSION['field_parameters_edit'] = $_POST;           
            $type_field_to_edit = $data_fields->get_type_field($_SESSION['field_parameters_edit'][$entity_name.'_edit_field'], $entity_name);
            $_SESSION['field_parameters_added']['field_type'] = $type_field_to_edit['field_type'];
            $data_fields = Field::get_field_instance();
            $edit_container = $data_fields->load_fields_in_edit_form($entity_name, false);
            $form_edit .="<tr><td colspan=10><hr>";
            $form_edit .="<fieldset><legend>USER VALUES TO EDIT:</legend>".$edit_container;
            $form_edit .="<button name='".$entity_name."_btn-submit_field_details_edit' type='submit' style='float: right'>Save User</button>";
            $form_edit .="</fieldset>";
        }
        if(isset($_POST[$entity_name.'_btn-submit_field_details_edit']))
        {
            $_SESSION['field_parameters_edit'] += $_POST;
            
            $data_fields->edit_field($entity_name);
        }
        ////////////////////////////////////////
        
        $fields_ordered = $data_fields ->_get_all_fields_ordered($entity_name);
        $table_fields_sorted_out .="<tr><td colspan='4'><h2  class='text-center'>".ucfirst($entity_name)."</h2></td></tr>";
        $table_fields_sorted_out .= "<tr>";
        $columns = "<tr><th>Field Name</th><th>Options</th><th colspan='2'>Order</th></tr>";
        $table_fields_sorted_out .= $columns;
        
        for($i=0; $i<count($fields_ordered);$i++)
        {

            $table_fields_sorted_out .= "<tr> <td>".ucfirst($fields_ordered[$i]['name'])."</td>";
            $table_fields_sorted_out .= "<td>";
             if ($fields_ordered[$i]['removed']==1){
                 $table_fields_sorted_out .= "<button class='$entity_name' type='submit' name='".$entity_name."_show_field' value='".$fields_ordered[$i]['id']."'>Show</button>";
             }else{
                 $table_fields_sorted_out .= "<button class='$entity_name' type='submit' name='".$entity_name."_delete_field' value='".$fields_ordered[$i]['id']."'>Delete</button>";
             }
            $table_fields_sorted_out .= "<button class='$entity_name' type='' name='".$entity_name."_edit_field'  value='".$fields_ordered[$i]['id']."'>Edit</button></td>";
            if ($i != count($fields_ordered)-1)
            {
                $table_fields_sorted_out .= "<td><button class='$entity_name' name='".$entity_name."_order_down' type='submit' value=".$fields_ordered[$i]['sortorder'].">DOWN</button></td>";
            }
            if ($i!=0)
            {
                $table_fields_sorted_out .= "<td><button class='$entity_name' name='".$entity_name."_order_up' type='submit' value=".$fields_ordered[$i]['sortorder'].">UP</button></td>";
            }

            $table_fields_sorted_out .= "</tr>";
        }
        $table_fields_sorted_out .="</tr>";
        $table_fields_sorted_out .= $form_edit;
        $table_fields_sorted_out .="</td></tr>";
}

