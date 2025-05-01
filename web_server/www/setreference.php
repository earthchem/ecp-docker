<?PHP
/**
 * setreference.php
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
$searchquery=$db->get_row("select doi, author, title, journal, advkeyword1, yearmin, yearmax from search_query where pkey = $pkey") or die('could not fetch search_query record in setreference.php');

?>
<H1>Set Reference Data</H1>
<FORM id='setreference' action='searchupdate.php' method='post'>
	<TABLE class='ku_grid'>
		<TR>
			<TD><H4>			AUTHOR
</H4>
			</TD>
			<TD>
				<INPUT name='author' type='text' id='author' value='<?=$searchquery->author; ?>'>
			</TD>
		</TR>
		<TR>
			<TD><H4>			TITLE
</H4>
			</TD>
			<TD>
				<INPUT name='title' type='text' id='title' value='<?=$searchquery->title ?>'>
			</TD>
		</TR>
		<TR>
			<TD><H4>			JOURNAL
</H4>
			</TD>
			<TD>
				<INPUT name='journal' type='text' id='journal' value='<?=$searchquery->journal ?>'>
			</TD>
		</TR>
		<TR>
			<TD><H4>			DOI
</H4>
			</TD>
			<TD>
				<INPUT name='doi' type='text' id='doi' value='<?=$searchquery->doi ?>'>
			</TD>
		</TR>
<?
/*
		<TR>
			<TD><H4>			KEYWORD
</H4>
			</TD>
			<TD>
				<INPUT name='advkeyword1' type='text' id='advkeyword1' value='<?=$searchquery->advkeyword1 ?>'> <font size=1>word or exact phrase</font>
			</TD>
		</TR>
*/
?>
		<TR valign='top'>
			<TD><H4>			PUBLICATION&nbsp;YEAR&nbsp;
</H4>
			</TD>
			<TD>
				<TABLE class='ku_nogrid'>
					<TR>
						<TD>EXACT&nbsp;YEAR:
						</TD>
						<TD>
							<INPUT name='year' type='text' id='year' size='5' value=''>
						</TD>
						<TD>&nbsp;
<I>						or
</I>							&nbsp;
						</TD>
						<TD>MIN:&nbsp;
							<INPUT name='yearmin' type='text' id='yearmin' size='5' value='<?=$searchquery->YEARMIN ?>'>
						</TD>
						<TD>MAX:&nbsp;
							<INPUT name='yearmax' type='text' id='yearmax' size='5' value='<?=$searchquery->YEARMAX ?>'>
						</TD>
					</TR>
				</TABLE>
			</TD>
		</TR>
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