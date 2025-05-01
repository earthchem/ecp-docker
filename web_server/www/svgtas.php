<?PHP
/**
 * svgtas.php
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


//print_r($_GET);exit();

$d=$_GET['d'];

include "get_pkey.php";
include "db.php";
include "srcwhere.php";
include("queries.php");

if($d!=""){
	header('Content-type: image/svg+xml');
	header('Content-Disposition: attachment; filename="earthchem_svg_tas_'.$pkey.'.svg"');
}

//echo nl2br($foofoo_sql);exit();

function convertx($x){
	$thisval=(13.10416667*($x-36))+58;
	return round($thisval,1);
}

function converty($y){
	$thisval=488-(24.16666667*$y);
	return round($thisval,1);
}



$ymin = 0; $ymax = 18;
$xmin = 36; $xmax = 84;

//echo nl2br($tas_sql);exit();

//was $foofoo_sql
$results=$db->get_results($tas_sql) or die("could not execute tas_sql query in svgtas.php near line 20");//not sure about foofoo_sql here

if(count($results)>0){

//svg file header here

echo '<?xml version="1.0" encoding="UTF-8" standalone="no"?>';

?>
<!-- Generator: Adobe Illustrator 16.0.0, SVG Export Plug-In . SVG Version: 6.00 Build 0)  -->
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="705px" height="540px" viewBox="0 0 705 540" enable-background="new 0 0 705 540" xml:space="preserve">
<rect fill="#FAFAFA" width="705" height="540"/>
<text transform="matrix(1 0 0 1 250.4531 34)" font-family="'Helvetica'" font-size="24">Total Alkali vs SiO2</text>
<path stroke="#CCCCCC" d="M58,488h629 M58,439.67h629 M58,391.33h629 M58,343h629 M58,294.67h629 M58,246.33h629 M58,198h629
	 M58,149.67h629 M58,101.33h629 M58,53h629 M58,53v435 M110.42,53v435 M162.83,53v435 M215.25,53v435 M267.67,53v435 M320.08,53v435
	 M372.5,53v435 M424.92,53v435 M477.33,53v435 M529.75,53v435 M582.17,53v435 M634.58,53v435 M687,53v435"/>
<path stroke="#333333" d="M58,491v-3 M110.42,491v-3 M162.83,491v-3 M215.25,491v-3 M267.67,491v-3 M320.08,491v-3 M372.5,491v-3
	 M424.92,491v-3 M477.33,491v-3 M529.75,491v-3 M582.17,491v-3 M634.58,491v-3 M687,491v-3 M55,488h3 M55,439.67h3 M55,391.33h3
	 M55,343h3 M55,294.67h3 M55,246.33h3 M55,198h3 M55,149.67h3 M55,101.33h3 M55,53h3"/>
<g>
	<path stroke="#333333" stroke-width="2" d="M56,488h633"/>
</g>
<g>
	<g>
		<text transform="matrix(1 0 0 1 45.6348 491.5996)" fill="#333333" font-family="'Georgia'" font-size="12">0</text>
		<text transform="matrix(1 0 0 1 46.2969 443.2695)" fill="#333333" font-family="'Georgia'" font-size="12">2</text>
		<text transform="matrix(1 0 0 1 46.2207 394.9297)" fill="#333333" font-family="'Georgia'" font-size="12">4</text>
		<text transform="matrix(1 0 0 1 46.209 346.5996)" fill="#333333" font-family="'Georgia'" font-size="12">6</text>
		<text transform="matrix(1 0 0 1 45.8457 298.2695)" fill="#333333" font-family="'Georgia'" font-size="12">8</text>
		<text transform="matrix(1 0 0 1 40.4785 249.9297)" fill="#333333" font-family="'Georgia'" font-size="12">10</text>
		<text transform="matrix(1 0 0 1 41.1406 201.5996)" fill="#333333" font-family="'Georgia'" font-size="12">12</text>
		<text transform="matrix(1 0 0 1 41.0645 153.2695)" fill="#333333" font-family="'Georgia'" font-size="12">14</text>
		<text transform="matrix(1 0 0 1 41.0527 104.9297)" fill="#333333" font-family="'Georgia'" font-size="12">16</text>
		<text transform="matrix(1 0 0 1 40.6895 56.5996)" fill="#333333" font-family="'Georgia'" font-size="12">18</text>
	</g>
	<g>
		<text transform="matrix(1 0 0 1 51.2939 501.4004)" fill="#333333" font-family="'Georgia'" font-size="12">36</text>
		<text transform="matrix(1 0 0 1 103.3477 501.4004)" fill="#333333" font-family="'Georgia'" font-size="12">40</text>
		<text transform="matrix(1 0 0 1 156.0508 501.4004)" fill="#333333" font-family="'Georgia'" font-size="12">44</text>
		<text transform="matrix(1 0 0 1 208.2832 501.4004)" fill="#333333" font-family="'Georgia'" font-size="12">48</text>
		<text transform="matrix(1 0 0 1 261.1484 501.4004)" fill="#333333" font-family="'Georgia'" font-size="12">52</text>
		<text transform="matrix(1 0 0 1 313.5146 501.4004)" fill="#333333" font-family="'Georgia'" font-size="12">56</text>
		<text transform="matrix(1 0 0 1 365.4219 501.4004)" fill="#333333" font-family="'Georgia'" font-size="12">60</text>
		<text transform="matrix(1 0 0 1 418.1348 501.4004)" fill="#333333" font-family="'Georgia'" font-size="12">64</text>
		<text transform="matrix(1 0 0 1 470.3574 501.4004)" fill="#333333" font-family="'Georgia'" font-size="12">68</text>
		<text transform="matrix(1 0 0 1 523.3838 501.4004)" fill="#333333" font-family="'Georgia'" font-size="12">72</text>
		<text transform="matrix(1 0 0 1 575.7598 501.4004)" fill="#333333" font-family="'Georgia'" font-size="12">76</text>
		<text transform="matrix(1 0 0 1 627.3203 501.4004)" fill="#333333" font-family="'Georgia'" font-size="12">80</text>
		<text transform="matrix(1 0 0 1 680.0332 501.4004)" fill="#333333" font-family="'Georgia'" font-size="12">84</text>
	</g>
	<g>
		<text transform="matrix(1 0 0 1 298.1504 130)" fill="#333333" font-family="'Helvetica'" font-size="10">Phonolite</text>
		<text transform="matrix(1 0 0 1 239.0864 215)" fill="#333333" font-family="'Helvetica'" font-size="10">Tephriphonolite</text>
		<text transform="matrix(1 0 0 1 258.4927 308)" fill="#333333" font-family="'Helvetica'" font-size="10">Basaltic</text>
		<text transform="matrix(1 0 0 1 259.2373 323)" fill="#333333" font-family="'Helvetica'" font-size="10">Trachy-</text>
		<text transform="matrix(1 0 0 1 257.0962 338)" fill="#333333" font-family="'Helvetica'" font-size="10">andesite</text>
		<text transform="matrix(1 0 0 1 297.9985 266)" fill="#333333" font-family="'Helvetica'" font-size="10">Trachyandesite</text>
		<text transform="matrix(1 0 0 1 406.7324 186)" fill="#333333" font-family="'Helvetica'" font-size="10">Trachyte</text>
		<text transform="matrix(1 0 0 1 408.5605 267)" fill="#333333" font-family="'Helvetica'" font-size="10">Trachydacite</text>
		<text transform="matrix(1 0 0 1 195.8647 264)" fill="#333333" font-family="'Helvetica'" font-size="10">Phonotephrite</text>
		<text transform="matrix(1 0 0 1 195.5601 363)" fill="#333333" font-family="'Helvetica'" font-size="10">Trachybasalt</text>
		<text transform="matrix(1 0 0 1 132.9434 430)" fill="#333333" font-family="'Helvetica'" font-size="10">Picro-</text>
		<text transform="matrix(1 0 0 1 132.6577 445)" fill="#333333" font-family="'Helvetica'" font-size="10">basalt</text>
		<text transform="matrix(1 0 0 1 208.1035 406)" fill="#333333" font-family="'Helvetica'" font-size="10">Basalt</text>
		<text transform="matrix(1 0 0 1 283.4927 406)" fill="#333333" font-family="'Helvetica'" font-size="10">Basaltic</text>
		<text transform="matrix(1 0 0 1 281.542 421)" fill="#333333" font-family="'Helvetica'" font-size="10">Andesite</text>
		<text transform="matrix(1 0 0 1 354.542 406)" fill="#333333" font-family="'Helvetica'" font-size="10">Andesite</text>
		<text transform="matrix(1 0 0 1 465.8281 406)" fill="#333333" font-family="'Helvetica'" font-size="10">Dacite</text>
		<text transform="matrix(1 0 0 1 536.9355 283)" fill="#333333" font-family="'Helvetica'" font-size="10">Rhyolite</text>
		<text transform="matrix(1 0 0 1 136.2119 328)" fill="#333333" font-family="'Helvetica'" font-size="10">Tephrite</text>
		<text transform="matrix(1 0 0 1 128.542 355)" fill="#333333" font-family="'Helvetica'" font-size="10">Basanite</text>
		<text transform="matrix(1 0 0 1 75.9927 280)" fill="#333333" font-family="'Helvetica'" font-size="10">Foidite</text>
	</g>
	<g>
		<text transform="matrix(1 0 0 1 607.2119 532)" fill="#333333" font-family="'Helvetica'" font-size="10">2 samples</text>
	</g>
	<text transform="matrix(1 0 0 1 308.0352 524)" font-family="'Helvetica'" font-size="16">SiO2 (wt. percent)</text>
	<text transform="matrix(-1.836970e-16 -1 1 -1.836970e-16 32 363.2031)" font-family="'Helvetica'" font-size="16">Na2O + K2O (wt. percent)</text>
</g>
<path fill="none" stroke="#333333" d="M123.5,488V318.8l150.7-169.1"/>
<path fill="none" stroke="#333333" d="M241.5,123.1l32.7,26.6l66.898,55.5l70.7,113.6V488"/>
<path fill="none" stroke="#333333" d="M586.1,488L490.4,294.7"/>
<path fill="none" stroke="#333333" d="M58,246.3h131"/>
<path fill="none" stroke="#333333" d="M58,318.8h65.5"/>
<path fill="none" stroke="#333333" d="M58,415.5h117.9"/>
<path fill="none" stroke="#333333" d="M175.9,488v-72.5v-48.3l57.7-55.601l47.2-48.399l60.3-58l44.5-43.5"/>
<path fill="none" stroke="#333333" d="M175.9,367.2h91.8l222.7-72.5V173.8"/>
<path fill="none" stroke="#333333" d="M175.9,260.8l57.7,50.8l34.1,55.602V488"/>
<path fill="none" stroke="#333333" d="M220.5,210.1l60.3,53.1l52.4,82.2V488"/>
<path fill="none" stroke="#333333" enable-background="new    " d="M0,0"/>
<path fill="#BBBBBB" d="M154.002,202.35l5.859,5.447l5.242-11.512L154.002,202.35z"/>
<path marker-end="url(#head)" fill="#BBBBBB" d="M157,205"/>
<path fill="#BBBBBB" enable-background="new    " d="M0,0"/>
<path fill="#BBBBBB" d="M96,390.9h-8l4,12L96,390.9z"/>
<path marker-end="url(#head)" fill="#BBBBBB" d="M92,391"/>
<rect fill-opacity="0" width="705" height="540"/>
<line fill="none" stroke="#BBBBBB" stroke-width="2" x1="99.333" y1="266" x2="156.853" y2="205"/>
<line fill="none" stroke="#BBBBBB" stroke-width="2" x1="92" y1="390.9" x2="92" y2="287.834"/>
<g id="ss191615">
<?

//<circle id="e2" fill="#FF0000" stroke="#000000" cx="687" cy="53" r="3"/>
foreach ($results as $row) {
	$sio2=$row->sio2;
	$alkali=$row->na2o + $row->k2o;
	if ($sio2 >= $xmin && $sio2 <= $xmax && $alkali > 0 && $alkali <= $ymax) {
		//echo "sio2: $sio2 alkali: $alkali<br>";
		$sio2=convertx($sio2);
		$alkali=converty($alkali);
		$pkey=$row->sample_pkey;
		//echo "<use x=\"$sio2\" y=\"$alkali\" xlink:href=\"#e2\" id=\"e4\"></use>";
		//echo "<use x=\"$sio2\" y=\"$alkali\" xlink:href=\"#e2\" id=\"ss$pkey\"></use>";
		echo "<circle id=\"ss$pkey\" fill=\"#FF0000\" stroke=\"#000000\" cx=\"$sio2\" cy=\"$alkali\" r=\"3\"/>";
	} // if SiO2 >. ...
} // end foreach results

?>	
</g>
</svg>
<?

}else{
	echo "no results found.";
}
?>