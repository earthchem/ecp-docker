<?PHP
/**
 * savedqueries.php
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

if (!isset($user)) {
	header("Location:login.php?savedqueries=yes");
}

include('includes/ks_head.html');

//$userspkey=$_SESSION['users_pkey'];


?>

<h3>Saved Queries</h3><br>

<?
$myqueries=$db->get_results("select * from search_query where saveduser=$userpkey order by pkey");

//echo "select * from search_query where saveduser=$userpkey order by pkey";

if($db->num_rows > 0){

?>
<table class="ku_htmloutput">
<tr>
<td></th>
<td><h4>Query Name</h4></td>
<td><h4>Date Saved</h4></td>
<td></td>
</tr>
<?

	foreach($myqueries as $q){
		if ($row == 1) {
			$bgcolor="#FFFFFF";
			$row=2;
		} else {
			$bgcolor="#FDE6C2";
			$row=1;
		}
		

		?>
			<tr style="background-color:<?=$bgcolor?> valign=top;">
			<td><input type="button" value="View" onclick="window.location='search.php?pkey=<?=$q->pkey?>'"></td>
			<td><?=$q->queryname?></td>
			<td><?=$q->time_stamp?></td>
			<td><a href="deletesavedquery.php?pkey=<?=$q->pkey?>" OnClick="return confirm('Are you sure you want to delete <?=$q->queryname?>??');">delete</a></td>
			</tr>
		<?
	}
?>
</table>
<?

}else{

echo "You have no saved queries.";

}


include('includes/ks_footer.html');
?>