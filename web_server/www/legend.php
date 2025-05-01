<?PHP
/**
 * legend.php
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


include("db.php");
include("datasources.php");

$dscount=count($datasources);

$rowcount=ceil($dscount/4);

//echo "$rowcount";
//exit();

$imageheight=23*$rowcount;

// Create the canvas
//$canvas = imagecreate( 440, $imageheight ); //multiples of 23



$canvas = imagecreatetruecolor( 540, $imageheight ); //multiples of 23

// Switch antialiasing on for one image
//imageantialias($canvas, true);





// First colour - this will be the default colour for the canvas
$white = imagecolorallocate( $canvas, 255, 255, 255 );

imagefilledrectangle($canvas, 0, 0, 540, $imageheight, $white);

// The second colour - to be used for the text
$black = imagecolorallocate( $canvas, 0, 0, 0 );
// Path to the font you are going to use
$font = "fonts/ob.ttf";
// Text to write
$text = "Legend:";
// Font size
$size = "10";

//build the colors here
foreach($datasources as $ds){

	eval("\$".$ds->name."color = imagecolorallocate( \$canvas, ".$ds->dotcolor." );");

}

// Add the text to the canvas
//imagettftext ( resource $image , float $size , float $angle , int $x , int $y , int $color ,  $fontfile ,  $text )

imageTTFText( $canvas, 12, 0, 0, 15, $black, $font, "Legend:" );

$dsnum=1;
$y=15;
$x=90;

foreach($datasources as $ds){

	imageTTFText( $canvas, $size, 0, $x, $y, $black, $font, $ds->showname );
	eval("imagefilledarc ( \$canvas , $x-8 , $y-6 , 14 , 14 , 0 , 360 , \$black , IMG_ARC_PIE );");
	eval("imagefilledarc ( \$canvas , $x-8 , $y-6 , 9 , 9 , 0 , 360 , \$".$ds->name."color , IMG_ARC_PIE );");
	//eval("imagearc ( \$canvas , $x-8 , $y-6 , 12 , 12 , 0 , 360 , \$black );");

	
	
	if($dsnum%4==0){
		$y=$y+20;
		$x=100;
		$dsnum=1;
	}else{
		$dsnum++;
		$x=$x+100;
	}
}

//imagefilledarc ( resource $image , int $cx , int $cy , int $width , int $height , int $start , int $end , int $color , int $style )
//imagefilledarc ( $canvas , 30 , 30 , 15 , 15 , 0 , 360 , $navdatcolor , IMG_ARC_PIE );

/*
imageTTFText( $canvas, $size, 0, 90, 15, $black, $font, "MetPetDB" );
imageTTFText( $canvas, $size, 0, 180, 15, $black, $font, "MetPetDB" );
imageTTFText( $canvas, $size, 0, 270, 15, $black, $font, "MetPetDB" );
imageTTFText( $canvas, $size, 0, 360, 15, $black, $font, "MetPetDB" );

imageTTFText( $canvas, $size, 0, 90, 35, $black, $font, "MetPetDB" );
imageTTFText( $canvas, $size, 0, 180, 35, $black, $font, "MetPetDB" );
imageTTFText( $canvas, $size, 0, 270, 35, $black, $font, "MetPetDB" );
imageTTFText( $canvas, $size, 0, 360, 35, $black, $font, "MetPetDB" );
*/


header('Content-type: image/png');

imagejpeg( $canvas);
// Clear the memory of the tempory image 
ImageDestroy( $canvas );
?> 