<?PHP
/**
 * setsampleid.php
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
$searchquery=$db->get_row("select sampleid from search_query where pkey = $pkey") or die('could not fetch search_query record in setreference.php');

?>
<H1>Set Sample ID</H1>
<!--
<br>
<div style="border:1px dashed #BBBBBB; width:800px; padding:5px; background:#EEEEEE;">
The keyword query searches a generic descriptor field contained in the EarthChem schema. This is a free-text field 
that allows the supported datasets to provide a descriptor of their own choosing. Most often, this field contains
a short sample description describing the sample's location and composition. The keyword search also includes rock name
in the queried results.
</div>
-->
<br>

<FORM id='setreference' action='searchupdate.php' method='post'>
	<TABLE class='ku_grid'>

		<TR>
			<TD><H4>SAMPLE ID</H4>
			</TD>
			<TD>
				<INPUT name='sampleid' type='text' id='sampleid' value='<?=$searchquery->sampleid ?>'>
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