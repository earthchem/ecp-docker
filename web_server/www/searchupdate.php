<?PHP
/**
 * searchupdate.php
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

require_once("db.php");

// This file is included in search.php 
// Trim all the form variables. Easiest to trim them all here, though not all will need it.
foreach($_POST as $key=>$val) {
	//echo "$key:$val<br>";
	if(!is_array($val)){
		$POST[$key]=trim($val);
	}
}
// This file - searchupdate.php - is included in search.php. It checks for form variables (search parameters), process the ones that need processing, and if form variables passed, updates the record in the search_query table. 

//let's look for the age stuff here and clean up the values
if($_POST['age_min']!="" or $_POST['age_max']!="" or $_POST['age']!="" ){
	//first, clear out all age stuff from db
	
	$db->query("update search_query set geoage=null, age=null, age_min=null, age_max=null  where pkey=$pkey");
	
	if($_POST['age_min']!="" or $_POST['age_max']!=""){
		$_POST['age']="";
		$_POST['geoage']="";
	}else{
		$_POST['age_min']="";
		$_POST['age_max']="";
		$_POST['geoage']="";
	}
}else{
	$_POST['age']="";
	$_POST['age_min']="";
	$_POST['age_max']="";
}


//let's get the lepr values done here....

$leprlist="";
$leprdelim="";
$changelepr="no";

$lepr_field_array=array('albite','anorthite','apatite','calciumorthosilicate','corundum','diopside','hypersthene','kaliophilite','leucite','nepheline','olivine','orthoclase','perofskite','potassiummetasilicate','quartz','rutile','sodiummetasilicate','titanite','wollastonite');

foreach($lepr_field_array as $currleprname){

	$checkvar=$currleprname."_check";
	$gtvar=$currleprname."_gt";
	$ltvar=$currleprname."_lt";

	if(isset($_POST[$checkvar])){
		
		$leprlist=$leprlist.$leprdelim."$currleprname:checked";
		$leprdelim=";";
		$changelepr="yes";
	
	}elseif($_POST[$gtvar]!='' and $_POST[$ltvar]!=''){
	
		$leprlist=$leprlist.$leprdelim.$_POST[$ltvar].":".$currleprname.":".$_POST[$gtvar];
		$leprdelim=";";
		$changelepr="yes";
	
	}

}

if($changelepr=="yes"){
	$db->query("update search_query set lepr='$leprlist' where pkey=$pkey");
}


$latitudes=array();$longitudes=array(); // Build arrays of latitudes and longitudes. If there is form data for location, these will be populated

// Lat/long coordinates from Polygon Map (we need 3 or more coordinates) or from the Bounding Box Map (we need exactly 2 coordinates) are passed in as lat1, long1, lat2, long2, etc.
for($i=0;$i<25;$i++) { // There are at max 25 coordinates from the Polygon Map 
	$latvarname='lat'.$i;
	$longvarname='long'.$i; 
	if (isset($_POST[$latvarname]) & isset($_POST[$longvarname]) and is_numeric($_POST[$latvarname]) & is_numeric($_POST[$longvarname]) ) { 
		$latitudes[]=$_POST[$latvarname]; $longitudes[]=$_POST[$longvarname]; 
	} 
}
// Lat/long coordinates from the textarea field. These must be formatted like Polygon: space-separated lat/long pairs, delimited by ; . Add code to validate this Eileen.
if (isset($_POST['latitude_longitude_data'])) { 
	$coordinates_array=split(';',$_POST['latitude_longitude_data']); 
	foreach($coordinates_array as $key=>$val) { 
		$val = trim($val); 
		if ($val>'') { 
			while (strpos(' ',$val)>1) {$val=str_replace('  ',' ',$val);} 
			$latlong_array=split(' ',$val);
			$lat=trim($latlong_array[1]);$long=trim($latlong_array[0]);
			if (is_numeric($lat) and is_numeric($long)) {
				$latitudes[]=$lat;$longitudes[]=$long;
			}
		}
	}
} 
// Lat/long coordinates from the Bounding Box defined in text input fields 
if (isset($_POST['latitudenorth']) and isset($_POST['latitudesouth']) and isset($_POST['longitudeeast']) and isset($_POST['longitudewest']) and
	$_POST['latitudenorth']<=90  and $_POST['latitudenorth']>=-90  and 
	$_POST['latitudesouth']<=90  and $_POST['latitudesouth']>=-90  and 
	$_POST['longitudeeast'] <=180 and $_POST['longitudeeast'] >=-180 and 
	$_POST['longitudewest'] <=180 and $_POST['longitudewest'] >=-180
) { 
	
	
	
	/* this is wrong
	$latitudes=array();$longitudes=array();
	$latitudes[0]=$_POST['latitudenorth'];
	$latitudes[1]=$_POST['latitudesouth'];
	$longitudes[0]=$_POST['longitudeeast'];
	$longitudes[1]=$_POST['longitudewest'];
	*/
	
	$north=$_POST['latitudenorth'];
	$south=$_POST['latitudesouth'];
	$west=$_POST['longitudewest'];
	$east=$_POST['longitudeeast'];
	
	//$latitudes[]=$north;
	//$longitudes[]=$west;
	//$latitudes[]=$north;
	//$longitudes[]=$east;
	//$latitudes[]=$south;
	//$longitudes[]=$east;
	//$latitudes[]=$south;
	//$longitudes[]=$west;

	
	unset($_POST['latitudenorth']);
	unset($_POST['latitudesouth']);
	unset($_POST['longitudewest']);
	unset($_POST['longitudeeast']);
	
	$_POST['polygon']="$west $north; $east $north; $east $south; $west $south";
	
	//echo "here"; exit();
	
} 
// Now evaluate any lat/long coordinates. Store them in search_query record as either 'polygon' or 'latitudenorth' 'latitudesouth' 'longitudeeast' longitudewest'
if (sizeof($latitudes)==2) { // Bounding Box requires exactly 2 coordinates. BB coordinates are stored as latitudenorth, latitudesouth, etc. 
	

	
	// Be sure the coordinates are in the correct order WRONG! JMA - 1-3-2009
	/*
	if($latitudes[0] > $latitudes[1]){
		$_POST['latitudenorth']=$latitudes[0];
		$_POST['latitudesouth']=$latitudes[1];
	} else {
		$_POST['latitudenorth']=$latitudes[1];
		$_POST['latitudesouth']=$latitudes[0];
	}
	if($longitudes[0] > $longitudes[1]){
		$_POST['longitudeeast']=$longitudes[0];
		$_POST['longitudewest']=$longitudes[1];
	} else {
		$_POST['longitudeeast']=$longitudes[1];
		$_POST['longitudewest']=$longitudes[0];
	}
	*/
	//$_POST['polygon']=''; // If the Bounding Box is defined, the Polygon must be blank
	
	//echo "here";exit();
	
	$_POST['polygon']="$west $north; $east $north; $east $south; $west $south";
	
	
} elseif (sizeof($latitudes)>=3) { // Polygon requires 3 or more coordinates. Polygon coordinates are stored as space-delimited pairs, the pairs delimited by ;
	$polygon='';
	for ($i=0;$i<sizeof($latitudes);$i++) {
		if($longitudes[$i] < -180){$longitudes[$i]=$longitudes[$i]+360;}
		if($longitudes[$i] > 180 ){$longitudes[$i]=$longitudes[$i]-360;}
		$polygon = $polygon . $longitudes[$i].' '.$latitudes[$i].'; ' ;
	}
	$polygon=substr($polygon,0,-2); // We don't need the trailing ; 
	$_POST['polygon']=$polygon; // Fake a form variable to process in the loop below 
	$_POST['latitudenorth']='';$_POST['latitudesouth']='';$_POST['longitudeeast']='';$_POST['longitudewest']=''; // If the Polygon is defined, the Bounding Box must be undefined
}


//fix polygon here if it is utm (3395)

//polgyon coming in looks like this:
//-11354261.929542 4816498.8777847; -10620466.458008 4811606.9079745; -10625358.427818 4415357.3533459; -11324910.110681 4444709.1722073

if($_POST['polygon']!=""){
	
	//break it up and look for values > 360
	$parts=explode("; ",$_POST['polygon']);
	if(abs($parts[0])>375){
		//OK, we found some utm, so let's convert here
		$mercpoly="";
		foreach($parts as $thispart){
			$mercpoly.=$thispart.", ";
		}
		$mercpoly.=$parts[0];

	
		//echo $mercpoly;exit();
	
		$fixedpoly=$db->get_var("SELECT ST_AsText(ST_Transform(ST_GeomFromText('POLYGON(($mercpoly))',3395),4326)) as geom;");
	
		$fixedpoly=str_replace("POLYGON((","",$fixedpoly);
		$fixedpoly=str_replace("))","",$fixedpoly);

		$fixedarray=explode(",",$fixedpoly);
	
		array_pop($fixedarray);
	
		$newpoly="";
		$newdelim="";
	
		foreach($fixedarray as $newpart){
			$newpoly.=$newdelim.$newpart;
			$newdelim="; ";
		}
	
		//echo $newpoly;exit();
		$_POST['polygon']=$newpoly;

	}
	
	//echo $_POST['polygon'];exit();
}

if($_POST['polarpolygon']!=""){

	$_POST['polygon']="";
	$db->query("UPDATE search_query SET polygon='' WHERE pkey=$pkey;");
	
}

//let's take care of the new sampletype search here
if(isset($_POST['metamorphichidden'])){$_POST['hiddenrocknames']=$_POST['metamorphichidden'];}
if(isset($_POST['alterationhidden'])){$_POST['hiddenrocknames']=$_POST['alterationhidden'];}
if(isset($_POST['veinhidden'])){$_POST['hiddenrocknames']=$_POST['veinhidden'];}
if(isset($_POST['orehidden'])){$_POST['hiddenrocknames']=$_POST['orehidden'];}
if(isset($_POST['hiddensednames'])){$_POST['hiddenrocknames']=$_POST['hiddensednames'];}
if(isset($_POST['xenolithhidden'])){$_POST['hiddenrocknames']=$_POST['xenolithhidden'];}

if(isset($_POST['igneousechhidden'])){$_POST['ech']=$_POST['igneousechhidden'];}
if(isset($_POST['sedimentaryechhidden'])){$_POST['ech']=$_POST['sedimentaryechhidden'];}


// ROCK DATA 
/* If any level 1 through 4 or tasname (rock data) was set, clear all any existing rock data before we update the record with the new data. */
if (isset($_POST['level1']) or isset($_POST['level2']) or isset($_POST['level3']) or isset($_POST['level4']) or isset($_POST['tasname']) or isset($_POST['rockname']) or isset($_POST['hiddenrocknames']) or isset($_POST['ech']) ) {
	$db->query("update search_query set level1='', level2='', level3='', level4='', tasname='', rockname='' where pkey=$pkey") or die('could not update search_query for level1 in searchupdate.php');
}



if($_POST['level1']!="" && $_POST['hiddenrocknames']!="" ){

	$_POST['level4']=$_POST['hiddenrocknames'];

}else{

	if (isset($_POST['level1']) or isset($_POST['level2']) or isset($_POST['level3']) or isset($_POST['level4']) or isset($_POST['tasname']) ){
		$_POST['rockname']=="";
		$_POST['hiddenrocknames']="";
	}elseif($_POST['rockname']!=""){
		$_POST['hiddenrocknames']="";
	}elseif($_POST['hiddenrocknames']!=""){
		$_POST['level4']=$_POST['hiddenrocknames'];
	}elseif($_POST['ech']!=""){
		$_POST['rockname']=="";
		$_POST['hiddenrocknames']="";
		$_POST['tasname']="";
	}

}

if (isset($_POST['metamorphic'])){
	$_POST['level1']='metamorphic';
}

// PUBLICATION YEAR
/*
If yearmin or yearmax is nonnumeric (user made a mistake), set it to null.
If there is a nonzero yearmin but not a nonzero yearmax, set yearmax=yearmin (this is an exact publication year, not a range).
If yearmin is greater than yearmax, set both to null. - eej 12/4/06
If year is set, set yearmin and yearmax to year 
Allow year to = 0 because that is a valid year  
*/
// If user defined an exact year, this overrides any year range they may have set. Exact year is stored as yearmin = yearmax.
if(isset($_POST['year']) and is_numeric($_POST['year'])){ 
	$_POST['yearmin']=$_POST['year'];$_POST['yearmax']=$_POST['year'];unset($_POST['year']);
} elseif (isset($_POST['yearmin']) and isset($_POST['yearmax']) ) {  
	// If yearmin is greater than yearmax, switch the values of the variables
	if (is_numeric($_POST['yearmin']) and is_numeric($_POST['yearmax'])) {
		if($_POST['yearmin'] > $_POST['yearmax']){
			$y=$_POST['yearmax'];
			$_POST['yearmax']=$_POST['yearmin'];
			$_POST['yearmin']=$y;
			unset($y);
		}
	} else {
		// If either yearmin or yearmax is non-numeric, set both to null
		$_POST['yearmin']=''; $_POST['yearmax']='';
	}
} 
// end processing of post variables for pubyear

// example of using a constant:  define("FOO","some scalar value for constant FOO")

// Build a sql statement and update the record in table search_query.
$sql = '';

// Loop through these variable names. If any of them exist as form variables, add them to the sql statement. Most are scalars, but level1 level2 level3 level4 rocktype may be arrays.
$form_field_array=array('author', 'title', 'journal', 'advkeyword1', 'yearmin', 'yearmax',  
'latitudenorth', 'latitudesouth', 'longitudewest', 'longitudeeast', 'polygon', 'polarpolygon', 'chemistry', 'MajorElementNormalization', 'SearchDataSource', 'searchopt', 
'rockmode','chemmethods', 'material', 'polygon','tasname', 'level1', 'level2', 'level3', 'level4', 'rocktype', 'material', 'rockclass', 'rockname', 'ech',
'chemswitch', 'normalization', 'mineralname', 'age', 'age_min', 'age_max', 'geoage', 'rocknamecart', 'sampleid', 'igsn','geology', 'volcano', 'doi', 'oceanfeature', 'ageexists', 'cruiseid');
foreach($_POST as $key=>$val) { 
	if (in_array($key,$form_field_array)) { 
		if (is_array($val)) { // If the variable is an array, convert it to a comma-delimited string 
			$val=implode(',',$val);
		} // if
		$val=trim($val); // strip leading/trailing spaces from text variables like author, journal, title, etc. 
		
		//JMA - need to check for length here to avoid nulls
		if(strlen($val)>0){
			$sql .= ", $key='$val' ";
		}
	} // if 
}//foreach

// Now finish building the sql statement and execute it. 
if (strlen($sql)>1) {
	$sql = substr($sql,1); //remove leading comma
	$sql = 'UPDATE search_query SET '.$sql.' WHERE pkey='.$pkey; 

	$db->query($sql) or die('could not update search_query for a variable in the list, in searchupdate.php');

	//echo "<br><br> $sql <br><br>";
}


// CHEMISTRY 
//$getchems = $db->get_results('select pkey, field_name from data_field_selection');
$getchems = $db->get_results('select distinct field_name from data_field_selection');
//or die('could not execute query getchems in searchupdate.php');
//$db->vardump($getchems);
$chemlist = ''; // The list of chemicals and constraints to store in the chemistry column of table search_query
$methlist = ''; // The list of methods for any chemicals, in format item:method;method:method,item:method;method;method,item:method 
foreach ($getchems as $g) { 
	$item_set = false; // Was this chemical/item set as a parameter? 
	$var1=$g->field_name."_check"; // the checkbox for this chemical/item - if checked it means 'this chemical exists'
	$var2=$g->field_name."_lt"; // less than number
	$var3=$g->field_name."_gt"; // greater than numer
	$var4=$g->field_name."_methods"; // list of methods for this chemical/item (not required, may be null)
	if (isset($_POST[$var2]) and isset($_POST[$var3]) and $_POST[$var2] != '' and $_POST[$var3] != '') { // User defined a range of values for this chemical
		$chemlist .= ','.$_POST[$var2].':'.$g->field_name.':'.$_POST[$var3]; // e.g. 25:SIO2:50 for 'SIO2 is between 25 and 50'
		$item_set = true;
	} elseif (isset($_POST[$var1]) and $_POST[$var1]) { // User checked the 'exists' checkbox, so constraint is only that chemical exists
		$chemlist .= ','.$g->field_name.':EXISTS'; // e.g. SIO2:EXISTS
		$item_set = true;
	} 
	if ($item_set) { // If the chemical/item is set, see if the user selected any methods for it 
		if (isset($_POST[$var4]) and $_POST[$var4]>'') { // Method(s) were chosen 
			$_POST[$var4] = str_replace(',',';',$_POST[$var4]); // The list of methods for this chemical/item must be ; delimited
			$methlist = $methlist . "," . $g->field_name . ":" . $_POST[$var4]; // The methods column takes the form item:method;method;method,item:method,item:method;method
		}
	}
}
if ($chemlist>'') { 
	$chemlist=trim(substr($chemlist,1)); // remove the leading comma
	$updatechems = $db->query("UPDATE search_query SET chemistry='".$chemlist."' WHERE pkey=$pkey") or die('could not update chemistry in searchupdate.php');
}
if ($methlist>'') { 
	$methlist=trim(substr($methlist,1)); // remove the leading comma
	$updatechemmethods = $db->query("UPDATE search_query SET chemmethods='".$methlist."' WHERE pkey=$pkey") or die('could not update chemmethods in searchupdate.php');
}

//if searchquery_material!=3 (mineral), let's clear the mineralname
$currsearch=$db->get_row("select material, mineralname from search_query where pkey=$pkey");
if($currsearch->material!=3 and $currsearch->mineralname!=""){
	$db->query("update search_query set mineralname='' where pkey=$pkey");
}

//redirect here
header("Location:search.php?pkey=$pkey&glossary_pkey=$glossary_pkey");


?>
