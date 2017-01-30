<?php
abstract class database
{
    static function get_driver_instance()
    {
        // dynamically instantiate and return a new instance of the database
        global $CONFIG;
        if ($CONFIG->database_instance=='postgreSQL' || $CONFIG->database_instance=='mySQL') {
            require_once(dirname(__FILE__) . '/' . $CONFIG->database_instance . '.php');
            return new $CONFIG->database_instance;
        }else{
            echo 'Error: The database instance name is not right!';
        }
    }

    abstract function connect();
    abstract function _get_type($field, $value);
    abstract function _get_autoincrement();
    abstract function _get_indexes($table, $value, $field);
    abstract function _get_alter_column($table,$type, $column, $parameters=false);
    abstract function _get_alter_primary_keys($table, $columns);
    abstract function checkIf_IndexExist($table_name, $index_name);
    abstract function concatenation($string_columns, $separate_by);
    abstract function set_limit_rows($number_rows, $number);
    abstract function _get_type_column($table, $column);
    abstract function upsert($table, $columns);
    abstract function checkIf_tableExist($table_name);
    abstract function comparison ($comparison);
    ////////////////////////////////////////////////FUNCTIONS FOR BOTH DATABASES//////////////////////////////////////////////////////////////////////////
    public function GET_ERROR($stmt){
        //to get sql queries errors
        if($stmt->errorCode()) {
            $errors = $stmt->errorInfo();
            echo($errors[2]);
        }
    }
    public function join_tables($tables){
        $string_tables ="";
        if(is_array($tables)){
            foreach ($tables as $number_of_join_table=>$table_names_join){
                foreach($table_names_join as $join_type=>$tables_name){
                    /*display(array_values($tables_name));
                    display(array_keys($tables_name));*/
                    $table_columns_toJoin = array_values($tables_name);
                    $table_names_toJoin = array_keys($tables_name);
                    $Main_table = $table_names_toJoin[0];
                    $string_tables .= " " . $join_type . " " . $table_names_toJoin[1] .
                        " ON (".$table_names_toJoin[0].".".$table_columns_toJoin[0]."=".$table_names_toJoin[1].".".$table_columns_toJoin[1].")";
                }
            }
            $string_tables = $Main_table.$string_tables;
        }else{
            $string_tables = $tables;
        }
        return $string_tables;
    }
    public function get_table_ids($tables){ //used in the delete_records_cascade()
        //display($tables);
        $string_tables ="";
        if(is_array($tables)){
            foreach ($tables as $number_of_join_table=>$table_names_join){
                //display ($table_names_join);
                foreach($table_names_join as $join_type=>$tables_name){
                    /*display(array_values($tables_name));
                    display(array_keys($tables_name));*/
                    $table_columns_toJoin = array_values($tables_name);
                    $table_names_toJoin = array_keys($tables_name);
                    $string_tables .=$table_names_toJoin[0].".".$table_columns_toJoin[0].",".$table_names_toJoin[1].".".$table_columns_toJoin[1].",";
                }
            }
            $string_tables = substr($string_tables, 0, -1);
        }else{
            $string_tables = $tables;
        }
        return $string_tables;
    }
    public function create_tables($table_names)
    {
        $i = 0;
        $external_indexes = array();
       // var_dump($table_names);
        foreach ($table_names as $table => $fields) {
            ////var_dump($table_names);
            //check if table exist in this point
            if (!$this->checkIf_tableExist($table)) {
                $sql[$i] = "CREATE TABLE IF NOT EXISTS $table (";

                $columns = array();
                foreach ($fields as $field => $attributes) {
                    $fieldsql = '';
                    foreach ($attributes as $attribute => $value) {
                        switch ($attribute) {
                            case 'type':
                            {
                                if (array_key_exists('autoincrement', $attributes)) {
                                    $fieldsql .= $field." ".$this->_get_autoincrement();
                                } else {
                                    $type_field = $this->_get_type($field, $value);
                                    $fieldsql .= $type_field['sql_returned'];
                                }
                                break;
                            }
                            case 'length':
                            {
                                if ($type_field['remove_length']==false)
                                {
                                    $fieldsql .= "(" . (int)$value . ")";
                                }

                                break;
                            }
                            case 'default':
                            {
                                $type_variable = gettype($value);
                                switch ($type_variable) {
                                    case 'boolean':
                                        $type_default = ($value) ? 'TRUE' : 'FALSE';
                                        break;
                                    case 'integer':
                                        $type_default = (int)$value;
                                        break;
                                    case 'string':
                                        $type_default = strtoupper($value);
                                        break;
                                }
                                $fieldsql .= " DEFAULT " . $type_default;
                                break;
                            }
                            case 'is_null':
                            {
                                if ($value) {
                                        //mysql and postgreSQL create the fields NULL
                                }else{
                                    $fieldsql .= " NOT NULL ";
                                }
                                break;
                            }
                            case 'index': {
                                $name_index = $table.ucfirst($value).ucfirst($field);
                                switch ($value) {
                                    case 'primary':
                                        $fieldsql .= " PRIMARY KEY";
                                        break;
                                    case 'unique':
                                        $fieldsql .= " " . strtoupper($value);
                                        break;
                                    //postgraeSQL index cases
                                    case 'index':
                                    case 'btree':

                                        if($this->checkIf_IndexExist($table,$name_index)) {
                                            $external_indexes [] = "CREATE INDEX $name_index ON $table ($field)";
                                        }
                                        break;
                                    default:
                                        if($this->checkIf_IndexExist($table,$name_index)) {
                                            $external_indexes [] = $this->_get_indexes($table, $value, $field);
                                        }
                                        break;
                                }
                                break;
                            }
                            default:
                                break;
                        }
                    }
                    $columns[] = $fieldsql;
                }
                $sql[$i] .= implode(', ', $columns);
                $sql[$i] .= ");";
                $i++;
              //  display($sql);
                //display($external_indexes);
            } else {
                $sql[$i]='';
                //echo ("The table '".$table."' exist already<br>");
            }
            //CREATE TABLES AND INDEXES DEPENDING OF THE DATABASE TYPE
            foreach ($sql as $s) {
                if($s!='')
                $this->create_sql_query($s);
            }
            foreach ($external_indexes as $index) {
                $this->create_table($index);
            }
        }
    }
    public function create_sql_query ($sql){
        global $db;
        $stmt = $db-> prepare($sql);
        $stmt->execute();
        //var_dump($stmt);
        $rows=$stmt->fetchAll();
        $this->GET_ERROR($stmt);
        return $rows;
    }
    public function create_sql_query_assoc ($entity, $sql, $type_displayed, $show_all=false){
        global $db;
        $stmt = $db-> prepare($sql); //this query return me the entities id for the next query
        $stmt->execute();
//        echo ("<pre>");
//        var_dump($sql);
        $rows=$stmt->fetchAll(PDO::FETCH_ASSOC);
        $all_ids_filtered ='';
        //var_dump($rows);
        if (!empty($rows)) {
            if (isset($_GET['pagination'])) {
                ($_GET['pagination'] == 0) ? $max = 2 : $max = $_GET['pagination'] + 3;
                for ($i = $_GET['pagination']; $i <= ($max); $i++) {
                    if (isset($rows[$i]['id']) && isset($rows[$i]['id_field'])) {
                      //  $all_ids_filtered .= "log_id=" . $rows[$i]['id'] . " or ";
                       // $all_ids_filtered .= $entity."_data.entity_row_id=" . $rows[$i]['entity_row_id'] . " or ";
                    }
                }

            } else {
                foreach ($rows as $id_filtered) {
                   // $all_ids_filtered .= "log_id=" . $id_filtered['id'] . " or ";
                    //$all_ids_filtered .= $entity."_data.entity_row_id=" . $rows[$i]['entity_row_id'] . " or ";
                }
            }
            $all_ids_filtered = substr($all_ids_filtered, 0, -4);
            if (empty($all_ids_filtered) == '') {
                $all_ids_filtered .= ' and ';
            }
            if (isset($_GET['sort'])){
                $sorted  = $_GET['sort'];
            }else{
                $sorted = 'sortorder';
            }
            $stmt = $db->prepare("SELECT log_id, name as name, value_field as value_field, entity_row_id \n "
                    . "FROM ".$entity."_data  JOIN ".$entity."_fields "
                    . "ON (".$entity."_data.field_id=".$entity."_fields.id and $type_displayed)  
                       \n WHERE  $all_ids_filtered $type_displayed "
                    . "and removed = false "
                    . "order by ".$sorted.", entity_row_id asc ");
            $stmt->execute();
            $rows_entity = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->GET_ERROR($stmt);
            $total[0]['total'] = count($rows);
        }else{
            $rows=0;
            $rows_entity=0;
        }
        if (empty($rows)){$total[0]['total']=0;}

        //var_dump($total);
        return array('data'=>$rows_entity,'total'=>($total));
    }
    public function get_record($tables, $columns,$parameters=false)
    {
        global $db;
        $string_parameters =$string_tables=$table_name_first=$table_name_second= $string_columns="";
        //SORTING OUT THE PARAMETERS
        if ($parameters != false && is_array($parameters)) {
            foreach ($parameters as $key => $value) {
                //var_dump($key);
                if(is_string($value)){$key_lower = "LOWER(".$key .")";}else{$key_lower=$key;}
                $string_parameters .= $key_lower . " = :" . $key . " AND ";
            }
            $string_parameters = "WHERE " . strtolower($string_parameters);
            $string_parameters = substr($string_parameters, 0, -5);
        }else{
            $string_parameters=$parameters;
        }
        //SORTING OUT THE COLUMNS
        if (is_array($columns)) {
            foreach ($columns as $key_col => $col) {
                if ($key_col != '*') {
                    //add an alias (good when is a concatenation)
                    $string_columns .= $key_col . " as " . $col . ", ";
                } else {
                    $string_columns .= $col . ", ";
                }
            }
            $string_columns = substr($string_columns, 0, -2);
        }else{
            $string_columns = $columns;
        }
        //SORTING OUT THE TABLE JOINS depend of type of join
        $string_tables = $this->join_tables($tables);
        $stmt = $db-> prepare("SELECT $string_columns FROM $string_tables $string_parameters");
        if ($parameters != false && is_array($parameters)) {
            foreach ($parameters as $key_paramenter => $value_parameter)
            {
                if($value_parameter=='true' || $value_parameter=='false') {
                    $value_parameter = filter_var($value_parameter, FILTER_VALIDATE_BOOLEAN);
                    $stmt->bindValue(':' . $key_paramenter, $value_parameter, PDO::PARAM_BOOL);
                }else{
                    $stmt->bindValue(':' . $key_paramenter, strtolower($value_parameter) );
                }
            }
        }
        $stmt->execute();
        //var_dump($stmt);
        $this->GET_ERROR($stmt);
        $row = $stmt->fetch();
        if (empty($row)){
          //  echo "<BR>There is not any row to select with those parameters";display($parameters);
        }

        //display($row);
        return $row;
    }
    public function get_records($tables,$columns,$parameters=false, $comparison='=', $order_by=false, $echo_result=false){
        global $db;
        $string_parameters =$string_tables=$table_name_first=$table_name_second= $string_columns="";

        //SORTING OUT THE PARAMETERS
        if ($parameters != false && is_array($parameters)) {
            foreach ($parameters as $key => $value) {
                if(is_string($value)){$key_lower = "".$key ."";}else{$key_lower=$key;}
                if (strtolower($comparison) != 'in') {
                    $string_parameters .= $key_lower . " $comparison :" . $key . " AND ";
                } else {
                    $string_parameters .= $key_lower . " $comparison (:" . $key . ") AND ";
                }
            }
            $string_parameters = " WHERE " . strtolower($string_parameters);
            $string_parameters = substr($string_parameters, 0, -5);
        }else{
            $string_parameters = $parameters;
        }
        $string_parameters .= " ".$order_by;
        //var_dump($string_parameters);
        //SORTING OUT THE COLUMNS
        if (is_array($columns))
        {
            foreach ($columns as $key_col => $col)
            {
                if ($key_col != '*') {
                    //add an alias (good when is a concatenation)
                    $string_columns .= $key_col . " as " . $col . ", ";
                } else {
                    $string_columns .= $col . ", ";
                }
            }
            $string_columns = substr($string_columns, 0, -2);
        }else
            {
            $string_columns = $columns;
        }

        //SORTING OUT THE TABLE JOINS depend of type of join
        $string_tables = $this->join_tables($tables);
        $stmt = $db-> prepare("SELECT $string_columns FROM $string_tables $string_parameters");

        if ($parameters != false && is_array($parameters))
        {
            foreach ($parameters as $key_paramenter => $value_parameter)
            {
                if ($echo_result==true) {
                    var_dump($key_paramenter);
                    var_dump(strtolower($value_parameter));
                }
                if($value_parameter=='true' || $value_parameter=='false') {
                    $value_parameter = filter_var($value_parameter, FILTER_VALIDATE_BOOLEAN);
                    $stmt->bindValue(':' . $key_paramenter, $value_parameter, PDO::PARAM_BOOL);
                }else{
                    $stmt->bindValue(':' . $key_paramenter, strtolower($value_parameter) );
                }
            }
        }

        $stmt->execute();
        if ($echo_result == true) {
            var_dump("SELECT $string_columns FROM $string_tables $string_parameters");
        }
        //        var_dump($stmt);
                $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if ($echo_result==true)
                {
                    //display($row);
                    if (empty($row))
                    {
                        echo "<BR>There is not any row to select with those parameters";display($parameters);
                    }
                }
        //display($row);
                return $row;
            }
    public function add_record($table, $parameters)
    {
        global $db;
        $string_parameters=$table_name_first=$table_name_second=$string_columns="";
        //SORTING OUT THE PARAMETERS
            if (is_array($parameters))
            {
                foreach ($parameters as $key => $value) {
                    $string_parameters .= ":" . $key . " , ";
                    $string_columns .= $key.",";
                }
                $string_parameters = substr($string_parameters, 0, -3);
                $string_parameters =  "(".$string_parameters.")";
                $string_columns = substr($string_columns, 0, -1);
                $string_columns =  "(".$string_columns.")";

            }
        $stmt = $db-> prepare("INSERT INTO $table $string_columns VALUES  $string_parameters");
        if (is_array($parameters))
        {
            foreach ($parameters as $key_paramenter => $value_parameter)
            {
                //var_dump($value_parameter);
                if($value_parameter=='true' || $value_parameter=='false')
                {
                    $value_parameter = filter_var($value_parameter, FILTER_VALIDATE_BOOLEAN);
                    $stmt->bindValue(':' . $key_paramenter, $value_parameter, PDO::PARAM_BOOL);
                }else{
                    $stmt->bindValue(':' . $key_paramenter, strtolower($value_parameter) );
                }
            }
        }
        $stmt->execute();
       // var_dump($stmt);
        $this->GET_ERROR($stmt);
    }
    public function update_record($tables, $columns,  $parameters, $table_to_update=false){
        global $db;
        $string_parameters = $string_columns = $string_tables = "";
        ($table_to_update==false)?$table_to_update=$tables:''; //If doesn't send any table_name_to_update variable then
        // should be a single query without joins. So $tables variable would be the table name to update. (this is just other way for calling the update record function)
         $row_id = $this->get_record($tables, $table_to_update.'.id' , $parameters);
        //var_dump($row_id)
        //IF the database doesn't have a row then do nothing
        if (empty($row_id)){
            echo "This ".key($parameters)." => ".$parameters[key($parameters)]." doesn't exist in the database to update it";
        }else {
            //var_dump("asfafsasfdasfdasfasdf");
            /*foreach ($parameters as $key => $value) {
                $string_parameters .= $key . "=:" . $key;
            }*/
            $string_parameters .=  "id=:id";
            foreach ($columns as $columns_key => $columns_value) {
       // var_dump($columns_key);
                $string_columns .= $columns_key . "=:" . $columns_key . " , ";
            }
            $string_columns = substr($string_columns, 0, -3);
            //display($string_tables);
            $stmt = $db->prepare("UPDATE $table_to_update SET $string_columns WHERE $string_parameters ");
            //var_dump($stmt);
            foreach ($columns as $key_paramenter => $value_parameter) {
                //($value_column===true)?$value_column=1:$value_column=0; //this is because if the value is boolean true/false doesn't works in mySQL database and in postgreSQL works with both ways
                //ALERT: THE VARIABLE ABOVE DOESN'T WORKS IN POSTGRESQL
                if(($value_parameter=='true') || ($value_parameter=='false')) {
                    $value_parameter = filter_var($value_parameter, FILTER_VALIDATE_BOOLEAN);
                    $stmt->bindValue(':' . $key_paramenter, $value_parameter, PDO::PARAM_BOOL);

                }else{
                    $stmt->bindValue(':' . $key_paramenter, strtolower($value_parameter) );
                }
                //var_dump($value_parameter);
            }
            $stmt->bindValue(':id', $row_id['id'] );
            //var_dump($row_id);
            $stmt->execute();
            $this->GET_ERROR($stmt);
            $if_executed = $stmt->execute()? true:false;
            if($if_executed){
               // echo " Row updated.";
            }
        }
    }
    public function delete_record($table, $parameters){
        global $db;
	$is_exist = $this->get_record($table, array(key($parameters)), $parameters);
	//var_dump($is_exist);
        //IF the database doesn't have a row then do nothing
        if (empty($is_exist) || !isset($is_exist) )
        {
            echo "This ".key($parameters)." => ".$parameters[key($parameters)]." doesn't exist in $table table to delete it";
        }else {
            $string_parameters="";

            foreach ($parameters as $key => $value) {
                $string_parameters .= $key . "=:" . $key." AND ";
            }
            $string_parameters = substr($string_parameters, 0, -5);
            $stmt = $db->prepare("DELETE FROM $table WHERE $string_parameters");
            foreach ($parameters as $key_paramenter => $value_parameter) {
                if($value_parameter=='true' || $value_parameter=='false') {
                    $value_parameter = filter_var($value_parameter, FILTER_VALIDATE_BOOLEAN);
                    $stmt->bindValue(':' . $key_paramenter, $value_parameter, PDO::PARAM_BOOL);
                }else{
                    $stmt->bindValue(':' . $key_paramenter, strtolower($value_parameter) );
                }
            }

           // display($stmt);
            if ($stmt->execute())
            {
                echo "Row deleted";
            } else {
                echo "something was wrong removing a row.";
            }
        }
    }
    public function delete_records_cascade($tables, $parameters){
        global $db;
        $table_keys_to_show = $string_table = $string_parameters = "";
        $is_exist = $this->get_record($table, array(key($parameters)), $parameters);
	var_dump($is_exist);
        //IF the database doesn't have a row then do nothing
        if (empty($is_exist) || !isset($is_exist) )
        {
            if (is_array($tables)){
                //all this if, it is to remove a row in more than a table(CASCADE REMOVE)
                $string_table_ids = $this->get_table_ids($tables); //string with the columns that link the tables
                $results = $this->get_record($tables,$string_table_ids, $parameters, 'FETCH_NUM'); //Results of the columns that link the tables
                //this part is to get data results with the name of the table and the column
                $array_tableWithNameandColumn =(explode(",",$string_table_ids));

                foreach($array_tableWithNameandColumn as $k=>$table) {
                    $array_table_and_column [$table] = $results[$k];
                }

                foreach($array_table_and_column as $table_row_to_remove=>$result){
                    $string_parameters ="";
                    $column = ltrim(strstr($table_row_to_remove, '.'),".");
                    $table_name = strstr($table_row_to_remove, '.', true);
                    $string_parameters .= $column . "=:".$column." AND ";
                    $string_parameters = substr($string_parameters, 0, -5);
                    $stmt = $db->prepare("DELETE FROM $table_name WHERE ".$string_parameters);
                    $stmt->bindValue(':'.$column, strtolower($result));
                    IF ($stmt->execute()) {
                        echo $stmt->rowCount()." rows deleted";
                    } else {
                        echo "something was wrong removing rows.";
                    }
                    display($stmt);
                }

            }else{ //else, if is not an array it means is just a normal one to remove it
                $this->delete_record($tables, $parameters);
            }
        }
    }

    public function alter_table($command, $type, $table, $columns, $sentence=false){
        switch ($command){
            case 'alter_column':
                        $this->_get_alter_column($table,$type, $columns, $sentence);
                break;
            case 'change_table_name':
                break;
            case 'drop':
                break;
            case 'primary_keys':
                $this->_get_alter_primary_keys($table, $columns);
                break;
        }
    }
    public function count_column_table($table, $column, $parameters=false){
        global $db; $string_parameters='';
        if (is_array($parameters)) {
            foreach ($parameters as $key => $value) {
                $string_parameters .= $key . " = :" . $key . " AND ";
            }
            $string_parameters = "WHERE " . $string_parameters;
            $string_parameters = substr($string_parameters, 0, -5);

        }
        $stmt = $db-> prepare("SELECT count(".key($column).") as ".$column[key($column)]." FROM $table  $string_parameters");
        if (is_array($parameters)) {
            foreach ($parameters as $key_paramenter => $value_parameter) {
                $stmt->bindValue(':' . $key_paramenter, strtolower($value_parameter));
            }
        }
        $stmt->execute();
       // var_dump($stmt);
        $rows=$stmt->fetch(PDO::FETCH_ASSOC);
        $this->GET_ERROR($stmt);
        return $rows[$column[key($column)]];
    }
    public function get_data_report($entity, $type_displayed)
    {
        if(isset($_SESSION['report_form_search']))
        {
            $parameters_to_display = $_SESSION['report_form_search'];
        }else{
           // $parameters_to_display ='';
        }

        $SQL = array('columns' => '', 'join' => '', 'where' => '');
        //var_dump($parameters_to_display);
        if (!empty($parameters_to_display)) //when is filtered
        {
            foreach ($parameters_to_display as $id => $parameter_to_search)
            {
             //var_dump($parameter_to_search);
                //$comparison_type = $parameter_to_search[''];
                isset($parameters_to_display['comparison'][$id])?
                $comparison = $parameters_to_display['comparison'][$id] :$comparison='';
                $field_sql[$id] = $this->filter_report_SQL($entity, $id, $parameter_to_search, $comparison);

                if ($id != 'comparison') {
                    $SQL['columns'] .= $field_sql[$id]['columns'] . ",";
                    $SQL['join'] .= $field_sql[$id]['join'];
                    $SQL['where'] .= $field_sql[$id]['where'] . " AND ";
                }
            }
            $SQL ['columns'] = substr($SQL ['columns'], 0, -2);
            $SQL ['where'] = substr($SQL ['where'], 0, -5);
            //echo ("<pre>");
          //  var_dump($SQL);
            $data_person = $this->create_sql_query_assoc($entity, "SELECT user_log.id, " . $SQL['columns'] .
                    " \n FROM user_log" . $SQL['join'] .
                    " \n  WHERE " . $SQL['where'], $type_displayed);
        }else{ //when is no filtered
            //when is not filtered, so it shows all of them
            $data_person = $this->create_sql_query_assoc($entity, "SELECT user_log.id  \n "
                    . "FROM user_log "
                    . "LEFT JOIN ".$entity."_data ON (user_log.id=".$entity."_data.log_id) "
                    . "AND ".$entity."_data.id is not null "
                    . "GROUP BY user_log.id ", $type_displayed);
          //  echo ("<pre>");
           // var_dump($data_person);
        }
        //var_dump($data_person);
        return (array('data_'.$entity => $data_person['data'], $entity.'_total' => $data_person['total']));

    }
    function load_report_headers($entity)
    {
        global $db;
        $stmt = $db-> prepare("SELECT name FROM ".$entity."_fields WHERE displayed_report=true and removed=false");
        $stmt->execute();
        //var_dump($stmt);
        return $stmt->fetchall(PDO::FETCH_NUM);

    }
    function print_out_table_report($entity, $data_report)
    {
        global $db_driver;
//AND THEN GET THE USER RESULTS FROM USER_FORM FILTERED
        $table_entity =$row_table='';
        $table_entity .="<table class='table table-striped table-bordered table-hover table-sm table-responsive'>";
        //$table_entity .="<head>-------------------------REPORTS--------------------------</head><br>";
//var_dump($data_report['data_users']);
        $table_headers = array();
        //echo ("<pre>");
        //var_dump($data_report['data_'.$entity]);
        if ($data_report['data_'.$entity]!=0 ) {

            ////////////////////////////*SORTING OUT THE HEADERS*/////////////////////////////////////////////////////
            $headers_for_reports = $this->load_report_headers($entity);
            //var_dump($headers_for_reports);
            foreach ($headers_for_reports as $key_header => $header)
            {
                $table_headers [$header[0]] =$header[0];
            }
            //printing out the headers
            $table_entity .="<tr>";
            foreach ($table_headers as $header_cell)
            {
                $table_entity.="<th><a href='index.php?sort=$header_cell'>$header_cell</a></th>";
            }
            $table_entity .= "</tr>";

            ///////////////////////////////////////*SORTING OUT THE ROWS*////////////////////////////////////////////////////
            foreach ($data_report['data_'.$entity] as $row)
            {
                $row_table [$row['log_id']][$row['entity_row_id']][$row['name']] =$row['value_field'];
            }

            ///printing out the rows
            if (!empty($row_table)){
                foreach ($row_table as $row_entity) { //foreach user thtat have inserted
                    foreach ($row_entity as $entity_group_id) { //foreach group of entities
                        $table_entity .= "<tr>";

                        foreach ($table_headers as $column) { //foreach value of a group of entities
                            if (isset($entity_group_id[$column])){ //to avoid errors
                                $table_entity .= "<td>" . $entity_group_id[$column] . "</td>";
                            }else{
                                $table_entity .= "<td></td>";
                            }
                        }
                        $table_entity .= "</tr>";
                    }

                }
            }

        }else{
            $table_entity .= "<tr><th> There are not rows to show </th></tr>";
        }
        $table_entity .= "</table>";
        return $table_entity;
    }
    function filter_report_SQL($entity, $id, $value_to_search, $comparison)
    {
        $sql = array();
        if ($id != 'comparison') {
                $value_string = $value_to_search;
                $sql['columns'] = "ud_" . $id . ".id AS id_field, ud_" . $id . ".value_field AS value_field"
                        . $id . " , uf_" . $id . ".name " . "AS name , ud_" . $id . ".entity_row_id ";
               // $sql['columns'] =" ud_".$id.".* as ll ";
                $sql['join'] = "\n JOIN ".$entity."_data AS ud_" . $id . " ON user_log.id = ud_" . $id . ".log_id AND  ud_" . $id . ".field_id = " . $id;
                $sql['join'] .= "\n JOIN ".$entity."_fields AS uf_" . $id . " ON uf_" . $id . ".id = ud_" . $id . ".field_id";
                 /*var_dump($value_to_search);
                 var_dump($comparison);*/
                //var_dump(count($value_to_search));
                if (is_array($value_to_search)) {
                    $value_string = '';
                    foreach ($value_to_search as $value) {


                        $value_string .= $value . ",";
                    }
                    $value_string = substr($value_string, 0, -1);
                }


                $type_comparison = $this->comparison($comparison);

                ($comparison == 'content') ? $sub_query = " '%" . $value_string . "%' " : $sub_query = " '" . $value_string . "' ";

                $sql['where'] = " ud_" . $id . ".value_field " . $type_comparison . $sub_query;
        }
        //var_dump($sql);
        //die;
        return $sql;
    }

    /* public function get_data_report($table, $columns, $limit_rows=''){
        $parameters='';
        //echo $limit_rows;
        if (!isset($_SESSION['report_form_search'])){$parameters='';}else{$parameters=$_SESSION['report_form_search'];}
var_dump($parameters);
$data_users =  $this->get_records($table,$columns, $parameters,'=', " ORDER BY id ASC ".$limit_rows, true);

$users_total = $this->count_column_table($table, array('id'=>'total'), $parameters);
//var_dump($_SESSION);
return (array('data_users'=>$data_users, 'users_total'=>$users_total));

}*/
}