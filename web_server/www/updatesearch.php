<?PHP
/**
 * updatesearch.php
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

include "get_pkey.php";

include "db.php";

include("datasources.php");

include "srcwhere.php"; 

if (isset($_POST['showmethods'])) {
$showmethods=true;
} else {
$showmethods=false;
}
if (isset($_POST['showunits'])) {
$showunits=true;
} else {
$showunits=false;
}
$chemical_data_set=false; //A flag, set if the user checked at least one of the "chemical data" checkboxes. They must check at least one; if they don't we display a message and abort. 
if (isset($_POST['MAJORS'])) {
	//echo "majors: ".$_POST['MAJORS']."<br><Br>";
	$MAJORS = $_POST['MAJORS'];
	$chemical_data_set=true;
} else {
$majors ='';
}
if (isset($_POST['TRACES'])) {
	//echo "traces: ".$_POST['TRACES']."<br><Br>";
	$TRACES = $_POST['TRACES'];
	$chemical_data_set=true;
} else {
	$TRACES ='';
}
if (isset($_POST['ISOTOPES'])) {
	//echo "isotopes: ".$_POST['ISOTOPES']."<br><Br>";
	$ISOTOPES = $_POST['ISOTOPES'];
	$chemical_data_set=true;
} else {
	$ISOTOPES ='';
}

$updatesql = '';


//$group_array=array('MAJOR_OXIDES','ISOTOPE_RATIO','NOBLE_GAS','REE','U_SERIES','VOLATILE','TRACE_ELEMENTS','STABLE_ISOTOPE');
$group_array=array("MAJOR_OXIDE", "RATIO", "NOBLE_GAS", "RARE_EARTH_ELEMENT", "URANIUM_SERIES", "VOLATILE", "TRACE_ELEMENT", "STABLE_ISOTOPES", "RADIOGENIC_ISOTOPES");

// Process the form variables - an array of checkboxes - for each group 
foreach($group_array as $currgroup) {
	if (isset($_POST[$currgroup])) {
		
		//echo "$currgroup = ".$_POST[$currgroup]."<br><br>";
		
		//var_dump($_POST[$currgroup]);
		//echo "<br><br>";
		
		
		$list=implode(',',$_POST[$currgroup]); // The form variable is an array of checkboxes
		$chemical_data_set=true;
		$updatesql .= " $currgroup = '$list', ";
	} else {
		$list = '';
		$updatesql .= " $currgroup = '$list', "; // We have to null out a group that the user didn't check any boxes under 
	}
}

//add lepr manually here
if(isset($_POST["lepr"])){

	$list=implode(',',$_POST["lepr"]);
	$chemical_data_set=true;
	$updatesql .= " lepr_items = '$list', ";

}else{
	$list = '';
	$updatesql .= " lepr_items = '$list', ";
}


if (isset($_POST['searchopt'])) {
	$searchopt = $_POST['searchopt'];
} else {
	$searchopt ='';
}
$updatesql .= " searchopt = '$searchopt', "; 

if ($updatesql > '') {
	$updatesql = substr($updatesql,0,(strlen($updatesql)-2) ); // strip off the trailing comma 
}



//echo "updatesql: $updatesql<br><br><br>";



/*
if (isset($_POST['MAJOR_OXIDES'])) {
$MAJOR_OXIDES = implode(',',$_POST['MAJOR_OXIDES']);
$chemical_data_set=true;
} else {
$MAJOR_OXIDES ='';
} 
if (isset($_POST['ISOTOPE_RATIO'])) {
$ISOTOPE_RATIO = $_POST['ISOTOPE_RATIO'];
$chemical_data_set=true;
} else {
$ISOTOPE_RATIO ='';
}
if (isset($_POST['NOBLE_GAS'])) {
$NOBLE_GAS = $_POST['NOBLE_GAS'];
$chemical_data_set=true;
} else {
$NOBLE_GAS ='';
}
if (isset($_POST['REE'])) {
$REE = $_POST['REE'];
$chemical_data_set=true;
} else {
$REE ='';
}
if (isset($_POST['VOLATILE'])) {
$VOLATILE = $_POST['VOLATILE'];
$chemical_data_set=true;
} else {
$VOLATILE ='';
}
if (isset($_POST['U_SERIES'])) {
$U_SERIES = $_POST['U_SERIES'];
$chemical_data_set=true;
} else {
$U_SERIES ='';
}
if (isset($_POST['TRACE_ELEMENTS'])) {
$TRACE_ELEMENTS = $_POST['TRACE_ELEMENTS'];
$chemical_data_set=true;
} else {
$TRACE_ELEMENTS ='';
}
if (isset($_POST['STABLE_ISOTOPE'])) {
$STABLE_ISOTOPE = $_POST['STABLE_ISOTOPE'];
$chemical_data_set=true;
} else {
$STABLE_ISOTOPE ='';
}
*/

if (!$chemical_data_set) {
include("includes/ks_head.html");
echo "Please choose chemical data to display. At least one item must be checked.
<p>Use the Back button on your browser to return to the previous page.
";
include("includes/ks_footer.html");
exit;
}
/*
MAJOR_OXIDES,ISOTOPE_RATIO,NOBLE_GAS,REE,U_SERIES,VOLATILE,TRACE_ELEMENTS,STABLE_ISOTOPE
*/

$sql = "update search_query set ".$updatesql." where pkey=$pkey "; 
//echo "<p>$sql ";

//exit();

$updatesearch=$db->query($sql) or die("could not execute updatesearch query in updatesearch.php");

$rowtype=$_POST['rowtype'];

$dispmode=$_POST['dispmode'];
//echo "<p><a href='advancedoutputc.php?pkey=$pkey&georoc=$georoc&navdat=$navdat&petdb=$petdb&usgs=$usgs&dispmode=$dispmode&rowtype=$rowtype&showmethods=$showmethods&showunits=$showunits>next step</a> ";

$headerstring="Location:advancedoutputc.php?pkey=$pkey&dispmode=$dispmode&rowtype=$rowtype&showmethods=$showmethods&showunits=$showunits";

foreach($datasources as $ds){
	//eval("\$thisdsval=\$".$ds->name.";");
	//$headerstring.="&".$ds->name."=".$thisdsval;
}

//echo "$headerstring";exit();

header("$headerstring");

?>

