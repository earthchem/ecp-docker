<?PHP
/**
 * geologypoly.php
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
//if (!extension_loaded("MapScript"))
//  dl('php_mapscript.'.PHP_SHLIB_SUFFIX);

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

$mycoords=$db->get_var("select poly from shapegeology where gid=$gids");

//echo $mycoords;exit();

//triangle: -101.278125 37.959840345383; -100.2375 39.337965345383; -99.534375 37.762965345383
//square: -97.621875 38.916090345383; -97.65 37.931715345383; -96.6375 37.903590345383; -96.665625 38.859840345383

//add 360 to any longitudes < 0
$pointsdelim="";
$points=explode(",",$mycoords);
foreach($points as $point){
	$parts=explode(" ",$point);
	$long=$parts[0];
	$lat=$parts[1];
	//if($long < 0){$long=$long+360;}
	if($long < -150){$long=$long+360;}
	$newcoords.=$pointsdelim.$long." ".$lat;$pointsdelim="; ";
}

$mycoords=$newcoords;

//echo $mycoords;exit();

//$mycoords="-101.278125 37.959840345383; -100.2375 39.337965345383; -99.534375 37.762965345383:-97.621875 38.916090345383; -97.65 37.931715345383; -96.6375 37.903590345383; -96.665625 38.859840345383";

$left=999;
$right=-999;
$up=-999;
$down=999;

$polys=explode(":",$mycoords);

foreach($polys as $poly){

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

}//end foreach polys

//print_r($mylons);
//echo "<br>";
//print_r($mylats);
//exit();

if($world!=""){
	$offset=45; //25
}else{
	$offset=1;
}

$left=$left-$offset;
$right=$right+$offset;
$up=$up+$offset;
$down=$down-$offset;

//if($left > 180) $left=$left-180;
//if($right > 180) $right=$right-180;



//echo "left: $left right: $right up: $up down: $down";exit();


// Create a map object. This calls an external control file (bathy.map) look at that file to see what's going on there.
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

	$oMapImage = $oMap->draw();

}//end foreach polys









































/*

$oMapImage->saveImage("temp/".$myrand."_spmap.png");

$bigimg = new Imagick("temp/".$myrand."_spmap.png");

$ecoverlayimg = new Imagick("eclogooverlaysmall.png");

$bigimg->compositeImage($ecoverlayimg, imagick::COMPOSITE_OVER, 5, 195);

$bigimg->thumbnailImage(200,150);

*/





























header('Content-type: image/png');

//echo $bigimg;

$oMapImage->saveImage("");

unlink("temp/".$myrand."_spmap.png");



















unset($oMap);


?>
