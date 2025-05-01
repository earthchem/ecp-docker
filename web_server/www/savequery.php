<?PHP
/**
 * savequery.php
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
include("get_pkey.php");

include("geopasstest.php");

if (!isset($user) or $pkey=="") {
	header("Location:login.php?pkey=$pkey&savequery=yes");
	exit();
}

//echo "savequery here";
//exit();

include("db.php");

if($_POST['submit']!=""){
	if($_POST['queryname']==""){
		$error="<div style=\"color:#FF0000;font-weight:bold;\">Query Name Cannot be Blank!</div><br><br>";
	}else{
		//update db here
		$queryname=$_POST['queryname'];
		//$users_pkey=$_SESSION['users_pkey'];
		$db->query("update search_query set queryname='$queryname', saveduser=$userpkey where pkey=$pkey");
		header("Location:search.php?pkey=$pkey");
		exit();
	}
}



include('includes/ks_head.html');

/*
foreach($_SESSION as $key=>$value){
	echo "$key : $value <br>";
}
*/

?>
<h3>Save Query</h3>

<form method="POST">
<br>
<?=$error?>
Name of Query:&nbsp;<input type="text" name="queryname">
<input type="submit" name="submit" value="Submit">
<input type="hidden" name="pkey" value="<?=$pkey?>">
</form>















<?
include('includes/ks_footer.html');
?>