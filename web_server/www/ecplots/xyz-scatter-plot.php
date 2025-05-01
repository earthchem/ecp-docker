<?php
$scatterplot[$i]=new ScatterPlot($datay,$datax);
// Uncomment the following line to display the values for the y axis:   $scatterplot[$i]->value->Show();

//$scatterplot[$i]->value->SetFormatCallback('5',5);$scatterplot[$i]->value->Show();
if ($pointtype == 'MARK_DIAMOND') {
$scatterplot[$i]->mark->SetType(MARK_DIAMOND);
} elseif ($pointtype == 'MARK_UTRIANGLE') {
$scatterplot[$i]->mark->SetType(MARK_UTRIANGLE);
} elseif ($pointtype == 'MARK_DTRIANGLE') {
$scatterplot[$i]->mark->SetType(MARK_DTRIANGLE);
} elseif ($pointtype == 'MARK_SQUARE') {
$scatterplot[$i]->mark->SetType(MARK_SQUARE);
} elseif ($pointtype == 'MARK_CIRCLE') {
$scatterplot[$i]->mark->SetType(MARK_CIRCLE);
} elseif ($pointtype == 'MARK_FILLEDCIRCLE') {
$scatterplot[$i]->mark->SetType(MARK_FILLEDCIRCLE);
} elseif ($pointtype == 'MARK_CROSS') {
$scatterplot[$i]->mark->SetType(MARK_CROSS);
} elseif ($pointtype == 'MARK_STAR') {
$scatterplot[$i]->mark->SetType(MARK_STAR);
} elseif ($pointtype == 'MARK_X') {
$scatterplot[$i]->mark->SetType(MARK_X);
} elseif ($pointtype == 'MARK_IMG_SBALL') {
$scatterplot[$i]->mark->SetType(MARK_IMG_SBALL,'red');
} elseif ($pointtype == 'MARK_IMG_MBALL') {
$scatterplot[$i]->mark->SetType(MARK_IMG_MBALL,'red');
} elseif ($pointtype == 'MARK_IMG_LBALL') {
$scatterplot[$i]->mark->SetType(MARK_IMG_LBALL,'red');
} elseif ($pointtype == 'MARK_IMG_SQUARE') {
$scatterplot[$i]->mark->SetType(MARK_IMG_SQUARE,'red'); 
} elseif ($pointtype == 'MARK_IMG_STAR') {
$scatterplot[$i]->mark->SetType(MARK_IMG_STAR,'blue');
} elseif ($pointtype == 'MARK_IMG_DIAMOND') {
$scatterplot[$i]->mark->SetType(MARK_IMG_DIAMOND,'red'); 
} elseif ($pointtype == 'TARHEEL') {
$scatterplot[$i]->mark->SetType(MARK_IMG,'tarheel.png'); 
} elseif ($pointtype == 'BUFFALO') {
$scatterplot[$i]->mark->SetType(MARK_IMG,'buffalo.png'); 
} elseif ($pointtype == 'JAYHAWK') {
$scatterplot[$i]->mark->SetType(MARK_IMG,'jayhawk.png'); 
} elseif ($pointtype == 'GHAWK') {
$scatterplot[$i]->mark->SetType(MARK_IMG,'ghawk.png'); 
} elseif ($pointtype == 'BUFFALO') {
$scatterplot[$i]->mark->SetType(MARK_IMG,'buffalo.png'); 
} elseif ($pointtype == 'TARHEEL') {
$scatterplot[$i]->mark->SetType(MARK_IMG,'tarheel.png'); 
} elseif ($pointtype == 'DOUG') {
$scatterplot[$i]->mark->SetType(MARK_IMG,'doug2.gif'); 
} else {
$scatterplot[$i]->mark->SetType(MARK_DIAMOND);
}

$scatterplot[$i]->mark->SetFillColor($fillcolor);
$scatterplot[$i]->mark->SetColor($strokecolor);
$scatterplot[$i]->mark->SetWidth($pointsize);
// Add a legend for the z intervals 
if (isset($z_intervals_num) && $z_intervals_num>0 && $set_z) {
	$legendtext = "";  
	if ($z_precision!='') {$legendtext .= round($z_interval_min,$z_precision);} else {$legendtext .= $z_interval_min;}
	$legendtext .= "   <=   $ztitle   ";
	if ($i==$z_intervals_num-1) {$legendtext .= "<=  ";} else {$legendtext .= "<    ";}
	if ($z_precision!='') {$legendtext .= round($z_interval_max,$z_precision);} else {$legendtext .= $z_interval_max;}
	$legendtext .= " (".sizeof($datax);
	if (sizeof($datax) == 1) {$legendtext .= " sample)";} else {$legendtext .= " samples)";}
	$scatterplot[$i]->SetLegend ($legendtext); // set the text for the legend, for this layer only
}

if ($links) {$scatterplot[$i]->SetCSIMTargets($target,$alt);} // make an imagemap from the target and alt arrays

$graph->Add($scatterplot[$i]);
?>