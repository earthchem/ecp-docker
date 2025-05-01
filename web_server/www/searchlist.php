<?PHP
/**
 * searchlist.php
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


$srchrow=$db->get_row("select * from search_query where pkey=$pkey");

if($srchrow->queryname!="Untitled"&&$srchrow->queryname!=""){$srchlist["Saved Query Name"]=$srchrow->queryname;}
if($srchrow->yearmin!=""){$srchlist["Year Min"]=$srchrow->yearmin;}
if($srchrow->yearmax!=""){$srchlist["Year Max"]=$srchrow->yearmax;}
if($srchrow->georef!=""){$srchlist["Georef"]=$srchrow->georef;}
if($srchrow->author!=""){$srchlist["Author"]=$srchrow->author;}



$constraints_set = false;
if ($srchrow->chemistry > '') { 
	// Make an array of the methods. Each array element will be something like item:method;method;method 
	if ($srchrow->chemmethods > '') {
		$chemmethods_array=split(',',$srchrow->chemmethods); 
	} else {
		$chemmethods_array=array();
	}
	//$chemmethods_array=sort($chemmethods_array);
	$chemistry_array=split(',', $srchrow->chemistry);
	//$chemistry_array=sort($chemistry_array);
	foreach($chemistry_array as $currchem) { //echo '<br><font color=red>'.$currchem.'</font>';
		// display the chemical and constraint, and get the chemical name 
		$chemstring = ''; // This is the way we will display the chemical and constraints 
		if (strpos($currchem,'EXISTS')>0) { // pattern is like SIO2:exists
			$currchem_array=split(':',$currchem); // split chemical name and 'EXISTS'
			$chemname=$currchem_array[0];
			$chemstring=''.$currchem_array[0].''.' '.$currchem_array[1]; // This should be like SIO2 EXISTS
			$constraints_set = true;
		} elseif (strpos($currchem,':')>0) { // pattern is like 10:SIO2:75
			$currchem_array=split(':',$currchem); // split at the : into lowerbound, chemical name, upperbound 
			$i=strpos($chemname,':'); 
			$lowerbound=$currchem_array[0];
			$upperbound=$currchem_array[2];
			$chemname=$currchem_array[1];
			$chemstring = $lowerbound.' < '.$chemname.' < '.$upperbound; // This should be like 10 < SIO2 < 75 
			$constraints_set = true;
		}
		if ($chemstring > '') {
			//echo $chemstring;
			$srchlist["Chemistry"].=$chemdelim.$chemstring;
			$chemdelim=", ";
		} else {
			//echo $currchem;
			$srchlist["Chemistry"].=$chemdelim.$currchem;
			$chemdelim=", ";
		} 
		//If this chemical has a method, show it 
		for ($i=0;$i<sizeof($chemmethods_array);$i++) { 
			$l = strlen($chemname); 
			if (substr($chemmethods_array[$i],0,$l) == $chemname) {
				$srchlist["Chemistry"].= ' (';
				$srchlist["Chemistry"].= substr($chemmethods_array[$i],$l+1);
				$srchlist["Chemistry"].= ') '; 
				//unset($chemmethods_array[$i]);
				//break;
			} // if
		} // for
	} // foreach
} // if



if($srchrow->longitudeeast!=""){$srchlist["Longitude East"]=$srchrow->longitudeeast;}
if($srchrow->longitudewest!=""){$srchlist["Longitude West"]=$srchrow->longitudewest;}
if($srchrow->latitudenorth!=""){$srchlist["Latitude North"]=$srchrow->latitudenorth;}
if($srchrow->latitudesouth!=""){$srchlist["Latitude South"]=$srchrow->latitudesouth;}
//if($srchrow->chemmethods!=""){$srchlist["Chemistry Methods"]=$srchrow->chemmethods;}
if($srchrow->rockmode!=""){$srchlist["Rock Mode"]=$srchrow->rockmode;}
if($srchrow->title!=""){$srchlist["Title"]=$srchrow->title;}
if($srchrow->journal!=""){$srchlist["Journal"]=$srchrow->journal;}
if($srchrow->rocktype!=""){$srchlist["Rock Type"]=$srchrow->rocktype;}
if($srchrow->level1!=""){$srchlist["Material"]=$srchrow->level1;}
if($srchrow->level2!=""){$srchlist["Type"]=$srchrow->level2;}
if($srchrow->level3!=""){$srchlist["Composition"]=$srchrow->level3;}
if($srchrow->level4!=""){$srchlist["Rock Name"]=$srchrow->level4;}
//if($srchrow->material!=""){$srchlist["Material Type"]=$srchrow->material;}
if($srchrow->advkeyword1!=""){$srchlist["Keyword"]=$srchrow->advkeyword1;}
if($srchrow->tasname!=""){$srchlist["TAS Name"]=$srchrow->tasname;}
if($srchrow->polygon!=""){$srchlist["Polygon"]=$srchrow->polygon;}
if($srchrow->coordinates!=""){$srchlist["Coordinates"]=$srchrow->coordinates;}
if($srchrow->rockname!=""){$srchlist["RockName"]=$srchrow->rockname;}
if($srchrow->rockclass!=""){$srchlist["Rock Class"]=$srchrow->rockclass;}
if($srchrow->lepr!=""){$srchlist["LEPR"]=$srchrow->lepr;}
//if($srchrow->lepr_items!=""){$srchlist["LEPR Items"]=$srchrow->lepr_items;}
if($srchrow->mineralname!=""){$srchlist["Mineral Name"]=$srchrow->mineralname;}
if($srchrow->age!=""){$srchlist["Age"]=$srchrow->age;}
if($srchrow->age_min!=""){$srchlist["Min Age"]=$srchrow->age_min;}
if($srchrow->age_max!=""){$srchlist["Max Age"]=$srchrow->age_max;}


if($srchrow->geoage!=""){

	$thisagerow=$db->get_row("select * from geoages where pkey=".$srchrow->geoage);
	
	$srchlist["Geological Age"]=$thisagerow->agename." ".$thisagerow->agelabel;

}


if($srchrow->rocknamecart!=""){$srchlist["Rock Name Cart"]=$srchrow->rocknamecart;}
if($srchrow->sampleid!=""){$srchlist["Sample ID"]=$srchrow->sampleid;}
if($srchrow->ech!=""){$srchlist["EarthChem Hierarchy"]=$srchrow->ech;}

if($srchrow->geology!=""){
	$geonum=$srchrow->geology;
	$showgeologys = $db->get_results("select name from shapegeology where gid in ($geonum)");
	foreach($showgeologys as $geologyname){
		$srchlist["Geological Province"].=$showgeologydelim.$geologyname->name;
		$showgeologydelim="; ";
	}
}


if($srchrow->volcano!=""){

	$volcanonum=$srchrow->volcano;
	$showvolcanos = $db->get_results("select name from shapevolcano where gid in ($volcanonum)");
	
	foreach($showvolcanos as $volcname){
		$srchlist["Volcano Name"].=$volcdelim.utf8_decode($volcname->name);
		$volcdelim="; ";
	}

}


if($srchrow->doi!=""){$srchlist["DOI"]=$srchrow->doi;}



if($srchrow->oceanfeature!=""){

	$geonum=$srchrow->oceanfeature;
	$showoceanfeatures = $db->get_results("select name from oceanfeatures where gid in ($geonum)");
	foreach($showoceanfeatures as $oceanfeaturename){
		$srchlist["Ocean Feature"].=$showoceanfeaturedelim.$oceanfeaturename->name;
		$showoceanfeaturedelim="; ";
	}

}










?>