<?PHP
/**
 * ecdynmap.php
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


// Load MapScript extension
if (!extension_loaded("MapScript"))
  dl('php_mapscript.'.PHP_SHLIB_SUFFIX);

include('db.php');

include("get_pkey.php");

include("datasources.php");

include('srcwhere.php');

include('queries.php');

//echo nl2br($mapstring);


//exit();

$rows=$db->get_results("$mapstring");

/*
echo "<pre>";
//print_r($rows);
print_r($mapstring);
echo "</pre>";
exit();
*/

// Create a map object.
$oMap = ms_newMapObj("dynmap.map");

//echo $oMap->getProjection();exit();
//projection:
//+init=epsg:4326

$oMap->setSize(800,600);

if($_GET['BBOX'] != "" ){
	$mybox=$_GET['BBOX'];
}else{
	$mybox="-146.78124999999997,-5.062500000000085,-56.78124999999997,84.93749999999991";
}

$BBOX=explode(",",$mybox);

$oMap->setExtent($BBOX[0], $BBOX[1], $BBOX[2], $BBOX[3]);

$nSymbolId = ms_newSymbolObj($oMap, "circle");
$oSymbol = $oMap->getsymbolobjectbyid($nSymbolId);
$oSymbol->set("type", MS_SYMBOL_ELLIPSE);
$oSymbol->set("filled", MS_TRUE);
$aPoints[0] = 1;
$aPoints[1] = 1;
$oSymbol->setpoints($aPoints);

// Create another layer to hold point locations
$oLayerPoints = ms_newLayerObj($oMap);
$oLayerPoints->setProjection("+init=epsg:4326");
//echo $oLayerPoints->getProjection();exit();
$oLayerPoints->set( "name", "custom_points");
$oLayerPoints->set( "type", MS_LAYER_POINT);
$oLayerPoints->set( "status", MS_DEFAULT);

//$oLayerPoints->set("transparency", 20);

// Render the map into an image object
$oMapImage = $oMap->draw();

$oMapClass = ms_newClassObj($oLayerPoints);

// Create a style object defining how to draw features
$oPointStyle = ms_newStyleObj($oMapClass);
//$oPointStyle->color->setRGB(250,0,0);
$oPointStyle->outlinecolor->setRGB(0,0,0);
$oPointStyle->set( "symbolname", "circle");
$oPointStyle->set( "size", "9");
$oPointStyle->color->setRGB(255,0,0);

srand(time());

foreach($rows as $row){

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
	
	$point->setXY($row->longitude,$row->latitude);
	
   	$point->draw($oMap,$oLayerPoints,$oMapImage,0,'');

}

// Save the map to an image file

header('Content-type: image/png');

$oMapImage->saveImage("");

unset($oMap);


/*
echo "<img src=\"multicolor.gif\"></img>";
*/
?>
