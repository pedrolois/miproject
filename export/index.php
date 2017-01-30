<?php

require_once(dirname(dirname(__FILE__)).'/class/export.php');
require_once(dirname((dirname(__FILE__))).'/class/entity.php');
$export_doc = new Export();
$userFields = new Entity();
//$array_table = array('first_name'=>'pedro', 'last_name'=>'','sex'=>'', 'address1'=>'', 'city'=>'', 'county'=>'', 'country'=>'', 'phone1'=>'', 'phone2'=>'' );
session_start();
$data_report = $db_driver->get_data_report($userFields, "displayed_export=true");
//var_dump($data_report);
//die;
//var_dump($_POST);
if (isset($_POST['btn-export_document']))
{
    switch ($_POST['select-export_report'])
    {
        case 'CSV':
            $export_doc->export_CSV($data_report, "REPORT_CSV");
            break;
        case 'XLS':
            $export_doc->export_XLS($data_report, "REPORT_XLS");
            break;
        case 'XML':
            $export_doc->export_XML($data_report, "REPORT_XML",'xml');
            break;
        case 'DOC':
            $export_doc->export_DOC($data_report, "REPORT_DOC", 'DOC');
            break;
        case 'PDF_portrait':
            $export_doc->export_PDF($data_report, "REPORT_PDF",'P');
            break;
        case 'PDF_landscape':
            $export_doc->export_PDF($data_report, "REPORT_PDF", 'L');
            break;
    }
}


