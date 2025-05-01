<?PHP
/**
 * get_pkey.php
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

// Get the primary key for the current search, or post an error message. 
// If the user came directly to this page, it is possible no pkey variable is available from url or form; post error message.
if(!isset($getsearch)){
	if (isset($_GET['pkey']) && is_numeric($_GET['pkey'])) {
		$pkey=$_GET['pkey'];  
	} elseif (isset($_POST['pkey']) && is_numeric($_POST['pkey'])) {
		$pkey=$_POST['pkey'];
	} else { 
		$pkey='';
		include('includes/ks_head.html');
		echo "<p>No search is defined. Use the browser's back button to return to the previous page, or start a <a href='./'>new search</a><p>";
		include('includes/ks_footer.html');
		exit;	
	}
	
	if (isset($_GET['glossary_pkey']) && is_numeric($_GET['glossary_pkey'])) {
		$glossary_pkey=$_GET['glossary_pkey'];  
	} elseif (isset($_POST['glossary_pkey']) && is_numeric($_POST['glossary_pkey'])) {
		$glossary_pkey=$_POST['glossary_pkey'];
	} else {
		$glossary_pkey = '';
	}
}
//debug: echo "<br>glossary_pkey $glossary_pkey <br>";
?>