<?PHP
/**
 * lepr_count_wrapper.php
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


$items=array('sio2','al2o3','cao','mgo','na2o','k2o','p2o5','mno','tio2','feo');

foreach($items as $item){
	if($_GET["$item"._min]!="" && $_GET["$item"._max]!=""){
		$string.=$delim."$item"."_min=".$_GET["$item"."_min"]."&"."$item"."_max=".$_GET["$item"."_max"];
		$delim="&";
	}
}

$contentstring="http://lepr.ofm-research.org/YUI/Webservices/ws_LEPRcount?type=bulk&".$string;
$linkstring="http://lepr.ofm-research.org/YUI/Webservices/ws_LEPRsearch?type=bulk&".$string;

//echo $string;

//echo $contentstring;
//exit();

$content = file_get_contents($contentstring); 

if($content > 0){
	echo "$content samples found.<div style=\"font-size:.5em;\">Click <a href=\"$linkstring\" target=\"_blank\">Here</a> to see these samples at LEPR</div>";
}else{
	echo "No samples found.<div style=\"font-size:.5em;padding-left:10px;\">Adjust sliders or turn off oxides<br>above to broaden your search.</div>";
}

//echo $content;

?>