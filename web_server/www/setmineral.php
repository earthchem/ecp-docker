<?PHP
/**
 * setmineral.php
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
$searchquery=$db->get_row("select mineralname from search_query where pkey = $pkey") or die('could not fetch search_query record in setreference.php');

$mymaterial=$searchquery->material;
if(!is_numeric($mymaterial)){
	$mymaterial="1,4";
}

?>
<H1>Set Mineral Name</H1>
<FORM id='setmineral' action='searchupdate.php' method='post'>
	<TABLE class='ku_grid'>
		<TR>
			<TD><H4>MINERAL NAME</H4>
			</TD>
			<TD>
				
				
				<select name="mineralname">
					<option value="">Select...
					<option value="amphibole"<?if($searchquery->mineralname=="amphibole"){echo " selected ";}?>>Amphibole
					<option value="apatite"<?if($searchquery->mineralname=="apatite"){echo " selected ";}?>>Apatite
					<option value="augite"<?if($searchquery->mineralname=="augite"){echo " selected ";}?>>Augite
					<option value="biotite"<?if($searchquery->mineralname=="biotite"){echo " selected ";}?>>Biotite
					<option value="bronzite"<?if($searchquery->mineralname=="bronzite"){echo " selected ";}?>>Bronzite
					<option value="chlorite"<?if($searchquery->mineralname=="chlorite"){echo " selected ";}?>>Chlorite
					<option value="clinopyroxene"<?if($searchquery->mineralname=="clinopyroxene"){echo " selected ";}?>>Clinopyroxene
					<option value="feldspar"<?if($searchquery->mineralname=="feldspar"){echo " selected ";}?>>Feldspar
					<option value="ferrohedenbergite"<?if($searchquery->mineralname=="ferrohedenbergite"){echo " selected ";}?>>Ferrohedenbergite
					<option value="galena"<?if($searchquery->mineralname=="galena"){echo " selected ";}?>>Galena
					<option value="garnet"<?if($searchquery->mineralname=="garnet"){echo " selected ";}?>>Garnet
					<option value="glass"<?if($searchquery->mineralname=="glass"){echo " selected ";}?>>Glass
					<option value="groundmass"<?if($searchquery->mineralname=="groundmass"){echo " selected ";}?>>Groundmass
					<option value="hornblende"<?if($searchquery->mineralname=="hornblende"){echo " selected ";}?>>Hornblende
					<option value="hypersthene"<?if($searchquery->mineralname=="hypersthene"){echo " selected ";}?>>Hypersthene
					<option value="ilmenite"<?if($searchquery->mineralname=="ilmenite"){echo " selected ";}?>>Ilmenite
					<option value="leucite"<?if($searchquery->mineralname=="leucite"){echo " selected ";}?>>Leucite
					<option value="magnetite"<?if($searchquery->mineralname=="magnetite"){echo " selected ";}?>>Magnetite
					<option value="olivine"<?if($searchquery->mineralname=="olivine"){echo " selected ";}?>>Olivine
					<option value="orthopyroxene"<?if($searchquery->mineralname=="orthopyroxene"){echo " selected ";}?>>Orthopyroxene
					<option value="plagioclase"<?if($searchquery->mineralname=="plagioclase"){echo " selected ";}?>>Plagioclase
					<option value="pumice"<?if($searchquery->mineralname=="pumice"){echo " selected ";}?>>Pumice
					<option value="pyroxene"<?if($searchquery->mineralname=="pyroxene"){echo " selected ";}?>>Pyroxene
					<option value="quartz"<?if($searchquery->mineralname=="quartz"){echo " selected ";}?>>Quartz
					<option value="sanidine"<?if($searchquery->mineralname=="sanidine"){echo " selected ";}?>>Sanidine
					<option value="sillimanite"<?if($searchquery->mineralname=="sillimanite"){echo " selected ";}?>>Sillimanite
					<option value="spinel"<?if($searchquery->mineralname=="spinel"){echo " selected ";}?>>Spinel
					<option value="tourmaline"<?if($searchquery->mineralname=="tourmaline"){echo " selected ";}?>>Tourmaline
					<option value="zircon"<?if($searchquery->mineralname=="zircon"){echo " selected ";}?>>Zircon
				</select>
				
				
				
				
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
