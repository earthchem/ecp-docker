<?PHP
/**
 * queries.php
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


$lepr_items_array=array('albite','anorthite','apatite','calciumorthosilicate','corundum','diopside','hypersthene','kaliophilite','leucite','nepheline','olivine','orthoclase','perofskite','potassiummetasilicate','quartz','rutile','sodiummetasilicate','titanite','wollastonite');

//and st_geomfromtext('Polygon(($coordbox))') ~ crd.mypoint

// This file is included in search.php. It contains most of the long queries used in EarthChem.
include_once "db.php"; // Database drivers and connect. This should be included in the file that includes this one

if ( !isset($pkey) || !(is_numeric($pkey)) ) { // Make it possible for a programmer to go directly to this page, providing pkey as a url parameter 
	include "get_pkey.php";
}
if ( !isset($pkey) || !(is_numeric($pkey)) ) {
	//echo '<br>No search has been defined in queries.php. Please start a new search.';
	if(!isset($getsearch)){
	exit;
	}
}
if (!isset($srcwhere) or $srcwhere=="") { 
	include "srcwhere.php"; // Create a bit of sql limiting the sources (sample.source), e.g. "" or " AND (source='navdat' OR source='usgs')
}

// Eileen make these arrays throughout, until you get this done allow for both array and string in allfields variable.
// For Quick Display output, we won't limit the samples to display by any chemistry.
// For Advanced Output, the default is to be sure that at least SIO2, TIO2, AL2O3 values exist.
// Var fieldlist_array is the list of chemicals we want to display.
if (isset($allfields_array)) { //mistake? was if (isset($fieldlist_array)) { JMA 11/06/2008
	$fieldlist=implode(',',$allfields_array);
	$fieldlist_array=$allfields_array;
} elseif (isset($allfields)) {
	$fieldlist_array = split(',',$allfields);
	$fieldlist=implode(',',$allfields_array);
} elseif (isset($allfields_array)) {
	$fieldlist_array=$allfields_array;
	$fieldlist=implode(',',$fieldlist_array);
} else {
	//$fieldlist = 'SIO2,TIO2,AL2O3';
	//$fieldlist_array = split(',',$fieldlist);
	$fieldlist = '';
	//$fieldlist_array = split(',',$fieldlist);
}



if (isset($_GET['showmethods'])) {
	$showmethods = $_GET['showmethods'];
} else {
	$showmethods = false;
}

if (isset($_GET['showunits'])) {
	$showunits = $_GET['showunits'];
} else {
	$showunits = false;
}

// Get the record for the search 
if(!isset($getsearch)){ //allow for SOAP service
	$getsearch = $db->get_row('select * from search_query where pkey = '.$pkey);
}


$chemswitch=$getsearch->chemswitch;


if($getsearch->normalization=="normal"){
	$methview="vmethnormal";
	$matview="vmatnormal";
}else{
	$methview="vmethreported";
	$matview="vmatreported";
}

if($getsearch->material!=""){
	if($getsearch->material=="rock"){
		$mynormmaterial="1,4";
	}else{
		$mynormmaterial=$getsearch->material;
	}
}else{
	$mynormmaterial="1,4";
}


// Ages for simplestuff
$theage = '';
if ($getsearch->age_min != "" or $getsearch->age_max != "" or $getsearch->age != "" or $getsearch->geoage != "" ) {
	
	if($getsearch->age_min != "" or $getsearch->age_max != ""){
		if($getsearch->age_min != ""){
			$theage .= " AND age >= ".$getsearch->age_min;
		}
		if($getsearch->age_max != ""){
			$theage .= " AND age <= ".$getsearch->age_max;
		}
	}elseif($getsearch->age != ""){
		$theage = " AND age = ".$getsearch->age." ";	
	}else{
		$thisagerow=$db->get_row("select * from geoages where pkey=".$getsearch->geoage);
		$theage = " AND age >= ".$thisagerow->minage." AND age <= ".$thisagerow->maxage." ";
	}
	
	//echo "theage: $theage";

	$simplestuff .= $theage;
}

// Age Exists
if($getsearch->ageexists != ""){
	$theage .= " AND age is not null ";
	$simplestuff .= " AND age is not null ";
}

//Sample ID here
if($getsearch->sampleid != ""){
	$thisid=$getsearch->sampleid;
	$fixed_id=$db->get_var("select fix_sample_id('$thisid')");
	$thesampleid = " AND fixed_sample_id like '".$fixed_id."'";
	$simplestuff .= $thesampleid;
}

//IGSN here
if($getsearch->igsn != ""){
	$thisigsn=trim(strtolower($getsearch->igsn));
	$theigsn = " AND lower(igsn)='".$thisigsn."'";
	$simplestuff .= $theigsn;
}

//Cruise ID here
if($getsearch->cruiseid != ""){
	$cruiseid=$getsearch->cruiseid;
	
	$cruiseidarray = explode(",",$cruiseid);
	
	$cruiselist="";
	$cruisedelim="";
	
	foreach($cruiseidarray as $currcruise){
	
		$currcruise=strtolower($currcruise);
	
		$cruiselist.="$cruisedelim'$currcruise'";
		
		$cruisedelim=",";
	}
	
	$cruiselist="($cruiselist)";
	
	$thecruiseid = " AND lower(expeditionid) in $cruiselist";
	$simplestuff .= $thecruiseid;
}



// Author for simplestuff
$theauthor = '';
if ($getsearch->author > '') {
	$authstring=$getsearch->author;
	$authstring=str_replace(" and "," ",$authstring);
	$authstring=str_replace(" & "," ",$authstring);
	$authstring=str_replace(" "," & ",$authstring);
	$theauthor .= " AND authorlistvector @@ to_tsquery('$authstring')";
	$simplestuff .= $theauthor;
}


// Title for simplestuff 
$thetitle = '';
if ( $getsearch->title != '') { 
	$titlestring=$getsearch->title;
	$titlestring=str_replace(" and "," ",$titlestring);
	$titlestring=str_replace(" & "," ",$titlestring);
	$titlestring=str_replace(" "," & ",$titlestring);
	$thetitle .= " AND titlevector @@ to_tsquery('".$titlestring."') ";
	$simplestuff .= $thetitle;
}

// DOI for simplestuff 
$thedoi = '';
if ( $getsearch->doi != '') { 
	$doistring=$getsearch->doi;
	$thedoi .= " AND doi = '$doistring' ";
	$simplestuff .= $thedoi;
}



// Journal for simplestuff
$thejournal = '';
if ($getsearch->journal != '') {
	$journalstring=$getsearch->journal;
	$journalstring=str_replace(" and "," ",$journalstring);
	$journalstring=str_replace(" & "," ",$journalstring);
	$journalstring=str_replace(" "," & ",$journalstring);
	$thejournal .= " AND journalvector @@ to_tsquery('".$journalstring."')  ";
	$simplestuff .= $thejournal;
}

// Keyword for simplestuff. 
$thekeyword = ''; 
if ($getsearch->advkeyword1 > '') {
	$keywordstring=$getsearch->advkeyword1;
	$keywordstring=str_replace(" and "," ",$keywordstring);
	$keywordstring=str_replace(" & "," ",$keywordstring);
	$keywordstring=str_replace(" "," & ",$keywordstring);
	$thekeyword .= " AND ( gendescvector @@ to_tsquery('".$keywordstring."') ) ";
	$simplestuff .= $thekeyword;
}

// Publication Year for simplestuff
$thepubyear = '';
if ( $getsearch->yearmin != '' && $getsearch->yearmax != '' ) {
	$thepubyear .= " AND pubyear >= ".$getsearch->yearmin." AND pubyear <= ".$getsearch->yearmax." ";
	$simplestuff .= $thepubyear;
}



// Coordinates - Latitude, Longitude for simplestuff 
$thecoordinates = '';
$polylist="";
$polydelim="";

if ($getsearch->polarpolygon > '') {

	$newpolarpolygon="";
	$polarpolygon = $getsearch->polarpolygon;
	$parts = explode("; ",$polarpolygon);
	foreach($parts as $part){
		$newpolarpolygon .= $part . ",";
	}
	$newpolarpolygon .= $parts[0];
	$thecoordinates .= " AND the_geom is not null \n"." 
			AND ST_Contains(ST_GeomFromText('POLYGON(($newpolarpolygon))',3031),ST_Transform(ST_SetSRID(the_geom,4326),3031))\n";
	$simplestuff .= $thecoordinates;
}elseif ($getsearch->polygon > '') {
	$srs = $getsearch->srs;
	$iedapolygon = $getsearch->iedapolygon;
	if($srs!="" && $srs!="4326" && $srs!=4326 && $iedapolygon!=""){
		$thecoordinates.= " AND the_geom is not null "." 
			AND ST_Contains(ST_GeomFromText('POLYGON(($iedapolygon))',$srs),ST_Transform(the_geom,$srs))"; 
	}else{
		$polylist="";
		$polydelim="";

		//need to add code here to split on a colon (:) for multiple polys
		$mypolys=$getsearch->polygon;	
		$mypolys=explode(":",$mypolys);
		foreach($mypolys as $mypoly){
			$mybox="";
			$coordlist=$mypoly;
			$coordarray=explode("; ",$coordlist);
			foreach($coordarray as $currcoord){
				$mybox.=$currcoord.",";
			}
			$mybox.=$coordarray[0];
		
			$polylist.=$polydelim."(($mybox))";
			$polydelim=", ";
	
		}

		$newpoly=$db->get_var("select fixpoly('Multipolygon($polylist)')");
		//$thecoordinates = " and ST_Contains(st_geomfromtext('$newpoly',4326),the_geom)";
		$thecoordinates = " and ST_Contains(st_geomfromtext('$newpoly'),the_geom)";

	}

	$simplestuff .= $thecoordinates;


}elseif ($getsearch->longitudeeast != '') {

	$top=$getsearch->latitudenorth;
	$bottom=$getsearch->latitudesouth;
	$right=$getsearch->longitudeeast;
	$left=$getsearch->longitudewest;
	
	$mybox="$left $top,$right $top,$right $bottom,$left $bottom,$left $top";
	$thecoordinates = " and st_geomfromtext('Polygon(($mybox))') ~ the_geom";
	$simplestuff .= $thecoordinates;
}


if($getsearch->geology != ""){

	$polylist="";
	$polydelim="";

	//do geology here
	$geologydelim="";
	
	$geonums=$getsearch->geology;
	$geonums=explode(",",$geonums);
	
	foreach($geonums as $geonum){
		$mybox=$db->get_var("select poly from shapegeology where gid=$geonum");
		$polylist.=$polydelim."(($mybox))";
		$polydelim=", ";
	}

	$newpoly=$db->get_var("select fixpoly('Multipolygon($polylist)')");
	$thegeology = " and ST_Contains(st_geomfromtext('$newpoly'),the_geom)";
	$simplestuff .= $thegeology;

}

if($getsearch->oceanfeature != ""){

	//do oceanfeature here

	$oceanfeaturedelim="";
	
	$oceanfeaturenums=$getsearch->oceanfeature;
	$oceanfeaturenums=explode(",",$oceanfeaturenums);
	
	foreach($oceanfeaturenums as $oceanfeaturenum){
		$oceanfeaturestring=$db->get_var("select ST_AsText(the_geom) from oceanfeatures where gid=$oceanfeaturenum");
		$oceanfeaturestring=str_replace("MULTILINESTRING((","MULTIPOLYGON(((",$oceanfeaturestring);
		$oceanfeaturestring=str_replace("))",")))",$oceanfeaturestring);
		$theoceanfeature .= $oceanfeaturedelim."ST_Contains(st_geomfromtext('$oceanfeaturestring'),the_geom)";
		$oceanfeaturedelim = " or ";
	}
	
	$theoceanfeature = " and ( $theoceanfeature )";
	$simplestuff .= $theoceanfeature;


}


if($getsearch->volcano != ""){

	//do volcano here
	$volcanodelim="";
	$volcanonums=$getsearch->volcano;
	$volcanonums=explode(",",$volcanonums);
	
	foreach($volcanonums as $volcanonum){
		$volcanostring=$db->get_row("select lat,lon,octagon from shapevolcano where gid=$volcanonum");
		$lon=$volcanostring->lon;
		$lat=$volcanostring->lat;
		$octagon=$volcanostring->octagon;
	
		//$thevolcano = " and st_distance_sphere( crd.mypoint, PointFromText('POINT($lon $lat)') ) < 5000";
	
		$thevolcano .= $volcanodelim."ST_Contains(st_geomfromtext('Polygon(($octagon))'),the_geom)";
		$volcanodelim = " or ";
	}
	
	$thevolcano=" and ( $thevolcano )";
	$simplestuff .= $thevolcano;

}




// Chemistry for simplestuff
$chemstring = '';
if ($getsearch->chemistry != '') {  
	$delim=''; 
	$chemistry_array=split(',',$getsearch->chemistry); 
	foreach($chemistry_array as $currchem) {  

		$chemname='';  
		if (strpos($currchem,':EXISTS') > 0) {  
			$chemname = substr($currchem,0,strpos($currchem,':EXISTS'));  
			$chemstring .= " $delim ( $chemname > -99 ) ";  
			$delim = " AND ";
		} elseif (substr_count($currchem,':') > 1 ) {  
			$currchem_array=split(':',$currchem);  
			$i=strpos($chemname,':'); 
			$lowerbound=$currchem_array[0];
			$upperbound=$currchem_array[2];
			$chemname=$currchem_array[1];
			$chemstring .= " $delim ( $chemname > $lowerbound AND $chemname < $upperbound ) ";
			$delim = " AND ";
		
		} // if      
		$methstring='';  
		$methdelim='';  
		$chemmethods_array=split(',',$getsearch->chemmethods); 
		foreach($chemmethods_array as $currmeth) {  
			if (strpos($currmeth,'$chemname:') == 0 ) { 
				$loopflag=false;  
				$currmeth_array=split(':',$currmeth);
				foreach($currmeth_array as $mycurr) {  
					if ($loopflag) {      
						$currmethlist=explode(";",$mycurr);
						foreach($currmethlist as $methname){ //needed to add this loop here because code wasn't digging deep enough - JMA
							$methstring .= " $methdelim chem.method = '$methname' "; 
							$methdelim = " OR ";      
						}
					} // if      
					$loopflag=true;      
				} // foreach     
			} // if     
		} // foreach         
		if ($methstring > '') {      
			$chemstring .= " AND ( $methstring ) ";      
		} // if     
		$delim = " $chemswitch ";            
	} // foreach chemistry_array      
	//$chemstring = " AND mat.material_pkey in ( select material_pkey from $matview where $chemstring ) ";
	
	$chemstring = " and ($chemstring) ";
	
} // if 



// Chemistry for sample level output
$chemstringsample = '';
if ($getsearch->chemistry != '') {  
	$delim=''; 
	$chemistry_array=split(',',$getsearch->chemistry); 
	foreach($chemistry_array as $currchem) {  

		$chemname='';  
		if (strpos($currchem,':EXISTS') > 0) {  
			$chemname = substr($currchem,0,strpos($currchem,':EXISTS'));  
			$chemstringsample .= " $delim ( $chemname > -99 ) ";  
			$delim = " AND ";
		} elseif (substr_count($currchem,':') > 1 ) {  
			$currchem_array=split(':',$currchem);  
			$i=strpos($chemname,':'); 
			$lowerbound=$currchem_array[0];
			$upperbound=$currchem_array[2];
			$chemname=$currchem_array[1];
			$chemstringsample .= " $delim ( $chemname > $lowerbound AND $chemname < $upperbound ) ";
			$delim = " AND ";
		
		
		} // if      
		$methstring='';  
		$methdelim='';  
		$chemmethods_array=split(',',$getsearch->chemmethods); 
		foreach($chemmethods_array as $currmeth) {  
			if (strpos($currmeth,'$chemname:') == 0 ) { 
				$loopflag=false;  
				$currmeth_array=split(':',$currmeth);
				foreach($currmeth_array as $mycurr) {  
					if ($loopflag) {      
						$currmethlist=explode(";",$mycurr);
						foreach($currmethlist as $methname){ //needed to add this loop here because code wasn't digging deep enough - JMA
							$methstring .= " $methdelim chem.method = '$methname' "; 
							$methdelim = " OR ";      
						}
					} // if      
					$loopflag=true;      
				} // foreach     
			} // if     
		} // foreach         
		if ($methstring > '') {      
			$chemstringsample .= " AND ( $methstring ) ";      
		} // if     
		$delim = " $chemswitch ";            
	} // foreach chemistry_array      
	$chemstringsample = "( $chemstringsample ) ";
} // if 


$simplestuff .= $chemstring;               

//LEPR values for simplestuff
if ($getsearch->lepr != '') { 
	$delim=''; 
	$lepr_array=split(';',$getsearch->lepr); 
	foreach($lepr_array as $currlepr) {  

		$leprname='';  
		if (strpos($currlepr,':checked') > 0) {  
			$leprname = substr($currlepr,0,strpos($currlepr,':checked'));  
			$leprstring .= " $delim ( $leprname > -99 ) ";  
			$delim = " OR ";
		} elseif (substr_count($currlepr,':') > 1 ) {  
			$currlepr_array=split(':',$currlepr);  
			$i=strpos($leprname,':'); 
			$lowerbound=$currlepr_array[0];
			$upperbound=$currlepr_array[2];
			$leprname=$currlepr_array[1];
			$leprstring .= " $delim ( $leprname > $lowerbound AND $leprname < $upperbound ) ";
			$delim = " OR ";
		
		} // if
		
	}// end foreach

	$leprstring=" and ($leprstring)";
	$simplestuff .= $leprstring;
	$thelepr = $leprstring;
}




// Rock Names for simplestuff
$therocknames='';
if ($getsearch->tasname != '') {
	//$therocknames .= " and samp.sample_pkey in (select sample_pkey from sampletas where tasname in ( ";
	$therocknames .= " and tasname in ( ";
	$delim="";
	$tasname_array = split(',',$getsearch->tasname);
	foreach ($tasname_array as $currlev) {  
		$therocknames = $therocknames.$delim."'".$currlev."'";
		$delim=",";
	}  
	$therocknames .= ' ) ';
}
if ( $getsearch->level1 != '') { // Fields in sampletype are named CLASS1 CLASS2 CLASS3 CLASS4 TASNAME. There is a discrepancy with uppercase/lowercase between search_query table and sampletype table, so force both to uppercase in this query
	$therocknames .= " and class1 in ( ";
	$delim="";
	$LEVEL1_array = split(',',$getsearch->level1);
	foreach ($LEVEL1_array as $currlev) {  
		$therocknames = $therocknames.$delim."'".strtolower($currlev)."'";
		$delim=",";
	}  
	$therocknames .= ' ) ';
}
if ( $getsearch->level2 != '') {
	$therocknames .= " and class2 in ( ";
	$delim="";
	$LEVEL2_array = split(',',$getsearch->level2);
	foreach ($LEVEL2_array as $currlev) {  
		$therocknames = $therocknames.$delim."'".strtolower($currlev)."'";
		$delim=",";
	} 
	$therocknames .= ' ) '; 
}
if ( $getsearch->level3 != '') {
	$therocknames .= " and class3 in ( ";
	$delim="";
	$LEVEL3_array = split(',',$getsearch->level3);
	foreach ($LEVEL3_array as $currlev) {  
		$therocknames = $therocknames.$delim."'".strtolower($currlev)."'";
		$delim=",";
	}  
	$therocknames .= ' ) ';
}
if ( $getsearch->level4 != '') {
	$therocknames .= " and class4 in ( ";
	$delim="";
	$LEVEL4_array = split(',',$getsearch->level4);
	foreach ($LEVEL4_array as $currlev) { 
		$therocknames = $therocknames.$delim."'".strtolower($currlev)."'";
		$delim=",";
	} 
	$therocknames .= ' ) '; 
}

if ( $getsearch->ech != '') {
	$therocknames .= " and ( ";
	$delim="";
	$ech_array = split(',',$getsearch->ech);
	foreach ($ech_array as $currlev) { 
		//$thisechpkey=$db->get_var("select pkey from ech where hierarchy='$currlev'");
		$echrow=$db->get_row("select * from ech where hierarchy='$currlev'");
		
			$indelim="";
			$therocknames.=$delim." ( ";
			if($echrow->echint1!=""){$therocknames.=$indelim." echint1 = ".$echrow->echint1; $indelim=" and ";}
			if($echrow->echint2!=""){$therocknames.=$indelim." echint2 = ".$echrow->echint2; $indelim=" and ";}
			if($echrow->echint3!=""){$therocknames.=$indelim." echint3 = ".$echrow->echint3; $indelim=" and ";}
			if($echrow->echint4!=""){$therocknames.=$indelim." echint4 = ".$echrow->echint4; $indelim=" and ";}
			$therocknames.=" ) ";
			
		$delim=" or ";
	} 
	$therocknames .= ' ) '; 
}

if ( $getsearch->rockclass != '') {
	$therocknames .= " and ( ";
	$delim="";
	$rockclass_array = split(',',$getsearch->rockclass);
	foreach ($rockclass_array as $currlev) { 
	
		$therocknames .= "$delim(";
		
		$rockdelim="";
		$rockx="1";
		$classarray=split(' : ',$currlev);
		foreach($classarray as $currclass){
			
			$therocknames .= "$rockdelim class$rockx = '$currclass'";
			$rockdelim = " and ";
			$rockx++;
			
		}
		
		$delim=" or ";
		$therocknames .= ")";

	} 
	$therocknames .= ' ) '; 
}

if ( $getsearch->rockname != '') {
	$therocknames .= " and class4 in ( ";
	$delim="";
	$rockname_array = split(',',$getsearch->rockname);
	foreach ($rockname_array as $currlev) { 
		$therocknames = $therocknames.$delim."'".strtolower($currlev)."'";
		$delim=",";
	} 
	$therocknames .= ' ) '; 
}



$simplestuff .= $therocknames;


//norm material:
$thematerial .= " AND normtype in ($mynormmaterial) ";
$simplestuff .= $thematerial;

//omit until Bai fixes his stuff//$simplestuff .= $thematerial;
// end Material for simplestuff


//mineralname
if($getsearch->mineralname!=""){
	//$themineralname=" AND mineralname='".$getsearch->mineralname."' ";
	////make case-insensitive for mineral name(by Peng)
	$themineralname=" AND mineralname ILIKE '".$getsearch->mineralname."%' ";
	$simplestuff .= $themineralname;
}
//end mineralname

////////////////////////// $thefields: a var we need for some queries ////////////////////////////////
// Fields in $field_array that must be non-null if this sample is to be displayed. For Advanced Output the field search_query.searchopt will contain a non-null value: exactopt, anyopt or alldata. searchopt will dictate whether all the fields in $fields_array must have data, at least one of those fields must have data, or no constraint should be applied at all. For Quick Display output there is no such constraint.  
if (count($fieldlist_array)>0) {
	$thefields = '';
	if ($getsearch->searchopt == 'exactopt') {
		foreach($fieldlist_array as $curritem) {
			if(in_array($curritem,$lepr_items_array)){
				$thefields .= " AND $curritem > -99 ";
			}else{
				$thefields .= " AND $curritem > -99 ";
			}
		}
		$thefields = substr($thefields,4); // remove the first conditional
		$thefields = " AND ( $thefields ) ";
		//$simplestuff .= $thefields; 
	} elseif ($getsearch->searchopt == 'anyopt') {
		foreach($fieldlist_array as $curritem) {
			if(in_array($curritem,$lepr_items_array)){
				$thefields .= " OR $curritem > -99 ";
			}else{
				$thefields .= " OR $curritem > -99 ";
			}
		}
		$thefields = substr($thefields,4); // remove the first conditional
		$thefields = " AND ( $thefields ) ";
		//$simplestuff .= $thefields; 
	} elseif ($getsearch->searchopt = 'alldata') { 
		// We will not limit samples by the data values for the checkbox'd fields, so we can omit this bit of sql
	}
}
// end fields to limit output

$dss=$db->get_results("select * from datasources where active='y' order by pkey");

// var countstring 
$countstring = "
SELECT    
";

foreach($dss as $ds){
	$countstring.="Sum(".$ds->name."count) as ".$ds->dbvar.",\n";
}


$countstring.="
Sum(totalcount) as tc
FROM
(SELECT
";

foreach($dss as $ds){
	//$countstring.="(CASE WHEN source = '".$ds->name."' THEN 1 ELSE 0 END) AS ".$ds->name."count,\n";
	$countstring.="(CASE WHEN sourceint = ".$ds->pkey." THEN 1 ELSE 0 END) AS ".$ds->name."count,\n";
}

$countstring.="
(CASE WHEN sourceint IS NOT NULL THEN 1 ELSE 0 END) AS totalcount
FROM
(
SELECT 	sourceint
FROM 	$matview
WHERE
		1=1
		$simplestuff 
		$srcwhere ) foo ) foob";
//echo "<p>in queries.php countstring is <p>$countstring<p>";
// end var countstring






// var advcount 
$advcount = " 
SELECT 	Count(*) count
FROM 	method meth,
		$methview chem,
		material mat,
		sampletypeb st,
		--citation_authors ca,
		citation cit,
		sample samp,
		lepr_norms lepr,
		coords crd
WHERE	meth.method_pkey = chem.method_pkey AND
		meth.material_pkey = mat.material_pkey AND
		mat.sampletype_pkey = st.sampletype_pkey AND
		st.sample_pkey = samp.sample_pkey AND
		--st.citation_pkey = ca.citation_pkey AND
		st.citation_pkey = cit.citation_pkehy AND
		samp.sample_pkey = crd.coord_pkey AND
		--samp.sample_pkey = lepr.sample_pkey
		mat.material_pkey = lepr.material_pkey
";

$advcount .= $theauthor;
$advcount .= $thedoi;
$advcount .= $theage;
$advcount .= $thesampleid;
$advcount .= $theigsn;
$advcount .= $thecruiseid;
$advcount .= $thetitle;
$advcount .= $thejournal;
$advcount .= $thekeyword; 
$advcount .= $thepubyear; 
$advcount .= $thepolygon; 
$advcount .= $chemstring; 
$advcount .= $therocknames; 
$advcount .= $thecoordinates; 
$advcount .= $thegeology;
$advcount .= $theoceanfeature;
$advcount .= $thevolcano;
$advcount .= $thematerial;
$advcount .= $themineralname;
$advcount .= $thelepr;
$advcount .= $srcwhere;
// end advcount
////




// var simplecount 
$simplecount = "
SELECT 	count(*) as count
FROM 	sample samp,
		location loc,
		coords crd
		--lepr_norms lepr
WHERE
		samp.sample_pkey = loc.sample_pkey AND
		loc.location_pkey = crd.location_pkey AND
		--samp.sample_pkey = lepr.sample_pkey
		
		$simplestuff 
		$srcwhere ";
// end simplecount
////echo "<p>simplecount<br>$simplecount";




// var itemstring
$itemstring = 
"
SELECT 	it.itemname as name
FROM 	item it,
		method meth,
		$methview chem,
		material mat,
		sampletypeb st,
		--citation_authors ca,
		citation cit,
		sample samp,
		lepr_norms lepr,
		coords crd
WHERE	it.itemname!='age' and it.itemname!='age_min' and it.itemname!='age_max' and
		it.method_pkey = meth.method_pkey AND
		meth.method_pkey = chem.method_pkey AND
		meth.material_pkey = mat.material_pkey AND
		mat.sampletype_pkey = st.sampletype_pkey AND
		st.sample_pkey = samp.sample_pkey AND
		--ca.citation_pkey = st.citation_pkey AND
		cit.citation_pkey = st.citation_pkey AND
		samp.sample_pkey = crd.coord_pkey AND
		--samp.sample_pkey = lepr.sample_pkey
		mat.material_pkey = lepr.material_pkey
";

$itemstring .= $thepolygon; 
$itemstring .= $chemstring; 
//$itemstring .= $thefields;
$itemstring .= $therocknames; 
$itemstring .= $theauthor;
$itemstring .= $thedoi;
$itemstring .= $theage;
$itemstring .= $thesampleid;
$itemstring .= $theigsn;
$itemstring .= $thecruiseid;
$itemstring .= $thetitle; 
$itemstring .= $thejournal; 
$itemstring .= $thepubyear; 
$itemstring .= $thekeyword; 
$itemstring .= $thematerial;
$itemstring .= $themineralname;
$itemstring .= $thecoordinates;
$itemstring .= $thegeology;
$itemstring .= $theoceanfeature;
$itemstring .= $thevolcano;
$itemstring .= $thelepr;
$itemstring .= $srcwhere;
$itemstring .= " group by name order by name";
////echo "<p>itemstring is <br>$itemstring";
// end itemstring




$itemstring="
SELECT 	it.itemname as name 	
FROM 	item it join $matview mat on it.material_pkey = mat.material_pkey
WHERE
		it.itemname!='age' and it.itemname!='age_min' and it.itemname!='age_max'
		$simplestuff
		$srcwhere
		group by name order by name
";


//echo "<pre>itemstring is $itemstring</pre>";

// var simpstring
$simpstring = "
SELECT 	samp.sample_pkey,
		samp.sample_id,
		samp.source,
		samp.url,
		samp.samplenumber as sample_num,
		sample_rockclass(samp.sample_pkey) as mainclasses,
		sample_rockname(samp.sample_pkey) as rockname,
		samp.age,
		samp.age_min,
		samp.age_max,
		
		crd.xval as longitude,
		crd.yval as latitude,
		crd.locdecimal
FROM 	sample samp,
		--lepr_norms lepr,
		location loc,
		coords crd
WHERE
		samp.sample_pkey =loc.sample_pkey AND
		--samp.sample_pkey = lepr.sample_pkey AND
		loc.location_pkey = crd.location_pkey
		   
		
		$simplestuff
		$srcwhere ";
////echo "<p>simpstring is<br>$simpstring"; echo "<p>itemstring is <br>$itemstring";
// end simpstring


// var simpstring
$mapstring = "
SELECT 	source,
		sample_id,
		sample_pkey as sample_pkey,
		longitude,
		latitude
FROM 	$matview
WHERE
		1=1
		$simplestuff 
		$srcwhere ";



$polarmapstring = "
SELECT 	source,
		sample_id,
		sample_pkey,
		longitude,
		latitude,
		ST_AsText(ST_Transform(ST_SetSRID(the_geom,4326),3031)) as polarcoords
FROM 	$matview
WHERE
		1=1
		$simplestuff 
		$srcwhere ";




$legendmapstring = "
SELECT 	sample_pkey
FROM 	$matview
WHERE	1=1
		$simplestuff 
		$srcwhere ";
		

$lepritemstring2 = "
SELECT 	distinct(itemname)
FROM 	lepr_pivot samp
WHERE	1=1
		$simplestuff 
		$srcwhere 
		order by itemname";




$lepritemstring = "
SELECT 	lep.itemname 	
FROM 	lepr_pivot lep join $matview mat on lep.sample_pkey = mat.sample_pkey
WHERE
		1=1
		$simplestuff
		$srcwhere
		group by itemname order by itemname
";



// var samplechemcount
$samplechemcount = "
SELECT 	count(*) as count
FROM 	sample samp,
		lepr_norms lepr,
		$matview chem,
		location loc,
		coords crd,
		material mat, 
		--citation_authors ca,
		citation cit
WHERE
		samp.sample_pkey =loc.sample_pkey AND
		--samp.sample_pkey = lepr.sample_pkey AND
		mat.material_pkey = lepr.material_pkey
		samp.sample_pkey = chem.sample_pkey AND
		loc.location_pkey = crd.location_pkey 
		";
if ($getsearch->author > '') {
	//$samplechemcount .= " AND samp.sample_pkey IN (SELECT sample_pkey FROM citation_relate WHERE citation_pkey IN (SELECT  cit.citation_pkey FROM citation cit, citation_authors ca WHERE cit.citation_pkey = ca.citation_pkey AND ca.vector @@ to_tsquery('".strtolower($authstring)."')))";
	$samplechemcount .= " AND samp.sample_pkey IN (SELECT sample_pkey FROM citation where authorlistvector @@ to_tsquery('".strtolower($authstring)."'))";
	
}



if ($getsearch->title > '') {
	$samplechemcount .= " AND samp.sample_pkey IN (SELECT sample_pkey FROM citation WHERE citation_pkey in (select citation_pkey from citation where titlevector @@ to_tsquery('".$titlestring."')))";
}

if ($getsearch->doi > '') {
	$samplechemcount .= " AND samp.sample_pkey IN (SELECT sample_pkey FROM citation WHERE citation_pkey in (select citation_pkey from citation where doi = '$doistring'))";
}



$samplechemcount .= $thejournal;
if ($getsearch->journal > '') {
	$samplechemcount .= " AND samp.sample_pkey IN (SELECT sample_pkey FROM citation WHERE journalvector @@ to_tsquery('".$journalstring."') )";
}
if ($getsearch->advkeyword1> '') {
	$samplechemcount .= " AND ( gendescvector @@ to_tsquery('".$keywordstring."') ) ";
}
if ($getsearch->yearmin != '' && $getsearch->yearmax != '') {
	$samplechemcount .= " AND samp.sample_pkey in (SELECT sample_pkey from citation WHERE citation_pkey in (SELECT citation_pkey FROM citation where pubyear >= $getsearch->yearmin and pubyear <= $getsearch->yearmax ) ) ";
}

if ($getsearch->chemistry > '') {
	$samplechemcount .= " AND samp.sample_pkey in ( select sample_pkey from $methview where ( ".$chemstring." ) ";
}
$samplechemcount .= $thefields;
if ($therocknames > '') {
	$samplechemcount .= " AND samp.sample_pkey IN (select sample_pkey from sampletypeb st where 1=1 $therocknames ) ";
}
$samplechemcount .= $thecoordinates;
$samplechemcount .= $thegeology;
$samplechemcount .= $theoceanfeature;
$samplechemcount .= $thevolcano;
$samplechemcount .= $thematerial;
$samplechemcount .= $themineralname;
$samplechemcount .= $thelepr;
$samplechemcount .= $srcwhere;
//dev ////echo "<p>samplechemcount <br>$samplechemcount "; echo "<p>simpstring is<br>$simpstring"; echo "<p>itemstring is <br>$itemstring";
// end var samplechemcount 


//getauthor(st.citation_pkey) author,


// var advstring
$advstring = "
SELECT
samp.expeditionid as cruiseid,
samp.cruiseurl,
samp.sample_pkey,
meth.method_pkey,
cit.authorlist as author,
(SELECT trim(title) FROM citation WHERE citation_pkey=st.citation_pkey) as title,
(SELECT trim(journal) FROM citation WHERE citation_pkey=st.citation_pkey) as journal,
(SELECT doi FROM citation WHERE citation_pkey=st.citation_pkey) as doi,
st.citation_pkey,
samp.sample_id,
samp.source,
samp.url,
samp.samplenumber as sample_num,
(SELECT pubyear FROM citation WHERE citation_pkey=st.citation_pkey) as year,
st.class1 as ec_level_1,
st.class2 as ec_level_2,
st.class3 as ec_level_3,
st.class4 as ec_level_4,
meth.NAME method,
		samp.age,
		samp.age_min,
		samp.age_max,
";

if(count($fieldlist_array)>0){
	foreach($fieldlist_array AS $curritem) {
		if(in_array($curritem,$lepr_items_array)){
			$advstring .= " , lepr.$curritem ";
		}else{
			$advstring .= " , chem.$curritem ";
		}
	}
}


$advstring .= " , 
		crd.xval longitude,
		crd.yval latitude,
		crd.locdecimal
FROM 	method meth,
		$methview chem,
		material mat,
		sampletypeb st,
		--citation_authors ca,
		citation cit,
		sample samp,
		lepr_norms lepr,
		coords crd
WHERE	meth.method_pkey = chem.method_pkey AND
		meth.material_pkey = mat.material_pkey AND
		mat.sampletype_pkey = st.sampletype_pkey AND
		st.sample_pkey = samp.sample_pkey AND
		--ca.citation_pkey = st.citation_pkey AND
		cit.citation_pkey = st.citation_pkey AND
		samp.sample_pkey = crd.coord_pkey AND
		--samp.sample_pkey = lepr.sample_pkey
		mat.material_pkey = lepr.material_pkey
";
$advstring .= $theauthor;
$advstring .= $thedoi;
$advstring .= $theage;
$advstring .= $thesampleid;
$advstring .= $theigsn;
$advstring .= $thecruiseid;
$advstring .= $thekeyword;
$advstring .= $thepolygon;
$advstring .= $chemstring;
$advstring .= $thefields;

$advstring .= $therocknames;
$advstring .= $thepubyear;
$advstring .= $thecoordinates;
$advstring .= $thegeology;
$advstring .= $theoceanfeature;
$advstring .= $thevolcano;
$advstring .= $thejournal;
$advstring .= $thetitle;
$advstring .= $thematerial;
$advstring .= $themineralname;
$advstring .= $thelepr;
$advstring .= $srcwhere;
//dev ////echo "<p>advstring <br>$advstring ";
// end var advstring













/*
$refstring="SELECT 	DISTINCT(title||journal||pubyear),
		title,
		journal,
		pubyear,
		authorlist as author
FROM material_level_metadata where 1=1 $simplestuff $srcwhere";
*/

$refstring="
			select citation_pkey, title, journal, pubyear, authors as author, citation_url
			from citation where citation_pkey in (
				select citation_pkey from vmatnormal where 1=1 $simplestuff $srcwhere
			) order by authors
";
















$methunits="";
if(count($fieldlist_array)>0){
	foreach($fieldlist_array as $curritem) {
		$methunits .= "mu.".$curritem."_meth,\n";
		$methunits .= "mu.".$curritem."_unit,\n";
	} // foreach
}




$samplechemstring = "select
					$methunits
					mv.*
					from $matview mv,
					material_level_methunits mu
					where mv.material_pkey = mu.material_pkey 
";

// the chemical fields the user checked (some or all must have data)
if ($getsearch->searchopt == 'exactopt') {
	$delim="AND";
} elseif ($getsearch->searchopt == 'anyopt') {
	$delim="OR";
} else { // something is wrong, we should have this value 
	$delim="OR";
}
$fieldstring = "";


if(count($fieldlist_array)>0){
	foreach($fieldlist_array as $curritem) {
		if(in_array($curritem,$lepr_items_array)){
			$fieldstring .= " $delim $curritem > -99 "; //#delim#(schem.#curritem# > -99)
		}else{
			$fieldstring .= " $delim $curritem > -99 "; //#delim#(schem.#curritem# > -99)
		}
		
	} // foreach
}


if (strlen($fieldstring)>0) {
	$fieldstring = substr($fieldstring,4); // remove the leading AND or OR 
	$fieldstring = " AND ( $fieldstring ) ";
	$samplechemstring .= $fieldstring;
}

$samplechemstring .= " $simplestuff $srcwhere ";


//Peng added on Mar 5 2024
$unitsaggstring="";
if(count($fieldlist_array)>0){
	foreach($fieldlist_array as $curritem) {
        	$unitsaggstring .= "array_to_string(array_agg(distinct mu.".$curritem."_unit),',','*') as ".$curritem." ,\n";
        }
        $unitsaggstring = substr_replace($unitsaggstring, "", -2);
}

$samplechemaggstring = "select $unitsaggstring from $matview mv, material_level_methunits mu where mv.material_pkey = mu.material_pkey";
$samplechemaggstring .= " $simplestuff $srcwhere ";








$advstring="
	select * from $methview
	where 1 = 1
";

// Fields to show, for advstring. If user has selected Advanced Output the field search_query.searchopt will contain a non-null value. For Quick Display output search_query.searchopt should be null.
$thefields = '';
if (count($fieldlist_array)>0) {
	if ($getsearch->searchopt == 'exactopt') {
		foreach($fieldlist_array as $curritem) {
			if(in_array($curritem,$lepr_items_array)){
				$thefields .= " AND $curritem > -99 ";
			}else{
				$thefields .= " AND $curritem > -99 ";
			}
		} // foreach
		$thefields = substr($thefields,4); // remove the first conditional
		$thefields = " AND ( $thefields ) ";
		$advstring .= $thefields; 
	} elseif ($getsearch->searchopt == 'anyopt') {
		foreach($fieldlist_array as $curritem) {
			if(in_array($curritem,$lepr_items_array)){
				$thefields .= " OR $curritem > -99 ";
			}else{
				$thefields .= " OR $curritem > -99 ";
			}
		} // foreach
		$thefields = substr($thefields,4); // remove the first conditional
		$thefields = " AND ( $thefields ) ";
		$advstring .= $thefields; 
	} elseif ($getsearch->searchopt = 'alldata') { 
		// We will not limit samples by the data values for the checkbox'd fields, so we can omit this bit of sql
	} // if	
} // if

$advstring.="
	$simplestuff
	$srcwhere
";
























$advcount = "select count(*) as totalcount from $methview
			where 1=1 
			";

// Fields to show, for advcount. If user has selected Advanced Output the field search_query.searchopt will contain a non-null value. For Quick Display output search_query.searchopt should be null.
$thefields = '';
if (count($fieldlist_array)>0) {
	if ($getsearch->searchopt == 'exactopt') {
		foreach($fieldlist_array as $curritem) {
			if(in_array($curritem,$lepr_items_array)){
				$thefields .= " AND $curritem > -99 \n";
			}else{
				$thefields .= " AND $curritem > -99 \n";
			}
		} // foreach
		$thefields = substr($thefields,4); // remove the first conditional
		$thefields = " AND ( $thefields ) ";
		$advcount .= $thefields; 
	} elseif ($getsearch->searchopt == 'anyopt') {
		foreach($fieldlist_array as $curritem) {
			if(in_array($curritem,$lepr_items_array)){
				$thefields .= " OR $curritem > -99 \n";
			}else{
				$thefields .= " OR $curritem > -99 \n";
			}
		} // foreach
		$thefields = substr($thefields,4); // remove the first conditional
		$thefields = " AND ( $thefields ) ";
		$advcount .= $thefields; 
	} elseif ($getsearch->searchopt = 'alldata') { 
		// We will not limit samples by the data values for the checkbox'd fields, so we can omit this bit of sql
	} // if	
} // if

$advcount.="$simplestuff $srcwhere";




$samplecount = "select count(*) as totalcount from $matview
			where 1=1 
			";

// Fields to show, for advcount. If user has selected Advanced Output the field search_query.searchopt will contain a non-null value. For Quick Display output search_query.searchopt should be null.
$thefields = '';
if (count($fieldlist_array)>0) {
	if ($getsearch->searchopt == 'exactopt') {
		foreach($fieldlist_array as $curritem) {
			if(in_array($curritem,$lepr_items_array)){
				$thefields .= " AND $curritem > -99 \n";
			}else{
				$thefields .= " AND $curritem > -99 \n";
			}
		} // foreach
		$thefields = substr($thefields,4); // remove the first conditional
		$thefields = " AND ( $thefields ) ";
		$advcount .= $thefields; 
	} elseif ($getsearch->searchopt == 'anyopt') {
		foreach($fieldlist_array as $curritem) {
			if(in_array($curritem,$lepr_items_array)){
				$thefields .= " OR $curritem > -99 \n";
			}else{
				$thefields .= " OR $curritem > -99 \n";
			}
		} // foreach
		$thefields = substr($thefields,4); // remove the first conditional
		$thefields = " AND ( $thefields ) ";
		$samplecount .= $thefields; 
	} elseif ($getsearch->searchopt = 'alldata') { 
		// We will not limit samples by the data values for the checkbox'd fields, so we can omit this bit of sql
	} // if	
} // if

$samplecount.="$simplestuff $srcwhere";















$tasstring = "
select
sample_pkey,
sio2,
na2o,
k2o,
url
from $matview
where 1=1
$simplestuff
$srcwhere
";




// This sql is a copy of graphstring in querytest.cfm, but with only 3 chemical columns - sio2, na2o, k2o - returned instead of 9. It is for the TAS graph. EEJones --->

$tas_sql = "
select
sample_pkey,
sio2,
na2o,
k2o,
url
from $matview
where 1=1
$simplestuff
$srcwhere
";


$tas_hist_sql = "
select
sio2,
na2o+k2o as alkali
from $matview
where 1=1
$simplestuff
$srcwhere
";

$tas_hist_sql="
			
		select sio2, alkali from (
			$tas_hist_sql
		)foo
        where
        sio2 >= 36 and
        sio2 <= 84 and
        alkali >= 0 and
        alkali <= 18

	";












// Chemistry for simplestuff
$taschemstring = '';
if ($getsearch->chemistry != '') {  
	$delim=''; 
	$chemistry_array=split(',',$getsearch->chemistry); 
	foreach($chemistry_array as $currchem) {  

		$chemname='';  
		if (strpos($currchem,':EXISTS') > 0) {  
			$chemname = substr($currchem,0,strpos($currchem,':EXISTS'));  
			$taschemstring .= " $delim ( chem.$chemname > -99 ) ";  
			$delim = " AND ";
		} elseif (substr_count($currchem,':') > 1 ) {  
			$currchem_array=split(':',$currchem);  
			$i=strpos($chemname,':'); 
			$lowerbound=$currchem_array[0];
			$upperbound=$currchem_array[2];
			$chemname=$currchem_array[1];
			$taschemstring .= " $delim ( chem.$chemname > $lowerbound AND chem.$chemname < $upperbound ) ";
			$delim = " AND ";
		
		
		} // if      
		$methstring='';  
		$methdelim='';  
		$chemmethods_array=split(',',$getsearch->chemmethods); 
		foreach($chemmethods_array as $currmeth) {  
			if (strpos($currmeth,'$chemname:') == 0 ) { 
				$loopflag=false;  
				$currmeth_array=split(':',$currmeth);
				foreach($currmeth_array as $mycurr) {  
					if ($loopflag) {      
						$currmethlist=explode(";",$mycurr);
						foreach($currmethlist as $methname){ //needed to add this loop here because code wasn't digging deep enough - JMA
							$methstring .= " $methdelim chem.method = '$methname' "; 
							$methdelim = " OR ";      
						}
					} // if      
					$loopflag=true;      
				} // foreach     
			} // if     
		} // foreach         
		if ($methstring > '') {      
			$taschemstring .= " AND ( $methstring ) ";      
		} // if     
		$delim = " $chemswitch ";            
	} // foreach chemistry_array      
	//$taschemstring = " AND mat.material_pkey in ( select material_pkey from $matview where $taschemstring ) ";
	$taschemstring = " AND $taschemstring ";
} // if 



// redo foofoo_sql 
$foofoo_sql = "
SELECT 	samp.sample_pkey,
		chem.sio2 AS sio2,
		chem.na2o AS na2o,
		chem.k2o AS k2o,
		samp.url 
FROM 	method meth,
		$methview chem,
		material mat,
		sampletypeb st,
		--citation_authors ca,
		citation cit,
		sample samp,
		lepr_norms lepr,
		coords crd
WHERE	meth.method_pkey = chem.method_pkey AND
		meth.material_pkey = mat.material_pkey AND
		mat.sampletype_pkey = st.sampletype_pkey AND
		st.sample_pkey = samp.sample_pkey AND
		--ca.citation_pkey = st.citation_pkey AND
		cit.citation_pkey = st.citation_pkey AND
		samp.sample_pkey = crd.coord_pkey AND
		--samp.sample_pkey = lepr.sample_pkey
		mat.material_pkey = lepr.material_pkey
		";
if ($getsearch->author > '') { //<cfif getsearch.author neq "">
	//$foofoo_sql .= " AND contains(ca.authors, '%$getsearch->author%',4)>0 ";
	$foofoo_sql .= " AND cit.authorlist_vector @@ to_tsquery('$authstring')";
} //</cfif>



// age here
$foofoo_sql .= $theage;
$foofoo_sql .= $thesampleid;
$foofoo_sql .= $theigsn;
$foofoo_sql .= $thecruiseid;

if ($getsearch->title > '') { //<cfif getsearch.title neq "">
	//$foofoo_sql .= " AND st.citation_pkey in (select citation_pkey from citation where contains(title,'%$getsearch->title%',6)>0) ";
	$foofoo_sql .= " AND cit.titlevector @@ to_tsquery('$titlestring') ";
} //</cfif>

if ($getsearch->doi > '') {
	$foofoo_sql .= " AND cit.doi = '$doistring' ";
} //</cfif>



if ($getsearch->journal > '') { //<cfif getsearch.title neq "">
	//$foofoo_sql .= " AND st.citation_pkey in (select citation_pkey from citation where contains(title,'%$getsearch->title%',6)>0) ";
	$foofoo_sql .= " AND cit.journalvector @@ to_tsquery('$journalstring') ";
} //</cfif>

if ($getsearch->yearmin > 0 && $getsearch->yearmax > 0) {
	$foofoo_sql .= " AND cit.pubyear >= $getsearch->yearmin AND cit.pubyear <= $getsearch->yearmax ";
}	
if ($getsearch->advkeyword1 > '') { 
		//$foofoo_sql .= " AND (contains(samp.genericdescriptor, '%$getsearch->advkeyword1%', 3) > 0 ) ";
		$foofoo_sql .= " AND ( gendescvector @@ to_tsquery('".$keywordstring."') ) ";
}









$foofoo_sql .= $taschemstring; 








// Fields to show, for foofoo_sql. If user has selected Advanced Output the field search_query.searchopt will contain a non-null value. For Quick Display output search_query.searchopt should be null.
$thefields = '';
if (count($fieldlist_array)>0) {
	if ($getsearch->searchopt == 'exactopt') {
		foreach($fieldlist_array as $curritem) {
			if(in_array($curritem,$lepr_items_array)){
				$thefields .= " AND lepr.$curritem > -99 ";
			}else{
				$thefields .= " AND chem.$curritem > -99 ";
			}
		} // foreach
		$thefields = substr($thefields,4); // remove the first conditional
		$thefields = " AND ( $thefields ) ";
		$foofoo_sql .= $thefields; 
	} elseif ($getsearch->searchopt == 'anyopt') {
		foreach($fieldlist_array as $curritem) {
			if(in_array($curritem,$lepr_items_array)){
				$thefields .= " OR lepr.$curritem > -99 ";
			}else{
				$thefields .= " OR chem.$curritem > -99 ";
			}
		} // foreach
		$thefields = substr($thefields,4); // remove the first conditional
		$thefields = " AND ( $thefields ) ";
		$foofoo_sql .= $thefields; 
	} elseif ($getsearch->searchopt = 'alldata') { 
		// We will not limit samples by the data values for the checkbox'd fields, so we can omit this bit of sql
	} // if	
} // if
		
$foofoo_sql .= $therocknames; 

/*
if ($getsearch->material == 'rock') { //	<cfif SearchQuery.material eq "rock">
	$foofoo_sql .= " AND mat.materialtype = 'whole rock' ";
} else { // <cfelse>
	$foofoo_sql .= " AND mat.materialtype = 'mineral' ";
} // </cfif>
*/

//norm material
$foofoo_sql .= " AND mat.normtype in ($mynormmaterial) ";

if($getsearch->mineralname != ""){
//	$foofoo_sql .= " AND mat.mineralname='".$getsearch->mineralname."' ";
//make case-insensitive for mineral name(by Peng)
	$foofoo_sql .= " AND mat.mineralname ILIKE '".$getsearch->mineralname."%' ";
}

$foofoo_sql .= $thecoordinates;

$foofoo_sql .= $thegeology;
$foofoo_sql .= $theoceanfeature;
$foofoo_sql .= $thevolcano;

$foofoo_sql .= $thelepr;

$foofoo_sql .= $srcwhere; //#srcwhere#

























































$tas_ani_sql = "SELECT samp.age, feo.n_feo_sio2 AS sio2, feo.n_feo_na2o + feo.n_feo_k2o AS alkali,     samp.url FROM sample samp, chemistry feo WHERE samp.sample_pkey = feo.sample_pkey $simplestuff $srcwhere ";
$tas_ani_sql .= "$tasnamestring";
$tas_ani_sql .= " AND samp.age IS NOT NULL AND n_feo_sio2 BETWEEN 36 AND 84 AND (n_feo_na2o+n_feo_k2o) BETWEEN 0 AND 18 ORDER BY samp.age, sio2, alkali
";





































$harker_sql = "
SELECT 	url,sample_pkey,
		n_feo_sio2 AS sio2,
		n_feo_al2o3 AS al2o3,
		n_feo_mgo AS mgo,
		n_feo_tio2 AS tio2,
		n_feo_na2o AS na2o,
		n_feo_cao AS cao,
		n_feo_k2o AS k2o,
		n_feo_p2o5 AS p2o5,
		n_feo_feot AS feot
FROM 	vmatreported
WHERE
		1=1
		$simplestuff
		$srcwhere 
		AND
		n_feo_sio2  BETWEEN 0 AND 100 AND
		n_feo_al2o3  BETWEEN 0 AND 100 AND
		n_feo_mgo  BETWEEN 0 AND 100 AND
		n_feo_tio2  BETWEEN 0 AND 100 AND
		n_feo_na2o  BETWEEN 0 AND 100 AND
		n_feo_cao  BETWEEN 0 AND 100 AND
		n_feo_k2o  BETWEEN 0 AND 100 AND
		n_feo_p2o5  BETWEEN 0 AND 100 AND
		n_feo_feot  BETWEEN 0 AND 100 
		group by sample_pkey,url,n_feo_sio2,n_feo_al2o3,n_feo_mgo,
		n_feo_tio2,n_feo_na2o,n_feo_cao,n_feo_k2o,n_feo_p2o5,n_feo_feot;
		";







// for the Harker animations
$harker_ani_sql = "
SELECT 	samp.age, feo.n_feo_sio2 AS sio2,
		feo.n_feo_al2o3 AS al2o3,
		feo.n_feo_mgo AS mgo,
		feo.n_feo_tio2 AS tio2,
		feo.n_feo_na2o AS na2o,
		feo.n_feo_cao AS cao,
		feo.n_feo_k2o AS k2o,
		feo.n_feo_p2o5 AS p2o5,
		feo.n_feo_feot AS feot
FROM 	sample samp,
		lepr_norms lepr,
		location loc,
		material_feo_norm feo,
		coords crd
WHERE
		samp.sample_pkey =loc.sample_pkey AND
		samp.sample_pkey = lepr.sample_pkey AND
		samp.sample_pkey = feo.sample_pkey AND
		loc.location_pkey = crd.location_pkey

		$simplestuff
		$srcwhere 

		AND

		feo.n_feo_sio2  BETWEEN 0 AND 100 AND
		feo.n_feo_al2o3  BETWEEN 0 AND 100 AND
		feo.n_feo_mgo  BETWEEN 0 AND 100 AND
		feo.n_feo_tio2  BETWEEN 0 AND 100 AND
		feo.n_feo_na2o  BETWEEN 0 AND 100 AND
		feo.n_feo_cao  BETWEEN 0 AND 100 AND
		feo.n_feo_k2o  BETWEEN 0 AND 100 AND
		feo.n_feo_p2o5  BETWEEN 0 AND 100 AND
		feo.n_feo_feot  BETWEEN 0 AND 100 AND 
		samp.age IS NOT NULL
		$srcwhere ";
// end harker__ani_sql
//echo $harker_ani_sql;

//echo nl2br($countstring);

$newsimpstring .= "SELECT * from $matview
					where 1=1
					$simplestuff
					$srcwhere
					order by material_pkey
					
				";
?>
