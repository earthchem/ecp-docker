<?PHP
/**
 * search.php
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

session_start();

function dumpVar($var){
	echo "<pre>";
	print_r($var);
	echo "</pre>";
}

#echo ini_get('include_path');exit();

//echo getcwd();exit();

//echo E_ERROR ^ E_PARSE;exit();

include("geopasstest.php");

include('get_pkey.php');

#include('/local/public/mgg/web/ecp.iedadata.org/htdocs/includes/ks_head.html'); exit();

include('includes/ks_head.html');



//echo "<h2>dev site here.</h2><br>";



//echo "pkey: $pkey<br>";

/*
foreach($_POST as $key=>$value){
	echo "$key - $value<Br>";
}
*/

 

/*
if (isset($_SESSION['users_pkey'])) { // The user is logged in - so also show the glossary header
	include('includes/glossary_header.php'); 
}
*/
echo '<h1>EarthChem Portal Search</h1>';
//glossary stuff here
?>
<!-- <DIV style="float:right;margin:10 0 ;clear:left;padding:5px;border:1px solid #996600"> -->
<?php


//if (isset($user)) {
//echo 'Logged in as '.$username.'&nbsp;|&nbsp;<A href="logout.php?search=yes&searchpkey='.$pkey.'">Log out</A>&nbsp;';
//} else {
//echo 'You are not logged in. <A href="login.php?search=yes&searchpkey='.$pkey.'">Log in</A>&nbsp;&nbsp;|&nbsp;<A href="http://geopass.iedadata.org/josso/register.jsp">Register</A>';
//} // if


?>
<!-- </DIV> --><br clear="left" />
<?


include('db.php'); // database drivers, and connect to the database 

//include('datasources.php');

//dumpVar($datasources);exit();


//var_dump($_POST);

$error = false;
$show_results = false;
$constraints_set = false;

//include 'searchupdate.php'; // update the record in the search_query table 
include 'searchaction.php'; // execute the search 


$searchquery=$db->get_row('SELECT * from search_query where pkey = '.$pkey); // or die ('could not select record from search_query table'); 
// If yearmin and yearmax are 0, set them both to null
if ($searchquery->YEARMIN==0 and $searchquery->YEARMAX==0) {$searchquery->YEARMIN='';$searchquery->YEARMAX='';}



if ($searchquery->earthchemwhere > '' or $searchquery->polygon > '' or $searchquery->polarpolygon > '') {

	//dumpVar($datasources);exit();
	
	require ('queries.php'); // This used to be querytest.php
	
	//dumpVar($datasources);exit();
	
	//echo "$itemstring";
	
	//echo nl2br($countstring);

	/*
	?>
	<div>countstring:<?=$countstring?></div>
	<?
	*/
	
	$getcount=$db->get_row($countstring) or die('could not execute countstring query in search.php');
	$totalcount=$getcount->tc;
	
	if ($totalcount == '') {
		$totalcount=0;
	}
	
	foreach($datasources as $ds){
		eval("\$".$ds->name."total=\$getcount->".$ds->dbvar.";");
		eval("if(\$".$ds->name."total==''){\$".$ds->name."total=0;}");
	}



} else { // if earthchemwhere != '' or...
	$totalcount=0;
} // if else 
	

//Determine whether this is an ordinary search, or a Glossary Entry (a saved search with some additional data in a separate table). 
$glossary_entry = false; // Is this a Glossary Entry rather than a regular search? 
$author = false; // Is this a Glossary Entry being viewed by its author?
$admin = false; // Is this a Glossary Entry being viewed by an admin user?
$published = false; // Is this a Glossay Entry thas has been published? 
if ($glossary_pkey>'') { 
	$glossary = $db->get_row("SELECT * FROM glossary WHERE pkey = $glossary_pkey");	
	if (count($glossary)>0) {
		$glossary_entry=true;
		// Determine whether the user is admin 
		if ($user_level=='admin') {
			$admin=true;
		}
		if ($glossary->published==1) {
			$published=true;
		}else{
			$published=false;
		}
		
		// Determine what type of user is viewing the page.
		if (is_numeric($glossary->users_pkey) and isset($userpkey) and $glossary->users_pkey == $userpkey) { 
			$author=true; // The logged-in user is the glossary entry's author, and can edit or delete (until the entry is published) 
			echo "<br>You are the author of this Glossary Entry. "; 	
			if ($published) {
				echo '<br>It has been published, therefore it is no longer editable. If you wish to modify the entry, please email Doug Walker at <A href="jdwalker@ku.edu">jdwalker@ku.edu</A> to request the ability to edit the entry.<br>';
			}
		}
		if ($admin) {
			//The logged-in user is admin or poweruser and can edit or delete, and publish, the entry - even after the entry is published 
			echo '<br>As '.$_SESSION['user_level'].' you may edit, delete, publish or unpublish any Glossary Entry. All edits are automatically saved.<br>';
		} elseif ($author and !$published) {
			// The logged-in user is the author of this entry
			echo '<br>As the author of this Glossary Entry, you may edit or delete it until it has been published.<br>';
		}
	}
	if ($glossary_entry and ($admin or ($author and !$published) ) ) { // delete button - available to admin whether or not published, and to author if it hasn't been published
		echo '<INPUT TYPE="button" value="Delete" onClick="location=\'glossary_delete.php?pkey='.$pkey.'&glossary_pkey='.$glossary_pkey.'\'"> ';
	}
	if ($glossary_entry and $admin and !$published) { // publish button - show to admin if it has not been published already
		echo '<INPUT TYPE="button" value="Publish" onClick="location=\'glossary_publish.php?pkey='.$pkey.'&glossary_pkey='.$glossary_pkey.'\'">';
	}
	if ($glossary_entry and $admin and $published) { // unpublish button - show to admin if it has been published
		echo '<INPUT TYPE="button" value="Unpublish" onClick="location=\'glossary_unpublish.php?pkey='.$pkey.'&glossary_pkey='.$glossary_pkey.'\'">';
	}
}
//test: echo "<p>admin $admin published $published author $author glossary_entry $glossary_entry ";

// Show different headings for a glossary entry or ordinary search 
if ($glossary_entry) {
	echo "<H2>";
	echo "Glossary Entry</h2>
A Glossary Entry is a saved search that can be viewed by anyone.";

		if(!isset($user)){

			echo "You must be <a href='login.php'>logged&nbsp;in</a> user to add a Glossary Entry.<br>";

		}
		

} else {
	echo '<br>
<H2>Create&nbsp;a&nbsp;Search&nbsp;Query</H2>';
}

// If this is a glossary entry that cannot be edited (because it is being viewed by someone other than admin or its author, or by the author but it has been published), set a flag to false 
if ($glossary_entry and !($admin or ($author and !$published) ) ) { // This is a glossary entry, and it's being viewed by a user who is not either admin, or the author if it is unpublished, it is not editable
	$colspan=2;
	$editable=false;
} else { // This is an ordinary search, or a glossary entry that can be edited by this user 
	$colspan=3;
	$editable=true;
}


?>
<table border="0">
<tr><td style="padding-right:20px;">
<?


echo ' 
<FORM name="gotoresults" action="results.php" method="post">

<table class="searchpage" border=0 width="300px">';

// GLOSSARY NAME, DESCRIPTION
if ($glossary_entry) {
echo '<TR>';
if ($editable) { echo '
<TD style="white-space:nowrap">
<INPUT TYPE="button" value="Set" onClick="location=\'setdescription.php?pkey='.$pkey.'&glossary_pkey='.$glossary_pkey.'\'">
</TD>';
}
echo '
<TD><div class="searchlabel">GLOSSARY NAME</div>
</TD>
<TD >'.$glossary->glossary_name.'
</TD>
</TR>
<TR>';
if ($editable) { echo '
<TD style="white-space:nowrap">
<INPUT TYPE="button" value="Set" onClick="location=\'setdescription.php?pkey='.$pkey.'&glossary_pkey='.$glossary_pkey.'\'">
</TD>';
}
echo '<TD><div class="searchlabel">DESCRIPTION</div></TD><TD>'.$glossary->description.'</TD></TR>';
} // end GLOSSARY NAME, DESCRIPTION


if($searchquery->queryname!="Untitled" && $searchquery->queryname!=""){
?>
<TR>
	<?
	if ($editable) {
	?>
	<TD></TD>
	<?
	}
	?>
	<TD><div class="searchlabel">QUERY NAME</div></TD>
	<TD><?=$searchquery->queryname?></TD>
</TR>
<?
}

// REFERENCE
echo '<TR>';
if ($editable) {
	?>
	<TD style="white-space:nowrap"><INPUT TYPE="button" value="Set" onClick="location='setreference.php?pkey=<?=$pkey?>&glossary_pkey=<?=$glossary_pkey?>'">
	<?
	if ($searchquery->doi != '' or $searchquery->author != '' or $searchquery->title != '' or $searchquery->journal != '' or $searchquery->yearmin != '' or $searchquery->yearmax != '' or $searchquery->advkeyword100 != '' ) {
		?>
		<INPUT TYPE="button" value="Clear" onClick="location='clearreference.php?pkey=<?=$pkey?>&glossary_pkey=<?=$glossary_pkey?>'">
		<?
	} // if			
	echo '</TD>';
} // if
echo '<TD><div class="searchlabel">REFERENCE</div><div style="color:#333333;">e.g. Author, Title, Year</div></TD><TD>';
$constraints_set = false;
if ($searchquery->author > '') {
echo 'AUTHOR&nbsp;=&nbsp;'.$searchquery->author.'<BR/>';
$constraints_set = true;
}
if ($searchquery->doi > '') {
echo 'DOI&nbsp;=&nbsp;'.$searchquery->doi.'<BR/>';
$constraints_set = true;
}
if ($searchquery->title != '') {
echo 'TITLE&nbsp;=&nbsp;'.$searchquery->title.'<BR/>';
$constraints_set = true;
}
if ($searchquery->journal != '') {
echo 'JOURNAL&nbsp;=&nbsp;'.$searchquery->journal.'<BR/>';
$constraints_set = true;
}
if ($searchquery->yearmin != '') {
echo 'MIN YEAR&nbsp;=&nbsp;'.$searchquery->yearmin.'<BR/>';
$constraints_set = true;
}
if ($searchquery->yearmax != '') {
echo 'MAX YEAR&nbsp;=&nbsp;'.$searchquery->yearmax.'<BR/>';
$constraints_set = true;
}
/*
if ($searchquery->advkeyword1 > '') {
echo 'KEYWORD&nbsp;=&nbsp;'.$searchquery->advkeyword1.'<BR/>';
$constraints_set = true;
}
*/
if ($constraints_set) {$show_results=true;} else {echo " NO&nbsp;CONSTRAINTS&nbsp;SET ";}
echo '</TD></TR>';
// end REFERENCE






















// KEYWORD
echo '<TR>';
if ($editable) {
	?>
	<TD style="white-space:nowrap"><INPUT TYPE="button" value="Set" onClick="location='setkeyword.php?pkey=<?=$pkey?>&glossary_pkey=<?=$glossary_pkey?>'">
	<?
	if ($searchquery->advkeyword1 != '' ) {
		?>
		<INPUT TYPE="button" value="Clear" onClick="location='clearkeyword.php?pkey=<?=$pkey?>&glossary_pkey=<?=$glossary_pkey?>'">
		<?
	} // if			
	echo '</TD>';
} // if
echo '<TD><div class="searchlabel">KEYWORD</div><div style="color:#333333;">e.g. Sr, Seamount</div></TD><TD>';
$constraints_set = false;
if ($searchquery->advkeyword1 > '') {
echo ''.$searchquery->advkeyword1.'<BR/>';
$constraints_set = true;
}
if ($constraints_set) {$show_results=true;} else {echo " NO&nbsp;CONSTRAINTS&nbsp;SET ";}
echo '</TD></TR>';
// end KEYWORD







// SAMPLE ID
echo '<TR>';
if ($editable) {
	?>
	<TD style="white-space:nowrap"><INPUT TYPE="button" value="Set" onClick="location='setsampleid.php?pkey=<?=$pkey?>&glossary_pkey=<?=$glossary_pkey?>'">
	<?
	if ($searchquery->sampleid != '' ) {
		?>
		<INPUT TYPE="button" value="Clear" onClick="location='clearsampleid.php?pkey=<?=$pkey?>&glossary_pkey=<?=$glossary_pkey?>'">
		<?
	} // if			
	echo '</TD>';
} // if
echo '<TD><div class="searchlabel">SAMPLE ID</div><div style="color:#333333;">e.g. A-1, 11-10-08A</div></TD><TD>';
$constraints_set = false;
if ($searchquery->sampleid > '') {
echo ''.$searchquery->sampleid.'<BR/>';
$constraints_set = true;
}
if ($constraints_set) {$show_results=true;} else {echo " NO&nbsp;CONSTRAINTS&nbsp;SET ";}
echo '</TD></TR>';
// end SAMPLE ID



// IGSN
echo '<TR>';
if ($editable) {
	?>
	<TD style="white-space:nowrap"><INPUT TYPE="button" value="Set" onClick="location='setigsn.php?pkey=<?=$pkey?>&glossary_pkey=<?=$glossary_pkey?>'">
	<?
	if ($searchquery->igsn != '' ) {
		?>
		<INPUT TYPE="button" value="Clear" onClick="location='clearigsn.php?pkey=<?=$pkey?>&glossary_pkey=<?=$glossary_pkey?>'">
		<?
	} // if			
	echo '</TD>';
} // if
echo '<TD><div class="searchlabel">IGSN</div><div style="color:#333333;">e.g. ODP02OBQF, RUD00000M</div></TD><TD>';
$constraints_set = false;
if ($searchquery->igsn > '') {
echo ''.$searchquery->igsn.'<BR/>';
$constraints_set = true;
}
if ($constraints_set) {$show_results=true;} else {echo " NO&nbsp;CONSTRAINTS&nbsp;SET ";}
echo '</TD></TR>';
// end IGSN



if ($searchquery->cruiseid > '') {
	$cruiselist=str_replace(",",", ",$searchquery->cruiseid);
}



// CRUISE ID
echo '<TR>';
if ($editable) {
	?>
	<TD style="white-space:nowrap"><INPUT TYPE="button" value="Set" onClick="location='setcruiseid.php?pkey=<?=$pkey?>&glossary_pkey=<?=$glossary_pkey?>'">
	<?
	if ($searchquery->cruiseid != '' ) {
		?>
		<INPUT TYPE="button" value="Clear" onClick="location='clearcruiseid.php?pkey=<?=$pkey?>&glossary_pkey=<?=$glossary_pkey?>'">
		<?
	} // if			
	echo '</TD>';
} // if
echo '<TD><div class="searchlabel">CRUISE ID</div><div style="color:#333333;">e.g. AKU1975, CON2802</div></TD><TD>';
$constraints_set = false;
if ($searchquery->cruiseid > '') {
echo ''.$cruiselist.'<BR/>';
$constraints_set = true;
}
if ($constraints_set) {$show_results=true;} else {echo " NO&nbsp;CONSTRAINTS&nbsp;SET ";}
echo '</TD></TR>';
// end CRUISE ID
































// LOCATION
$constraints_set=false;
if ( $searchquery->polygon > '' or $searchquery->polarpolygon > '' or
(is_numeric($searchquery->latitudenorth) and is_numeric($searchquery->latitudesouth) and is_numeric($searchquery->longitudewest) and is_numeric($searchquery->longitudeeast) and $searchquery->latitudenorth+$searchquery->latitudesouth+$searchquery->longitudewest+$searchquery->longitudeeast != 0)  ) {
	$constraints_set=true;
}
echo '<TR>';
if ($editable) {
	echo '<TD style="white-space:nowrap">';
	?>
	<INPUT TYPE="button" value="Set" onClick="location='setlocation.php?pkey=<?=$pkey?>&glossary_pkey=<?=$glossary_pkey?>'">
	<?
	if ($constraints_set) {
		?>
		<INPUT TYPE="button" value="Clear" onClick="location='clearlocation.php?pkey=<?=$pkey?>&glossary_pkey=<?=$glossary_pkey?>'">
		<?
	}
	echo "</td>";
}
echo '<TD><div class="searchlabel">LOCATION</div><div style="color:#333333;">Bounding Box or Polygon</div></TD><td>';
if ($searchquery->polygon>'') {
	$show_results=true;
	//$polygon=$searchquery->polygon; 
	//$latlonginfo = $polygon;
	//$polygon=str_replace(';',';  ',$polygon);
	//echo $polygon;
	echo "<img src=\"searchpagepoly.php?pkey=$pkey\">";
}elseif ($searchquery->polarpolygon>'') {
	$show_results=true;
	//$polygon=$searchquery->polygon; 
	//$latlonginfo = $polygon;
	//$polygon=str_replace(';',';  ',$polygon);
	//echo $polygon;
	echo "<img src=\"searchpagepolarpoly.php?pkey=$pkey\">";

} elseif ( 
		($searchquery->latitudenorth == '' or $searchquery->latitudesouth == '' or $searchquery->longitudewest == '' or $searchquery->longitudeeast == '') and
		($searchquery->latitudenorth >'' or $searchquery->latitudesouth >'' or $searchquery->longitudewest >'' or $searchquery->longitudeeast >'')
		) { // If the user set one value but not all four, error msg
	echo 'Please enter values for all latitude and longitude fields.<br><br>'; 
	$error=true;
} elseif ( 
	is_numeric($searchquery->latitudenorth) and is_numeric($searchquery->latitudesouth) and is_numeric($searchquery->longitudewest) and is_numeric($searchquery->longitudeeast) and $searchquery->latitudenorth+$searchquery->latitudesouth+$searchquery->longitudewest+$searchquery->longitudeeast != 0) 
	{
	$show_results=true;
	echo "<span style=white-space:nowrap'>
	NORTHERN BOUND = $searchquery->latitudenorth<br>
	SOUTHERN BOUND = $searchquery->latitudesouth<br>
	WESTERN BOUND = $searchquery->longitudewest<br>
	EASTERN BOUND = $searchquery->longitudeeast<br>
	</span>";
	$latlonginfo="$searchquery->longitudewest;$searchquery->latitudenorth;$searchquery->longitudeeast;$searchquery->latitudenorth;$searchquery->longitudeeast;$searchquery->latitudesouth;$searchquery->longitudewest;$searchquery->latitudesouth" ; // delimit with ; because that is used in the 'Define a polygon by inputting pairs of coordinates' set location option 
} else {
	echo 'NO&nbsp;CONSTRAINTS&nbsp;SET';
	$latlonginfo='';
}


$latlonginfo=trim($latlonginfo);
if ($latlonginfo>'') {
	$arcserver='hagrid';
	//<CFSET location_pkey = getlocations.pkey>
	$minlong=999;$maxlong=-999;$minlat=999;$maxlat=-999;
	$latlonginfo_array=split(';',$latlonginfo);
	foreach($latlonginfo_array as $currentpair) {
		$currentpair = trim($currentpair);
		$myarray=split(' ',$currentpair);
		if ($myarray[0] < $minlong) {
			$minlong=$myarray[0];
		}
		if ($myarray[0] > $maxlong) {
			$maxlong=$myarray[0];
		}
		if ($myarray[1] < $minlat) {
			$minlat=$myarray[1];
		}
		if ($myarray[1] > $maxlat) {
			$maxlat=$myarray[1];
		}
	} // foreach
	$minlong -= 5;
	$maxlong += 5;
	$minlat -= 5;
	$maxlat += 5;
	$W_minx=$minlong; //-160 
	$W_miny=$minlat; //0  
	$W_maxx=$maxlong; //80 
	$W_maxy=$maxlat; //50 
} // if latlonginto > ''
echo '</TD></TR>';
// end LOCATION




// GEOLOGY

	echo '<TR>';
	if ($editable) {
		?>
		<TD style="white-space:nowrap"><INPUT TYPE="button" value="Set" onClick="location='setgeology.php?pkey=<?=$pkey?>&glossary_pkey=<?=$glossary_pkey?>'">
		<?
		if ($searchquery->geology != "") {
			$show_results = true;
			?>
			<INPUT TYPE="button" value="Clear" onClick="location='cleargeology.php?pkey=<?=$pkey?>&glossary_pkey=<?=$glossary_pkey?>'">
			<?
		}
		echo "</td>";
	} // if 
	echo '<TD><div class="searchlabel">GEOLOGIC PROVINCE</div><div style="color:#333333;">e.g. Fiji Ridge, Salton Trough</div></TD><TD>';
	
	if ($searchquery->geology != "") {
		$geonum=$searchquery->geology;
		$showgeologys = $db->get_results("select name from shapegeology where gid in ($geonum)");
		foreach($showgeologys as $geologyname){
			$showgeology.=$showgeologydelim.$geologyname->name;
			$showgeologydelim="<br>";
		}


		$showgeo="yes";
	}else{
		$showgeology = "NO&nbsp;CONSTRAINTS&nbsp;SET";
		$showgeo="no";
	}
	
	if($showgeo=="yes"){
		//show pic here
		echo "<img src=\"geologypoly.php?size=small&gids=$geonum\"><br>";
		echo $showgeology;
	}else{
		echo $showgeology;
	}
	
	
	echo '</td></tr>';

// end GEOLOGY



// OCEAN FEATURE NAME

	echo '<TR>';
	if ($editable) {
		?>
		<TD style="white-space:nowrap"><INPUT TYPE="button" value="Set" onClick="location='setoceanfeature.php?pkey=<?=$pkey?>&glossary_pkey=<?=$glossary_pkey?>'">
		<?
		if ($searchquery->oceanfeature != "") {
			$show_results = true;
			?>
			<INPUT TYPE="button" value="Clear" onClick="location='clearoceanfeature.php?pkey=<?=$pkey?>&glossary_pkey=<?=$glossary_pkey?>'">
			<?
		}
		echo "</td>";
	} // if 
	echo '<TD><div class="searchlabel">OCEAN FEATURE NAME</div><div style="color:#333333;">e.g. Carlsberg Ridge, Mid-Atlantic Ridge</div></TD><TD>';
	
	if ($searchquery->oceanfeature != "") {
		$geonum=$searchquery->oceanfeature;
		$showoceanfeatures = $db->get_results("select name from oceanfeatures where gid in ($geonum)");
		foreach($showoceanfeatures as $oceanfeaturename){
			$showoceanfeature.=$showoceanfeaturedelim.$oceanfeaturename->name;
			$showoceanfeaturedelim="<br>";
		}


		$showof="yes";
	}else{
		$showoceanfeature = "NO&nbsp;CONSTRAINTS&nbsp;SET";
		$showof="no";
	}
	
	if($showof=="yes"){
		//show pic here
		echo "<img src=\"oceanfeaturepoly.php?size=small&gids=$geonum\"><br>";
		echo $showoceanfeature;
	}else{
		echo $showoceanfeature;
	}
	
	
	echo '</td></tr>';

// end OCEAN FEATURE NAME









// VOLCANO

	echo '<TR>';
	if ($editable) {
		?>
		<TD style="white-space:nowrap"><INPUT TYPE="button" value="Set" onClick="location='setvolcano.php?pkey=<?=$pkey?>&glossary_pkey=<?=$glossary_pkey?>'">
		<?
		if ($searchquery->volcano != "") {
			$show_results = true;
			?>
			<INPUT TYPE="button" value="Clear" onClick="location='clearvolcano.php?pkey=<?=$pkey?>&glossary_pkey=<?=$glossary_pkey?>'">
			<?
		}
		echo "</td>";
	} // if 
	echo '<TD><div class="searchlabel">VOLCANO NAME</div><div style="color:#333333;">e.g. Hood, Huaynaput</div></TD><TD>';
	
	if ($searchquery->volcano != "") {
		$volcanonum=$searchquery->volcano;
		$showvolcanos = $db->get_results("select name from shapevolcano where gid in ($volcanonum)");
		
		foreach($showvolcanos as $volcname){
			$showvolcano.=$volcdelim.$volcname->name;
			$volcdelim="<br>";
		}
		
		$showvol="yes";
	}else{
		$showvolcano = "NO&nbsp;CONSTRAINTS&nbsp;SET";
		$showvol="no";
	}
	
	if($showvol=="yes"){
		//show pic here
		echo "<img src=\"volcanoimage.php?size=small&gids=$volcanonum\"><br>";
		echo $showvolcano;
	}else{
		echo $showvolcano;
	}
	
	
	echo '</td></tr>';

// end VOLCANO














// ROCK TYPE
echo '<TR>';
if ($editable) {
	?>
	<TD style="white-space:nowrap"><INPUT TYPE="button" value="Set" onClick="location='setrocktype.php?pkey=<?=$pkey?>&glossary_pkey=<?=$glossary_pkey?>'">
	<?
	//if ($searchquery->rockclass > "" or $searchquery->rockname > "") {
	if (
			$searchquery->level1 > "" or 
			$searchquery->level4 > "" or 
			$searchquery->rockname > "" or
			$searchquery->tasname > "" or
			$searchquery->ech > ""
		) {
		?>
		<INPUT TYPE="button" value="Clear" onClick="location='clearrocktype.php?pkey=<?=$pkey?>&glossary_pkey=<?=$glossary_pkey?>'">
		<?
	}
	echo "</td>";
} // if 
echo '<TD><div class="searchlabel">SAMPLE TYPE</div><div style="color:#333333;">e.g.&nbsp;Igneous&nbsp;or&nbsp;Sedimentary</div></TD><TD>';
$constraints_set = false;
//rockclass
if ($searchquery->level1 > '') {
$constraints_set = true;
echo '<b>ROCK CLASS:</b><br>'.$searchquery->level1.'<br>';
}
//rockname
if ($searchquery->level2 > '') {
$constraints_set = true;
echo '<b>TYPE:</b><br>'.$searchquery->level2.'<br>';
}
if ($searchquery->level3 > '') {
$constraints_set = true;
echo '<b>COMPOSITION:</b><br>'.$searchquery->level3.'<br>';
}
if ($searchquery->level4 > '') {
$constraints_set = true;
echo '<b>ROCK NAME:</b><br>'.str_replace(",",", ",$searchquery->level4).'<br>';
}
if ($searchquery->tasname > '') {
$constraints_set = true;
echo '<b>ROCK NAME:</b><br>'.$searchquery->tasname.'<br>';
}
if ($searchquery->rockname > '') {
$constraints_set = true;
echo '<b>ROCK NAME:</b><br>'.$searchquery->rockname.'<br>';
}
if ($searchquery->ech > '') {
$constraints_set = true;
echo '<b>EARTHCHEM CATEGORIES:</b><br>'.str_replace(",","<br>",$searchquery->ech).'<br>';
}
if ($constraints_set) {
$show_results=true;
} else {
echo 'NO&nbsp;CONSTRAINTS&nbsp;SET';
}
echo '</td></tr>';
// end ROCK TYPE


// CHEMISTRY
echo '<tr>'; 
if ($editable) {
	?>
	<TD style="white-space:nowrap"><INPUT TYPE="button" value="Set" onClick="location='setchemistry.php?pkey=<?=$pkey?>&glossary_pkey=<?=$glossary_pkey?>'">
	<?
	if ($searchquery->chemistry > '' or $searchquery->lepr > '') {
		$show_results = true;
		?>
		<INPUT TYPE="button" value="Clear" onClick="location='clearchemistry.php?pkey=<?=$pkey?>&glossary_pkey=<?=$glossary_pkey?>'">
		<?
	}
	echo '</td>';
} 
echo '<td><div class="searchlabel">CHEMISTRY</div><div style="color:#333333;">e.g. 55 < WT% SiO2 < 66</div></TD><td>';

//debug echo "<br><font color=fuchsia>searchquery.CHEMISTRY is $searchquery->CHEMISTRY</font><br>";
$constraints_set = false;
if ($searchquery->chemistry > '') { 
	// Make an array of the methods. Each array element will be something like item:method;method;method 
	if ($searchquery->chemmethods > '') {
		$chemmethods_array=split(',',$searchquery->chemmethods); 
	} else {
		$chemmethods_array=array();
	}
	//$chemmethods_array=sort($chemmethods_array);
	$chemistry_array=split(',', $searchquery->chemistry);
	//$chemistry_array=sort($chemistry_array);
	foreach($chemistry_array as $currchem) { //echo '<br><font color=red>'.$currchem.'</font>';
		// display the chemical and constraint, and get the chemical name 
		$chemstring = ''; // This is the way we will display the chemical and constraints 
		if (strpos($currchem,'EXISTS')>0) { // pattern is like SIO2:exists
			$currchem_array=split(':',$currchem); // split chemical name and 'EXISTS'
			$chemname=$currchem_array[0];
			$chemstring='<b>'.$currchem_array[0].'</b>'.' '.$currchem_array[1]; // This should be like SIO2 EXISTS
			$constraints_set = true;
		} elseif (strpos($currchem,':')>0) { // pattern is like 10:SIO2:75
			$currchem_array=split(':',$currchem); // split at the : into lowerbound, chemical name, upperbound 
			$i=strpos($chemname,':'); 
			$lowerbound=$currchem_array[0];
			$upperbound=$currchem_array[2];
			$chemname=$currchem_array[1];
			$chemstring = $lowerbound.' < <b>'.$chemname.'</b> < '.$upperbound; // This should be like 10 < SIO2 < 75 
			$constraints_set = true;
		}
		if ($chemstring > '') {
			echo $chemstring;
		} else {
			echo $currchem;
		} 
		//If this chemical has a method, show it 
		for ($i=0;$i<sizeof($chemmethods_array);$i++) { 
			$l = strlen($chemname); 
			if (substr($chemmethods_array[$i],0,$l) == $chemname) {
				echo ' (';
				echo substr($chemmethods_array[$i],$l+1);
				echo ')'; 
				//unset($chemmethods_array[$i]);
				//break;
			} // if
		} // for
		echo '<br>';
	} // foreach
} // if




//lepr here
if ($searchquery->lepr > '') { 
	$lepr_array=split(';', $searchquery->lepr);
	foreach($lepr_array as $currlepr) { 
		// display the lepr and constraint, and get the lepr name 
		$leprstringy = ''; // This is the way we will display the lepr and constraints 
		if (strpos($currlepr,'checked')>0) { // pattern is like SIO2:exists
			$currlepr_array=split(':',$currlepr); // split chemical name and 'checked'
			$leprname=$currlepr_array[0];
			$leprstringy='<b>'.ucfirst($currlepr_array[0]).'</b>'.' EXISTS'; // This should be like SIO2 EXISTS
			$constraints_set = true;
		} elseif (strpos($currlepr,':')>0) { // pattern is like 10:SIO2:75
			$currlepr_array=split(':',$currlepr); // split at the : into lowerbound, chemical name, upperbound 
			$i=strpos($leprname,':'); 
			$lowerbound=$currlepr_array[0];
			$upperbound=$currlepr_array[2];
			$leprname=$currlepr_array[1];
			$leprstringy = $lowerbound.' <b>'.$leprname.'</b> < '.$upperbound; // This should be like 10 < SIO2 < 75 
			$constraints_set = true;
		}
		if ($leprstringy > '') {
			echo $leprstringy;
		} else {
			echo $currchem;
		} 
		echo '<br>';
	} // foreach
} // if




if ($constraints_set) {
$show_results=true; 
} else {
echo 'NO&nbsp;CONSTRAINTS&nbsp;SET';
} // if


echo '</TD></TR>';
// end CHEMISTRY







// AGE
	echo '<TR>';
	if ($editable) {
		?>
		<TD style="white-space:nowrap"><INPUT TYPE="button" value="Set" onClick="location='setage.php?pkey=<?=$pkey?>&glossary_pkey=<?=$glossary_pkey?>'">
		<?
		if ($searchquery->age_min != "" or $searchquery->age_max != "" or $searchquery->age != "" or $searchquery->geoage != "" or $searchquery->ageexists != "") {
			$show_results = true;
			//echo "here";
			?>
			<INPUT TYPE="button" value="Clear" onClick="location='clearage.php?pkey=<?=$pkey?>&glossary_pkey=<?=$glossary_pkey?>'">
			<?
		}
		echo "</td>";
	} // if 
	echo '<TD><div class="searchlabel">AGE</div><div style="color:#333333;">e.g. 55 Ma or Paleocene</div></TD><TD>';
	
	if ($searchquery->age_min != "" or $searchquery->age_max != "" or $searchquery->age != "" or $searchquery->geoage != "" or $searchquery->ageexists != "") {
		if($searchquery->age!=""){
			$showage = "Age = ".$searchquery->age;
		}elseif($searchquery->age_min!="" and $searchquery->age_max!=""){
			$showage = $searchquery->age_min." < Age < ".$searchquery->age_max;
		}elseif($searchquery->age_min!="" or $searchquery->age_max!=""){
			if($searchquery->age_min!=""){
				$showage = "Age > ".$searchquery->age_min;
			}elseif($searchquery->age_max!=""){
				$showage = "Age < ".$searchquery->age_max;
			}
		}elseif($searchquery->geoage!=""){
			//get minage and maxage from geoage
			$thisagerow=$db->get_row("select * from geoages where pkey=".$searchquery->geoage);
			
			$showage = "Geological Age: ".$thisagerow->agename." ".$thisagerow->agelabel;
		}
		if($searchquery->ageexists!=""){
			if($showage==""){
				$showage="Age Exists";
			}else{
				$showage.="<br>Age Exists";
			}
		}
		$show_results=true;
		$constraints_set=true;
	}else{
		$showage = "NO&nbsp;CONSTRAINTS&nbsp;SET";
	}
	
	echo $showage;
	
	
	echo '</td></tr>';
// end AGE









// MINERAL
if($searchquery->material == "3"){
	echo '<TR>';
	if ($editable) {
		?>
		<TD style="white-space:nowrap"><INPUT TYPE="button" value="Set" onClick="location='setmineral.php?pkey=<?=$pkey?>&glossary_pkey=<?=$glossary_pkey?>'">
		<?
		if ($searchquery->mineralname != "") {
			$show_results = true;
			?>
			<INPUT TYPE="button" value="Clear" onClick="location='clearmineral.php?pkey=<?=$pkey?>&glossary_pkey=<?=$glossary_pkey?>'">
			<?
		}
		echo "</td>";
	} // if 
	echo '<TD><div class="searchlabel">MINERAL NAME</div></TD><TD>';
	
	if ($searchquery->mineralname != "") {
		$showmineral = ucfirst($searchquery->mineralname);
	}else{
		$showmineral = "NO&nbsp;CONSTRAINTS&nbsp;SET";
	}
	
	echo $showmineral;
	
	
	echo '</td></tr>';
}
// end MINERAL














// MATERIAL
echo '<TR>';
if ($editable) {
	?>
	<TD style="white-space:nowrap"><INPUT TYPE="button" value="Set" onClick="location='setmaterial.php?pkey=<?=$pkey?>&glossary_pkey=<?=$glossary_pkey?>'">
	<?
	echo "</td>";
} // if 
echo '<TD><div class="searchlabel">MATERIAL</div><div style="color:#333333;">e.g. Rock or Glass</div></TD><TD>';

if ($searchquery->material == '1,4' or $searchquery->material == 'rock') {
	$showmaterial = "BULK";
}elseif($searchquery->material == '1') {
	$showmaterial = "WHOLE ROCK / ROCK";
}elseif($searchquery->material == "4"){
	$showmaterial = "GLASS";
}elseif($searchquery->material == "2,4"){
	$showmaterial = "GLASS / INCLUSION";
}elseif($searchquery->material == "3"){
	$showmaterial = "MINERAL";
}elseif($searchquery->material == "2"){
	$showmaterial = "INCLUSION";
}elseif($searchquery->material == "4"){
	$showmaterial = "GLASS";
}

/*
if ($searchquery->material == '1' or $searchquery->material == 'rock') {
	$showmaterial = "ROCK / WHOLE ROCK";
}elseif($searchquery->material == "2"){
	$showmaterial = "INCLUSION";
}elseif($searchquery->material == "3"){
	$showmaterial = "MINERAL";
}elseif($searchquery->material == "4"){
	$showmaterial = "GLASS / GROUNDMASS";
}
*/

echo '<b>MATERIAL:</b><br>'.$showmaterial.'<br>';


echo '</td></tr>';
// end MATERIAL





// NORMALIZATION
echo '<TR>';
if ($editable) {
	?>
	<TD style="white-space:nowrap"><INPUT TYPE="button" value="Set" onClick="location='setnormalization.php?pkey=<?=$pkey?>&glossary_pkey=<?=$glossary_pkey?>'">
	<?
	echo "</td>";
} // if 
echo '<TD><div class="searchlabel">NORMALIZATION</div><div style="color:#333333;">Fe Conversion</div></TD><TD>';

if ($searchquery->normalization == 'reported') {
	$shownormal = "Major elements as reported";
}elseif($searchquery->normalization == 'normal') {
	$shownormal = "Major Elements Normalized to FeOT and 100% Volatile Free Basis";
}



echo '<b>Normalization:</b><br>'.$shownormal.'<br>';


echo '</td></tr>';
// end NORMALIZATION


// Go to Data button 
echo '<TR><TD colspan="'.$colspan.'" align="left" style="border:none">';
if ($show_results and !$error and $totalcount>0) {
	//echo '<INPUT style="float:right" type="image" src="images/gotodata.gif" name="clicky">';
}
?>			
</TD>
</TR>

</TABLE>

</td><td valign="top">
		
<?php
if ($show_results and !$error) {
// Show the list of checkboxes for the user to choose the sources

?>
<table class='ku_sources' style='margin-right:10px'>
	<tr>
		<th colspan='4' nowrap>Native EarthChem Data:
		</th>
	</tr>
	
	<?
	
	//dumpVar($datasources);
	
	foreach($datasources as $ds){
	if($ds->level=="native"){
	?>
	
	<tr bgcolor='White'>
		<td>
			<input type='checkbox' name='<?=$ds->name?>' value='true' checked>
		</td>
		<td><?=$ds->showname?>
		</td>
		<td align='right' nowrap><?=eval("echo \$".$ds->name."total;") ?> samples&nbsp;found</td>
		<td>
		<?
		eval("\$thistotal=\$".$ds->name."total;");
		if($thistotal>0){
		?>
		<div style="padding:3px;">
			<input type=button value="View <?=$ds->showname?> Samples" onClick="window.location = 'quickresults.php?pkey=<?=$pkey?>&<?=$ds->name?>=true';">
		</div>
		<?
		}else{echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"; }
		?>
		</td>

	</tr>

	<?
	}//end if native
	}
	?>













</table>

<br>

<table cellpadding="0" cellspacing="0">
	<tr>
		<td>
			<table class='ku_sources' style='margin-right:10px'>
				<tr>
					<th colspan='4' nowrap>Affiliate EarthChem Data:
					</th>
				</tr>






	<?
	foreach($datasources as $ds){
	if($ds->level=="affiliate"){
	?>
	
	<tr bgcolor='White'>
		<td>
			<input type='checkbox' name='<?=$ds->name?>' value='true' checked>
		</td>
		<td><?=$ds->showname?>
		</td>
		<td align='right' nowrap><?=eval("echo \$".$ds->name."total;") ?> samples&nbsp;found</td>
		<td>
		<?
		eval("\$thistotal=\$".$ds->name."total;");
		if($thistotal>0){
		?>
		<div style="padding:3px;">
			<input type=button value="View <?=$ds->showname?> Samples" onClick="window.location = 'quickresults.php?pkey=<?=$pkey?>&<?=$ds->name?>=true';">
		</div>
		<?
		}else{echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"; }
		?>
		</td>

	</tr>

	<?
	}//end if affiliate
	}
	?>








			</table>
		</td>
		<?
		if($gansekitotal > 0){
		?>
		<td>
			<table style='margin-right:10px'>
				<tr>
					<th nowrap>&nbsp;</th>
				</tr>
				<tr bgcolor="#FFFFFF">
					<td style="font-size:1em;" nowrap>
					Click <a href="http://www.jamstec.go.jp/ganseki/index.html" target="_blank">here</a> to search Ganseki
					</td>
				</tr>
			</table>
		</td>
		<?
		}
		?>
	</tr>
</table>

<br>

<table class='ku_sources' style='margin-right:10px'>
	<tr bgcolor='White' valign='bottom'>
		<td colspan='2' nowrap>Total from all sources
		</td>
		<td align='right'><?=$totalcount ?>
		</td>
	</tr>
</table>

<?
if ($show_results and !$error and $totalcount>0) {
	echo "<br><br><div align=\"center\">";
	echo '<INPUT type="submit" name="clicky" value="Continue to Data Selection"><br>';
	if($searchquery->queryname=="Untitled"){
	echo '<br><INPUT type="button" name="saveme" value="Save Query" onclick="window.location=\'savequery.php?pkey='.$pkey.'\'">';
	}
	echo "</div>";
}
?>




<?php
}
// if 
if ($glossary_entry and is_numeric($glossary_pkey) and is_numeric($glossary->users_pkey)) {
	// If this is a glossary entry, show the name of the author. 
	$author=$db->get_row("SELECT firstname, lastname, email from users where pkey = $glossary->users_pkey");
	?>
	<br>The author of this Glossary Entry is <?=$author->firstname?>&nbsp;<?=$author->lastname?>&nbsp;<a href="mailto:<?=$author->email?>"></a>
	<?
}
?>

</td></tr></table>

<input type='hidden' name='pkey' value='<?=$pkey ?>'>
<!---Pass the sample counts, so we can calculate the number of samples and decide whether to post a warning message beside the Dynamikc Map. --->
<?
foreach($datasources as $ds) {
?>
<input type='hidden' name='<?=$ds->name?>total' value='<? eval("echo \$".$ds->name."total;") ?>'>
<?
}
?>
</FORM>


<div id="debug" style="color:#666666;font-size:.8em;display:none;">

countstring:
<?php

echo nl2br($countstring);

echo "<br><br><br> ".nl2br($leprstring);

echo "<br><br> ".nl2br($mapstring);
//echo "<br><br><br><br><br><br><br><br><br><br><br><br> ".nl2br($countstring);

echo "<p>pkey $pkey <p>";

echo "<b>form vars:</b><br>";
foreach($_POST as $key=>$value){
	echo "$key : $value<br>";
}

print_r($user);

?>
</div>
<?
//echo "<div style=\"font-size:.5em;\">hopper</div>";
include('includes/ks_footer.html');
?>
