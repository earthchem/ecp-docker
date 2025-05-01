<?PHP
/**
 * meltsinterface.php
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

include("db.php");

if($_POST['temp']){
	$temp=$_POST['temp'];
}else{
	$temp=1200;
}

if($_POST['pressure']){
	$pressure=$_POST['pressure'];
}else{
	$pressure=1000;
}


$method_pkey=$_GET['method_pkey'];

if($method_pkey==""){
	echo "Error: Method pkey not provided.";exit();
}

$mrow=$db->get_row("select * from vmethreported where method_pkey=$method_pkey");

foreach($_POST as $key=>$value){
	if($key=="water"){
		$waterset="yes";
	}
}

if($waterset=="yes"){
	$h2o=$_POST['water'];
	//echo "post set";
}else{
	$h2o=$mrow->h2o;
}

//echo "h2o: $h2o<br>";

$sample_pkey=$mrow->sample_pkey;

if($sample_pkey==""){
	echo "Error: Sample not found.";exit();
}

$srow=$db->get_row("select * from sample where sample_pkey=$sample_pkey");

$sample_id=$srow->sample_id;

if($sample_id==""){
	echo "Error: Sample not found.";exit();
}

$varnames=array("sio2","tio2","al2o3","fe2o3","cr2o3","feo","mno","mgo","nio","coo","cao","na2o","k2o","p2o5","h2o");
$itemnames=array("SiO2","TiO2","Al2O3","Fe2O3","Cr2O3","FeO","MnO","MgO","NiO","CoO","CaO","Na2O","K2O","P2O5","H2O");


include('includes/ks_head.html');

?>
<h1>EarthChem/MELTS Interface</h1><br>

<div style="border:1px dashed #BBBBBB; width:800px; padding:5px; background:#EEEEEE;">
The EarthChem/MELTS interface allows users to interact with the MELTS software package.
MELTS is a software package designed to facilitate thermodynamic modeling of phase equilibria in magmatic systems. 
It provides the ability to compute equilibrium phase relations for igneous systems over the temperature range 500-2000 °C and the pressure range 0-2 GPa.
<div align="center">
More information can be found at <a href="http://melts.ofm-research.org/">http://melts.ofm-research.org/</a>.
</div>
</div><br>

<h3>Sample <?=$srow->sample_id?> has the following chemical values:</h3>

<table class="ku_htmloutput">
	<tr>
		<th><h4>SiO2</h4></th>
		<th><h4>TiO2</h4></th>
		<th><h4>Al2O3</h4></th>
		<th><h4>Fe2O3</h4></th>
		<th><h4>Cr2O3</h4></th>
		<th><h4>FeO</h4></th>
		<th><h4>MnO</h4></th>
		<th><h4>MgO</h4></th>
		<th><h4>NiO</h4></th>
		<th><h4>CoO</h4></th>
		<th><h4>CaO</h4></th>
		<th><h4>Na2O</h4></th>
		<th><h4>K2O</h4></th>
		<th><h4>P2O5</h4></th>
	</tr>
	<tr>
		<td><?=$mrow->sio2?></td>
		<td><?=$mrow->tio2?></td>
		<td><?=$mrow->al2o3?></td>
		<td><?=$mrow->fe2o3?></td>
		<td><?=$mrow->cr2o3?></td>
		<td><?=$mrow->feo?></td>
		<td><?=$mrow->mno?></td>
		<td><?=$mrow->mgo?></td>
		<td><?=$mrow->nio?></td>
		<td><?=$mrow->coo?></td>
		<td><?=$mrow->cao?></td>
		<td><?=$mrow->na2o?></td>
		<td><?=$mrow->k2o?></td>
		<td><?=$mrow->p2o5?></td>
	</tr>
</table>

<form method="POST">

<?
	if($mrow->h2o!=""){
		$showwater=$mrow->h2o;
	}else{
		$showwater="NULL";
	}
?>

<table>
	<tr>
		<td>
			<div style="font-size:1.2em;font-weight:bold;">H2O Value:</div>
			<input type="button" onclick='document.getElementById("water").value="";' value="Clear Value">&nbsp;<input id="water" name="water" type="text" size="4" value="<?=$h2o?>">
		</td>
		<td valign="bottom">
			<div style="font-size:.8em;">
				This value (<?=$showwater?>) was reported in the database, but it can be changed or cleared before submitting to the MELTS service.
			</div>
		</td>
	</tr>
</table>

<br><br>
<div style="font-size:1.2em;font-weight:bold;">Please provide the following:</div>

<table>
<tr><td>T (&deg;C) (600 - 2500)</td><td>&nbsp;&nbsp;&nbsp;<input type="text" name="temp" value="<?=$temp?>" size="5"><td></tr>
<tr><td>P (bars) (0 - 10K)</td><td>&nbsp;&nbsp;&nbsp;<input type="text" name="pressure" value="<?=$pressure?>" size="5"><td></tr>




<tr><td>fO2Path</td>
<td>&nbsp;&nbsp;
	<select name="fo2path">
		<?
		if(($mrow->fe2o3!="" && $mrow->feo=="") || ($mrow->feo!="" && $mrow->fe2o3=="")){
		?>
		<option value="nno"<?if($_POST['fo2path']=="nno"){echo " selected";}?>>nickel-nickel oxide buffer</option>
		<option value="fmq"<?if($_POST['fo2path']=="fmq"){echo " selected";}?>>Quartz, Fayalite, Magnetite buffer</option>
		<option value="coh"<?if($_POST['fo2path']=="coh"){echo " selected";}?>>carbon + water buffer</option>
		<option value="iw"<?if($_POST['fo2path']=="iw"){echo " selected";}?>>iron-wustite buffer</option>
		<option value="hm "<?if($_POST['fo2path']=="hm "){echo " selected";}?>>hematite-magnetite buffer</option>
		<?
		}else{
		?>
		<option value="none"<?if($_POST['fo2path']=="none"){echo " selected";}?>>None</option>
		<option value="fmq"<?if($_POST['fo2path']=="fmq"){echo " selected";}?>>Quartz, Fayalite, Magnetite buffer</option>
		<option value="coh"<?if($_POST['fo2path']=="coh"){echo " selected";}?>>carbon + water buffer</option>
		<option value="nno"<?if($_POST['fo2path']=="nno"){echo " selected";}?>>nickel-nickel oxide buffer</option>
		<option value="iw"<?if($_POST['fo2path']=="iw"){echo " selected";}?>>iron-wustite buffer</option>
		<option value="hm "<?if($_POST['fo2path']=="hm "){echo " selected";}?>>hematite-magnetite buffer</option>
		<?
		}
		?>
	</select>
<td></tr>





<tr><td colspan="2"><div align="center"><br><input type="submit" name="submit" value="Submit"></div></td></tr>
</table>
</form>

<?
if($_POST['submit']!=""){

	$temp=$_POST['temp'];
	$pressure=$_POST['pressure'];
	$fo2path=$_POST['fo2path'];

	//echo "fo2path: $fo2path<br>";

	$error="";



	//OK, look for temp and pressure
	
	if($temp=="" || $pressure==""){
		$error.="Temperature and pressure must be provided.<br>";
	}
	
	if($temp!=""){
		if($temp<600 || $temp>2500 || !is_numeric($temp)){
			$error.="Temperature must be between 600&deg;C and 2500&deg;C.<br>";
		}
	}

	if($pressure!=""){
		if($pressure<=0 || $pressure>10000 || !is_numeric($pressure)){
			$error.="Pressure must positive and less than 10,000.<br>";
		}
	}

	if($error==""){
	
		//curl the url
$formxml="<MELTSinput><initialize><SiO2>$mrow->sio2</SiO2><TiO2>$mrow->tio2</TiO2><Al2O3>$mrow->al2o3</Al2O3><Fe2O3>$mrow->fe2o3</Fe2O3><Cr2O3>$mrow->cr2o3</Cr2O3><FeO>$mrow->feo</FeO><MnO>$mrow->mno</MnO><MgO>$mrow->mgo</MgO><NiO>$mrow->nio</NiO><CoO>$mrow->coo</CoO><CaO>$mrow->cao</CaO><Na2O>$mrow->na2o</Na2O><K2O>$mrow->k2o</K2O><P2O5>$mrow->p2o5</P2O5><H2O>$h2o</H2O></initialize><calculationMode>equilibrate</calculationMode><title>EarthChem Web Service Call ($method_pkey)</title><constraints><setTP><initialT>$temp</initialT><initialP>$pressure</initialP><fo2path>$fo2path</fo2path></setTP></constraints></MELTSinput>";
//$formxml="<MELTSinput><initialize><SiO2>$mrow->sio2</SiO2><TiO2>$mrow->tio2</TiO2><Al2O3>$mrow->al2o3</Al2O3><Fe2O3>$mrow->fe2o3</Fe2O3><Cr2O3>$mrow->cr2o3</Cr2O3><FeO>$mrow->feo</FeO><MnO>$mrow->mno</MnO><MgO>$mrow->mgo</MgO><NiO>$mrow->nio</NiO><CoO>$mrow->coo</CoO><CaO>$mrow->cao</CaO><Na2O>$mrow->na2o</Na2O><K2O>$mrow->k2o</K2O><P2O5>$mrow->p2o5</P2O5><H2O>$h2o</H2O></initialize><calculationMode>equilibrate</calculationMode><title>EarthChem Web Service Call ($method_pkey)</title><constraints><setTP><initialT>$temp</initialT><initialP>$pressure</initialP></setTP></constraints></MELTSinput>";

//echo "$formxml";exit();

		/*
			data: formxml, 
			dataType:"xml", 
			cache:false, 
			contentType:"text/xml", 
		*/
		
		//url is: http://thermofit.ofm-research.org:8080/MELTSWSBxApp/Compute



		$ch = curl_init();

		//curl_setopt($ch, CURLOPT_URL,            "http://thermofit.ofm-research.org:8080/MELTSWSBxApp/Compute" );
		curl_setopt($ch, CURLOPT_URL,            "http://thermofit.ofm-research.org:8080/multiMELTSWSBxApp/Compute" );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt($ch, CURLOPT_POST,           1 );
		curl_setopt($ch, CURLOPT_POSTFIELDS,     "$formxml" ); 
		curl_setopt($ch, CURLOPT_HTTPHEADER,     array('Content-Type: text/xml')); 
		curl_setopt($ch, CURLOPT_TIMEOUT,     10); 

		
		$server_output = curl_exec ($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE); 

		curl_close ($ch);
		
		//echo "httpcode: $httpcode<br>";
		//echo "<pre>serveroutput: $server_output</pre>";
		
		if($httpcode!=200){
			echo "MELTS Server Error:<br>";
			echo $server_output;
		}

		if($server_output==""){
			$error.="Timeout. No MELTS server response.";
		}else{
		
			//server output is set, so let's parse it 
			
			$dom = new DomDocument();
			$dom->loadXML("$server_output");
			
			//first, look at status flag
			$statuses = $dom->getElementsByTagName("status");
			foreach($statuses as $status) {
				$mystatus=$status->textContent;
			}
			
			//echo "mystatus: $mystatus";
			
			if(strtolower(substr($mystatus,0,5))=="error"){
				$thiserror=str_replace("Error: ","",$mystatus);
				$error.=$thiserror;
			}else{
			
				//status is OK, let's parse for solids and potential solids.
				$showsolids=no;
				$solids = $dom->getElementsByTagName("solid");
				foreach($solids as $solid){
					$showsolids="yes";
				}
			
				if($showsolids=="yes"){
				
					?>
					<br><br>
					<h3>Solids:</h3>
					<table class="ku_htmloutput">
						<tr>
							<th><h4>Name</h4></th>
							<th><h4>Mass</h4></th>
							<th><h4>Formula</h4></th>
						</tr>
					<?
					
					foreach($solids as $solid){
						$names = $solid->getElementsByTagName("name");
						foreach($names as $name){
							$myname=$name->textContent;
						}

						$masses = $solid->getElementsByTagName("mass");
						foreach($masses as $mass){
							$mymass=$mass->textContent;
						}

						$formulas = $solid->getElementsByTagName("formula");
						foreach($formulas as $formula){
							$myformula=$formula->textContent;
						}
						
						?>
						<tr>
							<td><?=$myname?></td>
							<td><?=$mymass?></td>
							<td><?=$myformula?></td>
						</tr>
						<?
						
					}

					?>

					</table>
					<?
					
				}


				$showpotentialsolids=no;
				$potentialsolids = $dom->getElementsByTagName("potentialSolid");
				foreach($potentialsolids as $potentialsolid){
					$showpotentialsolids="yes";
				}
			
				if($showpotentialsolids=="yes"){
				
					?>
					<br><br>
					<h3>Potential Solids:</h3>
					<table class="ku_htmloutput">
						<tr>
							<th><h4>Name</h4></th>
							<th><h4>Affinity</h4></th>
							<th><h4>Formula</h4></th>
						</tr>
					<?
					
					foreach($potentialsolids as $potentialsolid){
						$names = $potentialsolid->getElementsByTagName("name");
						foreach($names as $name){
							$myname=$name->textContent;
						}

						$affinitys = $potentialsolid->getElementsByTagName("affinity");
						foreach($affinitys as $affinity){
							$myaffinity=$affinity->textContent;
						}

						$formulas = $potentialsolid->getElementsByTagName("formula");
						foreach($formulas as $formula){
							$myformula=$formula->textContent;
						}
						
						?>
						<tr>
							<td><?=$myname?></td>
							<td><?=$myaffinity?></td>
							<td><?=$myformula?></td>
						</tr>
						<?
						
					}

					?>

					</table>
					<?

				}







				

			
			}
		
		}



		
		//echo $server_output;

	}

	if($error!=""){
	
		echo "<div style=\"font-weight:bold;color:#FF0000;\">Error!<br>$error</div>";
	
	}

}










include('includes/ks_footer.html');
?>