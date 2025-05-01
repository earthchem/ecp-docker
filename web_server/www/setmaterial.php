<?PHP
/**
 * setmaterial.php
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
$searchquery=$db->get_row("select author, title, journal, advkeyword1, yearmin, yearmax, material from search_query where pkey = $pkey") or die('could not fetch search_query record in setreference.php');

$mymaterial=$searchquery->material;
if(!is_numeric($mymaterial)){
	$mymaterial="1,4";
}

?>
<H1>Set Material</H1>
<FORM id='setreference' action='searchupdate.php' method='post'>
	<TABLE class='ku_grid'>
		<TR>
			<TD><H4>MATERIAL</H4>
			</TD>
			<TD>
				
				
				<input name="material" type="radio" value="1,4" <?if($mymaterial=="1,4"){?>checked="checked"<?}?> /> Bulk ( Whole Rock / Rock / Glass / Groundmass )<br>
				<input name="material" type="radio" value="1" <?if($mymaterial=="1"){?>checked="checked"<?}?> /> Whole Rock / Rock<br>
				<input name="material" type="radio" value="4" <?if($mymaterial=="4"){?>checked="checked"<?}?> /> Glass<br>
				<input name="material" type="radio" value="2" <?if($mymaterial=="2"){?>checked="checked"<?}?> /> Inclusion<br>
				<?
				/*
				<input name="material" type="radio" value="2" <?if($mymaterial=="2"){?>checked="checked"<?}?> /> Glass<br>
				<input name="material" type="radio" value="2,4" <?if($mymaterial=="2,4"){?>checked="checked"<?}?> /> Glass / Inclusion<br>
				*/
				?>
				<input name="material" type="radio" value="3" <?if($mymaterial=="3"){?>checked="checked"<?}?> /> Mineral<br>


				
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