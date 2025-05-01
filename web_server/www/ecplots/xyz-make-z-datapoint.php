
<?php
// If we fired this script through javascript, we passed in url variables 
// If we just included this file (in xyz.php) the variables were set in the calling script 
if (isset($_GET["i"]) && strlen($_GET["i"])>0) { $i=$_GET["i"];}
if (isset($_GET["fillcolor"]) && strlen($_GET["fillcolor"])>0) {$fillcolor = $_GET["fillcolor"];}
if (isset($_GET["strokecolor"]) && strlen($_GET["strokecolor"])>0) {$strokecolor = $_GET["strokecolor"];}
if (isset($_GET["pointtype"]) && strlen($_GET["pointtype"])>0) {$pointtype = $_GET["pointtype"];}
if (isset($_GET["pointsize"]) && strlen($_GET["pointsize"])>0) {$pointsize = $_GET["pointsize"];}
if (isset($_GET["timestamp"]) && strlen($_GET["timestamp"])>0) {$timestamp = $_GET["timestamp"];} 


// Default values for point design, different in here than in the calling program. These should never actually need to be set, unless someone visits this page directly in the url without coming from another script, which they would never need to do. If you see a large black square for the datapoint, you'll suspect these defaults were applied here.
if (isset($fillcolor) && strlen($fillcolor)>0) {} else {$fillcolor="#000000";} 
if (isset($strokecolor) && strlen($strokecolor)>0) {} else {$strokecolor="#000000";}
if (isset($pointtype) && strlen($pointtype)>0) {} else {$pointtype="MARK_SQUARE";}
if (isset($pointsize) && strlen($pointsize)>0) {} else {$pointsize=12;}
if (isset($i) && strlen($i)>0) {} else {$i=0;}

// This $jpgraph_files_already_included flag is a crude way to determine whether the jpgraph files were included. If they were included already elsewhere, including them again below causes the script to fail. If we call this script through javascript, we must included the jpgraph files. If we simply "include" this in xyz.php using php include(), we already included the jpgraph files in xyz.php and should not include them again. I set the flag in xyz.php, so if we included this in xyz.php.
if (isset($jpgraph_files_already_included) && $jpgraph_files_already_included) {
// debug echo " not including jpgraph files ";
} else { 
// debug echo " including jpgraph files ";
include ("/var/www/html/jpgraph/jpgraph.php");
include ("/var/www/html/jpgraph/jpgraph_scatter.php");
}
 
$graph=new Graph(30,30,"auto");
$graph->CheckCSIMCache("image2",10); // image map cache
$graph->img->SetAntiAliasing(); 
$graph->SetScale("linlin"); // x and y scales are linear (not text, int, or log)

$graph->img->SetMargin(0,0,0,0); // left, right, top, bottom

$graph->SetColor("#ffffff");

// We don't want any labels at all for this single-point plot
$graph->xaxis->HideLabels(true);
$graph->yaxis->HideLabels(true);
$graph->xaxis->Hide(true);
$graph->yaxis->Hide(true);
$graph->xaxis->HideTicks(true);
$graph->yaxis->HideTicks(true);
$graph->xaxis->HideLine(true);
$graph->yaxis->HideLine(true);

$x=array();$y=array();$x[]=1;$y[]=1; // Define a single point
 
$sp[0]=new ScatterPlot($y,$x);
if ($pointtype == 'MARK_DIAMOND') {
$sp[0]->mark->SetType(MARK_DIAMOND);
} elseif ($pointtype == 'MARK_UTRIANGLE') {
$sp[0]->mark->SetType(MARK_UTRIANGLE);
} elseif ($pointtype == 'MARK_DTRIANGLE') {
$sp[0]->mark->SetType(MARK_DTRIANGLE);
} elseif ($pointtype == 'MARK_SQUARE') {
$sp[0]->mark->SetType(MARK_SQUARE);
} elseif ($pointtype == 'MARK_CIRCLE') {
$sp[0]->mark->SetType(MARK_CIRCLE);
} elseif ($pointtype == 'MARK_FILLEDCIRCLE') {
$sp[0]->mark->SetType(MARK_FILLEDCIRCLE);
} elseif ($pointtype == 'MARK_CROSS') {
$sp[0]->mark->SetType(MARK_CROSS);
} elseif ($pointtype == 'MARK_STAR') {
$sp[0]->mark->SetType(MARK_STAR);
} elseif ($pointtype == 'MARK_X') {
$sp[0]->mark->SetType(MARK_X);
} elseif ($pointtype == 'MARK_IMG_SBALL') {
$sp[0]->mark->SetType(MARK_IMG_SBALL,'red');
} elseif ($pointtype == 'MARK_IMG_MBALL') {
$sp[0]->mark->SetType(MARK_IMG_MBALL,'red');
} elseif ($pointtype == 'MARK_IMG_LBALL') {
$sp[0]->mark->SetType(MARK_IMG_LBALL,'red');
} elseif ($pointtype == 'MARK_IMG_SQUARE') {
$sp[0]->mark->SetType(MARK_IMG_SQUARE,'red'); 
} elseif ($pointtype == 'MARK_IMG_STAR') {
$sp[0]->mark->SetType(MARK_IMG_STAR,'blue');
} elseif ($pointtype == 'MARK_IMG_DIAMOND') {
$sp[0]->mark->SetType(MARK_IMG_DIAMOND,'red'); 
} elseif ($pointtype == 'TARHEEL') {
$sp[0]->mark->SetType(MARK_IMG,'tarheel.png'); 
} elseif ($pointtype == 'BUFFALO') {
$sp[0]->mark->SetType(MARK_IMG,'buffalo.png'); 
} elseif ($pointtype == 'JAYHAWK') {
$sp[0]->mark->SetType(MARK_IMG,'jayhawk.png'); 
} elseif ($pointtype == 'GHAWK') {
$sp[0]->mark->SetType(MARK_IMG,'ghawk.png'); 
} elseif ($pointtype == 'BUFFALO') {
$sp[0]->mark->SetType(MARK_IMG,'buffalo.png'); 
} elseif ($pointtype == 'TARHEEL') {
$sp[0]->mark->SetType(MARK_IMG,'tarheel.png'); 
} else {
$sp[0]->mark->SetType(MARK_DIAMOND);
}
$sp[0]->mark->SetFillColor($fillcolor);
$sp[0]->mark->SetColor($strokecolor);
$sp[0]->mark->SetWidth($pointsize);
$graph->Add($sp[0]);
$graph->SetFrame(true,'#ffffff',1); 
// add a random number to the filename to create a new filename - because if the old image and new image have the same filename, the image in the div won't refresh and we see the old image - but keep a copy with the fixed filename to display elsewhere
$randomnumber=rand(0,100000);
$z_point_image_filename="plots/z-$i-$timestamp.png"; // keep this file, so we can re-display it after the user submits form_xyz
$new_z_point_image_filename="plots/z-$i-$timestamp-$randomnumber.png"; // use this file to replace the innerHTML in the div, because if we replace an image with another of the same filename, the image doesn't change, because the div content does not refresh, it just changes
//$default_z_point_image_filename="plots/z-default-$timestamp.png";

$graph->Stroke($z_point_image_filename); 

$new_z_point_image_filename="plots/z-$i-$timestamp-$randomnumber.png"; // use this file to replace the innerHTML in the div, because if we replace an image with another of the same filename, the image doesn't change, because the div content does not refresh, it just changes
!copy($z_point_image_filename, $new_z_point_image_filename); // copy the image file - so we have a copy with the fixed name, and a copy with a new name (see above comments)
echo " <img src='$new_z_point_image_filename' alt='' border=0 hspace=0 vspace=0>";
//debug: echo "in xyz-make-z-datapoint.php $new_z_point_image_filename ";
?>