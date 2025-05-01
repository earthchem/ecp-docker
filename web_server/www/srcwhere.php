<?PHP
/**
 * srcwhere.php
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

if (!isset($srcwhere)) {

$source_array=array();
$srcwhere=''; // If fewer than 'all' sources were chosen, define a bit of sql limiting the sources chosen, for the sql , e.g. "" or " AND (source = 'usgs' OR source = 'georoc') "
$sourcelinktext="";


include_once("db.php");

$datasources=$db->get_results("select * from datasources where active='y' order by ordernum");


foreach($datasources as $ds){

	if (isset($_POST[$ds->name]) and $_POST[$ds->name]!="false" and $_POST[$ds->name]!="") {
		$source_array[]=$ds->name;
		eval("\$".$ds->name."=\"true\";");
		$sourcelinktext.="&".$ds->name."=true";
		
	} elseif (isset($_GET[$ds->name]) and $_GET[$ds->name]!="false" and $_GET[$ds->name]!="") {
		$source_array[]=$ds->name;
		eval("\$".$ds->name."=\"true\";");
		$sourcelinktext.="&".$ds->name."=true";
	}else{
		eval("\$".$ds->name."=\"false\";");
	}

}


//print_r($source_array);



/*
if (isset($_POST['navdat']) and $_POST['navdat']!="false" and $_POST['navdat']!="") {
	$source_array[]='navdat';
	$navdat="true";
} elseif (isset($_GET['navdat']) and $_GET['navdat']!="false" and $_GET['navdat']!="") {
	$source_array[]='navdat';
	$navdat="true";
} // if

if (isset($_POST['georoc']) and $_POST['georoc']!="false" and $_POST['georoc']!="") {
	$source_array[]='georoc';
	$georoc="true";
} elseif (isset($_GET['georoc']) and $_GET['georoc']!="false" and $_GET['georoc']!="") {
	$source_array[]='georoc';
	$georoc="true";
} // if
if (isset($_POST['petdb']) and $_POST['petdb']!="false" and $_POST['petdb']!="") {
	$source_array[]='petdb';
	$petdb="true";
} elseif (isset($_GET['petdb']) and $_GET['petdb']!="false" and $_GET['petdb']!="") {
	$source_array[]='petdb';
	$petdb="true";
} // if

if (isset($_POST['usgs']) and $_POST['usgs']!="false" and $_POST['usgs']!="") {
	$source_array[]='usgs';
	$usgs="true";
} elseif (isset($_GET['usgs']) and $_GET['usgs']!="false" and $_GET['usgs']!="") {
	$source_array[]='usgs';
	$usgs="true";
} // if

if (isset($_POST['seddb']) and $_POST['seddb']!="false" and $_POST['seddb']!="") {
	$source_array[]='seddb';
	$seddb="true";
} elseif (isset($_GET['seddb']) and $_GET['seddb']!="false" and $_GET['seddb']!="") {
	$source_array[]='seddb';
	$seddb="true";
} // if



if (isset($_POST['ganseki']) and $_POST['ganseki']!="false" and $_POST['ganseki']!="") {
	$source_array[]='ganseki';
	$ganseki="true";
} elseif (isset($_GET['ganseki']) and $_GET['ganseki']!="false" and $_GET['ganseki']!="") {
	$source_array[]='ganseki';
	$ganseki="true";
} // if

*/



//echo "count source array = ".count($source_array)."<br>";
//echo "count ds = ".count($datasources)."<br>";








$srcwhere = "";
if(count($source_array)>0){
if (count($source_array) != count($datasources)) {
	$srcwheredelim="";
	foreach($datasources as $ds) {
		if(in_array($ds->name,$source_array)){
			$srcwhere .= $srcwheredelim.$ds->pkey;
			$srcwheredelim=",";
		}
	}
	
	//$srcwhere = " AND ( samp.sourceint in ($srcwhere) )";
	$srcwhere = " AND ( sourceint in ($srcwhere) )";


} // if
} // if
} // if

$searchdatasource = implode(",",$source_array);

//echo "srcwhere = $srcwhere<br>";

?>