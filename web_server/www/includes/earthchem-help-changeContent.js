//This javascript file was generated from the Administer Help Contents page. It needs to be included after earthchem-help.js in the main header file on both neko and geoportal.
function changeContent(title) {
var thisContent
if (1==1) {} 
else if (title == 'Select x, y, z') {thisContent = '<b>Select x, y, z</b><br />Select chemicals to make a two-dimensional (x, y) plot, or a three-dimensional (x, y, z) plot.
<p />
Optionally, apply a ratio (numerator or denominator n) to x, y, and/or z.
<p />
If z is defined, then after a plot is generated, additional options will be presented for designing the datapoints to represent the z axis.'} 
else if (title == 'Design datapoints') {thisContent = '<b>Design datapoints</b><br />Select the shape, color and size of the datapoints. This is the main data point design, initially applied to all data points.
<p>
This main datapoint design can be overridden by design datapoints for an interval (range of values) for z.'} 
else if (title == 'Number of intervals for z') {thisContent = '<b>Number of intervals for z</b><br />A z interval is a range of values. This option is offered after a chemical has been selected for the z axis. Axis z can be represented by varying the data point design for different values for z. The z range will be divided evenly into the number of intervals specified. (Sometimes this results in an interval with 0 data points; such an interval will not appear on the plot or plot legend.)
<p>
If the number of z intervals is greater than one, design options for the z intervals will be offered: Show z intervals with a color gradient, Show z intervals with increasing point size, and Design data points for individual z intervals.'} 
else if (title == 'Gradient for z intervals') {thisContent = '<b>Gradient for z intervals</b><br />Different intervals for z can be represented with different datapoint colors. One option is to select a color gradient, starting  from the color selected in this step and ending with the fill color. 
<p>
For example, if the starting gradient color is yellow and the fill color is red, the z intervals will be represented with yellow, oranges and red.'} 
else if (title == 'Design datapoints for any z interval') {thisContent = '<b>Design datapoints for any z interval</b><br />Datapoints can be designed for any z interval (range of values for the chemical plotted on the z axis). This option overrides, for that interval, the main datapoint design.

A Design button opens a popup window in which the datapoints can be designed for a single z interval.


The plotting order refers to the order in which the datapoints for z intervals are layered. To make a z interval appear on top, assign to it the highest number for plotting order.'} 
else if (title == 'Finish your plot.') {thisContent = '<b>Finish your plot.</b><br />Choose the size of the plotting area. Margins will be added to this measurement and the finished plot image will be larger. 
<p />
Optional subtitles will appear directly below the title, and above the plot.

Then plot.'} 
else if (title == 'Limit range') {thisContent = '<b>Limit range</b><br />To plot only a subset of the samples in the search results, define the minimum and maximum values to plot for x, y or z.
<p />
To set the precision for the values, choose the number of decimal places for rounding. Rounding only affects the display on the plot and screen; it does not affect the calculations.
<p />
If the axis labels appear crowded, try setting the precision, increasing the plot size, or defining the maximum and minimum values to plot. If unwanted negative numbers are plotted, try defining the minimum value to plot.'} 
else if (title == 'Link datapoints to sample data') {thisContent = '<b>Link datapoints to sample data</b><br />Check this box to turn each datapoint into a link to additional data about the sample it represents.'} 
else if (title == 'Show z with increasing point size') {thisContent = '<b>Show z with increasing point size</b><br />Intervals for z can be represented by increasing the datapoint size for each interval, up to the datapoint size you selected (or larger if necessary).'} 
else if (title == 'A test title') {thisContent = '<b>A test title</b><br />'} 
else if (title == 'A test title') {thisContent = '<b>A test title</b><br />'} 
else if (title == 'A test title') {thisContent = '<b>A test title</b><br />'} 
else if (title == 'A test title') {thisContent = '<b>A test title</b><br />'} 
else if (title == 'Limit range') {thisContent = '<b>Limit range</b><br />To plot only a subset of the samples in the search results, define the minimum and maximum values to plot for x, y or z.
<p />
To set the precision for the values, choose the number of decimal places for rounding. Rounding only affects the display on the plot and screen; it does not affect the calculations.
<p />
If the axis labels appear crowded, try setting the precision, increasing the plot size, or defining the maximum and minimum values to plot. If unwanted negative numbers are plotted, try defining the minimum value to plot.'} 
else if (title == 'A test title') {thisContent = '<b>A test title</b><br />'} 
else if (title == 'A test title') {thisContent = '<b>A test title</b><br />'} 
else {thisContent = 'This help topic was not found.'
}
document.getElementById('TitleText').innerHTML=thisContent; //Change contents of the help div which follows the body tag
}