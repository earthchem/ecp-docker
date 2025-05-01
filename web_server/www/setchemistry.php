<?PHP
/**
 * setchemistry.php
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
?>
<script src="AjaxRequest.js" type="text/javascript"></script>
<script src="ajax.js" type="text/javascript"></script>
<script src="jquery.js" type="text/javascript"></script>
<script src="thickbox.js" type="text/javascript"></script>
<?
include("includes/ks_head.html");
//if (isset($_POST)) {print_r($_POST);}
//if (isset($_GET)) {print_r($_GET);}
include('get_pkey.php'); // the primary key for search_query table 
include('db.php'); // database drivers, and connect
$getsearch = $db->get_row("SELECT chemistry, chemmethods, lepr, chemswitch FROM search_query WHERE pkey = $pkey");
// Parse the chemistry into arrays 
$chemistry_array=split(',',$getsearch->chemistry);
$item_exists = array(); // array of chemical/items where parameter is that item must exit  
$item_range = array(); // array of chemical/items where parameter is that item falls within a range 
foreach ($chemistry_array as $c) { 
	if (substr_count($c,':EXISTS')>0) { // pattern is e.g. SIO2:EXISTS
		$item_exists[]=substr($c,0, strpos($c,':') ); // parse out the chemical/item name, e.g. SIO2
	} elseif (substr_count($c,':')==2) {
		$item_range[]=split(':',$c); // parse out the range elements, e.g. 25 TIO2 55 for 25<TIO2<55 
	}
}
// Parse the chemistry methods into arrays 
$temparray1=split(',',$getsearch->chemmethods); 
$chemmethods_array=array();
foreach($temparray1 as $t) { 
	$temparray2=split(':',$t);
	$chemmethods_array[$temparray2[0]]=$temparray2[1];
}
unset($temparray1);unset($temparray2);

$chemswitch=$getsearch->chemswitch;

/* debug:
echo "<p>chemmethods_array "; print_r($chemmethods_array);
echo "<p>item_exists "; print_r($item_exists);
echo "<p>item_range "; print_r($item_range );
*/
?>

<script type="text/javascript">

	function setclear(myitem) {
		
		mycheck=document.getElementById(myitem+'_check');
		if(mycheck.checked==1){
			//alert(myitem+' is checked');
			mylt=document.getElementById(myitem+'_lt');
			mylt.value='0';
			mygt=document.getElementById(myitem+'_gt');
			mygt.value='100';
		}else{
			//alert(myitem+' is not checked');
			mylt=document.getElementById(myitem+'_lt');
			mylt.value='';
			mygt=document.getElementById(myitem+'_gt');
			mygt.value='';
		}
		
	}
	
</script>

<h1>Set Chemistry / CIPW Norms</h1>
<form name='chemistry_form' id='chemistry_form' action='searchupdate.php' method='post'>

<div style="padding-left:10px;padding-top:10px;">
<h3>Query Operator:</h3>
<div style="padding-top:5px;"></div>
<div style="padding-left:15px;"><input type="radio" name="chemswitch" value="and" <? if($chemswitch=="and"){echo "checked=\"checked\"";}?> > AND &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#434343;">i.e. 0 < sio2 < 5 <strong>AND</strong> 0 < tio2 < 5</span></div>
<div style="padding-left:15px;"><input type="radio" name="chemswitch" value="or" <? if($chemswitch=="or"){echo "checked=\"checked\"";}?> > OR &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#434343;">i.e. 0 < sio2 < 5 <strong>OR</strong> 0 < tio2 < 5</span></div>
</div>


<TABLE class='ku_htmloutput' style='border:none'>
<?
$groups=array('MAJOR ELEMENTS','TRACE ELEMENTS','VOLATILE','RARE EARTH ELEMENTS','STABLE ISOTOPES','RADIOGENIC ISOTOPE SYSTEMS','DISEQUILIBRIUM SERIES ISOTOPES'); // 
foreach($groups as $mygroup){

	if($mygroup=='MAJOR ELEMENTS'){
		$showunits="y";
		$unittoshow="&nbsp;&nbsp;&nbsp;WT%";
	}elseif($mygroup=='TRACE ELEMENTS'){
		$showunits="y";
		$unittoshow="&nbsp;&nbsp;&nbsp;PPM";
	}elseif($mygroup=='VOLATILE'){
		$showunits="y";
		$unittoshow="&nbsp;&nbsp;&nbsp;PPM";
	}elseif($mygroup=='RARE EARTH ELEMENTS'){
		$showunits="y";
		$unittoshow="&nbsp;&nbsp;&nbsp;PPM";
	}elseif($mygroup=='STABLE ISOTOPES'){
		$showunits="n";
		$unittoshow="";
	}elseif($mygroup=='RADIOGENIC ISOTOPE SYSTEMS'){
		$showunits="n";
		$unittoshow="";
	}elseif($mygroup=='DISEQUILIBRIUM SERIES ISOTOPES'){
		$showunits="n";
		$unittoshow="";
	}

?>
<tr><td colspan=10 style='border:none;'></td></tr>
  	<TR>
  		<td colspan=8 style='border:none;vertical-align:bottom'>
       	<h3><?=$mygroup?></h3>
  		</td><td style='border:none;vertical-align:bottom' colspan='2'><input type="submit" name="submit1" value="Submit"></td>
  	</TR>
<TR style='border:1px solid;'>
	<td style='border:none;'><h4>EXISTS</h4></td>
	<td style='border:none;'>&nbsp;</td>
	<td style='border:none;'>&nbsp;</td>
	<td style='border:none;'></td>
	<td style='border:none;'>&nbsp;</td>
	<td style='border:none;'>&nbsp;</td>
	<?
	if($showunits=="y"){
	?>
	<td style='border:none;'><h4>UNITS</h4></td>
	<?
	}
	/*
	?>
	<th align='left'>METHOD</th>
	<?
	*/
	?>
	<td style='border:none;'></td>
</tr> 
<?php
$items=$db->get_results("SELECT display_name, field_name, total_in_database FROM data_field_selection WHERE display_group_name='$mygroup' and data_order < 99999 AND total_in_database > 0 ORDER BY data_order") or die('could not select from data_field_selection table');  

foreach($items as $myitem){ 
	$checked = '';  // $checked $lt $gt $methods are form field values. Initialize to null, and possibly populate just below. 
	$lt = '';
	$gt = '';
	$methods = '';
	if (in_array($myitem->field_name,$item_exists,TRUE)) {
		$checked = " checked ";
	} else {
		foreach($item_range as $i) {
			if ($i[1]==$myitem->field_name) {
				$lt = $i[0];
				$gt = $i[2];
			}
		} // foreach
	} // if 
	if (isset($chemmethods_array[$myitem->field_name])) { // methods are set for this chemical/item 
		$methods=$chemmethods_array[$myitem->field_name];
	}


if($mygroup=='MAJOR ELEMENTS'){
	$onclick=" onclick=\"setclear('$myitem->field_name')\"";
}


?>

<tr style='border:1px solid;' bgcolor='#fcfcfc'>
<td  style='border-width:0 0 1 1'>
<input <?=$checked ?> type='checkbox' name='<?=$myitem->field_name?>_check' id='<?=$myitem->field_name?>_check'<?=$onclick?>></td>
<td  style='border-width:0 0 1 0'><input type='text' size='5' name='<?=$myitem->field_name?>_lt' id='<?=$myitem->field_name?>_lt' value='<?=$lt?>' ></TD>
<td  style='border-width:0 0 1 0'>&nbsp;&lt;</TD>
<td  style='border-width:0 0 1 0' align='center'><?=$myitem->display_name?> <?=$myitem->atotal_in_database?></TD>
<td  style='border-width:0 0 1 0'>&lt;&nbsp;</TD>
<td  style='border-width:0 0 1 0'><input type='text' size='5' name='<?=$myitem->field_name?>_gt' id='<?=$myitem->field_name?>_gt' value='<?=$gt?>' ></TD>
<?
if($showunits=="y"){
?>
<td  style='border-width:0 0 1 0'><?=$unittoshow?></TD>
<?
}
/*
?>
<td  style='border-width:0 0 1 0;white-space:nowrap' >
<a href="setmethod.php?item=<?=$myitem->field_name?>&pkey=<?=$pkey?>&height=335&width=410" class="thickbox">SET</a>
<!--<a href="javascript:void(0)" OnClick="greetclear('<?=$myitem->field_name?>', '<?=$pkey?>')">-->
<a href="javascript:void(0)" OnClick="clearmethods('<?=$myitem->field_name?>')">
CLEAR</a></TD>
<td  style='border-width:0 1 1 0'>
<?php
// Method list (e.g. AAS,AES,AR_AR) for this chemical/item (an optional field) is populated in setmethod.php by javascript in 2 elements: a div to display, and a hidden form field
?>
<div id="<?=$myitem->field_name?>_methods_div"><?=$methods?></div>
<input type='hidden' value='<?=$methods?>' name="<?=$myitem->field_name?>_methods" id="<?=$myitem->field_name?>_methods"> 
</TD>
<?
*/
?>
</tr>
<?php
} // foreach $items
} // foreach $groups

//lepr norms below
?>

<TR><td colspan=10 style='border:none;'></td></tr>
  	<TR>
  		<td colspan=8 style='border:none;vertical-align:bottom'>
       	<h3>CIPW Norms</h3>
  		</td><td style='border:none;vertical-align:bottom' colspan='2'><input type="submit" name="submit1" value="Submit"></td>
  	</TR>
<TR style='border:1px solid;'>
	<td style='border:none;'><h4>EXISTS</h4></td>
	<td style='border:none;'>&nbsp;</td>
	<td style='border:none;'>&nbsp;</td>
	<td style='border:none;'><div align="center"><h4>MINERAL</h4></div></td>
	<td style='border:none;'>&nbsp;</td>
	<td style='border:none;'></td>
	<td style='border:none;'><h4>UNITS</h4></td>
</tr> 

<?


//parse through lepr values stored in search_query here to get gt/lt/checked vals
// Parse the lepr vals into arrays 
$lepr_array=split(';',$getsearch->lepr);
$lepr_item_exists = array(); // array of lepr items where parameter is that item must exit  
$lepr_item_range = array(); // array of lepr items where parameter is that item falls within a range 
foreach ($lepr_array as $c) { 
	if (substr_count($c,':checked')>0) { // pattern is e.g. kaliophilite:checked
		$lepr_item_exists[]=substr($c,0, strpos($c,':') ); // parse out the lepr item name, e.g. kaliophilite
	} elseif (substr_count($c,':')==2) {
		$lepr_item_range[]=split(':',$c); // parse out the range elements, e.g. 25 kaliophilite 55 for 25<kaliophilite<55 
	}
}


$lepr_field_array=array('albite','anorthite','apatite','calciumorthosilicate','corundum','diopside','hypersthene','kaliophilite','leucite','nepheline','olivine','orthoclase','perofskite','potassiummetasilicate','quartz','rutile','sodiummetasilicate','titanite','wollastonite');

foreach($lepr_field_array as $currleprname){

	$checked = '';  // $checked $lt $gt are form field values. Initialize to null, and possibly populate just below. 
	$lt = '';
	$gt = '';
	if (in_array($currleprname,$lepr_item_exists,TRUE)) {
		$checked = " checked ";
	} else {
		foreach($lepr_item_range as $i) {
			if ($i[1]==$currleprname) {
				$lt = $i[0];
				$gt = $i[2];
			}
		} // foreach
	} // if 
	
	$onclick=" onclick=\"setclear('$currleprname')\"";
	
?>


<tr style='border:1px solid;' bgcolor='#fcfcfc'>
<td style='border:none;'><input <?=$checked ?> type='checkbox' name='<?=$currleprname?>_check' id='<?=$currleprname?>_check'<?=$onclick?>></td>
<td style='border:none;'><input type='text' size='5' name='<?=$currleprname?>_lt' id='<?=$currleprname?>_lt' value='<?=$lt?>' ></TD>
<td style='border:none;'>&nbsp;&lt;</TD>
<td style='border:none;' align='center'><?=ucfirst($currleprname)?></TD>
<td style='border:none;'>&lt;&nbsp;</TD>
<td style='border:none;'><input type='text' size='5' name='<?=$currleprname?>_gt' id='<?=$currleprname?>_gt' value='<?=$gt?>' ></TD>
<td style='border:none;'>&nbsp;&nbsp;&nbsp;%</td>
</tr>

<?
}
?>

</TABLE>
<input type="hidden" name="pkey" value="<?=$pkey?>">
<input type="hidden" name="glossary_pkey" value="<?=$glossary_pkey?>">
</form>

<?php
include("includes/ks_footer.html");
?>