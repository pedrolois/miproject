<?php
// Include the main TCPDF library (search for installation path).
require_once(dirname(dirname(__FILE__)).'/export/TCPDF/examples/tcpdf_include.php');
require_once(dirname(dirname(__FILE__)).'/export/TCPDF/tcpdf.php');

// extend TCPF with custom functions
class MYPDF extends TCPDF {

    // Load table data from file
    public function LoadData($file) {
        // Read file lines
        $lines = file($file);
        $data = array();
        foreach($lines as $line) {
            $data[] = explode(';', chop($line));
        }
        return $data;
    }

    // Colored table
    public function ColoredTable($header_names,$data_report, $columns_size) {
        // Colors, line width and bold font
        $this->SetFillColor(255, 0, 0);
        $this->SetTextColor(255);
        $this->SetDrawColor(128, 0, 0);
        $this->SetLineWidth(0.3);
        $this->SetFont('', 'B');
        // Header
        //$header_names = array_keys($data_report['data_users'][0]);

        $num_headers = count($header_names);
        $columns_width = $columns_size/$num_headers;

        $lengths = array_map('strlen', $header_names);
        $maxlength = max($lengths);

        //var_dump($header_names);
        foreach ($header_names as $header){
            $this->MultiCell($columns_width, $maxlength, $header, 1, 'L', 1, 0, '', '', true);
        }

        $this->Ln();
        // Color and font restoration
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0);
        $this->SetFont('');

        // Data
        $fill = 0;
        foreach ($data_report as $rows)
        {
            $lengths = array_map('strlen', $rows);
            $maxlength = max($lengths);
            foreach($rows as $cell_value){
                $this->MultiCell($columns_width, $maxlength , $cell_value, 1, 'L', $fill, 0, '', '', true);
            }
            $this->Ln();
            $fill=!$fill;
        }
        $this->Cell(array_sum($header_names), 0, '', 'T');
    }
}

