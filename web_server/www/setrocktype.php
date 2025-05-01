<?PHP
/**
 * setrocktype.php
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
include('get_pkey.php'); // get the primary key for the current search
include('db.php'); // load database drivers, and connect 
$searchquery=$db->get_row('SELECT rocktype FROM search_query WHERE pkey = '.$pkey) or die ('could not select from search_query table in setrocktype.php'); 
$rocktype=$searchquery->ROCKTYPE;
?>

<script type="text/javascript">
	$('#loadinganimation').hide();
</script>

<script language="JavaScript1.2" type="text/javascript">
function showpaper() {
	document.getElementById("blank").style.display = "none";
	document.getElementById("paper").style.display = "block";
	document.getElementById("tas").style.display = "none";
	document.getElementById("ech").style.display = "none";
	return;
}
function showtas() {
	document.getElementById("blank").style.display = "none";
	document.getElementById("paper").style.display = "none";
	document.getElementById("tas").style.display = "block";
	document.getElementById("ech").style.display = "none";
	return;
}
function showech() {
	document.getElementById("blank").style.display = "none";
	document.getElementById("paper").style.display = "none";
	document.getElementById("tas").style.display = "none";
	document.getElementById("ech").style.display = "block";
	return;
}
</script>
<SCRIPT type="text/javascript" src="autocomplete/prototype.js"></SCRIPT>
<SCRIPT type="text/javascript" src="ajaxb.js"></SCRIPT>
<SCRIPT type="text/javascript" src="sarissa.js"></SCRIPT>

<H1>Set Sample Type</H1>

<table>
<tr>

<td width="200px;" valign="top">

<table>
<tr><td><input type="radio" name="stypechooser" value="" onclick="javascript:changerock('igneous');" ></td><td>Igneous&nbsp;Rocks</td></tr>
<tr><td><input type="radio" name="stypechooser" value="" onclick="javascript:changerock('metamorphic');" ></td><td>Metamorphic&nbsp;Rocks</td></tr>
<tr><td><input type="radio" name="stypechooser" value="" onclick="javascript:changerock('alteration');" ></td><td>Alteration&nbsp;Products</td></tr>
<tr><td><input type="radio" name="stypechooser" value="" onclick="javascript:changerock('vein');" ></td><td>Vein</td></tr>
<tr><td><input type="radio" name="stypechooser" value="" onclick="javascript:changerock('ore');" ></td><td>Ore</td></tr>
<tr><td><input type="radio" name="stypechooser" value="" onclick="javascript:changerock('sedimentary');" ></td><td>Sedimentary</td></tr>
<tr><td><input type="radio" name="stypechooser" value="" onclick="javascript:changerock('xenolith');" ></td><td>Xenolith</td></tr>
</table>






</td>

<td width="500px;" valign="top">

	<div id="igneous" style="display:none;">
		<h4>Igneous Rocks: <br><br>
		<input type="radio" name="itemtype" value="vehicle"  onclick="showpaper()"> Names assigned by collector or author<br>
		<input type="radio" name="itemtype" value="vehicle"  onclick="showtas()"> Names calculated from chemistry using TAS classifications<br>
		<input type="radio" name="itemtype" value="vehicle"  onclick="showech()"> Names from EarthChem Categories
		</h4>
		
		
		
		<div id="paper" style="display:none;">

		<script src="autocomplete/effects.js" type="text/javascript"></script>
		<script src="autocomplete/controls.js" type="text/javascript"></script>
		<link rel="stylesheet" href="autocomplete/style.css" type="text/css" />
		
		<br>
		
		<h4>Igneous Rock Name:</h4>
		
		<div id="container">
			<form method="POST" action="searchupdate.php">
			<input type="text" id="rockname" name="rockname" autocomplete="off" class="input" value="" />
			<span id="rockindicator" style="display: none">
				<img src="rockspinner.gif" alt="Working..." />
			</span>
			<input type="submit" name="submit" value="Submit">
			<br />
			<div id="update" style="display: none; position:relative;"></div>
					<INPUT type="hidden" name="pkey" value="<?=$pkey ?>">
					<INPUT type="hidden" name="glossary_pkey" value="<?=$glossary_pkey ?>">
			
			</form>
			<script type="text/javascript">
			//alert('bau');
			
			new Ajax.Autocompleter("rockname", "update", "rocknameresponse.php", {
			  //paramName: "value", 
			  minChars: 1, 
			  //updateElement: addItemToList, 
			  indicator: 'rockindicator'
			});
			
			</script>
		</div>
		
		
		
		<br><hr><br>
		
		
		
		<?
		//$myrocks=$db->get_results("select rockname from autorocknames order by rockname");
		$myrocks=$db->get_results("select distinct(data4) as rockname 
									from raw_rock_translation where data1='igneous' 
									and data4 !=''
									order by rockname");
		?>
		
			<h4>Igneous Rock Name:</h4>
			<div style="background-color:#DDDDDD;padding:5px;">
		
				<form method="POST" action="searchupdate.php">
				<table>
					<tr>
						<td>
							<select name="rocknamegroup[]" size="12" style="width:200px;" id="rocknamegroup" multiple>
								<?
								foreach($myrocks as $r){
									echo "<option value=\"$r->rockname\">$r->rockname";
								}
								?>
							</select>
						</td>
						<td>
							<center>
							<input type="button" value=">>" onclick="javascript:addrocks();"><br><br>
							<input type="button" value="<<" onclick="javascript:removerocks();"><br><br>
							<input type="button" value="CLEAR" onclick="javascript:clearrocks();">
							</center>
						</td>
						<td>
							<select name="rockchosengroup[]" size="12" style="width:200px;" id="rockchosengroup" multiple>
								
							</select>
						</td>
					</tr>
				</table>
				<div style="text-align:right;">
				<input type="submit" value="Submit">
				</div>
				<INPUT type="hidden" name="hiddenrocknames" id="hiddenrocknames" value="">
				<INPUT type="hidden" name="level1" id="level1" value="igneous">
				<INPUT type="hidden" name="pkey" value="<?=$pkey ?>">
				<INPUT type="hidden" name="glossary_pkey" value="<?=$glossary_pkey ?>">
				</form>
			</div>
			
			<!--- <input type="button" value="Show Rocks" onclick="javascript:showrocks();"> --->
		
		</div> <!--- end of paper div --->
		
		
		
		<div id="tas" style="display:none;">
			
				<TABLE class="ku_nogrid_outer">
					<TR>
						<TD >
							<h4>
							<form name="tastypeform">
							<input type="radio" id="tastype" name="tastype" value="volcanic" onclick="javascript:changetas('volcanic');"> Volcanic Tas Classification <br>
							<input type="radio" id="tastype" name="tastype" value="plutonic" onclick="javascript:changetas('plutonic');"> Plutonic Tas Classification <br>
							</form>
							</h4>
							
							<br>
							
							<DIV name="volcanictasdiv" id="volcanictasdiv" style="display:none;"><h4>Volcanic TAS Name</h4>
								<FORM action="searchupdate.php" method="post">
								<SELECT size="12" name="tasname[]" id="tasname" multiple>
								<OPTION value="ANDESITE">ANDESITE
								<OPTION value="BASALT">BASALT
								<OPTION value="BASALTIC-ANDESITE">BASALTIC-ANDESITE
								<OPTION value="BASALTIC-TRACHYANDESITE">BASALTIC-TRACHYANDESITE
								<OPTION value="DACITE">DACITE
								<OPTION value="FOIDITE">FOIDITE
								<OPTION value="PHONOLITE">PHONOLITE
								<OPTION value="PHONOTEPHRITE">PHONOTEPHRITE
								<OPTION value="PICRO-BASALT">PICRO-BASALT
								<OPTION value="RYOLITE">RHYOLITE
								<OPTION value="TEPHRIPHONOLITE">TEPHRIPHONOLITE
								<OPTION value="TEPHRITE-BASANITE">TEPHRITE-BASANITE
								<OPTION value="TRACHITE">TRACHITE
								<OPTION value="TRACHYANDESITE">TRACHYANDESITE
								<OPTION value="TRACHYBASALT">TRACHYBASALT
								</SELECT><br>
								<INPUT type="hidden" name="pkey" value="<?=$pkey ?>">
								<INPUT type="hidden" name="glossary_pkey" value="<?=$glossary_pkey ?>">
								<input type="submit" value="Submit">
								</FORM>
							</DIV>
							<DIV name="plutonictasdiv" id="plutonictasdiv" style="display:none;"><h4>Plutonic TAS Name</h4>
								<FORM action="searchupdate.php" method="post">
								<SELECT size="12" name="tasname[]" id="tasname" multiple>
								<OPTION value="ALKALIC GABBRO">ALKALIC GABBRO
								<OPTION value="DIORITE">DIORITE
								<OPTION value="FOID GABBRO">FOID GABBRO
								<OPTION value="FOID MONZODIORITE">FOID MONZODIORITE
								<OPTION value="FOID MONZOSYENITE">FOID MONZOSYENITE
								<OPTION value="FOIDOLITE">FOIDOLITE
								<OPTION value="FOID SYENITE">FOID SYENITE
								<OPTION value="GABBROIC DIORITE">GABBROIC DIORITE
								<OPTION value="GRANITE">GRANITE
								<OPTION value="GRANODIORITE">GRANODIORITE
								<OPTION value="MONZODIORITE">MONZODIORITE
								<OPTION value="MONZOGABBRO">MONZOGABBRO
								<OPTION value="MONZONITE">MONZONITE
								<OPTION value="PERIDOTGABBRO">PERIDOTGABBRO
								<OPTION value="QUARTZ MONZONITE">QUARTZ MONZONITE
								<OPTION value="QUARTZOLITE">QUARTZOLITE
								<OPTION value="SUBALKALIC GABBRO">SUBALKALIC GABBRO
								<OPTION value="SYENITE">SYENITE
								</SELECT><br>
								<INPUT type="hidden" name="pkey" value="<?=$pkey ?>">
								<INPUT type="hidden" name="glossary_pkey" value="<?=$glossary_pkey ?>">
								<input type="submit" value="Submit">
								</FORM>
							</DIV>
						</TD>
					</TR>
				</TABLE>

			</FORM>
		</div> <!--- end of tas dif --->
		
		<div id="ech" style="display:none;"><br><br>
			<h4>Igneous EarthChem Categories</h4><br><br>
			
			<?
			//$myrocks=$db->get_results("select rockname from autorocknames order by rockname");
			$myseds=$db->get_results("select distinct(hierarchy) as rockname 
										from ech where data1='igneous' 
										and hierarchy !='' order by hierarchy
										");
			?>
	
			
				<div style="background-color:#DDDDDD;padding:5px;">
			
					<form method="POST" action="searchupdate.php">
					<table>
						<tr>
							<td>
								<select name="igneousechnamegrp[]" size="12" style="width:300px;" id="igneousechnamegrp" multiple>
									<?
									foreach($myseds as $r){
										echo "<option value=\"$r->rockname\">$r->rockname";
									}
									?>
								</select>
							</td>
							<td>
								<center>
								<input type="button" value=">>" onclick="javascript:addnames('igneousech');"><br><br>
								<input type="button" value="<<" onclick="javascript:removenames('igneousech');"><br><br>
								<input type="button" value="CLEAR" onclick="javascript:clearnames('igneousech');">
								</center>
							</td>
							<td>
								<select name="igneousechchosengrp[]" size="12" style="width:300px;" id="igneousechchosengrp" multiple>
									
								</select>
							</td>
						</tr>
					</table>
					<div style="text-align:right;">
					<input type="submit" value="Submit">
					</div>
					<INPUT type="hidden" name="igneousechhidden" id="igneousechhidden" value="">
					<INPUT type="hidden" name="pkey" value="<?=$pkey ?>">
					<INPUT type="hidden" name="glossary_pkey" value="<?=$glossary_pkey ?>">
					</form>
				</div>
			
			
		</div> <!--- end of ech dif --->
	
	</div>

	<div id="metamorphic" style="display:none;">
		<h4>Metamorphic Rocks:<br><br></h4>

			<h4>Metamorphic Rock Name:</h4>
			
			<div id="container">
				<form method="POST" action="searchupdate.php">
				<input type="text" id="metamorphicname" name="rockname" autocomplete="off" class="input" value="" />
				<span id="metamorphicindicator" style="display: none">
					<img src="rockspinner.gif" alt="Working..." />
				</span>
				<input type="submit" name="submit" value="Submit">
				<br />
				<div id="metamorphicupdate" style="display: none; position:relative;"></div>
						<INPUT type="hidden" name="pkey" value="<?=$pkey ?>">
						<INPUT type="hidden" name="glossary_pkey" value="<?=$glossary_pkey ?>">
				
				</form>
				<script type="text/javascript">
				//alert('bau');
				
				new Ajax.Autocompleter("metamorphicname", "metamorphicupdate", "metamorphicrocknameresponse.php", {
				  //paramName: "value", 
				  minChars: 1, 
				  //updateElement: addItemToList, 
				  indicator: 'metamorphicindicator'
				});
				
				</script>
			</div>

			<br>
			<hr>
			<br>


			<?
			//$myrocks=$db->get_results("select rockname from autorocknames order by rockname");
			$myseds=$db->get_results("select distinct(data4) as rockname 
										from raw_rock_translation where data1='metamorphic' 
										and data4 !=''
										order by rockname");
			?>
	
			<h4>Metamorphic Rock Name:</h4>
				<div style="background-color:#DDDDDD;padding:5px;">
			
					<form method="POST" action="searchupdate.php">
					<table>
						<tr>
							<td>
								<select name="metamorphicnamegrp[]" size="12" style="width:200px;" id="metamorphicnamegrp" multiple>
									<?
									foreach($myseds as $r){
										echo "<option value=\"$r->rockname\">$r->rockname";
									}
									?>
								</select>
							</td>
							<td>
								<center>
								<input type="button" value=">>" onclick="javascript:addnames('metamorphic');"><br><br>
								<input type="button" value="<<" onclick="javascript:removenames('metamorphic');"><br><br>
								<input type="button" value="CLEAR" onclick="javascript:clearnames('metamorphic');">
								</center>
							</td>
							<td>
								<select name="metamorphicchosengrp[]" size="12" style="width:200px;" id="metamorphicchosengrp" multiple>
									
								</select>
							</td>
						</tr>
					</table>
					<div style="text-align:right;">
					<input type="submit" value="Submit">
					</div>
					<INPUT type="hidden" name="metamorphichidden" id="metamorphichidden" value="">
					<INPUT type="hidden" name="level1" id="level1" value="metamorphic">
					<INPUT type="hidden" name="pkey" value="<?=$pkey ?>">
					<INPUT type="hidden" name="glossary_pkey" value="<?=$glossary_pkey ?>">
					</form>
				</div>
			

			<div id="debug">
			<br>
			<form method="POST" action="searchupdate.php">
				<h4>All Metamorphic Rock Samples:</h4>
					<INPUT TYPE=CHECKBOX NAME="metamorphic">  Check this box to return all metamorphic rock samples.
					<input type="submit" value="Submit">
					<INPUT type="hidden" name="pkey" value="<?=$pkey ?>">
					<INPUT type="hidden" name="glossary_pkey" value="<?=$glossary_pkey ?>">
			</form>
			</div>





		</div>

	</div>

	<div id="alteration" style="display:none;">
		<h4>Alteration Products:<br><br></h4>

			<h4>Alteration Product Name:</h4>
			
			<div id="container">
				<form method="POST" action="searchupdate.php">
				<input type="text" id="alterationname" name="rockname" autocomplete="off" class="input" value="" />
				<span id="alterationindicator" style="display: none">
					<img src="rockspinner.gif" alt="Working..." />
				</span>
				<input type="submit" name="submit" value="Submit">
				<br />
				<div id="alterationupdate" style="display: none; position:relative;"></div>
						<INPUT type="hidden" name="pkey" value="<?=$pkey ?>">
						<INPUT type="hidden" name="glossary_pkey" value="<?=$glossary_pkey ?>">
				
				</form>
				<script type="text/javascript">
				//alert('bau');
				
				new Ajax.Autocompleter("alterationname", "alterationupdate", "alterationrocknameresponse.php", {
				  //paramName: "value", 
				  minChars: 1, 
				  //updateElement: addItemToList, 
				  indicator: 'alterationindicator'
				});
				
				</script>
			</div>

			<br>
			<hr>
			<br>


			<?
			//$myrocks=$db->get_results("select rockname from autorocknames order by rockname");
			$myseds=$db->get_results("select distinct(data4) as rockname 
										from raw_rock_translation where data1='alteration' 
										and data4 !=''
										order by rockname");
			?>
	
			<h4>Alteration Product Name:</h4>
				<div style="background-color:#DDDDDD;padding:5px;">
			
					<form method="POST" action="searchupdate.php">
					<table>
						<tr>
							<td>
								<select name="alterationnamegrp[]" size="12" style="width:200px;" id="alterationnamegrp" multiple>
									<?
									foreach($myseds as $r){
										echo "<option value=\"$r->rockname\">$r->rockname";
									}
									?>
								</select>
							</td>
							<td>
								<center>
								<input type="button" value=">>" onclick="javascript:addnames('alteration');"><br><br>
								<input type="button" value="<<" onclick="javascript:removenames('alteration');"><br><br>
								<input type="button" value="CLEAR" onclick="javascript:clearnames('alteration');">
								</center>
							</td>
							<td>
								<select name="alterationchosengrp[]" size="12" style="width:200px;" id="alterationchosengrp" multiple>
									
								</select>
							</td>
						</tr>
					</table>
					<div style="text-align:right;">
					<input type="submit" value="Submit">
					</div>
					<INPUT type="hidden" name="alterationhidden" id="alterationhidden" value="">
					<INPUT type="hidden" name="level1" id="level1" value="alteration">
					<INPUT type="hidden" name="pkey" value="<?=$pkey ?>">
					<INPUT type="hidden" name="glossary_pkey" value="<?=$glossary_pkey ?>">
					</form>
				</div>

		</div>

	</div>

	<div id="vein" style="display:none;">
		<h4>Vein<br><br></h4>
		
			<h4>Vein Name:</h4>
			
			<div id="container">
				<form method="POST" action="searchupdate.php">
				<input type="text" id="veinname" name="rockname" autocomplete="off" class="input" value="" />
				<span id="veinindicator" style="display: none">
					<img src="rockspinner.gif" alt="Working..." />
				</span>
				<input type="submit" name="submit" value="Submit">
				<br />
				<div id="veinupdate" style="display: none; position:relative;"></div>
						<INPUT type="hidden" name="pkey" value="<?=$pkey ?>">
						<INPUT type="hidden" name="glossary_pkey" value="<?=$glossary_pkey ?>">
				
				</form>
				<script type="text/javascript">
				//alert('bau');
				
				new Ajax.Autocompleter("veinname", "veinupdate", "veinrocknameresponse.php", {
				  //paramName: "value", 
				  minChars: 1, 
				  //updateElement: addItemToList, 
				  indicator: 'veinindicator'
				});
				
				</script>
			</div>

			<br>
			<hr>
			<br>


			<?
			//$myrocks=$db->get_results("select rockname from autorocknames order by rockname");
			$myseds=$db->get_results("select distinct(data4) as rockname 
										from raw_rock_translation where data1='vein' 
										and data4 !=''
										order by rockname");
			?>
	
			<h4>Vein Name:</h4>
				<div style="background-color:#DDDDDD;padding:5px;">
			
					<form method="POST" action="searchupdate.php">
					<table>
						<tr>
							<td>
								<select name="veinnamegrp[]" size="12" style="width:200px;" id="veinnamegrp" multiple>
									<?
									foreach($myseds as $r){
										echo "<option value=\"$r->rockname\">$r->rockname";
									}
									?>
								</select>
							</td>
							<td>
								<center>
								<input type="button" value=">>" onclick="javascript:addnames('vein');"><br><br>
								<input type="button" value="<<" onclick="javascript:removenames('vein');"><br><br>
								<input type="button" value="CLEAR" onclick="javascript:clearnames('vein');">
								</center>
							</td>
							<td>
								<select name="veinchosengrp[]" size="12" style="width:200px;" id="veinchosengrp" multiple>
									
								</select>
							</td>
						</tr>
					</table>
					<div style="text-align:right;">
					<input type="submit" value="Submit">
					</div>
					<INPUT type="hidden" name="veinhidden" id="veinhidden" value="">
					<INPUT type="hidden" name="level1" id="level1" value="vein">
					<INPUT type="hidden" name="pkey" value="<?=$pkey ?>">
					<INPUT type="hidden" name="glossary_pkey" value="<?=$glossary_pkey ?>">
					</form>
				</div>

		</div>
		
	</div>

	<div id="ore" style="display:none;">
		<h4>Ore<br><br></h4>
		
			<h4>Ore Name:</h4>
			
			<div id="container">
				<form method="POST" action="searchupdate.php">
				<input type="text" id="orename" name="rockname" autocomplete="off" class="input" value="" />
				<span id="oreindicator" style="display: none">
					<img src="rockspinner.gif" alt="Working..." />
				</span>
				<input type="submit" name="submit" value="Submit">
				<br />
				<div id="oreupdate" style="display: none; position:relative;"></div>
						<INPUT type="hidden" name="pkey" value="<?=$pkey ?>">
						<INPUT type="hidden" name="glossary_pkey" value="<?=$glossary_pkey ?>">
				
				</form>
				<script type="text/javascript">
				//alert('bau');
				
				new Ajax.Autocompleter("orename", "oreupdate", "orerocknameresponse.php", {
				  //paramName: "value", 
				  minChars: 1, 
				  //updateElement: addItemToList, 
				  indicator: 'oreindicator'
				});
				
				</script>
			</div>

			<br>
			<hr>
			<br>


			<?
			//$myrocks=$db->get_results("select rockname from autorocknames order by rockname");
			$myseds=$db->get_results("select distinct(data4) as rockname 
										from raw_rock_translation where data1='ore' 
										and data4 !=''
										order by rockname");
			?>
	
			<h4>Ore Name:</h4>
				<div style="background-color:#DDDDDD;padding:5px;">
			
					<form method="POST" action="searchupdate.php">
					<table>
						<tr>
							<td>
								<select name="orenamegrp[]" size="12" style="width:200px;" id="orenamegrp" multiple>
									<?
									foreach($myseds as $r){
										echo "<option value=\"$r->rockname\">$r->rockname";
									}
									?>
								</select>
							</td>
							<td>
								<center>
								<input type="button" value=">>" onclick="javascript:addnames('ore');"><br><br>
								<input type="button" value="<<" onclick="javascript:removenames('ore');"><br><br>
								<input type="button" value="CLEAR" onclick="javascript:clearnames('ore');">
								</center>
							</td>
							<td>
								<select name="orechosengrp[]" size="12" style="width:200px;" id="orechosengrp" multiple>
									
								</select>
							</td>
						</tr>
					</table>
					<div style="text-align:right;">
					<input type="submit" value="Submit">
					</div>
					<INPUT type="hidden" name="orehidden" id="orehidden" value="">
					<INPUT type="hidden" name="level1" id="level1" value="ore">
					<INPUT type="hidden" name="pkey" value="<?=$pkey ?>">
					<INPUT type="hidden" name="glossary_pkey" value="<?=$glossary_pkey ?>">
					</form>
				</div>

		</div>
		
	</div>

	<div id="sedimentary" style="display:none;">
		
		<h4>
		<input type="radio" name="sedtype" value="vehicle"  onclick="sedtypeswitch('author')"> Names assigned by collector or author<br>
		<input type="radio" name="sedtype" value="vehicle"  onclick="sedtypeswitch('ech')"> Names from EarthChem Categories
		</h4>
		
		<br><br>
		
		<div id="sedauthor" style="display:none;">



			<h4>Sedimentary Rock Name:</h4>
			
			<div id="container">
				<form method="POST" action="searchupdate.php">
				<input type="text" id="sedname" name="rockname" autocomplete="off" class="input" value="" />
				<span id="sedindicator" style="display: none">
					<img src="rockspinner.gif" alt="Working..." />
				</span>
				<input type="submit" name="submit" value="Submit">
				<br />
				<div id="sedupdate" style="display: none; position:relative;"></div>
						<INPUT type="hidden" name="pkey" value="<?=$pkey ?>">
						<INPUT type="hidden" name="glossary_pkey" value="<?=$glossary_pkey ?>">
				
				</form>
				<script type="text/javascript">
				//alert('bau');
				
				new Ajax.Autocompleter("sedname", "sedupdate", "sedrocknameresponse.php", {
				  //paramName: "value", 
				  minChars: 1, 
				  //updateElement: addItemToList, 
				  indicator: 'sedindicator'
				});
				
				</script>
			</div>

			<br>
			<hr>
			<br>


			<?
			//$myrocks=$db->get_results("select rockname from autorocknames order by rockname");
			$myseds=$db->get_results("select distinct(data4) as rockname 
										from raw_rock_translation where data1='sedimentary' 
										and data4 !=''
										order by rockname");
			?>
	
			<h4>Sedimentary Rock Name:</h4>
				<div style="background-color:#DDDDDD;padding:5px;">
			
					<form method="POST" action="searchupdate.php">
					<table>
						<tr>
							<td>
								<select name="sednamegroup[]" size="12" style="width:200px;" id="sednamegroup" multiple>
									<?
									foreach($myseds as $r){
										echo "<option value=\"$r->rockname\">$r->rockname";
									}
									?>
								</select>
							</td>
							<td>
								<center>
								<input type="button" value=">>" onclick="javascript:addseds();"><br><br>
								<input type="button" value="<<" onclick="javascript:removeseds();"><br><br>
								<input type="button" value="CLEAR" onclick="javascript:clearseds();">
								</center>
							</td>
							<td>
								<select name="sedchosengroup[]" size="12" style="width:200px;" id="sedchosengroup" multiple>
									
								</select>
							</td>
						</tr>
					</table>
					<div style="text-align:right;">
					<input type="submit" value="Submit">
					</div>
					<INPUT type="hidden" name="hiddensednames" id="hiddensednames" value="">
					<INPUT type="hidden" name="level1" id="level1" value="sedimentary">
					<INPUT type="hidden" name="pkey" value="<?=$pkey ?>">
					<INPUT type="hidden" name="glossary_pkey" value="<?=$glossary_pkey ?>">
					</form>
				</div>

		</div>
		
		<div id="sedech" style="display:none;">
		
			<h4>Sedimentary Rock Name:</h4>
			
			<?
			//$myrocks=$db->get_results("select rockname from autorocknames order by rockname");
			$myseds=$db->get_results("select distinct(hierarchy) as rockname 
										from ech where data1='sedimentary' 
										and hierarchy !=''
										");
			?>
	
			
				<div style="background-color:#DDDDDD;padding:5px;">
			
					<form method="POST" action="searchupdate.php">
					<table>
						<tr>
							<td>
								<select name="sedimentaryechnamegrp[]" size="12" style="width:300px;" id="sedimentaryechnamegrp" multiple>
									<?
									foreach($myseds as $r){
										echo "<option value=\"$r->rockname\">$r->rockname";
									}
									?>
								</select>
							</td>
							<td>
								<center>
								<input type="button" value=">>" onclick="javascript:addnames('sedimentaryech');"><br><br>
								<input type="button" value="<<" onclick="javascript:removenames('sedimentaryech');"><br><br>
								<input type="button" value="CLEAR" onclick="javascript:clearnames('sedimentaryech');">
								</center>
							</td>
							<td>
								<select name="sedimentaryechchosengrp[]" size="12" style="width:300px;" id="sedimentaryechchosengrp" multiple>
									
								</select>
							</td>
						</tr>
					</table>
					<div style="text-align:right;">
					<input type="submit" value="Submit">
					</div>
					<INPUT type="hidden" name="sedimentaryechhidden" id="sedimentaryechhidden" value="">
					<INPUT type="hidden" name="pkey" value="<?=$pkey ?>">
					<INPUT type="hidden" name="glossary_pkey" value="<?=$glossary_pkey ?>">
					</form>
				</div>
			
		</div>

	</div>

	<div id="xenolith" style="display:none;">
		<h4>Xenolith<br><br></h4>
		
			<h4>Xenolith Name:</h4>
			
			<div id="container">
				<form method="POST" action="searchupdate.php">
				<input type="text" id="xenolithname" name="rockname" autocomplete="off" class="input" value="" />
				<span id="xenolithindicator" style="display: none">
					<img src="rockspinner.gif" alt="Working..." />
				</span>
				<input type="submit" name="submit" value="Submit">
				<br />
				<div id="xenolithupdate" style="display: none; position:relative;"></div>
						<INPUT type="hidden" name="pkey" value="<?=$pkey ?>">
						<INPUT type="hidden" name="glossary_pkey" value="<?=$glossary_pkey ?>">
				
				</form>
				<script type="text/javascript">
				//alert('bau');
				
				new Ajax.Autocompleter("xenolithname", "xenolithupdate", "xenolithrocknameresponse.php", {
				  //paramName: "value", 
				  minChars: 1, 
				  //updateElement: addItemToList, 
				  indicator: 'xenolithindicator'
				});
				
				</script>
			</div>

			<br>
			<hr>
			<br>


			<?
			//$myrocks=$db->get_results("select rockname from autorocknames order by rockname");
			$myseds=$db->get_results("select distinct(data4) as rockname 
										from raw_rock_translation where data1='xenolith' 
										and data4 !=''
										order by rockname");
			?>
	
			<h4>Xenolith Name:</h4>
				<div style="background-color:#DDDDDD;padding:5px;">
			
					<form method="POST" action="searchupdate.php">
					<table>
						<tr>
							<td>
								<select name="xenolithnamegrp[]" size="12" style="width:200px;" id="xenolithnamegrp" multiple>
									<?
									foreach($myseds as $r){
										echo "<option value=\"$r->rockname\">$r->rockname";
									}
									?>
								</select>
							</td>
							<td>
								<center>
								<input type="button" value=">>" onclick="javascript:addnames('xenolith');"><br><br>
								<input type="button" value="<<" onclick="javascript:removenames('xenolith');"><br><br>
								<input type="button" value="CLEAR" onclick="javascript:clearnames('xenolith');">
								</center>
							</td>
							<td>
								<select name="xenolithchosengrp[]" size="12" style="width:200px;" id="xenolithchosengrp" multiple>
									
								</select>
							</td>
						</tr>
					</table>
					<div style="text-align:right;">
					<input type="submit" value="Submit">
					</div>
					<INPUT type="hidden" name="xenolithhidden" id="xenolithhidden" value="">
					<INPUT type="hidden" name="level1" id="level1" value="xenolith">
					<INPUT type="hidden" name="pkey" value="<?=$pkey ?>">
					<INPUT type="hidden" name="glossary_pkey" value="<?=$glossary_pkey ?>">
					</form>
				</div>

		</div>
		
	</div>



</td>
</tr>
</table>



<div id="blank" style="height:500px;display:block;">
</div> <!--- end of blank vertical spacer div --->


<?php
include('includes/ks_footer.html');
?>
<!---
sedimentary stuff:
			<div style="background-color:#DDDDDD;padding:5px;">
		
				<form method="POST" action="searchupdate.php">
				<table>
					<tr>
						<td>
							<select name="sednamegroup[]" size="12" style="width:200px;" id="sednamegroup" multiple>
								<?
								foreach($myseds as $r){
									echo "<option value=\"$r->rockname\">$r->rockname";
								}
								?>
							</select>
						</td>
						<td>
							<center>
							<input type="button" value=">>" onclick="javascript:addseds();"><br><br>
							<input type="button" value="<<" onclick="javascript:removeseds();"><br><br>
							<input type="button" value="CLEAR" onclick="javascript:clearseds();">
							</center>
						</td>
						<td>
							<select name="sedchosengroup[]" size="12" style="width:200px;" id="sedchosengroup" multiple>
								
							</select>
						</td>
					</tr>
				</table>
				<div style="text-align:right;">
				<input type="submit" value="Submit">
				</div>
				<INPUT type="hidden" name="hiddensednames" id="hiddensednames" value="">
				<INPUT type="hidden" name="pkey" value="<?=$pkey ?>">
				<INPUT type="hidden" name="glossary_pkey" value="<?=$glossary_pkey ?>">
				</form>
			</div>
--->