<?PHP
/**
 * advancedoptions.php
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

//print_r($_POST);

echo '<h1>ADVANCED OUTPUT OPTIONS</h1>';

include "get_pkey.php";

include "db.php";

include("datasources.php");

include("srcwhere.php");

include "queries.php";


$searchmaterial=$db->get_var("select material from search_query where pkey=$pkey");
?>




	<script type="text/javascript" src="src/adapter/shadowbox-base.js"></script>
	<script type="text/javascript" src="src/shadowbox.js"></script>

	<script type="text/javascript">
	
	Shadowbox.loadSkin('classic', 'src/skin');
	Shadowbox.loadLanguage('en', 'src/lang');
	Shadowbox.loadPlayer(['flv', 'html', 'iframe', 'img', 'qt', 'swf', 'wmp'], 'src/player');
	
	window.onload = function(){
	
		Shadowbox.init();

	};
	</script>

<?
//get all items here
/*
$getnames = $db->get_results("SELECT DISTINCT(field_name), display_name, DATA_ORDER 
FROM data_fields 
WHERE showfield=TRUE
and total_in_database > 0
and data_order < 999999
ORDER BY DATA_ORDER ASC");
*/

$getnames = $db->get_results("SELECT DISTINCT(useval) as field_name, display_name, display_order as data_order 
FROM petdb_vars 
WHERE putin=true 
ORDER BY display_order ASC");



foreach($getnames as $g) {
	if ($g->field_name != 'IN') {
		$allitems[]=strtoupper($g->field_name);
	}
} // foreach getnames

//dumpVar($allitems);

$getitems=$db->get_results($itemstring); // itemstring gets the chemicals - "items" - for which there are data in this search query result sample set
if($db->num_rows > 0){
	foreach($getitems AS $g) {
		if(in_array(strtoupper($g->name),$allitems)){
			if( strtoupper($g->name)!='RA226_TH230'  && strtoupper($g->name)!='TH230_U238' ){
				$item_array[]=strtolower($g->name);
			}
		}
	}
}else{
	echo "<p />No chemical data exists for this sample set. Advanced Output is not possible. Please try another search.<p /> ";
	echo "<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>";
	include "includes/ks_footer.html";
	exit();
}


?>

<div id="debug" style="display:none;">
<?
print_r($item_array);
?>
<?=nl2br($itemstring)?>
<br><br>
<?=nl2br($lepritemstring)?>
</div>



<?php
//exit();
//$getquery=$db->get_row("select earthchemwhere from search_query where pkey=$pkey");
//$searchquery=$db->get_row("select * from search_query where pkey=$pkey");
//include('srcwhere.php');
//include('queries.php');


//dumpVar($itemstring);exit();









//also do lepr items here so we have them for the javascript functions
/*
$lepritems=$db->get_results($lepritemstring); 
$lepritemcount=$db->num_rows;

if($lepritemcount > 0){
	foreach($lepritems AS $g) {
		if( $g->itemname!="ilmenite" ){
			$lepr_item_array[]=$g->itemname;
		} 
	}
}else{
	unset($lepr_item_array);
}
*/

//$group_array=array('MAJOR_OXIDES','ISOTOPE_RATIO','NOBLE_GAS','REE','U_SERIES','VOLATILE','TRACE_ELEMENTS','STABLE_ISOTOPE');


$group_array=array("MAJOR_OXIDE", "RATIO", "NOBLE_GAS", "RARE_EARTH_ELEMENT", "URANIUM_SERIES", "VOLATILE", "TRACE_ELEMENT", "STABLE_ISOTOPES", "RADIOGENIC_ISOTOPES");






//get lepr items here
//$lepr_items=array('albite','anorthite','apatite','calciumorthosilicate','corundum','diopside','hypersthene','kaliophilite','leucite','nepheline','olivine','orthoclase','perofskite','potassiummetasilicate','quartz','rutile','sodiummetasilicate','titanite','wollastonite');

//let's build the javascript functions here

?>
<script type="text/javascript">
function SetAllCheckBoxes(FormName, className, CheckValue) {
	var all = document.all ? document.all : document.getElementsByTagName('*');
	var elements = new Array();
	for (var e = 0; e < all.length; e++) {
		if (all[e].className == className) {
			elements[elements.length] = all[e];
		}
	}
	var countCheckBoxes = elements.length;
	for(var i = 0; i < countCheckBoxes; i++) {
		elements[i].checked = CheckValue;
  	}
}


function ShowStandard() {


	SetAllCheckBoxes('myform', 'MAJOR_OXIDE', false);
	SetAllCheckBoxes('myform', 'RATIO', false);
	SetAllCheckBoxes('myform', 'NOBLE_GAS', false);
	SetAllCheckBoxes('myform', 'RARE_EARTH_ELEMENT', false);
	SetAllCheckBoxes('myform', 'URANIUM_SERIES', false);
	SetAllCheckBoxes('myform', 'VOLATILE', false);
	SetAllCheckBoxes('myform', 'TRACE_ELEMENT', false);
	SetAllCheckBoxes('myform', 'STABLE_ISOTOPES', false);
	SetAllCheckBoxes('myform', 'RADIOGENIC_ISOTOPES', false);






	var myitems=["SIO2","TIO2","AL2O3","CR2O3","FE2O3","FE2O3T","FEO","FEOT","NIO","MNO","MGO","CAO","NA2O","K2O","P2O5","LOI","H2O","H2OM","H2OP","S","CO2","F","CL","AG","ARSENIC","AU","B","BA","BE","BI","CD","CO","CR","CS","CU","GA","HF","HG","I","IR","K","LI","MN","MO","NB","NI","OS","PB","PD","PT","RB","RE","S","SB","SC","SE","SN","SR","TA","TE","TH","TI","TL","U","V","W","Y","ZN","ZR","LA","CE","PR","ND","SM","EU","GD","TB","DY","HO","ER","TM","YB","LU","SR87_SR86","ND143_ND144","PB206_PB204","PB207_PB204","PB208_PB204","HF176_HF177","LU176_HF177","OS187_OS188","BE10","HE3_HE4"];

	for(var i = 0; i < myitems.length; i++) {
		//console.log(myitems[i]);
		document.getElementById(myitems[i]).checked=true;
  	}
}

function ShowExistingold() {

	SetAllCheckBoxes('myform', 'MAJOR_OXIDE', false);
	SetAllCheckBoxes('myform', 'RATIO', false);
	SetAllCheckBoxes('myform', 'NOBLE_GAS', false);
	SetAllCheckBoxes('myform', 'RARE_EARTH_ELEMENT', false);
	SetAllCheckBoxes('myform', 'URANIUM_SERIES', false);
	SetAllCheckBoxes('myform', 'VOLATILE', false);
	SetAllCheckBoxes('myform', 'TRACE_ELEMENT', false);
	SetAllCheckBoxes('myform', 'STABLE_ISOTOPES', false);
	SetAllCheckBoxes('myform', 'RADIOGENIC_ISOTOPES', false);



<?
$jitem="var myitems=[";
$jitemdelim="";
foreach($item_array as $thisitem){
	//check in allitems for thisitem
	$thisitem=strtoupper($thisitem);
	if (in_array($thisitem, $allitems)) {
		$jitem.=$jitemdelim."\"$thisitem\"";
		$jitemdelim=",";
		echo "console.log('$thisitem');\n";
		echo "document.getElementById('$thisitem').checked=true;\n";
	}
}

$jitem.="];";
?>

	<?
		//$jitem
	?>
	
	//for(var i = 0; i < myitems.length; i++) {
	//	document.getElementById(myitems[i]).checked=true;
  	//}
	
	

}

function ClearAll() {

	SetAllCheckBoxes('myform', 'MAJOR_OXIDE', false);
	SetAllCheckBoxes('myform', 'RATIO', false);
	SetAllCheckBoxes('myform', 'NOBLE_GAS', false);
	SetAllCheckBoxes('myform', 'RARE_EARTH_ELEMENT', false);
	SetAllCheckBoxes('myform', 'URANIUM_SERIES', false);
	SetAllCheckBoxes('myform', 'VOLATILE', false);
	SetAllCheckBoxes('myform', 'TRACE_ELEMENT', false);
	SetAllCheckBoxes('myform', 'STABLE_ISOTOPES', false);
	SetAllCheckBoxes('myform', 'RADIOGENIC_ISOTOPES', false);
}

</script>

	<form name="myform" id="myform" action="updatesearch.php" method="post">
		<table border="0" cellspacing="0" cellpadding="5">
		<tr><td colspan=2>
		<div name="message" id="message" value=""></div>
		</td></tr>
			<tr valign="top">
				<td ><h2>				Samples to Display:
</h2>
				</td>
				<td>
					<input type="radio" name="searchopt" value="anyopt" checked>&nbsp;Show samples with any of the checked values defined.<br>
					<input type="radio" name="searchopt" value="exactopt">&nbsp;Show samples with all of the below values defined.<br>
					<input type="radio" name="searchopt" value="alldata">&nbsp;Show all samples.
				</td>
			</tr>
			<tr>
				<td><h2>				File Type to Display:
</h2>
				</td>
				<td>
					<input type="radio" name="dispmode" value="html" checked>&nbsp;HTML Table &nbsp;
					<input type="radio" name="dispmode" value="text">&nbsp;Text File &nbsp;
					<input type="radio" name="dispmode" value="xlsx">&nbsp;XLSX Spreadsheet&nbsp;
				</td>
			</tr>
			<tr valign="top">
				<td><h2>				Output Format:
</h2>
				</td>
				<td>
					<input type="radio" name="rowtype" value="sample" checked>One Row Per Sample &nbsp;
					Show Methods <input type="checkbox" name="showmethods" value="true"> &nbsp;
				</td>
			</tr>
		</table>
<?php
$chemdata_exists=false; // if we do falset find any chemical data to display, we cannoft give advanced output, so don't show the Go to Data button 
?>
<h2 style="margin-left:5px">Choose Chemical Data to Display:</h2>

<div class="optionsbox">
Note: The items shown in bold below actually have values that lie within your search criteria.
Those which are not bold do not have any values within your search. You can use the buttons below
to choose a set of standard output items to use with multiple downloads. 
</div>

<br>
<div style="width:600px;color:#FF0000;padding-left:40px;">
<input type="button" class="submitbutton" value="Show Standard Output Items" onClick="javascript:ShowStandard();return false">
<input type="button" class="submitbutton" value="Show Items that Exist in Current Query" onClick="javascript:ShowExisting();return false">
<input type="button" class="submitbutton" value="Clear All Items" onClick="javascript:ClearAll();return false">
<!--<input type="button" class="submitbutton" value="Test" onClick="javascript:$('#AG').prop('checked', true);return false">-->
</div>

<br>

		<table border="1" cellpadding="5" cellspacing="0">
			<tr>
			<?php
				foreach($group_array AS $currgroup) {
					
				//echo "<p>SELECT DISTINCT(field_name), DATA_ORDER FROM data_fields WHERE (group_name ='$currgroup') ORDER BY DATA_ORDER ASC";
					
					if($currgroup=="MAJOR_OXIDE"){
					
						$getnames = $db->get_results("SELECT DISTINCT(useval) as field_name, display_name, display_order as data_order
						FROM petdb_vars 
						WHERE (upper(vartype) ='$currgroup') 
						and putin=true 
                                                and useval not in (select useval from petdb_vars where upper(vartype)='TRACE_ELEMENT')
						ORDER BY display_order ASC");
					
					}else{
					
						$getnames = $db->get_results("SELECT DISTINCT(useval) as field_name, display_name, display_order as data_order
						FROM petdb_vars 
						WHERE (upper(vartype) ='$currgroup') 
                                                and putin=true 
						ORDER BY display_order ASC");
					
					}
					
					
					//var_dump($getnames);
					
					$showcolumn=false;
					foreach($getnames as $g) {
						if ($g->field_name != 'IN') { 
							if (in_array($g->field_name,$item_array)) { 
								$showcolumn=true;
								$chemdata_exists=true;
							}
						}
					} // foreach getnames
					$showcolumn=true; //put this here to show all columns
					if ($showcolumn) {
						$currgroup_display = str_replace("_", " ", $currgroup); //column names might have a _ in them, remove for display
						echo "<TD >$currgroup_display:</TD>";
					} // if
				} // foreach group_array
				// add lepr columns manually here... because they are in a different table
				//if($searchmaterial!=3){
				if($searchmaterial==34567){
				?>
				<TD>CIPW NORMS:</TD>
				<?
				}
				?>
			</tr>
			<tr valign="top">
			<?php
				foreach($group_array as $currgroup) {

					if($currgroup=="MAJOR_OXIDE"){
					
						$getnames = $db->get_results("SELECT DISTINCT(useval) as field_name, display_name, display_order as data_order
						FROM petdb_vars 
						WHERE (upper(vartype) ='$currgroup')
                                                and putin=true  
						and useval not in (select useval from petdb_vars where upper(vartype)='TRACE_ELEMENT')
						ORDER BY display_order ASC");
					
					}else{
					
						$getnames = $db->get_results("SELECT DISTINCT(useval) as field_name, display_name, display_order as data_order
						FROM petdb_vars 
						WHERE (upper(vartype) ='$currgroup') 
                                                and putin=true 
						ORDER BY display_order ASC");
					
					}

					/*
					echo "<pre>";
					print_r($getnames);
					echo "</pre>";
					*/
					
					$showcolumn=false;
					foreach($getnames as $g) {
						if ($g->field_name != 'IN') {
							if (in_array($g->field_name,$item_array)) {
								$showcolumn="true";
							}
						}
					} // foreach
					$showcolumn=true; //put this here to show all columns
					if ($showcolumn) {
				?>
						<td style="white-space:nowrap">
							<div><input type="button" value="Mark All" onClick="javascript:SetAllCheckBoxes('myform', '<?=$currgroup ?>', true);return false"></div>
							<div style="padding-top:5px;"><input type="button" value="Clear All" onClick="javascript:SetAllCheckBoxes('myform', '<?=$currgroup ?>', false);return false"></div>
							<br>
							<?php
							$i=0;
							foreach($getnames as $g) { 
								if ($g->field_name != 'IN') { 
									if (in_array($g->field_name,$item_array)) { 
										echo "<INPUT type='checkbox' class=\"$currgroup\" checked name='".$currgroup."[".$i."]' id='".strtoupper($g->field_name)."' value='".strtoupper($g->field_name)."'>&nbsp;<b>".$g->display_name."</b><br>\n";
										$i++;
									}else{
										echo "<INPUT type='checkbox' class=\"$currgroup\" name='".$currgroup."[".$i."]' id='".strtoupper($g->field_name)."' value='".strtoupper($g->field_name)."'>&nbsp;".$g->display_name."<br>\n";
										$i++;
									}
									$shownitems[]=strtoupper($g->field_name);
								}
							} // foreach
							?>
						</td>
						<?php
					} // if
				} // foreach
				
				//lepr manual entry here...
				//if($searchmaterial!=3){
				if($searchmaterial==34567){
				?>
				
				<td style="white-space:nowrap">
					<input type="button" value="Mark All" onClick="javascript:SetAllCheckBoxes('myform', 'lepr', true);return false">
					<br/>
					<input type="button" value="Clear All" onClick="javascript:SetAllCheckBoxes('myform', 'lepr', false);return false">
					<br>
					
					<?


					$i=1;
					
					
					foreach($lepr_items as $currlepr){

						if (in_array($currlepr,$lepr_item_array)) { 
							echo "<INPUT type='checkbox' class=\"lepr\" checked name='lepr"."[".$i."]' id='".$currlepr."' value='".$currlepr."'>&nbsp;<b>".ucfirst($currlepr)."</b><br>\n";
							$i++;
						}else{
							echo "<INPUT type='checkbox' class=\"lepr\" name='lepr"."[".$i."]' id='".$currlepr."' value='".$currlepr."'>&nbsp;".ucfirst($currlepr)."<br>\n";
							$i++;
						}
						

					}
				}
				?>
					
				</td>
			</tr>
		</table>
		<p/><br/>
		<table cellspacing="0" cellpadding="0" border="0">
			<?php
			if ($chemdata_exists) { 
			?>

<script type="text/javascript">document.getElementById('message').innerHTML='<input style="float:right" type="submit" class="submitbutton" value="Go to Data"><br clear="all">';</script>
<input type="hidden" name="pkey" value="<?=$pkey ?>" >

<?
/*
$formsources="";
$formsourcedelim="";
foreach($datasources as $ds){
	eval("\$thisdsvalue=\$".$ds->name.";");
	$formsources.=$formsourcedelim.'<input type="hidden" name="'.$ds->name.'" value="'.$thisdsvalue.'">';
	$formsourcedelim="\n";
}
*/
?>

<?=$formsources?>

			<?php
			} else {
			?>
			<script type="text/javascript">document.getElementById('message').innerHTML="<p />No schemical data exists for this sample set. Advanced Output is not possible. Please try another search.<p /> ";
			</script>
			<?php
			}
			?>
		</TABLE>

</form>


<script type="text/javascript">

function ShowExisting() {

	
	SetAllCheckBoxes('myform', 'MAJOR_OXIDE', false);
	SetAllCheckBoxes('myform', 'RATIO', false);
	SetAllCheckBoxes('myform', 'NOBLE_GAS', false);
	SetAllCheckBoxes('myform', 'RARE_EARTH_ELEMENT', false);
	SetAllCheckBoxes('myform', 'URANIUM_SERIES', false);
	SetAllCheckBoxes('myform', 'VOLATILE', false);
	SetAllCheckBoxes('myform', 'TRACE_ELEMENT', false);
	SetAllCheckBoxes('myform', 'STABLE_ISOTOPES', false);
	SetAllCheckBoxes('myform', 'RADIOGENIC_ISOTOPES', false);
	


<?
$jitem="var myitems=[";
$jitemdelim="";
foreach($item_array as $thisitem){
	//check in allitems for thisitem
	$thisitem=strtoupper($thisitem);
	echo "console.log('$thisitem');\n";
	if (in_array($thisitem, $shownitems)) {
		$jitem.=$jitemdelim."\"$thisitem\"";
		$jitemdelim=",";
		
		//echo "document.getElementById('$thisitem').checked=true;\n";
		
		echo "$('#".$thisitem."').prop('checked', true);\n";
		
		//echo "console.log('does exist');\n";
	}else{
		//echo "console.log('doesnt exist');\n";
	}
}

$jitem.="];";
?>


	

}

</script>

<?php
//echo nl2br($lepritemstring);
include "includes/ks_footer.html";
?>
