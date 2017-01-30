<?php
class postgreSQL extends database {
    function connect()
    {
        global $CONFIG; global $db;
        try {
            $db = new PDO($CONFIG->type_database.':host='.$CONFIG->host.';port='.$CONFIG->port.';dbname='.$CONFIG->db_name, $CONFIG->user, $CONFIG->psw);
            return $db;
              /*var_dump($db);
              foreach($db->query('SELECT * from user_data') as $row) {
                  print_r($row);
              }*/
        } catch (PDOException $e) {
            print "¡Error!: " . $e->getMessage() . "<br/>";
            die();
        }

    }
    public function _get_type($field, $value) {
        //if is int or "char varying" postgreSQL don't accept length in the create table sentences but mysql need it. So this function is to manage it

            if (strcasecmp($value,'char')==0) { //I am filtering all the char to do them "char varying"
                $remove_length=true;
                $fieldsql =  $field ." CHAR VARYING ";
            }elseif (strcasecmp($value,'int')==0) {
                $remove_length=true;
                $fieldsql = $field ." ".strtoupper($value) . " ";

            }else{
                $fieldsql = $field ." ".strtoupper($value) . " ";
                $remove_length=false;
            }

            //$remove_length variable manage when is necessary to remove the length
        return array('sql_returned'=>$fieldsql,'remove_length'=>$remove_length);
    }
    public function _get_autoincrement() {
        $fieldsql = '';
        $fieldsql .= " SERIAL";

        return $fieldsql;
    }
    public function _get_indexes($table, $value, $field){
        //this is the way to create indexes for PostgreSQL
        $name_index = $table.ucfirst($value).ucfirst($field);
        return "CREATE INDEX".$name_index." ON " .$table." USING ".$value." ( ".$field.")";
    }
    public function _get_alter_column($table, $type, $column, $sentence=false){
        //some examples of possible alter columns in postgreSQL
        global $db;
        if($this->checkIf_tableExist($table)){
            switch ($type){
                case 'rename':
                    break;
                case 're_set':
                    $stmt = $db->prepare("ALTER TABLE ".$table." ALTER COLUMN ".$column." SET ".$sentence);
                    break;
                case 're_type':
                    $stmt = $db->prepare("ALTER TABLE ".$table." ALTER COLUMN ".$column." TYPE ".$sentence);
                    break;
                case 're_name':
                    $stmt = $db->prepare("ALTER TABLE ".$table." RENAME COLUMN ".$column." TO ".$sentence);
                    break;
                case 'add_column':
                    break;
                case 'change_comments_table':
                    break;
                default:
                    break;
            }
            //var_dump($stmt);
            $stmt->execute();

        }else{
            echo ("Table doesn't exist to modify it");
        }
    }
    public function _get_alter_primary_keys($table, $columns){
        //some examples of possible alter columns in postgreSQL
        var_dump($this->checkIf_tableExist($table));
        global $db;
        if($this->checkIf_tableExist($table))
        {

                    $stmt = $db->prepare("ALTER TABLE ".$table." ADD PRIMARY KEY (".$columns.")");

            var_dump($stmt);
            $stmt->execute();

        }else{
            echo ("Table doesn't exist to modify it");
        }
    }
    // this is to concatenate columns base on a symbol or space to separate them
    public function concatenation($string_columns, $separate_by){
        $space = "||'$separate_by'||";
        $concatenation = '';
        $array_columns_to_concatenate =(explode(",",$string_columns));
        foreach ($array_columns_to_concatenate as $column){
            $concatenation .= ("$column $space");
        }
        $concatenation = trim($concatenation, "$space");
        return $concatenation;
    }
    // this is to check if an index exist to avoid his new creation
    public function checkIf_IndexExist($table_name, $index_name){
        global $db;
        $stmt = $db->prepare("SELECT * FROM pg_indexes WHERE indexname ~ '$index_name'");
        $stmt->execute();
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (isset($row) || empty($row)) {
            $exist = false;
        } else {
            $exist = true;
        }

        return $exist;
    }
    //to get a limit of rows in a table
    public function set_limit_rows($number_rows, $number=0){
        $offset='';
        if ($number!=0 or $number!=false){$offset=" OFFSET ".$number;}
        return "LIMIT ".$number_rows.$offset;
    }
    public function _get_type_column($table, $column){
        global $db;

        $stmt = $db->prepare("select column_name, data_type from information_schema.columns where table_name = '$table' and column_name = '$column'");
        $stmt->execute();
        //var_dump($stmt);
        $this->GET_ERROR($stmt);
        $row= $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['data_type'];
    }

    function upsert($table, $columns){
        //var_dump($columns);
        global $db;
        $log_id = $_SESSION['user_log_id'];
        foreach ($columns as $field_id=>$column_value) {

            $stmt = $db->prepare("INSERT INTO $table (log_id, field_id, value_field) values ($log_id, $field_id, :value_field)
                                      ON CONFLICT (log_id, field_id) DO UPDATE SET value_field =:value_field");
            $stmt->bindValue(":value_field",strtolower($column_value));
            $stmt->execute();
//            /var_dump($stmt);
        }

        if ($stmt->execute()){
            echo ("USER UPDATE SUCCESSFULLY<br><br>");
            //unset($_POST);
        } else {
            //  var_dump($stmt->errorInfo());
        }
    }
    function checkIf_tableExist($table_name){
        //true if exist, false if doesn't
        global $db;
        // var_dump($db);

        $stmt = $db->prepare("SELECT EXISTS (
                                   SELECT 1
                                   FROM   information_schema.tables 
                                   where table_name='$table_name'
                                )");

        try {
            $stmt->execute();
         //   var_dump($stmt);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
        }catch(mysqli_sql_exception $e){
        //    var_dump($stmt);
        }

        return $row['exists'];
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
