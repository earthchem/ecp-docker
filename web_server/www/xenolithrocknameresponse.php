<?PHP
/**
 * xenolithrocknameresponse.php
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
$rockname=strtolower($_POST['rockname']);
//$rocknames=$db->get_results("select rockname from autorocknames where lower(rockname) like '".$rockname."%' order by rockname limit 10");
$rocknames=$db->get_results("select distinct(data4) as rockname from raw_rock_translation where lower(data4) like '".$rockname."%' and data1='xenolith' order by rockname limit 10");



?>
<ul>
<?
if($db->num_rows > 0){
foreach($rocknames as $r){
echo "<li>$r->rockname</li>\n";
}
}
?>
</ul>
<?




?>