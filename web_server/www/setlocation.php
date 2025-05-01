<?PHP
/**
 * setlocation.php
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


include("includes/ks_head.html"); // page header
include("get_pkey.php"); // get the primary key for the current search
include("db.php"); // database drivers, and connect
$search_query=$db->get_row("SELECT * from search_query where pkey = ".$pkey) or die ("could not execute search_query in setlocation.php"); 
$latitudenorth = $search_query->LATITUDENORTH;
$latitudesouth = $search_query->LATITUDESOUTH;
$longitudewest = $search_query->LONGITUDEWEST;
$longitudeeast = $search_query->LONGITUDEEAST;
$polygon=$search_query->POLYGON;
?><h1>Set Location Data
</h1><h4>Define location using any of the methods offered below.
</h4>
<table border="1" cellspacing="0" cellpadding="10">
	
	<?
	/*
	<tr>
		<td><a href="earthchem_bounding_box_map.php?pkey=<?=$pkey ?>&glossary_pkey=<?=$glossary_pkey ?>"><img src="images/intmap_box.gif" border="1" alt="bounding box"></a>
		</td>
		<td>1. Define two corners of a bounding box on an interactive map.
		</td>
	</tr>
	*/
	?>
	
	<tr>
		<td><a href="earthchem_polygon_map.php?pkey=<?=$pkey ?>&glossary_pkey=<?=$glossary_pkey ?>"><img src="images/intmap_polygon.gif" border="1" alt="polygon map"></a>
		</td>
		<td>1. Define a polygon on an interactive map.
		</td>
	</tr>

	<tr>
		<td><a href="earthchem_south_polar_polygon_map.php?pkey=<?=$pkey ?>&glossary_pkey=<?=$glossary_pkey ?>"><img src="images/int_polar_map_polygon.gif" border="1" alt="polar polygon map"></a>
		</td>
		<td>2. Define a polygon on a southern polar interactive map.
		</td>
	</tr>

	<!---
	<tr>
		<td><a href="gazetteer.php?pkey=<?=$pkey ?>&glossary_pkey=<?=$glossary_pkey ?>"><img src="images/gazetteer.jpg" border="1" alt="gazzateer">
		</td>
		<td>2. Choose terrestrial location using a text-based gazetteer. (Ocean features are mostly missing from this gazetteer at present.)
		</td>
	</tr>
	--->


	<tr><td></td>
		<td><br>3. Define a bounding box with north, south, west, east
			<form action="searchupdate.php" method="post">
				<table class="ku_grid"><!--
					<TR>
						<TD colspan="4" bgcolor="#FB8F4E">4. Define a bounding box with north, south, west, east
						</TD>
					</TR>-->
					<tr>
						<td colspan="2" width="50%" align="center"><h4>						Latitude
</h4>							-90 to 90 (degrees)
						</td>
						<td colspan="2" width="50%" align="center"><h4>						Longitude
</h4>							-180 to 180 (degrees)
						</td>
					</tr>
					<tr>
						<td align="right">Northern Bound
						</td>
						<td>
							<input name="latitudenorth" tabindex="3" type="text" id="latitudenorth" size="5" value="<?=$latitudenorth ?>">
						</td>

						<td align="right">Eastern Bound
						</td>
						<td>
							<input name="longitudeeast" tabindex="6" type="text" id="longitudeeast" size="5" value="<?=$longitudeeast?>">
						</td>
					</tr>
					<tr>
						<td align="right">Southern Bound
						</td>
						<td>
							<input name="latitudesouth" tabindex="4" type="text" id="latitudesouth" size="5" value="<?=$latitudesouth?>">
						</td>
						<td align="right">Western Bound
						</td>
						<td>
							<input name="longitudewest" tabindex="5" type="text" id="longitudewest" size="5" value="<?=$longitudewest?>">
						</td>
					</tr>
					<tr>
						<td style="border-style:none" colspan="7" align="right">
							<input type="submit" value="Submit" name="submit">
						</td>
					</tr>
				</table>
				<input type="hidden" name="pkey" value="<?=$pkey ?>">
				<input type="hidden" name="glossary_pkey" value="<?=$glossary_pkey?>">
			</form>
		</td>
	</tr>
	<tr><td></td>
		<td><br>4. Define a polygon with longitude/latitude pairs. <br><br>
			
			<form action="searchupdate.php" method="post">
				<table class="ku_nogrid">

					<tr>
						<td valign="top">
							<p/>Enter a longitude/latitude pair for each vertice of an envelope or polygon.
							<br/>Separate the longitude/latitude pairs with semicolons.
							<br/>Enter the pairs in a sequence that traces the perimeter of a polygon.
							<br/>From 3 to 25 pairs are allowed.
							<br>For&nbsp;example:<br><font size="1">-109.6&nbsp;41.2; &nbsp;&nbsp; -105.8&nbsp;45.2; &nbsp;&nbsp; -101.6&nbsp;41.1; &nbsp;&nbsp; -101.88&nbsp;26.20; &nbsp;&nbsp; -109.9 36.3</font>
							<br/>
							<?php
							$polygon=$search_query->POLYGON;
							//$polygon=str_replace(' ','&nbsp;',$polygon);
							$polygon=str_replace(';',';  ',$polygon); // extra space for more legible display
							?>							
							<textarea tabindex="8" name="latitude_longitude_data" rows="8" cols="60" wrap="virtual" style="font-size:10px;padding:10"><?=$polygon?></textarea>
							<input type="hidden" name="pkey" value="<?=$pkey ?>">
							<input type="hidden" name="glossary_pkey" value="<?=$glossary_pkey ?>">
						</td>
					</tr>
					<tr>
						<td style="border-style:none" align="right">
							<input type="submit" value="Submit" name="submit">
						</td>
					</tr>
				</table>
			</form>
		</td>
	</tr>

</table>

<a href="gazetteer.php?pkey=<?=$pkey ?>&glossary_pkey=<?=$glossary_pkey ?>">.</a<

<?php
include("includes/ks_footer.html");
?>