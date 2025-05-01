<?PHP
/**
 * refoutput.php
 *
 * longdesc
 *
 * LICENSE: This source file is subject to version 4.0 of the Creative Commons
 * license that is available through the world-wide-web at the following URI:
 * https://creativecommons.org/licenses/by/4.0/
 *
 * @category   Geochemistry
 * @package    ECDB Search
 * @author     Jason Ash <jasonash@ku.edu>
 * @copyright  IEDA (http://www.iedadata.org/)
 * @license    https://creativecommons.org/licenses/by/4.0/  Creative Commons License 4.0
 * @version    GitHub: $
 * @link       https://www.iedadata.org
 * @see        EarthChem, Geochemistry
 */

include('includes/ks_head.html');



include "get_pkey.php";

include("db.php");

include("datasources.php");

include('srcwhere.php');

include('queries.php');

?>
<div id="debug" style="display:none;"><?=nl2br($refstring)?></div>
<?

//http://newsearch.earthchemportal.org/searchbyreference?cpkey=195

//echo nl2br($refstring);

$rows=$db->get_results("$refstring");

if($db->num_rows > 0){

	?>
	<TABLE style="width:100%" class="ku_htmloutput">
	
  	<TR>
  		<td colspan=1 style='width:200px;border:none;vertical-align:bottom'>
       	<h1>References:</h1>

  		<form name='getrefs' action='refoutputxls.php'>
  		<input type="hidden" name="pkey" value="<?=$pkey?>">
  		<input type="submit" class="submitbutton" name="submit1" value="Download References in XLS Spreadsheet">
  		</form>
  		
  		</td>
  	</TR>
	
	<tr>
	<th><h4>TITLE</h4></th>
	<th><h4>JOURNAL</h4></th>
	<th><h4>AUTHOR</h4></th>
	<th><h4>PUB&nbsp;YEAR</h4></th>
	</tr>
	<?



	$rownum=1;
	foreach($rows as $row){

	if ($rownum == 1) { // alternate background colors for the rows, for readability
		$bgcolor='white';
		$rownum=2;
	} else {
		$bgcolor='#FFF5F5';//FDE6C2
		$rownum=1;
	}

	?>
	<tr bgcolor="<?=$bgcolor?>">
	<!--<td><a href="/searchbyreference?cpkey=<?=$row->citation_pkey?>"><?=$row->title?></a></td>-->
	<!--<td><a href="<?=$row->citation_url?>" target="_blank"><?=$row->title?></a></td>-->
	<td><?=$row->title?></td>
	<td><?=$row->journal?></td>
	<td><?=strtoupper($row->author)?></td>
	<td><?=$row->pubyear?></td>
	</tr>
	<?


	}

	?>
	</table>
	<?

}else{

	echo "No results found.";

}


include ('includes/ks_footer.html');
?>