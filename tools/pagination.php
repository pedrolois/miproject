<?php

//PAGINATION/////////////////////////////////
//$no_rows_to_show_in_table --> This a variable in global $CONFIG to show the numer of rows you want per page
//($data_report['users_total']['total']=='')? $data_report['users_total']['total']=0 :'';
//var_dump($data_report);
//tengo que mirar la query donde carga el total
$entity_total= !isset ($data_report[$entity.'_total'][0]['total']) ? 0:$data_report[$entity.'_total'][0]['total'];
var_dump($no_rows_to_show_in_table);
$total_pages = ceil($entity_total/$no_rows_to_show_in_table);
$actual_page = ($_SESSION['pagination']/$no_rows_to_show_in_table)+1;
var_dump($total_pages);
if ($total_pages>1) {
    $pagination = "<table><form id='user_form' action='" . $_SERVER['PHP_SELF'] . "' method='get'>";
/// <<--
    $pagination .= "<td><button class='glyphicon glyphicon-fast-backward' name='pagination' type='submit' value='0'></button></td>";
/// <-
    $prev = (($_SESSION['pagination'] - $no_rows_to_show_in_table >= 0) ? $_SESSION['pagination'] - $no_rows_to_show_in_table : 0);
    $pagination .= "<td><button class='glyphicon glyphicon-arrow-left' name='pagination' type='submit' value='" . $prev . "'></button></td>";
//PAGE NUMBERS
    for ($page = 0; $total_pages > $page; $page++)
    {
        $where_start_limit_sql = $page * $no_rows_to_show_in_table;
        if (($page == ($actual_page + 1)) || ($page == $actual_page) ||($page == ($actual_page - 1))) //this is just to show just 3 buttons
        {
        $pagination .= "<td class=''><button class='btn-group-sm' name='pagination' type='submit' value='$where_start_limit_sql'>" . ($page+1) . "</button></td>";
        }

    }
/// -->
    $next = (($_SESSION['pagination'] + $no_rows_to_show_in_table <= $where_start_limit_sql) ? $_SESSION['pagination'] + $no_rows_to_show_in_table : $where_start_limit_sql);
    $pagination .= "<td><button class='glyphicon glyphicon-arrow-right' name='pagination' type='submit' value='" . $next . "'></button></td>";
/// -->>
    $pagination .= "<td><button class='glyphicon glyphicon-fast-forward' name='pagination' type='submit' value='$where_start_limit_sql'></button></td>";
    $pagination .= "</form></table>";
}
//TOTALS//////////////////////////////////////////

 $totals ="Page: $actual_page | Total users in the system: ".$users_total;

//display($data_report);