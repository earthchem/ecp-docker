<?php
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

unset($plot_num_layers);
$plot_num_layers=array(); // an array of numbers representing the age range for a layer, where $plot_num is the current age range and smaller numbers are earlier age range layers 
array_push($plot_num_layers,$plot_num-10);
array_push($plot_num_layers,$plot_num-9);
array_push($plot_num_layers,$plot_num-8);
array_push($plot_num_layers,$plot_num-7);
array_push($plot_num_layers,$plot_num-6); 
array_push($plot_num_layers,$plot_num-5);
array_push($plot_num_layers,$plot_num-4);
array_push($plot_num_layers,$plot_num-3);
array_push($plot_num_layers,$plot_num-2);
array_push($plot_num_layers,$plot_num-1);
array_push($plot_num_layers,$plot_num);  

// this method shows the min age of the sample in the age range we are plotting - not the conceptual age of the frame   $n=min($zarray[$plot_num]); if ($n>0) {$rightfooter=number_format($n, 1, '.', '');} // show the minimum age for this plot's main (most recent) layer, rounded to 1 decimal place (per Doug)
$n=$mins[$plot_num]; {$rightfooter=number_format($n, 2, '.', '');} // show the conceptual minimum age of the main group of samples we are plotting in this frame (not the ones fading in/our or remaining - but the new ones that appear in this layer at tiniest size). Note there may not be any samples in this frame, but we show the frame age.
$layer_plot_num=-1; // this will progress from 0 to 10, it represents the layer order of the scatter plot in the current frame
foreach ($plot_num_layers as $plot_num_layer) //for ($age_range_min_index=$plot_num-$latency_multiple;$age_range_min_index<=$plot_num;$age_range_min_index++;) 
{ 
$layer_plot_num++; 
$i=$plot_num_layer; // Note: In this version of jpgraph we cannot reuse a scatter plot object like $sp in the same image, so we use $i to uniquely name them as an array
//echo "<br><font color=orange>layer_plot_num $layer_plot_num plot_num_layer $plot_num_layer</font>";
if ($layer_plot_num>=3 && $layer_plot_num<=7) {$layer_pointsize=$pointsize;} 
elseif ($layer_plot_num==0 || $layer_plot_num==10) {$layer_pointsize=$pointsize-3;}
elseif ($layer_plot_num==1 || $layer_plot_num==9) {$layer_pointsize=$pointsize-2;}
elseif ($layer_plot_num==2 || $layer_plot_num==8) {$layer_pointsize=$pointsize-1;}
else {$layer_pointsize=20;$fillcolor='black';} // this is an error, make the points look wrong to call attention
if ($layer_plot_num<4) {$layer_fillcolor='#cccccc';$layer_strokecolor='#cccccc';} else {$layer_fillcolor=$fillcolor;$layer_strokecolor=$strokecolor;} // show the fadeout in gray, for now
{
$datax=array();$datay=array();
$datax=$xarray[$plot_num_layer];$datay=$yarray[$plot_num_layer];$dataz=$zarray[$plot_num_layer];

/////REDO THIS datax, datay - we have all this in arrays, don't need the above 
//$datax=array();$datay=array();if ($plot_num_layer >= 0) {$datax=$xarray[$plot_num_layer]; $datay=$yarray[$plot_num_layer];}
$samplecount=sizeof($datax); 
//echo "<br><font size=3 color=green>$samplecount samples in this layer</font> ";print_r($datax);
if ($samplecount>0) {
$sp[$i] = new ScatterPlot($datay,$datax);
if ($pointtype == 'MARK_DIAMOND') {
$sp[$i]->mark->SetType(MARK_DIAMOND);
} elseif ($pointtype == 'MARK_UTRIANGLE') {
$sp[$i]->mark->SetType(MARK_UTRIANGLE);
} elseif ($pointtype == 'MARK_DTRIANGLE') {
$sp[$i]->mark->SetType(MARK_DTRIANGLE);
} elseif ($pointtype == 'MARK_SQUARE') {
$sp[$i]->mark->SetType(MARK_SQUARE);
} elseif ($pointtype == 'MARK_CIRCLE') {
$sp[$i]->mark->SetType(MARK_CIRCLE);
} elseif ($pointtype == 'MARK_FILLEDCIRCLE') {
$sp[$i]->mark->SetType(MARK_FILLEDCIRCLE);
} elseif ($pointtype == 'MARK_CROSS') {
$sp[$i]->mark->SetType(MARK_CROSS);
} elseif ($pointtype == 'MARK_STAR') {
$sp[$i]->mark->SetType(MARK_STAR);
} elseif ($pointtype == 'MARK_X') {
$sp[$i]->mark->SetType(MARK_X);
} elseif ($pointtype == 'MARK_IMG_SBALL') {
$sp[$i]->mark->SetType(MARK_IMG_SBALL,'red');
} elseif ($pointtype == 'MARK_IMG_MBALL') {
$sp[$i]->mark->SetType(MARK_IMG_MBALL,'red');
} elseif ($pointtype == 'MARK_IMG_LBALL') {
$sp[$i]->mark->SetType(MARK_IMG_LBALL,'red');
} elseif ($pointtype == 'MARK_IMG_SQUARE') {
$sp[$i]->mark->SetType(MARK_IMG_SQUARE,'red'); 
$sp[$i]->mark->SetType(MARK_IMG_STAR,'blue');
} elseif ($pointtype == 'MARK_IMG_DIAMOND') {
$sp[$i]->mark->SetType(MARK_IMG_DIAMOND,'red'); 
} elseif ($pointtype == 'TARHEEL') {
$sp[$i]->mark->SetType(MARK_IMG,'tarheel.png'); 
} elseif ($pointtype == 'BUFFALO') {
$sp[$i]->mark->SetType(MARK_IMG,'buffalo.png'); 
} elseif ($pointtype == 'JAYHAWK') {
$sp[$i]->mark->SetType(MARK_IMG,'jayhawk.png'); 
} elseif ($pointtype == 'GHAWK') {
$sp[$i]->mark->SetType(MARK_IMG,'ghawk.png'); 
} elseif ($pointtype == 'BUFFALO') {
$sp[$i]->mark->SetType(MARK_IMG,'buffalo.png'); 
} elseif ($pointtype == 'TARHEEL') {
$sp[$i]->mark->SetType(MARK_IMG,'tarheel.png'); 
} else {
$sp[$i]->mark->SetType(MARK_DIAMOND);
}
// Specify the callback     $sp[$i]->mark->SetCallback("FCallback");
$sp[$i]->mark->SetFillColor($layer_fillcolor);
$sp[$i]->mark->SetColor($layer_strokecolor);
$sp[$i]->mark->SetWidth($layer_pointsize);
$graph->Add($sp[$i]);
} // if sizeof datax > 0 (don't make a scatter plot if there is no x,y data)
} // if plot_min > 0
} // for 

$graph->footer->right->Set($rightfooter." Ma");  
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
if ($display_frames) {echo '<br><img src="'.$image_filename.'"><p>'.$image_filename.'<hr>';}
?>
 