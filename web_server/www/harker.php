<?PHP
/**
 * harker.php
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


include "includes/ks_head.html";

include("get_pkey.php");

$randnum=rand(10000,99999);

?>
	<h1>SVG Harker Diagrams</h1>
	<div style="width:800px;text-align:right;">
	<?
	echo "<input type=button value=\"Back to EarthChem Output\" onClick=\"window.location = 'http://www.earthchemportal.org/results.php?pkey=$pkey';\">";
	?>
	</div>
   <b>Values used for Harker Diagrams</b>
      <ul>
         <li>The Harker diagrams are made using a normalized major oxide dataset.</li>
         <li>For a sample to plot, it must contain data for SiO2, Fe (as FeO and/or Fe2O3), Al2O3, Na2O, K2O, CaO, MgO, and P2O5.</li>
         <li>Fe is converted to FeO (FeO*), and the oxides are normalized to a 100% volatile-free basis.</li>
      </ul>

	<table>
		<tr>
			<td style="padding-left:100px;padding-top:50px;"><img src="harkersvg.php?yaxis=al2o3&pkey=<?=$pkey?>&rand=<?=$randnum?>"/>
				<div align="center"><input type=button value="Download SVG File" onClick="window.location = '/harkersvg.php?yaxis=al2o3&pkey=<?=$_GET['pkey']?>&d=y';"></div>
			</td>
			<td style="padding-left:100px;padding-top:50px;"><img src="harkersvg.php?yaxis=cao&pkey=<?=$pkey?>&rand=<?=$randnum?>"/>
				<div align="center"><input type=button value="Download SVG File" onClick="window.location = '/harkersvg.php?yaxis=cao&pkey=<?=$_GET['pkey']?>&d=y';"></div>
			</td>
		</tr>
		<tr>
			<td style="padding-left:100px;padding-top:50px;"><img src="harkersvg.php?yaxis=feot&pkey=<?=$pkey?>&rand=<?=$randnum?>"/>
				<div align="center"><input type=button value="Download SVG File" onClick="window.location = '/harkersvg.php?yaxis=feot&pkey=<?=$_GET['pkey']?>&d=y';"></div>
			</td>
			<td style="padding-left:100px;padding-top:50px;"><img src="harkersvg.php?yaxis=k2o&pkey=<?=$pkey?>&rand=<?=$randnum?>"/>
				<div align="center"><input type=button value="Download SVG File" onClick="window.location = '/harkersvg.php?yaxis=k2o&pkey=<?=$_GET['pkey']?>&d=y';"></div>
			</td>
		</tr>
		<tr>
			<td style="padding-left:100px;padding-top:50px;"><img src="harkersvg.php?yaxis=mgo&pkey=<?=$pkey?>&rand=<?=$randnum?>"/>
				<div align="center"><input type=button value="Download SVG File" onClick="window.location = '/harkersvg.php?yaxis=mgo&pkey=<?=$_GET['pkey']?>&d=y';"></div>
			</td>
			<td style="padding-left:100px;padding-top:50px;"><img src="harkersvg.php?yaxis=na2o&pkey=<?=$pkey?>&rand=<?=$randnum?>"/>
				<div align="center"><input type=button value="Download SVG File" onClick="window.location = '/harkersvg.php?yaxis=na2o&pkey=<?=$_GET['pkey']?>&d=y';"></div>
			</td>
		</tr>
		<tr>
			<td style="padding-left:100px;padding-top:50px;"><img src="harkersvg.php?yaxis=p2o5&pkey=<?=$pkey?>&rand=<?=$randnum?>"/>
				<div align="center"><input type=button value="Download SVG File" onClick="window.location = '/harkersvg.php?yaxis=p2o5&pkey=<?=$_GET['pkey']?>&d=y';"></div>
			</td>
			<td style="padding-left:100px;padding-top:50px;"><img src="harkersvg.php?yaxis=tio2&pkey=<?=$pkey?>&rand=<?=$randnum?>"/>
				<div align="center"><input type=button value="Download SVG File" onClick="window.location = '/harkersvg.php?yaxis=tio2&pkey=<?=$_GET['pkey']?>&d=y';"></div>
			</td>
		</tr>
	</table>

<?php

include ("includes/ks_footer.html");

?>
