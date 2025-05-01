<?PHP
/**
 * clearrocktype.php
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

// This page clears any parameters that were set for rock type
include('get_pkey.php'); // the primary key(s)
include('db.php'); // database drivers, and connect
$searchquery=$db->query(
"UPDATE search_query SET
level1 = '',
level2 = '',
level3 = '',
level4 = '',
tasname='',
rockclass='',
rockname='',
ech=''
WHERE pkey = $pkey") 
or die('could not clear rock type in clearrocktype.php');
header("Location: search.php?pkey=$pkey&glossary_pkey=$glossary_pkey"); 
?>