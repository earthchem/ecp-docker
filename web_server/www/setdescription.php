<?PHP
/**
 * setdescription.php
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
include('get_pkey.php'); // get primary key for search 
include('db.php'); // database drivers, and connect

include("geopasstest.php");

if (isset($userpkey) && $glossary_pkey>'' && $pkey>'') { // If this is a glossary entry and the user is logged-in...

if (isset($_POST['submit'])) { // Process the form
	$errtext='';

	$glossary_name=trim($_POST['glossary_name']);
	$description=trim($_POST['description']);

	if ($glossary_name == '' || $description == '') {
		$errtext.='Please enter Glossary Name and Description.<br>';
	} // if
	if (strlen($glossary_name) > 255) {
		$thelen = strlen($glossary_name);
		$errtext.='Glossary Name is '.$thelen.' characters long. The maximum length allowed is 255 characters.<br>';
	} // if
	if (strlen($description) > 255) {
		$thelen = strlen($description);
		$errtext.='Description is '.$thelen.' characters long. The maximum length allowed is 255 characters.<br>';
	} // if
	if ($errtext == '') {
		$update=$db->query("UPDATE glossary SET glossary_name='$glossary_name', description='$description' WHERE pkey=$glossary_pkey");
		header("Location: search.php?pkey=$pkey&glossary_pkey=$glossary_pkey");
		exit;
	} 
} else { // if form was submitted, else 

} // if

// Get the glossary record 
$getglossary=$db->get_row("SELECT * FROM glossary WHERE pkey=$glossary_pkey") or die("could not execute glossary in setdescription.php");

$glossary_name=$getglossary->glossary_name;
$description=$getglossary->description; 
// Display the page including the form
include('includes/ks_head.html');
include('includes/glossary_header.php');


?>

<script type="text/javascript">
	function validateForm(){
		var x=document.forms["myform"]["glossary_name"].value;
		var y=document.forms["myform"]["description"].value;
		
		if (x==null || x=="" || y==null || y=="" ){
			alert("Both Name and Description must be provided.");
			return false;
		}
	}
</script

<h1>Set Glossary Name and Description</h1>
<?=$errtext?>
<form name="myform" action="" method="post" onsubmit="return validateForm()">
<TABLE border="0" cellspacing="0" cellpadding="5" border=1>
<tr>
<TD colspan="2"><H3;Edit&nbsp;Name&nbsp;and&nbsp;Description
</H3>
</td>
</tr>
<tr>
<TD align="right">Name
</td>
<td> 
<INPUT type="text" name="glossary_name" size="50" value="<?=$glossary_name?>" >
</td>
</tr>
<tr>
<TD align="right">Description
</td>
<td> 
<TEXTAREA name="description" cols="80" rows="4" wrap="virtual"><?=$description?></TEXTAREA>
<BR>(255 characters)
</td>
</tr>
<tr>
<TD colspan="2" align="right">
<INPUT type="submit" name="submit" value="Submit">
</td>
</tr>
</TABLE>
<INPUT type="hidden" name="pkey" value="<?=$pkey?>">
<INPUT type="hidden" name="glossary_pkey" value="<?=$glossary_pkey?>">
</FORM>
<?php
include("includes/ks_footer.html");

} else { // This is either not a glossary entry, or is not being viewed by a logged-in user, so redirect

header("Location: search.php?pkey=$pkey&glossary_search_pkey=$glossary_search_pkey");
//<CFLOCATION url="search.cfm?pkey=#search_query_pkey#&glossary_pkey=$glossary_pkey&search_query_pkey=#search_query_pkey#" addtoken="no";

}
?>