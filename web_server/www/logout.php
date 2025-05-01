<?PHP
/**
 * logout.php
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

//Earthchem Logout  
session_start(); 
//include('includes/ks_head.html');
// Unset all of the session variables.
$_SESSION['user_pkey']='';
$_SESSION['user_level']='';
$_SESSION = array();
// Destroy the session.
session_destroy();
header("Location: geopass/josso-logout.php?josso_current_url=http://ecp.iedadata.org");







exit();
//echo 'You have logged out. ';
//include('includes/ks_footer.html');

$search=$_GET['search'];
$searchpkey=$_GET['searchpkey'];

if($search=="yes"){
	if($searchpkey!=""){
		header("location:search.php?pkey=$searchpkey");
	}else{
		header("location:index.php");
	}
}else{
	header("location:glossary.php");
}


?>