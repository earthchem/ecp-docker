<?PHP
/**
 * searchpagepoly.php
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

// bathy.php
// Jason Ash, April 26, 2008
// Build a map using a pre-made map file and multi-colored points

include('db.php');

include("get_pkey.php");

$mycoords=$db->get_var("select polygon from search_query where pkey=$pkey");

//add 360 to any longitudes < 0 why?
$pointsdelim="";
$points=explode("; ",$mycoords);
foreach($points as $point){
	$parts=explode(" ",$point);
	$long=$parts[0];
	$lat=$parts[1];
	//if($long < 0){$long=$long+360;}
	//if($long < 0){$long=$long+360;}
	$newcoords.=$pointsdelim.$long." ".$lat;$pointsdelim="; ";
}

$mycoords=$newcoords;

$left=999;
$right=-999;
$up=-999;
$down=999;

$polys=explode(":",$mycoords);

foreach($polys as $poly){

	$coords=explode("; ",$poly);

	for($i=0; $i<count($coords); $i++){
		//echo $i." = ".$coords[$i]."<br>";
		$myparts=explode(" ",$coords[$i]);
		$mylons[$i]=$myparts[0];
		$mylats[$i]=$myparts[1];
		if($myparts[0]>$right){$right=$myparts[0];}
		if($myparts[0]<$left){$left=$myparts[0];}
		if($myparts[1]>$up){$up=$myparts[1];}
		if($myparts[1]<$down){$down=$myparts[1];}
	}

}

$offset=1;
$left=$left-$offset;
$right=$right+$offset;
$up=$up+$offset;
$down=$down-$offset;

$oMap = ms_newMapObj("bathy.map");

$oMap->setSize(200,150);

//Check for a url bbox variable. If not set, use a default (north america)
if($_GET['BBOX'] != "" ){
	$mybox=$_GET['BBOX'];
}else{
	$mybox="-146.78124999999997,-5.062500000000085,-56.78124999999997,84.93749999999991";
}

//We need to explode the BBOX parameter so we can feed it to mapserver
$BBOX=explode(",",$mybox);


//Send the bounding box to mapserver
$oMap->setExtent($left, $down, $right, $up);

// Create another layer to hold point locations
$oLayerPoints = ms_newLayerObj($oMap);
$oLayerPoints->set( "name", "custom_points");
$oLayerPoints->set( "type", MS_LAYER_POLYGON);
$oLayerPoints->set( "status", MS_DEFAULT);
$oLayerPoints->updateFromString('LAYER COMPOSITE OPACITY 40 END END'); 

//Create a class Object out of the points layer so we can add to it
$oMapClass = ms_newClassObj($oLayerPoints);

// Create a style object defining how to draw features
// We don't set a color here, because we want to do it inside the fetch loop
$oPointStyle = ms_newStyleObj($oMapClass);
$oPointStyle->outlinecolor->setRGB(0,0,0);

$oPointStyle->color->setRGB(255,0,0);

$oMapImage = $oMap->draw();

$polys=explode(":",$mycoords);

foreach($polys as $poly){

	unset($mylons);
	unset($mylats);

	$coords=explode("; ",$poly);
	
	//print_r($coords);
	
	for($i=0; $i<count($coords); $i++){
		//echo $i." = ".$coords[$i]."<br>";
		$myparts=explode(" ",$coords[$i]);
		$mylons[$i]=$myparts[0];
		$mylats[$i]=$myparts[1];
		if($myparts[0]>$right){$right=$myparts[0];}
		if($myparts[0]<$left){$left=$myparts[0];}
		if($myparts[1]>$up){$up=$myparts[1];}
		if($myparts[1]<$down){$down=$myparts[1];}
	}

	$oShp = ms_newShapeObj(MS_SHAPE_POLYGON);
	$oLine = ms_newLineObj();
	$pointObj = ms_newPointObj();
	
	//add polygon coords here
	
	for($i=0; $i<count($mylons); $i++){
		$pointObj->setXY($mylons[$i],$mylats[$i]);
		$oLine->add($pointObj);
	}
	$pointObj->setXY($mylons[0],$mylats[0]);
	$oLine->add($pointObj);
	
	
	$oShp->add($oLine);
	
	$oLayerPoints->addFeature($oShp);

}

$oMapImage = $oMap->draw();


header('Content-type: image/png');

$oMapImage->saveImage("");

unlink("temp/".$myrand."_spmap.png");

unset($oMap);

?>
