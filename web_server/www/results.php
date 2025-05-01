<?PHP
/**
 * results.php
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


include "get_pkey.php"; // get the primary key for the current search
include "db.php"; // database drivers and connect, make $db object

include("datasources.php");

if($_POST["pkey"]!=""){
	include "srcwhere.php"; // get the sources the user checked on the search page, e.g. navdat, petdb, georoc, usgs, and make a bit of sql  

	$db->query("update search_query set srcwhere='$srcwhere', searchdatasource='$searchdatasource' where pkey=$pkey");

	header("Location: results?pkey=$pkey");
	
	exit();
}

$srcwhere=$db->get_var("select srcwhere from search_query where pkey=$pkey");

include "includes/ks_head.html";

//echo $_SERVER['REMOTE_ADDR'];

//var_dump($_POST);echo "<br><br>";exit();

//echo "pkey: $pkey";exit();

$sources = $db->get_var("select searchdatasource from search_query where pkey=$pkey");
$sources = explode(",",$sources);

//var_dump($sources);exit();

$formsources="";
$formsourcedelim="";
foreach($datasources as $ds){
	if(in_array($ds->name,$sources)){
		$val="true";
	}else{
		$val="false";
	}
	$formsources.=$formsourcedelim.'<input type="hidden" name="'.$ds->name.'" value="'.$val.'">';
	$formsourcedelim="\n";
}



?>
<h2>DATA&nbsp;ACCESS</h2>
<table class='ku_grid'>


<tr>
	<td valign='middle'><button class="submitbutton" onclick="window.location.href='advancedoptions?pkey=<?=$pkey?>'">Get Chemical Data</button></td>
	<td valign='middle'>Display samples in an HTML table, a Text file, or an XLS Spreadsheet. You can choose which columns to display.</td>
</tr>


</table><br>



<h2>METADATA</h2>
<table class="ku_grid">

<!-- References -->
<form name="getrefs" action="refoutput.php" >
<tr>
<td valign="middle">
<input type="submit" name="references" value="View All References">
</td>
<td valign="middle">Download a list of references for the samples retrieved by your search.
<?=$formsources?>

<input type="hidden" name="pkey" value="<?=$pkey ?>">
</td>
</tr>
</form>

<!-- Quick Display - an HTML table -->
<form action="quickresults.php" method="post" >
<tr>
<td valign="middle">
<input type="submit" name="quick" value="View All Samples">
</td>
<td valign="middle">Display samples in a quickly rendered HTML table. Output columns are pre-determined to commonly displayed output.
<?=$formsources?>

<input type="hidden" name="url.pkey" value="<?=$pkey ?>">
<input type="hidden" name="pkey" value="<?=$pkey ?>">
</td>
</tr>
</form>
</table><br>











<h2>VISUALIZATION&nbsp;TOOLS</h2>
<table class='ku_grid'>



<?php
include('queries.php'); // We need this only for the plots
if ($mynormmaterial == 1) { // From queries.php. materialtype a/k/a normtype is an integer, 1 = rock or whole rock, etc.  TAS and Harker are offered only for rock/whole rock.
// Eileen changed the tas_sql in queries.php and it contains the source string now. $tas_sql .= $srcwhere; $tas_sql = str_replace(chr(13),' ',$tas_sql); $tas_sql = str_replace(chr(10),' ',$tas_sql); // Strip the carriage returns & line feeds...
// ... and ditto for the harker_sql $harker_sql .= $srcwhere; $harker_sql = str_replace(chr(13),' ',$harker_sql); $harker_sql = str_replace(chr(10),' ',$harker_sql); // ...because they will truncate the string in a hidden input form field 
//was foofoo_sql
?>
<form id='TASForm' name='TASForm' method='post' action='ecplots/tas.php' >
<tr>
<td valign='middle'>
<input type="submit" name="TASButton" value="Plot TAS Diagram">
<input type="hidden" name="tas_sql" value="<?=$tasstring?>"> <!--$tas_sql-->
<input type='hidden' name='referring_website' value='earthchem'>
<input type='hidden' name='pkey' value='<?=$pkey ?>'>
<?=$formsources?>
</td>
<td valign='middle'>Create a Total Alkali vs. Silica diagram.
</td>
</tr>
</form>


<form id='TAShist2dForm' name='TAShist2dForm' method='post' action='hist2dtas.php' >
<tr>
<td valign='middle'>
<input type="submit" name="TAShist2dButton" value="Plot 2D TAS Histogram">
<input type="hidden" name="tas_hist_sql" value="<?=$tas_hist_sql?>"> 
<input type='hidden' name='referring_website' value='earthchem'>
<input type='hidden' name='pkey' value='<?=$pkey ?>'>
<?=$formsources?>
</td>
<td valign='middle'>Create a 2D Histogram of Total Alkali vs. Silica data. This diagram is especially useful when displaying large numbers of samples.
</td>
</tr>
</form>




<?
/*
<!-- TAS Animation -->
<form id='TASAniForm' name='TASAniForm' method='post' action='ecplots/tasAni.php' >
<tr>
<td valign='middle'>
 
<input type="submit" name="TASButton" value="Animate TAS Diagram">
<input type="hidden" name="tas_ani_sql" value="<?=$tas_ani_sql?>"> 
<input type='hidden' name='referring_website' value='earthchem'>
<input type='hidden' name='pkey' value='<?=$pkey ?>'>
</td>
<td valign='middle'>Create a Total Alkali vs. Silica Diagram Animated by Age. 
</td>
</tr>
</form>
*/
?>


<!-- Harker Plots -->
<form id='HarkerForm' name='HarkerForm' method='post' action='ecplots/harker.php' >
<tr>
<td valign="middle">
<input type="submit" name="HarkerButton" value="Plot Harker Diagrams">
<input type="hidden" name="harker_sql" value="<?=$harker_sql?>">
<input type="hidden" name="referring_website" value="earthchem">
<input type="hidden" name="srcwhere" value="<?=$srcwhere?>">
<input type='hidden' name='pkey' value='<?=$pkey ?>'>
</td>
<td valign='middle'>Create one or more Harker diagrams.
</td>
</tr>
</form>




<!-- Harker Plots -->
<form id='hist2dHarkerForm' name='hist2dHarkerForm' method='post' action='hist2dharker.php' >
<tr>
<td valign="middle">
<input type="submit" name="HarkerButton" value="Plot 2D Histogram Harker Diagrams">
<input type="hidden" name="harker_sql" value="<?=$harker_sql?>">
<input type="hidden" name="referring_website" value="earthchem">
<input type="hidden" name="srcwhere" value="<?=$srcwhere?>">
<input type='hidden' name='pkey' value='<?=$pkey ?>'>
</td>
<td valign='middle'>Create 2D Histogram Harker diagrams.
</td>
</tr>
</form>


























<!-- SVG TAS Plots -->
<?
if($_SERVER['REMOTE_ADDR']=="10.237.81.12"){
?>
<form id='svgtasForm' name='svgtasForm' method='post' action='svgtas.php' >
<tr>
<td valign="middle">
<input type="submit" name="svgtasButton" value="Plot SVG TAS Diagrams">
<input type="hidden" name="sql" value="<?=$foofoo_sql?>">
<input type="hidden" name="referring_website" value="earthchem">
<input type="hidden" name="srcwhere" value="<?=$srcwhere?>">
<input type='hidden' name='pkey' value='<?=$pkey ?>'>
</td>
<td valign='middle'>Create an SVG Total Alkali vs. Silica diagram.
</td>
</tr>
</form>
<?
}
?>



<?
/*
<!-- Harker Animation -->
<form id='HarkerAniForm' name='HarkerAniForm' method='post' action='ecplots/harkerAni.php' >
<tr>
<td valign="middle">
<input type="submit" name="HarkerButton" value="Animate Harker Diagrams">
<input type="hidden" name="harker_ani_sql" value="<?=$harker_ani_sql?>">
<input type="hidden" name="referring_website" value="earthchem">
<input type='hidden' name='pkey' value='<?=$pkey ?>'>
</td>
<td valign='middle'>Create Harker Diagrams Animated by Age. 
</td>
</tr>
</form>
*/
?>

<?php
} // if mynormmaterial==1
?>



<!-- XYZ Plot 
<form id='xyz_form' name='xyz_form' method='post' action='ecplots/xyz.php' >
<tr>
<td valign="middle">
<input type="submit" name="xyzButton" value="Plot XYZ Diagram">
<input type="hidden" name="referring_website" value="earthchem">

<?=$formsources?>

<input type="hidden" name="pkey" value="<?=$pkey ?>">
<input type="hidden" name="srcwhere" value="<?=$srcwhere ?>"> 
</td>
<td valign="middle">Make an x,y or x,y,z plot. (in test mode)
</td>
</tr>
</form>
-->



<!-- Dynamic Map -->

<tr>
<td valign='middle'>
<form action='ecinteractivemap.php' method='post' >
<input type="submit" name="dynamicmap" value="View Dynamic Map">
<input type='Hidden' name='pkey' value='<?=$pkey ?>'>

<?=$formsources?>
</form>

<!---
<form action='ecinteractivemap2.php' method='post'>
<input type="submit" name="dynamicmap" value="View Dev Map">
<input type='Hidden' name='pkey' value='<?=$pkey ?>'>
<input type='hidden' name='georoc' value='<?=$_POST['georoc']?>'>
<input type='hidden' name='navdat' value='<?=$_POST['navdat']?>'>
<input type='hidden' name='petdb' value='<?=$_POST['petdb']?>'>
<input type='hidden' name='usgs' value='<?=$_POST['usgs']?>'>
</form>
--->

</td>
<td valign='middle'>Display sample locations on a dynamic map.
<?php
$totalcount=0;

foreach($datasources as $ds){
	if ($_POST[$ds->name]) {
	$totalcount += $_POST[$ds->name.'total'];
	}
}


echo "The result set contains $totalcount samples. ";
?>
</td>
</tr>




<?
if($_SERVER['REMOTE_ADDR']!="1129.236.30.44"){
?>

<!-- Dynamic Polar Map -->

<tr>
<td valign='middle'>
<form action='ecinteractivepolarmap.php' method='post' >
<input type="submit" name="dynamicmap" value="View Dynamic Southern Polar Map">
<input type='Hidden' name='pkey' value='<?=$pkey ?>'>

<?=$formsources?>
</form>

<!---
<form action='ecinteractivemap2.php' method='post'>
<input type="submit" name="dynamicmap" value="View Dev Map">
<input type='Hidden' name='pkey' value='<?=$pkey ?>'>
<input type='hidden' name='georoc' value='<?=$_POST['georoc']?>'>
<input type='hidden' name='navdat' value='<?=$_POST['navdat']?>'>
<input type='hidden' name='petdb' value='<?=$_POST['petdb']?>'>
<input type='hidden' name='usgs' value='<?=$_POST['usgs']?>'>
</form>
--->

</td>
<td valign='middle'>Display sample locations on a dynamic southern polar map.
<?php
$totalcount=0;

foreach($datasources as $ds){
	if ($_POST[$ds->name]) {
	$totalcount += $_POST[$ds->name.'total'];
	}
}


echo "The result set contains $totalcount samples. ";
?>
</td>
</tr>

<?
}
?>







































<?
if($_SERVER['REMOTE_ADDR']=="129.236.10.53"){
?>


<!-- Dynamic Map -->

<tr>
<td valign='middle'>
<form action='ecinteractivemap2.php' method='post' >
<input type="submit" name="dynamicmap" value="Debug Dynamic Map">
<input type='Hidden' name='pkey' value='<?=$pkey ?>'>

<?=$formsources?>
</form>

<!---
<form action='ecinteractivemap2.php' method='post'>
<input type="submit" name="dynamicmap" value="View Dev Map">
<input type='Hidden' name='pkey' value='<?=$pkey ?>'>
<input type='hidden' name='georoc' value='<?=$_POST['georoc']?>'>
<input type='hidden' name='navdat' value='<?=$_POST['navdat']?>'>
<input type='hidden' name='petdb' value='<?=$_POST['petdb']?>'>
<input type='hidden' name='usgs' value='<?=$_POST['usgs']?>'>
</form>
--->

</td>
<td valign='middle'>Display sample locations on a dynamic map.
<?php
$totalcount=0;

foreach($datasources as $ds){
	if ($_POST[$ds->name]) {
	$totalcount += $_POST[$ds->name.'total'];
	}
}


echo "The result set contains $totalcount samples. ";
?>
</td>
</tr>


<?
}
?>
























<!-- Static Map -->
<form action='showstaticmap.php' method='post' >
<tr>
<td valign='middle'>
<input type="submit" name="simplemap" value="View Static Map">
<input type='Hidden' name='pkey' value='<?=$pkey ?>'>
<?=$formsources?>
</td>
<td valign='middle'>Display sample locations on a static map suitable for downloading.
</td>
</tr>
</form>






<!-- Static Map -->
<form action='showstaticpolarmap.php' method='post' >
<tr>
<td valign='middle'>
<input type="submit" name="simplemap" value="View Static Southern Polar Map">
<input type='Hidden' name='pkey' value='<?=$pkey ?>'>
<?=$formsources?>
</td>
<td valign='middle'>Display sample locations on a static southern polar map suitable for downloading.
</td>
</tr>
</form>


<!-- Google Earth KML File 
<form action='downloadkml.php' method='post' >
<tr>
<td valign='middle'>
<input type="submit" name="googleearch" value="Google Earth">
<input type='Hidden' name='pkey' value='<?=$pkey ?>'>
<?=$formsources?>
</td>
<td valign='middle'>Download GoogleEarth KML file containing samples from this query.
</td>
</tr>
</form>
-->






</table>

<div id="debug" style="display:none;">
<table>
<form action='advancedoptionsdebug.php' method='post'  >
<tr>
<td valign='middle'>
<input type="submit" name="advanced" value="Get Chemical Data">
</td>
<td valign='middle'>Display samples in an HTML table, a Text file, or an XLS Spreadsheet. You can choose which columns to display.
<?=$formsources?>
<input type='hidden' name='url.pkey' value='<?=$pkey ?>'>
<input type='hidden' name='pkey' value='<?=$pkey ?>'>
<input type="hidden" name="srcwhere" value="<?=$srcwhere?>">
</td>
</tr>
</form>
</table>

<pre><?=$tasstring?></pre>

</div>

<?php

/*
echo "<p>For dev & test: ";
echo "<p>srcwhere <br>$srcwhere";
echo "<p>harker_sql<br>$harker_sql";
echo "<p>harker_ani_sql<br>$harker_ani_sql";
echo "<p>tas_sql<br>$tas_sql";
echo "<p>tas_ani_sql<br>$tas_ani_sql";
echo "<p>simplestuff<br>$simplestuff";
echo "<p>countstring<br>$countstring<p>";
echo "<p>advcount<br>$advcount";
echo "<p>simplecount<br>$simplecount";
echo "<p>samplechemcount <br>$samplechemcount "; 
echo "<p>simpstring <br>$simpstring"; 
echo "<p>itemstring <br>$itemstring";
echo "<p>advstring <br>$advstring ";
echo "<p>mapstring <br>$mapstring ";
echo "<p>legendmapstring <br>$legendmapstring ";
*/

include ('includes/ks_footer.html');





?>