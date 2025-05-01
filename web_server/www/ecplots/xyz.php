<!-- XYZ Plotter works for both EarthChem and Navdat. A button on the data output (search results) page links to this page. March 2008 -- EEJones -->
<!-- Need these files: xyzplotter.php, xyz-write_form_xyz.php, xyz-rewrite_axis_select.php, xyzplotter.js, xyz-scatter-plot.php, xyz-make-z-datapoint.php, colors.php, points.php -->
<?php 
$earthchem_top_dir="earthchemphp3";  
session_start(); // Note: I had to pass the longer arrays and sql statements as SESSION variables instead of through the url in the javascript. Reason: Different browsers have different limits for how many butes can be passed in a url string. IE has a limit of 2083 bytes. My arrays & sql would exceed that, resulting in a javascript error and browser crash in IE. -->
include('includes/get_pkey.php');  
include ("../jpgraph/jpgraph.php"); // jpgraph
include ("../jpgraph/jpgraph_scatter.php");
$jpgraph_files_already_included=1; // See notes below as to why we set this flag, for xyz-make-z-datapoint.php.
include "xyz.js";
$referring_website = isset($_POST['referring_website']) ? $_POST['referring_website'] : ""; // Did we come from EarthChem or Navdat?
$navdat=false;$earthchem=false;if ($referring_website=='navdat') {$navdat=true;} elseif ($referring_website=='earthchem') {$earthchem=true;} else {echo "<p>There is a problem near line 12 in xyz.php. Please start a new search and try again.<p>"; exit;} 
// Include a page header (and, later, a page footer). Load database drivers and connect to the appropriate database. 
if ($navdat) {
	include "../navdat/NewHeader.cfm";
	include "includes/navdat_db.php";
} elseif ($earthchem) {
	include "../$earthchem_top_dir/includes/ks_head.html";
	include "includes/earthchem_db.php";
	//echo "<p>To test this software for EarthChem, put the search pkey (required) in the url string as in:<p><a href='http://matisse.kgs.ku.edu/eileen/xyzplotter-matisse4.php?pkey=10700'>http://matisse.kgs.ku.edu/eileen/xyzplotter-matisse4.php?pkey=10700</a>";
}

?>
<style type="text/css">
body, div, td {font-size:10px;}
.optional {font-size:10px; font-weight:normal; font-style:italic; color:black; }
</style>
<?php
include "includes/plot_design_arrays.php"; // Arrays of colors and datapoint styles (jpgraph shapes) we wil offer
$navdatgreen="#508139"; 
$navdatblue="#215b80"; // blue from the earthchem banner "#6800b3"; //indigo with the brightness increased  
//debug: echo "<p>";foreach($_POST as $key=>$val) { echo "<br> &bull; $key: <font color=green>$val</font> ";print_r($val);}
?>
<style type="text/css">input, select {font-size:.9em;}</style> 
<?php
if ($navdat) {
echo '<div id="div_main_xyz" style="line-height:120%;margin:20px">';
} elseif ($earthchem) {
echo '<div id="div_main_xyz" style="line-height:120%">';
}
// The main heading. 
echo "<h1>XYZ Plotter - Plot any two or three chemicals</h1>";
ob_flush();flush();

if($earthchem){
	$pkey=$_POST['pkey'];
	?>
	<div style="width:800px;text-align:right;">
	<?
	echo "<input type=button value=\"Back to EarthChem Output\" onClick=\"window.location = 'http://matisse.kgs.ku.edu/earthchemnew/results.php?pkey=$pkey';\">";
	?>
	</div>
	<?
}

// Use a timestamp to prepare a unique filename for the plot image. We hide this value in a hidden form field in form_xyz, and will retrieve it with javascript functions. The first time we access this page we need to create it from the system time.
if (isset($_POST["timestamp"]) && strlen($_POST["timestamp"])>0) {$timestamp=$_POST["timestamp"];} else {$timestamp=time();} 














































//*************************************************************** FORM WAS SUBMITTED *******************************
if ( (isset($_POST["submit"])) ) { //echo "<p>POSTED"; print_r($_POST);
//************************************************************** PROCESS FORM VARIABLES ***********************************//
	$msg=""; // Collect messages to the user as we go along, and display them all at once, later. 
	$fillcolor = $_POST['fillcolor']!="" ? $_POST['fillcolor'] : "#ffff00"; // $fillcolor=$_POST["fillcolor"]; if (strlen($fillcolor)==0) {$fillcolor='#ff0000';} // default is red
	$strokecolor = $_POST["strokecolor"]="" ? $fillcolor : $_POST['strokecolor']; // if $strokecolor=='' we will make the strokecolor the same as the fillcolor for every datapoint
	$pointsize=$_POST["pointsize"];$plotsize=$_POST["plotsize"];$pointtype=$_POST["pointtype"];
	$subtitle=$_POST["subtitle"];$subsubtitle=$_POST["subsubtitle"];
// Print out the post variables, for debug/dev/test     foreach ($_POST as $key=>$val) { {echo "<br />$key <font color=red>$val</font> ";} }
	$master_fieldnames_array = $_POST['master_fieldnames_array']; //$fields_numeric_not_null=$_SESSION["fields_numeric_not_null"]; // The master 2D array of fields that are of a numeric type and contain some non-null data
	$x_column_name=$_POST["x_column_name"];$y_column_name=$_POST["y_column_name"];$z_column_name=$_POST["z_column_name"]; 
	if ($z_column_name!="") {$set_z=true;} else {$set_z=false;} // ? false : true; // Set a flag, if the user chose a chemical for the z axis
	//echo "<p>z $z_column_name $set_z ";
	$x_ratio=$_POST["x_ratio"];$y_ratio=$_POST["y_ratio"];$z_ratio=$_POST["z_ratio"];
	$x_ratio_part=$_POST["x_ratio_part"];$y_ratio_part=$_POST["y_ratio_part"];$z_ratio_part=$_POST["z_ratio_part"];
	$xtitle=$_POST["xtitle"];$ytitle=$_POST["ytitle"];$ztitle=$_POST["ztitle"]; // The chem and any ratio, using display names, e.g. 1/SiO2
	$xaxis=$_POST["xaxis"];$yaxis=$_POST["yaxis"];$zaxis=$_POST["zaxis"]; // The chem and any ratio, using database column names, e.g. 1/sio2
	//echo "<p>133 x_ratio_part $x_ratio_part  ";
	//debug:echo "<p>ranges: --- $xmin to $xmax &bull; $ymin to $ymax &bull; $zmin to $zmax";
	// If the user didn't select a ratio part (numerator or denominator) but did select a ratio for x,y or z, force to the default of denominator
	$xmin=trim($_POST["xmin"]);$ymin=trim($_POST["ymin"]);$zmin=trim($_POST["zmin"]);$xmax=trim($_POST["xmax"]);$ymax=trim($_POST["ymax"]);$zmax=trim($_POST["zmax"]); // If the user did not set these, they will equal ""
	$x_precision=$_POST["x_precision"];$y_precision=$_POST["y_precision"];$z_precision=$_POST["z_precision"];
	//if (isset($_SESSION["NAVDATSqlStatement"])) {$NAVDATSqlStatement=$_SESSION["NAVDATSqlStatement"];} // for navdat
	$plot_sql=$_POST['plot_sql']; //if (isset($_SESSION["xyzgraphstring"])) {$xyzgraphstring=$_SESSION["xyzgraphstring"];} // for earthchem
	//$fields_numeric_not_null=$_SESSION["fields_numeric_not_null"];
	$links=$_POST["links"]; 
	if ($set_z) {$z_intervals_num=$_POST["z_intervals_num"];} else {$z_intervals_num="";} // If we are making an xyz plot, the # of plot layers we use to represent z
	if ($set_z and isset($_POST["z_interval_mins"]) and isset($_POST["z_interval_maxes"])) {
		$z_interval_mins=$_POST["z_interval_mins"]; $z_interval_maxes=$_POST["z_interval_maxes"];
		// If the user defined the z interval min/maxes but left some blank fields, nuke the whole array. They will get the auto calc for the intervals.
		for ($i=0;$i<$z_intervals_num;$i++) {
			//echo strlen(trim($z_interval_mins[$i])); 
			if (strlen(trim($z_interval_mins[$i]))<1 or strlen(trim($z_interval_maxes[$i]))<1) {
				unset($z_interval_mins);unset($z_interval_maxes);break;
			} 
		}
		// If the arrays survived the last test, remove the excess ones from the array 
		//if (isset($z_interval_mins) and isset($z_interval_maxes)) {for ($i=$z_intervals_num+1;$i<10;$i++) {unset($z_interval_mins[$i];unset($z_interval_maxes[$i])}}
	}
	$show_z_with_pointsize_inc=$_POST["show_z_with_pointsize_inc"]; // If we are making an xyz plot, do we want to show z layers with increasing pointsize?
	if (isset($_POST["z_color_gradient_from"]) ) {$z_color_gradient_from=$_POST["z_color_gradient_from"];} else {$z_color_gradient_from="";} // If we are making an xyz plot, do we want to show z with a color gradient starting with this color and ending with $fillcolor ?
	if (isset($_POST["z_order"])) {$z_order=$_POST["z_order"];} else {$z_order=array();for($i=0;$i<10;$i++){$z_order[$i]=$i;} } // array of the order in which to layer the scatter plot, for each z interval
	if (array_sum($z_order) < 1) {for($i=0;$i<10;$i++){$z_order[$i]=$i;} }
	$zpointtype=$_POST["zpointtype"]; // array of pointtype for each z interval
	$zfillcolor=$_POST["zfillcolor"]; // array of fillcolor for each z interval
	$zstrokecolor=$_POST["zstrokecolor"]; // array of strokecolor for each z interval
	$zpointsize=$_POST["zpointsize"]; // array of pointsize for each z interval
	$zpointimagefilename=$_POST["zpointimagefilename"]; // array of image filename (the single point plot) for each z interval
	//$column_names=$_SESSION['column_names']; 
	//$master_column_names_list=$_POST['master_column_names_list'];
	
	
/*/Set some defaults
	if (strlen($x_ratio)>0 && strlen($x_ratio_part)<1) {$x_ratio_part='denominator';} // denominator is the default for the ratio
	if (strlen($y_ratio)>0 && strlen($y_ratio_part)<1) {$y_ratio_part='denominator';}
	if (strlen($z_ratio)>0 && strlen($z_ratio_part)<1) {$z_ratio_part='denominator';} */
// Test if the user has set values for x,y (or x,y,z). If so, build the SQL statement to retrieve the values for our data points.
//	if (strlen($x_column_name)>0 && strlen($y_column_name)>0 ) {
//		if ($navdat and 1==99 ) {$xyzsql=$NAVDATSqlStatement ." AND $x_column_name IS NOT NULL AND $y_column_name IS NOT NULL ";}
//		if ($earthchem or $navdat ) {$xyzsql=$xyzgraphstring ." AND $x_column_name IS NOT NULL AND $y_column_name IS NOT NULL ";}
//		if ($set_z) {$xyzsql .= " AND $z_column_name is not null ";}
// Make sure we are not looking for a ratio where the denominator=0, or we'll get a division by zero error 
//	if (strlen($x_ratio)>0 && $x_ratio_part=='numerator') { $xyzsql .= " AND $x_column_name <> 0 "; } // If x is a denominator, it cannot be 0 or we get a division by zero error
//	if (strlen($y_ratio)>0 && $y_ratio_part=='numerator') { $xyzsql .= " AND $y_column_name <> 0 "; } // If y is a denominator, it cannot be 0 or we get a division by zero error
//	if ($set_z && strlen($z_ratio)>0 && $z_ratio_part=='numerator') { $xyzsql .= " AND $z_column_name <> 0 "; } // If z is a denominator, it cannot be 0 or we get a division by zero error

// Retrieve only the fields we need, to speed up the query. This requires rewriting the SQL statement. First remove just the part of the sql beginning with 'FROM'...
//	$targetstring=strtoupper($xyzsql); $from_position=strpos($targetstring, ' FROM'); 
//	$xyzsql=substr($xyzsql,$from_position); 
// ...and rewrite the select portion. If the user specified a ratio by choosing a numerator or denominator for x or y, the sql select is more complicated; that numerator might be a column name, or an integer.
	
/*/ $x_sql is the bit of sql for x, using column names (not display names), and incorporating any ratio the user defined, e.g. " 1 / SIO2 ";
	$x_sql=""; 
	if (strlen($x_ratio)>0 && isset($x_ratio_part)) {
		if ($x_ratio_part == 'numerator') {
			if (!is_numeric($x_ratio)) {
				$x_sql .= " ";
			} 
			$x_sql .= "$x_ratio / $x_column_name ";
		} elseif ($x_ratio_part == 'denominator') {
			$x_sql .= " $x_column_name / ";
			if (!is_numeric($x_ratio)) {
				$x_sql .= " ";
			}
			$x_sql .= "$x_ratio ";
		} 
	} else {
		$x_sql .= " $x_column_name ";
	}
*/	
/*/ Build the title for the x axis
	//$xyzsql_select .= "$x_sql AS x "; // $xyzsql_select .= "$x_sql AS $x_column_name "; 
	if (strlen($x_ratio)>0 && $x_ratio_part=='numerator') {
		if (is_numeric($x_ratio)) {
			$xtitle=$x_ratio." / $fields_numeric_not_null[$x_column_name]";
		} else {
			$xtitle="$fields_numeric_not_null[$x_ratio] / $fields_numeric_not_null[$x_column_name]";
		}
	} elseif (strlen($x_ratio)>0 && $x_ratio_part=='denominator') {
		if (is_numeric($x_ratio)) {
			$xtitle="$fields_numeric_not_null[$x_column_name] / ".$x_ratio;
		} else {
			$xtitle="$fields_numeric_not_null[$x_column_name] / $fields_numeric_not_null[$x_ratio] ";
		}
	} else {
		$xtitle=$fields_numeric_not_null[$x_column_name];
	}
*/ 	
/*/ Build the bit of sql for y in the select part of the sql statement
	$y_sql=""; 
	if (strlen($y_ratio)>0 && isset($y_ratio_part)) {
		if ($y_ratio_part == 'numerator') {
			if (!is_numeric($y_ratio)) {
				$y_sql .= " ";
			} 
			$y_sql .= "$y_ratio / $y_column_name ";
		} elseif ($y_ratio_part == 'denominator') {
			$y_sql .= " $y_column_name / ";
			if (!is_numeric($y_ratio)) {
				$y_sql .= " ";
			}
			$y_sql .= "$y_ratio ";
		} 
	} else {
		$y_sql .= " $y_column_name ";
	}
	

// Build the title for the y axis 
	if (strlen($y_ratio)>0 && $y_ratio_part=='numerator') {
		if (is_numeric($y_ratio)) {
			$ytitle=$y_ratio." / $fields_numeric_not_null[$y_column_name]";
		} else {
			$ytitle="$fields_numeric_not_null[$y_ratio] / $fields_numeric_not_null[$y_column_name]";
		}
	} elseif (strlen($y_ratio)>0 && $y_ratio_part=='denominator') {
		if (is_numeric($y_ratio)) {
			$ytitle="$fields_numeric_not_null[$y_column_name] / ".$y_ratio;
		} else {
			$ytitle="$fields_numeric_not_null[$y_column_name] / $fields_numeric_not_null[$y_ratio] ";
		}
	} else {
		$ytitle=$fields_numeric_not_null[$y_column_name];
	}
	
// Build the bit of sql for z in the select part of the sql statement
	$z_sql=""; $z_title=""; // initialize these values, which we will update below if z has been set 
	if ($set_z) {
		if (strlen($z_ratio)>0 && isset($z_ratio_part)) {
			if ($z_ratio_part == 'numerator') {
				if (!is_numeric($z_ratio)) {
					$z_sql .= " ";
				} 
				$z_sql .= "$z_ratio / $z_column_name ";
			} elseif ($z_ratio_part == 'denominator') {
				$z_sql .= " $z_column_name / ";
				if (!is_numeric($z_ratio)) {
					$z_sql .= " ";
				}
				$z_sql .= "$z_ratio ";
			} 
		} else {
			$z_sql .= " $z_column_name ";
		}
	
	// Build the title for z 
		if (strlen($z_ratio)>0 && $z_ratio_part=='numerator') {
			if (is_numeric($z_ratio)) {
				$ztitle=$z_ratio." / $fields_numeric_not_null[$z_column_name]";
			} else {
				$ztitle="$fields_numeric_not_null[$z_ratio] / $fields_numeric_not_null[$z_column_name]";
			}
		} elseif (strlen($z_ratio)>0 && $z_ratio_part=='denominator') {
			if (is_numeric($z_ratio)) {
				$ztitle="$fields_numeric_not_null[$z_column_name] / ".$z_ratio;
			} else {
				$ztitle="$fields_numeric_not_null[$z_column_name] / $fields_numeric_not_null[$z_ratio] ";
			}
		} else {
			$ztitle=$fields_numeric_not_null[$z_column_name];
		}
	} else {
		$z_sql=" 0 ";
	} // if $set_z - if the user choose a chemical to plot for z

	
// Build the SELECT part of the sql statement here
	$xyzsql_select="SELECT ";
	if (strlen($x_sql)>0 && strlen($y_sql)>0 ) {$xyzsql_select .= " $x_sql AS x, $y_sql AS y ";}
	if (strlen($z_sql)>0) {$xyzsql_select .= " , $z_sql AS z ";} 

	// If the user asked for hotlinks, we need sample_num also to make the linked-to url to individual sample data 
	if ($links) {
		if ($navdat) {
			$xyzsql_select .= ", sample_num ";
		} 
		// We will modify the earthchem sql statement, if the user asked for hotlinks, later 
	}

	$xyzsql=$xyzsql_select." ".$xyzsql; 
	
	// If there is a numerator for x, y or z and it is a column name (not an integer), be sure only non-null values are retrieved for the numerator, and only positive values for the denominator.
	if ($x_ratio_part=='numerator' && strlen($x_ratio)>0 && !is_numeric($x_ratio) ) {$xyzsql .= " AND $x_ratio IS NOT NULL ";} 
	if ($y_ratio_part=='numerator' && strlen($y_ratio)>0 && !is_numeric($y_ratio) ) {$xyzsql .= " AND $y_ratio IS NOT NULL ";} 
	if ($set_z && $z_ratio_part=='numerator' && strlen($z_ratio)>0 && !is_numeric($z_ratio) ) {$xyzsql .= " AND $z_ratio IS NOT NULL ";} 
	// If there is a denominator for x, y or z and it is a column name (not an integer), be sure only only positive values are retrieved for the denominator. We screen out non-null values for the x_column_name,y_column_name,z_column_name elsewhere.
	if ($x_ratio_part=='denominator' && strlen($x_ratio)>0 && !is_numeric($x_ratio)) {$xyzsql .= " AND $x_ratio > 0 "; }
	if ($y_ratio_part=='denominator' && strlen($y_ratio)>0 && !is_numeric($y_ratio)) {$xyzsql .= " AND $y_ratio > 0 "; }
	if ($set_z && $z_ratio_part=='denominator' && strlen($z_ratio)>0 && !is_numeric($z_ratio)) {$xyzsql .= " AND $z_ratio > 0 "; }
	echo "<p>old xyzsql: $xyzsql"; 
	*/
	$plot_sql=$_POST['plot_sql']; //echo "<p>plot_sql: $plot_sql "; 
	$min_max_count_sql=$_POST['min_max_count_sql']; //echo "<p>min_max_count_sql: <br>$min_max_count_sql ";
	
/******************************************* END PROCESSING FORM VARIABLES *****************************************/
// Get the sample count (for display only), and min/max for x,y,z. Display form fields so the user can optionally define their own min/max. If they define a min or max that is outside the range of values returned by the query, this will force the plot scale to be the min/max the user defined.
if ($x_column_name == "" or $y_column_name == "") {
	echo "<p><span style='font-size:150%;color:red;font-weight:bold'>You cannot make a plot unless you define at least x and y.</span><p>";
} else {
	$row=$db->get_row($min_max_count_sql) or die("NEAR 314 IN MAIN SCRIPT, COULD NOT EXECUTE min_max_count_sql");	
	$count=$row->count;
	$min_x=$row->min_x;
	$max_x=$row->max_x;
	$min_y=$row->min_y;
	$max_y=$row->max_y;
	$min_z=$row->min_z;
	$max_z=$row->max_z;

// Check if the user specified min/max for x,y or z. If so, add this limit to the sql statement we will execute to get the values for the data points.
	$plot_sql_with_range_limits=$plot_sql; // plot_sql was built by a method and passed in through a form variable 
	
	if ($xmin != "") {$plot_sql_with_range_limits .= " and $xaxis >= $xmin ";}  
	if ($xmax != "") {$plot_sql_with_range_limits .= " and $xaxis <= $xmax ";}
	if ($ymin != "") {$plot_sql_with_range_limits .= " and $yaxis >= $ymin ";}
	if ($ymax != "") {$plot_sql_with_range_limits .= " and $yaxis <= $ymax ";}
	if ($zmin != "") {$plot_sql_with_range_limits .= " and $zaxis >= $zmin ";}
	if ($zmax != "") {$plot_sql_with_range_limits .= " and $zaxis <= $zmax ";}

	// Order by z, x, y or by x, y . Note: ordering by $xaxis,$yaxis,$zaxis does not work if the sql returns no samples 
	if ($z_column_name!="") {$plot_sql_with_range_limits .= " ORDER BY z,x,y ";} else {$plot_sql_with_range_limits .= " ORDER BY x,y ";}
	


// Execute the SQL statement that retrieves values for our data points. Store the values in arrays datax, datay, dataz (which we will feed to jpgraph to plot).

	if ($earthchem and $links) {
		// Do nothing. We already included url in the sql we built in the class.
		// For EarthChem, if the user asked for the data points to be hotlinks to sample data (checked the checkbox), alter the sql to get the url from another table
		// Modify sql to get the url for each sample 
		//$plot_sql_with_range_limits=str_replace("FROM", ", temp.sample_pkey, samp.url FROM sample samp, ", $plot_sql_with_range_limits);
		//$plot_sql_with_range_limits=str_replace("WHERE", "WHERE temp.sample_pkey=samp.sample_pkey AND ", $plot_sql_with_range_limits);
	} elseif ($navdat and $links) {
		$plot_sql_with_range_limits = str_replace("SELECT", "SELECT samp.sample_num, ", $plot_sql_with_range_limits); // For Navdat links, we just pass the sample_num in a url to GetSample.cfm	
	}
		
	//echo "<p>at 348 in xyz.php: plot_sql_with_range_limits<p>".nl2br($plot_sql_with_range_limits)."<p>";
	$rows=$db->get_results($plot_sql_with_range_limits) or die("COULD NOT EXECUTE plot_sql_with_range_limits near 347");
	$samplecount=count($rows); 
	if ($samplecount<1) {
		$msg .= "<p>$samplecount samples found. Cannot make a plot.";
	} else {	
		
	unset($datax);unset($datay);unset($dataz);unset($target);unset($alt); // 3 arrays hold the x,y,z datapoint values. 2 others hold target & alt text for links on data points, if user asked for them
	foreach ($rows as $row) { //echo" <font color=purple size=1> | x=";echo $row[strtoupper($x_column_name)]; echo "</font>";
		$datax[]=$row->x; //[strtoupper('x')]; // if we applied a ratio to x, that was applied in the search query 
		$datay[]=$row->y; //[strtoupper('y')];
		$dataz[]=$row->z; //[strtoupper('z')];
		if ($links) {
			$alt_msg=""; 
			if ($navdat) {echo "<h1>Change the link for GetSample.cfm Eileen</h1>";
				$sample_num=$row->sample_num;$url="http://navdat.kgs.ku.edu/NavdatPortal/GetSample.cfm?sample_num=".$sample_num;
			} elseif ($earthchem) {
				$url=$row->url; 
			}
			if (strlen($url)<7) {
				$alt_msg="No link to sample data is available ~ ".$url;$url="http://neko.kgs.ku.edu/harker/harker_no_link.php"; 
			}
			$target[]="javascript:window.open('$url','newwindow','height=600,width=800,scrollbars=1,resizable=1');newwindow.focus()";
			$alt[]="x=$row->x y=$row->y z=$row->z "; 
		} 
	} // foreach $row (sample)


		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
	
//************************************************ MAKE THE PLOT ******************************************************
$xmin_from_query=min($datax);$xmax_from_query=max($datax);$ymin_from_query=min($datay);$ymax_from_query=max($datay);$zmin_from_query=min($dataz);$zmax_from_query=max($dataz); // We will show these values to the user in the form below, and let them limit the x,y min/max
$scale=$plotsize/100;
$margin=floor(($plotsize)/4); if ($margin>100) {$margin=100;} elseif ($margin<100) {$margin=100;}
$titlemargin=50;
$axistitle_fontsize=4+$scale; //10; if ($plotsize<500) {$axistitle_fontsize-=2;} elseif ($plotsize>600) {$axistitle_fontsize+=2;}
$title_fontsize=6+$scale; //14; if ($plotsize<500) {$title_fontsize-=2;} elseif ($plotsize>600) {$title_fontsize+=2;}
$ticklabel_fontsize=2+$scale;
$xtitlemargin=$ticklabel_fontsize*3+$scale; 
$ytitlemargin=$xtitlemargin+$axistitle_fontsize+$axistitle_fontsize+4; 
$samplecount_fontsize=$axistitle_fontsize-2;
$watermark_fontsize=$axistitle_fontsize;
$legend_fontsize=$samplecount_fontsize;
// If there is more than one z interval (scatter plot layer), be sure there is enough margin to hold the legend without overwriting the plot
$gridcolor="#c7dedf";
$titlecolor="#363535";
$subtitlecolor="#4c4b4b";
$tickcolor=$subtitlecolor;
// Strip tags out of titles, because jpgraph does not support them
$plot_xtitle=$xtitle;$plot_ytitle=$ytitle;$plot_ztitle=$ztitle;
$plot_xtitle = str_ireplace('<SUP>','',$plot_xtitle);
$plot_xtitle = str_ireplace('<SUB>','',$plot_xtitle);
$plot_ytitle = str_ireplace('<SUP>','',$plot_ytitle);
$plot_ytitle = str_ireplace('<SUB>','',$plot_ytitle);
$plot_ztitle = str_ireplace('<SUP>','',$plot_ztitle);
$plot_ztitle = str_ireplace('<SUB>','',$plot_ztitle);
$plot_xtitle = str_ireplace('</SUP>','',$plot_xtitle);
$plot_xtitle = str_ireplace('</SUB>','',$plot_xtitle);
$plot_ytitle = str_ireplace('</SUP>','',$plot_ytitle);
$plot_ytitle = str_ireplace('</SUB>','',$plot_ytitle);
$plot_ztitle = str_ireplace('</SUP>','',$plot_ztitle);
$plot_ztitle = str_ireplace('</SUB>','',$plot_ztitle); //echo "<h1>$plot_xtitle $plot_ytitle $plot_ztitle </h1>";
$title="$plot_xtitle vs $plot_ytitle"; if ($set_z) {$title .= " by $plot_ztitle";}
$n=$z_intervals_num*($legend_fontsize*2)+$legend_fontsize*2+$axistitle_fontsize*2; if ($margin<$n) {$margin=$n;} unset($n);
$image_height=$plotsize+$margin;
$image_width=$plotsize+$margin;
$graph=new Graph($image_width,$image_height,"auto");
$graph->CheckCSIMCache( "image1",10); // image map cache
$graph->img->SetAntiAliasing(); 
$graph->SetScale("linlin"); // x and y scales are linear (not text, int, or log), and both x and y scale are automatic
$margintop=$margin*.5;$marginbottom=$margin*1.5; 
$graph->img->SetMargin($margin,$margin,$margintop,$marginbottom); // left, right, top, bottom
//$graph->SetShadow(); // drop shadow around graph
$graph->SetColor("#ffffff");
$graph->SetMarginColor("#ffffff");
$graph->xgrid->SetColor($gridcolor);

// Left footer is a watermark
if ($navdat) {$graph->footer->left->Set("NAVDAT.org");}
elseif ($earthchem) {$graph->footer->left->Set("EarthChem.org"); $watermark_fontsize+=2;}
else {$graph->footer->left->Set("footer error"); }
$graph->footer->left->SetColor('#cccccc'); 
$graph->footer->left->SetFont(FF_VERA,FS_BOLD,$watermark_fontsize);

// Right footer is the count of samples plotted 
$samplecount=sizeof($datax); if ($samplecount>1) {$samplemsg="$samplecount samples";} else {$samplemsg="$samplecount sample";}
$graph ->footer->right->Set($samplemsg); // # of samples in LR corner 
$graph->footer->right->SetColor($subtitlecolor);
$graph->footer->right->SetFont(FF_VERA,FS_NORMAL,$samplecount_fontsize);

// Automatically place the x and y axes titles. (Eileen there is code in any_xy-dev.php that places these explicitly.
$graph->xaxis->SetTitle($plot_xtitle,'middle'); // accepts: high, middle or low
$graph->xaxis->title->SetFont(FF_VERA,FS_NORMAL,$axistitle_fontsize);
$graph->xaxis->title->SetColor($subtitlecolor);
$graph->xaxis->SetTitleMargin($xtitlemargin);
$graph->yaxis->SetTitle($plot_ytitle,'middle');
$graph->yaxis->title->SetFont(FF_VERA,FS_NORMAL,$axistitle_fontsize);
$graph->yaxis->title->SetColor($subtitlecolor);
$graph->yaxis->SetTitleMargin($ytitlemargin);

// Apply rounding to the x,y axes - if the user selected this in the form
if (strlen($x_precision)>0) {
$format_mask='%1.' . $x_precision . 'f';
$graph->xaxis->SetLabelFormat($format_mask); 
unset($format_mask);
}
if (strlen($y_precision)>0) {
$format_mask='%1.' . $y_precision . 'f';
$graph->yaxis->SetLabelFormat($format_mask); 
unset($format_mask);
}

// Show the grid 
$graph->xgrid->Show();

// The subtitle and subsubtitle
if (strlen($subtitle)>0) {
$subtitle_fontsize=$title_fontsize-2;
$graph->subtitle->Set($subtitle,middle); // accepts: high, middle or low
$graph->subtitle->SetFont(FF_VERA,FS_NORMAL,$subtitle_fontsize);
$graph->subtitle->SetColor($subtitlecolor);
$titlemargin += $title_fontsize; // move the title up a bit because there was a subtitle
}
if (strlen($subsubtitle)>0) {
$subsubtitle_fontsize=$title_fontsize-4;
$graph->subsubtitle->Set($subsubtitle,middle); // accepts: high, middle or low
$graph->subsubtitle->SetFont(FF_VERA,FS_NORMAL,$subsubtitle_fontsize);
$graph->subsubtitle->SetColor($subtitlecolor);
$titlemargin += $title_fontsize; // move the title up a bit because there was a subtitle
}

// The main title
$graph->title->Set($title,middle); // accepts: high, middle or low
$graph->title->SetFont(FF_VERA,FS_NORMAL,$title_fontsize);
$graph->title->SetColor($titlecolor);
// why does this cause an error? $graph->SetTitlemargin(200); 
// and this too? $graph->title->SetTitleMargin($titlemargin);

// Design the tick labels
$graph->xaxis->SetFont(FF_VERA,FS_NORMAL,$ticklabel_fontsize);
$graph->xaxis->SetColor($gridcolor,$tickcolor); 
$graph->yaxis->SetFont(FF_VERA,FS_NORMAL,$ticklabel_fontsize);
$graph->yaxis->SetColor($gridcolor,$tickcolor); 

// ***** Make the z layers *****

if ($set_z) { 
// Do a whole lot of stuff for the z axis. We are making a 3D plot, not a 2D plot.
// z_intervals_num is a form variable representing the # of scatter plot layers (each with different-looking points) we want to make to represent z
	$z_interval_size=(max($dataz)-min($dataz))/$z_intervals_num; // Normally we would divide the min,max values for z by the number of intervals to get the interval size, but...
	$z_interval_min=min($dataz) - $z_interval_size;
	//echo "<p><p>DEBUG 345: THERE IS A Z LAYER: z_interval_size $z_interval_size z_intervals_num $z_intervals_num z_interval_min $z_interval_min<br />";echo max($dataz);echo min($dataz);echo "<br />z_order ";print_r($z_order);
$plotdata=array();
// Save the entire dataset, and the main pointsize fill color etc., in position 1000 of this array. Then add array elements for each z layer. 
$plotdata[1000]['datax']=$datax;
$plotdata[1000]['datay']=$datay;
$plotdata[1000]['dataz']=$dataz;
$plotdata[1000]['pointsize']=$pointsize;
$plotdata[1000]['fillcolor']=$fillcolor;
$plotdata[1000]['strokecolor']=$strokecolor;
$plotdata[1000]['pointtype']=$pointtype;
//$plotdata[-1]=$plotdata[1000]; echo "<font color=pink>";print_r($plotdata[-1];)echo"</font>";
$pointsize_save=$pointsize; // Save this; it may change as we loop through the z intervals. It is the main pointsize, but the user may set pointsizes for individual z intervals. We want to keep the original main pointsize when the user submits the form to plot (so we can save this in the form for the user).
$fillcolor_save=$fillcolor; // ditto for main fillcolor
$strokecolor_save=$strokecolor; // ditto for main strokecolor
$pointtype_save=$pointtype; // ditto for main pointtype
$datax_save=$datax; $datay_save=$datay; $dataz_save=$dataz; // Save the x,y,z values, for the entire data set. We will make subsets of these arrays, called datax, datay, dataz, for the individual scatter plots we will layer.
if ($links) {$alt_save=$alt; $target_save=$target;} // Save the arrays for the links image map, for the entire data set 

// Build an array of fill colors for the z intervals (layers)
unset($z_layer_colors);$z_layer_colors=array();
$z_layer_colors=array_fill(0,$z_intervals_num,$fillcolor); // Initially set all z layers' fillcolor to the fillcolor
if ($z_intervals_num>1 and strlen($z_color_gradient_from)>=6) {// if user selected a "gradient from" color, calculate a color gradient ranging from that color to the fill color, and put the colors in an array
	$z_layer_colors=gradient(substr($z_color_gradient_from,1,6),substr($fillcolor,1,6),$z_intervals_num);//a php function computes the gradient color
	//echo "<p>z_color_gradient_from $z_color_gradient_from ";print_r($z_layer_colors);
	$z_layer_colors[$z_intervals_num-1]=$fillcolor; // Use the user-selected fill color for the last (highest z values) layer. Minute rounding errors in computer calculations might result in the function assigning a value slightly off this ending color, so override the color the function assigned.
}

for ($i=0;$i<$z_intervals_num;$i++) {if ($zfillcolor[$i]>"") {$z_layer_colors[$i]=$zfillcolor[$i];} } // If user explicitly set color for any z layer, honor that 

// Build an array of stroke (point outline) colors for the z intervals (layers)
unset($z_layer_stroke_colors);$z_layer_stroke_colors=array();
for ($i=0;$i<$z_intervals_num;$i++) {$z_layer_stroke_colors[$i]=$z_layer_colors[$i]; if (strlen($strokecolor)>0) {$z_layer_stroke_colors[$i]=$strokecolor;}} // Initially set all z layers' strokecolor to the strokecolor
for ($i=0;$i<$z_intervals_num;$i++) {if ($zstrokecolor[$i]>"") {$z_layer_stroke_colors[$i]=$zstrokecolor[$i];} } // If user explicitly set color for any z layer, honor that 

// Build an array of pointsizes for the z intervals
unset($z_layer_pointsizes);$z_layer_pointsizes=array();
$z_layer_pointsizes=array_fill(0,$z_intervals_num,$pointsize); // Initially set all z layers' pointsize to the main pointsize

// If user wants to show z by increasing pointsize up to the pointsize they specified, recalculate the pointsize if necessary to handle the number of z intervals (it cannot be too small)
if ($show_z_with_pointsize_inc) {
	$ps=$pointsize-$z_intervals_num-1; if ($ps<1) {$ps=1;} 
	for ($i=0;$i<$z_intervals_num;$i++) {
		$ps++; // User may have chosen to increase pointsize for each z interval
		$z_layer_pointsizes[$i]=$ps; 
	}
}
// If the user specified pointsizes for any z interval, modify that value in the array of pointsizes for z intervals
for ($i=0;$i<$z_intervals_num;$i++) {
	if ($zpointsize[$i]>0) {$z_layer_pointsizes[$i]=$zpointsize[$i];}
}

// Build an array of pointtypes for the z intervals
$z_layer_pointtypes=array();
for ($i=0;$i<$z_intervals_num;$i++) {
$z_layer_pointtypes[$i]=$pointtype; 
}
// If the user specified pointtypes for any z interval, modify that value in the array of pointtypes for z intervals
for ($i=0;$i<$z_intervals_num;$i++) {
	if (strlen($zpointtype[$i])>0) {$z_layer_pointtypes[$i]=$zpointtype[$i];}
}

// Make an array of image filenames - for the single-point plots that show the user what the z interval data points (they may have designed) will look like
$zpointimagefilename=array();
for ($i=0;$i<$z_intervals_num;$i++) {
	$z_point_image_filename="plots/z-$i-$timestamp.png"; // This file format must match that in xyz-make-z-datapoint.php 
	$zpointimagefilename[$i]=$z_point_image_filename;
}
//echo "<br /><p>DEBUG 456 z layer colors<br />";print_r($z_layer_colors);echo "<p>";
//echo "<br /><p>DEBUG 456 z layer pointtypes<br />";print_r($z_layer_pointtypes);echo "<p>";
//echo "<br /><p>DEBUG 456 z layer pointsizes<br />";print_r($z_layer_pointsizes);echo "<p>";
//echo "<br /><p>DEBUG 456 z_intervals_num<br />$z_intervals_num<p>";

/*************************************************Z LAYER AND LEGEND************************************************************/

// Now loop through the layers (z intervals). Make a separate scatter plot for each z interval, and layer the plots. At the same time, write the legend to the screen.

$legend = '<div id="legend" style="margin:20px 0px">';
$legend .= '<h3>Legend: '.$title.'</h3>';
$legend .= '<table cellpadding=5 cellspacing=0 border=0>'; // table to contain the legend, with 1 row per z interval (scatter plot layer) 
if (isset($z_interval_mins)) {//echo "<br />z_interval_mins is defined ------------------ ";print_r($z_interval_mins);
} else { // User did not define the z interval min and maxes so we calculate some here
	$z_interval_mins=array();$z_interval_maxes=array();
	for ($i=0;$i<$z_intervals_num;$i++) {
		//echo "<p>DEBUG 567: setting z_interval_mins and maxes: i is $i";
		unset($datax);unset($datay);unset($dataz);if ($links) {unset($alt);unset($target);} // We will rebuild these - one for each scatter plot layer (z interval)
		$z_interval_min=$z_interval_min + $z_interval_size; $z_interval_max=$z_interval_min+$z_interval_size;
		$z_interval_mins[$i]=$z_interval_min;$z_interval_maxes[$i]=$z_interval_max;
	}
}
for ($i=0;$i<$z_intervals_num;$i++) {
	//echo "<p><p>DEBUG 567: i is $i<br />";
	unset($datax);unset($datay);unset($dataz);if ($links) {unset($alt);unset($target);} // We will rebuild these - one for each scatter plot layer (z interval)
	$z_interval_min=$z_interval_mins[$i]; // + $z_interval_size; $z_interval_max=$z_interval_min+$z_interval_size;
	$z_interval_max=$z_interval_maxes[$i];
	//echo "<br /><p>DEBUG 678: z_interval_min and max: $z_interval_min $z_interval_max z_interval_size: $z_interval_size";
	$fillcolor=$z_layer_colors[$i];
	$strokecolor=$z_layer_stroke_colors[$i]; 
	$pointsize=$z_layer_pointsizes[$i]; 
	$pointtype=$z_layer_pointtypes[$i];
	foreach ($dataz_save as $key=>$val) {
		if ( ($val >= $z_interval_min && $val < $z_interval_max) || ($i==0 && $val >= ($z_interval_min-1) && $val < $z_interval_max) || ($i==($z_intervals_num-1) && $val >= $z_interval_min && $val <= ($z_interval_max+1) )) { // For the last layer, include the upper limit (<= instead of <) . For the first layer, include the lower limit. Add or subtract 1 because, because of computer math, it might not catch the value that is at the upper limit
			$datax[]=$datax_save[$key];$datay[]=$datay_save[$key];$dataz[]=$dataz_save[$key];
			if ($links) {$alt[]=$alt_save[$key];$target[]=$target_save[$key];}
		} // if
	} // foreach dataz_save
	$zsamplecount[$i]=sizeof($dataz); // # of samples in this z interval plot layer
	$plotdata[$i]['datax']=$datax;
	$plotdata[$i]['datay']=$datay;
	$plotdata[$i]['dataz']=$dataz;
	$plotdata[$i]['samplecount']=sizeof($dataz);
	$plotdata[$i]['fillcolor']=$fillcolor;
	$plotdata[$i]['strokecolor']=$strokecolor;
	$plotdata[$i]['pointsize']=$pointsize; 
	$plotdata[$i]['pointtype']=$pointtype; 
	$plotdata[$i]['z_interval_min']=$z_interval_min;
	$plotdata[$i]['z_interval_max']=$z_interval_max;
	$plotdata[$i]['z_order']=$i;
	//debug: echo "<p><font color=red>$pointtype";print_r($pointtypes_array);echo"</font>";
	$p=array_search($pointtype, $pointtypes_array); // Get the display name for the pointtype from array $pointtypes, e.g. square instead of MARK_SQUARE. This function finds the key for a value, in an array.
	$n=$plotdata[$i]['samplecount']; 
	$legend .= "
<tr><td style='color:#666666'>plotting order $z_order[$i] </td>
<td style='color:#999999'>z interval $i</td><td align=right>$z_interval_min</td><td><=</td><td>&nbsp;<b>$ztitle</b></td><td>";
if ($i == $z_intervals_num-1) {$legend .= "<=";} else {$legend .= "<&nbsp";}
$legend .= "</td><td>$z_interval_max</td>";
	$legend .= "<td align=right style='color:$fillcolor'>$n&nbsp;</td><td style='color:$fillcolor'>";
	if ($n==1) { $legend .= "sample "; } else {$legend .= "samples"; }
	$legend .= "</td>";
	// This image may display an older cached image by the same name, unless the image is reloaded. This is an old trick: The server ignores what's after ? but the browser will see it as a unique filename (because of the string of numbers after the ?) and refresh the image. This ought to be a random number Eileen. Fix this later.
	$r = rand();
	$legend .= "<td><IMG id='pic$i' hspace=5 vspace=5 border=0 src='plots/z-$i-$timestamp.png?$r' alt='plots/z-$i-$timestamp.png?$r'></TD>";
	unset($r);
	$legend .= "</tr></font>";

//echo "<br /><p>DEBUG 789: datax ";print_r($datax);
//echo "<br /><p>DEBUG 890: datay ";print_r($datay);
//echo "<br /><p>DEBUG 900: dataz ";print_r($dataz);
} // for i --- each z interval layer
// Don't do this. This will put the '' (for omit) at the beginning: asort($z_order);
$temp=array_unique($z_order); 
if (sizeof($temp)==1 && strlen($temp[0])==0) {
	foreach($z_order as $key=>$val) {$z_order[$key]=$key;} 
	$msg .= "<p>You have set every z interval to 'omit' which is not enough data for a plot. The plotting order has been reset to the normal order.";
}
//echo "<br /><p>DEBUG ### z_order ";print_r($z_order);echo " sizeof z_order ";echo sizeof($z_order);echo " z_order ::: ";print_r($z_order); 
asort($z_order); 
foreach ($z_order as $i=>$zo) { 
	if (strlen($zo) > 0 && sizeof($plotdata[$i]['dataz'])>0) {
		$datax=$plotdata[$i]['datax'];
		$datay=$plotdata[$i]['datay'];
		$dataz=$plotdata[$i]['dataz'];
		//echo "<p>DEBUG 5978 sizes of datax datay dataz ";echo sizeof($datax); echo sizeof($datay); echo sizeof($dataz);
		$fillcolor=$plotdata[$i]['fillcolor'];
		$strokecolor=$plotdata[$i]['strokecolor'];
		$pointtype=$plotdata[$i]['pointtype'];
		$pointsize=$plotdata[$i]['pointsize']; 
		$z_interval_min=$plotdata[$i]['z_interval_min'];
		$z_interval_max=$plotdata[$i]['z_interval_max'];
		include "xyz-scatter-plot.php"; // Make a scatter plot layer from datax() and datay() arrays. Add it to this graph.
		// this does not work: if ($links) {$sp1->SetCSIMTargets($target,$alt);}
	}
}
	$pointsize=$pointsize_save; // restore pointsize to what the user selected, so we can save it for them in the form field 
	$fillcolor=$fillcolor_save;
	$strokecolor=$strokecolor_save;
	$pointtype=$pointtype_save;
	$legend .= "</table>";
	$legend .= "</div>"; // legend is now in the variable $legend and we can display it wherever we want


	for ($i=0;$i<$z_intervals_num;$i++) { 
	// If user explicitly set color for any z layer, honor that 
		if ($zfillcolor[$i]>"") {
			$z_layer_colors[$i]=$zfillcolor[$i];
		} 
	} 

} else {  
	include ("xyz-scatter-plot.php"); // Make a scatter plot layer from datax() and datay() arrays. Add it to this graph. 
	// This was done in xyz-scatter-plot.php, above if ($links) {$sp1->SetCSIMTargets($target,$alt);} // If user checked the box for links to individual sample data, make an imagemap from the target and alt arrays
} // if $set_z

// Design the legend. We made a legend in the include scatter plot file, for each scatter plot we layered 
if ($set_z) { // if there is a z axis 
	$graph ->legend->Pos( 0.5,.97,"center" ,"bottom"); // x,y,horiz position,vert position
	$graph->legend->SetColor($subtitlecolor,'#ffffff'); // font color and frame color for the legend
	$graph->legend->SetFillColor('#ffffff'); // background color for the legend box 
	$graph->legend->SetShadow(0); // no shadow
	//$graph->legend->SetReverse(); // reverse order of legend
	$graph->legend->SetFont(FF_VERA,FS_NORMAL,$ticklabel_fontsize); // legend font face, style, size 
	$graph->legend->SetLayout(LEGEND_VERT); // horizontal or vertical layout LEGEND_HOR LEGEND_VERT
}
$graph->SetFrame(true,'#ffffff',1); 
$image_filename="plots/xyz_plot-".$timestamp.".png"; 
$graph->Stroke($image_filename); // generate the image, send it to the browser without saving it on the server
$image_margin=floor($plotsize/10); // white space around the plot image
// Done making the plot image








//************************************************ DISPLAY THE PLOT IMAGE *****************************************************
echo "<div id='plot_image'>";
if ($links) { // If the user chose to add links to the data points (via the form), add an image map with the links
$mapName='ImageMap-'.$image_filename; 
$imgMap=$graph->GetHTMLImageMap($mapName); 
echo "<div name='div_linkmsg'><font size=1>Click on a datapoint to view individual sample data.</font></div>";
echo "$imgMap<img ismap usemap='#".$mapName."' src='$image_filename' style='margin:20px 0px;border:solid 1$gridcolor 1px;padding:20px' alt='$title'>";
} else {
echo "<img src='$image_filename'  border=0 style='margin:20px 0px;border:solid $gridcolor 1px;padding:20px' alt='$title'>";
} // if links, else
?>
</div><!-- plot_image -->

	
<?php
} // if ($samplecount<1) --- test number of samples, and if >0, make a plot
		
} 


} // if x and y are defined





































































//**********************FIRST TIME THROUGH PAGE - form_xyz WAS NOT SUBMITTED**************************
// The first time this page is accessed, we execute this section. 
// No submit button is set, so we know we came directly from another page, not this one (NAVDAT or EarthChem search results page). 

if (!isset($_POST["submit"]) ) { 
// First Time Step 0. Set some defaults for the form field values. 
	if (!isset($x_precision) || strlen($x_precision)==0) {$x_precision='';}
	if (!isset($y_precision) || strlen($y_precision)==0) {$y_precision='';}
	if (!isset($z_precision) || strlen($z_precision)==0) {$z_precision='';}
	if (!isset($fillcolor) || strlen($fillcolor)==0) {$fillcolor="#00ff00";}
	$fillcolor2=$fillcolor;
	if (!isset($strokecolor) || strlen($strokecolor)==0) {$strokecolor="#000000";}
	if (!isset($pointtype) || $pointtype=="") {$pointtype="MARK_DIAMOND";}
	if (!isset($pointsize) || $pointsize=="") {$pointsize=3;}
	if (!isset($plotsize) || $plotsize=="") {$plotsize=500;}
	if (!isset($z_color_gradient_from) || strlen($z_color_gradient_from)==0) {$z_color_gradient_from="#ff0000";} // red
	if (!isset($z_intervals_num)) {$z_intervals_num=1;} // number of intervals for the z axis
	if (!isset($show_z_with_pointsize_inc)) {$show_z_with_pointsize_inc="1";} // default is to show z layers with increasing point size
	

// Works, but do not show this datapoint for now. include("xyz-make-z-datapoint.php"); // This will create and display a single-point plot showing the default datapoint design

/*	foreach ($rs as $row) { 
		$field_name=$row['FIELD_NAME']; $display_name=$row['DISPLAY_NAME']; 
		// Strip out the html superscript and subscript tags, until I figure out how to make JPGraph display these in the titles. (The fonts don't include those characters.)
		$display_name=str_ireplace ( '<sup>', '', $display_name ); 
		$display_name=str_ireplace ( '</sup>', '', $display_name );
		$display_name=str_ireplace ( '<sub>', '', $display_name ); 
		$display_name=str_ireplace ( '</sub>', '', $display_name ); 
		$data_field_selection[$field_name]=$display_name; // Replace the array value with the display name. Now we have key:value pairs with column:display name association
	}

	/ EILEEN FIX THIS LATER / We will get 8 chemicals from the table normalized for iron
	unset($fields_numeric_not_null["SIO2"]); 
	unset($fields_numeric_not_null["AL2O3"]);
	unset($fields_numeric_not_null["MGO"]);
	unset($fields_numeric_not_null["TIO2"]);
	unset($fields_numeric_not_null["NA2O"]);
	unset($fields_numeric_not_null["CAO"]);
	unset($fields_numeric_not_null["K2O"]);
	unset($fields_numeric_not_null["P2O5"]);
	unset($fields_numeric_not_null["FEOT"]); /
*/

} // if !(isset($_POST["submit_set_xyz"])) 


?>

<a name='1'></a><!-- the show/hide z options layer links to anchors named 1 and 2 -->
<!-- *********************************************************** DISPLAY MESAGES IN RED *********************************************** -->
<?php if (strlen($msg)>0) {echo "<div style='margin:0px 20px;color:red;font-weight:bold;'>$msg</div>";}  /* Display any message we built */ ?>
<!-- *********************************************************** THE XYZ FORM - FORM_XYZ ********************************************** -->
<div name="xyz" style="">
<b>Select chemicals to plot x, y, z.</b>
<a href="#" onClick="javascript:showHelp('xyz-selectxyz')"><img src="images/help.gif" class="helpbutton" border="0" alt="xyz-selectxyz"></a> 


<?php 
 // write the div & form that contains the dropdown select boxes for x, y, z
echo '<br /><font size=1>No values have been normalized for iron.</font><br>';
//Note: form_xyz is opened in the included file that follows
include('xyz-write_form_xyz.php'); // Opens the form, which is closed below. Writes the div containing the x,y,z dropdown selection boxes. form_xyz is the first of 2 forms on this page.

 ?>
</div>
<!-- div xyz -->









<!---------------------- form_xyz - THE MAIN FORM ---------------------------------------------------------------------------->
<?php
//include('myClass.php');
/*
//print_r($obj);
$plot_sql = $obj->make_plot_sql(); 
echo "<p>plot_sql $plot_sql<p>"; 
if ($plot_sql != false) {
echo '<input type="hidden" name="plot_sql" value="'.$plot_sql.'">'; // The plotting script will not have to recreate the sql statement
}
*/
echo '
<div id="div_form_xyz" style="margin:0px;" name="div_form_xyz">';
// echo <form action="" method="post" name="form_xyz" id="form_xyz">';
//echo '<input type="text" size=500 name="plot_sql" value="plot_sql"><br>'; // The plotting script will not have to recreate the sql statement
//<input type="text" name="plot_sql" value="hello kitty">
//<input type="text" name="master_fieldnames_list" value="hello kitty">
//<input type="from" name="from" value="hello kitty">
//<input type="where" name="where" value="hello kitty">

//**********************PLOT BUTTON ****************************************************************************
echo '<div id="button1" style="margin:0px 20px;float:right;width:100px;height:60px;">';
if ($earthchem) {
	echo '<input type="submit" name="submit" value="Plot">';
} elseif ($navdat) { 
	//echo '<input type="submit" name="submit" value="" style="background-image:url(images/navdat_plot.gif);" />';
	//echo '<input type="image" src="images/navdat_plot.gif" name="submit" width="68" height="30">';
	echo '<input type="submit" value="Plot" name="submit">';
}
echo '</div><br clear="left" />'; // div button1 
/*echo "
<input type='text' name='xmin' value='$xmin'>
<input type='text' name='xmax' value='$xmax'>
<input type='text' name='ymin' value='$ymin'>
<input type='text' name='ymax' value='$ymax'>
<input type='text' name='zmin' value='$zmin'>
<input type='text' name='zmax' value='$zmax'>
";*/
?> 

 <div style='margin:0 20px;color:<?php echo $navdatblue; ?>'>
 <?php 
 if ( isset($samplecount) ) {
	if ($samplecount==1) {echo "$samplecount sample exists for ";} else {echo "$samplecount samples exist for ";}
	echo "&bull; $xtitle ";
	if (strlen($xmin)>0) {echo " from $xmin ";}
	if (strlen($xmax)>0) {echo " to $xmax ";}
	echo "&bull; $ytitle ";
	if (strlen($ymin)>0) {echo " from $ymin ";}
	if (strlen($ymax)>0) {echo " to $ymax ";}
	if ($set_z) {
		echo " &bull; $ztitle ";
		if (strlen($zmin)>0) {echo " from $zmin ";}
		if (strlen($zmax)>0) {echo " to $zmax ";}
	}
	}
?>
 </div>


<!-- Limit ranges of values to plot for x,y,z -->
<div style='margin:20px 0px'>
<b>Limit range of values to plot.</b>
<a href="#" onClick="javascript:showHelp('xyz-limitrange')"><img src="images/help.gif" class="helpbutton" border="0" alt="xyz-limitrange"></a>
<table border='0' cellspacing='0' cellpadding='5' style='margin-left:20px'>
<?php
//if ($x_precision=='') {
$xmin_display=$min_x;$xmax_display=$max_x;
//} else {
//$xmin_display=round($xyzsql_xmin,$x_precision);$xmax_display=round($xyzsql_xmax,$x_precision);
//} 
if (isset($xmin_display) && isset($xmax_display)) {
echo "<tr><td style='padding:3 5 0 0'  colspan=3><div name='show_x_range' id='show_x_range' style='color:$navdatblue'>$xtitle exists from <b>$xmin_display</b> to <b>$xmax_display</b></div></td></tr>"; 
unset($xmin_display); unset($xmax_display);
} 
?>
<tr>
<td style='padding:3 5 3 10' >x&nbsp;min&nbsp;<input style='width:50px;' type='text' name='xmin' id='xmin' value='<?php echo "$xmin"?>'></td>
<td style='padding:3 5 3 10' >x&nbsp;max&nbsp;<input style='width:50px;' type='text' name='xmax' id='xmax' value='<?php echo "$xmax"?>'></td>
<td style='padding:3 5 3 10' >x&nbsp;precision&nbsp;<select name='x_precision' style='width:50px;'>
<option value=''></option>
<?php for ($n=0;$n<=15;$n++) {
echo "<option value='$n' "; if ($n==$x_precision && strlen($n)==strlen($x_precision)) {echo " selected "; } echo ">$n</option>";
} 
?>
</select>&nbsp;<font size=1>decimal&nbsp;places</font></td>
</tr>
<tr>
<?php 
//if ($y_precision=='') {
$ymin_display=$min_y;$ymax_display=$max_y;
//} else {
//$ymin_display=round($xyzsql_ymin,$y_precision);$ymax_display=round($xyzsql_ymax,$y_precision);
//} 
if (isset($ymin_display) && isset($ymax_display)) {
echo "<tr><td style='padding:3 5 0 0'  colspan=3><div name='show_y_range' id='show_y_range' style='color:$navdatblue'>$ytitle exists from <b>$ymin_display</b> to <b>$ymax_display</b></div></td></tr>"; 
unset($ymin_display); unset($ymax_display);
} 
?>
<tr>
<td style='padding:3 5 3 10' >y&nbsp;min&nbsp;<input style='width:50px;' type='text' name='ymin' value='<?php echo "$ymin"?>'></td> 
<td style='padding:3 5 3 10' >y&nbsp;max&nbsp;<input style='width:50px;' type='text' name='ymax' value='<?php echo "$ymax"?>'></td>
<td style='padding:3 5 3 10' >y&nbsp;precision&nbsp;<select name='y_precision' style='width:50px;'>
<option value=''></option>
<?php for ($n=0;$n<=15;$n++) {
echo "<option value='$n' "; if ($n==$y_precision && strlen($n)==strlen($y_precision)) {echo " selected "; } echo ">$n</option>";
} 
?>
</select>&nbsp;<font size=1>decimal&nbsp;places</font></td>
</tr>
<?php 
//if ($z_precision=='') {
$zmin_display=$min_z;$zmax_display=$max_z;
//} else {
//$zmin_display=round($xyzsql_zmin,$z_precision);$zmax_display=round($xyzsql_zmax,$z_precision);
//} 
if (isset($zmin_display) && isset($zmax_display)) {
echo "<tr><td style='padding:3 5 0 0'  colspan=3><div name='show_z_range' id='show_z_range' style='color:$navdatblue'>$ztitle exists from <b>$zmin_display</b> to <b>$zmax_display</b></div></td></tr>"; 
unset($zmin_display); unset($zmax_display);
} 
?>
<td style='padding:3 5 3 10' >z&nbsp;min&nbsp;<input style='width:50px;' type='text' name='zmin' value='<?php echo "$zmin"?>'></td>
<td style='padding:3 5 3 10' >z&nbsp;max&nbsp;<input style='width:50px;' type='text' name='zmax' value='<?php echo "$zmax"?>'></td>
<td style='padding:3 5 3 10' >z&nbsp;precision&nbsp;<select name='z_precision' style='width:50px;'>
<option value=''></option>
<?php for ($n=0;$n<=15;$n++) {
echo "<option value='$n' "; if ($n==$z_precision && strlen($n)==strlen($z_precision)) {echo " selected "; } echo ">$n</option>";
} 
?>
</select>&nbsp;<font size=1>decimal&nbsp;places</font></td>
</tr>
</table>
</div>

<div id='links' style='margin:20px 0px;'><b>Link data points to sample data?</b>
<a href="#" onClick="javascript:showHelp('xyz-links')"><img src="images/help.gif" class="helpbutton" border="0" alt="xyz-links"></a>
<?php  
echo "<input ";
if ($links) {echo " checked ";} 
echo " type='checkbox' name='links'>"; 
?>
</div><!-- links -->


<div id="set_basic_point_design" style="margin:20px 0px;">
<b>Design data points.</b>
<a href="#" onClick="javascript:showHelp('xyz-datapoints')"><img src="images/help.gif" class="helpbutton" border="0" alt="xyz-datapoints"></a>
<div id='div_main_datapoint_design' style='margin:10 20px;'>
shape <select name='pointtype' onChange='resetz(10)'>
<?php
foreach($pointtypes_array as $key=>$val) {echo "<option "; if ($pointtype==$val) {echo " selected ";} echo " value='$val'>$key</option>";}
?>
</select>

&nbsp; fill&nbsp;color&nbsp;<select name='fillcolor' 
onChange="
document.form_xyz.fillcolor2.selectedIndex = document.form_xyz.fillcolor.selectedIndex;
var color = document.form_xyz.fillcolor.options[document.form_xyz.fillcolor.selectedIndex].value;
document.getElementById('div_fillcolor2_example').innerHTML='<font color='+color+'>&#9632;</font>'
document.getElementById('div_fillcolor_example').innerHTML='<font color='+color+'>&#9632;</font>'
resetz(10);
"> 
<option value=''></option>
<?php
foreach($colors_array as $key=>$val) {echo '<option '; if ($fillcolor==$val) {echo ' selected ';} echo ' style="background-color:'.$val.'" value="'.$val.'">'.$key;}
echo "</select> 
<div id='div_fillcolor_example' style='display:inline'><span style='color:$fillcolor'>&#9632;</span>
</div><!-- div_fillcolor_example -->
";


// Point stroke color
echo " &nbsp; outline color&nbsp;<select name='strokecolor' onChange='resetz(10)'><option value=''>none</option>";
foreach($colors_array as $key=>$val) {echo '<option '; if ($strokecolor==$val) {echo ' selected ';} echo ' style="background-color:'.$val.'" value="'.$val.'">'.$key;}
echo "</select>";
// Point size 
echo " &nbsp; size&nbsp;<select name='pointsize' onChange='resetz(10)'>";
for ( $i=1; $i <= 12; $i++) {echo "<option value='$i' "; if ($pointsize==$i) {echo " selected ";} echo " > $i";}
echo "</select>&nbsp;<font size=1>pixels</font>"; 
// works, but clutters up the screen with the plot image: include ("xyz-make-z-datapoint.php"); // Makes a single-point plot and displays it here 
// Hotlinks (not available for Georoc on Earthchem)
echo "</div><!-- div_main_datapoint_design -->
</div><!-- set_basic_datapoint_design -->";


	
//********************************************************** Z DESIGN OPTIONS ************************************************* 
echo '
<a name="2"></a>
<div style="margin:20px 0px">
<b>Select z data interval preferences.</b> 
<a href="#" onClick="javascript:showHelp(\'xyz-zpref\')"><img src="images/help.gif" class="helpbutton" border="0" alt="xyz-zpref"></a>
<a href="#2" onClick="hidelayer(\'div_z_options\')">hide</a> 
<a href="#2" onClick="showlayer(\'div_z_options\')">show</a> 
</div>
<div id="div_z_options" style="';
//if ($z_column_name == "") {echo 'visibility:hidden;';} // hide this div if nothing has been selected for the z axis
echo 'float:left;margin:0px;padding:10px;border:dotted black 1px;">
'; //the float:left makes the width naturally fit the contents -->

if ($set_z && $z_intervals_num<1) {$z_intervals_num=1;}
?>
<div id='div_z_intervals_num' style='margin:0px'>
<b>Number of intervals for z</b> 
<a href="#" onClick="javascript:showHelp('xyz-zintervalsnum')"><img src="images/help.gif" class="helpbutton" border="0" alt="xyz-zintervalsnum"></a>
<select name="z_intervals_num" id="z_intervals_num" onChange="reset_z_intervals_mins_maxes(10);resetz(10);pop_z_interval_definitions(document.form_xyz.elements['xyzsql_zmin'].value,document.form_xyz.elements['xyzsql_zmax'].value)">
<option value="">
<?php
for ($i=1;$i<=10;$i++) {
echo "<option "; if ($i==$z_intervals_num) {echo " selected ";} echo "value=$i>$i";
}
?>
</select>
</div><!-- div_z_intervals_num -->

<div name='set_color_gradient' style='display:inline;margin:20px 0px'>
<b>Show z with color gradient</b>
<a href="#" onClick="javascript:showHelp('xyz-gradient')"><img src="images/help.gif" class="helpbutton" border="0" alt="xyz-gradient"></a>
from 
<select name='z_color_gradient_from' onChange="
resetz(10);
var color = document.form_xyz.z_color_gradient_from.options[document.form_xyz.z_color_gradient_from.selectedIndex].value;
document.getElementById('div_gradient_from_example').innerHTML='<font color='+color+'>&#9632;</font>'">
<option value=''></option>
<?php
foreach ($colors_array as $key=>$val) {echo "<option "; if ($z_color_gradient_from == "$val") {echo " selected ";} echo " style='background-color:$val' value='$val' >$key";}
?>
</select>
<div id='div_gradient_from_example' style='display:inline'><span style='color:<?php echo $z_color_gradient_from?>'>&#9632;</span></div><!--div_gradient_from_example-->
to 
<div id='div_fillcolor2_example' style='display:inline'><span style='color:<?php echo $fillcolor?>'>&#9632;</span></div><!--div_fillcolor2_example-->
<div id='div_fillcolor2' style=display:inline;border:none' >
<select name='fillcolor2' onChange="
var color = document.form_xyz.fillcolor2.options[document.form_xyz.fillcolor2.selectedIndex].value;
document.getElementById('div_fillcolor2_example').innerHTML='<font color='+color+'>&#9632;</font>'
document.getElementById('div_fillcolor_example').innerHTML='<font color='+color+'>&#9632;</font>'
document.form_xyz.fillcolor.selectedIndex = document.form_xyz.fillcolor2.selectedIndex;
resetz(10);
"><option value=''></option>
<?php
foreach ($colors_array as $key=>$val) {echo "<option "; if ($fillcolor == "$val") {echo " selected ";} echo " style='background-color:$val' value='$val' >$key";}
?>
</select> 
</div><!-- div_fillcolor2 -->
</div><!-- div set_color_gradient -->



<div name='set_inc_pointsize' style='margin:20px 0px'>
<b>Show z with increasing point size</b>
<a href="#" onClick="javascript:showHelp('xyz-increase')"><img src="images/help.gif" class="helpbutton" border="0" alt="xyz-increase"></a>
<?php // Point size increase
echo "<input "; if ($show_z_with_pointsize_inc) echo " checked "; echo " type='checkbox' name='show_z_with_pointsize_inc' onChange='resetz(10)'>";
?>
</div>


<!----------------------Z INTERVALS Design data points for individual Z intervals------------------------------------------>
<?php 
if (1==1) {
// Write the div for the z intervals 
?>
<div name='div_z_intervals_design' style='margin:20px 0px;'>
<b>Design data points for any z interval</b>
<a href="#" onClick="javascript:showHelp('xyz-designz')"><img src="images/help.gif" class="helpbutton" border="0" alt="xyz-designz"></a>



<div style='margin:20px 0px;padding:10px'>
<input type='button' value='Clear' onClick='resetz(10)'> z interval designs and plotting order.<br />
<input type='button' value='Clear' onClick='reset_z_intervals_mins_maxes(10)'> all z interval min and max
</div> 

<div id='div_z'>
<table cellpadding=0 cellspacing=0 border=0>
<?php
//echo "<br>debug <font color=red>zpointsize ";print_r($zpointsize);echo " </font>";
//echo "<br>debug <font color=orange>z_layer_pointsizes ";print_r($z_layer_pointsizes);echo " </font>";
//**********************Write all the z layer rows in the form. Hide the ones we aren't using in this plot. 
for ($i=0;$i<10;$i++) // 10 is the max z intervals we allow
{
// A new z interval row - where the user can set the datapoint design, plot order and min/max for a z interval 
echo '<div ';
if ($i<$z_intervals_num) {echo 'style="display:inline" ';} else {echo 'style="display:none" ';} // will collapse the div when hidden
echo '><tr valign=top><td valign=top style="padding:10px;margin:0px 20px;">';
if ($i<$z_intervals_num && $z_intervals_num > 1) {
	if (isset($zpointtype[$i]) && strlen($zpointtype[$i])>0 ) {} elseif (isset($z_layer_pointtypes[$i]) && strlen($z_layer_pointtypes[$i])>0) {$zpointtype[$i]=$z_layer_pointtypes[$i];} else {$zpointtype[$i]=$pointtype;}
	if (isset($zpointsize[$i]) && strlen($zpointsize[$i])>0 ) {} elseif (isset($z_layer_pointsizes[$i]) && strlen($z_layer_pointsizes[$i])>0) {$zpointsize[$i]=$z_layer_pointsizes[$i];} else {$zpointsize[$i]=$pointsize;}
	if (isset($zfillcolor[$i]) && strlen($zfillcolor[$i])>0 ) {} elseif (isset($z_layer_colors[$i]) && strlen($z_layer_colors[$i])>0) {$zfillcolor[$i]=$z_layer_colors[$i];} else {$zfillcolor[$i]=$fillcolor;}
	if (isset($zstrokecolor[$i]) && strlen($zstrokecolor[$i])>0 ) {} elseif (isset($z_layer_stroke_colors[$i]) && strlen($z_layer_stroke_colors[$i])>0) {$zstrokecolor[$i]=$z_layer_stroke_colors[$i];} else {$zstrokecolor[$i]=$strokecolor;}
} // if...
echo "
 <INPUT NAME='zpointtype[$i]' TYPE='hidden' VALUE='$zpointtype[$i]'>
 <INPUT NAME='zpointsize[$i]' TYPE='hidden' VALUE='$zpointsize[$i]'>
 <INPUT NAME='zfillcolor[$i]' TYPE='hidden' VALUE='$zfillcolor[$i]'>
 <INPUT NAME='zstrokecolor[$i]' TYPE='hidden' VALUE='$zstrokecolor[$i]'>
";
$fillcolor=$zfillcolor[$i];$strokecolor=$zstrokecolor[$i];$pointtype=$zpointtype[$i];$pointsize=$zpointsize[$i];
/* This button uses php to send the url string. It works but if the window was previously opened, and the form field within it submitted ('SET'), the form fields were updated with javascript, and php doesn't know about the updates. The second button uses javascript to send the url variables so the problem is solved. 
*/
echo "<input type='button' value='Design' style='float:left' onClick='javascript:openChild(\"$i\")'>";
if ($i<$z_intervals_num && $z_intervals_num > 1) {
	echo "<br clear='left'/>z interval $i";
}
echo "</td>";
// The datapoint image
echo "<td style='padding:10px;font-size:80%'><div name='div_z_$i' id='div_z_$i' style='text-align:center'>";
if ($i<$z_intervals_num && $z_intervals_num > 1) {
	include("xyz-make-z-datapoint.php"); 
}
echo "&nbsp;</div></td>";
echo "<td style='padding:10px;font-size:80%'>$ztitle >= ";
echo "<input type='text' name='z_interval_mins[$i]' id='z_interval_mins[$i]' value='$z_interval_mins[$i]'> and ";
if ($i==$z_intervals_num-1) {echo " <= ";} else {echo " < ";} // the last interval is inclusive of the max
echo " <input type='text' name='z_interval_maxes[$i]' id='z_interval_maxes[$i]' value='$z_interval_maxes[$i]'>";
if ($i<$z_intervals_num && $z_intervals_num > 1) {
	// the min and max for the interval 
	echo "<br />(for $xtitle, $ytitle) $ztitle >= $z_interval_mins[$i] and ";
	if ($i==$z_intervals_num-1) {echo " <= ";} else {echo " < ";} // the last interval is inclusive of the max, the other intervals just include values up to (not including) the max value
	echo " $z_interval_maxes[$i]";
	// number of samples for the interval 
	echo "<br />$zsamplecount[$i] "; if ($zsamplecount[$i]==1) {echo " sample ";} else {echo " samples ";} 
}
echo "</td>";
echo "<td style='padding:10px;font-size:80%'>";
echo "<select name='z_order[$i]'>";
for ($j=0;$j<10;$j++) {
	echo "<option value='$j'"; 
	if ($j==$i) {echo " selected ";}
	echo ">$j</option>";
}
echo "<option value='' >omit</option>";
echo "</select><br />plotting&nbsp;order";
echo "</td>";
echo "</tr></div>";
} // for $i...
echo '</table>
</div><!-- div_z -->
</div><!-- div_z_intervals_design -->
'; 
} // if z_intervals_num > 1
?>
 <input type='hidden' name='i' value=''>
 <!-- Need this form field to save the value of i in a javascript function. Our functions may alter this value, depending on which z interval (represented by i) we are working on. -->
 <input type='hidden' name='timestamp' value='<?php echo $timestamp; ?>'>
 <!-- Need to save the value of timestamp in a javascript function. We use it to make a set of unique names for files associated with this user's plot. -->
 <!--moved this to xyz-write_form_xyz.php     <input type='hidden' name='referring_website' value='<?php if ($earthchem) {echo "earthchem";} elseif ($navdat) {echo "navdat";} ?>'>-->
 </div>
 <!-- div_z_options -->

<br clear="left" />
 
<!-- Finish your plot: plotting area size, subtitles -->
<div id="finish" style="margin:10px 0px"><b>Finish your plot.</b>
<a href="#" onClick="javascript:showHelp('xyz-finish')"><img src="images/help.gif" class="helpbutton" border="0" alt="xyz-finish"></a>

<div name="finish_contents" style="margin:20px 0px;">
<div name="plot_size" style="margin:20px 0px">size of plotting area <select name="plotsize">
<?php
for ( $i=100; $i <= 1000; $i += 100) {echo "<option value='$i'"; if ($plotsize==$i) {echo " selected ";} echo "> $i x $i ";}
echo "<option value=2000 "; if ($plotsize==2000) {echo " selected ";} echo ">2000 x 2000";
echo "<option value=3000 "; if ($plotsize==3000) {echo " selected ";} echo ">3000 x 3000";
?>
</select>&nbsp;<font size=1>pixels</font>
</div><!-- plot_size -->

<div name="div_subtitles" style="margin:20px 0px">
subtitle&nbsp;1&nbsp;<input type=text name='subtitle' value='<?php echo $subtitle?>' size=40 maxlength=60 style='font-size:.8em;'>&nbsp; 
subtitle&nbsp;2&nbsp;<input type=text name='subsubtitle' value='<?php echo $subsubtitle?>' size=40 maxlength=60 style='font-size:.8em;'>
</div><!-- size -->
</div><!-- finish -->
</div><!-- finish_contents -->


<div name="button2" style="margin:0px 20px;float:right">
<?php
if ($earthchem) { echo '
<input type="submit" name="submit" value="Plot">
';
} elseif ($navdat) { 
//echo '<input type="submit" name="submit" value="" style="background-image:url(images/navdat_plot.gif)" />';
//echo '<input type="image" src="images/navdat_plot.gif" name="submit" width="68" height="30">';
echo '<input type="submit" value="Plot" name="submit">';
}
?>
</div><!-- div button2 -->
<br clear="left" />
</form><!-- form_xyz -->
</div><!-- div_form_xyz -->

<?php
if (isset($legend)) {
?>
<div id='legend'>
<?php echo $legend ?>
</div><!-- legend -->
<div id='troubleshoot' style="margin:20px 0px">
<b>What if I don't like the plot?</b>
<a href="#" onClick="javascript:showHelp('xyz-trouble')"><img src="images/help.gif" class="helpbutton" border="0" alt="xyz-trouble"></a>
</div><!-- div troubleshoot -->

<?php
}
?>

</div><!-- div_main_xyz -->












 



<?php // php function gradient
function gradient($hexstart, $hexend, $steps) {
// Takes a start hex color, end hex color, and n number of steps, and returns an array with n colors grading from one to the other
// Important: exclude the # from the color arguments $hexstart and $hexend
$start['r']=hexdec(substr($hexstart, 0, 2));
$start['g']=hexdec(substr($hexstart, 2, 2));
$start['b']=hexdec(substr($hexstart, 4, 2));
$end['r']=hexdec(substr($hexend, 0, 2));
$end['g']=hexdec(substr($hexend, 2, 2));
$end['b']=hexdec(substr($hexend, 4, 2));
$step['r']=($start['r'] - $end['r']) / ($steps - 1);
$step['g']=($start['g'] - $end['g']) / ($steps - 1);
$step['b']=($start['b'] - $end['b']) / ($steps - 1);
$gradient=array();
for ($i=0; $i <= $steps-2; $i++) {
$rgb['r']=floor($start['r'] - ($step['r'] * $i));
$rgb['g']=floor($start['g'] - ($step['g'] * $i));
$rgb['b']=floor($start['b'] - ($step['b'] * $i));
$hex['x']="#";
$hex['r']=sprintf('%02x', ($rgb['r']));
$hex['g']=sprintf('%02x', ($rgb['g']));
$hex['b']=sprintf('%02x', ($rgb['b']));
$gradient[]=implode(NULL, $hex);
} // for $i
$gradient[]="#".$hexend;
return $gradient;
}


/***************************************************************** FOOTER FOR PAGE ********************************************************/
if ($navdat) {
	include "../navdat/NewFooter.cfm";
} elseif ($earthchem) {
	include "../$earthchem_top_dir/includes/ks_footer.html";
}
?>