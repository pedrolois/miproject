<?php
class mySQL extends database {
    public function connect()
    {
      global $CONFIG; global $db;
 	
		//handle error connection
		try {
		    $db = new PDO($CONFIG->type_database.':host='.$CONFIG->host.';dbname='.$CONFIG->db_name, $CONFIG->user, $CONFIG->psw);
		  /* foreach ($db->query('SELECT * from user_data') as $row) {
		        print_r($row);
		    }*/
		   
		} catch (PDOException $e) {
		    print "¡Error!: " . $e->getMessage() . "<br/>";
		    die();
		}
    }
    public function _get_type($field, $value) {
        $fieldsql = $field . " ".$value;
        //$remove_length variable manage when is necessary to remove the length, Mysql must have in all the cases but postgreSQL it dosen't
        return array('sql_returned'=>$fieldsql,'remove_length'=>false);
    }
    public function _get_autoincrement() {
        $fieldsql='';
        $fieldsql .= " INT AUTO_INCREMENT";
        return $fieldsql;
    }
    public function _get_indexes($table, $value, $field){
        //this is the way to create indexes for Mysql
        $name_index = $table.ucfirst($value).ucfirst($field);
        return " CREATE INDEX ".$name_index." ON ".$table."(".$field.") USING ".$value ;
    }
    public function _get_type_column($table, $column){
        //This function has been created just for Mysql. Is send it back the type of a column, because then is
        //compulsory for some alter column.

        //Example: if you want alter a column and change it to NOT NULL,
        // you must to introduce the type of variable as well,
        // so this function introduce his actual one (see how has been used in _get_alter_column() )
        global $db;

        $stmt = $db->prepare("SHOW COLUMNS FROM ".$table." WHERE field=:field");
        $stmt->bindValue(':field',$column);
        $stmt->execute();
        $this->GET_ERROR($stmt);
        $row= $stmt->fetch(PDO::FETCH_ASSOC);

        return $row;
    }
    public function _get_alter_column($table, $type, $column, $sentence=false){
        //some examples of possible alter columns in mySQL
        global $db;
        if(!$this->checkIf_tableExist($table)){
            $type_column = $this->_get_type_column($table, $column);
            switch ($type){
                case 're_set':
                    //Mysql need to add a type column for example when you want to modify a column to NOT NULL
                    (strtolower ($sentence)==strtolower ('NOT NULL'))?$type_add=$type_column['Type']:$type_add='';
                    $stmt = $db->prepare("ALTER TABLE ".$table." MODIFY ".$column."  ".$type_add." ".$sentence);
                    break;
                case 're_type':
                    $stmt = $db->prepare("ALTER TABLE ".$table." MODIFY ".$column." ".$sentence);
                    break;
                case 're_name':
                    $stmt = $db->prepare("ALTER TABLE ".$table." CHANGE ".$column." ".$sentence." ".$type_column['Type']);
                    break;
                case 'add_column':
                    break;
                case 'change_comments_table':
                    break;
                default:
                    break;
            }
            $stmt->execute();
            $this->GET_ERROR($stmt);

        }else{
            echo ("Table doesn't exist to modify it");
        }
    }
    public function _get_alter_primary_keys($table, $columns){
        //some examples of possible alter columns in postgreSQL
//        /var_dump($this->checkIf_tableExist($table));
        global $db;
        $type = $this->_get_type_column($table, 'id');
            //var_dump($type);
        if($this->checkIf_tableExist($table))
        {
            $stmt = $db->prepare("ALTER TABLE $table
                                    DROP PRIMARY KEY,
                                    CHANGE id id ".$type['Type'].",
                                    ADD PRIMARY KEY (".$columns.")");
            $stmt->execute();
            //var_dump($stmt);

        }else{
            echo ("Table doesn't exist to modify it");
        }
    }
    // this is to concatenate columns base on a symbol or space to separate them
    public function concatenation($string_columns, $separate_by){
        $string_columns = str_replace(",",",'".$separate_by."',",$string_columns);
        $concatenation = "CONCAT($string_columns)";

        return $concatenation;
    }
    // this is to check if an index exist to avoid his new creation
    function checkIf_IndexExist($table_name, $index_name){
        global $db;
        $stmt = $db->prepare("SHOW INDEX FROM $table_name where KEy_name='$index_name'");
        $stmt->execute();
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        //display ($row);
        if (isset($row) || empty($row)) {
            $exist = false;
        } else {
            $exist = true;
        }

        return $exist;
    }
    //to get a limit of rows in a table
    public function set_limit_rows($number_rows, $number){
        if ($number!=0 or $number!=false){$offset=" , ".$number_rows;}
        return "LIMIT ".$number.$offset;
    }

    function upsert($table, $columns){
        global $db;
        $error=false;
        $log_id = $_SESSION['user_log_id'];
        //var_dump($columns);
        foreach ($columns as $field_id=>$value_parameter) {

          /*  $stmt = $db->prepare("INSERT INTO $table (log_id, field_id, value_field) values ($log_id, :field_id, :value_field)
                                      ON DUPLICATE KEY UPDATE   field_id=:field_id");
           * /* if($value_parameter=='true' || $value_parameter=='false') {
                $value_parameter = filter_var($value_parameter, FILTER_VALIDATE_BOOLEAN);
                $stmt->bindValue(':value_field', $value_parameter, PDO::PARAM_BOOL);
                $stmt->bindValue(':field_id', strtolower($field_id) );
            }else{
                $stmt->bindValue(':value_field', strtolower($value_parameter) );
                $stmt->bindValue(':field_id', strtolower($field_id) );
            }
            var_dump($stmt);
            if(!$stmt->execute()){
               $error=true; 
            }*/
            //upsert doesn't work in my version of phpmyadmin 
            
            $have_data = $this->get_record($table,"id",array("field_id"=>$field_id, "log_id"=>$log_id));
            $params = array( "log_id"=>$log_id,"field_id"=>$field_id, "value_field"=>$value_parameter);
            
            if ($have_data==false)
            { 
                $this->add_record($table, $params);
                $type="insert";
            }else{
                $this->update_record($table, $params, array("id"=>$have_data['id']));
                $type="update";
                
            }
        }
        if ($type=='insert')
        {
           echo ("USER INSERTED SUCCESSFULLY<br><br>"); 
        }else{
           echo ("USER UPDATE SUCCESSFULLY<br><br>");
        }
    }
    function checkIf_tableExist($table_name){
        //true if exist, false if doesn't
        global $db;
        // var_dump($db);

        $stmt = $db->prepare("SHOW TABLES LIKE '$table_name'");
        try {
            $stmt->execute();
            //   var_dump($stmt);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
        }catch(mysqli_sql_exception $e){
            //    var_dump($stmt);
        }

        return $row;
    }
    function comparison ($comparison)
    {
        $symbol =' = ';
        switch ($comparison)
        {
            case 'content':
                $symbol = ' LIKE ';
                break;
            case 'not_content':
                $symbol = ' NOT LIKE';
                break;
            case '':
                $symbol = ' = ';
                break;
            default:
                $symbol = ' = ';
                break;
        }

        return $symbol;
    }

}