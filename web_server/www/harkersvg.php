<?PHP
/**
 * harkersvg.php
 *
 * longdesc
 *
 * LICENSE: This source file is subject to version 4.0 of the Creative Commons
 * license that is available through the world-wide-web at the following URI:
 * https://creativecommons.org/licenses/by/4.0/
 *
 * @category   Geochemistry
 * @package    EarthChem Portal
 * @author     Jason Ash <jasonash@ku.edu>
 * @copyright  IEDA (http://www.iedadata.org/)
 * @license    https://creativecommons.org/licenses/by/4.0/  Creative Commons License 4.0
 * @version    GitHub: $
 * @link       http://ecp.iedadata.org
 * @see        EarthChem, Geochemistry
 */


$items['al2o3']="Al2O3";
$items['cao']="CaO";
$items['feot']="FeO*";
$items['k2o']="K2O";
$items['mgo']="MgO";
$items['na2o']="Na2O";
$items['p2o5']="P2O5";
$items['tio2']="TiO2";

$yaxis=$_GET['yaxis'];

if($yaxis==""){
	echo "error: no y axis provided.";exit();
}

$showaxis=$items[$yaxis];

if($showaxis==""){
	echo "error: incorrect y axis provided.";exit();
}

include "get_pkey.php";

$d=$_GET['d'];

if($d!=""){
	header('Content-type: image/svg+xml');
	header('Content-Disposition: attachment; filename="earthchem_svg_harker_'.$showaxis.'_'.$pkey.'.svg"');
}else{
	header('Content-Type: image/png');
}






include "db.php";

$srcwhere=$db->get_var("select srcwhere from search_query where pkey=$pkey");

include("queries.php");

//echo nl2br($harker_sql);exit();

$rows=$db->get_results($harker_sql);


require_once('svggraph/SVGGraph.php');
 
$settings = array(
  'back_colour'       => '#F9F9F9',    'stroke_colour'      => '#000',
  'back_stroke_width' => 0,         'back_stroke_colour' => '#eee',
  'axis_colour'       => '#333',    'axis_overlap'       => 2,
  'axis_font'         => 'Georgia', 'axis_font_size'     => 10,
  'grid_colour'       => '#CCCCCC',    'label_colour'       => '#000',
  'pad_right'         => 20,        'pad_left'           => 20,
  'marker_colour'     => "red",
  'marker_type'       => "circle",
  'marker_size'       => 3,
  'pad_left'		  => 0,
  'scatter_2d'        => true,
  'label_h'			=> "SiO2",
  'scatter_2d'		=> "true",
  'label_font_size' => 12,
  'graph_title_font_size' => 14,
  'marker_stroke_colour' => "#000000",
);

$values=array();
$links=array();

$minh=99999;
$maxh=-99999;
$minv=99999;
$maxv=-99999;

foreach($rows as $row){

	eval("\$thisval=\$row->$yaxis;");
	$sio2=$row->sio2;
	$sample_pkey=$row->sample_pkey;
	
	$values[]=array($sio2, $thisval);
	$links[]="/v/$sample_pkey";
	
	//get min/max extents
	if($sio2 > $maxh){$maxh=$sio2;}
	if($sio2 < $minh){$minh=$sio2;}
	if($thisval < $minv){$minv=$thisval;}
	if($thisval > $maxv){$maxv=$thisval;}

}

//print_r($links);
//print_r($values);exit();

$minv=$minv-5;
$maxv=$maxv+5;
$minh=$minh-5;
$maxh=$maxh+5;

if($minv < 0){$minv=0;}
if($minh < 0){$minh=0;}


$settings['axis_min_h']=round($minh);
$settings['axis_max_h']=round($maxh);
$settings['axis_min_v']=round($minv);
$settings['axis_max_v']=round($maxv);


$settings['label_v'] = "$showaxis";
$settings['graph_title'] = "$showaxis vs SiO2";

 
$graph = new SVGGraph(300, 300, $settings);
$graph->Links($links);
$graph->Values($values);
$graph->Render('ScatterGraph');

?>