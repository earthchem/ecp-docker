<?PHP
/**
 * showstaticpolarmap.php
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

include('includes/ks_head.html');

//var_dump($_POST);

?>
<h1>Earthchem&nbsp;Static&nbsp;Southern&nbsp;Polar&nbsp;Map</h1>
<?

include('get_pkey.php');
include('db.php');
include('datasources.php');
include('srcwhere.php');

?>
<img src="legend.jpg" border="0"><br>
<?
//echo "ecstaticmap.php?pkey=$pkey <br>";

$imgstring="<image src=\"ecstaticpolarmap.php?pkey=$pkey";
foreach($datasources as $ds){
	eval("\$thisval=$".$ds->name.";");
	$imgstring.="&".$ds->name."=$thisval";
}
$imgstring.="\">";

echo "$imgstring";

include('includes/ks_footer.html');

?>