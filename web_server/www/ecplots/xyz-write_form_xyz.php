<?php
// This script writes/rewrites the div and form that contain the form elements for x,y,z and their ratios (e.g. user can define SiO2/K2O as x axis).
// This is included directly in the main plotting script file. 
// This script is also executed onChange of the x, y, z dropdown select boxes, from ajax-like javascript in xyplotter.js (url variables are retrieved from form variables, and passed back as url variables).
// Save some variables as hidden form fields, so they can be retrieved by the javascript in xyplotter.js
// There is no submit button on this form. A single button submits both forms with javascript. 
//echo $_SERVER['HTTP_REFERER'] ; echo " is the http referrer "; 

?>

<form name="form_xyz" action="" method="post">
<div id="div_xyz" style="visibility:visible"> 
<?php //print_r($_GET); echo "<p>"; print_r($_POST);
// Get the referring website first, before declaring the class. The class needs it. 
$referring_website="";
if (isset($_POST['referring_website'])) {
	$referring_website=$_POST['referring_website'];
} elseif (isset($_GET['referring_website'])) {
	$referring_website=$_GET['referring_website'];	
} 
$earthchem=false;$navdat=false;
if ($referring_website=='earthchem') {
	$earthchem=true;
} elseif ($referring_website=='navdat') {
	$navdat=true;
}
// Load database drivers and connect to the right database. Be sure to do this before including xyzClass.php which needs the $db object
if ($earthchem) {
	require ("includes/earthchem_db.php"); // database driver, and db connect for EarthChem
}
elseif ($navdat) {
	require ("includes/navdat_db.php"); // database driver, and db connect for EarthChem
} else {
	echo "<p>No website has been identified. Please start a new search and try again.<p>"; exit;
}


include('xyzClass.php'); // All the hard work of building sql and deciding which field names to offer in the dropdown boxes, is done in this file.
// Declare the new object 
$obj = new xyzClass;
$obj->referring_website=$referring_website;
$obj->earthchem=$earthchem;
$obj->navdat=$navdat;
if ($earthchem) {$obj->alias = "";} elseif ($navdat) {$obj->alias = "norm.";} // alias for a table in the search query sql (we only need this in the class). See var declaration in class for more explanation.
// Hide this, so the javascript can retrieve it and pass it back here (GET), or the main script can retrieve it (POST)
echo '<input type="hidden" name="referring_website" id="referring_website" value="'.$obj->referring_website.'">';
// We only really need pkey for EarthChem, but we get it from Navdat too.
if (isset($pkey) and is_numeric($pkey)) { 
	// nothing
} else {
	include('includes/get_pkey.php');
} 
$obj->pkey=$pkey; 
// Hide this, so the javascript can retrieve it and pass it back here (GET), or the main script can retrieve it (POST)
echo '<input type="hidden" name="pkey" id="pkey" value="'.$obj->pkey.'">';

$_POST['navdat']=true;$_POST['petdb']='true';
$source_array=array(); // The first time through, we get the 'source' values from the results page 
if (isset($_POST['srcwhere'])) {
	$obj->srcwhere = $_POST['srcwhere']; // If the checked fewer than 4 sources (navdat, petdb, georoc, usgs), a bit of sql is needed to limit the search to those sources
}
// Variables from and where are bits of sql for the user's search query. They might be passed thru the url from the javascript in xyplotter.js
// If they are not passed in here, they are created in the class. Hide them in hidden form fields below.
$obj->from="";$obj->where="";
if (isset($_POST['from']) and isset($_POST['where'])) {
	$obj->from=$_POST['from'];
	$obj->where=$_POST['where'];
} elseif (isset($_GET['from']) and isset($_GET['where'])) {
	$obj->from=$_GET['from'];
	$obj->where=$_GET['where'];		
} 
if ($navdat) {
	if (isset($_GET['navdatsqlstatement'])) {
		$navdatsqlstatement= $_GET['navdatsqlstatement'];
	} elseif (isset($_POST['navdatsqlstatement'])) {
		$navdatsqlstatement = $_POST['navdatsqlstatement'];
	} else {
		$navdatsqlstatement = '';
	}
	$obj->navdatsqlstatement = $navdatsqlstatement; 
}
if (isset($_GET['x_column_name'])) {
	$x_column_name= $_GET['x_column_name'];
} elseif (isset($_POST['x_column_name'])) {
	$x_column_name = $_POST['x_column_name'];
} else {
	$x_column_name = '';
}
if (isset($_GET['x_ratio'])) {
	$x_ratio= $_GET['x_ratio'];
} elseif (isset($_POST['x_ratio'])) {
	$x_ratio = $_POST['x_ratio'];
} else {
	$x_ratio = '';
}
if (isset($_GET['x_ratio_part'])) {
	$x_ratio_part= $_GET['x_ratio_part'];
} elseif (isset($_POST['x_ratio_part'])) {
	$x_ratio_part = $_POST['x_ratio_part'];
} else {
	$x_ratio_part = 'numerator';
}
if (isset($_GET['y_column_name'])) {
	$y_column_name= $_GET['y_column_name'];
} elseif (isset($_POST['y_column_name'])) {
	$y_column_name = $_POST['y_column_name'];
} else {
	$y_column_name = '';
}
if (isset($_GET['y_ratio'])) {
	$y_ratio= $_GET['y_ratio'];
} elseif (isset($_POST['y_ratio'])) {
	$y_ratio = $_POST['y_ratio'];
} else {
	$y_ratio = '';
}
if (isset($_GET['y_ratio_part'])) {
	$y_ratio_part= $_GET['y_ratio_part'];
} elseif (isset($_POST['y_ratio_part'])) {
	$y_ratio_part = $_POST['y_ratio_part'];
} else {
	$y_ratio_part = 'numerator';
}
if (isset($_GET['z_column_name'])) {
	$z_column_name= $_GET['z_column_name'];
} elseif (isset($_POST['z_column_name'])) {
	$z_column_name = $_POST['z_column_name'];
} else {
	$z_column_name = '';
}
if (isset($_GET['z_ratio'])) {
	$z_ratio= $_GET['z_ratio'];
} elseif (isset($_POST['z_ratio'])) {
	$z_ratio = $_POST['z_ratio'];
} else {
	$z_ratio = '';
}
if (isset($_GET['z_ratio_part'])) {
	$z_ratio_part= $_GET['z_ratio_part'];
} elseif (isset($_POST['z_ratio_part'])) {
	$z_ratio_part = $_POST['z_ratio_part'];
} else {
	$z_ratio_part = 'numerator';
}
if (isset($_POST['master_fieldnames_list'])) {
	$master_fieldnames_list=$_POST['master_fieldnames_list'];
} elseif (isset($_GET['master_fieldnames_list'])) {
	$master_fieldnames_list=$_GET['master_fieldnames_list']; 
} else {
	$master_fieldnames_list='';
}
$obj->master_fieldnames_list = $master_fieldnames_list; //echo "<p>in xyzwrite at 100 master_fieldnames_list $obj->master_fieldnames_list"; /*
if (isset($_POST['master_displaynames_list'])) {
	$master_displaynames_list=$_POST['master_displaynames_list'];
} elseif (isset($_GET['master_displaynames_list'])) {
	$master_displaynames_list=$_GET['master_displaynames_list']; 
} else {
	$master_displaynames_list='';
}
$obj->master_displaynames_list = $master_displaynames_list; //echo "<p>in xyzwrite at 100 master_displaynames_list $obj->master_displaynames_list"; /*

$obj->x=$x_column_name;
$obj->y=$y_column_name;
$obj->z=$z_column_name;
$obj->x_ratio=$x_ratio;
$obj->y_ratio=$y_ratio;
$obj->z_ratio=$z_ratio;
$obj->x_ratio_part=$x_ratio_part;
$obj->y_ratio_part=$y_ratio_part;
$obj->z_ratio_part=$z_ratio_part; 
$fieldnames_to_offer_array=$obj->make_fieldnames_to_offer_array(); // This also makes some object variables the first time: from, where, master_fieldnames_list, master_fieldnames_array - which we never have to build again because we pass them to the javascript hide('div_xyz');redoColumnSelections() which passes them back to this script. This technique is the only way to make the repopulation of the dropdown select boxes fast enough to be practical.
asort($fieldnames_to_offer_array); // sort on the values (display_name)

if ($obj->x != "") { 
$success = $obj->make_plot_sql(); // The sql to retrieve the data for the plot 
echo '<input type="hidden" name="plot_sql" value="'.$obj->plot_sql.'">'; // The plotting script will not have to recreate the sql statement
echo '<input type="hidden" name="xtitle" value="'.$obj->xtitle.'">';
echo '<input type="hidden" name="ytitle" value="'.$obj->ytitle.'">';
echo '<input type="hidden" name="ztitle" value="'.$obj->ztitle.'">';
echo '<input type="hidden" name="xaxis" value="'.$obj->xaxis.'">';
echo '<input type="hidden" name="yaxis" value="'.$obj->yaxis.'">';
echo '<input type="hidden" name="zaxis" value="'.$obj->zaxis.'">';
$success = $obj->make_min_max_count_sql(); // The sql to retrieve the data for the plot 
echo '<input type="hidden" name="min_max_count_sql" value="'.$obj->min_max_count_sql.'">'; // The plotting script will not have to recreate the sql statement
} // if x != ""
echo '<input type="hidden" name="from" value="'.$obj->from.'">';
echo '<input type="hidden" name="where" value="'; echo $obj->where; echo '">'; // Made with make_fieldnames_to_offer_array() method. Hide it to be retrieved by the javascript.
echo '<input type="hidden" name="master_fieldnames_list" id="master_fieldnames_list" value="'.$obj->master_fieldnames_list.'">'; // made the first time in the method just above
echo '<input type="hidden" name="master_displaynames_list" id="master_displaynames_list" value="'.$obj->master_displaynames_list.'">'; // made the first time in the method just above
asort($fieldnames_to_offer_array); // sort 2D array on the values 

echo '<table border=0 cellspacing=5 cellpadding=0 style="margin-left:15px">';

// Now write the form elements 
$axes=array('x','y','z'); // Write a dropdown select box etc. for each of these axes
$axis_parts=array('column_name','ratio'); // Write the same for the column and a ratio

foreach ($axis_parts as $axis_part) 
{
echo "
<tr>";
foreach ($axes as $axis) 
{


if ($axis_part=='column_name') 
{
echo "<td><i>$axis</i></td><td>";
// onChange in the dropdown select boxes, hide the div so the user cannot make a selection while the boxes are being repopulated, they will reappear when the div is rewritten
switch ($axis) {
case "x": {echo "<select name='x_column_name' onChange=\" hide('div_xyz');redoColumnSelections_xyz('x'); document.form_xyz.mapbackground.checked = false; \">"; break;}
case "y": {echo "<select name='y_column_name' onChange=\" hide('div_xyz');redoColumnSelections_xyz('y'); document.form_xyz.mapbackground.checked = false; \">"; break;}
case "z": {echo "<select name='z_column_name' onChange=\" if (document.form_xyz.z_intervals_num.selectedIndex=='') {document.form_xyz.z_intervals_num.selectedIndex=3;} hide('div_xyz');redoColumnSelections_xyz('z'); showlayer('div_z_options'); document.form_xyz.mapbackground.checked = false; \">"; break;}
// For the z axis, in the line just above: If the user selects a value for $z_column_name, show the div with additional options, and enforce a default value for $z_intervals_num.
// Eileen: also add some code that does not let all this happen if the user selects "" for $z_column_name
} // switch 
} // if 

if ($axis_part=='ratio') {
echo "<td><i>n</i></td><td>";
switch ($axis) {
case "x": {echo "<select name='x_ratio' onChange=\"hide('div_xyz');redoColumnSelections_xyz('x'); document.form_xyz.mapbackground.checked = false; \">"; break;}
case "y": {echo "<select name='y_ratio' onChange=\"hide('div_xyz');redoColumnSelections_xyz('y'); document.form_xyz.mapbackground.checked = false; \">"; break;}
case "z": {echo "<select name='z_ratio' onChange=\"hide('div_xyz');redoColumnSelections_xyz('z'); document.form_xyz.mapbackground.checked = false; \">"; break;}
} // switch 
} // if 

echo "<option value=''>"; // All of the dropdown boxes get a selection of "none"

// The x,y & z ratio selection boxes get an additional selection of '1' (e.g. for 1/x or x/1)
if ($axis_part=='ratio') {
echo "<option value='1' ";
switch ($axis) {
case "x": {if ($x_ratio==1) {echo " selected ";} break;}
case "y": {if ($y_ratio==1) {echo " selected ";} break;}
case "z": {if ($z_ratio==1) {echo " selected ";} break;}
}
echo ">1";
}

$i=0;
foreach($fieldnames_to_offer_array as $key=>$val) { // key = field name, val = display name 
$i++; // keep track of the # of options, just for debug purposes temporarily
echo "<option id='$axis-$key' value='$key' ";

if ($axis_part == 'column_name') {
switch ($axis) {
case "x": {if ($key==$x_column_name) {echo " selected ";} break;}
case "y": {if ($key==$y_column_name) {echo " selected ";} break;}
case "z": {if ($key==$z_column_name) {echo " selected ";} break;}
}
}

if ($axis_part == 'ratio') {
switch ($axis) {
case "x": {if ($key==$x_ratio) {echo " selected ";} break;}
case "y": {if ($key==$y_ratio) {echo " selected ";} break;}
case "z": {if ($key==$z_ratio) {echo " selected ";} break;}
}
}

echo ">$val"; 
} // foreach axes as axis

echo "</select> "; 
echo "</td><td>&nbsp;</td>";

//echo " | $x_column_name $x_ratio | $y_column_name $y_ratio | $z_column_name $z_ratio ";

} // foreach axes as axis 
echo "<td ><font size=1>"; echo sizeof($fieldnames_to_offer_array); echo " selections</font></td>";
echo "</tr>";

} // foreach axis_parts as field

// Now offer the radio buttons for numerator/denominator choice for x,y,z ratios (ratio_part)
echo "<tr>
<td colspan=3><i>x/n</i> <input type='radio' name='x_ratio_part' value='denominator' ";
if ($x_ratio_part=='denominator') { echo " checked "; }
echo " onChange=\"hide('div_xyz');redoColumnSelections_xyz('x'); 
\"> &nbsp; <i>n/x</i> <input type='radio' name='x_ratio_part' value='numerator' ";
if ($x_ratio_part=='numerator') { echo " checked "; }
echo " onChange=\"hide('div_xyz');redoColumnSelections_xyz('x'); 
 
\"> </td>
<td colspan=3><i>y/n</i> <input type='radio' name='y_ratio_part' value='denominator' ";
if ($y_ratio_part=='denominator') { echo " checked "; }
echo " onChange=\"hide('div_xyz');redoColumnSelections_xyz('y'); 
\"> &nbsp; <i>n/y</i> <input type='radio' name='y_ratio_part' value='numerator' ";
if ($y_ratio_part=='numerator') { echo " checked "; }
echo " onChange=\"hide('div_xyz');redoColumnSelections_xyz('y'); 
 
\"> </td>
<td colspan=3><i>z/n</i> <input type='radio' name='z_ratio_part' value='denominator' ";
if ($z_ratio_part=='denominator') { echo " checked "; }
echo " onChange=\"hide('div_xyz');redoColumnSelections_xyz('z');
 
\"> &nbsp; <i>n/z</i> <input type='radio' name='z_ratio_part' value='numerator' ";
if ($z_ratio_part=='numerator') { echo " checked "; }
echo " onChange=\"hide('div_xyz');redoColumnSelections_xyz('z'); 
 
\"> </td>
<td> &nbsp; </td></tr>";
echo "</table>";
//print_r($obj);

// We opened a form in this script, above. We will add the </form> closing tag in the main script. 
echo "</div>"; 

?>
<script language="JavaScript">
document.getElementById('div_progress').innerHTML=''; /* hide the progress bar */ 
show('div_xyz');
</script>

<script language="JavaScript">
// The following function hides a div layer 
function hide(layer_ref) {
state = 'hidden';
if (document.all) { //IS IE 4 or 5 (or 6 beta)
eval( "document.all." + layer_ref + ".style.visibility = state");
}
if (document.layers) { //IS NETSCAPE 4 or below
document.layers[layer_ref].visibility = state;
}
if (document.getElementById && !document.all) {
maxwell_smart = document.getElementById(layer_ref);
maxwell_smart.style.visibility = state;
}
} // end function

// The following function shows a div layer 
function show(layer_ref,x) {
// layer_ref is the hidden div to show, x is the name of the form field to evaluate
//if (strlen(document.getElementById(x).value)>1) 
{
state = 'visible';
if (document.all) { //IS IE 4 or 5 (or 6 beta)
	eval( "document.all." + layer_ref + ".style.visibility = state");
}
if (document.layers) { //IS NETSCAPE 4 or below
	document.layers[layer_ref].visibility = state;
}
if (document.getElementById && !document.all) {
	document.getElementById(layer_ref).innerHTML = document.getElementById(x).value;
	thelength = (document.getElementById(x).value.length);
	document.getElementById(layer_ref).style.visibility = state;
}
} // end function

function upperCase(x)
{
var y=document.getElementById(x).value
document.getElementById(x).value=y.toUpperCase()
} // end function

function disable_xyz() { // disable the dropdown select boxes for x,y,z; not used because boxes cannot be repopulated during disable
document.form_xyz.x_column_name.disabled=true;
document.form_xyz.y_column_name.disabled=true;
document.form_xyz.z_column_name.disabled=true;
}
function enable_xyz() { // enable the dropdown select boxes for x,y,z; 
document.form_xyz.y_column_name.enabled=true;
document.form_xyz.z_column_name.enabled=true;
}
function disable(disableIt) { // example of disabling a form element 
	document.frm.sel.disabled = disableIt;
}
function waiting(thediv) { // Display contents of div with something using innerHTML
document.getElementById(thediv).innerHTML='hello kitty';
}


</script>


