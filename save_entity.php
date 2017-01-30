<?php
//validation User Form and control errors
$error = false;
unset($_POST[$entity.'btn-submit']);
foreach ($_POST as $id_field=>$field_result)
{

    if (!is_array($_POST[$id_field])) {
        if (empty($_POST[$id_field])) {
            //   $array_form_errors[$id_field] .= "<br> - Error -";
            //$error = true;
        } else {
            $array_form_person[$id_field] = $field_result;
        }
    }else{
        $array_form_person[$id_field] ='';
            foreach ($field_result as $field_option) {
                $array_form_person[$id_field] .= $field_option . ",";
            }
            $array_form_person[$id_field] = substr($array_form_person[$id_field], 0, -1);

    }
}



