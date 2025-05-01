<?PHP
/**
 * oceanfeaturepoly.php
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

$myrand=rand(10000000,99999999);

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

	$mycoords=$db->get_var("select ST_AsText(the_geom) from oceanfeatures where gid = $mygid limit 1");
	
	//$mycoords=str_replace("MULTIPOLYGON(((","",$mycoords);
	//$mycoords=str_replace(")))","",$mycoords);

	$mycoords=str_replace("MULTILINESTRING((","",$mycoords);
	$mycoords=str_replace("))","",$mycoords);

	
	
	
	
	//echo "$mycoords";
	
	$coords=explode(",",$mycoords);
	
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

if($world!=""){
	$offset=30; //30
}else{
	$offset=1;
}

$left=$left-$offset;
$right=$right+$offset;
$up=$up+$offset;
$down=$down-$offset;




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

$nSymbolId = ms_newSymbolObj($oMap, "circle");
$oSymbol = $oMap->getsymbolobjectbyid($nSymbolId);
$oSymbol->set("type", MS_SYMBOL_ELLIPSE);
$oSymbol->set("filled", MS_TRUE);
$aPoints[0] = 1;
$aPoints[1] = 1;
$oSymbol->setpoints($aPoints);

// Create another layer to hold point locations
$oLayerPoints = ms_newLayerObj($oMap);
$oLayerPoints->set( "name", "custom_points");
$oLayerPoints->set( "type", MS_LAYER_POLYGON);
$oLayerPoints->set( "status", MS_DEFAULT);

$oLayerPoints2 = ms_newLayerObj($oMap);
$oLayerPoints2->set( "name", "custom_points");
$oLayerPoints2->set( "type", MS_LAYER_POINT);
$oLayerPoints2->set( "status", MS_DEFAULT);

if($world!=""){
	//$oLayerPoints->set( "opacity", ".3");
	$oLayerPoints->updateFromString('LAYER COMPOSITE OPACITY 40 END END'); 
}else{
	//$oLayerPoints->set( "opacity", ".3");
	$oLayerPoints->updateFromString('LAYER COMPOSITE OPACITY 40 END END');
}

//Create a class Object out of the points layer so we can add to it
$oMapClass = ms_newClassObj($oLayerPoints);

$oMapClass2 = ms_newClassObj($oLayerPoints2);

$oPointStyle = ms_newStyleObj($oMapClass);
//$oPointStyle->color->setRGB(250,0,0);
$oPointStyle->outlinecolor->setRGB(0,0,0);

$oPointStyle->color->setRGB(255,0,0);

$oPointStyle2 = ms_newStyleObj($oMapClass2);
//$oPointStyle->color->setRGB(250,0,0);
$oPointStyle2->outlinecolor->setRGB(0,0,0);
$oPointStyle2->set( "symbolname", "circle");
$oPointStyle2->set( "size", "9");
$oPointStyle2->color->setRGB(255,0,0);



//add polygon coords here

foreach($mygids as $mygid){

	unset($mylons);
	unset($mylats);

	$oShp = ms_newShapeObj(MS_SHAPE_POLYGON);
	$oLine = ms_newLineObj();
	$pointObj = ms_newPointObj();

	$myrow=$db->get_row("select shape_leng, ST_AsText(the_geom) as thegeom from oceanfeatures where gid = $mygid");
	
	$mycoords=$myrow->thegeom;
	
	$myleng=$myrow->shape_leng;
	

	
	$mycoords=str_replace("MULTILINESTRING((","",$mycoords);
	$mycoords=str_replace("))","",$mycoords);

	
	//echo "$mycoords";
	
	$coords=explode(",",$mycoords);
	
	for($i=0; $i<count($coords); $i++){
		//echo $i." = ".$coords[$i]."<br>";
		$myparts=explode(" ",$coords[$i]);
		$mylons[$i]=$myparts[0];
		$mylats[$i]=$myparts[1];
		
		$pointlon=$myparts[0];
		$pointlat=$myparts[1];
	}
	
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

if($world!=""){
	if($myleng<5){
		$point = ms_newPointObj();
		$point->setXY($pointlon,$pointlat);
		$point->draw($oMap,$oLayerPoints2,$oMapImage,0,'');
	}
}




/*

$oMapImage->saveImage("temp/".$myrand."_omap.png");

unset($oMap);

$bigimg = new Imagick("temp/".$myrand."_omap.png");

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

unlink("temp/".$myrand."_omap.png");




?>
