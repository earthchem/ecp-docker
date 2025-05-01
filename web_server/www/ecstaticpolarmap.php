<?PHP
/**
 * ecstaticpolarmap.php
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


$myrand=rand(10000,99999);

// Load MapScript extension
if (!extension_loaded("MapScript"))
  dl('php_mapscript.'.PHP_SHLIB_SUFFIX);

include('db.php');

include("get_pkey.php");

include("datasources.php");

include('srcwhere.php');

include('queries.php');

//echo "$mapstring";


//exit();

$rows=$db->get_results("$polarmapstring");




// Create a map object.
$oMap = ms_newMapObj("bathypolar.map");



//$oMap->setSize(512,512);
$oMap->setSize(800       ,400);


$BBOX=explode(",",$mybox);

$oMap->setExtent(-3200000, -3200000, 3200000, 3200000);





















$nSymbolId = ms_newSymbolObj($oMap, "circle");
$oSymbol = $oMap->getsymbolobjectbyid($nSymbolId);
$oSymbol->set("type", MS_SYMBOL_ELLIPSE);
$oSymbol->set("filled", MS_TRUE);
$aPoints[0] = 1;
$aPoints[1] = 1;
$oSymbol->setpoints($aPoints);

// Create another layer to hold point locations
$oLayerPoints = ms_newLayerObj($oMap);
$oLayerPoints->setProjection("+init=epsg:3031");
$oLayerPoints->set( "name", "custom_points");
$oLayerPoints->set( "type", MS_LAYER_POINT);
$oLayerPoints->set( "status", MS_DEFAULT);

$oMapImage = $oMap->draw();

$oMapClass = ms_newClassObj($oLayerPoints);

//$oMapClass->label->set( "position", MS_AUTO);
//$oMapClass->label->set( "size", 15);
//$oMapClass->label->color->setRGB(250,0,0);
//$oMapClass->label->outlinecolor->setRGB(255,255,255);

// Create a style object defining how to draw features
$oPointStyle = ms_newStyleObj($oMapClass);
//$oPointStyle->color->setRGB(250,0,0);
$oPointStyle->outlinecolor->setRGB(0,0,0);
$oPointStyle->set( "symbolname", "circle");
$oPointStyle->set( "size", "9");
$oPointStyle->color->setRGB(255,0,0);

srand(time());



foreach($rows as $row){

	//print_r($row);exit();
	
	$polarcoords = $row->polarcoords;

	$polarcoords = str_replace("POINT(","",$polarcoords);
	$polarcoords = str_replace("LINESTRING(","",$polarcoords);
	$polarcoords = str_replace(")","",$polarcoords);
	$polarcoords = explode(",",$polarcoords);
	$polarcoords = $polarcoords[0];
	$polarcoords = explode(" ",$polarcoords);
	$longitude = $polarcoords[0];
	$latitude = $polarcoords[1];

	$redcolor=255;
	$bluecolor=255;
	$greencolor=255;

	foreach($datasources as $ds){
		if($ds->name==$row->source){
		
			$colors=explode(",",$ds->dotcolor);
			$redcolor=$colors[0];
			$bluecolor=$colors[1];
			$greencolor=$colors[2];

		}

	}



   
   $oPointStyle->color->setRGB($redcolor,$bluecolor,$greencolor);
   $point = ms_newPointObj();
   //$point->setXY($row->longitude,$row->latitude);
   $point->setXY($longitude,$latitude);
   $point->draw($oMap,$oLayerPoints,$oMapImage,0,'');
	
}

/*

$oMapImage->saveImage("temp/".$myrand."_static.png");

$staticimg = new Imagick("temp/".$myrand."_static.png");

$ecoverlayimg = new Imagick("eclogooverlay.png");

$staticimg->compositeImage($ecoverlayimg, imagick::COMPOSITE_OVER, 5, 357);

*/






header('Content-type: image/png');

//echo $staticimg;
$oMapImage->saveImage("");

unlink("temp/".$myrand."_static.png");

?>
