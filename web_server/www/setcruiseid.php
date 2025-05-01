<?PHP
/**
 * setcruiseid.php
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
$searchquery=$db->get_row("select cruiseid from search_query where pkey = $pkey") or die('could not fetch search_query record in setreference.php');

$rows=$db->get_results("select * from cruiseids order by pkey");

$thiscruiseid=$searchquery->cruiseid;

$cruisearray=explode(",",$thiscruiseid);

//print_r($cruisearray);

?>
<H1>Set Cruise ID</H1><br>
<div style="border:1px dashed #BBBBBB; width:500px; padding:5px; background:#EEEEEE;">
Select desired Cruise ID(s) from the list below. Multiple Cruise IDs may be selected<br>
by holding down the control key (Windows/Linux) or the command key (Mac).
</div>
<br>

<FORM id='setreference' action='searchupdate.php' method='post'>
	<TABLE class='ku_grid'>

		<TR>
			<TD valign="top">
				<H4>CRUISE ID</H4>

			</TD>
			<TD>
				

				<SELECT id="myselect" SIZE=11 Name = "cruiseid[]" MULTIPLE >
					<?
					foreach($rows as $row){
					
					//work out selected
					if(in_array($row->cruiseid,$cruisearray)){
						$selected=" SELECTED";
					}else{
						$selected="";
					}
					
					
					?>
					<OPTION VALUE="<?=$row->cruiseid?>"<?=$selected?>><?=$row->cruiseid?>
					
					<?
					}
					?>
				</SELECT>





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