<?PHP
/**
 * gazetteerpoly.php
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

// Load MapScript extension
if (!extension_loaded("MapScript"))
  dl('php_mapscript.'.PHP_SHLIB_SUFFIX);

$left=$_GET['west'];
$right=$_GET['east'];
$up=$_GET['north'];
$down=$_GET['south'];

$mycoords="$left $up; $right $up; $right $down; $left $down";

//echo $mycoords;
//exit();

//echo $mycoords;

//exit();

$coords=explode("; ",$mycoords);

//print_r($coords);

for($i=0; $i<count($coords); $i++){
	//echo $i." = ".$coords[$i]."<br>";
	$myparts=explode(" ",$coords[$i]);
	$mylons[$i]=$myparts[0];
	$mylats[$i]=$myparts[1];
}


//print_r($mylons);
//echo "<br>";
//print_r($mylats);
//exit();

$offset=1.5;
$left=$left-$offset;
$right=$right+$offset;
$up=$up+$offset;
$down=$down-$offset;

/*
print_r($mylons);
echo "<br>";
print_r($mylats);
echo "<br>";
echo "up: $up down: $down left: $left right: $right <br>";
exit();
*/

//print_r($mylats);
//echo "<br><Br>";
//print_r($mylons);

//exit();


// Create a map object. This calls an external control file (bathy.map) look at that file to see what's going on there.
$oMap = ms_newMapObj("/public/mgg/web/ecp.iedadata.org/htdocs/bathy.map");

//Set the size of resulting image file
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
//$oMap->setExtent(-180,-90,180,90);

// Create another layer to hold point locations
$oLayerPoints = ms_newLayerObj($oMap);
$oLayerPoints->set( "name", "custom_points");
$oLayerPoints->set( "type", MS_LAYER_POLYGON);
$oLayerPoints->set( "status", MS_DEFAULT);
$oLayerPoints->set( "opacity", "50");

//Create a class Object out of the points layer so we can add to it
$oMapClass = ms_newClassObj($oLayerPoints);

// Create a style object defining how to draw features
// We don't set a color here, because we want to do it inside the fetch loop
$oPointStyle = ms_newStyleObj($oMapClass);
$oPointStyle->outlinecolor->setRGB(0,0,0);

$oPointStyle->color->setRGB(255,0,0);

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


/*
$pointObj->setXY(-130,5);
$oLine->add($pointObj);
$pointObj->setXY(-130,45);
$oLine->add($pointObj);
$pointObj->setXY(-100,25);
$oLine->add($pointObj);
$pointObj->setXY(-130,5);
$oLine->add($pointObj);
 */

$oShp->add($oLine);

$oLayerPoints->addFeature($oShp);


$oMapImage = $oMap->draw();


//we don't want to save an image each time,
//so we're going to force the image out to
//STDOUT:
//
//first, send a header so the browser knows what we're sending:
header('Content-type: image/png');


//here we save the image, but we don't give it
//a filename... this forces it to go to SDTOUT.
$oMapImage->saveImage("");

//finally, unset the global map object to free up memory:
unset($oMap);


?>
