<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once('/tcpdf/config/lang/eng.php');
// require_once('/tcpdf/tcpdf.php');
require_once('/tcpdf/tcpdf_include.php');
// Extend the TCPDF class to create custom Header and Footer
class Cetak_pdf{
	public function create($html="&nbsp",$filename="report.pdf",$page_ori='P',$letter='A4',$fontsize=9){
		// create new PDF document
		// $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf = new TCPDF($page_ori, PDF_UNIT, $letter, true, 'UTF-8', false);

		// set document information
		$pdf->SetCreator(PDF_CREATOR);
		//$pdf->SetAuthor('Nicola Asuni');
		//$pdf->SetTitle('TCPDF Example 006');
		//$pdf->SetSubject('TCPDF Tutorial');
		//$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

		// set default header data
		// $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

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
		if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
			require_once(dirname(__FILE__).'/lang/eng.php');
			$pdf->setLanguageArray($l);
		}

		// ---------------------------------------------------------

		// set font
		$pdf->SetFont('freesans', '', $fontsize);

		// add a page
		$page_format = array(
			'Dur' => 3,
			'trans' => array(
				'D' => 1.5,
				'S' => 'Split',
				'Dm' => 'V',
				'M' => 'O'
			),
			'Rotate' => 0,
			'PZ' => 1,
		);

	// Check the example n. 29 for viewer preferences

	// add first page ---
	$pdf->AddPage($page_ori, $page_format, false, false);

		// writeHTML($html, $ln=true, $fill=false, $reseth=false, $cell=false, $align='')
		// writeHTMLCell($w, $h, $x, $y, $html='', $border=0, $ln=0, $fill=0, $reseth=true, $align='', $autopadding=true)

		// create some HTML content
		$html2 = '<h2 align="center">SASARAN KERJA PEGAWAI</h2>
		<table border="1" cellspacing="3" cellpadding="4" border-style="solid">
			<tr>
				<th>#</th>
				<th align="right">RIGHT align</th>
				<th align="left">LEFT align</th>
				<th>4A</th>
			</tr>
			<tr>
				<td>1</td>
				<td bgcolor="#cccccc" align="center" colspan="2">A1 ex<i>amp</i>le <a href="http://www.tcpdf.org">link</a> column span. One two tree four five six seven eight nine ten.<br />line after br<br /><small>small text</small> normal <sub>subscript</sub> normal <sup>superscript</sup> normal  bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla<ol><li>first<ol><li>sublist</li><li>sublist</li></ol></li><li>second</li></ol><small color="#FF0000" bgcolor="#FFFF00">small small small small small small small small small small small small small small small small small small small small</small></td>
				<td>4B</td>
			</tr>
			<tr>
				<td>aa</td>
				<td bgcolor="#0000FF" bgcolor="yellow" align="center">lk</td>
				<td bgcolor="#FFFF00" align="left"><font bgcolor="#FF0000">Red</font> Yellow BG</td>
				<td>4C</td>
			</tr>
			<tr>
				<td>1A</td>
				<td rowspan="2" colspan="2" bgcolor="#FFFFCC">2AA<br />2AB<br />2AC</td>
				<td bgcolor="#FF0000">4D</td>
			</tr>
			<tr>
				<td>1B</td>
				<td>4E</td>
			</tr>
			<tr>
				<td>1C</td>
				<td>2C</td>
				<td>3C</td>
				<td>4F</td>
			</tr>
		</table>';

		// output the HTML content
		$pdf->writeHTML($html, true, false, true, false, '');
		//Close and output PDF document
		$pdf->Output($filename, 'D'); // I or D
	}
}