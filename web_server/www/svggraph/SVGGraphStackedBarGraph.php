<?php
/**
 * Copyright (C) 2011-2013 Graham Breach
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

require_once 'SVGGraphMultiGraph.php';
require_once 'SVGGraphBarGraph.php';

class StackedBarGraph extends BarGraph {

  protected $multi_graph;
  protected $legend_reverse = true;

  protected function Draw()
  {
    if($this->log_axis_y)
      throw new Exception('log_axis_y not supported by StackedBarGraph');

    $body = $this->Grid() . $this->Guidelines(SVGG_GUIDELINE_BELOW);

    $bar_width = ($this->bar_space >= $this->bar_unit_width ? '1' : 
      $this->bar_unit_width - $this->bar_space);
    $bar_style = array();
    $this->SetStroke($bar_style);
    $bar = array('width' => $bar_width);

    $bspace = $this->bar_space / 2;
    $bnum = 0;
    $ccount = count($this->colours);
    $chunk_count = count($this->multi_graph);

    foreach($this->multi_graph as $itemlist) {
      $k = $itemlist[0]->key;
      $bar_pos = $this->GridPosition($k, $bnum);

      if(!is_null($bar_pos)) {
        $bar['x'] = $bspace + $bar_pos;

        $ypos = $yneg = 0;
        for($j = 0; $j < $chunk_count; ++$j) {
          $item = $itemlist[$j];
          if(!is_null($item->value)) {
            $this->Bar($item->value, $bar, $item->value >= 0 ? $ypos : $yneg);
            if($item->value < 0)
              $yneg += $item->value;
            else
              $ypos += $item->value;

            if($bar['height'] > 0) {
              $bar_style['fill'] = $this->GetColour($item, $j % $ccount);

              if($this->show_tooltips)
                $this->SetTooltip($bar, $item, $item->value, null,
                  !$this->compat_events && $this->show_bar_labels);
              $rect = $this->Element('rect', $bar, $bar_style);
              if($this->show_bar_labels)
                $rect .= $this->BarLabel($item, $bar, $j + 1 < $chunk_count);
              $body .= $this->GetLink($item, $k, $rect);
              unset($bar['id']); // clear for next value

              if(!isset($this->bar_styles[$j]))
                $this->bar_styles[$j] = $bar_style;
            }
          }
        }
      }
      ++$bnum;
    }

    $body .= $this->Guidelines(SVGG_GUIDELINE_ABOVE) . $this->Axes();
    return $body;
  }

  /**
   * construct multigraph
   */
  public function Values($values)
  {
    parent::Values($values);
    if(!$this->values->error)
      $this->multi_graph = new MultiGraph($this->values, $this->force_assoc,
        $this->require_integer_keys);
  }

  /**
   * Overridden to prevent drawing behind higher bars
   * $offset_y should be true for inner bars
   */
  protected function BarLabel(&$item, &$bar, $offset_y = null)
  {
    $font_size = $this->bar_label_font_size;
    $space = $this->bar_label_space;
    if($offset_y) {

      // bar too small, would be above
      if($bar['height'] < $font_size + 2 * $space)
        return parent::BarLabel($item, $bar, ($bar['height'] + $font_size)/2);

      // option set to above
      if($this->bar_label_position == 'above') {
        $this->bar_label_position = 'top';
        $label = parent::BarLabel($item, $bar);
        $this->bar_label_position = 'above';
        return $label;
      }
    }
    return parent::BarLabel($item, $bar);
  }

  /**
   * Find the longest data set
   */
  protected function GetHorizontalCount()
  {
    return $this->multi_graph->ItemsCount(-1);
  }

  /**
   * Returns the maximum (stacked) value
   */
  protected function GetMaxValue()
  {
    return $this->multi_graph->GetMaxSumValue();
  }

  /**
   * Returns the minimum (stacked) value
   */
  protected function GetMinValue()
  {
    return $this->multi_graph->GetMinSumValue();
  }

  /**
   * Returns the key from the MultiGraph
   */
  protected function GetKey($index)
  {
    return $this->multi_graph->GetKey($index);
  }

  /**
   * Returns the maximum key from the MultiGraph
   */
  protected function GetMaxKey()
  {
    return $this->multi_graph->GetMaxKey();
  }

  /**
   * Returns the minimum key from the MultiGraph
   */
  protected function GetMinKey()
  {
    return $this->multi_graph->GetMinKey();
  }

}

