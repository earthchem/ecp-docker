<?PHP
/**
 * volcanoimage.php
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


$myrand=rand(10000000,99999999);


// bathy.php
// Jason Ash, April 26, 2008
// Build a map using a pre-made map file and multi-colored points

function dumpVar($var){
	echo "<pre>";
	print_r($var);
	echo "</pre>";
}

include('db.php');

$gids=$_GET['gids'];
$world=$_GET['world'];

if($gids==""){
	header("Location:blank.png");
	exit();
}

//big or small
$size=$_GET['size'];
if($size==""){
	$size="big";
}

if($gids==""){
	exit();
}

$mygids=explode(",",$gids);

$left=999;
$right=-999;
$up=-999;
$down=999;

foreach($mygids as $mygid){

	$mycoords=$db->get_row("select lat, lon from shapevolcano where gid = $mygid");
	
	//dumpVar($mycoords);

	if($mycoords->lon==""){
		exit();
	}

	if($mycoords->lon>$right){$right=$mycoords->lon;}
	if($mycoords->lon<$left){$left=$mycoords->lon;}
	if($mycoords->lat>$up){$up=$mycoords->lat;}
	if($mycoords->lat<$down){$down=$mycoords->lat;}


}

if($world!=""){
	$offset=10; //25
}else{
	$offset=2;
}

$left=$left-$offset;
$right=$right+$offset;
$up=$up+$offset;
$down=$down-$offset;

if($up>90){$up=90;}
if($down<-90){$down=-90;}
if($right>180){$right=180;}
if($left<-180){$left=-180;}

//echo "up:$up<br>"; echo "down:$down<br>"; echo "right:$right<br>"; echo "left:$left<br>"; exit();



if($world=="ssddd"){
	$left=-180;
	$down=-90;
	$right=180;
	$up=90;
}

if($world!=""){
	//$oMap = ms_newMapObj("bathysmall.map");
	$oMap = ms_newMapObj("bathy.map");
}else{
	$oMap = ms_newMapObj("bathy.map");
}

if($size=="small"){
	$oMap->setSize(200,150);
}else{
	$oMap->setSize(300,225);
}

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
$oLayerPoints->setProjection("+init=epsg:4326");
$oLayerPoints->set( "name", "custom_points");
$oLayerPoints->set( "type", MS_LAYER_POINT);
$oLayerPoints->set( "status", MS_DEFAULT);
$oLayerPoints->updateFromString('LAYER COMPOSITE OPACITY 40 END END'); 
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


//print_r($mygids);exit();


foreach($mygids as $mygid){

	$mycoords=$db->get_row("select lat, lon from shapevolcano where gid = $mygid");

	//print_r($mycoords);exit();

	$redcolor=255;
	$bluecolor=0;
	$greencolor=0;

   



   
   $oPointStyle->color->setRGB($redcolor,$bluecolor,$greencolor);
   $point = ms_newPointObj();
   $point->setXY($mycoords->lon,$mycoords->lat);
   $point->draw($oMap,$oLayerPoints,$oMapImage,0,'');
	
}


/*
$oMapImage->saveImage("temp/".$myrand."_vmap.png");

$bigimg = new Imagick("temp/".$myrand."_vmap.png");

$ecoverlayimg = new Imagick("eclogooverlaysmall.png");

$bigimg->compositeImage($ecoverlayimg, imagick::COMPOSITE_OVER, 5, 195);

if($size=="small"){
	//thumbnail
	$bigimg->thumbnailImage(200,150);
}
*/





























header('Content-type: image/png');

//echo $bigimg;

$oMapImage->saveImage("");

unlink("temp/".$myrand."_vmap.png");

?>
