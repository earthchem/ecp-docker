<?PHP
/**
 * glossary.php
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

//print_r($_SERVER);
//exit();
//Earthchem Glossary main page. Shows a list of the glossary entries. If the user is logged in and is the author of the entry, the entry looks different to them. 
session_start();
include('db.php'); // database drivers, and connect
include("geopasstest.php");
include 'includes/ks_head.html'; 
include('includes/glossary_header.php'); // login/out buttons 
$myarray = range('A','Z'); //array_merge(range(0,9),range('A','Z')); 
//echo '<DIV style="float:right;margin:20px;clear:right;">';
if (isset($user)) { 
	echo '<INPUT TYPE="button" value="Add Glossary Entry" onClick="location=\'glossary_add.php\'">  ';
	$users_pkey=$userpkey;
	//echo "users_pkey: $users_pkey<br>";
	$admin=false;
	if ($user_level=='admin') {
		$admin=true;
	}
} else {
	echo 'You must be logged in to add a Glossary Entry.';
} // if 
//echo '</div>';
echo '<H1>EarthChem Glossary</H1> 
The Glossary enables any <a href="http://geopass.iedadata.org:8080/josso/register.jsp">registered</a> user to define a named term&nbsp;- a "Glossary&nbsp;Entry"&nbsp;- constrained to location, chemistry and sample characteristics. The Glossary Entry can be used by anyone to display the data that meet those criteria.<BR><BR>
';
echo '<h2>Glossary Entries &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <br>';
// Make a link to each digit and each letter. 
foreach($myarray as $currletter) { 
echo '<a href="#'.$currletter.'">'.$currletter.'</a> ';
}
echo '</h2>';
echo '<br>';

foreach($myarray as $currletter) { 	
echo '<a name="'.$currletter.'"></a><h3>'.$currletter.'</h3> '; // Make this link target invisible, because some of the letters will not have associated entries
$glossary=$db->get_results("select search_query_pkey, pkey as glossary_pkey,users_pkey,search_query_pkey,glossary_name,description,published from glossary where upper(glossary_name) like '$currletter%' order by glossary_name asc");
if (count($glossary)>0) {
foreach($glossary as $g) {
///////echo '<br><br>';
if ($g->users_pkey==$users_pkey) {$author=1;} else {$author=0;}
if ($g->published==1) {$published=1;} else {$published=0;}

//echo "author: $author published: $published $g->glossary_name <br>";

//if (isset($_SESSION['user_level']) && isset($_SESSION['users_pkey']) && $_SESSION['users_pkey'] == $g->USERS_PKEY && !$g->PUBLISHED) {
if (($author==1 && $published==0) || $admin) {  //If the user is logged in as the author of the entry and the entry is still unpublished; or if the user is logged in as admin; offer a delete button to delete the entry 
	echo '<INPUT TYPE="button" value="Delete" onClick="location=\'glossary_delete.php?pkey='.$g->search_query_pkey.'&glossary_pkey='.$g->glossary_pkey.'\'">  ';
} // if      
if ($admin==1 && $published==0) { //If the user is logged in as admin and the entry is still unpublished, show a publish button
	echo '<INPUT TYPE="button" value="Publish" onClick="location=\'glossary_publish.php?pkey='.$g->search_query_pkey.'&glossary_pkey='.$g->glossary_pkey.'\'">  ';
} // if      
if ((($author==1 && $published==0)) || $published==1 || ($admin==1) ) { // If the entry is published, or if the user is logged in as admin, display it 
echo '<A href="search.php?pkey='.$g->search_query_pkey.'&glossary_pkey='.$g->glossary_pkey.'">'.$g->glossary_name.'</a>&nbsp;'.$g->description.'<br><br>';				
} // if
} // foreach glossary
} // if count(glossary)>0
} // foreach letter   

include ('includes/ks_footer.html');
?>
