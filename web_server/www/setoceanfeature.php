<?PHP
/**
 * setoceanfeature.php
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
$searchquery=$db->get_row("select oceanfeature from search_query where pkey = $pkey") or die('could not fetch search_query record in setreference.php');

?>

<script TYPE="text/javascript">
<!--
function go()
{
	newstring=document.getElementById('myselect').value;
	document.getElementById('texty').innerHTML=document.getElementById('myselect').options[document.getElementById('myselect').options.selectedIndex].text;
	document.volfield.src='oceanfeaturepoly.php?gids='+newstring;
	document.volfieldworld.src='oceanfeaturepoly.php?gids='+newstring+'&world=yes';
	document.getElementById('textyworld').innerHTML='Overview';
}
//-->
</SCRIPT>


<H1>Set Ocean Feature Name</H1>

<br>
<!--
<div style="border:1px dashed #BBBBBB; width:800px; padding:5px; background:#EEEEEE;">
&nbsp;
</div>
<br>
-->

<?

$setgid=$searchquery->oceanfeature;

$mygeos=$db->get_results("select gid, name from oceanfeatures where showsearch=TRUE order by name");

?>

<FORM id='setreference' action='searchupdate.php' method='post'>
	<TABLE class='ku_gridgeo' border=0>
		<TR>
			<TD valign="top"><H4>OCEAN FEATURE NAME</H4></TD>
		<TR>
		<TR>
			<TD valign="top">
				<SELECT id="myselect" SIZE=11 Name = "oceanfeature" onChange="go()">
<?
foreach($mygeos as $mygeo){
?>
					<OPTION VALUE="<?=$mygeo->gid?>"<?if($setgid==$mygeo->gid){echo " selected";}?>><?=ucwords(strtolower($mygeo->name))?>
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
</FORM>
<?php
include 'includes/ks_footer.html';
?>