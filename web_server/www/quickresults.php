<?PHP
/**
 * quickresults.php
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

include('includes/ks_head.html');

include('get_pkey.php'); 


if (isset($_GET['totalcount'])) {
	$totalcount=$_GET['totalcount']; 
} elseif (isset($_POST['totalcount'])) {
	$totalcount=$_POST['totalcount'];
} elseif (isset($_GET['count'])) {
	$totalcount=$_GET['count']; 
} elseif (isset($_POST['count'])) {
	$totalcount=$_POST['count'];
} 

if (isset($_GET['numresults'])) {
	$numtoshow=$_GET['numresults']; 
} elseif (isset($_POST['numresults'])) {
	$numtoshow=$_POST['numresults'];
} else {
	$numtoshow=50;
}

if (isset($_GET['currentpage'])) {
	$currentpage=$_GET['currentpage'];
} elseif (isset($_POST['currentpage'])) {
	$currentpage=$_POST['currentpage'];
} else {
	$currentpage=1;
}

$startrow=($currentpage-1)*$numtoshow;
$endrow=$startrow+$numtoshow;
//debug: echo "<p>page $page currentpage $currentpage startrow $startrow endrow $endrow numtoshow $numtoshow";

include("db.php");

include('datasources.php');

include('srcwhere.php');

include('queries.php');

$dslink="";
foreach($datasources as $ds){
	eval("\$thisval=\$".$ds->name.";");
	$dslink.="&".$ds->name."=$thisval";
}

//echo $dslink;
//exit();

$searchquery = $db->get_row('SELECT * FROM search_query WHERE pkey = '.$pkey);
// Eileen do we need the javascript function showwhere() ? 
?>
<SCRIPT type="text/javascript">function showwhere()
{
var obj_batch = document.getElementById('wherestate');
if (obj_batch.style.display == "none")
obj_batch.style.display = "block";
else
obj_batch.style.display = "none";
}
</SCRIPT>
<?php 

//echo nl2br('SELECT count(*) as count FROM ( '.$simpstring.' ) foo ');
//exit();

if (!isset($totalcount) || !is_numeric($totalcount)) {
	$getcount=$db->get_row('SELECT count(*) as count FROM ( '.$newsimpstring.' ) foo ');
	$totalcount=$getcount->count;
}

// Compute the number of pages we will need to show the results, based on the number of samples in the result set, and the number of samples to show per page
$numpages=$totalcount/$numtoshow; 
if (floor($numpages) < $numpages) {
	$numpages=floor($numpages)+1; // round up 
} else {
	$numpages = floor($numpages);
}

$offset=($currentpage-1)*$numtoshow;

/* not sure what this is doing...
//debug: echo " <p>currentpage $currentpage numpages $numpages ";
$sql = "SELECT * FROM ( SELECT a.*, rownum rnum FROM ( $simpstring ) a WHERE rownum ";
if ($currentpage < $numpages) { $sql .= " < "; } else { $sql .= " <= "; }
$sql .= " $endrow) where rnum >= $startrow ";
echo "<p><font color=blue>getsample query uses simpstring<br>$sql </font>";
*/

$sql="$newsimpstring limit $numtoshow offset $offset";



//echo nl2br($sql);

//echo "<div>$sql</div>";

$getsample=$db->get_results($sql);

if (count($getsample) > 0) { // If the search query returned any samples, display them here. 
?>
<TABLE style="width:100%" class="ku_htmloutput">
<tr>
<td valign="top"colspan=8 style="padding:0px;border:none"><a name="topoftable"></a><H1>Sample Data Output</H1>
<?php
$endrow--;
if($totalcount<$endrow){$endrow=$totalcount;}
$currshow=$startrow+1;
echo "$totalcount samples<br>displaying $currshow through $endrow ";
if($numpages>1){echo "(page $currentpage)";}
?>
</td>
<td valign="top"colspan=3 style="padding:0px;border:none" align="right"><INPUT TYPE="button" class="submitbutton" value="New Search" onClick="parent.location='./'">
</td>
</tr>
<tr>
<td valign="top"colspan="11" align="right" style="border:none">
<?php

// Show all the pages, unless there are many many of them, in which case show the current page plus a few before and after. All pages except the current page are links. 
$pagelinks = ''; // Store the text for the links to other pages in a variable, so we can display it twice on this page. 
for ($p=1;$p<=$numpages;$p++) {
	if ($p==$currentpage) {
		$pagelinks .= " $p ";
	} elseif ( 
		( $p>=($currentpage-3) && $p<=($currentpage+3) ) ||
		( $p == 1 ) ||
		( $numpages < 20 ) 
		) { // Show links for page 1, and links for about three pages on either side of the current page, and each of the first 20 pages 
		// Try using a form so we do not have to pass so many url variables      echo " <a href=''>$p</a> ";
$pagelinks .= "<a href='quickresults.php?pkey=$pkey$dslink&totalcount=$totalcount&totalcount=$totalcount&currentpage=$p'> $p </a>";
	} else {
		//$pagelinks .= '.';
	}
}
$pagelinks .= " of $numpages ";
// add PREV page and NEXT page links as appropriate
if ($currentpage>1) {
	$p=$currentpage-1;
	$pagelinks = "<a href='quickresults.php?pkey=$pkey$dslink&totalcount=$totalcount&totalcount=$totalcount&currentpage=$p'>&lt;&lt;PREV &nbsp; </a>" . $pagelinks;
}
if ($currentpage<$numpages) {
	$p=$currentpage+1;
	$pagelinks .= "<a href='quickresults.php?pkey=$pkey$dslink&totalcount=$totalcount&totalcount=$totalcount&currentpage=$p'> &nbsp; NEXT&gt;&gt;</a>";
}
$pagelinks='<div style="margin:5px">'.$pagelinks.'</div>';
echo $pagelinks;

?>
</td>
</tr>
<tr>
<th><h4>SAMPLE&nbsp;ID</h4></th>
<th><h4>IGSN</h4></th>
<th><h4>SOURCE</h4></th>
<th><h4>DETAILS</h4></th>
<th><h4>MAP</h4></th>
<th><h4>LATITUDE</h4></th>
<th><h4>LONGITUDE</h4></th>
<th><h4>CRUISE&nbsp;ID</h4></th>
<th><h4>SAMPLE&nbsp;TYPE</h4></th>
<th><h4>ROCK&nbsp;CLASS</h4></th>
<?
//<th>ROCK&nbsp;NAME</th>
?>
</tr>

<?php
$row=1;
foreach($getsample as $g) {
	if ($row == 1) { // alternate background colors for the rows, for readability
		$bgcolor='white';
		$row=2;
	} else {
		$bgcolor='#FFF5F5'; //FDE6C2 F9E3E3
		$row=1;
	}
	if ($g->url>'') {

		$urlstring = '<a href="'.$g->url.'" target="_blank">DETAILS</a>'; 

		//$urlstring="<a href='".$g->url."' target='_blank'>DETAILS</a>";
	} else { 
		$urlstring='&nbsp;';
	}

	/*
	//make accomodations for petdb mess here
	if($g->source == 'petdbkk'){
		$urlstring='<a href="http://isotope.ldeo.columbia.edu:7001/petdbWeb/search/sample_info.jsp?singlenum='.$g->samplenumber.'" target="_blank">DETAILS</a>';
	}elseif($g->source == 'navdat'){
		$urlstring = '<a href="http://www.navdat.org/NavdatPortal/GetSample.cfm?sample_num='.$g->sample_num.'" target="_blank">DETAILS</a>'; 
	}else{
		$urlstring = '<a href="'.$g->url.'" target="_blank">DETAILS</a>'; 
	}
	*/

?>
<tr valign="top" bgcolor="<?=$bgcolor?>">
<td valign="top" style="white-space:nowrap"><!--ROW: <?=$g->RNUM ?>&nbsp;--><?=$g->sample_id ?>
</td>

<td valign="top" style="white-space:nowrap"><?=$g->igsn ?>
</td>

<td valign="top">
<?php

echo strtoupper($g->source);

?>
</td>
<td valign="top">
<?=$urlstring ?>
</td>
<td valign="top">
<!--<a href="ecindividualmap.php?pkey=<?=$g->sample_pkey?>" data-fancybox data-caption="EarthChem Sample Map">MAP</a>-->
<a data-fancybox data-type="iframe" data-src="ecindividualmap.php?pkey=<?=$g->sample_pkey?>" href="javascript:;">MAP</a>
</td>

<td valign="top"><?=$g->latitude ?>
</td>
<td valign="top"><?=$g->longitude ?>
</td>
		 <td nowrap>
		 <?
		 if($g->expeditionurl!=""){
		 ?>
		 <a href="<?=$g->expeditionurl?>" target="_blank"><?=$g->expeditionid?></a>
		 <?
		 }else{
		 ?>
		 <?=$g->expeditionid ?>
		 <?
		 }
		 ?>
		 </td>
<td valign="top"><?=strtoupper($g->materialtype) ?>
</td>
<td valign="top" nowrap>
<?
	unset($classes);
	if($g->class1!="") $classes[]=strtoupper($g->class1);
	if($g->class2!="") $classes[]=strtoupper($g->class2);
	if($g->class3!="") $classes[]=strtoupper($g->class3);
	if($g->class4!="") $classes[]=strtoupper($g->class4);
	if(count($classes)>0){
		$classes = implode(", ",$classes);
	}else{
		$classes = "&nbsp;";
	}
	
	echo $classes;
?>
</td>
<?
/*
<td valign="top" nowrap><?=$g->rockname ?></td>
*/
?>
</tr>
<?php
//</CFLOOP>
} // end looping through samples in search result set 
?>
<tr>
<td valign="top"colspan="11" align="right" style="border:none">
<?php
if (count($getsample)>15) { // If there are many samples on this page, repeat the links and add a to-top-of-page link, here
	echo $pagelinks;
	echo '<div style="margin:5px"><a href="#topoftable">top</a></div>'; 
}
?>
</td>
</tr>
</TABLE>
<?php
} else { // The search did not return any samples 
?>
<H2>Sorry, No Records Found</H2>
<?php
} // if, else 
?>
<div id="debug" style="display:none;"><?=nl2br($newsimpstring)?></div>
<?
echo '<div style="height:100"></div>'; // vertical separation from footer because width of footer looks awkward with wider table 
include ('includes/ks_footer.html');
?>
