<?PHP
/**
 * deletesavedquery.php
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
include("db.php");

include("geopasstest.php");

if (isset($user)) { 
	$userspkey=$userpkey;
}

include("get_pkey.php");

$db->query("update search_query set saveduser=null where saveduser=$userspkey and pkey=$pkey");
//echo("update search_query set saveduser=null where saveduser=$userspkey and pkey=$pkey");



header("Location:savedqueries.php");

?>