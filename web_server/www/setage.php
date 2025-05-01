<?PHP
/**
 * setage.php
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
$searchquery=$db->get_row("select age,age_min,age_max,ageexists from search_query where pkey = $pkey") or die('could not fetch search_query record in setreference.php');

$georows=$db->get_results("select * from geoages order by pkey");

$ageset=$db->get_var("select geoage from search_query where pkey=$pkey");

?>
<H1>Set Age</H1>
<FORM id='setmineral' action='searchupdate.php' method='post'>
	<TABLE class='ku_grid'>
		<TR>
			<TD><H4>Age Span</H4>
			</TD>
			<TD>
			Min: <input type="text" name="age_min" size="5" value="<?=$searchquery->age_min?>"> Ma
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			Max: <input type="text" name="age_max" size="5" value="<?=$searchquery->age_max?>"> Ma
			</TD>
		</TR>
		<TR>
		<TD style='border:none' colspan="2"><div align="center">or</div></TD>
		</TR>
		<TR>
			<TD><H4>Exact Age</H4>
			</TD>
			<TD>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="text" name="age" size="5" value="<?=$searchquery->age?>"> Ma
			</TD>
		</TR>
		<TR>
		<TD style='border:none' colspan="2"><div align="center">or</div></TD>
		</TR>
		<TR>
			<TD><H4>Geological Age</H4>
			</TD>
			<TD>
			<select name="geoage">
			<option value="">Select Age...
			<?
			foreach($georows as $g){
				$selected="";
				if($ageset==$g->pkey){
					$selected=" selected";
				}
				echo "<option value=\"".$g->pkey."\"$selected> ".$g->agename." ".$g->agelabel."\n";
			}
			?>
			</select>
			</TD>
		</TR>
		<TR>
		<TD style='border:none' colspan="2"><div align="center">or</div></TD>
		</TR>
		<TR>
			<TD><H4>Age Exists</H4>
			</TD>
			<TD>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="checkbox" name="ageexists" value="yes" <?if($searchquery->ageexists=="yes"){echo "checked";}?>><br>
			Use this option to return all samples that have age values associated with them.
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