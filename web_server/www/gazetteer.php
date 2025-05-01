<?PHP
/**
 * gazetteer.php
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

include('includes/ks_head.html');
include('get_pkey.php');


if($_POST['word']==""){
?>
		<br><br><br>
		<center>
		
		<table bgcolor="#000000" width="350px" cellpadding="0" cellspacing="1">
		<TR>
		<TD style="padding-left:5px;" bgcolor="#FB8F4E">
		<div style="font-size:12px;font-weight:bold;">ENTER SEARCH TERM:</div>
		</TD>
		</TR>
		
		<TR>
		<TD style="padding-left:5px;padding-top:5px;padding-bottom:5px;" bgcolor="#FFFFFF">
		<center>
			<form action="gazetteer.php" method="post">
			<input type="text" name="word"><br><br>
			<input type="hidden" name="pkey" value="<?=$pkey?>">
			<input type="submit" value="Submit" name="submit">
			</form>
		</center>
		<div align="center" style="font-size:12px;">... for example, "congo" or "kansas" or "lima"...</div>
		</TD>
		</TR>
		</table>
		
		</td>
		</tr>
		</table>

<?
	include('includes/ks_footer.html');
	exit();
}

		$myword=$_POST['word'];
		
		$myword=urlencode($myword);
		
		$myurl="http://api.geonames.org/search?q=".$myword."&maxRows=20&lang=en&username=earthchem&style=full";
		
		$mycontents=file_get_contents($myurl);
		
		//echo $mycontents;exit();
		
		$dom = new DomDocument();
		
		$dom->loadXML("$mycontents");
		
		$rows=$dom->getElementsByTagName("geoname");
				
?>

				<table bgcolor="#FFFFFF" width="640px" cellpadding="0" cellspacing="1">
				<tr>
				<td style="padding-top:5px;padding-bottom:5px;"><a href="gazetteer.php?pkey=<?=$pkey?>"><img src="images/newsearchterm.gif" border="0"></td>
				<td style="padding-top:5px;padding-bottom:5px;">
				<!---
				<div align="right"><a href="search.cfm?pkey=#pkey#"><img src="images/cancel.gif">
				--->
				</td>
				</tr>
				</table>
				
				<table bgcolor="#000000" width="640px" cellpadding="0" cellspacing="1">
				
				<TR>
				<TD style="padding-left:5px;" colspan="3" bgcolor="#FB8F4E">
				<div style="font-size:12px;font-weight:bold;">PLEASE CHOOSE FROM THE FOLLOWING RESULTS:</div>
				</TD>
				</TR>
				


<?
				$i=1;
				
				if(sizeof($rows)>0){
				
					foreach($rows as $row){
				

						$names=$row->getElementsByTagName("name");
						foreach($names as $name){
							$myname=$name->textContent;
						}						
						
						$countrynames=$row->getElementsByTagName("countryName");
						foreach($countrynames as $countryname){
							$mycountryname=$countryname->textContent;
						}						
						
						$fclnames=$row->getElementsByTagName("fclName");
						foreach($fclnames as $fclname){
							$myfclname=$fclname->textContent;
						}
						
						$description = "$myname : $mycountryname : $myfclname";					
						
						$bboxs=$row->getElementsByTagName("bbox");
						foreach($bboxs as $bbox){

							$wests=$bbox->getElementsByTagName("west");
							foreach($wests as $west){
								$longitudewest=$west->textContent;
							}

							$norths=$bbox->getElementsByTagName("north");
							foreach($norths as $north){
								$latitudenorth=$north->textContent;
							}

							$easts=$bbox->getElementsByTagName("east");
							foreach($easts as $east){
								$longitudeeast=$east->textContent;
							}

							$souths=$bbox->getElementsByTagName("south");
							foreach($souths as $south){
								$latitudesouth=$south->textContent;
							}


						}						
						

					

?>

						<TR>
					
					
					
						<TD style="padding-left:5px;padding-top:5px;" bgcolor="#FFFFFF" valign="top">
						<div style="font-size:12px;"><?=$description?></div>
						</TD>
					
						<TD style="padding-top:5px;padding-bottom:5px;" width="210px" bgcolor="#FFFFFF"><!--- start of image td --->
						<center>
					
					
					
						<img src="gazetteerpoly.php?north=<?=$latitudenorth?>&south=<?=$latitudesouth?>&east=<?=$longitudeeast?>&west=<?=$longitudewest?>">
					
					
						<!--- ************************************* --->
					
						</center>
						</td> <!--- end of image td --->
					
						<TD bgcolor="#FFFFFF">
					
						<center>
						<form name="form<?=$i?>" action="searchupdate.php" method="post">
						<input type="hidden" name="pkey" value="<?=$pkey?>">
						<input type="hidden" name="latitudenorth" value="<?=$latitudenorth?>">
						<input type="hidden" name="latitudesouth" value="<?=$latitudesouth?>">
						<input type="hidden" name="longitudewest" value="<?=$longitudewest?>">
						<input type="hidden" name="longitudeeast" value="<?=$longitudeeast?>">
						<input type="submit" value="Submit" name="submit">
						</form>
						</center>
					
						</TD>
					
						</TR>



	<?

				
					$i++;
				
					}//end foreach result
					
				}else{
					echo "<tr bgcolor=\"#FFFFFF\"><td> No results found for search term \"".$_POST['word']."\". Please try again.</td></tr>";
				}
				
				?></TABLE><?
		





?>