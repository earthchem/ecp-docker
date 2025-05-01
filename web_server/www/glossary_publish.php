<?PHP
/**
 * glossary_publish.php
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


	// This publishes a glossary_entry by setting a flag in the record in table glossary. It is called from a button on the search.php page. Only admin can publish.
	include('get_pkey.php'); // get the pkey
	include('db.php'); // database drivers, and connect
	//include('includes/ks_head.html'); 
	//print_r($_GET); 
	//echo $glossary_pkey; 
	//echo $pkey; 
	if ($glossary_pkey>'') {
	$update=$db->query("update glossary set published = 1 where pkey = $glossary_pkey") or die("could not publish the record in glossary_publish.php");
	//echo "Glossary Entry $glossary_pkey has been published.<br><br><a href=search.php?pkey=$pkey&glossary_pkey=$glossary_pkey>back to glossary entry</a>";
	
	header("Location:glossary.php");
	
	} else { 
	echo 'No Glossary Entry has been identified to publish. No pkey has been defined. <br><br><a href="glossary.php">Glossary home</a>';
	}
	//include('includes/ks_footer.html');


}
?>
