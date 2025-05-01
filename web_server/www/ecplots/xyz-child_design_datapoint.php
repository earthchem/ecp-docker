<?php 

if (isset($_GET["i"])) {$i = $_GET["i"];} else {$i=0;} // $i is the z interval (scatter plot layer) - and position in an array of z layers 
if (isset($_GET["zfillcolor"])) {$zfillcolor = $_GET["zfillcolor"];} else {$zfillcolor="";} // $i is the z interval (scatter plot layer) - and position in an array of z layers 
// ok, we passed in $zfillcolor[$i] via php in the url, we had it stored in the form - but next time this script is called, we won't have the NEW value resulting from this script having been called once, because the form might not have been submitted - argh! We really need to pick it out of the form field in the opener using javascfript - argh! Try saving it in a hidden form field in THIS script, not sure if this is a slick solution or a clumsy one. On the other hand... actually... it might be ok to present the user with the original values (last time the plot was plotted) for z datapoint fillcolor,strokecolor,etc. in case they want an easy way to restore them after they made different choices they didn't like
if (isset($_GET["zstrokecolor"])) {$zstrokecolor = $_GET["zstrokecolor"];} else {$zstrokecolor="";} // $i is the z interval (scatter plot layer) - and position in an array of z layers 
if (isset($_GET["zpointtype"])) {$zpointtype = $_GET["zpointtype"];} else {$zpointtype="";} // $i is the z interval (scatter plot layer) - and position in an array of z layers 
if (isset($_GET["zpointsize"])) {$zpointsize = $_GET["zpointsize"];} else {$zpointsize="";} // $i is the z interval (scatter plot layer) - and position in an array of z layers 
if (isset($_GET["timestamp"]) && strlen($_GET["timestamp"])>0) {$timestamp = $_GET["timestamp"];} elseif (isset($_SESSION["timestamp"]) && strlen($_SESSION["timestamp"])>0) {$timestamp=$_SESSION["timestamp"];} else {$timestamp="timestampmissing";}

include("includes/plot_design_arrays.php"); // array of pointtypes for the dropdown select box, array of colors for the dropdown select box 

// If the fillcolor and strokecolor are not in our array of colors, add them. Because the color could have come from a gradient function, it may not already be in the array of colors we offer, and it may not have a friendly name defined (like 'red')
if (in_array($zfillcolor, $colors_array)) {} else {$colors_array[$zfillcolor]=$zfillcolor;}
if (in_array($zstrokecolor, $colors_array)) {} else {$colors_array[$zstrokecolor]=$zstrokecolor;}
?>
<html>
<head>
<script type="text/javascript">
<!-- // hide from older browsers
function updateParent(i) {
    // one way to do it, without variable form field names: opener.document.form_xyz.color1.value = document.childForm.color.value;
	// testing - it works: opener.document.form_xyz.test.value =   "updated in xyz_child.php updateParent function " + document.childForm.elements['zpointtype['+i+']'].value; alert(i);
	opener.document.form_xyz.elements[  'zpointtype['+i+']' ].value =   document.childForm.elements['zpointtype['+i+']'].value;
	opener.document.form_xyz.elements[  'zpointsize['+i+']' ].value =   document.childForm.elements['zpointsize['+i+']'].value;
	opener.document.form_xyz.elements['zstrokecolor['+i+']' ].value = document.childForm.elements['zstrokecolor['+i+']'].value;
	opener.document.form_xyz.elements[  'zfillcolor['+i+']' ].value =   document.childForm.elements['zfillcolor['+i+']'].value;
	return false;
}
function resetParent(i) {
	opener.document.form_xyz.elements[  'zpointtype['+i+']' ].value = "";
	opener.document.form_xyz.elements[  'zpointsize['+i+']' ].value = "";
	opener.document.form_xyz.elements['zstrokecolor['+i+']' ].value = "";
	opener.document.form_xyz.elements[  'zfillcolor['+i+']' ].value = "";
	var zdivname='div_z_' + i;
	opener.document.getElementById(zdivname).innerHTML="<div style='height:30px;vertical-align:middle'>cleared</div>"; // remove the single-point plot image for the z interval
	self.close();
	return false;
}

var xmlhttp
var i 

function loadXMLDoc(i)
{ // test: alert('in loadXMLDoc(' + i + ')');
updateParent(i);
xmlhttp=null
// code for Mozilla, etc.
if (window.XMLHttpRequest)
  {
  xmlhttp=new XMLHttpRequest()
  }
// code for IE
else if (window.ActiveXObject)
  {
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP")
  }
if (xmlhttp!=null)
  {
  var fillcolor = document.childForm.elements[ 'zfillcolor['+i+']' ].value;
  var strokecolor = document.childForm.elements[ 'zstrokecolor['+i+']' ].value;
  var pointtype = document.childForm.elements[ 'zpointtype['+i+']' ].value;
  var pointsize = document.childForm.elements[ 'zpointsize['+i+']' ].value;
  var timestamp = opener.document.form_xyz.timestamp.value; // we save the timestamp as a hidden form field in form_xyz, just to use it here, to use in filenames
  opener.document.form_xyz.i.value = i; // save this variable so we can use it in function state_Change
  // test - it works     opener.document.form_xyz.subtitle.value = i; 
  xmlhttp.open("GET","xyz-make-z-datapoint.php?fillcolor=" + escape(fillcolor) + "&strokecolor=" + escape(strokecolor) + "&pointtype=" + pointtype + "&pointsize=" + pointsize + "&timestamp=" + timestamp + "&i=" + i ,true); // You need escape to escape out the # in the colors
  xmlhttp.onreadystatechange=state_Change;
  window.setTimeout('xmlhttp.send(null)',100);  
  window.setTimeout('self.close()', 1000); // close this popup window, need the timeout or it happens too soon 
  }
else
  {
  alert("Your browser does not support XMLHTTP.")
  }
}

function state_Change()
{ 
// if xmlhttp shows "loaded"
if (xmlhttp.readyState==4)
  {
  // if "OK"
  if (xmlhttp.status==200)
    {
    var response=xmlhttp.responseText;
	// test - it works                 alert(response);
	// i eliminated this div in xyz.php     opener.document.getElementById('div_z').innerHTML=response;
	var i = opener.document.form_xyz.i.value; // retrieve the value of i, which we saved by storing it in this form field in funcion loadXMLDoc . We need it to identify the right z interval div, below
	var timestamp = opener.document.form_xyz.timestamp.value; // we saved this variable, to use to make a set of unique filenames associated with this plotting session
	// just to test - it works: opener.document.form_xyz.subtitle.value =   i + " testing from function state_Change " ;
 
 	// replace the contents of the div for z interval i, with the output from test.php: a single-point plot that shows the user the datapoint they designed for this z interval
	var zdivname='div_z_' + i;
	opener.document.getElementById(zdivname).innerHTML=response;
	document.getElementById('div_zz').innerHTML=response; // the div in this window 
    }
  else
    { 
    alert("Problem creating image of datapoint")
    }
  }
}
-->
</script>
</head>
<script type="text/javascript">
<!--
// just testing to see if we can get the values from the form fields into php somehow through javascript
var zfillcolor=document.form_xyz.zfillcolor[i].value;
alert(zfillcolor);
-->
</script>
<?php



echo "<body>";
echo "
<FORM NAME='childForm' onsubmit='loadXMLDoc($i)'>
";

?>

<table width=100% align=center>
<tr>
  <td colspan=2 align=center><nobr><b>Design datapoints for z interval <?php echo $i; ?></b></nobr></td>
</tr>
<tr>
  <td colspan=2><br />
<?php echo "<input name='clearbutton' type='button' value='Clear' onclick=\"resetParent($i)\" > design and close"; ?> <p />
<?php echo "<input name='closebutton' type='button' value='Close' onclick='self.close()'> without setting"; ?> <p />
<?php echo '<input name="setbutton" type="button" value="SET" onclick="loadXMLDoc('.$i.')" > and close'; ?> <p />
</tr>
  </td>
</tr>
<!--onClick="self.close()"-->
<?
/*
echo "<tr><td colspan=2><input type='button' onClick=\"toggleBox('zdiv$i',0);\" value='close'></td></tr>";
*/
echo "<tr><td align=right>point type </td><td>
<select ";
//echo " onchange=\"getShowFormSelection(this,'div_z_show_pointtype$i')\" "; // When user makes a selection, display the selection in a div 
//echo " onChange='updateParent($i)' ";

// z interval datapoint pointtype
echo " name='zpointtype[$i]'>";
echo "<option value=''>";
foreach($pointtypes_array as $key=>$val){echo "<option "; 
if ($zpointtype==$val) { echo " selected "; } echo " value='$val'>$key</option>";}
echo "</select></td></tr>";  

// z interval datapoint fillcolor
echo "<tr><td align=right>point fill color </td><td>
<select "; 
//echo " onChange='updateParent($i)' ";
//echo " onchange=\"getShowFormSelection(this,'div_z_show_fillcolor$i')\" "; // When user makes a selection, display the selection in a div 
echo " name='zfillcolor[$i]'>";
echo "<option value=''>";
foreach($colors_array as $key=>$val){echo "<option "; 
echo " style='background-color:$val' " ;
if ($zfillcolor==$val) { echo " selected "; } echo " value='$val'>$key </option>";}
echo "</select></td></tr>";  

// z interval datapoint strokecolor
echo "<tr><td align=right>point outline color </td><td>
<select ";
//echo " onChange='updateParent($i)' ";
//echo " onchange=\"getShowFormSelection(this,'div_z_show_strokecolor$i')\" "; // When user makes a selection, display the selection in a div 
echo " name='zstrokecolor[$i]'>";
echo "<option value=''>";
foreach($colors_array as $key=>$val){echo "<option "; 
echo " style='background-color:$val' " ;
if ($zstrokecolor==$val) { echo " selected "; } echo "value='$val'>$key</option>";}
echo "</select></td></tr>";  

// z interval datapoint pointsize
echo "<tr><td align=right>point size </td><td>
<select  ";
//echo " onChange='updateParent($i)' ";
//echo " onchange=\"getShowFormSelection(this,'div_z_show_pointsize$i')\" "; // When user makes a selection, display the selection in a div 
echo " name='zpointsize[$i]'>";
echo "<option value=''>";
for ( $n=1; $n <= 12; $n++) {echo "<option value='$n' "; 
if ($zpointsize==$n) {echo " selected ";} echo " > $n";}
echo "</select> <font size=1>pixels</font></td></tr>";  

echo "<tr><td colspan=2 align=center><div id='div_zz' name='div_zz' style='width:100%;text-align:center'>";
$z_point_image_filename="plots/z-$i-$timestamp.png";  // If we made this single-point scatter plot file before, to illustrate this datapoint design, display it 
echo "<img src='$z_point_image_filename' alt='$z_point_image_filename'>"; 
//echo $z_point_image_filename; 
echo "</div></td></tr>

</table>";

echo "i = $i <br />zfillcolor = $zfillcolor <br />zstrokecolor = $zstrokecolor <br />zpointtype = $zpointtype <br />zpointsize = $zpointsize <br />timestamp $timestamp";

?>
</FORM>
</body>
</html>
