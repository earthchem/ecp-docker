<?php
/**
 * Copyright (C) 2010-2013 Graham Breach
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * For more information, please contact <graham@goat1000.com>
 */

require_once 'SVGGraphGridGraph.php';

/**
 * Abstract base class for graphs which use markers
 */
abstract class PointGraph extends GridGraph {

  private $markers = array();
  private $marker_ids = array();
  private $marker_link_ids = array();
  private $marker_used = array();
  private $marker_elements = array();
  private $marker_types = array();

  /**
   * Changes to crosshair cursor by overlaying a transparent rectangle
   */
  protected function CrossHairs()
  {
    $rect = array(
      'width' => $this->width, 'height' => $this->height,
      'opacity' => 0.0, 'cursor' => 'crosshair'
    );
    return $this->Element('rect', $rect);
  }


  /**
   * Adds a marker to the list
   */
  protected function AddMarker($x, $y, $item, $extra = NULL, $set = 0)
  {
    $m = new Marker($x, $y, $item, $extra);
    if(!is_null($item->Data('colour')))
      $m->id = $this->CreateSingleMarker($set,
        $this->GetColour($item, null, true));
    $this->markers[$set][] = $m;
  }

  /**
   * Draws (linked) markers on the graph
   */
  protected function DrawMarkers()
  {
    if($this->marker_size == 0 || count($this->markers) == 0)
      return '';

    $this->CreateMarkers();

    $markers = '';
    foreach($this->markers as $set => $data) {
      if($this->marker_ids[$set] && count($data)){
        $markers .= $this->DrawMarkerSet($set, $data);
        
      }
    }
    foreach(array_keys($this->marker_used) as $id) {
      $this->defs[] = $this->marker_elements[$id];
    }
    
    return $markers;
  }

  /**
   * Draws a single set of markers
   */
  protected function DrawMarkerSet($set, &$marker_data)
  {
    
    $markers = '';
    $markernum=0;
    foreach($marker_data as $m){
      $markers .= $this->GetMarker($m, $set, $markernum);
    	$markernum++;
    }
    return $markers;
  }


  /**
   * Returns a marker element
   */
  private function GetMarker($marker, $set, $markernum)
  {

    
    $id = isset($marker->id) ? $marker->id : $this->marker_ids[$set];
    $use = array(
      'x' => $marker->x,
      'y' => $marker->y,
      'xlink:href' => "#$id"
    );

    if(is_array($marker->extra))
      $use = array_merge($marker->extra, $use);
    if($this->show_tooltips)
      $this->SetTooltip($use, $marker->item, $marker->key, $marker->value);


    if($this->links[0][$markernum]!="") {
    	
      $id = $this->marker_link_ids[$id];
      $use['xlink:href'] = '#' . $id;
      $element = $this->GetLink($marker->item, $marker->key,
        $this->Element('use', $use, null, $this->empty_use ? '' : null),null,$markernum);
    } else {
      $element = $this->Element('use', $use, null, $this->empty_use ? '' : null);
    }


	//print_r($this->links);exit();

    if(!isset($this->marker_used[$id]))
      $this->marker_used[$id] = 1;
    return $element;
  }

  /**
   * Return a centred marker for the given set
   */
  protected function DrawLegendEntry($set, $x, $y, $w, $h)
  {
    if(!array_key_exists($set, $this->marker_ids))
      return '';

    $use = array(
      'x' => $x + $w/2,
      'y' => $y + $h/2,
      'xlink:href' => '#' . $this->marker_ids[$set]
    );
    return $this->Element('use', $use, null, $this->empty_use ? '' : null);
  }

  /**
   * Creates a single marker element and its link version
   */
  private function CreateMarker($type, $size, $fill, $stroke_width,
    $stroke_colour)
  {
    $m_key = "$type:$size:$fill:$stroke_width:$stroke_colour";
    if(isset($this->marker_types[$m_key]))
      return $this->marker_types[$m_key];

    $id = $this->NewID();
    $marker = array('id' => $id, 'cursor' => 'crosshair', 'fill' => $fill);
    if(!empty($stroke_colour) && $stroke_colour != 'none') {
      $marker['stroke'] = $stroke_colour;
      if(!empty($stroke_width))
        $marker['stroke-width'] = $stroke_width;
    }

    $a = $size; // will be repeated a lot, and 'a' is smaller
    $element = 'path';
    switch($type) {
    case 'triangle' :
      $o = $a * tan(M_PI / 6);
      $h = $a / cos(M_PI / 6);
      $marker['d'] = "M$a,$o L0,-$h L-$a,$o z";
      break;
    case 'diamond' :
      $marker['d'] = "M0 -{$a}L$a 0 0 $a -$a 0z";
      break;
    case 'square' :
      $element = 'rect';
      $marker['x'] = $marker['y'] = -$a;
      $marker['width'] = $marker['height'] = $a * 2;
      break;
    case 'x' :
      $marker['transform'] = 'rotate(45)';
      // no break - 'x' is a cross rotated by 45 degrees
    case 'cross' :
      $t = $a / 4;
      $marker['d'] = "M-$a,-$t L-$a,$t -$t,$t -$t,$a " .
        "$t,$a $t,$t $a,$t " .
        "$a,-$t $t,-$t $t,-$a " .
        "-$t,-$a -$t,-$t z";
      break;
    case 'octagon' :
      $t = $a * sin(M_PI / 8);
      $marker['d'] = "M$t -{$a}L$a -$t $a $t $t $a -$t $a " .
        "-$a $t -$a -$t -$t -{$a}z";
      break;
    case 'star' :
      $t = $a * 0.382;
      $x1 = $t * sin(M_PI * 0.8);
      $y1 = $t * cos(M_PI * 0.8);
      $x2 = $a * sin(M_PI * 0.6);
      $y2 = $a * cos(M_PI * 0.6);
      $x3 = $t * sin(M_PI * 0.4);
      $y3 = $t * cos(M_PI * 0.4);
      $x4 = $a * sin(M_PI * 0.2);
      $y4 = $a * cos(M_PI * 0.2);
      $marker['d'] = "M0 -{$a}L$x1 $y1 $x2 $y2 $x3 $y3 $x4 $y4 0 $t " .
        "-$x4 $y4 -$x3 $y3 -$x2 $y2 -$x1 $y1 z";
      break;
    case 'threestar' :
      $t = $a / 4;
      $t1 = $t * cos(M_PI / 6);
      $t2 = $t * sin(M_PI / 6);
      $a1 = $a * cos(M_PI / 6);
      $a2 = $a * sin(M_PI / 6);
      $marker['d'] = "M0 -{$a}L$t1 -$t2 $a1 $a2 0 $t -$a1 $a2 -$t1 -{$t2}z";
      break;
    case 'fourstar' :
      $t = $a / 4;
      $marker['d'] = "M0 -{$a}L$t -$t $a 0 $t $t " .
        "0 $a -$t $t -$a 0 -$t -{$t}z";
      break;
    case 'eightstar' :
      $t = $a * sin(M_PI / 8);
      $marker['d'] = "M0 -{$t}L$t -$a $t -$t $a -$t $t 0 " .
        "$a $t $t $t $t $a 0 $t -$t $a -$t $t -$a $t -$t 0 " .
        "-$a -$t -$t -$t -$t -{$a}z";
      break;
    case 'asterisk' :
      $t = $a / 3;
      $x1 = $a * sin(M_PI * 0.9);
      $y1 = $a * cos(M_PI * 0.9);
      $x2 = $t * sin(M_PI * 0.8);
      $y2 = $t * cos(M_PI * 0.8);
      $x3 = $a * sin(M_PI * 0.7);
      $y3 = $a * cos(M_PI * 0.7);
      $x4 = $a * sin(M_PI * 0.5);
      $y4 = $a * cos(M_PI * 0.5);
      $x5 = $t * sin(M_PI * 0.4);
      $y5 = $t * cos(M_PI * 0.4);
      $x6 = $a * sin(M_PI * 0.3);
      $y6 = $a * cos(M_PI * 0.3);
      $x7 = $a * sin(M_PI * 0.1);
      $y7 = $a * cos(M_PI * 0.1);
      $marker['d'] = "M$x1 {$y1}L$x2 $y2 $x3 $y3 $x4 $y4 $x5 $y5 " .
        "$x6 $y6 $x7 $y7 0 $t -$x7 $y7 -$x6 $y6 -$x5 $y5 -$x4 $y4 " . 
        "-$x3 $y3 -$x2 $y2 -$x1 ${y1}z";
      break;
    case 'pentagon' :
      $x1 = $a * sin(M_PI * 0.4);
      $y1 = $a * cos(M_PI * 0.4);
      $x2 = $a * sin(M_PI * 0.2);
      $y2 = $a * cos(M_PI * 0.2);
      $marker['d'] = "M0 -{$a}L$x1 -$y1 $x2 $y2 -$x2 $y2 -$x1 -{$y1}z";
      break;
    case 'hexagon' :
      $x = $a * sin(M_PI / 3);
      $y = $a * cos(M_PI / 3);
      $marker['d'] = "M0 -{$a}L$x -$y $x $y 0 $a -$x $y -$x -{$y}z";
      break;
    case 'circle' :
    default :
      $element = 'circle';
      $marker['r'] = $size;
    }
    $this->marker_elements[$marker['id']] = 
      $this->Element('symbol', NULL, NULL, 
        $this->Element($element, $marker, NULL));

    // add link version
    unset($marker['cursor']);
    $this->marker_link_ids[$marker['id']] = $this->NewID();
    $marker['id'] = $this->marker_link_ids[$marker['id']];
    $this->marker_elements[$marker['id']] =
      $this->Element('symbol', NULL, NULL,
        $this->Element($element, $marker, NULL));


    // save this marker style for reuse
    $this->marker_types[$m_key] = $id;
    return $id;
  }

  /**
   * Creates a single marker for the data set
   */
  private function CreateSingleMarker($set, $colour = null)
  {
    $type = is_array($this->marker_type) ?
      $this->marker_type[$set % count($this->marker_type)] :
      $this->marker_type;
    $size = is_array($this->marker_size) ?
      $this->marker_size[$set % count($this->marker_size)] :
      $this->marker_size;

    $stroke_width = '';
    $stroke_colour = $this->marker_stroke_colour;
    if(is_array($stroke_colour))
      $stroke_colour = $stroke_colour[$set % count($stroke_colour)];
    if(!empty($stroke_colour) && $stroke_colour != 'none') {
      $stroke_width = $this->marker_stroke_width;
      if(is_array($stroke_width))
        $stroke_width = $stroke_width[$set % count($stroke_width)];
    }

    $mcolour = $this->marker_colour;
    if(!is_null($colour)) {
      $fill = $colour;
    } elseif(!empty($mcolour)) {
      $fill = is_array($mcolour) ?  $mcolour[$set % count($mcolour)] : $mcolour;
    } else {
      $fill = $this->GetColour(null, $set % count($this->colours), true);
    }

    return $this->CreateMarker($type, $size, $fill, $stroke_width, $stroke_colour);
  }

  /**
   * Creates the marker types
   */
  private function CreateMarkers()
  {
    foreach(array_keys($this->markers) as $set) {
      // set the ID for this data set to use
      $this->marker_ids[$set] = $this->CreateSingleMarker($set);
    }
  }

  /**
   * Find the best fit line for the data points
   */
  protected function BestFit($type, $dataset, $colour, $stroke_width, $dash)
  {
    // only straight lines supported for now
    if($type != 'straight')
      return '';

    // use markers for data
    if(!isset($this->markers[$dataset]))
      return '';

    $sum_x = $sum_y = $sum_x2 = $sum_xy = 0;
    $count = 0;
    foreach($this->markers[$dataset] as $k => $v) {
      $x = $v->x - $this->pad_left;
      $y = $this->height - $this->pad_bottom - $v->y;

      $sum_x += $x;
      $sum_y += $y;
      $sum_x2 += pow($x, 2);
      $sum_xy += $x * $y;
      ++$count;
    }

    // can't draw a line through less than 2 points
    if($count < 2)
      return '';
    $mean_x = $sum_x / $count;
    $mean_y = $sum_y / $count;
    $x_max = $this->width - $this->pad_left - $this->pad_right;
    $y_max = $this->height - $this->pad_bottom - $this->pad_top;

    if($sum_x2 == $sum_x * $mean_x) {
      // line is vertical!
      $x1 = $this->GridX($mean_x);
      $x2 = $x1 = $mean_x;
      $y1 = 0;
      $y2 = $y_max;
    } else {
      $slope = ($sum_xy - $sum_x * $mean_y) / ($sum_x2 - $sum_x * $mean_x);
      $y_int = $mean_y - $slope * $mean_x;

      $x1 = 0;
      $y1 = $slope * $x1 + $y_int;
      $x2 = $x_max;
      $y2 = $slope * $x2 + $y_int;
      
      if($y1 < 0) {
        $x1 = -$y_int / $slope;
        $y1 = 0;
      } elseif($y1 > $y_max) {
        $x1 = ($y_max - $y_int) / $slope;
        $y1 = $y_max;
      }

      if($y2 < 0) {
        $x2 = - $y_int / $slope;
        $y2 = 0;
      } elseif($y2 > $y_max) {
        $x2 = ($y_max - $y_int) / $slope;
        $y2 = $y_max;
      }
    }
    $x1 += $this->pad_left;
    $x2 += $this->pad_left;
    $y1 = $this->height - $this->pad_bottom - $y1;
    $y2 = $this->height - $this->pad_bottom - $y2;
    $path = array(
      'd' => "M$x1 {$y1}L$x2 $y2",
      'stroke' => empty($colour) ? '#000' : $colour,
    );
    if($stroke_width != 1 && $stroke_width > 0)
      $path['stroke-width'] = $stroke_width;
    if(!empty($dash))
      $path['stroke-dasharray'] = $dash;

    return $this->Element('path', $path);
  }
}

class Marker {

  public $x, $y, $key, $value, $extra, $item;

  public function __construct($x, $y, &$item, $extra)
  {
    $this->x = $x;
    $this->y = $y;
    $this->key = $item->key;
    $this->value = $item->value;
    $this->extra = $extra;
    $this->item = &$item;
  }
}

