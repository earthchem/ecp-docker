<?PHP
/**
 * ecneighborwrapper.php
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

include("db.php");

$material_pkey=$_GET['material_pkey'];

//print_r($_GET);exit();

//echo "$sample_pkey";exit();

$myquery="SELECT *
		from vmatreported
		--where material_pkey = $material_pkey
		where 1=1 
";

//AND ( ( K2O > 0 AND K2O < 5 ) OR ( MGO > 0 AND MGO < 5 ) OR ( MNO > 0 AND MNO < 5 ) OR ( NA2O > 0 AND NA2O < 5 ) OR ( P2O5 > 0 AND P2O5 < 5 ) OR ( SIO2 > 0 AND SIO2 < 5 ) OR ( TIO2 > 0 AND TIO2 < 5 ) OR ( AL2O3 > 0 AND AL2O3 < 5 ) OR ( CAO > 0 AND CAO < 5 ) ) AND ( chem.SIO2 > -99 OR chem.TIO2 > -99 OR chem.AL2O3 > -99 OR chem.FE2O3 > -99 ) 

//AND mat.normtype in (1,4) limit 50 offset 51-1

$items=array('sio2','al2o3','cao','mgo','na2o','k2o','p2o5','mno','tio2','feo');

$showlist_array=$items;

foreach($items as $item){
	if($_GET[$item."_min"] != "" and $_GET[$item."_max"] != ""){
		if($item=="feo"){
			$useitem="n_feo_feot";
		}else{
			$useitem="n_feo_$item";
		}
		$chemstring.="AND $useitem > ".$_GET[$item."_min"]." AND $useitem < ".$_GET[$item."_max"]." ";
		$urlstring.="&".$item."_min=".$_GET[$item."_min"]."&".$item."_max=".$_GET[$item."_max"];
		$showstring.=$_GET[$item."_min"]." < $item < ".$_GET[$item."_max"]."<br>";
		$itemsset="yes";
	}
}

if($itemsset!="yes"){
	echo "no chemical values set";
	exit();
}

//echo "chemstring = $chemstring";



$myquery.=$chemstring;


//echo nl2br($myquery);exit();


$getsample=$db->get_results($myquery);


$count=$db->num_rows;

//print_r($db);exit();








if($showstring!="" && $count>0){
?>

	<div style="padding-left:10px; padding-top:10px;">
		<div style="font-size:1.2em; color:#434343">
			<div style="font-weight:bold; padding-top:10px; padding-bottom:10px;">
				<?=$count?> Samples Found.
			</div>
		</div>
	</div>
<?
}//end if showstring

if($count==0){
	echo "No results found.";
	exit();
}



?>
<?=$pagelinks?>
<table class="ku_htmloutput">
<tr valign=top VALIGN="bottom">
<th nowrap>
<h4>
<?php
echo 'SAMPLE&nbsp;ID';
?>
</h4>
</th>
<th nowrap>
<h4>
<?php
echo 'SOURCE';
?>
</h4>
</th>
<th nowrap>
<h4> DETAIL </h4>
</th>
<th nowrap>
<h4> MAP </h4>
</th>
<th nowrap>
<h4>
<?php
echo 'LATITUDE';
?>
</h4>
</th>
<th nowrap>
<h4>
<?php
echo 'LONGITUDE';
?>
</h4>
</th>
<?PHP
foreach($showlist_array as $field_name) { 
if (trim($field_name) == 'ARSENIC') { 
$showfield='AS'; 
} else {
$showfield=$field_name; 
}
?>
<th>
<h4>
<?php
echo $showfield;
?>
</h4>
</th>
<?PHP
if ($showunits) { 
?>
<th nowrap>
<h4>
<?php
echo $showfield;
?>
</h4>
</th>
<?PHP 
} // if $showunits				
if ($showmethods) {
?>
<th nowrap>
<h4>
<?php
echo $showfield.' METHOD';
?>
</h4>
</th>
<?PHP 
} // if $showmethods
} // foreach $showlist_array
?>
</tr>
<?PHP
$row=1;
foreach($getsample AS $g) { 
	if ($row == 1) {
		$bgcolor="#FFFFFF";
		$row=2;
	} else {
		$bgcolor="#FFF5F5";
		$row=1;
	}
	if ($g->url > '') {
		$urlstring = "<A HREF='".$g->url."' target='_blank'>DETAILS</A>";
	} else {
		$urlstring=' ';
	}
	echo "
	<tr valign='top' style=\"background-color:$bgcolor;\">
	<TD >$g->sample_id </td>
	<TD>".strtoupper($g->source)." </TD>
	<TD >$urlstring
	</TD>
	<TD >
	<a data-fancybox data-type=\"iframe\" data-src=\"ecindividualmap.php?pkey=".$g->sample_pkey."\" href=\"javascript:;\">MAP</a>
	</TD>
	<TD >$g->latitude
	</TD>
	<TD >$g->longitude
	</TD>
	";
	foreach($showlist_array as $currentfield) { 
		if($currentfield=="feo"){
			$showitem="n_feo_feot";
		}else{
			$showitem="n_feo_$currentfield";
		}
		$currentfield=strtolower($showitem);
		echo "<TD >"; echo $g->$showitem; 
		echo "</TD>";
	} // foreach showlist_array
	echo "</TR>";
} // foreach get_sample
echo "</TABLE>";











/*
$items=array('sio2','al2o3','cao','mgo','na2o','k2o','p2o5','mno','tio2','feo');

foreach($items as $item){
	if($_GET["$item"._min]!="" && $_GET["$item"._max]!=""){
		$string.=$delim."$item"."_min=".$_GET["$item"."_min"]."&"."$item"."_max=".$_GET["$item"."_max"];
		$delim="&";
	}
}

echo rand(111111,999999);
*/
?>