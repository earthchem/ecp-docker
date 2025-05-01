<?
include "/var/www/jpgraph/jpgraph.php";
include "/var/www/jpgraph/jpgraph_scatter.php";
// We are not using these callback functions because they base a change of pointsize/color on the y, or x,y values. We need to use a third value, representing age, to change the datapoints. Leave this in, in case you want to use this for something else.
function FCallback($aVal) {
// This callback will adjust the fill color and size of the datapoint according to the data value  
	if ($aVal < 3){ $c = "pink"; $pointsize=3;} elseif ($aVal<7) {$c="magenta";$pointsize=7;} else {$c="maroon"; $pointsize=9;}
	return array($pointsize,$c,$c);
}
//The difference from the ordinary SetCallback() is that the callback function specified in SetCallbackYX() gets passed both the Y and X coordinate (note the order!) as arguments.  
function FCallbackYX($aYVal,$aXVal) { 
	if ($aXVal < 3){ $c = "ccccff"; $pointsize=3;} elseif ($aXVal<7) {$c="blue";$pointsize=7;} else {$c="navy"; $pointsize=9;}
	return array($pointsize,"",$color);
}
	
//$gJpgBrandTiming=true; //This cool feature displays the time it took to plot, in the plot's LL corner; uncomment it and it works!

// $harker_ani_sql should have been created in the script that includes this one

$scale=$plotsize/100;
$margin=floor(($plotsize-100)/5); if ($margin>100) {$margin=100;} elseif ($margin<50) {$margin=50;}
$margin2=$margin; // for the top and right margins, a little smaller
$titlemargin=20;
$axistitle_fontsize=5+$scale;  
$title_fontsize=7+$scale;  
$ticklabel_fontsize=7; if ($scale > 5) {$ticklabel_fontsize++;}
$xtitlemargin=32+$scale; $ytitlemargin=$xtitlemargin+$axistitle_fontsize+4;  
$gridcolor="#c7dedf";
$titlecolor="#363535";
$subtitlecolor="#4c4b4b";
$tickcolor=$subtitlecolor;
$xtitle = $chemicals_array[$chem1]; // $key=>$val is field_name=>display_name for the chemicals
$ytitle = $chemicals_array[$chem2]; //if (strtolower(substr($ytitle,0,3))=='fe2') {$ytitle = 'Fe2O3';} // For iron the field in the table will be fe203t_calculated or something similar, we want a simpler label
$title = "$ytitle vs $xtitle Animated by Age";

// Defines where fonts are stored
DEFINE("TTF_DIR","/usr/src/fonts/");
// Define a font file that we know we have installed
DEFINE("TTF_FONTFILE","arial.ttf");

//echo "<div align=left>".nl2br($harker_ani_sql)."</div> <p />";
$rows = $db->get_results($harker_ani_sql." ORDER BY age") or die("could not execute harker_ani_sql near 50"); //echo "<p><font color=fuchsia>rs is <p>".$rs."</font><p>";
////////echo "<p><font color=red>rows returned: ".sizeof($rs)."</font>";

// Count the samples to plot, and get the min and max ages
//$count=0; $age_min=100000; $age_max=0;
//foreach ($rows as $row) {
	//$count++;
	//if ($row->age>$age_max) {$age_max=$row->age;}
	//elseif ($row->age<$age_min) {$age_min=$row->age;}
//}
//if ($age_min<0) {$age_min=0;} 
////////echo "<p><font color=blue>$count samples, age range $min to $max</font>";
	//$count=0; 
// Make 3 arrays containing all the search result samples - their x,y,z values
foreach ($rows as $row) { //$count++;
	$alldatax[]=$row->$chem1; // This is always 'sio2'
	$alldatay[]=$row->$chem2; // This is one of 9 chemicals in $chemicals_array
	$alldataz[]=$row->age; 
}
$count=sizeof($alldataz);$age_min=min($alldataz);$age_max=max($alldataz);
if ($age_min<0) {$age_min=0;}
//echo "<p>chem1 is $chem1 and chem2 is $chem2<p>harker_ani_sql $harker_ani_sql"; //<p>alldatay ";print_r($alldatay); 
 
$sample_age_range=$age_max-$age_min; 
//echo "<p><font color=blue>$count samples, age range $age_min to $age_max, sample_age_range is $sample_age_range</font>";

if ($sample_age_range<=0) { // handle case if user entered, e.g., min=55 and max=55
echo "<p /><font color=red>Cannot make an animation from sample age range $age_min to $age_max</font><p />";
} 
// get the min and max x, y values - so we can force the range for the plots, so they are all the same
$aYMin=min($alldatay);$aYMax=max($alldatay);$aXMin=min($alldatax);$aXMax=max($alldatax);$samplecount=sizeof($alldataz); 
//echo "<p>aYMin $aYMin aYMax $aYMax aXMin $aXMin aXMax $aXMax ";
$plot_age_range=$sample_age_range/$num_frames; // ceil would round up to a whole number - e.g. ceil($sample_age_range/10); 
//echo "<p />sample count $samplecount, age range of samples $sample_age_range, age range in each frame $plot_age_range";

/*//test ways to do the array: $arr = array("one", "two", "three");reset($arr);while (list(, $value) = each($arr)) {echo "Value: $value<br />";}foreach ($arr as $value) {echo "Value: $value<br />";} */

//echo "<p /><font size=1>Make a series of plots, with these minimum ages: ";
//make an array of the age minimum for each plot in the animation 
$n=$age_min;
while ($n<$age_max) {
$mins[]=$n; //echo " ".$n;  
$n+=$plot_age_range;
}
if ($age_direction == 1) {
$mins = array_reverse($mins);
// print out the reversed array for debug: 
//echo " <font color=fuchsia><br>reversed mins array to "; print_r($mins); echo "</font>";
}
//echo "</font>";
//foreach ($mins as $min) {echo "min for this plot: $min<br />";}echo $age_min; echo $age_max; 
//$return = system("sudo -u USERNAME -p PASSWORD ps -cx");

// *********************************************** MAKE THE PLOTS ************************************************
// Make several image files, each containing a plot, each representing a subset of the samples (an age range). These will be the frames in an animation.
$currenttime=time();  // use this to create a unique filename or dirname 
$basedir='plots/'.$currenttime;
//$basedir = 'test'; 
//echo "<hr>$basedir contains: ";system ('ls '.$basedir.'/*.*'); // in testing and dev, temporarily use a simpler dir name - and nuke everything in it so we can make new plots/movie
//exec('mkdir '.$basedir);
$success=system('mkdir '.$basedir);//echo "<p>Made directory $basedir ? |$success| ";
$success2 = system("chmod -R 777  \"/var/www/html/plots/$basedir\"  "); 
$basefilename=$basedir.'/harker_animation-'.$currenttime."-";
$num_plots = sizeof($mins); // the number of elements in the array of age minimums, is the number of plots we will make
$plot_num=0; // which plot in the sequence are we working on right now? use this in the image filename

/*//print out the data: echo "<table>";foreach ($rs as $row) { echo "<tr><td style='font:9px'>".$row->age." </td><td style='font: 9px;color: purple'>".$row["SIO2"]." </td><td style='font: 9px;color: green'>".$row->chem2." </td></tr>";}echo "</table>";*/

$xarray=array();$yarray=array();$zarray=array();
$ar=-1;
foreach ($mins as $plot_min) { $ar++;
	$plot_max=$plot_min+$plot_age_range;
	unset($x);unset($y);unset($z); 
	$x=array();$y=array();$z=array();
	//echo "<hr>$plot_min to $plot_max | "; echo " | "; echo $zarray[0][0];  echo " | "; 
	if ($earthchem) {
		$sql=$harker_ani_sql." AND age >= $plot_min AND age < $plot_max ";
	} elseif ($navdat) {
		$sql=$harker_ani_sql." AND age.calculated_age >= $plot_min AND age.calculated_age < $plot_max "; // age is ambiguous in navdatsqlstatement
	}    
	$lrows = $db->get_results($sql); // or die("could not execute near 122");     dies if retrieves 0 records (0 records may be valid)
	if (sizeof($lrows)>0) {
 	foreach ($lrows as $row) { 
	//echo " <font color=orange size=1> ".$row->age." </font>";
    	if ($row->age>=$plot_min && $row->age< $plot_max) {   
			array_push($z,$row->age);
			array_push($x,$row->sio2);
			array_push($y,$row->$chem2);
		    //echo " <br><font size=1>".$row->age." <font color=purple> ".$row["SIO2"]." <font color=green>".$row->chem2."</font></font></font>";
		} // if
	} // foreach
	} // if
	//echo "<p>arrays z,x,y contain: <p>"; print_r($z); echo "<p>"; print_r($x); echo "<p>"; print_r($y); "<p>";  
	$xarray[$ar]=$x;$yarray[$ar]=$y;$zarray[$ar]=$z;
	unset($x);unset($y);unset($z);
}





// THIS MAKES FRAMES WITH LAYERS OF SCATTER PLOTS, THE NEWEST LAYER HAVING THE SMALLEST POINT SIZE, SO THAT IN THE MOVIE THE POINTS APPEAR TO GROW WITH TIME, PERSIST ON THE SCREEN FOR A FEW FRAMES, THEN SHRINK - and it uses the 3 huge arrays we made, containing all the x,y,z values in the movie
if ($latency_method==2) { 
//echo "<p />$latency_method_description_2";
// Make sure the pointsize is large enough to accommodate the number of reductions in size we need to make as the points shrink and grow, if not, reset it
for ($plot_num=0;$plot_num <= sizeof($mins)-1+$latency_factor; $plot_num++) { // Make one plot image (frame for the movie) for each age range in this array
	// Make a filename for the image. The filename must end with the number in the sequence for the movie, e.g. 0000, 0001, 0002, etc. and the # of digits in this format must be consistent 
	/*if ($plot_num<10) {
	$image_filename=$basefilename.'000'.$plot_num.'.jpg';
	} elseif ($plot_num<100) {
	$image_filename=$basefilename.'00'.$plot_num.'.jpg';
	} elseif ($plot_num<1000) {
	$image_filename=$basefilename.'0'.$plot_num.'.jpg';
	} else {
	echo "<p>plot_num is greater than 1000, cannot make a movie with more than 999 plots<p>";
	}*/
	$nn=sprintf("%05d", $plot_num); $image_filename=$basefilename.$nn.".jpg"; unset($nn); // format filename so they are in alphanumeric order for the movie making software
	$datax=$xarray[$plot_num];$datay=$xarray[$plot_num];
	include "harkerAni-make_frame_with_age_layers.php";  // make the jpgraph plot with multiple scatter plots layered, showing age with different point sizes 
	//if ($display_frames) {echo "<img src='$image_filename' alt='$image_filename' hspace=20 vspace=25 border='0'>";} // display the images (one still frame)
} // for 
} // if ($latency_method==2)





// WE ARE NOT OFFERING THIS METHOD. THIS MAKES FRAMES WITH LAYERS OF SCATTER PLOTS, THE NEWEST LAYER HAVING THE SMALLEST POINT SIZE, SO THAT IN THE MOVIE THE POINTS APPEAR TO GROW WITH TIME, PERSIST ON THE SCREEN FOR A FEW FRAMES, THEN VANISH (NOT SHRINK)
if ($latency_method==10) { 
// we commented-out this variable in harkerAni.php     echo "<p />$latency_method_description_10";
for ($plot_num=0;$plot_num <= sizeof($mins)-1+$latency_factor; $plot_num++) { // Make one plot image (frame for the movie) for each age range in this array
//echo "<p><font color=purple>plot_num $plot_num - age range min $mins[$plot_num]</font>";
	// Make a filename for the image. The filename must end with the number in the sequence for the movie, e.g. 0000, 0001, 0002, etc. and the # of digits in this format must be consistent 
	if ($plot_num<10) {
	$image_filename=$basefilename.'000'.$plot_num.'.jpg';
	} elseif ($plot_num<100) {
	$image_filename=$basefilename.'00'.$plot_num.'.jpg';
	} elseif ($plot_num<1000) {
	$image_filename=$basefilename.'0'.$plot_num.'.jpg';
	} else {
	echo "<p>plot_num is greater than 1000, cannot make a movie with more than 999 plots<p>";
	}
	include ('harker_make_plot_with_age_layers2.php'); // make the jpgraph plot
	if ($display_frames) {echo "<img src='$image_filename' alt='$title' hspace=20 vspace=25 border='0'>";} // display the plot
} // for 
} // if ($latency_method==10)




// THIS MAKES FRAMES WITH DATAPOINTS IN A SINGLE SIZE, WITH OPTIONAL (USER-DEFINED) LATENCY (# of frames through which the datapoints persist)
if  ($latency_method==1) { // if $layer_age_ranges else
//$display_description = str_ireplace("<i>n</i>", $latency_multiple, $latency_method_description_1);echo "<p />$display_description";
$plot_num=0;
foreach ($mins as $min) {	
	unset($datax);unset($datay);unset($dataz);$datax=array();$datay=array();$dataz=array();
	$datax=$xarray[$plot_num];$datay=$yarray[$plot_num];$dataz=$zarray[$plot_num];

/// ADD TO THE RANGE, FOR THE LATENCY IF THE USER ASKED FOR IT 
	////////echo "<font color=cyan> plot_num $plot_num, latency_multiple $latency_multiple ";
	////////echo " count:".sizeof($dataz);
	//$m=0; // index for the array of minimums, $mins
	$historical_plot_num=$plot_num; // the ae range we are making a plot for, now; we might add previous age ranges if $latency_multiple > 0
	
	//echo "<p>historical_plot_num:$historical_plot_num plot_num:$plot_num latency_multiple:$latency_multiple ";
	for ($i=0;$i<$latency_multiple-1;$i++) { //while ($latency_multiple>0 && $m<$latency_multiple) { // default is 0, for no age range latency, but if it's 1 or more, add historial age ranges to the dataset
	$historical_plot_num--; //echo " | historical_plot_num:$historical_plot_num ";
	//if ($pn>=0 && sizeof($zarray[$pn]>0)) {
	//echo " latency_multiple:$latency_multiple ";
	if ($historical_plot_num>=0) { //echo "<p>historical_plot_num $historical_plot_num latency_multiple $latency_multiple merging <p>";
	$datax=array_merge($datax,$xarray[$historical_plot_num]); 
	$datay=array_merge($datay,$yarray[$historical_plot_num]); 
	$dataz=array_merge($dataz,$zarray[$historical_plot_num]); 
	} // if
	////////echo " ar:$ar count:".sizeof($dataz);
	} // for
	//echo "</font>";
	
	////////echo "<br><font color=orange> "; print_r($datax);echo" </font>";
	////////echo "<br><font color=red> "; print_r($datay);echo" </font>";
	////////echo "<br><font color=magenta> "; print_r($dataz);echo" </font>";

// if we found data for this age range, make a plot 
	//echo '<div width=400 height=600 style="background-color:#cccccc">';
	//if (isset($image_filename)) {$previous_image_filename=$image_filename;} else {$previous_image_filename="watermark.png";}
	//echo "<font size=0 color=red>".$previous_image_filename."</font>";
	if ($plot_num<10) {
	$image_filename=$basefilename.'000'.$plot_num.'.jpg';
	} elseif ($plot_num<100) {
	$image_filename=$basefilename.'00'.$plot_num.'.jpg';
	} elseif ($plot_num<1000) {
	$image_filename=$basefilename.'0'.$plot_num.'.jpg';
	} else {
	echo "<p>plot_num is greater than 1000, cannot make a movie with more than 999 plots<p>"; exit; // num_frames was forced to 1...1000 elsewhere so this should never happen
	}	
	include ('harkerAni-make_frame.php'); // make the jpgraph plot
	if ($display_frames) { // display the still frame, if the user checked this box in the form 
		echo "<hr><font size=1>".sizeof($datax)." samples in this plot<p />$image_filename</font><br /><img src='$image_filename' alt='$title' hspace=20 vspace=25 border='0'>";
	} 
	unset($datax);unset($datay);unset($dataz);
	$plot_num++;
} // foreach $mins as $min
} // if $latency_method==1

/*
// OLD CODE FOR MAKING THE MOVIE 
// Now make the movie, using system calls to execute mencoder and ffmpeg. Use exec() because you can send the output to a variable instead of seeing it on the screen
echo "<br /><font color=#cccccc size=0>";
//echo "<font color=blue size=0><p />frames: ";system(" ls /var/www/html/harker/$basedir/*.* ");echo "</font>";
exec(" cp /var/www/html/harker/$image_filename /var/www/html/harker/$basedir/movie_last_frame.jpg", $foo,$bar);
exec( "/usr/local/bin/mencoder 'mf://$basedir/ani*.jpg' -mf fps=$num_frames_per_second:type=jpg -ovc copy -oac copy -o /var/www/html/harker/$basedir/output.avi", $foo,$bar);
exec( "/usr/local/bin/mencoder /var/www/html/harker/$basedir/output.avi -ovc lavc -oac lavc -ffourcc DX50 -o /var/www/html/harker/$basedir/newout.avi", $foo,$bar);
exec( "/usr/bin/ffmpeg -i /var/www/html/harker/$basedir/newout.avi /var/www/html/harker/$basedir/movie.flv", $foo,$bar); 
exec( "/usr/bin/ffmpeg -i /var/www/html/harker/$basedir/newout.avi /var/www/html/harker/$basedir/movie.flv", $foo,$bar);
exec( "/usr/bin/ffmpeg -i /var/www/html/harker/$basedir/movie.flv -f mjpeg -t 0.001 /var/www/html/harker/$basedir/movie_first_frame.jpg", $foo,$bar); // make a jpeg from the first frame in the movie, to use for the splash image in the player 
exec( "cp /var/www/html/$image_filename /var/www/html/harker/$basedir/movie_last_frame.jpg", $foo,$bar); // DOES NOT WORK make a jpeg from the last frame in the movie, to use for the splash image in the player 
exec( "/usr/local/bin/flvtool2 -U /var/www/html/harker/$basedir/movie.flv", $foo,$bar);
echo "</font>";
//echo "<p />last filename: $image_filename ";
//echo "<p><font color=orange size=0><p />frames: ";system(" ls -l /var/www/html/harker/$basedir/*.* ");echo "</font>";
// Now show the Flash player and load the movie 
$playersize=$plotsize+23;
echo "<br /><a name='movie'></a>
  <object type=\"application/x-shockwave-flash\" data=\"http://neko.kgs.ku.edu/FlowPlayerLP.swf\" width=\"$plotsize\" height=\"$playersize\" id=\"FlowPlayer\">
        <param name=\"allowScriptAccess\" value=\"sameDomain\" />";
        //echo "<param name=\"movie\" value=\"http://neko.kgs.ku.edu/FlowPlayerLP.swf\" /> ";
		echo "<param name=\"movie\" value\"http://matisse.kgs.ku.edu/flowplayer/FlowPlayerLP.swf\" />
        <param name=\"quality\" value=\"high\" />
        <param name=\"scale\" value=\"noScale\" />
        <param name=\"wmode\" value=\"transparent\" />
		";
		//echo "<param name=\"flashvars\"  value=\"initialScale=orig&baseURL=http://neko.kgs.ku.edu/harker/$basedir&videoFile=movie.flv&splashImageFile=http://neko.kgs.ku.edu/harker/$basedir/movie_first_frame.jpg&loop=false&autoBuffering=true \" /> ";

		echo "
		<param name=\"flashvars\"  value=\"initialScale=orig&baseURL=http://matisse.kgs.ku.edu/plots/$basedir&videoFile=movie.flv&loop=false&autoBuffering=true \" /> ";

		// this version has a splash image      echo "<param name=\"flashvars\"  value=\"initialScale=orig&baseURL=http://neko.kgs.ku.edu/harker/$basedir&videoFile=movie.flv&splashImageFile=http://neko.kgs.ku.edu/harker/$basedir/movie_last_frame.jpg&loop=false&autoBuffering=true \" /> ";
  echo "
  </object>
  ";
echo "<p /><a href=http://neko.kgs.ku.edu/harker/$basedir/newout.avi>AVI file</a> | <a href='#top'>top of page</a> | <a href=http://navdat.kgs.ku.edu/NavdatSearch/>new search</a><p />";
*/


?>
