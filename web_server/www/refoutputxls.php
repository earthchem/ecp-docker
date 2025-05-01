<?PHP
/**
 * refoutputxls.php
 *
 * longdesc
 *
 * LICENSE: This source file is subject to version 4.0 of the Creative Commons
 * license that is available through the world-wide-web at the following URI:
 * https://creativecommons.org/licenses/by/4.0/
 *
 * @category   Geochemistry
 * @package    EarthChem Portal
 * @author     Jason Ash <jasonash@ku.edu>
 * @copyright  IEDA (http://www.iedadata.org/)
 * @license    https://creativecommons.org/licenses/by/4.0/  Creative Commons License 4.0
 * @version    GitHub: $
 * @link       http://ecp.iedadata.org
 * @see        EarthChem, Geochemistry
 */

include("get_pkey.php");

include("db.php");

include("datasources.php");

include('srcwhere.php');

include('queries.php');

//echo nl2br($refstring);exit();

$rows=$db->get_results("$refstring");



	function xlsBOF() {
		echo pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);  
		return;
	}
	
	function xlsEOF() {
		echo pack("ss", 0x0A, 0x00);
		return;
	}
	
	function xlsWriteNumber($Row, $Col, $Value) {
		echo pack("sssss", 0x203, 14, $Row-1, $Col, 0x0);
		echo pack("d", $Value);
		return;
	}
	
	function xlsWriteLabel($Row, $Col, $Value ) {
		$L = strlen($Value);
		echo pack("ssssss", 0x204, 8 + $L, $Row-1, $Col, 0x0, $L);
		echo $Value;
	return;
	} 

	$randnum=rand(10000,99999);

    // Send Header
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Type: application/octet-stream");
    header("Content-Type: application/download");;
    header("Content-Disposition: attachment;filename=earthchem_references_$randnum.xls "); 
    header("Content-Transfer-Encoding: binary ");

    // XLS Data Cell
    
    $mydate=date("l, M. j, Y g:i:s a");

	xlsBOF();
	xlsWriteLabel(1,0,"Earthchem References $mydate");

	
	xlsWriteLabel(3,0,"");
	xlsWriteLabel(3,1,"TITLE");
	xlsWriteLabel(3,2,"JOURNAL");
	xlsWriteLabel(3,3,"AUTHOR");
	xlsWriteLabel(3,4,"PUB YEAR");

	$x=4;
	$i=1;

	foreach($rows as $row){

	xlsWriteNumber($x,0,$i);
	xlsWriteLabel($x,1,$row->title);
	xlsWriteLabel($x,2,$row->journal);
	xlsWriteLabel($x,3,strtoupper($row->author));
	xlsWriteNumber($x,4,$row->pubyear);

	$x++;
	$i++;

	}


	xlsEOF();
	exit();

?>