<?PHP
/**
 * tas.php
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


include "get_pkey.php";

include "includes/ks_head.html";


echo '<h1>TAS Diagram</h1>';


?>
<div style="width:800px;text-align:right;">
<?
echo "<input type=button value=\"Back to EarthChem Output\" onClick=\"window.location = 'http://ecp.iedadata.org/results.php?pkey=$pkey';\">";
?>
</div><br><br>



<div align="center">
<?


include("svgtas.php");


?>
<br><br>


<input type=button value="Click Here to Download SVG File" onClick="window.location = '/svgtas.php?pkey=<?=$_GET['pkey']?>&d=y<?=$sourcelinktext?>';">


</div><br><br><br>
<?













include ('includes/ks_footer.html');
?>