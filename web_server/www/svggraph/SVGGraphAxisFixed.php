<?php
/**
 * Copyright (C) 2009-2012 Graham Breach
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

/**
 * Axis with fixed measurements
 */
class AxisFixed extends Axis { 

  protected $step;

  public function __construct($length, $max_val, $min_val, $step, $units)
  {
    parent::__construct($length, $max_val, $min_val, 1, false, $units);
    $this->step = $step;
  }

  /**
   * Calculates a grid based on min, max and step
   * min and max will be adjusted to fit step
   */
  protected function Grid($min, $round_up = false)
  {
    // if min and max are the same side of 0, only adjust one of them
    if($this->max_value * $this->min_value >= 0) {
      $count = $this->max_value - $this->min_value;
      // $round_up means bars, so add space for the bar
      if($round_up)
        ++$count;
      if(abs($this->max_value) >= abs($this->min_value)) {
        $this->max_value = $this->min_value +
          $this->step * ceil($count / $this->step);
      } else {
        $this->min_value = $this->max_value -
          $this->step * ceil($count / $this->step);
      }
    } else {
      $this->max_value = $this->step * ceil($this->max_value / $this->step);
      $this->min_value = $this->step * floor($this->min_value / $this->step);
    }

    $count = ($this->max_value - $this->min_value) / $this->step;
    $ulen = $this->max_value - $this->min_value;
    if($ulen == 0)
      throw new Exception("Zero length axis");
    $this->unit_size = $this->length / $ulen;
    $grid = $this->length / $count;
    $this->zero = (-$this->min_value / $this->step) * $grid;
    return $grid;
  }
}

