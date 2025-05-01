<?php
//foreach($_POST as $key=>$value){
//echo "$key : $value <br>";
//}
//echo "<br>";
// debug: foreach($_POST as $key=>$val) {echo "<br>$key &bull; $val ";};
// Harker plots. Modified to exist on matisse server, August 2008 - E.E. Jones
// Navdat and EarthChem both link to this script, from the Output Data and Results pages, respectively. 
$earthchem_top_dir="earthchemphp3";
require ("../jpgraph/jpgraph.php"); // jpgraph driver
require ("../jpgraph/jpgraph_scatter.php"); // jpgraph scatter plot 
//$gJpgBrandTiming=true; //This cool feature displays the time it took to plot, in the LL corner - it works!
$earthchem=false;$navdat=false;$getsample=false; // A flag to indicate which website/page originally sent us to this page
$referring_website = isset($_POST['referring_website']) ? $_POST['referring_website'] : '';
if ($referring_website=='earthchem') {$earthchem=true;} elseif ($referring_website=='navdat') {$navdat=true;} elseif ($referring_website=='getsample') {$getsample=true;} else {echo "<p>There is a problem. No referring website was identified.<p>";exit;}
// Include a page header, and, later, a footer 
if ($navdat) {
	include "../navdat/NewHeader.cfm";
	require "includes/navdat_db.php"; // database driver and connect 
	echo '<div id="maindiv" style="margin:20px ">';
} elseif ($earthchem) {
	//echo "is earthchem <br><br><br>";
	include "../includes/ks_head.html";
	include "../db.php"; // database driver and connect 
	echo '<div>'; 
} else {
	echo '<div>';
}
ob_flush();flush();
?>
   <h1>Harker Diagrams</h1>
<?
if($earthchem){
	$pkey=$_POST['pkey'];
	?>
	<div style="width:800px;text-align:right;">
	<?
	echo "<input type=button value=\"Back to EarthChem Output\" onClick=\"window.location = 'http://portal.earthchem.org/results.php?pkey=$pkey';\">";
	?>
	</div>
	<?
}
?>
   <b>Values used for Harker Diagrams</b>
      <ul>
         <li>The Harker diagrams are made using a normalized major oxide dataset.</li>
         <li>For a sample to plot, it must contain data for SiO2, Fe (as FeO and/or Fe2O3), Al2O3, Na2O, K2O, CaO, MgO, and P2O5.</li>
         <li>Fe is converted to FeO (FeO*), and the oxides are normalized to a 100% volatile-free basis.</li>
      </ul>
      
      <input type=button value="Click Here for SVG Harkers" onClick="window.location = '/harker.php?pkey=<?=$pkey?>';">
   
<?php
ob_flush();flush();

// Get form variables or set defaults
// Note: database column names are in lower case and the sql must also use lower case 
$chemicals_array = isset($_POST['chemicals_array']) ? $_POST['chemicals_array'] : array('al2o3'=>'Al2O3','cao'=>'CaO','feot'=>'FeO*','k2o'=>'K2O','mgo'=>'MgO','na2o'=>'Na2O','p2o5'=>'P2O5','tio2'=>'TiO2'); // All 8 chemicals that are possible to plot SiO2 against, for the Harker plots. field_name=>display_name 
//$chem2_array = isset($_POST['chem2_array']) ? $_POST['chem2_array'] : $chemicals_array; // User's selections of chemicals against which to plot SiO2 (default is all 8)
if (isset($_POST['chem2_array']) && sizeof($_POST['chem2_array'])>0) { $temp_array=array();
	foreach($_POST['chem2_array'] as $key=>$val) { // Form passed a 1D array, make it into a 2D array of field_name=>display_name using the lookup array $chemicals_array
		$temp_array[$val]=$chemicals_array[$val];   
		//unset($chem2_array[$key]);
	} 
	$chem2_array=$temp_array;
} else {
	$chem2_array=$chemicals_array;
} //echo "<br><font color=green>";print_r($chem2_array); echo sizeof($chem2_array); echo " "; echo sizeof($chemicals_array); echo"</font>";
$links = isset($_POST['links']) ? $_POST['links'] : false; // Do we want an image map with links to individual samples? (available for single plot only) if so we'll have to store an image on the server before displaying it
$plotsize = isset($_POST['plotsize']) ? $_POST['plotsize'] : 300; // side measurement (pixels) of the square finished plot
$pointtype = isset($_POST['pointtype']) ? $_POST['pointtype'] : 'MARK_DIAMOND';
$fillcolor = isset($_POST['fillcolor']) ? $_POST['fillcolor'] : '#ff0000'; 
$strokecolor = isset($_POST['strokecolor']) ? $_POST['strokecolor'] : '#000000';
$pointsize = isset($_POST['pointsize']) ? $_POST['pointsize'] : '3';


// Get the sql statement to retrieve the datapoints for the user's serach query results. 
// The first time we arrive here from EarthChem, $harker_sql is passed as a hidden form variable.
// The first time we arrive here from Navdat, $navdatsqlstatement is passed as a hidden form variable and we have to rewrite parts of it.
// If this form calls itself, when the user submits the form (to replot), $harker_sql is passed as a hidden form variable.
$harker_sql = isset($_POST['harker_sql']) ? $_POST['harker_sql'] : "";
if ($navdat && $harker_sql=="") { // For Navdat, the first time we arrive at this page, from Output Data page, we have to build the $harker_sql statement
	$navdatsqlstatement = isset($_POST['navdatsqlstatement']) ? $_POST['navdatsqlstatement'] : "";
	$select_chems=""; $where_chems="";
	$i = strripos($navdatsqlstatement,'FROM '); 
	$harker_sql = substr($navdatsqlstatement,$i); // Chop off the SELECT part, and replace it with something else 
	foreach($chemicals_array as $field_name=>$display_name) {
		$select_chems .= ", $field_name ";
		$where_chems .= " AND $field_name BETWEEN 0 AND 100 ";
	}
	$select_chems = substr($select_chems,1); // remove leading comma 
	$harker_sql = "SELECT $select_chems, sio2, norm.sample_num $harker_sql $where_chems"; // We need sample_num because if the user chose links, we need to pass sample_num in the url to GetSample.cfm. (For EarthChem, the url is retrieved as $url in $tas_sql.)
	$harker_sql = str_replace("view_data_output","view_norm_feo_total",$harker_sql);
	$harker_sql = str_replace("view_norm_fe2o3_total","view_norm_feo_total",$harker_sql);
	$harker_sql = str_replace("view_norm_feo_plus_fe2o3","view_norm_feo_total",$harker_sql);

}
//echo "<p>harker_sql for navdat<br>$harker_sql "; 

//echo nl2br($harker_sql);exit();


if ($harker_sql=="") {
	echo "<p>Something is wrong. There is no query to execute. Please start a new search and try again.<p>"; 
	exit; 
} // This should never happen.


$timestamp = isset($_POST['timestamp']) ? $_POST['timestamp'] : time(); // Unique number to build a unique filename for the plot image

include "includes/plot_design_arrays.php"; // Arrays of jpgraph point shapes, icons & colors we offer in the form. Include it after $navdat is set, because Navdat gets a couple of icons that EarthChem does not get.


echo '<div name="plots" style="margin-left:auto;margin-right:auto;margin-top:20px;text-align:center;width:100%;">'; // Need this because the div that follows is inline 




/*/ Put a progress bar animated gif where each plot image will eventually go 
// 128 x 15 - the dimensions of the progress.gif image
$hspace=floor($plotsize-128)/2 + 20;
$vspace=floor($plotsize-16)/2 + 20;
$progress_image = '<img src="images/progress.gif" height=16 width=128 style="margin: '.$vspace.' '.$hspace.' '.$vspace.' '.$hspace.'">';  // css in EarthChem needs style margin, not hspace= vspace=
*/

// Put a waiting animated gif where each plot image will eventually go 
// 28x28 - the dimensions of the waiting.gif image
$hspace=floor($plotsize-28)/2 + 20;
$vspace=floor($plotsize-28)/2 + 20;
$progress_image = '<img src="images/waiting.gif" height=28 width=28 style="margin: '.$vspace.' '.$hspace.' '.$vspace.' '.$hspace.'">';  // css in EarthChem needs style margin, not hspace= vspace=


$plotnum=0;
foreach ($chem2_array as $field_name=>$display_name) { 
if (($plotnum % 2) == 0) {echo "<br>";} 
$plotnum++;
// Make a div containing a progress image, that will be replaced with null when the plot is displayed
echo '
<!-- PLOT DIV FOR '.$field_name.' -->
<div id="'.$field_name.'" style="display:inline;">'
.$progress_image.
'</div><!-- div $field_name -->
';
} // foreach 

echo '<div id="div_message" style="margin:50px 0px;">';
if ($links) {
	echo 'Click on a datapoint to view sample data.';
}
echo '</div>';

ob_flush();flush();















// Make the plots, and replace the inner HTML of the divs with the plot images. 
foreach ($chem2_array as $field_name=>$display_name) { 
// Postgresql is so fast, it is not worth the confusion to rewrite the sql to search for fewer than the 8 chemicals if the user chose to make fewer plots 
// If we have not already executed the query, do it. This should execute only the first time through the foreach loop. 
if (!isset($rows)) {
	//echo nl2br($harker_sql);
	$rows=$db->get_results($harker_sql);
	//or die ("COULD NOT EXECUTE harker_sql QUERY NEAR LINE 100");
}
	 	unset($datax);unset($datay);unset($target);unset($alt); // 2 arrays hold the x,y scales. 2 others hold target & alt text for links on datapoints, if user asked for them
		if($db->num_rows > 0){
			foreach ($rows as $row) { 
				$datax[]=$row->sio2;$datay[]=$row->$field_name;
				if ($links) {
					if ($navdat) {$url = "http://matisse.kgs.ku.edu/navdat/NavdatPortal/GetSample.cfm?sample_num=".$row->sample_num;}
					if ($earthchem) {$url=$row->url;}
					$alt_msg=""; if (strlen($url)<7) {$url="harker_no_link.php"; $alt_msg="No link to sample data is available ~ ";}
					$target[]="javascript:window.open('$url','newwindow','height=600,width=800,scrollbars=1,resizable=1');newwindow.focus()";
					$alt[]=$alt_msg.$row->sio2." ~ ".$row->$field_name;
					//debug: echo "<br />$samplecount ".$url." ".$row->sio2." ".$row->$field_name;
				}
			}
			$samplecount=sizeof($datax); //echo $samplecount." samples";
			if ($samplecount<1) {
				echo "<br />No sssssamples meet the criteria for plotting on a Harker diagram.<p />";
			}
		}else{
			
			echo "<br />No samples meet the criteria for plotting on a Harker diagram.<p />";

			$plotnum=0;
			foreach ($chem2_array as $field_name=>$display_name) { 
			?>			
				<script language="javascript">
				document.getElementById('<?=$field_name?>').innerHTML='';
				</script>
			<?
			} // foreach 

			ob_flush();flush();

		}
// We have x,y values for samples - proceed with making the Harker diagram.
$scale=$plotsize/100;
$margin=floor(($plotsize-100)/5); if ($margin>100) {$margin=100;} elseif ($margin<50) {$margin=50;}
$margin2=$margin; // for the top and right margins, a little smaller
$titlemargin=20;
$axistitle_fontsize=5+$scale; //10; if ($plotsize<500) {$axistitle_fontsize-=2;} elseif ($plotsize>600) {$axistitle_fontsize+=2;}
$title_fontsize=7+$scale; //14; if ($plotsize<500) {$title_fontsize-=2;} elseif ($plotsize>600) {$title_fontsize+=2;}
$ticklabel_fontsize=7; if ($scale > 5) {$ticklabel_fontsize++;}
$xtitlemargin=32+$scale; $ytitlemargin=$xtitlemargin+$axistitle_fontsize+4; // xtitlemargin was 40
$gridcolor="#c7dedf";
$titlecolor="#363535";
$subtitlecolor="#4c4b4b";
$tickcolor=$subtitlecolor;
if ($fillcolor > "" ) {if (strlen($fillcolor) == 6) {$fillcolor="#".$fillcolor;}} else {$fillcolor="#ff0000";}
if ($strokecolor > "" ) {if (strlen($strokecolor) == 6) {$strokecolor="#".$strokecolor;}} else {$strokecolor="black";}

$xtitle = 'SiO2';
$ytitle = $display_name;
$title = $ytitle." vs ".$xtitle;

/*/ where your fonts are stored
DEFINE("TTF_DIR","/usr/src/fonts/");
// a font file that you know that you have
DEFINE("TTF_FONTFILE","VERA.ttf");
*/

// Define where our TTF fonts are stored and which font to use
DEFINE("TTF_DIR","usr/X11R6/lib/X11/fonts/truetype/");
DEFINE("TTF_FONTFILE","VERA.ttf");
DEFINE("TTF_FONTFILE","VERAbd.ttf");

$graph = new Graph($plotsize,$plotsize,"auto");
$graph->CheckCSIMCache( "image1",10); // image map cache
$graph->img->SetAntiAliasing();
$graph->SetScale("linlin"); // x and y scales are linear (not text, int, or log)

$graph->img->SetMargin($margin,$margin2,$margin2,$margin); // left, right, top, bottom
//$graph->SetShadow(); // drop shadow around graph

$graph->SetColor("#ffffff");
$graph->SetMarginColor("#ffffff");
$graph->xgrid->SetColor($gridcolor);

/////$graph->SetBackgroundImage("watermark.png",BGIMG_FILLFRAME); // NAVDAT logo for watermark
$watermark_fontsize=floor($plotsize/100) * 3;
if ($navdat) {$graph->footer->left->Set("NAVDAT");}
elseif ($earthchem) {$graph->footer->left->Set("earthchem"); $watermark_fontsize+=2;}
else {$graph->footer->left->Set("error 34213"); }
$graph->footer->left->SetColor('#efefef');
$graph->footer->left->SetFont(FF_VERA,FS_BOLD,$watermark_fontsize);

$samplecount=sizeof($datax);
$graph ->footer->right->Set($samplecount." samples"); // # of samples in LR corner
$graph->footer->right->SetColor($subtitlecolor);
$count_fontsize=$axistitle_fontsize-2;
$graph->footer->right->SetFont(FF_VERA,FS_NORMAL,$count_fontsize);



$txt = new Text($xtitle);
$txt->SetFont(FF_VERA,FS_NORMAL,$axistitle_fontsize);
$txt->SetColor($subtitlecolor);
$y=$plotsize-$axistitle_fontsize;$x=$plotsize/2;
$txt->SetAngle(0);
$txt->SetPos($x,$y,center,center);
$graph->Add($txt);

$txt2 = new Text($ytitle); // Note: In jpgraph on matisse, must name the var something different, e.g. $txt2, or this will not appear 
$txt2->SetFont(FF_VERA,FS_NORMAL,$axistitle_fontsize);
$txt2->SetColor($subtitlecolor);
$x=0+$axistitle_fontsize;$y=$plotsize/2;
$txt2->SetAngle(90); 
$txt2->SetPos($x,$y,center,center);
$graph->Add($txt2); 



/* The following 2 blocks generate the x and y axis titles more or less automatically, but I chose instead to use arbitrary text, above, so I could butt these against the left and bottom margins
$graph->xaxis->SetTitle($xtitle,'middle');
$graph->xaxis->title->SetFont(FF_VERA,FS_NORMAL,$axistitle_fontsize);
$graph->xaxis->title->SetColor($subtitlecolor);
$graph->xaxis->SetTitleMargin($xtitlemargin);
$graph->yaxis->SetTitle($ytitle,'middle');
$graph->yaxis->title->SetFont(FF_VERA,FS_NORMAL,$axistitle_fontsize);
$graph->yaxis->title->SetColor($subtitlecolor);
$graph->yaxis->SetTitleMargin($ytitlemargin);
*/

//$graph->ygrid->SetFill(true,'#DCF7DD@0.5','#8CD78D@0.5');
$graph->xgrid->Show();

$graph->title->Set($title);
$graph->title->SetFont(FF_VERA,FS_NORMAL,$title_fontsize);
$graph->title->SetColor($titlecolor);
$graph->title->SetMargin($titlemargin);

$graph->xaxis->SetFont(FF_VERA,FS_NORMAL,$ticklabel_fontsize);
$graph->xaxis->SetColor($gridcolor,$tickcolor);
$graph->yaxis->SetFont(FF_VERA,FS_NORMAL,$ticklabel_fontsize);
$graph->yaxis->SetColor($gridcolor,$tickcolor);
$sp1 = new ScatterPlot($datay,$datax);

// Note: jpgraph will not accept a variable in place of, e.g. MARK_DIAMOND in the SetType() 
if ($pointtype == 'MARK_DIAMOND') {
$sp1->mark->SetType(MARK_DIAMOND);
} elseif ($pointtype == 'MARK_UTRIANGLE') {
$sp1->mark->SetType(MARK_UTRIANGLE);
} elseif ($pointtype == 'MARK_DTRIANGLE') {
$sp1->mark->SetType(MARK_DTRIANGLE);
} elseif ($pointtype == 'MARK_SQUARE') {
$sp1->mark->SetType(MARK_SQUARE);
} elseif ($pointtype == 'MARK_CIRCLE') {
$sp1->mark->SetType(MARK_CIRCLE);
} elseif ($pointtype == 'MARK_FILLEDCIRCLE') {
$sp1->mark->SetType(MARK_FILLEDCIRCLE);
} elseif ($pointtype == 'MARK_CROSS') {
$sp1->mark->SetType(MARK_CROSS);
} elseif ($pointtype == 'MARK_STAR') {
$sp1->mark->SetType(MARK_STAR);
} elseif ($pointtype == 'MARK_X') {
$sp1->mark->SetType(MARK_X);
//} elseif ($pointtype == 'MARK_FLAG1') {
//$sp1->mark->SetType(MARK_FLAG1,'United Nations');
} elseif ($pointtype == 'MARK_IMG_SBALL') {
$sp1->mark->SetType(MARK_IMG_SBALL,'red');
} elseif ($pointtype == 'MARK_IMG_MBALL') {
$sp1->mark->SetType(MARK_IMG_MBALL,'red');
} elseif ($pointtype == 'MARK_IMG_LBALL') {
$sp1->mark->SetType(MARK_IMG_LBALL,'red');
} elseif ($pointtype == 'MARK_IMG_SQUARE') {
$sp1->mark->SetType(MARK_IMG_SQUARE,'red'); 
} elseif ($pointtype == 'MARK_IMG_STAR') {
$sp1->mark->SetType(MARK_IMG_STAR,'blue');
} elseif ($pointtype == 'MARK_IMG_DIAMOND') {
$sp1->mark->SetType(MARK_IMG_DIAMOND,'red'); 
} elseif ($pointtype == 'MARK_IMG_BEVEL') {
$sp1->mark->SetType(MARK_IMG_BEVEL,'red'); 
} elseif ($pointtype == 'MARK_IMG_SPUSHPIN') {
$sp1->mark->SetType(MARK_IMG_SPUSHPIN,'red'); 
} elseif ($pointtype == 'MARK_IMG_LPUSHPIN') {
$sp1->mark->SetType(MARK_IMG_LPUSHPIN,'red'); 
} elseif ($pointtype == 'JAYHAWK') {
$sp1->mark->SetType(MARK_IMG,'jayhawk.png');
//} elseif ($pointtype == 'GHAWK') {
//$sp1->mark->SetType(MARK_IMG,'ghawk.png');
} elseif ($pointtype == 'TARHEEL') {
$sp1->mark->SetType(MARK_IMG,'tarheel.png');
} elseif ($pointtype == 'BUFFALO') {
$sp1->mark->SetType(MARK_IMG,'buffalo.png');
} else {
$sp1->mark->SetType(MARK_DIAMOND);
}



$sp1->mark->SetFillColor($fillcolor);
$sp1->mark->SetColor($strokecolor);
$sp1->mark->SetWidth($pointsize);
if ($links) {$sp1->SetCSIMTargets($target,$alt);} // make an imagemap from the target and alt arrays
$graph->Add($sp1);
$graph->SetFrame(true,'#ffffff',1);

$image_filename="plots/harker-".$referring_website.'-'.$field_name."-".$timestamp.".png";
$graph->Stroke($image_filename); // generate the image, send it to the browser without saving it on the server
$image_margin=floor($plotsize/10); // white space around the plot image




echo '
<!-- ************************************************************ HARKER PLOT IMAGE ******************************************************************************************* -->
';

//Replace the progress image with the plot. Make each plot link directly to the image file. 
if ($links)
{ // If the user selected links in the form, add an image map with links to the individual samples
$mapName = 'ImageMap-'.$image_filename;
$imgMap = $graph->GetHTMLImageMap($mapName);
echo $imgMap;
$plot_image = '<a href="'.$image_filename.'"><img ismap usemap="#'.$mapName.'" src="'.$image_filename.'" style="margin:20px" border=0 alt="'.$title.'"></a>';
} else {
$plot_image = '<a href="'.$image_filename.'"><img src="'.$image_filename.'" style="margin:20px" border=0 alt="'.$title.'"></a>';
} // if links, else


echo "
<script language='javascript'>
document.getElementById('".$field_name."').innerHTML='".$plot_image."';
</script>
";

ob_flush();flush(); // force the plot to appear in the browser 

} // foreach chemical in the array



echo '</div><!-- plots -->';
ob_flush();flush();













// ************************************************* THE FORM ******************************************************

?> 

<div name="div_form" style="margin-top:50px;"> 
<a name="form"></a>
<form name="theform" id="theform" action="" method="post">
<table width="80%" border=0 align=center cellpadding="0" cellspacing="10">
	<tr><td colspan="2">
	       <div name="subtitle" style="float:left;vertical-align:top"><h3>Design and view Harker diagrams:</h3></div>
		   <div name="button" style="float:right;vertical-align:top">

         <?php
if ($earthchem) { 
// use a plain HTML button for EarthChem, per Jason/Kerstin     echo '<input type="submit" value="" style="background-image:url(images/EC_plot.gif); width:61px; height:21px; border:0;" />';
?>
<input type="submit" name="submit" value="Plot">         
         <?php
} elseif ($navdat) {
?>
<input type="submit" value="" style="background-image:url(images/navdat_plot.gif); width:68px; height:30px; border:0;" />
         <?php
}
?>
</div><!-- div button -->
</td></tr> 
         <tr valign="top">
            <td width=50% align="right" valign="top" nowrap style="vertical-align:top">chemical to plot<br />
               vs. SiO2</td>
            <td valign="top">
               <select multiple name="chem2_array[]">
                  <?php
foreach($chemicals_array as $field_name=>$display_name)
{echo '<option '; 
if (isset($chem2_array[$field_name])) {echo " selected "; }
echo ' value="'.$field_name.'">'.$display_name;}
?>
               </select>
               <font size=1>For multiple selections use shift key.</font></td>
         </tr>
         <tr>
            <td align=right valign="top" nowrap>plot size</td>
            <td>
               <select name="plotsize">
                  <?php
for ( $i = 200; $i <= 1000; $i += 100) {
echo "<option value='$i'"; if ($plotsize==$i) {echo " selected ";} echo "> $i x $i ";
}
?>
               </select>
               <font size=1>pixels</font></td>
         </tr>
         <?php
 
$pointtypes_array=array_merge($pointshapes_colors_array,$icons_colors_array,$custom_icons_array);

?>
         <tr>
            <td align=right>point shape</td>
            <td>
               <select name="pointtype" id="pointtype" onChange="if (

<?php
foreach($pointshapes_colors_array as $a) {
	echo " this.value == '".$a['pointtype']."' || ";
}

?>
this.value=='xxx' ) {
	enable_colors();
} else {
	disable_colors();
}">
                  <?php
//this.options[this.selectedIndex].value
foreach($pointtypes_array as $a)
{
?>
                  <option 
<?php
if ($pointtype==$a['pointtype']) {echo ' selected ';} echo ' value="'.$a['pointtype'].'">'.$a['pointtype_display_name'].'</option>';}
?>

               </select>
            </td>
         </tr>
         <tr valign=top>
            <td align=right>
               <div id="text1" style="display:inline">point fill color</div>
            </td>
            <td>
               <select name="fillcolor" >
                  <div id="fillcolors" name="fillcolors">
                  <?php
foreach($colors_array as $key=>$val)
{
	echo '<option '; if ($fillcolor==$val) {echo ' selected ';} echo ' style="background-color:'.$val.'" value="'.$val.'">'.$key;
}
?>
                  </div>
               </select></td></tr>
         <tr valign=top>
            <td align=right>
               <div id="text2" style="display:inline">point outline color</div>
            </td>
            <td>
               <select name="strokecolor" id="strokecolor" >
                  <?php
foreach($colors_array as $key=>$val)
{
	echo '<option '; if ($strokecolor==$val) {echo ' selected ';} echo ' style="background-color:'.$val.'" value="'.$val.'">'.$key;
}
?>
               </select></td></tr>
         <tr valign=top>
            <td align=right>
               <div id="text3" style="display:inline">point size</div>
            </td>
            <td>
               <select name="pointsize" >
                  <?php
for ( $i = 1; $i <= 10; $i++) {
echo "<option value='$i' "; if ($pointsize==$i) {echo " selected ";} echo " > $i";
}
?>
               </select>
            </td>
         </tr>
         <tr valign=top>
            <td align="right">Datapoints link to individual sample data?</td><td><input 
<?php
if ($links) {echo ' checked ';} 
?>
type="checkbox" name="links">
            </td>
         </tr>
      </table>
      <?php
echo '<input type="hidden" name="referring_website" value="'.$referring_website.'">';  
echo '<input type="hidden" name="timestamp" value="'.$timestamp.'">'; // Keep the same unique string to build the filename, for this query, so user generates a new plot he is overwriting other images on the server (other plots he just made)
echo '<input type="hidden" name="harker_sql" value="'.$harker_sql.'">';
echo '<input type="hidden" name="pkey" value="'.$pkey.'">';

?>
</form>
</div><!-- div_form-->
<?php
ob_flush();flush();
?>

<script language="javascript">
function disable_colors() { // disable and gray-out 3 select boxes
	document.theform.fillcolor.disabled=true;document.getElementById('text1').style.opacity=.5; // fillcolor field
	document.theform.strokecolor.disabled=true;document.getElementById('text2').style.opacity=.5; // strokecolor field
	document.theform.pointsize.disabled=true;document.getElementById('text3').style.opacity=.5; // pointsize field
}
function enable_colors() { // enable and make fully visible 3 select boxes
	document.theform.fillcolor.disabled=false;document.getElementById('text1').style.opacity=1;
	document.theform.strokecolor.disabled=false;document.getElementById('text2').style.opacity=1;
	document.theform.pointsize.disabled=false;document.getElementById('text3').style.opacity=1;
}
function disable_fillcolor() {
	document.theform.fillcolor.disabled=true;document.getElementById('text1').style.opacity=.5; // fillcolor field
}

</script>

<?php
// If the pointtype selected (or defaulted to) is one that does not accept additional selections (colors, size), disable those dropdown select boxes. 
?>
<script language="javascript">enable_colors();</script>
<?php
foreach($icons_colors_array as $a) {
	if ($pointtype == $a['pointtype']) {  
?>
<script language="javascript">disable_colors();</script>
<?php
	}
}
foreach($custom_icons_array as $a) { 
	if ($pointtype == $a['pointtype']) {  
?>
<script language="javascript">disable_colors();</script>
<?php
	}
}
foreach($pointshapes_colors_array as $a) { 
	if ($pointtype == $a['pointtype']) {  
	if (strpos($a['pointtype_display_name'],'outline')>0) {  
	?>
	<script language="javascript">disable_fillcolor();</script>
	<?php
	}
	}
}

/* FOR DEV & TEST 
$showwork=true;
if ($showwork) {
echo "<p>harker_sql <p>$harker_sql<p>";
// We have x,y values for samples - proceed with making the Harker diagram.
echo "<br>scale $scale"; //=$plotsize/100;
echo "<br>margin $margin"; //=floor(($plotsize-100)/5); if ($margin>100) {$margin=100;} elseif ($margin<50) {$margin=50;}
echo "<br>margin2 $margin2"; //=$margin; // for the top and right margins, a little smaller
echo "<br>titlemargin $titlemargin"; //=20;
echo "<br>axistitle_fontsize $axistitle_fontsize"; //=5+$scale; //10; if ($plotsize<500) {$axistitle_fontsize-=2;} elseif ($plotsize>600) {$axistitle_fontsize+=2;}
echo "<br>title_fontsize $title_fontsize"; //=7+$scale; //14; if ($plotsize<500) {$title_fontsize-=2;} elseif ($plotsize>600) {$title_fontsize+=2;}
echo "<br>ticklabel_fontsize $ticklabel_fontsize"; //$ticklabel_fontsize=7; if ($scale > 5) {$ticklabel_fontsize++;}
echo "<br>xtitlemargin $xtitlemargin ytitlemargin $ytitlemargin "; //$xtitlemargin=32+$scale; $ytitlemargin=$xtitlemargin+$axistitle_fontsize+4; // xtitlemargin was 40
//$gridcolor="#c7dedf";
echo "<br>titlecolor $titlecolor"; //="#363535";
echo "<br>subtitlecolor $subtitlecolor"; //="#4c4b4b";
echo "<br>tickcolor $tickcolor"; //=$subtitlecolor;
//if ($fillcolor > "" ) {if (strlen($fillcolor) == 6) {$fillcolor="#".$fillcolor;}} else {$fillcolor="#ff0000";}
//if ($strokecolor > "" ) {if (strlen($strokecolor) == 6) {$strokecolor="#".$strokecolor;}} else {$strokecolor="black";}
echo "<br>xtitle $xtitle ytitle $ytitle ";
//$xtitle = 'SiO2';
//$ytitle = $display_name;
//$title = $ytitle." vs ".$xtitle;
} // if 
*/

echo '</div><!-- maindiv -->'; // Need this div because navdat needs some extra margin.

// include a footer for the page 
if ($earthchem) {
include ("../includes/ks_footer.html");
}
elseif ($navdat) {
include ("../navdat/NewFooter.cfm"); 
}




?>
