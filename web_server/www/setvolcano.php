<?PHP
/**
 * setvolcano.php
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

// This page lets the user set parameters for the reference: author, title, journal, publication year (as a range or an exact year), key word.  
include('get_pkey.php'); // get primary key for search 
include('db.php'); // database drivers, and connect
include 'includes/ks_head.html';
//echo "<br>pkey is $pkey "; 
$searchquery=$db->get_row("select volcano from search_query where pkey = $pkey") or die('could not fetch search_query record in setreference.php');

$setgid=$searchquery->geology;

$mygeos=$db->get_results("select gid, name, number from shapevolcano where showvolcano = TRUE order by name");


?>




<script TYPE="text/javascript">
<!--
function goss()
{
newstring=document.getElementById('myselect').value;
document.getElementById('texty').innerHTML=document.getElementById('myselect').options[document.getElementById('myselect').options.selectedIndex].text;
document.volfield.src='volcanoimage.php?gids='+newstring;
document.volfieldworld.src='volcanoimage.php?gids='+newstring+'&world=yes';
document.getElementById('textyworld').innerHTML='Overview';
}


var volcanonumbers = [];

<?foreach($mygeos as $mygeo){?>
volcanonumbers[<?=$mygeo->gid?>]='<?=$mygeo->number?>';
<?}?>

function go()
{
	document.getElementById('texty').innerHTML='go';
	var volcanosel = document.getElementById('myselect');
	var volcanolist='';
	var volcanodelim='';
	var volcanoname='';
	var volcanotext='';
	var volcanotextdelim='';
	var volcanonum='';
	
	var i;
	var z=1;
	for (i = volcanosel.length - 1; i>=0; i--) {
		if (volcanosel.options[i].selected) {

			volcanonum = volcanosel.options[i].value;
			volcanolist = volcanolist+volcanodelim+volcanosel.options[i].value;
			volcanodelim=',';
			
			volcanoname = volcanosel.options[i].text;
			volcanotext = volcanotext+volcanotextdelim+volcanoname+'&nbsp;&nbsp;(<a href="http://volcano.si.edu/volcano.cfm?vn='+volcanonumbers[volcanonum]+'" target="_blank">Smithsonian Link</a>)';
			volcanotextdelim='<br>';

		}
	}

	//alert(volcanolist);
	document.getElementById('volcano').value=volcanolist;
	document.volfield.src='volcanoimage.php?gids='+volcanolist;
	document.volfieldworld.src='volcanoimage.php?gids='+volcanolist+'&world=yes';
	document.getElementById('texty').innerHTML=volcanotext;
	document.getElementById('textyworld').innerHTML='Overview';

}







//-->
</SCRIPT>


<H1>Set Volcano Name</H1>

<br>

<div style="border:1px dashed #BBBBBB; width:800px; padding:5px; background:#FFFFFF;">
<table>
	<tr>
		<td>
			<img src="silogo.gif" border="0">
		</td>
		<td>
This information comes from the Global Volcanism Program of the Smithsonian Institution (<a href="http://www.volcano.si.edu/" target="_blank">http://www.volcano.si.edu/</a>)
		</td>
	</tr>
</table>
</div>
<br>


<?


?>

<FORM id='setreference' action='searchupdate.php' method='post'>
	<TABLE class='ku_gridgeo' border=0>
		<TR>
			<TD valign="top"><H4>VOLCANO NAME</H4></TD>
		</TR>
		<TR>

			<TD valign="top">
				<SELECT id="myselect" SIZE=11 Name = "volcanofoo" MULTIPLE onChange="go()">
<?
foreach($mygeos as $mygeo){
?>
					<OPTION VALUE="<?=$mygeo->gid?>"<?if($setgid==$mygeo->gid){echo " selected";}?>><?=$mygeo->name?>
					
<?
}
?>
				</SELECT>
			</TD>
			<TD valign="top">
				<img name="volfield" id="volfield" src="blank.png" width="300" height="225">
				<div align="center" id="texty"></div>
			</TD>
			<TD valign="top">
				<img name="volfieldworld" id="volfieldworld" src="blank.png" width="300" height="225">
				<div align="center" id="textyworld"></div>
			</TD>
		</TR>
		<TR>
			<TD style='border:none' colspan='2' align='right' style='text-align:right'>
				<input type="submit" value="Submit" name="submit">
			</TD>
		</TR>
	</TABLE>
	<INPUT type='hidden' name='pkey' value='<?=$pkey?>'>
	<INPUT type='hidden' name='glossary_pkey' value='<?=$glossary_pkey?>'>
	<input type="hidden" name="volcano" id="volcano">
</FORM>
<?php
include 'includes/ks_footer.html';
?>