<?PHP
/**
 * ecdynpolarmap.php
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


//var_dump($_GET);

// ex1_map_basic.php
// Tyler Mitchell, August 2005
// Build a map using a pre-made map file

// Load MapScript extension
if (!extension_loaded("MapScript"))
  dl('php_mapscript.'.PHP_SHLIB_SUFFIX);

include('db.php');

include("get_pkey.php");

include("datasources.php");

include('srcwhere.php');

include('queries.php');

//echo nl2br($mapstring);exit();

//echo nl2br($polarmapstring);exit();

//exit();

$rows=$db->get_results("$polarmapstring");

//print_r($rows);exit();

//LINESTRING(717462.617910042 4079841.20644407,717156.636102518 4079687.4304211)

// Create a map object.
//$oMap = ms_newMapObj("ex4_map_points.map");
$oMap = ms_newMapObj("dynpolarmap.map");
//$oMap = ms_newMapObj("/system/link/server/apache/htdocs/marine-geo/services/ogc/wms2.map");



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


/*
$oLayerClouds = ms_newLayerObj($oMap);
$oLayerClouds->set( "name", "clouds");
$oLayerClouds->set( "type", MS_LAYER_RASTER);
$oLayerClouds->set( "status", MS_DEFAULT);
$oLayerClouds->set( "data","data/global_clouds.tif");
*/


// Create another layer to hold point locations
$oLayerPoints = ms_newLayerObj($oMap);
$oLayerPoints->set( "name", "custom_points");
$oLayerPoints->set( "type", MS_LAYER_POINT);
$oLayerPoints->set( "status", MS_DEFAULT);
//$oLayerPoints->set("transparency", 20);

/* ****************************************************************************
foreach ($rows as $row)
{
   //$aPointArray = explode(",",$sPointItem);
   // :TRICKY: Note although we are using points
   // we must use a line object (newLineObj)
   // here I call it a CoordList object for simplicity 
   $oCoordList = ms_newLineObj();
   $oPointShape = ms_newShapeObj(MS_SHAPE_POINT);
   $oCoordList->addXY($row->longitude,$row->latitude);
   $oPointShape->add($oCoordList);
   //$oPointShape->set( "text", chop($aPointArray[2]));
   $oLayerPoints->addFeature($oPointShape);
}

// Create a class object to set feature drawing styles.
$oMapClass = ms_newClassObj($oLayerPoints);

// Create a style object defining how to draw features
$oPointStyle = ms_newStyleObj($oMapClass);
$oPointStyle->color->setRGB(250,0,0);
$oPointStyle->outlinecolor->setRGB(0,0,0);
$oPointStyle->set( "symbolname", "circle");
$oPointStyle->set( "size", "7");

// Create label settings for drawing text labels
//$oMapClass->label->set( "position", MS_AUTO);
//$oMapClass->label->color->setRGB(250,0,0);
//$oMapClass->label->outlinecolor->setRGB(255,255,255);

********************************************************************************* */

// Render the map into an image object
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

	$polarcoords = $row->polarcoords;

	$polarcoords = str_replace("POINT(","",$polarcoords);
	$polarcoords = str_replace("LINESTRING(","",$polarcoords);
	$polarcoords = str_replace(")","",$polarcoords);
	$polarcoords = explode(",",$polarcoords);
	$polarcoords = $polarcoords[0];
	$polarcoords = explode(" ",$polarcoords);
	$longitude = $polarcoords[0];
	$latitude = $polarcoords[1];

	//echo "longitude: $longitude latitude: $latitude";exit();
	
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
   
	/*
	if($row->source=="navdat"){
		$redcolor=99;
		$bluecolor=101;
		$greencolor=49;
	}elseif($row->source=="petdb"){
		$redcolor=74;
		$bluecolor=154;
		$greencolor=165;
	}elseif($row->source=="georoc"){
		$redcolor=156;
		$bluecolor=0;
		$greencolor=156;
	}elseif($row->source=="usgs"){
		$redcolor=255;
		$bluecolor=0;
		$greencolor=0;
	}elseif($row->source=="seddb"){
		$redcolor=255;
		$bluecolor=153;
		$greencolor=0;
	}elseif($row->source=="ganseki"){
		$redcolor=240;
		$bluecolor=206;
		$greencolor=65;
	}else{
		$redcolor=255;
		$bluecolor=255;
		$greencolor=255;
	}
	*/

   
   $oPointStyle->color->setRGB($redcolor,$bluecolor,$greencolor);
   $point = ms_newPointObj();
   //$point->setXY($row->longitude,$row->latitude);
   $point->setXY($longitude,$latitude);
   $point->draw($oMap,$oLayerPoints,$oMapImage,0,'');
	
}




/*
   $point
 
 for ( $i=0; $i<3; $i++ ) {
   $meterStyle[$i] = $meterClass0->getStyle($i);
   $meterStyle[$i]->outlinecolor->setRGB($red[$i],$green[$i],$blue[$i]);
   $meterStyle[$i]->color->setRGB($red[$i],$green[$i],$blue[$i]);
   $meterStyle[$i]->set('offsetx',$i*3);
   $meterStyle[$i]->set('offsety',0);
 }
 $point = ms_newPointObj();
 $point->setXY((-1)*$row[1],$row[0]);
 $point->draw($map,$meterLayer,$image,0,$row[2]);
}

$image_url=$image->saveWebImage();
*/


// Save the map to an image file

header('Content-type: image/png');

$oMapImage->saveImage("");

unset($oMap);


/*
echo "<img src=\"multicolor.gif\"></img>";
*/
?>
