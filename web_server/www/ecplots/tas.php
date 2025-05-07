<?

//print_r($_POST);

include("../srcwhere.php");

//echo "sourcelinktext: $sourcelinktext";exit();

//print_r($_POST);
//$earthchem_top_dir="earthchemphp2"; 
//$earthchem_top_dir="earthchemnew";
$earthchem_top_dir="earthchemphp3";

// Modified for matisse server, July 2008 -- EEJones 
/* It is possible to arrive at this page from four different sources:
1. z
2. Earthchem results.cfm on geoportal (ditto)
3. GetSample.cfm on Navdat from Navdat page (link to sample detail - we will plot a single sample on the TAS diagram)
4. GetSample.cfm on Navdat from Earthchem page (ditto)
Data is received via a form - $_POST - or a url string - $_GET.
We set flags, named $getsample, $navdat and $earthchem, to let us know which website or page sent us to this page.
At several points there are decisions to be made depending on where we came from.
For Navdat, we receive a primary key pkey, and use it to look up the query parameters in a database tbl.
For Earchchem, we include a file that defines the query sql statement.
For GetSample.cfm, we are plotting a single point, and we receive the x,y values as form fields.
*/


$earthchem=false; $navdat=false; $getsample=false; // We'll set a flag to indicate which website/page originally sent us to this page
if (isset($_GET["referring_website"])) {
	$referring_website=$_GET["referring_website"];
} elseif (isset($_POST["referring_website"])) {
	$referring_website=$_POST["referring_website"];
}
if ($referring_website=='earthchem') {$earthchem=true;} elseif ($referring_website=='navdat') {$navdat=true;} elseif ($referring_website=='getsample') {$getsample=true;} else {
	echo "<p>No referring website was identified. Please start a new search and try again.<p>";
	print_r($_POST); print_r($_GET); exit;
}

// START THE PAGE OUTPUT

// Unless we came from GetSample.cfm (where just the image will be added to another page), add page header (and, later, a page footer)
if ($navdat) {
	include "../navdat/NewHeader.cfm";
	echo '<div name="main_div" style="margin:20px;">';
} elseif ($earthchem) {
	include "../includes/ks_head.html";
	echo '<div name="main_div">';
} else { // showing a single sample, for EarthChem or Navdat 
	echo '<div name="main_div">';
}
//var_dump($_POST);echo "<br><br>";

if ($earthchem or $navdat) {
	echo '<h1>TAS Diagram</h1>';
}

if($earthchem){
	$pkey=$_POST['pkey'];
	?>
	<div style="width:800px;text-align:right;">
	<?
	echo "<input type=button value=\"Back to EarthChem Output\" onClick=\"window.location = 'https://portal.earthchem.org/results.php?pkey=$pkey';\">";
	?>
	</div>
	<input type=button value="Click Here for SVG TAS" onClick="window.location = '/tas.php?pkey=<?=$pkey?><?=$sourcelinktext?>';">
	<?
}

require ("../jpgraph/jpgraph.php"); // jpgraph driver
require ("../jpgraph/jpgraph_scatter.php"); // jpgraph scatter plot 
//$earthchem_datasource="earthchem_portal3"; // Do we still need this?
//$gJpgBrandTiming=true; //This cool feature displays the time it took to plot, in the LL corner - it works!

if ($earthchem) {
	require ("../db.php"); // database driver, and db connect for EarthChem
} elseif ($navdat) {
	require ("includes/navdat_db.php"); // database driver, and db connect for EarthChem
} elseif ($getsample) {
	// We passed in the data, we do not need to search the databasel
}

// Receive or build the SQL statement to retrieve data points for the TAS plot. 
// First time we arrive on this page, variables will be passed from the form on the Output Data (Navdat) or Results (EarthChem) page.
// After that, variables will be passed from the form on this page. 
// Parameters passed if we came from GetSample.* on either EC or Navdat. If we arrived here from GetSample.*, we are plotting a single sample; get the x,y coordinates for the sample & skip all the query and code that gets the x,y coordinates for samples
$tas_sql = "";
if ($getsample) {
	$sio2=$_GET["sio2"];$alkali=$_GET["alkali"];
	$tas_sql="foo";
} elseif (isset($_POST['tas_sql'])) {
	$tas_sql=$_POST['tas_sql']; // Passed as a hidden variable in the form on this page. For both EC and Navdat.  
} elseif ($earthchem) {
	include("../earthchemphp3/queries.php"); // This should build $tas_sql for the EarthChem search query. Uses $pkey 
} elseif ($navdat) { // Navdat Output Data page should have passed $navdatsqlstatement and $pkey 
	if (!isset($_POST['navdatsqlstatement'])) {
		// Something is wrong, we did not get a sql statement, so build one using the pkey which we are certain to have	
		$navdatsqlstatement = $db->get_var("SELECT navdatsqltatement FROM search_query WHERE pkey = ".$pkey);
	} else {
		$navdatsqlstatement=$_POST['navdatsqlstatement']; 
	}
	// Rewrite the SQL statement to select only the fields we need, to greatly speed up the query
	$from_position = stripos($navdatsqlstatement, 'FROM ');
	$tas_sql=substr($navdatsqlstatement,$from_position);
	$tas_sql="SELECT sio2, na2o, k2o, samp.sample_num ".$tas_sql;
} 
if (strlen($tas_sql)==0) {echo "<p>Something is wrong. There is no search query. Please start a new search and try again.<p>";exit;}

//echo nl2br($tas_sql); exit();
//echo nl2br($advstring); exit();
//echo nl2br($foofoo_sql); exit();


// MAKE THE FILENAME: name of the image containing the TAS diagram, which we will store on the server, then display in the browser
if ($getsample) {
	$image_filename="plots/".$_GET["image_filename"]; // we passed in the name of the image, so we'd know the name in the calling page
}
elseif ($earthchem) {
	$image_filename='plots/tas-plot-ec-'.time().'.png';
}
elseif ($navdat) {
	$image_filename='plots/tas-plot-navdat-'.time().'.png';
}

if (isset($_POST['plotsize'])) {$plotsize=$_POST["plotsize"]; if ($plotsize<600) {$plotsize=600;}} else {$plotsize=600;} // from the form below

$sidex=$plotsize; $sidey=floor($plotsize*4/5); // plot dimension is a 4:3 width:height ratio
$margin=floor(($plotsize)/10); if ($margin>100) {$margin=100;} elseif ($margin<60) {$margin=60;}
$margin2=floor($margin*.75); // for the top and right margins, a little smaller
$sidex=$sidex+$margin+$margin2;$sidey=$sidey+$margin+margin2;
$margin_top=100;$margin_bottom=$sidey-100; 
//$progress_image = '<img align="middle" border=0 hspace=0 vspace=0 src="images/progress.gif" height=16 width=128 style="margin:'.$margin_top.' auto '.$margin_bottom.' auto ">';  
$progress_image = '<img align="middle" border=0 hspace=0 vspace=0 src="images/waiting.gif" height=28 width=28 style="margin:'.$margin_top.' auto '.$margin_bottom.' auto ">';
echo '<div id="div_plot" style="width:100%;text-align:center">'.$progress_image;
echo '</div>';

ob_flush();flush();

/*/ FIND OUT HOW MANY ROWS THIS QUERY WILL RETURN - possibly to use this to make a meaningful progress bar 
$from_position = stripos($tas_sql, 'FROM ');
$count_sql=substr($tas_sql,$from_position);
$count_sql="SELECT count(*) ".$count_sql;
*/

$point_design_method=$_POST["point_design_method"]; // from the form below - userdefined plot points or icon below
$pointsize_user_selected=$_POST["pointsize"]; if ($pointsize_user_selected<1) {$pointsize_user_selected=5;} $pointsize=$pointsize_user_selected; // from the form below
$fillcolor_user_selected=$_POST["fillcolor"]; if ($fillcolor_user_selected=="") {$fillcolor_user_selected='#ff0000';} $fillcolor=$fillcolor_user_selected; // from the form below
/* commented out option for indexed color, we may decide to offer this in the future:
$customfillcolor=trim($_POST["customfillcolor);
if (strlen($customfillcolor)>0) {
	$customfillcolor = str_replace("#", "", $customfillcolor); //strip any #
	if (strlen($customfillcolor)==6) {
		$customfillcolor="#".$customfillcolor; //add a leading #
		$fillcolor=$customfillcolor; // set the color to the valid custom color the user provided
	}
}*/
if (!$fillcolor>'') {$fillcolor="#ff0000";} // default fill color
$strokecolor_user_selected=$_POST["strokecolor"]; $strokecolor=$strokecolor_user_selected; if ($strokecolor_user_selected=="") {$strokecolor_user_selected='#000000';} // from the form below
if (strlen($strokecolor) == 0) {$strokecolor='#000000';} // default stroke color
$rocknamecolor=$_POST["rocknamecolor"]; // from the form below
if (strlen($rocknamecolor) == 0) {$rocknamecolor='#000000';} // default rock name color
$pointtype_userdefined=$_POST["pointtype_userdefined"]; // from the form below
$icon=$_POST["icon"]; // from the form below
if ($point_design_method == 'icon') {if ($icon > "") {$pointtype=$icon;}} else {$pointtype=$pointtype_userdefined;}
if ($pointtype=="") {$pointtype='MARK_DIAMOND"';} // default is diamond point shape
$links=$_POST["links"]; // true or false: include image map, links to individual samples?
$scale=$plotsize/100;
$axistitle_fontsize=5+$scale; //10; if ($plotsize<500) {$axistitle_fontsize-=2;} elseif ($plotsize>600) {$axistitle_fontsize+=2;}
$title_fontsize=7+$scale; //14; if ($plotsize<500) {$title_fontsize-=2;} elseif ($plotsize>600) {$title_fontsize+=2;}
$ticklabel_fontsize=7; if ($scale > 5) {$ticklabel_fontsize++;}
$xtitlemargin=32+$scale; $ytitlemargin=$xtitlemargin+$axistitle_fontsize+4;
$gridcolor="#c7dedf";
$titlecolor="#363535";
$subtitlecolor="#4c4b4b";
$tickcolor=$subtitlecolor;
$rockname_fontsize=$plotsize/100; if ($rockname_fontsize<7) {$rockname_fontsize=7;}

if ($getsample) { // If we are plotting a single sample, i.e. we came from getsample.cfm, build the array of 1 from the parms passed in
	$datax[]=$sio2;  
	$datay[]=$alkali;  
}

// Scale for a TAS plot is always the same
$ymin = 0; $ymax = 18;
$xmin = 36; $xmax = 84;
if ($getsample=="") { // If we are not plotting a single sample, for GetSample.cfm, set the scales for the x and y axes

//$success=$db->query("CREATE SEQUENCE temp_seq");



//echo nl2br($tas_sql);exit();

$results=$db->get_results($tas_sql) or die("could not execute tas_sql query in tas.php near line 178");

foreach ($results as $row) {
	$SiO2=$row->sio2;
	$Alkali=$row->na2o + $row->k2o;
	if ($SiO2 >= $xmin && $SiO2 <= $xmax && $Alkali > 0 && $Alkali <= $ymax) {
		$datax[]= $SiO2; //$SiO2_position;
		$datay[]= $Alkali; //$Alkali_position;
		if ($links) { // if user chose links, get data for the image map
			$url="";
			if ($navdat) {
				$url = "../navdat/NavdatPortal/GetSample.cfm?sample_num=".$row->sample_num;
			} elseif ($earthchem) {
				$url=$row->url;
				//$url=$row->sample_pkey;
			}
			$alt_msg="";
			if (strlen($url)<0) {$url="no_link.php"; $alt_msg="No link to sample data is available ~ ";}
			$alt[] = $alt_msg." SiO2 ".$SiO2." ~ Alkali ".$Alkali;
			$targ[]='javascript:window.open("'.$url.'","newwindow","height=600,width=800,scrollbars=1,resizable=1");newwindow.focus()';
		}
	} // if SiO2 >. ...
} // foreach

} // if $earthchem or $navdat, and not getsample


$samplecount=sizeof($datax);
if ($samplecount<1) {echo "<div align=center> <p />No samples meet the criteria for plotting on the TAS diagram.</div>"; 
	?>
	<script language="javascript">
	document.getElementById('div_plot').innerHTML='';
	</script>
	<?
	exit; 
}


// NOW START TO BUILD THE GRAPH IN JPGRAPH
$xtitle = 'SiO2 (wt. percent)';
$ytitle = 'Na2O + K2O (wt. percent)';
$title = 'Total Alkali vs SiO2';

// define where our TTF fonts are stored and which font to use
//DEFINE("TTF_DIR","usr/X11R6/lib/X11/fonts/truetype/");
DEFINE("TTF_DIR","/var/www/truetype/");
DEFINE("TTF_FONTFILE","arial.ttf");
DEFINE("TTF_FONTFILE","arialbd.ttf");

$graph = new Graph($sidex,$sidey,"auto"); // start a new graph

$graph->CheckCSIMCache( "image1",10); // image map cache

$graph->img->SetAntiAliasing();

$graph->img->SetMargin($margin,10,$margin,$margin); // left, right, top, bottom
//$graph->SetShadow(); // drop shadow around graph

$graph->SetScale('linlin',$ymin,$ymax,$xmin,$xmax);

// Set major * minor tick dist
$graph->yaxis->scale->ticks->Set(2,1);
$graph->xaxis->scale->ticks->Set(4,2);

$graph->SetColor("#ffffff");
//$graph->SetMarginColor("#ffffff");

$graph->SetGridDepth(DEPTH_BACK); // DEPTH_FRONT or DEPTH_BACK; sets whether grid is under or over plot points
$graph->xgrid->SetColor($gridcolor);
$graph->xgrid->Show();

//$graph->SetBackgroundImage("watermark-4x3.png",BGIMG_FILLFRAME); // watermark - I redid this as a footer, below

/* I chose to make the axis titles as arbitrary text, below - but save this for reference. 
// Add x and y axis titles, using the automatic way
$graph->xaxis->SetTitle($xtitle,'middle');
$graph->xaxis->title->SetFont(FF_ARIAL,FS_NORMAL,$axistitle_fontsize);
$graph->xaxis->title->SetColor($subtitlecolor);
$graph->xaxis->SetTitleMargin($xtitlemargin);
$graph->yaxis->SetTitle($ytitle,'middle');
$graph->yaxis->title->SetFont(FF_ARIAL,FS_NORMAL,$axistitle_fontsize);
$graph->yaxis->title->SetColor($subtitlecolor);
$graph->yaxis->SetTitleMargin($ytitlemargin);
*/

$graph->title->Set($title);
$graph->title->SetFont(FF_ARIAL,FS_NORMAL,$title_fontsize);
$graph->title->SetColor($titlecolor);
//$graph->title->SetMargin($titlemargin); //no title margin anymore 08142013

// Now add the x and y axis titles, using the technique of explicitly positioning arbitrary text
$graph->xaxis->SetFont(FF_ARIAL,FS_NORMAL,$ticklabel_fontsize); // set some fonts and colors for the x, y ticks and axes
$graph->xaxis->SetColor($gridcolor,$tickcolor);
$graph->yaxis->SetFont(FF_ARIAL,FS_NORMAL,$ticklabel_fontsize);
$graph->yaxis->SetColor($gridcolor,$tickcolor);
// the y axis title
$txt = new Text($ytitle);
$txt->SetFont(FF_ARIAL,FS_NORMAL,$axistitle_fontsize);
$txt->SetColor($subtitlecolor);
$x=0+floor($axistitle_fontsize*10/12);$y=$sidey/2;
$txt->SetAngle(90);
$txt->SetPos($x,$y,center,center);
$graph->Add($txt);
/* the x axis title - one way to do it, with arbitrary text
$txt = new Text($xtitle);
$txt->SetFont(FF_ARIAL,FS_NORMAL,$axistitle_fontsize);
$txt->SetColor($subtitlecolor);
$y=$sidey-$axistitle_fontsize;$x=$sidex/2;
$txt->SetAngle(0);
$txt->SetPos($x,$y,center,bottom);
$graph->Add($txt); */

/* the NAVDAT watermark - one way to do it, with arbitrary text
$watermark_fontsize=16;
$txt = new Text('NAVDAT');
$txt->SetFont(FF_ARIAL,FS_BOLD,$watermark_fontsize);
$txt->SetColor('#efefef');
$x=0+floor($axistitle_fontsize*10/12);$y=$sidey-$axistitle_fontsize;
$txt->SetAngle(0);
$txt->SetPos($x,$y,left,bottom);
$graph->Add($txt);*/

// the watermark as a footer
if ($navdat) {$graph->footer->left->Set("NAVDAT");}
if ($earthchem) {$graph->footer->left->Set("earthchem");}
if ($getsample) {$graph->footer->left->Set("");}
$graph->footer->left->SetColor('#efefef');
$watermark_fontsize=floor($plotsize/100) * 3;
$graph->footer->left->SetFont(FF_ARIAL,FS_BOLD,$watermark_fontsize);
// make the x axis title using the footer capability
$graph ->footer->center->Set($xtitle);
$graph->footer->center->SetColor($subtitlecolor);
$graph->footer->center->SetFont(FF_ARIAL,FS_NORMAL,$axistitle_fontsize);
// show the number of samples plotted in the lower right corner - but not for the single-point plot for the sample detail in GetSample.cfm
if (!$getsample) {
$graph ->footer->right->Set($samplecount." samples");
$graph->footer->right->SetColor($subtitlecolor);
$count_fontsize=$axistitle_fontsize-3;
$graph->footer->right->SetFont(FF_ARIAL,FS_NORMAL,$count_fontsize);
}

$sp1 = new ScatterPlot($datay,$datax); 
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
} elseif ($pointtype == 'MARK_FLAG1') {
$sp1->mark->SetType(MARK_FLAG1,'United Nations');
} elseif ($pointtype == 'MARK_IMG_SBALL') {
//'bluegreen','cyan','darkgray','greengray', 'gray','graypurple','green','greenblue','lightblue', 'lightred','navy','orange','purple','red','yellow'
$sp1->mark->SetType(MARK_IMG_SBALL,'red');
} elseif ($pointtype == 'MARK_IMG_MBALL') {
//'blue','bluegreen','brown','cyan', 'darkgray','greengray','gray','green', 'greenblue','lightblue','lightred', 'purple','red','white','yellow'
$sp1->mark->SetType(MARK_IMG_MBALL,'red');
} elseif ($pointtype == 'MARK_IMG_LBALL') {
//	'blue','lightblue','brown','darkgreen', 'green','purple','red','gray','yellow','silver','gray'
$sp1->mark->SetType(MARK_IMG_LBALL,'red');
} elseif ($pointtype == 'MARK_IMG_SQUARE') {
//'bluegreen','lightblue','purple','blue','green','pink','red','yellow
$sp1->mark->SetType(MARK_IMG_SQUARE,'red'); //'bluegreen','blue','green', 'lightblue','orange','purple','red','yellow'
} elseif ($pointtype == 'MARK_IMG_STAR') {
//'bluegreen','lightblue','purple','blue','green','pink','red','yellow
$sp1->mark->SetType(MARK_IMG_STAR,'blue');
} elseif ($pointtype == 'MARK_IMG_DIAMOND') {
$sp1->mark->SetType(MARK_IMG_DIAMOND,'red'); //'lightblue','darkblue','gray', 'blue','pink','purple','red','yellow'
} elseif ($pointtype == 'MARK_IMG_BEVEL') {
$sp1->mark->SetType(MARK_IMG_BEVEL,'red'); //'green','purple','orange','red','yellow'
} elseif ($pointtype == 'JAYHAWK') {
$sp1->mark->SetType(MARK_IMG,'jayhawk.png');
} elseif ($pointtype == 'GHAWK') {
$sp1->mark->SetType(MARK_IMG,'ghawk.png');
} elseif ($pointtype == 'TARHEEL') {
$sp1->mark->SetType(MARK_IMG,'tarheel.png');
} elseif ($pointtype == 'BUFFALO') {
$sp1->mark->SetType(MARK_IMG,'buffalo.png');
} else {
$sp1->mark->SetType(MARK_DIAMOND);
}

$sp1->mark->SetFillColor($fillcolor);
$sp1->mark->SetColor($strokecolor);
// make the plot point bigger if we are making a plot with a single datapoint for the sample detail
if ($getsample) {$pointsize++;$pointsize++;$pointsize++;}
$sp1->mark->SetWidth($pointsize);

$sp1->SetCSIMTargets($targ,$alt);

$graph->Add($sp1);



// NOW ADD THE LINES AND ROCK NAMES. THE LINES ARE ACTUALLY MULTIPLE SCATTER PLOTS WITH LINES CONNECTING THE DATA POINTS
// The points for the dividing lines are from this gnuplot TAS graph routine: http://warmada.staff.ugm.ac.id/Graphics/gnuplot/petclass/bas.html
// However, I took out any points that are along a straight line, to speed up the plotting

$fillcolor=$rocknamecolor;$strokecolor=$rocknamecolor;$linecolor=$rocknamecolor;
$pointsize=0;$linesize=1;

// IMPORTANT: Must name these differently, e.g. $lp, $lp0, $lp1... The verion of jpgraph on neko allows one to reuse these variables for different layers, but the jpgraph in matisse needs unique names for the layers (or will display only the last one identically named)
$datax=array(41,41,52.5);
$datay=array(0,7,14);
$lp = new ScatterPlot($datay,$datax);
$lp->mark->SetType(MARK_SQUARE);
$lp->mark->SetFillColor($fillcolor);
$lp->mark->SetColor($strokecolor);
$lp->mark->SetWidth($pointsize);
$lp->SetLinkPoints( true,$linecolor,$linesize); // lines connect the points
$graph->Add($lp);

$datax=array(50,52.5,57.6,63,63);
$datay=array(15.1,14,11.7,7,0);
$lp0 = new ScatterPlot($datay,$datax);
$lp0->mark->SetType(MARK_SQUARE);
$lp0->mark->SetFillColor($fillcolor);
$lp0->mark->SetColor($strokecolor);
$lp0->mark->SetWidth($pointsize);
$lp0->SetLinkPoints( true,$linecolor,$linesize); // lines connect the points
$graph->Add($lp0);

$datax=array(76.3,69);
$datay=array(0,8);
$lp1 = new ScatterPlot($datay,$datax);
$lp1->mark->SetType(MARK_SQUARE);
$lp1->mark->SetFillColor($fillcolor);
$lp1->mark->SetColor($strokecolor);
$lp1->mark->SetWidth($pointsize);
$lp1->SetLinkPoints( true,$linecolor,$linesize); // lines connect the points
$graph->Add($lp1);

$datax=array(36,46);
$datay=array(10,10);
$lp2 = new ScatterPlot($datay,$datax);
$lp2->mark->SetType(MARK_SQUARE);
$lp2->mark->SetFillColor($fillcolor);
$lp2->mark->SetColor($strokecolor);
$lp2->mark->SetWidth($pointsize);
$lp2->SetLinkPoints( true,$linecolor,$linesize); // lines connect the points
$graph->Add($lp2);

$datax=array(36,41);
$datay=array(7,7);
$lp3 = new ScatterPlot($datay,$datax);
$lp3->mark->SetType(MARK_SQUARE);
$lp3->mark->SetFillColor($fillcolor);
$lp3->mark->SetColor($strokecolor);
$lp3->mark->SetWidth($pointsize);
$lp3->SetLinkPoints( true,$linecolor,$linesize); // lines connect the points
$graph->Add($lp3);

$datax=array(36,45);
$datay=array(3,3);
$lp4 = new ScatterPlot($datay,$datax);
$lp4->mark->SetType(MARK_SQUARE);
$lp4->mark->SetFillColor($fillcolor);
$lp4->mark->SetColor($strokecolor);
$lp4->mark->SetWidth($pointsize);
$lp4->SetLinkPoints( true,$linecolor,$linesize); // lines connect the points
$graph->Add($lp4);

$datax=array(45,45,45,49.4,53,57.6,61);
$datay=array(0,3,5,7.3,9.3,11.7,13.5);
$lp5 = new ScatterPlot($datay,$datax);
$lp5->mark->SetType(MARK_SQUARE);
$lp5->mark->SetFillColor($fillcolor);
$lp5->mark->SetColor($strokecolor);
$lp5->mark->SetWidth($pointsize);
$lp5->SetLinkPoints( true,$linecolor,$linesize); // lines connect the points
$graph->Add($lp5);

$datax=array(45,52,69,69);
$datay=array(5,5,8,13);
$lp6 = new ScatterPlot($datay,$datax);
$lp6->mark->SetType(MARK_SQUARE);
$lp6->mark->SetFillColor($fillcolor);
$lp6->mark->SetColor($strokecolor);
$lp6->mark->SetWidth($pointsize);
$lp6->SetLinkPoints( true,$linecolor,$linesize); // lines connect the points
$graph->Add($lp6);

$datax=array(45,49.4,52,52);
$datay=array(9.4,7.3,5,0);
$lp7 = new ScatterPlot($datay,$datax);
$lp7->mark->SetType(MARK_SQUARE);
$lp7->mark->SetFillColor($fillcolor);
$lp7->mark->SetColor($strokecolor);
$lp7->mark->SetWidth($pointsize);
$lp7->SetLinkPoints( true,$linecolor,$linesize); // lines connect the points
$graph->Add($lp7);

$datax=array(48.4,53,57,57);
$datay=array(11.5,9.3,5.9,0);
$lp8 = new ScatterPlot($datay,$datax);
$lp8->mark->SetType(MARK_SQUARE);
$lp8->mark->SetFillColor($fillcolor);
$lp8->mark->SetColor($strokecolor);
$lp8->mark->SetWidth($pointsize);
$lp8->SetLinkPoints( true,$linecolor,$linesize); // lines connect the points
$graph->Add($lp8);

/* Draw the arrows for Foldite. I used a field plot instead, but keep this code for reference.
$linecolor='blue';$fillcolor='blue';$pointsize=5;
$datax=array(38.7,38.7);
$datay=array(7.9,2.5);
$lp2 = new ScatterPlot($datay,$datax);
$lp2->mark->SetType(MARK_DIAMOND);
$lp2->mark->SetFillColor($fillcolor);
$lp2->mark->SetColor($strokecolor);
$lp2->mark->SetWidth($pointsize);
$lp2->SetLinkPoints( true,$linecolor,$linesize); // lines connect the points
$graph->Add($lp2);
*/

// Now add the rock names as text superimposed on the plot

//Foidite
// This would place an image, if I were using images for rock names. I'm leaving these commented-in because the x,y values are worked out - in case we use images at a later date.  
/////$datax=array(39.25);$datay=array(8.5);$lp2 = new ScatterPlot($datay,$datax); $lp2->mark->SetType(MARK_IMG,'spacer.png'); $graph->Add($lp2);
$txt1 = new Text("Foidite");
$txt1->SetColor($rocknamecolor);
$txt1->SetFont(FF_ARIAL,FS_NORMAL,$rockname_fontsize);
$txt1->SetScalePos(37.25,8.75);
$graph->AddText($txt1);

//Phonolite
/////$datax=array(57);$datay=array(14.5);$lp2 = new ScatterPlot($datay,$datax); $lp2->mark->SetType(MARK_IMG,'spacer.png'); $graph->Add($lp2);
$txt2 = new Text("Phonolite");
$txt2->SetColor($rocknamecolor);
$txt2->SetFont(FF_ARIAL,FS_NORMAL,$rockname_fontsize);
$txt2->SetScalePos(54.70,15);
$graph->AddText($txt2);

//Tephriphonolite
/////$datax=array(53);$datay=array(11.85);$lp2 = new ScatterPlot($datay,$datax); $lp2->mark->SetType(MARK_IMG,'spacer.png'); $graph->Add($lp2);
$txt3 = new Text("Tephriphonolite");
$txt3->SetColor($rocknamecolor);
$txt3->SetFont(FF_ARIAL,FS_NORMAL,$rockname_fontsize);
$txt3->SetScalePos(49,11.75);
$graph->AddText($txt3);

//Phonotephrite
/////$datax=array(49);$datay=array(9.75);$lp2 = new ScatterPlot($datay,$datax); $lp2->mark->SetType(MARK_IMG,'spacer.png'); $graph->Add($lp2);
$txt4 = new Text("Phonotephrite");
$txt4->SetColor($rocknamecolor);
$txt4->SetFont(FF_ARIAL,FS_NORMAL,$rockname_fontsize);
$txt4->SetScalePos(45.75,9.6);
$graph->AddText($txt4);

//Tephrite
/////$datax=array(44.5);$datay=array(6.5);$lp2 = new ScatterPlot($datay,$datax); $lp2->mark->SetType(MARK_IMG,'spacer.png'); $graph->Add($lp2);
$txt5 = new Text("Tephrite");
$txt5->SetColor($rocknamecolor);
$txt5->SetFont(FF_ARIAL,FS_NORMAL,$rockname_fontsize);
$txt5->SetScalePos(42,6.75);
$graph->AddText($txt5);

//Basanite
//$datax=array(43.5);$datay=array(5.5);$lp2 = new ScatterPlot($datay,$datax); $lp2->mark->SetType(MARK_IMG,'spacer.png'); $graph->Add($lp2);
$txt6 = new Text("Basanite");
$txt6->SetColor($rocknamecolor);
$txt6->SetFont(FF_ARIAL,FS_NORMAL,$rockname_fontsize);
$txt6->SetScalePos(41.5,5.75);
$graph->AddText($txt6);

//Picrobasalt
//$datax=array(43);$datay=array(2.5);$lp2 = new ScatterPlot($datay,$datax); $lp2->mark->SetType(MARK_IMG,'spacer.png'); $graph->Add($lp2);
$txt7 = new Text("Picro-
basalt");
$txt7->SetColor($rocknamecolor);
$txt7->SetFont(FF_ARIAL,FS_NORMAL,$rockname_fontsize);
$txt7->SetScalePos(41.5,2.75);
$graph->AddText($txt7);

// Trachyte
//$datax=array(64.75);$datay=array(12.5);$lp2 = new ScatterPlot($datay,$datax); $lp2->mark->SetType(MARK_IMG,'spacer.png'); $graph->Add($lp2);
$txt8 = new Text("Trachyte");
$txt8->SetColor($rocknamecolor);
$txt8->SetFont(FF_ARIAL,FS_NORMAL,$rockname_fontsize);
$txt8->SetScalePos(62.5,12.75);
$graph->AddText($txt8);

//Trachydacite
//$datax=array(64.75);$datay=array(9.25);$lp2 = new ScatterPlot($datay,$datax); $lp2->mark->SetType(MARK_IMG,'spacer.png'); $graph->Add($lp2);
$txt9 = new Text("Trachydacite");
$txt9->SetColor($rocknamecolor);
$txt9->SetFont(FF_ARIAL,FS_NORMAL,$rockname_fontsize);
$txt9->SetScalePos(62.5,9.5);
$graph->AddText($txt9);

//Trachyandesite
/////$datax=array(57.5);$datay=array(9);$lp2 = new ScatterPlot($datay,$datax); $lp2->mark->SetType(MARK_IMG,'spacer.png'); $graph->Add($lp2);
$txt10 = new Text("Trachyandesite");
$txt10->SetColor($rocknamecolor);
$txt10->SetFont(FF_ARIAL,FS_NORMAL,$rockname_fontsize);
$txt10->SetScalePos(53.5,9.5);
$graph->AddText($txt10);

//Trachybasalt
/////$datax=array(49);$datay=array(5.9);$lp2 = new ScatterPlot($datay,$datax); $lp2->mark->SetType(MARK_IMG,'spacer.png'); $graph->Add($lp2);
$txt11 = new Text("Trachybasalt");
$txt11->SetColor($rocknamecolor);
$txt11->SetFont(FF_ARIAL,FS_NORMAL,$rockname_fontsize);
$txt11->SetScalePos(46.1,5.5);
$graph->AddText($txt11);

//Basaltic Trachyandesite
/////$datax=array(53);$datay=array(7.5);$lp2 = new ScatterPlot($datay,$datax); $lp2->mark->SetType(MARK_IMG,'spacer.png'); $graph->Add($lp2);
$txt12 = new Text("Basaltic
Trachy-
andesite");
$txt12->SetColor($rocknamecolor);
$txt12->SetFont(FF_ARIAL,FS_NORMAL,$rockname_fontsize);
$txt12->SetScalePos(51,7.75);
$graph->AddText($txt12);

//Basalt
$txt13 = new Text("Basalt");
$txt13->SetColor($rocknamecolor);
$txt13->SetFont(FF_ARIAL,FS_NORMAL,$rockname_fontsize);
$txt13->SetScalePos(47.5,3.75);
$graph->AddText($txt13);

// Rhyolite
/////$datax=array(73);$datay=array(8);$lp2 = new ScatterPlot($datay,$datax); $lp2->mark->SetType(MARK_IMG,'spacer.png'); $graph->Add($lp2);
$txt14 = new Text("Rhyolite");
$txt14->SetColor($rocknamecolor);
$txt14->SetFont(FF_ARIAL,FS_NORMAL,$rockname_fontsize);
$txt14->SetScalePos(72.,8.25);
$graph->AddText($txt14);

//Dacite
/////$datax=array(67.75);$datay=array(3.5);$lp2 = new ScatterPlot($datay,$datax); $lp2->mark->SetType(MARK_IMG,'spacer.png'); $graph->Add($lp2);
$txt15 = new Text("Dacite");
$txt15->SetColor($rocknamecolor);
$txt15->SetFont(FF_ARIAL,FS_NORMAL,$rockname_fontsize);
$txt15->SetScalePos(65.75,3.75);
$graph->AddText($txt15);

//Andesite
/////$datax=array(60.125);$datay=array(3.5);$lp2 = new ScatterPlot($datay,$datax); $lp2->mark->SetType(MARK_IMG,'spacer.png'); $graph->Add($lp2);
$txt16 = new Text("Andesite");
$txt16->SetColor($rocknamecolor);
$txt16->SetFont(FF_ARIAL,FS_NORMAL,$rockname_fontsize);
$txt16->SetScalePos(58,3.75);
$graph->AddText($txt16);

//Basaltic Andesite
/////$datax=array(54.5);$datay=array(3.5);$lp2 = new ScatterPlot($datay,$datax); $lp2->mark->SetType(MARK_IMG,'spacer.png'); $graph->Add($lp2);
$txt17 = new Text("Basaltic
Andesite");
$txt17->SetColor($rocknamecolor);
$txt17->SetFont(FF_ARIAL,FS_NORMAL,$rockname_fontsize);
$txt17->SetScalePos(52.5,3.75);
$graph->AddText($txt17);


// Now make the arrows around Foidite. Do this by making a field plot with 2 points.
// Setup the field plot
$datax=array(38.5,41.5); // x and y are the center of the arrow's line
$datay=array(5.75,10.5);
$angle=array(-90,45); // angle is which direction the arrow points, -90 is down
$fp = new FieldPlot($datay,$datax,$angle);
// First SetSize argument is length (in pixels of arrow)
// Second SetSize argument is roughly size of arrow. Arrow size is specified as an integer in the range [0,9]
$fp->arrow->SetSize(80,4);
$fp->arrow->SetColor('#cccccc');
$graph->Add($fp);

/* experiment with text placement - works!
$txt=new Text("text test");
$txt->SetPos($margin,$margin2); // position by exact x,y coordinate on the whole image
$txt->SetColor( "red");
$graph->AddText( $txt);
$txt2 = new Text("text test 2");
$txt2->SetScalePos(80,15); // position by x,y scale on the plot
$txt->SetColor( "blue");
$graph->AddText($txt2);
*/

/* example of a canvas graph, using a scale
$g = new CanvasGraph(550,450);
$scale = new CanvasScale($g);
$scale->Set(0,27,0,53);
$t = new CanvasRectangleText();
$t->SetFillColor('lightgreen');
$t->SetFontColor('navy');
$t->SetFont(FF_ARIAL,FS_NORMAL,16);
$t->ParagraphAlign('center');
$t->Set("First line\nSecond line",0.5,19,26,32);
$t->Stroke($g->img,$scale);
*/

// Almost done! Now add a frame, or not:
$graph->SetFrame(true,'#ffffff',0); // no frame around the graph

// use this if you are caching an image map file $graph->StrokeCSIM();

// imagemap code from tas.cfm:
//<area shape="circle" coords="#SIO2Location#,#AlkaliLocation#,8" href="../NavdatPortal/GetSample.cfm?sample_num=#CurrentSampleNum#">

// Now send the image to the browser without saving it on the server - or generate an image on the server, both of the next 2 statements work
//$graph->Stroke(''); // generate the image, send it to the browser without saving it on the server - but we cannot have an image map if we do this
$graph->Stroke($image_filename); // generate the image and save it to a file 
















//**************************************** THE TAS DIAGRAM IMAGE **************************************
/*
echo '<div style="text-align:center"><a href="'.$image_filename.'">';
if ($links) { // If the user selected links in the form, add an image map with links to the individual samples
$mapName = 'TASmap';
$imgMap = $graph->GetHTMLImageMap($mapName);
echo "
$imgMap
";
echo "
<img src='$image_filename' alt='$title' ismap usemap='#".$mapName."' border='0' hspace=20 vspace=0>
";
} else { // no links
echo "
<img src='$image_filename' alt='$title' border='0' hspace=20 vspace=0>
";
echo '</a></div>';
}
*/
$plot_image='<a href="'.$image_filename.'">';
if ($links) { // If the user selected links in the form, add an image map with links to the individual samples
$mapName = 'TASmap';
$imgMap = $graph->GetHTMLImageMap($mapName);
echo $imgMap;
$plot_image .= '<img src="'.$image_filename.'" alt="'.$title.'" ismap usemap="#'.$mapName.'" border=0 hspace=20 vspace=20>';
} else { // no links
$plot_image .= '<img src="'.$image_filename.'" alt="'.$title.'" border=0 hspace=20 vspace=20>';
}
$plot_image .= '</a>';
//if ($earthchem or $navdat) {
?>
<script language="javascript">
document.getElementById('div_plot').innerHTML='<?=$plot_image?>';
</script>
</div>

<?php












// ******************************************************* INPUT FORM ************************************************************
include "includes/plot_design_arrays.php"; // arrays of colors, point types, point icons
?>

   <form name="form_tax" id="form_tax" action="" method="post" style="margin-top:50px">
      <table align=center border=0 cellspacing=10 cellpadding=0>
         <tr valign=top>
            <td colspan=3 > 
			<div style="float:left;"><b>Design and view TAS diagram:</b> 
			<?php
			/*<i>
               <ol>
                  <li>To redesign the diagram, make selections below. (Defaults apply)</li>
                  <li>To make the diagram, click the 'plot' button.</li>
               </ol>
               </i> */
			   ?>
			   </div>
			   <div id="button" style="float:right;margin:20 0 20 20">
               <?php
if ($earthchem) { // use an HTML buttom for EarthChem
?>
               <input type="submit" name="submit" value="Plot"> 
               <?php
} elseif ($navdat) { // use a custom button for Navdat
?>
               <input type="submit" value="" style="background-image:url(images/navdat_plot.gif); width:68px; height:30px; border:0;" /> 
               <?php
}
?>
</div><!-- div button -->
<br clear="all" />
            </td>
         </tr>
         <tr>
            <td colspan=3 >&bull; Choose width of plotting area
               <select name="plotsize">
                  <?php
for ( $i = 600; $i <= 1000; $i += 100) {
	echo "<option value='$i'"; if ($plotsize==$i) {echo " selected ";} echo "> $i ";
}
?>
               </select>
               <font size=1>pixels</font></td>
         </tr>
         <tr valign=top>
            <td colspan=3 >&bull; Design datapoints, or choose an icon for the datapoints.</td>
         </tr>
         <tr valign=top>
            <td>
               <table border=0 style="margin-left:20px">
                  <tr valign=top>
                     <td colspan=2 >
                        <input 
<?php
if ($point_design_method=='userdefined') {echo ' checked ';}
?>
 type="radio" name="point_design_method" value="icon" >
                        Design datapoints:</td>
                  </tr><div id="div_userdefined" style="display:inline"> 
                  <tr>
                     <td align=right>point shape</td>
                     <td>
                        <select name="pointtype_userdefined" style="width:120px">
                           <?php
foreach($pointtypes_array as $key=>$val)
{
	echo '<option '; if ($pointtype_userdefined==$val) {echo ' selected ';} echo ' value="'.$val.'">'.$key.'</option>';
}
?>
                        </select>
                     </td>
                  </tr>
                  <tr valign=top>
                     <td align=right>point fill color</td>
                     <td>
                        <select name="fillcolor" style="width:120px">
                           <?php
foreach($colors_array as $key=>$val)
{
	echo '<option '; if ($fillcolor_user_selected==$val) {echo ' selected ';} echo ' style="background-color:'.$val.'" value="'.$val.'">'.$key;
}
?>
                        </select>
                  <tr valign=top>
                     <td align=right>point outline color</td>
                     <td>
                        <select name="strokecolor" style="width:120px">
                           <?php
foreach($colors_array as $key=>$val)
{
	echo '<option '; if ($strokecolor_user_selected==$val) {echo ' selected ';} echo ' style="background-color:'.$val.'" value="'.$val.'">'.$key;
}
?>
                        </select>
                     </td>
                  </tr>
                  <tr valign=top>
                  <td align=right>point size</td>
                  <td>
                  <select name="pointsize" style="width:120px">
                  
                  <?php
for ( $i = 1; $i <= 10; $i++) {
echo "<option value='$i' "; if ($pointsize_user_selected==$i) {echo " selected ";} echo " > $i";
}
?>
                  </td>
                  </tr>
                  </div>
               </table>
            </td>
            <td></td>
            <td>
               <table border=0>
                  <tr valign=top>
                     <td colspan=3 align=right><i>or</i>
                        <input 
<?php
if ($point_design_method=='icon') {echo ' checked ';} 
?>
type="radio" name="point_design_method" value="icon"  >
                        Choose icon for datapoints:<div id="div_icon"> 
                        <p />
                        <select name="icon" style="width:120px">
                           <?php
foreach($icons_array as $key=>$val)
{
	echo '<option '; if ($icon==$val) {echo ' selected ';} echo ' value="'.$val.'">'.$key;
}
?>
                        </select></div>
                     </td>
                  </tr>
               </table>
            </td>
         </tr>
         <tr valign=top>
            <td colspan=3 >&bull; Choose color for rock names
               <select style="width:120px" name="rocknamecolor">
                  <option value="">
                  <?php
foreach($colors_array as $key=>$val)
{
	echo '<option '; if ($rocknamecolor==$val) {echo ' selected '; } echo ' style="color:'.$val.'" value="'.$val.'">'.$key;
}
?>
               </select>
            </td>
         </tr>
         <tr valign=top>
            <td colspan=3  valign=top>&bull; Link datapoints to sample data?
               <input 
<?php
if ($links) {echo ' checked ';} 
?>
type="checkbox" name="links">
            </td>
         </tr>
      </table>
      <input type="hidden" name="referring_website" value="<?=$referring_website?>">
      <input type="hidden" name="tas_sql" value="<?=$tas_sql?>">
      <input type="hidden" name="pkey" value="<?=$pkey?>">
   </form>
   <p />
   <br><br><br><br><br><br><br><br>
   <!--<a href="/tas.php?pkey=<?=$pkey?>">Click here for SVG</a>-->
</div><!-- div main_div -->


<?php
//echo "tas sql:".nl2br($tas_sql);

ob_flush();flush();
if ($navdat) {
	include "../navdat/NewFooter.cfm";
} elseif ($earthchem) {
	include ("../includes/ks_footer.html");
}

//if($_SERVER['REMOTE_ADDR'] == "129.237.140.222"){echo $tas_sql;}
//echo "tas sql:".$tas_sql;

?>
