<?php
/**
 * Copyright (C) 2013 Graham Breach
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

require_once 'SVGGraphHorizontalBarGraph.php';

class HorizontalFloatingBarGraph extends HorizontalBarGraph {

  protected $require_structured = array('end');
  private $min_value = null;
  private $max_value = null;

  protected function Draw()
  {
    $body = $this->Grid() . $this->Guidelines(SVGG_GUIDELINE_BELOW);

    $bar_height = ($this->bar_space >= $this->bar_unit_height ? '1' : 
      $this->bar_unit_height - $this->bar_space);
    $bar_style = array();
    $this->SetStroke($bar_style);
    $bar = array('height' => $bar_height);

    $bspace = $this->bar_space / 2;
    $bnum = 0;
    $ccount = count($this->colours);

    foreach($this->values[0] as $item) {
      $bar_pos = $this->GridPosition($item->key, $bnum);

      if(!is_null($item->value) && !is_null($bar_pos)) {
        $bar['y'] = $bar_pos - $bspace - $bar_height;

        $end = $item->Data('end');
        $start = $item->value;
        $value = $end - $start;
        $this->Bar($value, $bar, $start);

        if($bar['width'] > 0) {
          $bar_style['fill'] = $this->GetColour($item, $bnum % $ccount);

          if($this->show_tooltips)
            $this->SetTooltip($bar, $item, $value, null,
              !$this->compat_events && $this->show_bar_labels);
          $rect = $this->Element('rect', $bar, $bar_style);
          $body .= $this->GetLink($item, $item->key, $rect);
          unset($bar['id']); // clear for next value

          if(!isset($this->bar_styles[$bnum]))
            $this->bar_styles[$bnum] = $bar_style;
        }
      }
      ++$bnum;
    }

    $body .= $this->Guidelines(SVGG_GUIDELINE_ABOVE) . $this->Axes();
    return $body;
  }

  /**
   * Checks that the data contains required fields
   */
  protected function CheckValues()
  {
    parent::CheckValues();
  }

  /**
   * Returns the maximum bar end
   */
  protected function GetMaxValue()
  {
    if(!is_null($this->max_value))
      return $this->max_value;
    $max = null;
    foreach($this->values[0] as $item) {
      $s = $item->value;
      $e = $item->Data('end');
      if(is_null($s) || is_null($e))
        continue;
      $m = max($s, $e);
      if(is_null($max) || $m > $max)
        $max = $m;
    }
    return ($this->max_value = $max);
  }

  /**
   * Returns the minimum bar end
   */
  protected function GetMinValue()
  {
    if(!is_null($this->min_value))
      return $this->min_value;
    $min = null;
    foreach($this->values[0] as $item) {
      $s = $item->value;
      $e = $item->Data('end');
      if(is_null($s) || is_null($e))
        continue;
      $m = min($s, $e);
      if(is_null($min) || $m < $min)
        $min = $m;
    }
    return ($this->min_value = $min);
  }

}

