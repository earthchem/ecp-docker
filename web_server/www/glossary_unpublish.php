<?PHP
/**
 * glossary_unpublish.php
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


session_start();

include("geopasstest.php");

if ($user_level=='admin') {

	// Unpublish a Glossary Entry. Only admin user can do this. 
	include('get_pkey.php');
	include('db.php');
	if (is_numeric($glossary_pkey)) {
	$delete=$db->query("update glossary set published = 0 where pkey = $glossary_pkey");
	header('Location:glossary.php');
	} else {
	include('includes/ks_head.html');
	echo '<p />No Glossary Entry has been identified to unpublish. No pkey has been defined. <a href="glossary.cfm">Glossary home</a>';
	include('includes/ks_footer.html');
	exit;
	}

}
?>
