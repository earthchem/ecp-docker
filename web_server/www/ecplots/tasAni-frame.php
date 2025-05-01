<?php
/////debug echo "<hr>in tas_make_plot_with_age_layers2.php<p />plot_num is $plot_num";echo "<p>".$image_filename; echo "<p>mins[$plot_num] = $mins[$plot_num]";
$xtitle = 'SiO2 (wt. percent)'; $ytitle = 'Na2O + K2O (wt. percent)'; $title = 'Total Alkali vs SiO2 Animated by Age';

// define where your TTF fonts are stored and which font to use
DEFINE("TTF_DIR","/usr/src/fonts/");
DEFINE("TTF_FONTFILE","arial.ttf");

$graph = new Graph($sidex,$sidey,"auto"); // start a new graph
$graph->img->SetImgFormat('jpeg') or die; // make the output files jpegs
// We do not offer links to sample data on the animations.   $graph->CheckCSIMCache( "image1",10); // image map cache

$graph->img->SetAntiAliasing(); 

$graph->img->SetMargin($margin,10,$margin,$margin); // left, right, top, bottom
//$graph->SetShadow(); // drop shadow around graph

$ymin = 0; $ymax = 18;$xmin = 36; $xmax = 84; // x and y scale for the TAS is always the same. Important, to prevent autoscale. All the plots in the animation have to have the same scale.
$graph->SetScale('linlin',$ymin,$ymax,$xmin,$xmax);

// Set major * minor tick dist  
$graph->yaxis->scale->ticks->Set(2,1);
$graph->xaxis->scale->ticks->Set(4,2);

$graph->SetColor("#ffffff");
$graph->SetMarginColor("#ffffff");

$graph->SetGridDepth(DEPTH_BACK); // DEPTH_FRONT or DEPTH_BACK; sets whether grid is under or over plot points
$graph->xgrid->SetColor($gridcolor);
$graph->xgrid->Show();

//$graph->SetBackgroundImage("watermark-4x3.png",BGIMG_FILLFRAME); // watermark - I redid this as a footer, below

/* I CHOSE TO MAKE THE AXES TITLES AS ARBITRARY TEXT, BELOW - SAVE THIS FOR REFERENCE
// Add x and y axis titles, using the automatic way
$graph->xaxis->SetTitle($xtitle,'middle');
$graph->xaxis->title->SetFont(FF_VERA,FS_NORMAL,$axistitle_fontsize);
$graph->xaxis->title->SetColor($subtitlecolor);
$graph->xaxis->SetTitleMargin($xtitlemargin);
$graph->yaxis->SetTitle($ytitle,'middle');
$graph->yaxis->title->SetFont(FF_VERA,FS_NORMAL,$axistitle_fontsize);
$graph->yaxis->title->SetColor($subtitlecolor);
$graph->yaxis->SetTitleMargin($ytitlemargin);
*/

$graph->title->Set($title);
$graph->title->SetFont(FF_VERA,FS_NORMAL,$title_fontsize);
$graph->title->SetColor($titlecolor);
$graph->title->SetMargin($titlemargin);

// Now add the x and y axis titles, using the technique of explicitly positioning arbitrary text
$graph->xaxis->SetFont(FF_VERA,FS_NORMAL,$ticklabel_fontsize); // set some fonts and colors for the x, y ticks and axes
$graph->xaxis->SetColor($gridcolor,$tickcolor); 
$graph->yaxis->SetFont(FF_VERA,FS_NORMAL,$ticklabel_fontsize);
$graph->yaxis->SetColor($gridcolor,$tickcolor); 

// Note: In this version of jpgraph, cannot re-use an object in the same image even with layered plots, therefore must use unique names e.g. $txt1
// the y axis title
$txty = new Text($ytitle);
$txty->SetFont(FF_VERA,FS_NORMAL,$axistitle_fontsize);
$txty->SetColor($subtitlecolor);
$x=0+floor($axistitle_fontsize*10/12);$y=$sidey/2;
$txty->SetAngle(90); 
$txty->SetPos($x,$y,center,center);
$graph->Add($txty);
/*////// the x axis title - one way to do it, with arbitrary text 
$txt = new Text($xtitle);
$txt->SetFont(FF_VERA,FS_NORMAL,$axistitle_fontsize);
$txt->SetColor($subtitlecolor);
$y=$sidey-$axistitle_fontsize;$x=$sidex/2;
$txt->SetAngle(0); 
$txt->SetPos($x,$y,center,bottom);
$graph->Add($txt); */

/*////// the NAVDAT watermark - one way to do it, with arbitrary text
$watermark_fontsize=16;
$txt = new Text('NAVDAT');
$txt->SetFont(FF_VERA,FS_BOLD,$watermark_fontsize);
$txt->SetColor('#efefef');
$x=0+floor($axistitle_fontsize*10/12);$y=$sidey-$axistitle_fontsize;
$txt->SetAngle(0); 
$txt->SetPos($x,$y,left,bottom);
$graph->Add($txt);*/

// the watermark utilizing the footer capability
if ($navdat) {$graph->footer->left->Set("NAVDAT");}
elseif ($earthchem) {$graph->footer->left->Set("EARTHCHEM");}
else {$graph->footer->left->Set("");} // error 
$graph->footer->left->SetColor("#efefef");
$watermark_fontsize=floor($plotsize/100) * 3; 
$graph->footer->left->SetFont(FF_VERA,FS_BOLD,$watermark_fontsize);
// make the x axis title using the footer capability
$graph ->footer->center->Set($xtitle);
$graph->footer->center->SetColor($subtitlecolor);
$graph->footer->center->SetFont(FF_VERA,FS_NORMAL,$axistitle_fontsize);

// In the LR corner, show an age counter
$graph ->footer->right->Set(number_format($mins[$plot_num], 1, '.', '')." Ma"); //... or show the number of samples instead: $graph ->footer->right->Set($samplecount." samples");
$graph->footer->right->SetColor($subtitlecolor);
$rightfooter_fontsize=$axistitle_fontsize-2;
$graph->footer->right->SetFont(FF_VERA,FS_NORMAL,$rightfooter_fontsize);








// NOW ADD THE LINES AND ROCK NAMES. THE LINES ARE ACTUALLY MULTIPLE SCATTER PLOTS WITH LINES CONNECTING THE POINTS
// The points for the dividing lines are from this gnuplot TAS graph routine: http://warmada.staff.ugm.ac.id/Graphics/gnuplot/petclass/bas.html
// However, I took out any points that are along a straight line, to speed up the plotting

$linecolor=$rocknamecolor; 

$dot_pointsize=0;$linesize=1;
$datax=array(41,41,52.5); 
$datay=array(0,7,14); 
$lp = new ScatterPlot($datay,$datax); 
$lp->mark->SetType(MARK_SQUARE);
$lp->mark->SetFillColor($linecolor); 
$lp->mark->SetColor($linecolor); 
$lp->mark->SetWidth($dot_pointsize); 
$lp->SetLinkPoints( true,$linecolor,$linesize); // lines connect the points
$graph->Add($lp);

$datax=array(50,52.5,57.6,63,63); 
$datay=array(15.1,14,11.7,7,0); 
$lp2 = new ScatterPlot($datay,$datax); 
$lp2->mark->SetType(MARK_SQUARE);
$lp2->mark->SetFillColor($linecolor); 
$lp2->mark->SetColor($linecolor); 
$lp2->mark->SetWidth($dot_pointsize); 
$lp2->SetLinkPoints( true,$linecolor,$linesize); // lines connect the points
$graph->Add($lp2);
 
$datax=array(76.3,69);  
$datay=array(0,8);  
$lp3 = new ScatterPlot($datay,$datax); 
$lp3->mark->SetType(MARK_SQUARE);
$lp3->mark->SetFillColor($linecolor); 
$lp3->mark->SetColor($linecolor); 
$lp3->mark->SetWidth($dot_pointsize); 
$lp3->SetLinkPoints( true,$linecolor,$linesize); // lines connect the points
$graph->Add($lp3);
 
$datax=array(36,46);
$datay=array(10,10);
$lp4 = new ScatterPlot($datay,$datax); 
$lp4->mark->SetType(MARK_SQUARE);
$lp4->mark->SetFillColor($linecolor); 
$lp4->mark->SetColor($linecolor); 
$lp4->mark->SetWidth($dot_pointsize); 
$lp4->SetLinkPoints( true,$linecolor,$linesize); // lines connect the points
$graph->Add($lp4);

$datax=array(36,41);
$datay=array(7,7);
$lp5 = new ScatterPlot($datay,$datax); 
$lp5->mark->SetType(MARK_SQUARE);
$lp5->mark->SetFillColor($linecolor); 
$lp5->mark->SetColor($linecolor); 
$lp5->mark->SetWidth($dot_pointsize); 
$lp5->SetLinkPoints( true,$linecolor,$linesize); // lines connect the points
$graph->Add($lp5);

$datax=array(36,45);   
$datay=array(3,3);  
$lp6 = new ScatterPlot($datay,$datax); 
$lp6->mark->SetType(MARK_SQUARE);
$lp6->mark->SetFillColor($linecolor); 
$lp6->mark->SetColor($linecolor); 
$lp6->mark->SetWidth($dot_pointsize); 
$lp6->SetLinkPoints( true,$linecolor,$linesize); // lines connect the points
$graph->Add($lp6);

$datax=array(45,45,45,49.4,53,57.6,61);
$datay=array(0,3,5,7.3,9.3,11.7,13.5);
$lp7 = new ScatterPlot($datay,$datax); 
$lp7->mark->SetType(MARK_SQUARE);
$lp7->mark->SetFillColor($linecolor); 
$lp7->mark->SetColor($linecolor); 
$lp7->mark->SetWidth($dot_pointsize); 
$lp7->SetLinkPoints( true,$linecolor,$linesize); // lines connect the points
$graph->Add($lp7);

$datax=array(45,52,69,69);
$datay=array(5,5,8,13);
$lp8 = new ScatterPlot($datay,$datax); 
$lp8->mark->SetType(MARK_SQUARE);
$lp8->mark->SetFillColor($linecolor); 
$lp8->mark->SetColor($linecolor); 
$lp8->mark->SetWidth($dot_pointsize); 
$lp8->SetLinkPoints( true,$linecolor,$linesize); // lines connect the points
$graph->Add($lp8);
 
$datax=array(45,49.4,52,52);
$datay=array(9.4,7.3,5,0);
$lp9 = new ScatterPlot($datay,$datax); 
$lp9->mark->SetType(MARK_SQUARE);
$lp9->mark->SetFillColor($linecolor); 
$lp9->mark->SetColor($linecolor); 
$lp9->mark->SetWidth($dot_pointsize); 
$lp9->SetLinkPoints( true,$linecolor,$linesize); // lines connect the points
$graph->Add($lp9);

$datax=array(48.4,53,57,57);
$datay=array(11.5,9.3,5.9,0);
$lp10 = new ScatterPlot($datay,$datax); 
$lp10->mark->SetType(MARK_SQUARE);
$lp10->mark->SetFillColor($linecolor); 
$lp10->mark->SetColor($linecolor); 
$lp10->mark->SetWidth($dot_pointsize); 
$lp10->SetLinkPoints( true,$linecolor,$linesize); // lines connect the points
$graph->Add($lp10);

/* Draw the arrows for Foldite - USED A FIELD PLOT INSTEAD, KEEP THIS CODE FOR REFERENCE
$linecolor='blue';$linecolor='blue';$pointsize=5;
$datax=array(38.7,38.7);
$datay=array(7.9,2.5);
$lp2 = new ScatterPlot($datay,$datax); 
$lp2->mark->SetType(MARK_DIAMOND);
$lp2->mark->SetFillColor($linecolor); 
$lp2->mark->SetColor($linecolor); 
$lp2->mark->SetWidth($pointsize); 
$lp2->SetLinkPoints( true,$linecolor,$linesize); // lines connect the points
$graph->Add($lp2);
*/



// Now add the rock names 

//Foidite
///// This would place an image, if I were using images for rock names. I'm leaving these commented-in because the x,y values are worked out - in case we use images at a later date.     $datax=array(39.25);$datay=array(8.5);$lp2 = new ScatterPlot($datay,$datax); $lp2->mark->SetType(MARK_IMG,'spacer.png'); $graph->Add($lp2);
$txt1 = new Text("Foidite");
$txt1->SetColor($rocknamecolor);
$txt1->SetFont(FF_VERA,FS_NORMAL,$rockname_fontsize);
$txt1->SetScalePos(37.25,8.75); 
$graph->AddText($txt1);

//Phonolite
/////$datax=array(57);$datay=array(14.5);$lp2 = new ScatterPlot($datay,$datax); $lp2->mark->SetType(MARK_IMG,'spacer.png'); $graph->Add($lp2);
$txt2 = new Text("Phonolite");
$txt2->SetColor($rocknamecolor);
$txt2->SetFont(FF_VERA,FS_NORMAL,$rockname_fontsize);
$txt2->SetScalePos(54.70,15); 
$graph->AddText($txt2);

//Tephriphonolite
/////$datax=array(53);$datay=array(11.85);$lp2 = new ScatterPlot($datay,$datax); $lp2->mark->SetType(MARK_IMG,'spacer.png'); $graph->Add($lp2);
$txt3 = new Text("Tephriphonolite");
$txt3->SetColor($rocknamecolor);
$txt3->SetFont(FF_VERA,FS_NORMAL,$rockname_fontsize);
$txt3->SetScalePos(49,11.75); 
$graph->AddText($txt3);

//Phonotephrite
/////$datax=array(49);$datay=array(9.75);$lp2 = new ScatterPlot($datay,$datax); $lp2->mark->SetType(MARK_IMG,'spacer.png'); $graph->Add($lp2);
$txt4 = new Text("Phonotephrite");
$txt4->SetColor($rocknamecolor);
$txt4->SetFont(FF_VERA,FS_NORMAL,$rockname_fontsize);
$txt4->SetScalePos(45.75,9.6); 
$graph->AddText($txt4);

//Tephrite
/////$datax=array(44.5);$datay=array(6.5);$lp2 = new ScatterPlot($datay,$datax); $lp2->mark->SetType(MARK_IMG,'spacer.png'); $graph->Add($lp2);
$txt5 = new Text("Tephrite");
$txt5->SetColor($rocknamecolor);
$txt5->SetFont(FF_VERA,FS_NORMAL,$rockname_fontsize);
$txt5->SetScalePos(42,6.75); 
$graph->AddText($txt5);

//Basanite
//$datax=array(43.5);$datay=array(5.5);$lp2 = new ScatterPlot($datay,$datax); $lp2->mark->SetType(MARK_IMG,'spacer.png'); $graph->Add($lp2);
$txt6 = new Text("Basanite");
$txt6->SetColor($rocknamecolor);
$txt6->SetFont(FF_VERA,FS_NORMAL,$rockname_fontsize);
$txt6->SetScalePos(41.5,5.75); 
$graph->AddText($txt6);

//Picrobasalt
//$datax=array(43);$datay=array(2.5);$lp2 = new ScatterPlot($datay,$datax); $lp2->mark->SetType(MARK_IMG,'spacer.png'); $graph->Add($lp2);
$txt7 = new Text("Picro-
basalt");
$txt7->SetColor($rocknamecolor);
$txt7->SetFont(FF_VERA,FS_NORMAL,$rockname_fontsize);
$txt7->SetScalePos(41.5,2.75); 
$graph->AddText($txt7);

//Trachyte
//$datax=array(64.75);$datay=array(12.5);$lp2 = new ScatterPlot($datay,$datax); $lp2->mark->SetType(MARK_IMG,'spacer.png'); $graph->Add($lp2);
$txt8 = new Text("Trachyte");
$txt8->SetColor($rocknamecolor);
$txt8->SetFont(FF_VERA,FS_NORMAL,$rockname_fontsize);
$txt8->SetScalePos(62.5,12.75); 
$graph->AddText($txt8);

//Trachydacite
//$datax=array(64.75);$datay=array(9.25);$lp2 = new ScatterPlot($datay,$datax); $lp2->mark->SetType(MARK_IMG,'spacer.png'); $graph->Add($lp2);
$txt9 = new Text("Trachydacite");
$txt9->SetColor($rocknamecolor);
$txt9->SetFont(FF_VERA,FS_NORMAL,$rockname_fontsize);
$txt9->SetScalePos(62.5,9.5); 
$graph->AddText($txt9);

//Trachyandesite
/////$datax=array(57.5);$datay=array(9);$lp2 = new ScatterPlot($datay,$datax); $lp2->mark->SetType(MARK_IMG,'spacer.png'); $graph->Add($lp2);
$txt10 = new Text("Trachyandesite");
$txt10->SetColor($rocknamecolor);
$txt10->SetFont(FF_VERA,FS_NORMAL,$rockname_fontsize);
$txt10->SetScalePos(53.5,9.5); 
$graph->AddText($txt10);

//Trachybasalt
/////$datax=array(49);$datay=array(5.9);$lp2 = new ScatterPlot($datay,$datax); $lp2->mark->SetType(MARK_IMG,'spacer.png'); $graph->Add($lp2);
$txt11 = new Text("Trachybasalt");
$txt11->SetColor($rocknamecolor);
$txt11->SetFont(FF_VERA,FS_NORMAL,$rockname_fontsize);
$txt11->SetScalePos(46.1,5.5); 
$graph->AddText($txt11);

//Basaltic Trachyandesite
/////$datax=array(53);$datay=array(7.5);$lp2 = new ScatterPlot($datay,$datax); $lp2->mark->SetType(MARK_IMG,'spacer.png'); $graph->Add($lp2);
$txt12 = new Text("Basaltic 
Trachy-
andesite");
$txt12->SetColor($rocknamecolor);
$txt12->SetFont(FF_VERA,FS_NORMAL,$rockname_fontsize);
$txt12->SetScalePos(51,7.75); 
$graph->AddText($txt12);

//Basalt
$datax=array(48.5);
$datay=array(3.5);
$lp99 = new ScatterPlot($datay,$datax); 
$lp99->mark->SetType(MARK_IMG,'spacer.png'); 
$graph->Add($lp99);
///////////////////
$txt13 = new Text("Basalt");
$txt13->SetColor($rocknamecolor);
$txt13->SetFont(FF_VERA,FS_NORMAL,$rockname_fontsize);
$txt13->SetScalePos(47.5,3.75); 
$graph->AddText($txt13);

//Rhyolite
/////$datax=array(73);$datay=array(8);$lp2 = new ScatterPlot($datay,$datax); $lp2->mark->SetType(MARK_IMG,'spacer.png'); $graph->Add($lp2);
$txt14 = new Text("Rhyolite");
$txt14->SetColor($rocknamecolor);
$txt14->SetFont(FF_VERA,FS_NORMAL,$rockname_fontsize);
$txt14->SetScalePos(72.,8.25); 
$graph->AddText($txt14);

//Dacite
/////$datax=array(67.75);$datay=array(3.5);$lp2 = new ScatterPlot($datay,$datax); $lp2->mark->SetType(MARK_IMG,'spacer.png'); $graph->Add($lp2);
$txt15 = new Text("Dacite");
$txt15->SetColor($rocknamecolor);
$txt15->SetFont(FF_VERA,FS_NORMAL,$rockname_fontsize);
$txt15->SetScalePos(65.75,3.75); 
$graph->AddText($txt15);

//Andesite
/////$datax=array(60.125);$datay=array(3.5);$lp2 = new ScatterPlot($datay,$datax); $lp2->mark->SetType(MARK_IMG,'spacer.png'); $graph->Add($lp2);
$txt16 = new Text("Andesite");
$txt16->SetColor($rocknamecolor);
$txt16->SetFont(FF_VERA,FS_NORMAL,$rockname_fontsize);
$txt16->SetScalePos(58,3.75); 
$graph->AddText($txt16);

//Basaltic Andesite
/////$datax=array(54.5);$datay=array(3.5);$lp2 = new ScatterPlot($datay,$datax); $lp2->mark->SetType(MARK_IMG,'spacer.png'); $graph->Add($lp2);
$txt17 = new Text("Basaltic 
Andesite");
$txt17->SetColor($rocknamecolor);
$txt17->SetFont(FF_VERA,FS_NORMAL,$rockname_fontsize);
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

/* experiment with text placement
$txt=new Text("text");
$txt->SetPos($margin,$margin2); // position by exact x,y coordinate on the whole image
$txt->SetColor( "red");
$graph->AddText( $txt);
$txt2 = new Text("80,15");
$txt2->SetScalePos(80,15); // position by x,y scale on the plot
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



// use this if you are caching an image map file $graph->StrokeCSIM();  We do not offer links to sample data on the animations.

// imagemap code from tas.cfm: 
//<area shape="circle" coords="#SIO2Location#,#AlkaliLocation#,8" href="../NavdatPortal/GetSample.cfm?sample_num=#CurrentSampleNum#">


// Now send the image to the browser without saving it on the server - or generate an image on the server, both of the next 2 statements work
//$graph->Stroke(''); // generate the image, send it to the browser without saving it on the server - but we cannot have an image map if we do this


 




// Now make the scatter plots to layer in this image (frame). 


if ($latency_method==2) { // datapoint grow over 4 frames, persist for 4 more frames, change to gray and shrink for 3 frames
//unset($plot_num_layers);
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

// one way of doing the age counter in the rightfooter: show the min age of the samples that newly appear in that frame    $n=min($zarray[$plot_num]); if ($n>0) {$rightfooter=number_format($n, 1, '.', '');} else {$rightfooter="";} // show the minimum age for this plot's main (most recent) layer, rounded to 1 decimal place (per Doug)

$layer_plot_num=-1; // this will progress from 0 to 10, it represents the layer order of the scatter plot in the current frame
foreach ($plot_num_layers as $plot_num_layer) //for ($age_range_min_index=$plot_num-$latency_multiple;$age_range_min_index<=$plot_num;$age_range_min_index++;) 
{ 
$layer_plot_num++; 
//echo "<br><font color='magenta'>layer_plot_num $layer_plot_num plot_num_layer $plot_num_layer</font>";
if ($layer_plot_num>=3 && $layer_plot_num<=7) {$layer_pointsize=$pointsize;} 
elseif ($layer_plot_num==0 || $layer_plot_num==10) {$layer_pointsize=$pointsize-3;}
elseif ($layer_plot_num==1 || $layer_plot_num==9) {$layer_pointsize=$pointsize-2;}
elseif ($layer_plot_num==2 || $layer_plot_num==8) {$layer_pointsize=$pointsize-1;}
else {$layer_pointsize=20;$fillcolor='black';} // this is an error, make the points look wrong to call attention
//echo " ($pointsize) layer_pointsize $layer_pointsize ";
// Show the shrinking points in a different color. This is different depending on whether our age direction is youngest-to-oldest or v.v.
if ($layer_plot_num<4) {
	$layer_fillcolor='#cccccc';$layer_strokecolor='#cccccc';
} else {
	$layer_fillcolor=$fillcolor;$layer_strokecolor=$strokecolor;
} 
//echo "<h1>layer $plot_num_layer</h1>";
$datax=array();$datay=array();
$datax=$xarray[$plot_num_layer];
if (sizeof($datax)>0) {
$datay=$yarray[$plot_num_layer];$dataz=$zarray[$plot_num_layer]; 
if (1==3) { // debug
	echo "<br>";foreach($datax as $key=>$val) {
	echo "$key: x $val y $datay[$key] ($dataz[$key]) &bull; "; //if (is_numeric($val)) {} else { echo " IS NOT NUMERIC "; }
}
foreach($datay as $key=>$val) {
	//debug echo "<br>y $key $val "; if (is_numeric($val)) {} else { echo " IS NOT NUMERIC "; }
}
} // if (debug)
$i=$plot_num_layer;
//echo "<p><font color='".$layer_fillcolor."'>sp[$i] New Scatter Plot Layer, pointtype $pointtype, layer_plot_num $layer_plot_num, layer_fillcolor $layer_fillcolor, layer_pointsize $layer_pointsize, x = ";print_r($datax);echo "</font>";
$sp[$i] = new ScatterPlot($datay,$datax);
if ($pointtype == 'MARK_DIAMOND') {
$sp[$i]->mark->SetType(MARK_DIAMOND); // Why does not a variable work in here for MARK_DIAMOND, so we could avoid this if construct?
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
} elseif ($pointtype == 'MARK_FLAG1') {
$sp[$i]->mark->SetType(MARK_FLAG1,'United Nations');
} elseif ($pointtype == 'MARK_IMG_SBALL') {
$sp[$i]->mark->SetType(MARK_IMG_SBALL,'red');
} elseif ($pointtype == 'MARK_IMG_MBALL') {
$sp[$i]->mark->SetType(MARK_IMG_MBALL,'red');
} elseif ($pointtype == 'MARK_IMG_LBALL') {
$sp[$i]->mark->SetType(MARK_IMG_LBALL,'red');
} elseif ($pointtype == 'MARK_IMG_SQUARE') {
$sp[$i]->mark->SetType(MARK_IMG_SQUARE,'red'); 
} elseif ($pointtype == 'MARK_IMG_STAR') {
$sp[$i]->mark->SetType(MARK_IMG_STAR,'blue');
} elseif ($pointtype == 'MARK_IMG_DIAMOND') {
$sp[$i]->mark->SetType(MARK_IMG_DIAMOND,'red');  
} elseif ($pointtype == 'MARK_IMG_BEVEL') {
$sp[$i]->mark->SetType(MARK_IMG_BEVEL,'red');  
} elseif ($pointtype == 'JAYHAWK') {
$sp[$i]->mark->SetType(MARK_IMG,'jayhawk.png'); 
} elseif ($pointtype == 'GHAWK') {
$sp[$i]->mark->SetType(MARK_IMG,'ghawk.png'); 
} elseif ($pointtype == 'TARHEEL') {
$sp[$i]->mark->SetType(MARK_IMG,'tarheel.png'); 
} elseif ($pointtype == 'BUFFALO') {
$sp[$i]->mark->SetType(MARK_IMG,'buffalo.png'); 
} else {
$sp[$i]->mark->SetType(MARK_DIAMOND);
}

$sp[$i]->mark->SetFillColor($layer_fillcolor); 
$sp[$i]->mark->SetColor($layer_strokecolor); 
$sp[$i]->mark->SetWidth($layer_pointsize);  
$graph->Add($sp[$i]);
} // if sizeof($datax)>0 (there are samples to plot in this scatter plot layer 
} // foreach plot_num_layer

} // if latency_method == 2

elseif ($latency_method == 1) { // datapoints appear at full size, persist for a total of 4 frames, then disappear
	//$i=$plot_num;
	$datax=array();$datay=array();
	for ($j=0;$j<$persist_frames;$j++) { 
		$i=$plot_num-$j; //echo "<br>plot_num $plot_num i = $i ";
		if (isset($xarray[$i])) { //echo "<br>";print_r($xarray[$i]);
			$datax=array_merge($datax,$xarray[$i]); //$xarray[$i];
			$datay=array_merge($datay,$yarray[$i]); //$yarray[$i]; 
		}
	}
/*
if ($persist_frames<1) {$persist_frames=1;}
$n=sizeof($xarray); echo "<h1>$n frames &bull; persist_frames: $persist_frames </h1>";
for ($i=0;$i<$n;$i++) {  echo "<br>frame i $i "; echo " count: "; echo sizeof($xarray[$i]);
	$datax=$xarray[$i]; //$xarray[$i];
	$datay=$yarray[$i]; //$yarray[$i]; 
	
	for ($j=0;$j<$persist_frames;$j++) { echo " j $j ";
		$k=$i-$j; echo " k $k ";
		if (isset($xarray[$k])) { 
			$datax=array_merge($datax,$xarray[$k]); 
			$datay=array_merge($datay,$yarray[$k]);
			echo " --- merging in division $k ";
		} // if
	} // for 
	
	echo " number of samples: "; echo sizeof($datax); 
	if (sizeof($datax)>0) { 
		$sp[$i] = new ScatterPlot($datay,$datax); 
		$sp[$i]->mark->SetType(MARK_SQUARE);
		$sp[$i]->mark->SetFillColor($fillcolor); 
		$sp[$i]->mark->SetColor($strokecolor); 
		$sp[$i]->mark->SetWidth($pointsize); 
		$graph->Add($sp[$i]); 
	} // if 
} // for $i 
*/
	if (sizeof($datax)>0) { 
		$sp[$i] = new ScatterPlot($datay,$datax); 
		$sp[$i]->mark->SetType(MARK_SQUARE);
		$sp[$i]->mark->SetFillColor($fillcolor); 
		$sp[$i]->mark->SetColor($strokecolor); 
		$sp[$i]->mark->SetWidth($pointsize); 
		$graph->Add($sp[$i]); 
	} // if 
} // if latency_method==1




// Make the plot image
$graph->Stroke("$image_filename"); 



?>