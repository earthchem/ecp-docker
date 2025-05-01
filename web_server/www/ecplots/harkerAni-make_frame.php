<?php

//if ($display_frames) {echo "<p>plot_num $plot_num for minimum age $mins[$plot_num] Ma";}
$rightfooter=$mins[$plot_num]." Ma";
//echo "<p>datax ";print_r($datax); echo "<p>datay ";print_r($datay);
$graph = new Graph($plotsize,$plotsize,"auto");
$graph->img->SetImgFormat('jpeg') or die; // make the output files jpegs
//make a transparent background     $graph->SetColor('white'); // pick any color not in the graph itself   $graph->img->SetTransparent('white'); // must be same color as above
$graph->img->SetAntiAliasing(); 
// If we previously set the min/max for the x and y scales (with multi-plot animations we want the same scales for all plots), set these explicitly, otherwise (for a single plot this is better) jpgraph calculate these (auto)
if (isset($aYMin)) {
$graph->SetScale("linlin",$aYMin,$aYMax,$aXMin,$aXMax);
} else {
$graph->SetScale("linlin","auto","auto","auto","auto"); // x and y scales are linear (not text, int, or log)
}
$graph->img->SetMargin($margin,$margin2,$margin2,$margin); // left, right, top, bottom
$graph->SetColor("#ffffff");
$graph->SetMarginColor("#ffffff");
$graph->xgrid->SetColor($gridcolor);
//$graph->SetBackgroundImage("watermark.png",BGIMG_FILLFRAME); // NAVDAT logo for watermark
$txt = new Text($ytitle);
$txt->SetFont(FF_VERA,FS_NORMAL,$axistitle_fontsize);
$txt->SetColor($subtitlecolor);
$x=0+$axistitle_fontsize;$y=$plotsize/2;
$txt->SetAngle(90); 
$txt->SetPos($x,$y,center,center);
$graph->Add($txt);
// Note: In this version of jpgraph, cannot reuse the $txt obj or we will only see the last one, use unique names. In older version of jpgraph we could reuse the obj.
$txt2 = new Text($xtitle);
$txt2->SetFont(FF_VERA,FS_NORMAL,$axistitle_fontsize);
$txt2->SetColor($subtitlecolor);
$y=$plotsize-$axistitle_fontsize;$x=$plotsize/2;
$txt2->SetAngle(0); 
$txt2->SetPos($x,$y,center,center);
$graph->Add($txt2);
$graph->xgrid->Show();
$graph->xaxis->SetFont(FF_VERA,FS_NORMAL,$ticklabel_fontsize);
$graph->xaxis->SetColor($gridcolor,$tickcolor); 
$graph->yaxis->SetFont(FF_VERA,FS_NORMAL,$ticklabel_fontsize);
$graph->yaxis->SetColor($gridcolor,$tickcolor); 

$samplecount=sizeof($datax); //echo "<h2>samplecount $samplecount</h2>";
if ($samplecount>0) { //echo " MAKING A PLOT ";
$sp = new ScatterPlot($datay,$datax);
if ($pointtype == 'MARK_DIAMOND') {
$sp->mark->SetType(MARK_DIAMOND);
} elseif ($pointtype == 'MARK_UTRIANGLE') {
$sp->mark->SetType(MARK_UTRIANGLE);
} elseif ($pointtype == 'MARK_DTRIANGLE') {
$sp->mark->SetType(MARK_DTRIANGLE);
} elseif ($pointtype == 'MARK_SQUARE') {
$sp->mark->SetType(MARK_SQUARE);
} elseif ($pointtype == 'MARK_CIRCLE') {
$sp->mark->SetType(MARK_CIRCLE);
} elseif ($pointtype == 'MARK_FILLEDCIRCLE') {
$sp->mark->SetType(MARK_FILLEDCIRCLE);
} elseif ($pointtype == 'MARK_CROSS') {
$sp->mark->SetType(MARK_CROSS);
} elseif ($pointtype == 'MARK_STAR') {
$sp->mark->SetType(MARK_STAR);
} elseif ($pointtype == 'MARK_X') {
$sp->mark->SetType(MARK_X);
} elseif ($pointtype == 'MARK_IMG_SBALL') {
$sp->mark->SetType(MARK_IMG_SBALL,'red');
} elseif ($pointtype == 'MARK_IMG_MBALL') {
$sp->mark->SetType(MARK_IMG_MBALL,'red');
} elseif ($pointtype == 'MARK_IMG_LBALL') {
$sp->mark->SetType(MARK_IMG_LBALL,'red');
} elseif ($pointtype == 'MARK_IMG_SQUARE') {
$sp->mark->SetType(MARK_IMG_SQUARE,'red'); //'bluegreen','blue','green', 'lightblue','orange','purple','red','yellow'
} elseif ($pointtype == 'MARK_IMG_STAR') {
$sp->mark->SetType(MARK_IMG_STAR,'blue');
} elseif ($pointtype == 'MARK_IMG_DIAMOND') {
$sp->mark->SetType(MARK_IMG_DIAMOND,'red'); //'lightblue','darkblue','gray', 'blue','pink','purple','red','yellow'
} elseif ($pointtype == 'TARHEEL') {
$sp->mark->SetType(MARK_IMG,'tarheel.png'); 
} elseif ($pointtype == 'BUFFALO') {
$sp->mark->SetType(MARK_IMG,'buffalo.png'); 
} elseif ($pointtype == 'JAYHAWK') {
$sp->mark->SetType(MARK_IMG,'jayhawk.png'); 
} elseif ($pointtype == 'GHAWK') {
$sp->mark->SetType(MARK_IMG,'ghawk.png'); 
} elseif ($pointtype == 'BUFFALO') {
$sp->mark->SetType(MARK_IMG,'buffalo.png'); 
} elseif ($pointtype == 'TARHEEL') {
$sp->mark->SetType(MARK_IMG,'tarheel.png'); 
} else {
$sp->mark->SetType(MARK_DIAMOND);
}
// Specify the callback     $sp->mark->SetCallback("FCallback");
$sp->mark->SetFillColor($fillcolor);
$sp->mark->SetColor($strokecolor);
$sp->mark->SetWidth($pointsize);
$graph->Add($sp);
} // if samplecount>0 (don't make a scatter plot if there is no x,y data)

// add the plot number $graph->footer->right->Set($plot_num);
$graph->footer->right->Set($rightfooter);  // minimum Ma  
$graph->footer->right->SetColor('#000000');
$graph->footer->right->SetFont(FF_VERAMONO,FS_NORMAL,8);
$watermark_pointsize=$plotsize/100*3;
if ($navdat) {
$graph->footer->left->Set('NAVDAT');
} elseif ($earthchem) {
$graph->footer->left->Set('EarthChem');
} else {
$graph->footer->left->Set(''); // should never happen
}
$graph->footer->left->SetColor('#cfcfcf');
$graph->footer->left->SetFont(FF_VERA,FS_BOLD,$watermark_pointsize);
$graph->title->Set($title);
$graph->title->SetFont(FF_VERA,FS_NORMAL,$title_fontsize);
$graph->title->SetColor($titlecolor);
$graph->title->SetMargin($titlemargin);
$graph->SetFrame(false); //$graph->SetFrame(true,'#000000',1); 
$graph->Stroke("$image_filename"); 


 
//$graph->SetBackgroundImage($previous_image_filename,BGIMG_FILLFRAME); // the last plot is the background for this one 
/*
function AdjImage($aBright,$aContr,$aSat)
Adjust brightness and constrast for image

Argument	Default	Description
$aBright 	 	Brightness (-1,1)
$aContr 	0 	Contrast (-1,1)
$aSat 	0 	Saturation (-1,1)
Description
With this method you can adjust both the brightness, contrast as well as color saturation in the image before it is sent to the browser or filedisplayed on the graph.

All values are gives as fraction between 0 and 1 where lower values indicates a "lower" value. The special value 0 leaves the parameter as is.  
 
See also
Graph::AdjBackgroundImage

Example
$graph->AdjImage(0.7,0.3); 
*/
//$graph->AdjBackgroundImage(.5,.5,.5); 
//$graph->SetFrame(true,'#000000',1); 

?>
