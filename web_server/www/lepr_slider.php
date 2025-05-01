<?PHP
/**
 * lepr_slider.php
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

	if(is_numeric($_GET['material_pkey'])){
		$material_pkey=$_GET['material_pkey'];
	}
	
	if($material_pkey==""){
		exit();
	}

	$myrow=$db->get_row("select * from vmatreported where material_pkey=$material_pkey limit 1");
	
	//$itemarray=array('sio2','al2o3','cao','mgo','na2o','k2o','p2o5','mno','tio2','total');
	$itemarray=array('sio2','al2o3','cao','mgo','na2o','k2o','p2o5','mno','tio2','feo');
	
	/*
	foreach($itemarray as $item){
	
		$dbname="n_feo_".$item;
		$val=$myrow->$dbname;
		
		if($val > 0 and $val != ""){		
			$minval=$val*.95;
			$maxval=$val*1.05;
			
			//echo "$item_min=$minval&$item=$val&$item_max=$maxval<br>";
			
			$urlstring=$urlstring."&".$item."_min=$minval&$item=$val&".$item."_max=$maxval";
			
			//echo $item." = ".$myrow->$dbname."<br>";
			//$urlstring=$urlstring."&".$item."=".$myrow->$dbname;
		}
	}
	
	$contentstring="http://lepr.ofm-research.org/YUI/Webservices/ws_LEPRcount?type=bulk".$urlstring;
	
	echo $contentstring;

	echo "<br><br><br>";
	*/

	$neighborstring="http://lepr.ofm-research.org/YUI/Webservices/ws_LEPR_NNRsearch.php?type=bulk";

	$contentstring="";
	$urlstring="";
	foreach($itemarray as $item){
	
		if($item=="feo"){
			$item="feot";
		}
	
		$dbname="n_feo_".$item;
		$val=$myrow->$dbname;

		if($item=="feot"){
			$item="feo";
		}

		if($val > 0 and $val != ""){
			
			//build string to find neighbor here
			$neighborstring.="&".$item."=".$val;
			
			
			$minval=$val*.95;
			$maxval=$val*1.05;
			
			//echo "$item_min=$minval&$item=$val&$item_max=$maxval<br>";
			
			for($x=0;$x<=1000;$x=$x+5){
				$num=$x/10;
				if($minval > $num){
					$useminval=$num;
				}
			}
			
			for($x=1000;$x>=0;$x=$x-5){
				$num=$x/10;
				if($maxval < $num){
					$usemaxval=$num;
				}
			}
			
			
			
			//echo $item." = ".$myrow->$dbname."<br>";
			//$urlstring=$urlstring."&".$item."=".$myrow->$dbname;
			
			$minvals["$item"]=$useminval*2;
			$maxvals["$item"]=($usemaxval*2)+20;
			$checkedvals["$item"]=" checked ";

			//$urlstring=$urlstring."&".$item."_min=$useminval&$item=$val&".$item."_max=$usemaxval";
			$urlstring=$urlstring."&".$item."_min=$useminval&".$item."_max=$usemaxval";
		
		}else{
		
			$minvals["$item"]=0;
			$maxvals["$item"]=220;
			$checkedvals["$item"]="";
		}
	}
	
	$contentstring="http://lepr.ofm-research.org/YUI/Webservices/ws_LEPRcount?type=bulk".$urlstring;
	$linkstring="http://lepr.ofm-research.org/YUI/Webservices/ws_LEPRsearch?type=bulk".$urlstring;

	//$contentstring="http://lepr.ofm-research.org/Webservices/ws_LEPRcount?type=bulk&sio2_min=0&sio2_max=100&al2o3_min=0&al2o3_max=100&cao_min=0&cao_max=100&mgo_min=0.0&mgo_max=100&na2o_min=0.0&na2o_max=100.0&k2o_min=0.0&k2o_max=100.0&p2o5_min=0.0&p2o5_max=100&mno_min=0.0&mno_max=100.0&feo_min=0.0&feo_max=100.0";
	
	//echo "content string: $contentstring";
	
	$content = file_get_contents($contentstring); 
	
	if($content > 0){
		$countstring = "$content samples found.<div style=\"font-size:.5em;\">Click <a href=\"$linkstring\" target=\"_blank\">Here</a> to see these samples at LEPR</div>";
	}else{
		$countstring = "No samples found.<div style=\"font-size:.5em;padding-left:10px;\">Adjust sliders or turn off oxides<br>above to broaden your search.</div>";
	}

	//echo $contentstring;

	//echo "<br><br><br>";


	//echo $neighborstring."<br><br>";
	
	$neighborresults=file_get_contents($neighborstring); 

	$neighborparts=split(";",$neighborresults);
	//print_r($neighborparts);

	//echo "<br><br>count = ".count($neighborparts);
	
	for($x=1;$x<count($neighborparts);$x++){
		//echo "$x <br>";
		$itemvalpair=split(":",$neighborparts[$x]);
		$itemname=$itemvalpair[0];
		$itemval=$itemvalpair[1];
		$neighboritems[$itemname]=$itemval;
	}

	//print_r($neighboritems);










	//header("Location:$urlstring");
	//exit();





include('includes/ks_head.html');
?>


<style type="text/css">
table.sample {
	border-width: 1px 1px 1px 1px;
	border-spacing: 0px;
	border-style: solid solid solid solid;
	border-color: black black black black;
	border-collapse: collapse;
	background-color: white;
}
table.sample th {
	border-width: 1px 1px 1px 1px;
	padding: 3px 3px 3px 3px;
	border-style: inset inset inset inset;
	border-color: gray gray gray gray;
	background-color: white;
	-moz-border-radius: 0px 0px 0px 0px;
}
table.sample td {
	border-width: 1px 1px 1px 1px;
	padding: 3px 3px 3px 3px;
	border-style: inset inset inset inset;
	border-color: gray gray gray gray;
	background-color: white;
	-moz-border-radius: 0px 0px 0px 0px;
}
</style>
<style type="text/css">
form{display:inline; }
a:hover{color:#1578C3; text-decoration:underline; font-weight: bold; }
a:link,a:visited{color:#1578C3; text-decoration:none; font-weight: bold; }
a:hover{color:#1578C3; text-decoration:underline; font-weight: bold; }
body,table,td,th,p,div,input {

	font-family: verdana,helvetica,arial;

	font-size: 12px;

}

</style>

<link rel="stylesheet" type="text/css" href="slider/fonts-min.css" />
<link rel="stylesheet" type="text/css" href="slider/slider.css" />
<script type="text/javascript" src="slider/yahoo-dom-event.js"></script>

<script type="text/javascript" src="slider/animation-min.js"></script>
<script type="text/javascript" src="slider/dragdrop-min.js"></script>
<script type="text/javascript" src="slider/slider-min.js"></script>

<!--there is no custom header content for this example-->



<div class="yui-skin-sam" style="margin:10px;background:#FFFFFF;">

<div>

	<div>
	
		<h1>LEPR Search Interface</h1>
		
		<div class="exampleIntro">
			<p>This interface allows you to find similar samples from the <a href="http://lepr.ofm-research.org" target="_blank">LEPR</a> database.</p>
		<ul>
			<li>The sliders below are used to define a chemistry query for LEPR.</li>
			<li>Initially, all sliders are set to +/-5% of values found for Earthchem sample.</li>
			<li>Those items with a value of 0 will not be checked below, but you can still select them to use in LEPR query.</li>
			<li>You can adjust the values used to query LEPR by using the sliders and text boxes below.</li>
			<li>The link provided under the sample count will take you to the LEPR site with the query shown.</li>
		</ul>
					
		</div>
		
		
		
		<div style="padding-top:20px;">
		
			<div style="float:left;border:#FFFFFF thin solid;padding:1px;height:450px;">
				<h3>Search LEPR Compositions:</h3>
				<table cellpadding="0" cellspacing="0">
					<tr style="color:#333333;background:#FFFFFF;">
						<td>
							select
						</td>
						<td>
							&nbsp;
						</td>
						<td>
							range
						</td>
						<td>
							&nbsp;
						</td>
					</tr>
				<!-- ****************************************** -->
					<tr>
						<td>
							<input type="checkbox" id="sio2check" value="check" checked >
						</td>
						<td>
							<b>SiO<sub>2</sub>:</b>&nbsp;&nbsp;
						</td>
						<td>
							<div id="sio2_bg" class="yui-h-slider" title="Range slider">
								<div id="sio2_min_thumb" class="yui-slider-thumb"><img src="left-slide.png"></div>
								<div id="sio2_max_thumb" class="yui-slider-thumb"><img src="right-slide.png"></div>
							</div>
						</td>
						<td>
							<label>Min: <input type="text" id="sio2_from" size="5" maxlength="8"value=""></label>
							<label>Target: <input type="text" id="sio2target" size="5" maxlength="8"value="<?=$myrow->n_feo_sio2?>" readonly="readonly"></label>
							<label>Max: <input type="text" id="sio2_to" size="5" maxlength="8"value=""></label>
						</td>
					</tr>
				<!-- ****************************************** -->
			
				<!-- ****************************************** -->
					<tr>
						<td>
							<input type="checkbox" id="al2o3check" value="check" checked >
						</td>
						<td>
							<b>Al<sub>2</sub>O<sub>3</sub>:</b>&nbsp;&nbsp;
						</td>
						<td>
							<div id="al2o3_bg" class="yui-h-slider" title="Range slider">
								<div id="al2o3_min_thumb" class="yui-slider-thumb"><img src="left-slide.png"></div>
								<div id="al2o3_max_thumb" class="yui-slider-thumb"><img src="right-slide.png"></div>
							</div>
						</td>
						<td>
							<label>Min: <input type="text" id="al2o3_from" size="5" maxlength="8"value=""></label>
							<label>Target: <input type="text" id="al2o3target" size="5" maxlength="8"value="<?=$myrow->n_feo_al2o3?>" readonly="readonly"></label>
							<label>Max: <input type="text" id="al2o3_to" size="5" maxlength="8"value=""></label>
						</td>
					</tr>
				<!-- ****************************************** -->
			
				<!-- ****************************************** -->
					<tr>
						<td>
							<input type="checkbox" id="caocheck" value="check" checked >
						</td>
						<td>
							<b>CaO:</b>&nbsp;&nbsp;
						</td>
						<td>
							<div id="cao_bg" class="yui-h-slider" title="Range slider">
								<div id="cao_min_thumb" class="yui-slider-thumb"><img src="left-slide.png"></div>
								<div id="cao_max_thumb" class="yui-slider-thumb"><img src="right-slide.png"></div>
							</div>
						</td>
						<td>
							<label>Min: <input type="text" id="cao_from" size="5" maxlength="8"value=""></label>
							<label>Target: <input type="text" id="caotarget" size="5" maxlength="8"value="<?=$myrow->n_feo_cao?>" readonly="readonly"></label>
							<label>Max: <input type="text" id="cao_to" size="5" maxlength="8"value=""></label>
						</td>
					</tr>
				<!-- ****************************************** -->
			
				<!-- ****************************************** -->
					<tr>
						<td>
							<input type="checkbox" id="mgocheck" value="check" checked >
						</td>
						<td>
							<b>MgO:</b>&nbsp;&nbsp;
						</td>
						<td>
							<div id="mgo_bg" class="yui-h-slider" title="Range slider">
								<div id="mgo_min_thumb" class="yui-slider-thumb"><img src="left-slide.png"></div>
								<div id="mgo_max_thumb" class="yui-slider-thumb"><img src="right-slide.png"></div>
							</div>
						</td>
						<td>
							<label>Min: <input type="text" id="mgo_from" size="5" maxlength="8"value=""></label>
							<label>Target: <input type="text" id="mgotarget" size="5" maxlength="8"value="<?=$myrow->n_feo_mgo?>" readonly="readonly"></label>
							<label>Max: <input type="text" id="mgo_to" size="5" maxlength="8"value=""></label>
						</td>
					</tr>
				<!-- ****************************************** -->
			
				<!-- ****************************************** -->
					<tr>
						<td>
							<input type="checkbox" id="na2ocheck" value="check" checked >
						</td>
						<td>
							<b>Na<sub>2</sub>O:</b>&nbsp;&nbsp;
						</td>
						<td>
							<div id="na2o_bg" class="yui-h-slider" title="Range slider">
								<div id="na2o_min_thumb" class="yui-slider-thumb"><img src="left-slide.png"></div>
								<div id="na2o_max_thumb" class="yui-slider-thumb"><img src="right-slide.png"></div>
							</div>
						</td>
						<td>
							<label>Min: <input type="text" id="na2o_from" size="5" maxlength="8"value=""></label>
							<label>Target: <input type="text" id="na2otarget" size="5" maxlength="8"value="<?=$myrow->n_feo_na2o?>" readonly="readonly"></label>
							<label>Max: <input type="text" id="na2o_to" size="5" maxlength="8"value=""></label>
						</td>
					</tr>
				<!-- ****************************************** -->
			
				<!-- ****************************************** -->
					<tr>
						<td>
							<input type="checkbox" id="k2ocheck" value="check" checked >
						</td>
						<td>
							<b>K<sub>2</sub>O:</b>&nbsp;&nbsp;
						</td>
						<td>
							<div id="k2o_bg" class="yui-h-slider" title="Range slider">
								<div id="k2o_min_thumb" class="yui-slider-thumb"><img src="left-slide.png"></div>
								<div id="k2o_max_thumb" class="yui-slider-thumb"><img src="right-slide.png"></div>
							</div>
						</td>
						<td>
							<label>Min: <input type="text" id="k2o_from" size="5" maxlength="8"value=""></label>
							<label>Target: <input type="text" id="k2otarget" size="5" maxlength="8"value="<?=$myrow->n_feo_k2o?>" readonly="readonly"></label>
							<label>Max: <input type="text" id="k2o_to" size="5" maxlength="8"value=""></label>
						</td>
					</tr>
				<!-- ****************************************** -->
			
				<!-- ****************************************** -->
					<tr>
						<td>
							<input type="checkbox" id="p2o5check" value="check" checked >
						</td>
						<td>
							<b>P<sub>2</sub>O<sub>5</sub>:</b>&nbsp;&nbsp;
						</td>
						<td>
							<div id="p2o5_bg" class="yui-h-slider" title="Range slider">
								<div id="p2o5_min_thumb" class="yui-slider-thumb"><img src="left-slide.png"></div>
								<div id="p2o5_max_thumb" class="yui-slider-thumb"><img src="right-slide.png"></div>
							</div>
						</td>
						<td>
							<label>Min: <input type="text" id="p2o5_from" size="5" maxlength="8"value=""></label>
							<label>Target: <input type="text" id="p2o5target" size="5" maxlength="8"value="<?=$myrow->n_feo_p2o5?>" readonly="readonly"></label>
							<label>Max: <input type="text" id="p2o5_to" size="5" maxlength="8"value=""></label>
						</td>
					</tr>
				<!-- ****************************************** -->
			
				<!-- ****************************************** -->
					<tr>
						<td>
							<input type="checkbox" id="mnocheck" value="check" checked >
						</td>
						<td>
							<b>MnO:</b>&nbsp;&nbsp;
						</td>
						<td>
							<div id="mno_bg" class="yui-h-slider" title="Range slider">
								<div id="mno_min_thumb" class="yui-slider-thumb"><img src="left-slide.png"></div>
								<div id="mno_max_thumb" class="yui-slider-thumb"><img src="right-slide.png"></div>
							</div>
						</td>
						<td>
							<label>Min: <input type="text" id="mno_from" size="5" maxlength="8"value=""></label>
							<label>Target: <input type="text" id="mnotarget" size="5" maxlength="8"value="<?=$myrow->n_feo_mno?>" readonly="readonly"></label>
							<label>Max: <input type="text" id="mno_to" size="5" maxlength="8"value=""></label>
						</td>
					</tr>
				<!-- ****************************************** -->
			
				<!-- ****************************************** -->
					<tr>
						<td>
							<input type="checkbox" id="tio2check" value="check" checked >
						</td>
						<td>
							<b>TiO<sub>2</sub>:</b>&nbsp;&nbsp;
						</td>
						<td>
							<div id="tio2_bg" class="yui-h-slider" title="Range slider">
								<div id="tio2_min_thumb" class="yui-slider-thumb"><img src="left-slide.png"></div>
								<div id="tio2_max_thumb" class="yui-slider-thumb"><img src="right-slide.png"></div>
							</div>
						</td>
						<td>
							<label>Min: <input type="text" id="tio2_from" size="5" maxlength="8"value=""></label>
							<label>Target: <input type="text" id="tio2target" size="5" maxlength="8"value="<?=$myrow->n_feo_tio2?>" readonly="readonly"></label>
							<label>Max: <input type="text" id="tio2_to" size="5" maxlength="8"value=""></label>
						</td>
					</tr>
				<!-- ****************************************** -->
			
				<!-- ****************************************** -->
					<tr>
						<td>
							<input type="checkbox" id="feocheck" value="check" checked >
						</td>
						<td>
							<b>FeO:</b>&nbsp;&nbsp;
						</td>
						<td>
							<div id="feo_bg" class="yui-h-slider" title="Range slider">
								<div id="feo_min_thumb" class="yui-slider-thumb"><img src="left-slide.png"></div>
								<div id="feo_max_thumb" class="yui-slider-thumb"><img src="right-slide.png"></div>
							</div>
						</td>
						<td>
							<label>Min: <input type="text" id="feo_from" size="5" maxlength="8"value=""></label>
							<label>Target: <input type="text" id="feotarget" size="5" maxlength="8"value="<?=$myrow->n_feo_feot?>" readonly="readonly"></label>
							<label>Max: <input type="text" id="feo_to" size="5" maxlength="8"value=""></label>
						</td>
					</tr>
				<!-- ****************************************** -->
				</table>
				<div id="mycount" style="padding-left:150px;padding-top:30px;font-size:1.8em;">
					<?=$countstring?>
				</div>
			</div>
			<div style="float:left;border:#CCCCCC thin solid;padding:20px;height:450px;margin-left:10px;">
				<div align="center">
					<h3>Closest Neighbor<br>in LEPR:</h3>
					<table class="sample">
						<? if($checkedvals["sio2"]==" checked "){ ?>
							<tr>
								<td><b>SiO<sub>2</sub></b></td>
								<td><?=$neighboritems['sio2']?></td>
							</tr>
						<? } ?>
						<? if($checkedvals["al2o3"]==" checked "){ ?>
							<tr>
								<td><b>Al<sub>2</sub>O<sub>3</sub></b></td>
								<td><?=$neighboritems['al2o3']?></td>
							</tr>
						<? } ?>
						<? if($checkedvals["cao"]==" checked "){ ?>
							<tr>
								<td><b>CaO</b></td>
								<td><?=$neighboritems['cao']?></td>
							</tr>
						<? } ?>
						<? if($checkedvals["mgo"]==" checked "){ ?>
							<tr>
								<td><b>MgO</b></td>
								<td><?=$neighboritems['mgo']?></td>
							</tr>
						<? } ?>
						<? if($checkedvals["na2o"]==" checked "){ ?>
							<tr>
								<td><b>Na<sub>2</sub>O</b></td>
								<td><?=$neighboritems['na2o']?></td>
							</tr>
						<? } ?>
						<? if($checkedvals["k2o"]==" checked "){ ?>
							<tr>
								<td><b>K<sub>2</sub>O</b></td>
								<td><?=$neighboritems['k2o']?></td>
							</tr>
						<? } ?>
						<? if($checkedvals["p2o5"]==" checked "){ ?>
							<tr>
								<td><b>P<sub>2</sub>O<sub>5</sub></b></td>
								<td><?=$neighboritems['p2o5']?></td>
							</tr>
						<? } ?>
						<? if($checkedvals["mno"]==" checked "){ ?>
							<tr>
								<td><b>MnO</b></td>
								<td><?=$neighboritems['mno']?></td>
							</tr>
						<? } ?>
						<? if($checkedvals["tio2"]==" checked "){ ?>
							<tr>
								<td><b>TiO<sub>2</sub></b></td>
								<td><?=$neighboritems['tio2']?></td>
							</tr>
						<? } ?>
						<? if($checkedvals["feo"]==" checked "){ ?>
							<tr>
								<td><b>FeO</b></td>
								<td><?=$neighboritems['feo']?></td>
							</tr>
						<? } ?>
					</table><br>
					<a href="<?=$neighborparts[0]?>" target="_blank">View</a> this sample at LEPR
				</div>
			</div>
			<div style="clear:left;"></div>
		</div>



		<div id="myurl"></div>
		
		<script type="text/javascript">
		
		
		
		(function () {
			YAHOO.namespace('example');
		
			var Dom = YAHOO.util.Dom;
			var range = 220;
			var tickSize = 0;
			var minThumbDistance = 0;
			var initValues = [55,85];
			var cf = 1;
		
		
		
		
			// Sliders set up is done when the DOM is ready
			YAHOO.util.Event.onDOMReady(function () {
		
		
		
		
		
		
		
		
				var getcount = function () {
				
					document.getElementById('mycount').innerHTML='<img src="slideload.gif"> Loading...';
				
					function makeRequest(url) {
						var httpRequest;
				
						if (window.XMLHttpRequest) { // Mozilla, Safari, ...
							httpRequest = new XMLHttpRequest();
							if (httpRequest.overrideMimeType) {
								httpRequest.overrideMimeType('text/xml');
								// See note below about this line
							}
						} 
						else if (window.ActiveXObject) { // IE
							try {
								httpRequest = new ActiveXObject("Msxml2.XMLHTTP");
							} 
							catch (e) {
								try {
									httpRequest = new ActiveXObject("Microsoft.XMLHTTP");
								} 
								catch (e) {}
							}
						}
				
						if (!httpRequest) {
							alert('Giving up :( Cannot create an XMLHTTP instance');
							return false;
						}
						httpRequest.onreadystatechange = function() { alertContents(httpRequest); };
						httpRequest.open('GET', url, true);
						httpRequest.send('');
				
					}
				
					function alertContents(httpRequest) {
				
						if (httpRequest.readyState == 4) {
							if (httpRequest.status == 200) {
								document.getElementById('mycount').innerHTML=httpRequest.responseText;
								//alert(httpRequest.responseText);
								//alert('success');
							} else {
								alert('There was a problem with the request.');
								//alert(httpRequest.responseText);
							}
						}
				
					}
		
					var urlstring='';
					var delimiter='';
		
					sio2min=((sio2slider.minVal)/2).toFixed(1);
					sio2max=((sio2slider.maxVal-20)/2).toFixed(1);
					check=document.getElementById('sio2check'); if(check.checked==1){ urlstring=urlstring+delimiter+'sio2_min='+sio2min+'&sio2_max='+sio2max; delimiter='&'; }
					al2o3min=((al2o3slider.minVal)/2).toFixed(1);
					al2o3max=((al2o3slider.maxVal-20)/2).toFixed(1);
					check=document.getElementById('al2o3check'); if(check.checked==1){ urlstring=urlstring+delimiter+'al2o3_min='+al2o3min+'&al2o3_max='+al2o3max; delimiter='&'; }
					caomin=((caoslider.minVal)/2).toFixed(1);
					caomax=((caoslider.maxVal-20)/2).toFixed(1);
					check=document.getElementById('caocheck'); if(check.checked==1){ urlstring=urlstring+delimiter+'cao_min='+caomin+'&cao_max='+caomax; delimiter='&'; }
					mgomin=((mgoslider.minVal)/2).toFixed(1);
					mgomax=((mgoslider.maxVal-20)/2).toFixed(1);
					check=document.getElementById('mgocheck'); if(check.checked==1){ urlstring=urlstring+delimiter+'mgo_min='+mgomin+'&mgo_max='+mgomax; delimiter='&'; }
					na2omin=((na2oslider.minVal)/2).toFixed(1);
					na2omax=((na2oslider.maxVal-20)/2).toFixed(1);
					check=document.getElementById('na2ocheck'); if(check.checked==1){ urlstring=urlstring+delimiter+'na2o_min='+na2omin+'&na2o_max='+na2omax; delimiter='&'; }
					k2omin=((k2oslider.minVal)/2).toFixed(1);
					k2omax=((k2oslider.maxVal-20)/2).toFixed(1);
					check=document.getElementById('k2ocheck'); if(check.checked==1){ urlstring=urlstring+delimiter+'k2o_min='+k2omin+'&k2o_max='+k2omax; delimiter='&'; }
					p2o5min=((p2o5slider.minVal)/2).toFixed(1);
					p2o5max=((p2o5slider.maxVal-20)/2).toFixed(1);
					check=document.getElementById('p2o5check'); if(check.checked==1){ urlstring=urlstring+delimiter+'p2o5_min='+p2o5min+'&p2o5_max='+p2o5max; delimiter='&'; }
					mnomin=((mnoslider.minVal)/2).toFixed(1);
					mnomax=((mnoslider.maxVal-20)/2).toFixed(1);
					check=document.getElementById('mnocheck'); if(check.checked==1){ urlstring=urlstring+delimiter+'mno_min='+mnomin+'&mno_max='+mnomax; delimiter='&'; }
					tio2min=((tio2slider.minVal)/2).toFixed(1);
					tio2max=((tio2slider.maxVal-20)/2).toFixed(1);
					check=document.getElementById('tio2check'); if(check.checked==1){ urlstring=urlstring+delimiter+'tio2_min='+tio2min+'&tio2_max='+tio2max; delimiter='&'; }
					feomin=((feoslider.minVal)/2).toFixed(1);
					feomax=((feoslider.maxVal-20)/2).toFixed(1);
					check=document.getElementById('feocheck'); if(check.checked==1){ urlstring=urlstring+delimiter+'feo_min='+feomin+'&feo_max='+feomax; delimiter='&'; }
		
					
					//myurl='lepr_count_wrapper.php?sio2_min='+sio2min+'&sio2_max='+sio2max+'&al2o3_min='+al2o3min+'&al2o3_max='+al2o3max+'&cao_min='+caomin+'&cao_max='+caomax+'&mgo_min='+mgomin+'&mgo_max='+mgomax+'&na2o_min='+na2omin+'&na2o_max='+na2omax+'&k2o_min='+k2omin+'&k2o_max='+k2omax+'&p2o5_min='+p2o5min+'&p2o5_max='+p2o5max+'&mno_min='+mnomin+'&mno_max='+mnomax+'&tio2_min='+tio2min+'&tio2_max='+tio2max+'&feo_min='+feomin+'&feo_max='+feomax;
					
					myurl='lepr_count_wrapper.php?'+urlstring;
					
					//document.getElementById('myurl').innerHTML=urlstring;
					
					makeRequest(myurl);
		
		
		
		
		
		
				}
				
				
				var oldcount = function () {
					//alert(((sio2slider.minVal)/2).toFixed(1)+' '+((sio2slider.maxVal-20)/2).toFixed(1));
					sio2min=((sio2slider.minVal)/2).toFixed(1);
					sio2max=((sio2slider.maxVal-20)/2).toFixed(1);
					al2o3min=((al2o3slider.minVal)/2).toFixed(1);
					al2o3max=((al2o3slider.maxVal-20)/2).toFixed(1);
					caomin=((caoslider.minVal)/2).toFixed(1);
					caomax=((caoslider.maxVal-20)/2).toFixed(1);
					mgomin=((mgoslider.minVal)/2).toFixed(1);
					mgomax=((mgoslider.maxVal-20)/2).toFixed(1);
					na2omin=((na2oslider.minVal)/2).toFixed(1);
					na2omax=((na2oslider.maxVal-20)/2).toFixed(1);
					k2omin=((k2oslider.minVal)/2).toFixed(1);
					k2omax=((k2oslider.maxVal-20)/2).toFixed(1);
					p2o5min=((p2o5slider.minVal)/2).toFixed(1);
					p2o5max=((p2o5slider.maxVal-20)/2).toFixed(1);
					mnomin=((mnoslider.minVal)/2).toFixed(1);
					mnomax=((mnoslider.maxVal-20)/2).toFixed(1);
					tio2min=((tio2slider.minVal)/2).toFixed(1);
					tio2max=((tio2slider.maxVal-20)/2).toFixed(1);
					feomin=((feoslider.minVal)/2).toFixed(1);
					feomax=((feoslider.maxVal-20)/2).toFixed(1);
					document.getElementById('myurl').innerHTML='http://foo.php?sio2_min='+sio2min+'&sio2_max='+sio2max+'&al2o3_min='+al2o3min+'&al2o3_max='+al2o3max+'&cao_min='+caomin+'&cao_max='+caomax+'&mgo_min='+mgomin+'&mgo_max='+mgomax+'&na2o_min='+na2omin+'&na2o_max='+na2omax+'&k2o_min='+k2omin+'&k2o_max='+k2omax+'&p2o5_min='+p2o5min+'&p2o5_max='+p2o5max+'&mno_min='+mnomin+'&mno_max='+mnomax+'&tio2_min='+tio2min+'&tio2_max='+tio2max+'&feo_min='+feomin+'&feo_max='+feomax;
					//document.getElementById('myurl').innerHTML='foo';
				}
			
				
				// Create the DualSlider
				//*****************************************************************
				var sio2_bg = Dom.get("sio2_bg"),
					sio2from    = Dom.get("sio2_from"),
					sio2to      = Dom.get("sio2_to");
				
				var sio2slider = YAHOO.widget.Slider.getHorizDualSlider(sio2_bg,"sio2_min_thumb", "sio2_max_thumb",range, tickSize, [<?=$minvals["sio2"]?>,<?=$maxvals["sio2"]?>]);
		
				var sio2updateUI = function () {
					sio2from.value = ((sio2slider.minVal)/2).toFixed(1);
					sio2to.value   = ((sio2slider.maxVal-20)/2).toFixed(1);
				};
		
				sio2slider.subscribe('ready', sio2updateUI);
				sio2slider.subscribe('change', sio2updateUI);
				sio2slider.subscribe('slideEnd', getcount);
				//*****************************************************************
		
				//*****************************************************************
				var al2o3_bg = Dom.get("al2o3_bg"),
					al2o3from    = Dom.get("al2o3_from"),
					al2o3to      = Dom.get("al2o3_to");
				
				var al2o3slider = YAHOO.widget.Slider.getHorizDualSlider(al2o3_bg,"al2o3_min_thumb", "al2o3_max_thumb",range, tickSize, [<?=$minvals["al2o3"]?>,<?=$maxvals["al2o3"]?>]);
		
				var al2o3updateUI = function () {
					al2o3from.value = ((al2o3slider.minVal)/2).toFixed(1);
					al2o3to.value   = ((al2o3slider.maxVal-20)/2).toFixed(1);
				};
		
				al2o3slider.subscribe('ready', al2o3updateUI);
				al2o3slider.subscribe('change', al2o3updateUI);
				al2o3slider.subscribe('slideEnd', getcount);
				//*****************************************************************
		
				//*****************************************************************
				var cao_bg = Dom.get("cao_bg"),
					caofrom    = Dom.get("cao_from"),
					caoto      = Dom.get("cao_to");
				
				var caoslider = YAHOO.widget.Slider.getHorizDualSlider(cao_bg,"cao_min_thumb", "cao_max_thumb",range, tickSize, [<?=$minvals["cao"]?>,<?=$maxvals["cao"]?>]);
		
				var caoupdateUI = function () {
					caofrom.value = ((caoslider.minVal)/2).toFixed(1);
					caoto.value   = ((caoslider.maxVal-20)/2).toFixed(1);
				};
		
				caoslider.subscribe('ready', caoupdateUI);
				caoslider.subscribe('change', caoupdateUI);
				caoslider.subscribe('slideEnd', getcount);
				//*****************************************************************
				
				//*****************************************************************
				var mgo_bg = Dom.get("mgo_bg"),
					mgofrom    = Dom.get("mgo_from"),
					mgoto      = Dom.get("mgo_to");
				
				var mgoslider = YAHOO.widget.Slider.getHorizDualSlider(mgo_bg,"mgo_min_thumb", "mgo_max_thumb",range, tickSize, [<?=$minvals["mgo"]?>,<?=$maxvals["mgo"]?>]);
		
				var mgoupdateUI = function () {
					mgofrom.value = ((mgoslider.minVal)/2).toFixed(1);
					mgoto.value   = ((mgoslider.maxVal-20)/2).toFixed(1);
				};
		
				mgoslider.subscribe('ready', mgoupdateUI);
				mgoslider.subscribe('change', mgoupdateUI);
				mgoslider.subscribe('slideEnd', getcount);
				//*****************************************************************
		
				//*****************************************************************
				var na2o_bg = Dom.get("na2o_bg"),
					na2ofrom    = Dom.get("na2o_from"),
					na2oto      = Dom.get("na2o_to");
				
				var na2oslider = YAHOO.widget.Slider.getHorizDualSlider(na2o_bg,"na2o_min_thumb", "na2o_max_thumb",range, tickSize, [<?=$minvals["na2o"]?>,<?=$maxvals["na2o"]?>]);
		
				var na2oupdateUI = function () {
					na2ofrom.value = ((na2oslider.minVal)/2).toFixed(1);
					na2oto.value   = ((na2oslider.maxVal-20)/2).toFixed(1);
				};
		
				na2oslider.subscribe('ready', na2oupdateUI);
				na2oslider.subscribe('change', na2oupdateUI);
				na2oslider.subscribe('slideEnd', getcount);
				//*****************************************************************
		
				//*****************************************************************
				var k2o_bg = Dom.get("k2o_bg"),
					k2ofrom    = Dom.get("k2o_from"),
					k2oto      = Dom.get("k2o_to");
				
				var k2oslider = YAHOO.widget.Slider.getHorizDualSlider(k2o_bg,"k2o_min_thumb", "k2o_max_thumb",range, tickSize, [<?=$minvals["k2o"]?>,<?=$maxvals["k2o"]?>]);
		
				var k2oupdateUI = function () {
					k2ofrom.value = ((k2oslider.minVal)/2).toFixed(1);
					k2oto.value   = ((k2oslider.maxVal-20)/2).toFixed(1);
				};
		
				k2oslider.subscribe('ready', k2oupdateUI);
				k2oslider.subscribe('change', k2oupdateUI);
				k2oslider.subscribe('slideEnd', getcount);
				//*****************************************************************
		
				//*****************************************************************
				var p2o5_bg = Dom.get("p2o5_bg"),
					p2o5from    = Dom.get("p2o5_from"),
					p2o5to      = Dom.get("p2o5_to");
				
				var p2o5slider = YAHOO.widget.Slider.getHorizDualSlider(p2o5_bg,"p2o5_min_thumb", "p2o5_max_thumb",range, tickSize, [<?=$minvals["p2o5"]?>,<?=$maxvals["p2o5"]?>]);
		
				var p2o5updateUI = function () {
					p2o5from.value = ((p2o5slider.minVal)/2).toFixed(1);
					p2o5to.value   = ((p2o5slider.maxVal-20)/2).toFixed(1);
				};
		
				p2o5slider.subscribe('ready', p2o5updateUI);
				p2o5slider.subscribe('change', p2o5updateUI);
				p2o5slider.subscribe('slideEnd', getcount);
				//*****************************************************************
		
				//*****************************************************************
				var mno_bg = Dom.get("mno_bg"),
					mnofrom    = Dom.get("mno_from"),
					mnoto      = Dom.get("mno_to");
				
				var mnoslider = YAHOO.widget.Slider.getHorizDualSlider(mno_bg,"mno_min_thumb", "mno_max_thumb",range, tickSize, [<?=$minvals["mno"]?>,<?=$maxvals["mno"]?>]);
		
				var mnoupdateUI = function () {
					mnofrom.value = ((mnoslider.minVal)/2).toFixed(1);
					mnoto.value   = ((mnoslider.maxVal-20)/2).toFixed(1);
				};
		
				mnoslider.subscribe('ready', mnoupdateUI);
				mnoslider.subscribe('change', mnoupdateUI);
				mnoslider.subscribe('slideEnd', getcount);
				//*****************************************************************
		
				//*****************************************************************
				var tio2_bg = Dom.get("tio2_bg"),
					tio2from    = Dom.get("tio2_from"),
					tio2to      = Dom.get("tio2_to");
				
				var tio2slider = YAHOO.widget.Slider.getHorizDualSlider(tio2_bg,"tio2_min_thumb", "tio2_max_thumb",range, tickSize, [<?=$minvals["tio2"]?>,<?=$maxvals["tio2"]?>]);
		
				var tio2updateUI = function () {
					tio2from.value = ((tio2slider.minVal)/2).toFixed(1);
					tio2to.value   = ((tio2slider.maxVal-20)/2).toFixed(1);
				};
		
				tio2slider.subscribe('ready', tio2updateUI);
				tio2slider.subscribe('change', tio2updateUI);
				tio2slider.subscribe('slideEnd', getcount);
				//*****************************************************************
		
				//*****************************************************************
				var feo_bg = Dom.get("feo_bg"),
					feofrom    = Dom.get("feo_from"),
					feoto      = Dom.get("feo_to");
				
				var feoslider = YAHOO.widget.Slider.getHorizDualSlider(feo_bg,"feo_min_thumb", "feo_max_thumb",range, tickSize, [<?=$minvals["feo"]?>,<?=$maxvals["feo"]?>]);
		
				var feoupdateUI = function () {
					feofrom.value = ((feoslider.minVal)/2).toFixed(1);
					feoto.value   = ((feoslider.maxVal-20)/2).toFixed(1);
				};
		
				feoslider.subscribe('ready', feoupdateUI);
				feoslider.subscribe('change', feoupdateUI);
				feoslider.subscribe('slideEnd', getcount);
				//*****************************************************************
		
				//var sio2check = document.getElementById("sio2check");
				//YAHOO.util.Event.on('sio2check','change',getcount());
				
				function fnCallback(e) { getcount(); } 
				
				function textCallback(e) {
						mytag=e.target.id;
						mytag=mytag.replace('_from','');
						mytag=mytag.replace('_to','');
		
						from    = document.getElementById(mytag+"_from");
						to      = document.getElementById(mytag+"_to");
		
						//alert(to.value);
						//return;
		
						min = Math.abs(parseInt(from.value,10)|0);
						max = Math.abs(parseInt(to.value,10)|0);
			
						if (min > max) {
							var hold = min;
							min = max;
							max = hold;
						}
			
		
						//min = Math.min(min,range - 30);
						//max = Math.max(Math.min(max,range),min + 20 + minThumbDistance);
			
						min = min*2;
						max = (max*2) + 20;
		
						if(mytag=="sio2"){sio2slider.setValues(min,max);}
						if(mytag=="al2o3"){al2o3slider.setValues(min,max);}
						if(mytag=="cao"){caoslider.setValues(min,max);}
						if(mytag=="mgo"){mgoslider.setValues(min,max);}
						if(mytag=="na2o"){na2oslider.setValues(min,max);}
						if(mytag=="k2o"){k2oslider.setValues(min,max);}
						if(mytag=="p2o5"){p2o5slider.setValues(min,max);}
						if(mytag=="mno"){mnoslider.setValues(min,max);}
						if(mytag=="tio2"){tio2slider.setValues(min,max);}
						if(mytag=="feo"){feoslider.setValues(min,max);}
		
		
				}
		
				var sio2check = document.getElementById("sio2check"); YAHOO.util.Event.addListener(sio2check, "click", fnCallback); 
				var al2o3check = document.getElementById("al2o3check"); YAHOO.util.Event.addListener(al2o3check, "click", fnCallback); 
				var caocheck = document.getElementById("caocheck"); YAHOO.util.Event.addListener(caocheck, "click", fnCallback); 
				var mgocheck = document.getElementById("mgocheck"); YAHOO.util.Event.addListener(mgocheck, "click", fnCallback); 
				var na2ocheck = document.getElementById("na2ocheck"); YAHOO.util.Event.addListener(na2ocheck, "click", fnCallback); 
				var k2ocheck = document.getElementById("k2ocheck"); YAHOO.util.Event.addListener(k2ocheck, "click", fnCallback); 
				var p2o5check = document.getElementById("p2o5check"); YAHOO.util.Event.addListener(p2o5check, "click", fnCallback); 
				var mnocheck = document.getElementById("mnocheck"); YAHOO.util.Event.addListener(mnocheck, "click", fnCallback); 
				var tio2check = document.getElementById("tio2check"); YAHOO.util.Event.addListener(tio2check, "click", fnCallback); 
				var feocheck = document.getElementById("feocheck"); YAHOO.util.Event.addListener(feocheck, "click", fnCallback); 
		
				var sio2_to = document.getElementById("sio2_to"); YAHOO.util.Event.addListener(sio2_to, "change", textCallback);
				var al2o3_to = document.getElementById("al2o3_to"); YAHOO.util.Event.addListener(al2o3_to, "change", textCallback);
				var cao_to = document.getElementById("cao_to"); YAHOO.util.Event.addListener(cao_to, "change", textCallback);
				var mgo_to = document.getElementById("mgo_to"); YAHOO.util.Event.addListener(mgo_to, "change", textCallback);
				var na2o_to = document.getElementById("na2o_to"); YAHOO.util.Event.addListener(na2o_to, "change", textCallback);
				var k2o_to = document.getElementById("k2o_to"); YAHOO.util.Event.addListener(k2o_to, "change", textCallback);
				var p2o5_to = document.getElementById("p2o5_to"); YAHOO.util.Event.addListener(p2o5_to, "change", textCallback);
				var mno_to = document.getElementById("mno_to"); YAHOO.util.Event.addListener(mno_to, "change", textCallback);
				var tio2_to = document.getElementById("tio2_to"); YAHOO.util.Event.addListener(tio2_to, "change", textCallback);
				var feo_to = document.getElementById("feo_to"); YAHOO.util.Event.addListener(feo_to, "change", textCallback);
		
				var sio2_from = document.getElementById("sio2_from"); YAHOO.util.Event.addListener(sio2_from, "change", textCallback);
				var al2o3_from = document.getElementById("al2o3_from"); YAHOO.util.Event.addListener(al2o3_from, "change", textCallback);
				var cao_from = document.getElementById("cao_from"); YAHOO.util.Event.addListener(cao_from, "change", textCallback);
				var mgo_from = document.getElementById("mgo_from"); YAHOO.util.Event.addListener(mgo_from, "change", textCallback);
				var na2o_from = document.getElementById("na2o_from"); YAHOO.util.Event.addListener(na2o_from, "change", textCallback);
				var k2o_from = document.getElementById("k2o_from"); YAHOO.util.Event.addListener(k2o_from, "change", textCallback);
				var p2o5_from = document.getElementById("p2o5_from"); YAHOO.util.Event.addListener(p2o5_from, "change", textCallback);
				var mno_from = document.getElementById("mno_from"); YAHOO.util.Event.addListener(mno_from, "change", textCallback);
				var tio2_from = document.getElementById("tio2_from"); YAHOO.util.Event.addListener(tio2_from, "change", textCallback);
				var feo_from = document.getElementById("feo_from"); YAHOO.util.Event.addListener(feo_from, "change", textCallback);
		
				// Attach the slider to the YAHOO.example namespace for public probing
				YAHOO.example.sio2slider = sio2slider;
			});
		})();
		
		</script>
	
	</div>
	
	<div style="float:left;">
		<div style="float:right;">
			&nbsp;
		</div>
		<div style="clear:right;"></div>
	</div>
	<div style="clear:left;"></div>

</div>

</div>

<?
include('includes/ks_footer.html');
?>

