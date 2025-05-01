<?php

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

?>