<?php
  abstract class Field extends Entity
{
    abstract function create_field($entity);
    abstract function edit_field($entity);
      ///////////////////////////////////////
    static function get_field_instance()
    {
       $field_parameter = (!isset($_SESSION['field_parameters_added']['field_type']) || empty($_SESSION['field_parameters_added']['field_type']) )?$field_parameter='Text':$field_parameter=$_SESSION['field_parameters_added']['field_type'];
       //SET every parameters and SESSIONS
    // dynamically instantiate and return a new instance of the database
     require_once(dirname(__FILE__).'/class_fields/'.strtolower($field_parameter).'.php');
     //return a new object of the above class
     return new $field_parameter();
   }
    public function _get_all_fields_ordered($entity)
    {
        global $db_driver;
        $fields = $db_driver->get_records($entity.'_fields','*',false, "=",  " ORDER BY ".$entity."_fields.sortorder ASC ", false);
        return $fields;
    }
    public function check_if_field_exist($entity)
    {
     global $db_driver;
      //var_dump($_POST);
      $checked = $db_driver->get_record($entity.'_fields','id', array('name'=>$_POST['name_column']) );
      //var_dump($checked);
     return $checked;
    }
    public function _get_max_id_fields_table($entity)
    {
        global $db_driver;

        $max_id = $db_driver->get_record($entity."_fields", array('max(id)'=>'max_id'));
        return $max_id['max_id'];
    }
    public function add_Data_table($entity)
    {
        global $db_driver;
        //var_dump($_SESSION);
        $max_fieldID= $this->_get_max_id_fields_table($entity);
        $db_driver->add_record($entity.'_data', array('log_id'=>$_SESSION['user_log_id'],'field_id'=>$max_fieldID));

    }
    public function get_type_field($id, $entity)
    {
        global $db_driver;
        $type = $db_driver->get_record($entity.'_fields','*', array('id'=>intval($id)));
        return $type;
    }
    public function get_last_field_order($entity)
    {
        global $db_driver;
        $max = $db_driver->get_record($entity.'_fields','max(sortorder) as max');
        return $max['max'];
   }
    public function change_order($position, $type_order, $entity)
    {
        global $db;

       //echo $order;
        if($type_order=="UP")
        {
            $new_position = $position-1;
            $stmt = $db->prepare("SELECT id as id_clicked FROM ".$entity."_fields WHERE sortorder=$position");
            $stmt->execute();
            //var_dump($stmt);
             $row_clicked = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt = $db->prepare("SELECT id as id_affected FROM ".$entity."_fields WHERE sortorder=$new_position");
            $stmt->execute();
            //var_dump($stmt);
            $row_affected = $stmt->fetch(PDO::FETCH_ASSOC);

            ////UPDATES
            $stmt = $db->prepare("UPDATE ".$entity."_fields SET sortorder=$new_position WHERE id=".$row_clicked['id_clicked']);
            $stmt->execute();
            $stmt = $db->prepare("UPDATE ".$entity."_fields SET sortorder=$position WHERE id=".$row_affected['id_affected']);
            $stmt->execute();

        }else{
            $new_position = $position+1;
            $stmt = $db->prepare("SELECT id as id_clicked FROM ".$entity."_fields WHERE sortorder=$position");
            $stmt->execute();
            //var_dump($stmt);
            $row_clicked = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt = $db->prepare("SELECT id as id_affected FROM ".$entity."_fields WHERE sortorder=$new_position");
            $stmt->execute();
            $row_affected = $stmt->fetch(PDO::FETCH_ASSOC);
            ////UPDATES
            $stmt = $db->prepare("UPDATE ".$entity."_fields SET sortorder=$new_position WHERE id=".$row_clicked['id_clicked']);
            $stmt->execute();
            $stmt = $db->prepare("UPDATE ".$entity."_fields SET sortorder=$position WHERE id=".$row_affected['id_affected']);
            $stmt->execute();
            /*echo ("id clicked ".$row_clicked['id_clicked']." position ".$position." will be ".$new_position."<br>");
            echo ("id affected ".$row_affected['id_affected']."position ".$new_position." will be ".$position);*/
        }
        unset($POST);

    }
    public function delete_field($id, $entity)
    {
        global $db;
        
        $stmt = $db->prepare("UPDATE ".$entity."_fields SET removed=true WHERE id=:id");
        $stmt->bindValue(":id", $id);
        $stmt->execute();

    }
    public function show_field($id, $entity)
    {
         global $db;
         $stmt = $db->prepare("UPDATE ".$entity."_fields SET removed=false WHERE id=:id");
         $stmt->bindValue(":id", $id);
         $stmt->execute();

     }
    public function load_field_options ($entity)
    {
           If (isset($_SESSION['field_parameters_edit'])) {
               $get_parameters_of_a_field = $this->get_type_field($_SESSION['field_parameters_edit'][$entity.'edit_field'], $entity);
           }
           $html='';
           $parameters = json_decode("{" . $get_parameters_of_a_field['other_values'] . "}", true);
           $i=1;
           $options =array();
           if (!empty($parameters)){
               foreach ($parameters['options'] as $name => $option)
               {
                   $html .= "Option $i:<input type='text' value='".$name."' name='options[$i]'><br>";
                   $i++;
               }
           }
           //var_dump($html);
           return $html;
    }
    public function load_fields_in_edit_form($entity, $show_radio=true)
    {
                   $return = $this->display_HTML($entity, $show_radio);
             return $return;
    }
    public function basic_html_type_column($entity, $show_radio=true)
    {
   //     /var_dump($_SESSION);
        $get_parameters_of_a_field=$displayed_report=$displayed_export=$displayed_form='';
        if(isset($_SESSION['field_parameters_edit'])) {

            $get_parameters_of_a_field = $this->get_type_field($_SESSION['field_parameters_edit'][$entity.'_edit_field'], $entity);
            //var_dump($get_parameters_of_a_field);
            (($get_parameters_of_a_field['displayed_report'] == true) || ($get_parameters_of_a_field['displayed_report'] == 'true')) ? $displayed_report = 'checked' : $displayed_report = '';
            ($get_parameters_of_a_field['displayed_form'] == true || ($get_parameters_of_a_field['displayed_form'] == 'true')) ? $displayed_form = 'checked' : $displayed_form = '';
            ($get_parameters_of_a_field['displayed_export'] == true || ($get_parameters_of_a_field['displayed_export'] == 'true')) ? $displayed_export = 'checked' : $displayed_export = '';
        }
       $html  = "<label id='label_column_name'>Name Column: <input type='text' name='name_column' ";
       $html .="value='".(isset($get_parameters_of_a_field['name'])?$get_parameters_of_a_field['name']:'')."'";
       $html .="></label><br>";
    if ($show_radio==true)
    {
        global $CONFIG;
        $entities = $CONFIG->entities;
        foreach ($entities as $entity)
        {
            $html .= "<label><input type='radio' value='$entity' name='object_to_add' required>".ucfirst($entity)."</label>";
        }
    }
       
       $html .= "<fieldset>
                   <legend>Displayed: </legend>
                           <input type='hidden' name='displayed_reports' value='false' />
                           <label><input type='checkbox' name='displayed_reports' value='true' $displayed_report/>Reports</label>                        
                           <input type='hidden' name='displayed_userForm' value='false' />
                           <label><input type='checkbox' name='displayed_userForm' value='true' $displayed_form/>".ucfirst($entity)." Form</label>                        
                           <input type='hidden' name='displayed_export' value='false' />
                           <label><input type='checkbox' name='displayed_export'  value='true' $displayed_export/>Export</label>

                 </fieldset>";
     return $html;
    }
    public function general_parameters($entity='user')
    {
        $get_parameters_of_a_field=$required=$unique='';
        $parameters['length']=0;
        
        if(isset($_SESSION['field_parameters_edit'])) 
        {
            $get_parameters_of_a_field = $this->get_type_field($_SESSION['field_parameters_edit'][$entity.'_edit_field'], $entity);
            $parameters = json_decode("{" . $get_parameters_of_a_field['other_values'] . "}", true);
            $required = ($parameters['required'] == 'true') ? 'checked' :  '';
            $unique =  ($parameters['unique'] == 'unique') ?  'checked' :  '';
        }
        $html  = "<br>Length: <input name='length' type='number' maxlength='3' min='0' max='999' title='Only Number' value=".$parameters['length']." required><br>";
        $html .= "<input type='hidden' name='null' value='false' /><label>Required: <input name='null' type='checkbox' value='true' $required></label><br>";
        $html .= "<input type='hidden' name='unique_column' value='' /><label>Unique: <input name='unique_column' type='checkbox' value='unique' $unique></label><br>";
        $html .= "<br>";
        return $html;

    }  
    public function load_column_linked_to_field()
    {
        global $db_driver, $CONFIG;
        $entities = $CONFIG->entities;
        $html  ='';
        $html .= "<select name='options'>";
        $html .="<option value=''>Select</input>";
         $i=0;
        foreach ($entities as $entity)
        {
           $data_table = $db_driver->get_records($entity.'_fields', '*'); 
           
           $html .="<optgroup label='$entity'>";
          
            foreach ($data_table as $data)
            { 
                  $html .="<option value='".$entity."&quot;:&quot;".$data['id']."' name='option[$i]'>".ucfirst($data['name'])."</option>"; 
                  $i++;
            }  
        }   
         $html .="</select>"; 
        return $html;
    }
}
?>


