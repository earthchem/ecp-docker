<?php
/**
 * Copyright (C) 2009-2013 Graham Breach
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

require_once 'SVGGraphAxis.php';
require_once 'SVGGraphAxisFixed.php';
require_once 'SVGGraphAxisLog.php';

define("SVGG_GUIDELINE_ABOVE", 1);
define("SVGG_GUIDELINE_BELOW", 0);

abstract class GridGraph extends Graph {

  protected $bar_unit_width = 0;
  protected $x_axis;
  protected $y_axis;
  protected $y_points;
  protected $x_points;
  protected $x_subdivs;
  protected $y_subdivs;

  /**
   * Set to true for horizontal graphs
   */
  protected $flip_axes = false;

  /**
   *  Set to true for block-based labelling
   */
  protected $label_centre = false;

  protected $g_width = null;
  protected $g_height = null;
  protected $uneven_x = false;
  protected $uneven_y = false;
  protected $label_adjust_done = false;
  protected $axes_calc_done = false;
  protected $guidelines;
  protected $min_guide = array('x' => null, 'y' => null);
  protected $max_guide = array('x' => null, 'y' => null);

  private $label_left_offset;
  private $label_bottom_offset;
  private $grid_limit;

  /**
   * Modifies the graph padding to allow room for labels
   */
  protected function LabelAdjustment()
  {
    // deprecated options need converting
    // NOTE: this works because graph settings become properties, whereas
    // defaults only exist in the $this->settings array
    if(isset($this->show_label_h) && !isset($this->show_axis_text_h))
      $this->show_axis_text_h = $this->show_label_h;
    if(isset($this->show_label_v) && !isset($this->show_axis_text_v))
      $this->show_axis_text_v = $this->show_label_v;

    // if the label_x or label_y are set but not _h and _v, assign them
    $lh = $this->flip_axes ? $this->label_y : $this->label_x;
    $lv = $this->flip_axes ? $this->label_x : $this->label_y;
    if(empty($this->label_h) && !empty($lh))
      $this->label_h = $lh;
    if(empty($this->label_v) && !empty($lv))
      $this->label_v = $lv;

    if(!empty($this->label_v)) {
      // increase padding
      $lines = $this->CountLines($this->label_v);
      $this->label_left_offset = $this->pad_left + $this->label_space +
        $this->label_font_size;
      $this->pad_left += $lines * $this->label_font_size +
        2 * $this->label_space;
    }
    if(!empty($this->label_h)) {
      $lines = $this->CountLines($this->label_h);
      $this->label_bottom_offset = $this->pad_bottom + $this->label_space +
        $this->label_font_size * ($lines - 1);
      $this->pad_bottom += $lines * $this->label_font_size +
        2 * $this->label_space;
    }
    $pad_l = $pad_r = $pad_b = $pad_t = 0;
    $space_x = $this->width - $this->pad_left - $this->pad_right;
    $space_y = $this->height - $this->pad_top - $this->pad_bottom;
    if($this->show_axes) {

      $div_size = $this->DivisionOverlap();

      if($this->show_axis_text_v || $this->show_axis_text_h) {
        $extra_r = $extra_t = 0;

        for($i = 0; $i < 10; ++$i) {
          // find the text bounding box and add overlap to padding
          // repeat with the new measurements in case overlap increases
          $x_len = $space_x - $pad_r - $pad_l;
          $y_len = $space_y - $pad_t - $pad_b;

          // 3D graphs will use this to reduce axis length
          list($extra_r, $extra_t) = $this->AdjustAxes($x_len, $y_len);

          $bbox = $this->FindAxisTextBBox($x_len, $y_len);
          $pr = $pl = $pb = $pt = 0;

          if($bbox['max_x'] > $x_len)
            $pr = ceil($bbox['max_x'] - $x_len);
          if($bbox['min_x'] < 0)
            $pl = ceil(abs($bbox['min_x']));
          if($bbox['min_y'] < 0)
            $pt = ceil(abs($bbox['min_y']));
          if($bbox['max_y'] > $y_len)
            $pb = ceil($bbox['max_y'] - $y_len);

          if($pr == $pad_r && $pl == $pad_l && $pt == $pad_t && $pb == $pad_b)
            break;

          $pad_r = $pr;
          $pad_l = $pl;
          $pad_t = $pt;
          $pad_b = $pb;
        }

        $pad_l = max($pad_l, $div_size['x']);
        $pad_b = max($pad_b, $div_size['y']);
        $pad_r += $extra_r;
        $pad_t += $extra_t;
      } else {

        // make space for divisions
        $pad_b = $div_size['x'];
        $pad_l = $div_size['y'];
      }
    } else {
      // 3D graphs will use this to reduce axis length
      list($pad_r, $pad_t) = $this->AdjustAxes($space_x, $space_y);
    }
    // apply the extra padding
    $this->pad_left += $pad_l;
    $this->pad_bottom += $pad_b;
    $this->pad_right += $pad_r;
    $this->pad_top += $pad_t;
    $this->label_adjust_done = true;
  }

  /**
   * Subclasses can override this to modify axis lengths
   * Return amount of padding added [r,t]
   */
  protected function AdjustAxes(&$x_len, &$y_len)
  {
    return array(0, 0);
  }

  /**
   * Find the bounding box of the axis text for given axis lengths
   */
  protected function FindAxisTextBBox($length_x, $length_y)
  {
    $ends = $this->GetAxisEnds();
    list($x_axis, $y_axis) = $this->GetAxes($ends, $length_x, $length_y);

    $min_space_h = $this->GetFirst($this->minimum_grid_spacing_h,
      $this->minimum_grid_spacing);
    $min_space_v = $this->GetFirst($this->minimum_grid_spacing_v,
      $this->minimum_grid_spacing);
    if($this->label_centre) {
      if($this->flip_axes)
        $y_axis->Bar();
      else
        $x_axis->Bar();
    }

    $y_points = $y_axis->GetGridPoints($min_space_v, 0);
    $x_points = $x_axis->GetGridPoints($min_space_h, 0);

    // initialise maxima and minima
    $min_x = $this->width;
    $min_y = $this->height;
    $max_x = $max_y = 0;

    $l = array($length_x, $length_y);

    $x_offset = $y_offset = 0;
    if($this->label_centre) {
      if($this->flip_axes)
        $y_offset = -0.5 * $y_axis->Unit();
      else
        $x_offset = 0.5 * $x_axis->Unit();
    }

    // need actual text positions
    $div_size = $this->DivisionOverlap();
    $inside_x = ('inside' == $this->GetFirst($this->axis_text_position_h,
      $this->axis_text_position));
    $inside_y = ('inside' == $this->GetFirst($this->axis_text_position_v,
      $this->axis_text_position));
    $x_positions = $this->XAxisTextPositions($x_points, $x_offset,
      $div_size['y'], $this->axis_text_angle_h, $inside_x);
    $y_positions = $this->YAxisTextPositions($y_points, $div_size['x'],
      $y_offset, $this->axis_text_angle_v, $inside_y);
    $font_size = $this->axis_font_size;

    // use the division overlap as starting positions
    $min_x = -$div_size['y'];
    $max_y = $length_y + $div_size['x'];

    foreach($x_positions as $p) {
      switch($p['text-anchor']) {
      case 'middle' : $off_x = $p['w'] / 2; break;
      case 'end' : $off_x = $p['w']; break;
      default : $off_x = 0;
      }
      $x = $p['x'] - $off_x;
      $y = $p['y'] - $font_size + $length_y;
      $xw = $x + $p['w'];
      $yh = $y + $p['h'];

      if($x < $min_x)
        $min_x = $x;
      if($xw > $max_x)
        $max_x = $xw;
      if($y < $min_y)
        $min_y = $y;
      if($yh > $max_y)
        $max_y = $yh;
    }
    foreach($y_positions as $p) {
      $off_x = ($p['text-anchor'] == 'end') ? $p['w'] : 0;
      $x = $p['x'] - $off_x;
      $y = $p['y'] - $font_size + $length_y;
      $xw = $x + $p['w'];
      $yh = $y + $p['h'];

      if($x < $min_x)
        $min_x = $x;
      if($xw > $max_x)
        $max_x = $xw;
      if($y < $min_y)
        $min_y = $y;
      if($yh > $max_y)
        $max_y = $yh;
    }

    return compact('min_x', 'min_y', 'max_x', 'max_y');
  }

  /**
   * Returns the amount of overlap the divisions and subdivisions use
   */
  protected function DivisionOverlap()
  {
    if(!$this->show_divisions && !$this->show_subdivisions)
      return array('x' => 0, 'y' => 0);

    $dx = $this->DOverlap(
      $this->GetFirst($this->division_style_h, $this->division_style),
      $this->GetFirst($this->division_size_h, $this->division_size));
    $dy = $this->DOverlap(
      $this->GetFirst($this->division_style_v, $this->division_style),
      $this->GetFirst($this->division_size_v, $this->division_size));
    $sx = $this->DOverlap(
      $this->GetFirst($this->subdivision_style_h, $this->subdivision_style),
      $this->GetFirst($this->subdivision_size_h, $this->subdivision_size));
    $sy = $this->DOverlap(
      $this->GetFirst($this->subdivision_style_v, $this->subdivision_style),
      $this->GetFirst($this->subdivision_size_v, $this->subdivision_size));
    $x = max($dx, $sx);
    $y = max($dy, $sy);

    return array('x' => $x, 'y' => $y);
  }

  /**
   * Calculates the overlap of a division or subdivision
   */
  protected function DOverlap($style, $size)
  {
    $overlap = 0;
    switch($style) {
    case 'in' :
    case 'infull' :
    case 'none' :
      return 0;
    case 'out' :
    case 'over' :
    case 'overfull' :
    default :
      return $size;
    }
  }

  /**
   * Sets up grid width and height to fill padded area
   */
  protected function SetGridDimensions()
  {
    $this->g_height = $this->height - $this->pad_top - $this->pad_bottom;
    $this->g_width = $this->width - $this->pad_left - $this->pad_right;
  }

  /**
   * Returns an array containing the value and key axis min and max
   */
  protected function GetAxisEnds()
  {
    // check guides
    if(is_null($this->guidelines))
      $this->CalcGuidelines();
    $minv_list = array($this->GetMinValue());
    $maxv_list = array($this->GetMaxValue());
    if(!is_null($this->min_guide['y']))
      $minv_list[] = (float)$this->min_guide['y'];
    if(!is_null($this->max_guide['y']))
      $maxv_list[] = (float)$this->max_guide['y'];
    if(!$this->log_axis_y) {
      $minv_list[] = 0;
      $maxv_list[] = 0;
    }
    $v_max = max($maxv_list);
    $v_min = min($minv_list);
    $k_max = max(0, $this->GetMaxKey(), (float)$this->max_guide['x']);
    $k_min = min(0, $this->GetMinKey(), (float)$this->min_guide['x']);

    // validate axes
    if((is_numeric($this->axis_max_h) && is_numeric($this->axis_min_h) &&
      $this->axis_max_h <= $this->axis_min_h) ||
      (is_numeric($this->axis_max_v) && is_numeric($this->axis_min_v) &&
      $this->axis_max_v <= $this->axis_min_v))
        throw new Exception('Invalid axes specified');
    if((is_numeric($this->axis_max_h) &&
      ($this->axis_max_h < ($this->flip_axes ? $v_min : $k_min))) ||
      (is_numeric($this->axis_min_h) &&
      ($this->axis_min_h >= ($this->flip_axes ? $v_max : $k_max+1))) ||
      (is_numeric($this->axis_max_v) &&
      ($this->axis_max_v < ($this->flip_axes ? $k_min : $v_min))) ||
      (is_numeric($this->axis_min_v) &&
      ($this->axis_min_v >= ($this->flip_axes ? $k_max+1 : $v_max))))
        throw new Exception('No values in grid range');

    $ends = compact('v_max', 'v_min', 'k_max', 'k_min');

    // use fixed values if set
    if(is_numeric($this->axis_max_h))
      $ends[$this->flip_axes ? 'v_max' : 'k_max'] = $this->axis_max_h;
    if(is_numeric($this->axis_min_h))
      $ends[$this->flip_axes ? 'v_min' : 'k_min'] = $this->axis_min_h;
    if(is_numeric($this->axis_max_v))
      $ends[$this->flip_axes ? 'k_max' : 'v_max'] = $this->axis_max_v;
    if(is_numeric($this->axis_min_v))
      $ends[$this->flip_axes ? 'k_min' : 'v_min'] = $this->axis_min_v;

    return $ends;
  }

  /**
   * Returns the X and Y axis class instances as a list
   */
  protected function GetAxes($ends, &$x_len, &$y_len)
  {
    // disable units for associative keys
    if($this->values->AssociativeKeys())
      $this->units_x = null;

    if($this->flip_axes) {
      $max_h = $ends['v_max'];
      $min_h = $ends['v_min'];
      $max_v = $ends['k_max'];
      $min_v = $ends['k_min'];
      $x_min_unit = $this->minimum_units_y;
      $x_fit = false;
      $y_min_unit = 1;
      $y_fit = true;
      $x_units = (string)$this->units_y;
      $y_units = (string)$this->units_x;

    } else {
      $max_h = $ends['k_max'];
      $min_h = $ends['k_min'];
      $max_v = $ends['v_max'];
      $min_v = $ends['v_min'];
      $x_min_unit = 1;
      $x_fit = true;
      $y_min_unit = $this->minimum_units_y;
      $y_fit = false;
      $x_units = (string)$this->units_x;
      $y_units = (string)$this->units_y;
    }

    // sanitise grid divisions
    if(is_numeric($this->grid_division_v) && $this->grid_division_v <= 0)
      $this->grid_division_v = null;
    if(is_numeric($this->grid_division_h) && $this->grid_division_h <= 0)
      $this->grid_division_h = null;

    // if fixed grid spacing is specified, make the min spacing 1 pixel
    if(is_numeric($this->grid_division_v))
      $this->minimum_grid_spacing_v = 1;
    if(is_numeric($this->grid_division_h))
      $this->minimum_grid_spacing_h = 1;

    if(!is_numeric($max_h) || !is_numeric($min_h) ||
      !is_numeric($max_v) || !is_numeric($min_v))
      throw new Exception('Non-numeric min/max');

    if($this->log_axis_y && $this->flip_axes)
      $x_axis = new AxisLog($x_len, $max_h, $min_h, $x_min_unit, $x_fit,
        $x_units, $this->log_axis_y_base, $this->grid_division_h);
    elseif(!is_numeric($this->grid_division_h))
      $x_axis = new Axis($x_len, $max_h, $min_h, $x_min_unit, $x_fit, $x_units);
    else
      $x_axis = new AxisFixed($x_len, $max_h, $min_h, $this->grid_division_h, $x_units);

    if($this->log_axis_y && !$this->flip_axes)
      $y_axis = new AxisLog($y_len, $max_v, $min_v, $y_min_unit, $y_fit,
        $y_units, $this->log_axis_y_base, $this->grid_division_v);
    elseif(!is_numeric($this->grid_division_v))
      $y_axis = new Axis($y_len, $max_v, $min_v, $y_min_unit, $y_fit, $y_units);
    else
      $y_axis = new AxisFixed($y_len, $max_v, $min_v, $this->grid_division_v, $y_units);

    $y_axis->Reverse(); // because axis starts at bottom
    return array($x_axis, $y_axis);
  }

  /**
   * Calculates the effect of axes, applying to padding
   */
  protected function CalcAxes()
  {
    if($this->axes_calc_done)
      return;

    $ends = $this->GetAxisEnds();
    if(!$this->label_adjust_done)
      $this->LabelAdjustment();
    if(is_null($this->g_height) || is_null($this->g_width))
      $this->SetGridDimensions();

    list($x_axis, $y_axis) = $this->GetAxes($ends, $this->g_width,
      $this->g_height);

    if($this->flip_axes) {
      if($this->label_centre)
        $y_axis->Bar();
      $x_min_unit = $this->minimum_units_y;
      $y_min_unit = 1;
    } else {
      if($this->label_centre)
        $x_axis->Bar();
      $x_min_unit = 1;
      $y_min_unit = $this->minimum_units_y;
    }

    $this->min_space_h = $this->GetFirst($this->minimum_grid_spacing_h,
      $this->minimum_grid_spacing);
    $this->min_space_v = $this->GetFirst($this->minimum_grid_spacing_v,
      $this->minimum_grid_spacing);
    $this->uneven_x = $x_axis->Uneven();
    $this->uneven_y = $y_axis->Uneven();
    $this->bar_unit_width = $x_axis->Unit();
    $this->bar_unit_height = $y_axis->Unit();
    $this->x_axis = $x_axis;
    $this->y_axis = $y_axis;

    $this->axes_calc_done = true;
  }

  /**
   * Calculates the position of grid lines
   */
  protected function CalcGrid()
  {
    if(isset($this->y_points))
      return;

    $grid_bottom = $this->height - $this->pad_bottom;
    $grid_left = $this->pad_left;
    $this->y_subdivs = array();
    $this->x_subdivs = array();

    $this->y_points = $this->y_axis->GetGridPoints($this->min_space_v, 
      $grid_bottom);
    $this->x_points = $this->x_axis->GetGridPoints($this->min_space_h,
      $grid_left);

    if($this->show_subdivisions) {
      $this->y_subdivs = $this->y_axis->GetGridSubdivisions($this->minimum_subdivision,
        $this->flip_axes ? 1 : $this->minimum_units_y, $grid_bottom, $this->subdivision_v);
      $this->x_subdivs = $this->x_axis->GetGridSubdivisions($this->minimum_subdivision,
        $this->flip_axes ? $this->minimum_units_y : 1, $grid_left, $this->subdivision_h);
    }

    if($this->flip_axes) {
      $this->grid_limit = $this->label_centre ?
        $this->g_height - ($this->bar_unit_height / 2) : $this->g_height;
    } else {
      $this->grid_limit = $this->label_centre ?
        $this->g_width - ($this->bar_unit_width / 2) : $this->g_width;
    }
    $this->grid_limit += 0.01; // allow for floating-point inaccuracy
  }


  /**
   * Subclasses can override this for non-linear graphs
   */
  protected function GetHorizontalCount()
  {
    return $this->values->ItemsCount();
  }

  /**
   * Returns the X axis SVG fragment
   */
  protected function XAxis($yoff)
  {
    $x = $this->pad_left - $this->axis_overlap;
    $y = $this->height - $this->pad_bottom - $yoff;
    $len = $this->g_width + 2 * $this->axis_overlap;
    $path = "M$x {$y}h$len";
    return $this->Element('path', array('d' => $path));
  }

  /**
   * Returns the Y axis SVG fragment
   */
  protected function YAxis($xoff)
  {
    $x = $this->pad_left + $xoff;
    $len = $this->g_height + 2 * $this->axis_overlap;
    $y = $this->height - $this->pad_bottom + $this->axis_overlap - $len;
    $path = "M$x {$y}v$len";
    return $this->Element('path', array('d' => $path));
  }

  /**
   * Returns the position and size of divisions
   * @retval array('pos' => $position, 'sz' => $size)
   */
  protected function DivisionsPositions($style, $size, $fullsize, $start,
    $axis_offset)
  {
    $sz = $size;
    $pos = $start + $axis_offset;

    switch($style) {
    case 'none' :
      return null; // no pos or sz
    case 'infull' :
      $pos = $start;
      $sz = $fullsize;
      break;
    case 'over' :
      $pos -= $size;
      $sz = $size * 2;
      break;
    case 'overfull' :
      $pos = $start - $size;
      $sz = $fullsize + $size;
      break;
    case 'in' :
      break; // no change
    case 'out' :
    default :
      $pos -= $size;
      $sz = $size;
    }

    return array('sz' => $sz, 'pos' => $pos);
  }

  /**
   * Returns X-axis divisions as a path
   */
  protected function XAxisDivisions(&$points, $style, $size, $yoff)
  {
    $path = '';
    $pos = $this->DivisionsPositions($style, $size, $this->g_height,
      $this->pad_bottom, $yoff);
    if(is_null($pos))
      return '';

    $y = $this->height - $pos['pos'];
    $height = -$pos['sz'];
    foreach($points as $x)
      $path .= "M$x {$y}v{$height}";
    return $path;
  }

  /**
   * Returns Y-axis divisions as a path
   */
  protected function YAxisDivisions(&$points, $style, $size, $xoff)
  {
    $path = '';
    $pos = $this->DivisionsPositions($style, $size, $this->g_width,
      $this->pad_left, $xoff);
    if(is_null($pos))
      return '';

    $x = $pos['pos'];
    $size = $pos['sz'];
    foreach($points as $y)
      $path .= "M$x {$y}h{$size}";
    return $path;
  }

  /**
   * Returns the X-axis text positions
   */
  protected function XAxisTextPositions(&$points, $xoff, $yoff, $angle, $inside)
  {
    $positions = array();
    $x_prev = -$this->width;
    $min_space = $this->GetFirst($this->minimum_grid_spacing_h,
      $this->minimum_grid_spacing);
    $count = count($points);
    $label_centre_x = $this->label_centre && !$this->flip_axes;
    $font_size = $this->GetFirst($this->axis_font_size_h, $this->axis_font_size);
    $font_adjust = $this->GetFirst($this->axis_font_adjust_h, $this->axis_font_adjust);
    $text_space = $this->GetFirst($this->axis_text_space_h, $this->axis_text_space);
    $text_centre = $font_size * 0.3;

    if($inside)
    {
      $y = -$yoff - $text_space;
      $angle = -$angle;
      $x_rotate_offset = -$text_centre;
    }
    else
    {
      $y = $yoff + $font_size + $text_space - $text_centre;
      $x_rotate_offset = $text_centre;
    }
    if($angle < 0)
      $x_rotate_offset = -$x_rotate_offset;
    $y_rotate_offset = -$text_centre;
    $position = array('y' => $y);
    if($angle == 0) {
      $position['text-anchor'] = 'middle';
    } else {
      $position['text-anchor'] = $this->axis_text_angle_h < 0 ? 'end' : 'start';
    }
    $p = 0;
    foreach($points as $label => $x) {
      $key = $this->flip_axes ? $label : $this->GetKey($label);

      // don't draw 0 over the axis line
      if($inside && !$label_centre_x && $key == '0')
        $key = '';

      if(strlen($key) > 0 && $x - $x_prev >= $min_space
         &&  (++$p < $count || !$label_centre_x)) {
        $position['x'] = $x + $xoff;
        if($angle != 0) {
          $position['x'] -= $x_rotate_offset;
          $rcx = $position['x'] + $x_rotate_offset;
          $rcy = $position['y'] + $y_rotate_offset;
          $position['transform'] = "rotate($angle,$rcx,$rcy)";
        }
        $size = $this->TextSize((string)$key, $font_size, $font_adjust, $angle,
          $font_size);
        $position['text'] = $key;
        $position['w'] = $size[0];
        $position['h'] = $size[1];
        $positions[] = $position;
      }
      $x_prev = $x;
    }
    return $positions;
  }

  /**
   * Returns the Y-axis text positions
   */
  protected function YAxisTextPositions(&$points, $xoff, $yoff, $angle, $inside)
  {
    $y_prev = $this->height;
    $min_space = $this->minimum_grid_spacing_v;
    $font_size = $this->GetFirst($this->axis_font_size_v, $this->axis_font_size);
    $font_adjust = $this->GetFirst($this->axis_font_adjust_v, $this->axis_font_adjust);
    $text_space = $this->GetFirst($this->axis_text_space_v, $this->axis_text_space);
    $text_centre = $font_size * 0.3;
    $label_centre_y = $this->label_centre && $this->flip_axes;
    $x_rotate_offset = $inside ? $text_centre : -$text_centre;
    $y_rotate_offset = -$text_centre;
    $x = $xoff + $text_space;
    if(!$inside)
      $x = -$x;

    $position = array('x' => $x);
    $position['text-anchor'] = $inside ? 'start' : 'end';
    $positions = array();
    $count = count($points);
    $p = 0;
    foreach($points as $label => $y) {
      $key = $this->flip_axes ? $this->GetKey($label) : $label;

      // don't draw 0 over the axis line
      if($inside && !$label_centre_y && $key == '0')
        $key = '';

      if(strlen($key) && $y_prev - $y >= $min_space &&
        (++$p < $count || !$label_centre_y)) {
        $position['y'] = $y + $text_centre + $yoff;
        if($angle != 0) {
          $rcx = $position['x'] + $x_rotate_offset;
          $rcy = $position['y'] + $y_rotate_offset;
          $position['transform'] = "rotate($angle,$rcx,$rcy)";
        }
        $size = $this->TextSize((string)$key, $font_size, $font_adjust, $angle,
          $font_size);
        $position['text'] = $key;
        $position['w'] = $size[0];
        $position['h'] = $size[1];
        $positions[] = $position;
      }
      $y_prev = $y;
    }
    return $positions;
  }

  /**
   * Returns the X-axis text fragment
   */
  protected function XAxisText(&$points, $xoff, $yoff, $angle)
  {
    $inside = ('inside' == $this->GetFirst($this->axis_text_position_h,
      $this->axis_text_position));
    if($inside)
      $yoff -= $this->height - $this->pad_bottom;
    else
      $yoff += $this->height - $this->pad_bottom;
    $positions = $this->XAxisTextPositions($points, $xoff, $yoff, $angle,
      $inside);
    if(empty($positions))
      return '';

    $labels = '';
    $font_size = $this->GetFirst($this->axis_font_size_h, $this->axis_font_size);
    $anchor = $positions[0]['text-anchor'];
    foreach($positions as $pos) {
      $text = $pos['text'];
      if($inside)
        $pos['y'] -= $pos['h'] - $font_size;
      unset($pos['w'], $pos['h'], $pos['text'], $pos['text-anchor']);
      $labels .= $this->Text($text, $font_size, $pos);
    }
    $group = array('text-anchor' => $anchor);
    if(!empty($this->axis_font_h))
      $group['font-family'] = $this->axis_font_h;
    if(!empty($this->axis_font_size_h))
      $group['font-size'] = $font_size;
    if(!empty($this->axis_text_colour_h))
      $group['fill'] = $this->axis_text_colour_h;

    return $this->Element('g', $group, NULL, $labels);
  }

  /**
   * Returns the Y-axis text fragment
   */
  protected function YAxisText(&$points, $xoff, $yoff, $angle)
  {
    $inside = ('inside' == $this->GetFirst($this->axis_text_position_v,
      $this->axis_text_position));

    if($inside)
      $xoff += $this->pad_left;
    else
      $xoff -= $this->pad_left;
    $positions = $this->YAxisTextPositions($points, $xoff, $yoff, $angle,
      $inside);
    if(empty($positions))
      return '';

    $labels = '';
    $font_size = $this->GetFirst($this->axis_font_size_v, $this->axis_font_size);
    $anchor = $positions[0]['text-anchor'];
    foreach($positions as $pos) {
      $text = $pos['text'];
      unset($pos['w'], $pos['h'], $pos['text'], $pos['text-anchor']);
      $labels .= $this->Text($text, $font_size, $pos);
    }
    $group = array('text-anchor' => $anchor);
    if(!empty($this->axis_font_v))
      $group['font-family'] = $this->axis_font_v;
    if(!empty($this->axis_font_size_v))
      $group['font-size'] = $font_size;
    if(!empty($this->axis_text_colour_v))
      $group['fill'] = $this->axis_text_colour_v;

    return $this->Element('g', $group, NULL, $labels);
  }

  /**
   * Returns the horizontal axis label
   */
  protected function HLabel(&$attribs)
  {
    if(empty($this->label_h))
      return '';

    $x = ($this->width - $this->pad_left - $this->pad_right) / 2 +
      $this->pad_left;
    $y = $this->height - $this->label_bottom_offset;
    $pos = compact('x', 'y');
    return $this->Text($this->label_h, $this->label_font_size,
      array_merge($attribs, $pos));
  }

  /**
   * Returns the vertical axis label
   */
  protected function VLabel(&$attribs)
  {
    if(empty($this->label_v))
      return '';

    $x = $this->label_left_offset;
    $y = ($this->height - $this->pad_bottom - $this->pad_top) / 2 +
      $this->pad_top;
    $transform = "rotate(270,$x,$y)";
    $pos = compact('x', 'y', 'transform');
    return $this->Text($this->label_v, $this->label_font_size,
      array_merge($attribs, $pos));
  }

  /**
   * Returns the labels grouped with the provided axis division labels
   */
  protected function Labels($axis_text = '')
  {
    $labels = $axis_text;
    if(!empty($this->label_h) || !empty($this->label_v)) {
      $label_text = array('text-anchor' => 'middle');
      if($this->label_font != $this->axis_font)
        $label_text['font-family'] = $this->label_font;
      if($this->label_font_size != $this->axis_font_size)
        $label_text['font-size'] = $this->label_font_size;
      if($this->label_font_weight != 'normal')
        $label_text['font-weight'] = $this->label_font_weight;
      if(!empty($this->label_colour) &&
        $this->label_colour != $this->axis_text_colour)
        $label_text['fill'] = $this->label_colour;

      if(!empty($this->label_h)) {
        $label_text['y'] = $this->height - $this->label_bottom_offset;
        $label_text['x'] = $this->pad_left +
          ($this->width - $this->pad_left - $this->pad_right) / 2;
        $labels .= $this->Text($this->label_h, $this->label_font_size,
          $label_text);
      }

      $labels .= $this->VLabel($label_text);
    }

    if(!empty($labels)) {
      $font = array(
        'font-size' => $this->axis_font_size,
        'font-family' => $this->axis_font,
        'fill' => empty($this->axis_text_colour) ?
          $this->axis_colour : $this->axis_text_colour,
      );
      $labels = $this->Element('g', $font, NULL, $labels);
    }
    return $labels;
  }

  /**
   * Draws bar or line graph axes
   */
  protected function Axes()
  {
    if(!$this->show_axes)
      return $this->Labels();

    $this->CalcGrid();
    $y0 = $this->y_axis->Zero();
    $x0 = $this->x_axis->Zero();
    $x_axis_visible = $this->show_axis_h && $y0 >= 0 && $y0 <= $this->g_height;
    $y_axis_visible = $this->show_axis_v && $x0 >= 0 && $x0 <= $this->g_width;
    $yoff = $x_axis_visible ? $y0 : 0;
    $xoff = $y_axis_visible ? $x0 : 0;

    $axis_group = $axes = $label_group = $divisions = $axis_text = '';
    if($x_axis_visible)
      $axes .= $this->XAxis($yoff);
    if($y_axis_visible)
      $axes .= $this->YAxis($xoff);

    if($axes != '') {
      $line = array(
        'stroke-width' => $this->axis_stroke_width,
        'stroke' => $this->axis_colour
      );
      $axis_group = $this->Element('g', $line, NULL, $axes);
    }

    $x_offset = $y_offset = 0;
    if($this->label_centre) {
      if($this->flip_axes)
        $y_offset = -0.5 * $this->bar_unit_height;
      else
        $x_offset = 0.5 * $this->bar_unit_width;
    }

    $text_offset = $this->DivisionOverlap();
    if($this->show_axis_text_v)
      $axis_text .= $this->YAxisText($this->y_points, $text_offset['y'],
        $y_offset, $this->axis_text_angle_v);
    if($this->show_axis_text_h)
      $axis_text .= $this->XAxisText($this->x_points, $x_offset,
        $text_offset['x'], $this->axis_text_angle_h);

    $label_group = $this->Labels($axis_text);

    if($this->show_divisions) {
      // use an array to join paths with same colour
      $div_paths = array();
      if($this->show_axis_h) {
        $dx_path = $this->XAxisDivisions($this->x_points,
          $this->GetFirst($this->division_style_h, $this->division_style), 
          $this->GetFirst($this->division_size_h, $this->division_size),
          $yoff);
        if(!empty($dx_path)) {
          $dx_colour = $this->GetFirst($this->division_colour_h,
            $this->division_colour, $this->axis_colour);
          @$div_paths[$dx_colour] .= $dx_path;
        }
      }
      if($this->show_axis_v) {
        $dy_path = $this->YAxisDivisions($this->y_points,
          $this->GetFirst($this->division_style_v, $this->division_style),
          $this->GetFirst($this->division_size_v, $this->division_size),
          $xoff);
        if(!empty($dy_path)) {
          $dy_colour = $this->GetFirst($this->division_colour_v,
            $this->division_colour, $this->axis_colour);
          @$div_paths[$dy_colour] .= $dy_path;
        }
      }

      if($this->show_subdivisions) {
        if($this->show_axis_h) {
          $sdx_path = $this->XAxisDivisions($this->x_subdivs,
            $this->GetFirst($this->subdivision_style_h,
              $this->subdivision_style), 
            $this->GetFirst($this->subdivision_size_h,
              $this->subdivision_size), $yoff);

          if(!empty($sdx_path)) {
            $sdx_colour = $this->GetFirst($this->subdivision_colour_h,
              $this->subdivision_colour, $this->division_colour_h,
              $this->division_colour, $this->axis_colour);
            @$div_paths[$sdx_colour] .= $sdx_path;
          }
        }
        if($this->show_axis_v) {
          $sdy_path = $this->YAxisDivisions($this->y_subdivs,
            $this->GetFirst($this->subdivision_style_v,
              $this->subdivision_style),
            $this->GetFirst($this->subdivision_size_v,
              $this->subdivision_size), $xoff);
          if(!empty($sdy_path)) {
            $sdy_colour = $this->GetFirst($this->subdivision_colour_v,
              $this->subdivision_colour, $this->division_colour_v,
              $this->division_colour, $this->axis_colour);
            @$div_paths[$sdy_colour] .= $sdy_path;
          }
        }
      }

      foreach($div_paths as $colour => $path) {
        $div = array(
          'd' => $path,
          'stroke-width' => 1,
          'stroke' => $colour
        );
        $divisions .= $this->Element('path', $div);
      }
    }
    return $divisions . $axis_group . $label_group;
  }

  /**
   * Returns a set of gridlines
   */
  protected function GridLines($path, $colour, $dash, $fill = null)
  {
    if($path == '' || $colour == 'none')
      return '';
    $opts = array('d' => $path, 'stroke' => $colour);
    if(!empty($dash))
      $opts['stroke-dasharray'] = $dash;
    if(!empty($fill))
      $opts['fill'] = $fill;
    return $this->Element('path', $opts);
  }

  /**
   * Draws the grid behind the bar / line graph
   */
  protected function Grid()
  {
    $this->CalcAxes();
    $this->CalcGrid();
    if(!$this->show_grid || (!$this->show_grid_h && !$this->show_grid_v))
      return '';

    $back = $subpath = $path_h = $path_v = '';
    $back_colour = $this->grid_back_colour;
    if(!empty($back_colour) && $back_colour != 'none') {

      if(is_array($back_colour)) {
        $gradient_id = $this->AddGradient($back_colour);
        $back_colour = "url(#{$gradient_id})";
      }
      $rect = array(
        'x' => $this->pad_left, 'y' => $this->pad_top,
        'width' => $this->g_width, 'height' => $this->g_height,
        'fill' => $back_colour
      );
      $back = $this->Element('rect', $rect);
    }
    if($this->grid_back_stripe) {
      $grp = array('fill' => $this->grid_back_stripe_colour);
      $bars = '';
      $c = 0;
      if($this->flip_axes) {
        $rect = array('y' => $this->pad_top, 'height' => $this->g_height);
        foreach($this->x_points as $x) {
          if($c % 2 == 0 && isset($rect['width'])) {
            $rect['x'] = $rect['width'];
            $rect['width'] = $x - $rect['width'];
            $bars .= $this->Element('rect', $rect);
          } else {
            $rect['width'] = $x;
          }
          ++$c;
        }
      } else {
        $rect = array('x' => $this->pad_left, 'width' => $this->g_width);
        foreach($this->y_points as $y) {
          if($c % 2 == 0 && isset($rect['height'])) {
            $rect['y'] = $y;
            $rect['height'] -= $y;
            $bars .= $this->Element('rect', $rect);
          } else {
            $rect['height'] = $y;
          }
          ++$c;
        }
      }
      $back .= $this->Element('g', $grp, null, $bars);
    }
    if($this->show_grid_subdivisions) {
      $subpath_h = $subpath_v = '';
      if($this->show_grid_h)
        foreach($this->y_subdivs as $y) 
          $subpath_v .= "M{$this->pad_left} {$y}h{$this->g_width}";
      if($this->show_grid_v)
        foreach($this->x_subdivs as $x) 
          $subpath_h .= "M$x {$this->pad_top}v{$this->g_height}";

      if($subpath_h != '' || $subpath_v != '') {
        $colour_h = $this->GetFirst($this->grid_subdivision_colour_h,
          $this->grid_subdivision_colour, $this->grid_colour_h,
          $this->grid_colour);
        $colour_v = $this->GetFirst($this->grid_subdivision_colour_v,
          $this->grid_subdivision_colour, $this->grid_colour_v,
          $this->grid_colour);
        $dash_h = $this->GetFirst($this->grid_subdivision_dash_h,
          $this->grid_subdivision_dash, $this->grid_dash_h, $this->grid_dash);
        $dash_v = $this->GetFirst($this->grid_subdivision_dash_v,
          $this->grid_subdivision_dash, $this->grid_dash_v, $this->grid_dash);

        if($dash_h == $dash_v && $colour_h == $colour_v) {
          $subpath = $this->GridLines($subpath_h . $subpath_v, $colour_h,
            $dash_h);
        } else {
          $subpath = $this->GridLines($subpath_h, $colour_h, $dash_h) .
            $this->GridLines($subpath_v, $colour_v, $dash_v);
        }
      }
    }

    if($this->show_grid_h)
      foreach($this->y_points as $y) 
        $path_v .= "M{$this->pad_left} {$y}h{$this->g_width}";
    if($this->show_grid_v)
      foreach($this->x_points as $x) 
        $path_h .= "M$x {$this->pad_top}v{$this->g_height}";

    $colour_h = $this->GetFirst($this->grid_colour_h, $this->grid_colour);
    $colour_v = $this->GetFirst($this->grid_colour_v, $this->grid_colour);
    $dash_h = $this->GetFirst($this->grid_dash_h, $this->grid_dash);
    $dash_v = $this->GetFirst($this->grid_dash_v, $this->grid_dash);

    if($dash_h == $dash_v && $colour_h == $colour_v) {
      $path = $this->GridLines($path_v . $path_h, $colour_h, $dash_h);
    } else {
      $path = $this->GridLines($path_h, $colour_h, $dash_h) .
        $this->GridLines($path_v, $colour_v, $dash_v);
    }

    return $back . $subpath . $path;
  }

  /**
   * clamps a value to the grid boundaries
   */
  protected function ClampVertical($val)
  {
    return max($this->pad_top, min($this->height - $this->pad_bottom, $val));
  }

  protected function ClampHorizontal($val)
  {
    return max($this->pad_left, min($this->width - $this->pad_right, $val));
  }

  /**
   * Returns a clipping path for the grid
   */
  protected function ClipGrid(&$attr)
  {
    $rect = array(
      'x' => $this->pad_left, 'y' => $this->pad_top,
      'width' => $this->width - $this->pad_left - $this->pad_right,
      'height' => $this->height - $this->pad_top - $this->pad_bottom
    );
    $clip_id = $this->NewID();
    $this->defs[] = $this->Element('clipPath', array('id' => $clip_id),
      NULL, $this->Element('rect', $rect));
    $attr['clip-path'] = "url(#{$clip_id})";
  }

  /**
   * Returns the grid position for a bar or point, or NULL if not on grid
   * $key  = actual value array index
   * $ikey = integer position in array
   */
  protected function GridPosition($key, $ikey)
  {
    $position = null;
    $gkey = $this->values->AssociativeKeys() ? $ikey : $key;
    $zero = -0.01; // catch values close to 0
    if($this->flip_axes) {
      $offset = $this->y_axis->Zero() + ($this->bar_unit_height * $gkey);
      if($offset >= $zero && floor($offset) <= $this->grid_limit)
        $position = $this->height - $this->pad_bottom - $offset;
    } else {
      $offset = $this->x_axis->Zero() + ($this->bar_unit_width * $gkey);
      if($offset >= $zero && floor($offset) <= $this->grid_limit)
        $position = $this->pad_left + $offset;
    }
    return $position;
  }

  /**
   * Returns the $x value as a grid position
   */
  protected function GridX($x)
  {
    $p = $this->x_axis->Position($x);
    if(!is_null($p))
      return $this->pad_left + $p;
    return null;
  }

  /**
   * Returns the $y value as a grid position
   */
  protected function GridY($y)
  {
    $p = $this->y_axis->Position($y);
    if(!is_null($p))
      return $this->height - $this->pad_bottom - $p;
    return null;
  }

  /**
   * Returns the location of the X axis origin
   */
  protected function OriginX()
  {
    return $this->pad_left + $this->x_axis->Origin();
  }

  /**
   * Returns the location of the Y axis origin
   */
  protected function OriginY()
  {
    return $this->height - $this->pad_bottom - $this->y_axis->Origin();
  }

  /**
   * Converts guideline options to more useful member variables
   */
  protected function CalcGuidelines($g = null)
  {
    if(is_null($this->guidelines))
      $this->guidelines = array();
    if(is_null($g)) {
      // no guidelines?
      if(empty($this->guideline) && $this->guideline !== 0)
        return;

      if(is_array($this->guideline) && count($this->guideline) > 1 &&
        !is_string($this->guideline[1])) {

        // array of guidelines
        foreach($this->guideline as $gl)
          $this->CalcGuidelines($gl);
        return;
      }

      // single guideline
      $g = $this->guideline;
    }

    if(!is_array($g))
      $g = array($g);

    $value = $g[0];
    $axis = (isset($g[2]) && ($g[2] == 'x' || $g[2] == 'y')) ? $g[2] : 'y';
    $above = isset($g['above']) ? $g['above'] : $this->guideline_above;
    $position = $above ? SVGG_GUIDELINE_ABOVE : SVGG_GUIDELINE_BELOW;
    $guideline = array(
      'value' => $value,
      'depth' => $position,
      'title' => isset($g[1]) ? $g[1] : '',
      'axis' => $axis
    );
    $lopts = $topts = array();
    $line_opts = array(
      'colour' => 'stroke',
      'dash' => 'stroke-dasharray',
      'stroke_width' => 'stroke-width',
      'opacity' => 'opacity',

      // not SVG attributes
      'length' => 'length',
      'length_units' => 'length_units',
    );
    $text_opts = array(
      'colour' => 'fill',
      'opacity' => 'opacity',
      'font' => 'font-family',
      'font_size' => 'font-size',
      'font_weight' => 'font-weight',
      'text_colour' => 'fill', // overrides 'colour' option from line
      'text_opacity' => 'opacity', // overrides line opacity

      // these options do not map to SVG attributes
      'font_adjust' => 'font_adjust',
      'text_position' => 'text_position',
      'text_padding' => 'text_padding',
      'text_angle' => 'text_angle',
      'text_align' => 'text_align',
    );
    foreach($line_opts as $okey => $opt)
      if(isset($g[$okey]))
        $lopts[$opt] = $g[$okey];
    foreach($text_opts as $okey => $opt)
      if(isset($g[$okey]))
        $topts[$opt] = $g[$okey];

    if(count($lopts))
      $guideline['line'] = $lopts;
    if(count($topts))
      $guideline['text'] = $topts;

    // update maxima and minima
    if(is_null($this->max_guide[$axis]) || $value > $this->max_guide[$axis])
      $this->max_guide[$axis] = $value;
    if(is_null($this->min_guide[$axis]) || $value < $this->min_guide[$axis])
      $this->min_guide[$axis] = $value;

    // can flip the axes now the min/max are stored
    if($this->flip_axes)
      $guideline['axis'] = ($guideline['axis'] == 'x' ? 'y' : 'x');

    $this->guidelines[] = $guideline;
  }

  /**
   * Returns the elements to draw the guidelines
   */
  protected function Guidelines($depth)
  {
    if(empty($this->guidelines))
      return '';

    // build all the lines at this depth (above/below) that use
    // global options as one path
    $d = $lines = $text = '';
    $path = array(
      'stroke' => $this->guideline_colour,
      'stroke-width' => $this->guideline_stroke_width,
      'stroke-dasharray' => $this->guideline_dash,
      'fill' => 'none'
    );
    if($this->guideline_opacity != 1)
      $path['opacity'] = $this->guideline_opacity;
    $textopts = array(
      'font-family' => $this->guideline_font,
      'font-size' => $this->guideline_font_size,
      'font-weight' => $this->guideline_font_weight,
      'fill' => $this->GetFirst($this->guideline_text_colour, 
        $this->guideline_colour),
    );
    $text_opacity = $this->GetFirst($this->guideline_text_opacity, 
      $this->guideline_opacity);

    foreach($this->guidelines as $line) {
      if($line['depth'] == $depth) {
        // opacity cannot go in the group because child opacity is multiplied
        // by group opacity
        if($text_opacity != 1 && !isset($line['text']['opacity']))
          $line['text']['opacity'] = $text_opacity;
        $this->BuildGuideline($line, $lines, $text, $path, $d);
      }
    }
    if(!empty($d)) {
      $path['d'] = $d;
      $lines .= $this->Element('path', $path);
    }

    if(!empty($text))
      $text = $this->Element('g', $textopts, null, $text);
    return $lines . $text;
  }

  /**
   * Adds a single guideline and its title to content
   */
  protected function BuildGuideline(&$line, &$lines, &$text, &$path, &$d)
  {
    $length = $this->guideline_length;
    $length_units = $this->guideline_length_units;
    if(isset($line['line'])) {
      $this->UpdateAndUnset($length, $line['line'], 'length');
      $this->UpdateAndUnset($length_units, $line['line'], 'length_units');
    }
    if($length != 0) {
      if($line['axis'] == 'x')
        $h = $length;
      else
        $w = $length;
    } elseif($length_units != 0) {
      if($line['axis'] == 'x')
        $h = $length_units * $this->bar_unit_height;
      else
        $w = $length_units * $this->bar_unit_width;
    }

    $path_data = $this->GuidelinePath($line['axis'], $line['value'],
      $line['depth'], $x, $y, $w, $h);
    if(!isset($line['line'])) {
      // no special options, add to main path
      $d .= $path_data;
    } else {
      $line_path = array_merge($path, $line['line'], array('d' => $path_data));
      $lines .= $this->Element('path', $line_path);
    }
    if(!empty($line['title'])) {
      $text_pos = $this->guideline_text_position;
      $text_pad = $this->guideline_text_padding;
      $text_angle = $this->guideline_text_angle;
      $text_align = $this->guideline_text_align;
      $font_size = $this->guideline_font_size;
      $font_adjust = $this->guideline_font_adjust;
      if(isset($line['text'])) {
        $this->UpdateAndUnset($text_pos, $line['text'], 'text_position');
        $this->UpdateAndUnset($text_pad, $line['text'], 'text_padding');
        $this->UpdateAndUnset($text_angle, $line['text'], 'text_angle');
        $this->UpdateAndUnset($text_align, $line['text'], 'text_align');
        $this->UpdateAndUnset($font_adjust, $line['text'], 'font_adjust');
        if(isset($line['text']['font-size']))
          $font_size = $line['text']['font-size'];
      }
      list($text_w, $text_h) = $this->TextSize($line['title'], 
        $font_size, $font_adjust, $text_angle, $font_size);

      list($x, $y, $text_right) = Graph::RelativePosition(
        $text_pos, $y, $x, $y + $h, $x + $w,
        $text_w, $text_h, $text_pad, true);

      $t = array('x' => $x, 'y' => $y + $font_size);
      if($text_right && empty($text_align))
        $text_align = 'right';
      $align_map = array('right' => 'end', 'centre' => 'middle');
      if(!empty($text_align) && isset($align_map[$text_align]))
        $t['text-anchor'] = $align_map[$text_align];

      if($text_angle != 0) {
        $rx = $x + $text_h/2;
        $ry = $y + $text_h/2;
        $t['transform'] = "rotate($text_angle,$rx,$ry)";
      }

      if(isset($line['text']))
        $t = array_merge($t, $line['text']);
      $text .= $this->Text($line['title'], $font_size, $t);
    }
  }

  /**
   * Creates the path data for a guideline and sets the dimensions
   */
  protected function GuidelinePath($axis, $value, $depth, &$x, &$y, &$w, &$h)
  {
    if($axis == 'x') {
      $x = $this->GridX($value);
      $y = $this->height - $this->pad_bottom - $this->g_height;
      $w = 0;
      if($h == 0) {
        $h = $this->g_height;
      } elseif($h < 0) {
        $h = -$h;
      } else {
        $y = $this->height - $this->pad_bottom - $h;
      }
      return "M$x {$y}v$h";
    } else {
      $x = $this->pad_left;
      $y = $this->GridY($value);
      if($w == 0) {
        $w = $this->g_width;
      } elseif($w < 0) {
        $w = -$w;
        $x = $this->pad_left + $this->g_width - $w;
      }
      $h = 0;
      return "M$x {$y}h$w";
    }
  }

  /**
   * Updates $var with $array[$key] and removes it from array
   */
  protected function UpdateAndUnset(&$var, &$array, $key)
  {
    if(isset($array[$key])) {
      $var = $array[$key];
      unset($array[$key]);
    }
  }
}

