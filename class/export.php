<?php
class Export
{
    function __construct()
    {

    }
    function sort_out_rows($data_report){
        foreach ($data_report['data_users'] as $row)
        {
            $row_table [$row['log_id']][$row['name']] =$row['value_field'];
        }
        return $row_table;
    }
    function get_headers ($data_report){
        foreach ($data_report['data_users'] as $key_header => $header)
        {
            $table_headers [$header['name']] =$header['name'];

        }
        return $table_headers;
    }
    function getletterFromNumber($num) {
        $numeric = $num % 26;
        $letter = chr(65 + $numeric);
        $num2 = intval($num / 26);
        if ($num2 > 0) {
            return $this->getletterFromNumber($num2 - 1) . $letter;
        } else {
            return $letter;
        }
    } //for Excel
    function export_XLS($data_report, $document_name)
    {

        /** Error reporting */
        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);
        date_default_timezone_set('Europe/London');

        if (PHP_SAPI == 'cli')
            die('This example should only be run from a Web Browser');

        /** Include PHPExcel */
        require_once dirname(dirname(__FILE__)) . '/export/PHPExcel/Classes/PHPExcel.php';


// Create new PHPExcel object
        $objPHPExcel = new PHPExcel();
/*
// Set document properties
        $objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
            ->setLastModifiedBy("Maarten Balliauw")
            ->setTitle("Office 2007 XLSX Test Document")
            ->setSubject("Office 2007 XLSX Test Document")
            ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Test result file");*/


// ADD HEADER COLUMNS AND DATA
        ///////////////////////////////////////*SORTING OUT THE ROWS*////////////////////////////////////////////////////

       $table_headers =$this->get_headers($data_report);
        $column_letter=0;
        foreach ($table_headers as $header)
        {
            $column_name_position = $this->getletterFromNumber($column_letter);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($column_name_position . "1", $header); //HEADERS
            $column_letter++;
        }
        $row_number = 2; //because the first one was for the headers
        //var_dump($row_table);
        $column_letter=0;
        $row_table = $this->sort_out_rows($data_report);
        foreach ($row_table as $column_name=>$data)
        {
            $column_letter=0;
            foreach ($table_headers as $header) {
                $column_name_position = $this->getletterFromNumber($column_letter);
                if (isset($data[$header]))
                {
                    //var_dump($data[$header]);
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($column_name_position . $row_number, $data[$header]);
                }else{
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($column_name_position . $row_number, '');
                }
                $column_letter++;
            }

            $row_number++;
        }
        //die;
        header('Content-type: text/'.'xls');
        header("Content-disposition: attachment; filename=".$document_name.".".'xls');
        header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');
// If you're serving to IE over SSL, then the following may be needed
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0
// Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        die;
    }
    function export_CSV($data_report, $document_name){

        $fp = fopen('php://output', 'w');
        $headersent = false;
        //display($data_report['data_users']);


        if (!empty($data_report['data_users']) || isset($data_report['data_users'])) {

            ////////////////////////////*SORTING OUT THE HEADERS*/////////////////////////////////////////////////////
            $table_headers =$this->get_headers($data_report);
            //printing out the headers

            fputcsv($fp,$table_headers);

            ///////////////////////////////////////*SORTING OUT THE ROWS*////////////////////////////////////////////////////
            $row_table = $this->sort_out_rows($data_report);
            //die;
            ///printing out the rows
            foreach ($row_table as $row)
            {
                if (isset($row)){
                    fputcsv($fp,$row);
                }else{
                    fputcsv($fp,'');
                }
            }

        }else{
            echo "There are not rows to show";

        }
        fclose($fp);

        header('Content-type: text/csv');
        header("Content-disposition: attachment; filename=".$document_name.".csv");
        header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');
// If you're serving to IE over SSL, then the following may be needed
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0
    }
    function export_XML($data_report, $document_name)
    {
        //create a new xmlwriter object
        $xml = new XMLWriter();
//using memory for string output
        $xml->openMemory();
//set the indentation to true (if false all the xml will be written on one line)
        $xml->setIndent(true);
//create the document tag, you can specify the version and encoding here
        $xml->startDocument();
//Create an element
//var_dump($data_report['data_users']);
//Write to the element
        ///////////////////////////////////////*SORTING OUT THE ROWS*////////////////////////////////////////////////////
        $row_table = $this->sort_out_rows($data_report);
        //var_dump($row_table);
        foreach ($row_table as $student_row=>$user_data) {
            $xml->startElement("User");
            foreach ($user_data as $column_name => $data) {
                //Write to the element
                if (isset($column_name))
                {
                    $column_name = str_replace(' ', '_', $column_name);
                    /*var_dump($column_name);
                    var_dump($data);
                    echo ("----------");*/
                    $xml->writeElement($column_name, $data);
                }
            }
            $xml->endElement(); //End the element
        }
        //die;
        header("Content-Type: application/force-download; name=\"$document_name.xml");
        header("Content-type: text/xml");
        header("Content-Transfer-Encoding: binary");
        header("Content-Disposition: attachment; filename=\"$document_name.xml");
        header("Expires: 0");
        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: no-cache");
//output the xml (obviosly this output could be written to a file)
        echo $xml->outputMemory();
        die;
    }
    function export_DOC($data_report, $document_name)
    {

        //require_once '../PHPWord.php';
        require_once dirname(dirname(__FILE__)).'/export/PHPWord/PHPWord.php';

// New Word Document
        $PHPWord = new PHPWord();

// New portrait section
        $section = $PHPWord->createSection();
/////////////////////////////STYLE////////////////////////////////////////////////////
// Define table style arrays
        $styleTable = array('borderSize'=>6, 'borderColor'=>'000000', 'cellMargin'=>80);
        $styleFirstRow = array('borderBottomSize'=>18, 'borderBottomColor'=>'0000FF', 'bgColor'=>'b3f0ff');

// Add table style
        $PHPWord->addTableStyle('myOwnTableStyle', $styleTable, $styleFirstRow);
// Add table
        $table = $section->addTable();

        $table->addRow();
        $table_headers =$this->get_headers($data_report);
        foreach ($table_headers as $header)
        {
            $table->addCell(1750, $styleFirstRow)->addText("$header");

        }

        $row_table = $this->sort_out_rows($data_report);
        foreach ($row_table as $column_name=>$data) {
            $table->addRow();
            foreach ($table_headers as $header)
            {
                if (isset($data[$header]))
                {
                    $table->addCell(1750, $styleTable)->addText("$data[$header]");
                }else{
                    $table->addCell(1750, $styleTable)->addText("");
                }
            }
        }
//die;
// Save File
        $objWriter = PHPWord_IOFactory::createWriter($PHPWord, 'Word2007');
        header("Content-type: application/vnd.ms-word");
        header("Content-Disposition: attachment;Filename='$document_name'.doc");
        $objWriter->save('php://output');
    }
    function export_PDF($data_report, $document_name, $orientation)
    {
        require_once(dirname(dirname(__FILE__)).'/class/PDF.php');
        require_once(dirname(dirname(__FILE__)).'/export/TCPDF/examples/tcpdf_include.php');
        require_once(dirname(dirname(__FILE__)).'/export/TCPDF/tcpdf.php');

// create new PDF document
        $pdf = new MYPDF($orientation, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
// set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Nicola Asuni');
        $pdf->SetTitle('TCPDF Example 011');
        $pdf->SetSubject('TCPDF Tutorial');
        $pdf->SetKeywords('TCPDF, PDF, example, test, guide');
// set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 011', PDF_HEADER_STRING);
// set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
// set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
// set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
// set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
// set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
// set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/TCPDF/examples/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }
// ---------------------------------------------------------
// set font
        $pdf->SetFont('helvetica', '', 12);
// add a page
        $pdf->AddPage();
// column titles
        $header = array('Country', 'Capital', 'Area (sq km)', 'Pop. (thousands)');
// data loading
        $data = $pdf->LoadData('TCPDF/examples/data/table_data_demo.txt');
// print colored table
        $headers = $this->get_headers($data_report);
        $row_table = $this->sort_out_rows($data_report);
        foreach ($row_table as $user_log_id=>$data)
        {
            foreach ($headers as $header)
            {
                if (isset($data[$header])) {
                    $array[$user_log_id][$header] = $data[$header];
                }else{
                    $array[$user_log_id][$header] = '';
                }
            }

        }

        ($orientation!='L')?$columns_size=180:$columns_size=270;
        $pdf->ColoredTable($headers, $array, $columns_size);
// ---------------------------------------------------------
// close and output PDF document
        $pdf->Output('example_011.pdf', 'I');

    }
    function html_display_export_form(){
    $html = "<label class=''>Export as :</label>
                            <form name='form-export_document'' action='../export/index.php' method='post'>
                            <select name='select-export_report'>
                                <option name=''>Select a type</option>
                                <option name='CSV' value='CSV'>CSV</option>
                                <option name='XLS' value='XLS'>XLS</option>
                                <option name='DOC' value='DOC'>DOC</option>
                                <option name='PDF_portrait' value='PDF_portrait'>PDF (Portrait)</option>
                                <option name='PDF_lasndscape' value='PDF_landscape'>PDF (Landsacape)</option>
                                <option name='XML' value='XML'>XML</option>
                            </select>
                            <button class=' btn btn-default btn-sm' type='submit' name='btn-export_document'>Export</button>
                            </form>";

    return $html;
    }
}

