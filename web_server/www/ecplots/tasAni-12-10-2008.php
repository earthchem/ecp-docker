<?
/* This is a variation on tas.php. It creates a movie of a TAS plot where age is represented by the animation. 
It is possible to arrive at this page from different places:
1. Navdat Output Data page  
2. Earthchem Results page
The still TAS graph might also originate from individual sample detail page GetSample.cfm on navdat or earthchem, but one sample point is not enough for an animation so we don't offer it, we don't come here from that page.
Data is received via a form - $_POST.
We set flags, $navdat and $earthchem, to let us know which website sent us to this page.
For Navdat, we receive a primary key pkey, and use it to look up the query in a database table.
For Earchchem, we receive a query as a form field. 
*/

$earthchem=0;$navdat=0; // We'll set a flag to indicate which website/page originally sent us to this page 
if (isset($_GET["referring_website"])) {$referring_website=$_GET["referring_website"];}
elseif (isset($_POST["referring_website"])) {$referring_website=$_POST["referring_website"];}
$earthchem=false;$navdat=false;if ($referring_website=='earthchem') {$earthchem=true;} elseif ($referring_website=='navdat') {$navdat=true;} else {echo '<p>There is a problem. Please start a new search and try again.<p>'; exit;}
// Include a page header 
if ($earthchem) { 
	include "../earthchemphp2/includes/ks_head.html";
	echo '<div name="page_div">';
	include "includes/earthchem_db.php"; // database driver, and db connect for EarthChem
} elseif ($navdat) {
	include "../navdat/NewHeader.cfm";
	echo '<div name="page_div" style="padding:20">';
	include "includes/navdat_db.php"; // database driver, and db connect for EarthChem
} // Note: We do not ever come here from GetSample.cfm which uses only 1 datapoint (too few to make a movie)



// Get or build the sql  
if ($earthchem) {
	$tas_ani_sql=$_POST['tas_ani_sql']; // Passed as a hidden variable in the form on this page. For both EC and Navdat.  
} elseif ($navdat) {
	include "includes/get_pkey.php"; // get the search_query table record's primary key in case we need it 
	if (isset($_POST['tas_ani_sql'])) { // from the form on this page
		$tas_ani_sql = $_POST['tas_ani_sql'];
	} elseif (isset($_POST['navdatsqlstatement'])) { // from the Output Data page 
		$navdatsqlstatement=$_POST['navdatsqlstatement']; //echo "<p>$navdatsqlstatement<p>";
		//$navdat_tas_ani_sql = $_POST["navdat_tas_ani_sql"]; // if we came originally from Navdat, and immediately from the form below (user is replotting on navdat)
		$pos1=stripos($navdatsqlstatement,"FROM ");
		$tas_ani_sql = substr($navdatsqlstatement,$pos1);
		$tas_ani_sql = "SELECT age.age, norm.sio2, norm.k2o+norm.na2o as alkali $tas_ani_sql AND age.age IS NOT NULL AND norm.sio2 BETWEEN 36 AND 84 AND (norm.na2o+norm.k2o) BETWEEN 0 AND 18 ORDER BY norm.age, norm.sio2, alkali ";
		//SELECT samp.age, feo.n_feo_sio2 AS sio2, feo.n_feo_na2o + feo.n_feo_k2o AS alkali, samp.url
	} elseif (isset($_POST['pkey'])) { // if something went wrong, we might have the pkey at least 
		$navdatsqlstatement = $db->get_var("SELECT navdatsqlstatement FROM search_query WHERE pkey = ".$_POST['pkey']);
		$pos1=stripos($navdatsqlstatement,"FROM ");
		$tas_ani_sql = substr($navdatsqlstatement,$pos1);
		$tas_ani_sql = "SELECT age.age, norm.sio2, norm.k2o+norm.na2o as alkali $tas_ani_sql AND age.age IS NOT NULL AND norm.sio2 BETWEEN 36 AND 84 AND (norm.na2o+norm.k2o) BETWEEN 0 AND 18 ORDER BY norm.age, norm.sio2, alkali ";
	} else { 
		// Trouble. We tried every way to make a sql statement for navdat but lack info about the search query.
	}
}
if (isset($_GET['display_sql'])) {echo "<p>$tas_ani_sql<p>"; } // let developer add a url variable to show the sql for dev & test purposes 

if ($tas_ani_sql=="") {echo "<p>There is a problem. No search query data is defined. Please start a new search the try again. <p>"; exit; }


include ("/var/www/html/jpgraph/jpgraph.php"); // jpgraph driver
include ("/var/www/html/jpgraph/jpgraph_scatter.php"); // jpgraph scatter plot

//echo "<h1>Total Alkali vs Silica Animated by Age</h1>";
/////debug echo "<p /><a href='#movie'>movie</a><br><font size=1>(link will not work until movie has been created)</font><p />";
if (isset($_POST["plotsize"])) {$plotsize=$_POST['plotsize'];} else {$plotsize=600;} // from the form below
if (isset($_POST["age_direction"])) {$age_direction=$_POST["age_direction"];} else {$age_direction=1;} // 0 is youngest to oldest, 1 is the reverse
if (isset($_POST["num_frames"])) {$num_frames=floor($_POST["num_frames"]);} else {$num_frames=10;} 
if ($num_frames<1 || $num_frames>1000) {$num_frames=10; echo "<p>Number of frames must be between 1 and 1000. Changing to $num_frames."; }
if (isset($_POST["num_frames_per_second"])) {$num_frames_per_second=floor($_POST["num_frames_per_second"]);} else {$num_frames_per_second = 1;} if ($num_frames_per_second<1 || $num_frames_per_second>25) {$num_frames_per_second=1; echo "<p><font color=green size=1>Number of frames per second must be between 1 and 25. Changing to $num_frames_per_second.</font>"; }
if (isset($_POST["persist_frames"])) {$persist_frames=floor($_POST["persist_frames"]);}  else {$persist_frames=1;} if ($persist_frames<0) {$persist_frames=1;}
if (isset($_POST["latency_method"])) {$latency_method=$_POST["latency_method"];} else {$latency_method=2;} 
if ($latency_method==2 && $num_frames<10) {$num_frames=10; echo "<p>Number of frames for this method of showing age must be at least $num_frames. Changing to $num_frames."; }
if (isset($_POST["display_frames"])) {$display_frames=$_POST["display_frames"];} else {$display_frames=false;} // eileen in the end make the default 0 
//if (isset($_POST['type'])) {$type=$_POST["type"];} // userdefined plot points or stock_icon icon, from the form below
if (isset($_POST["pointsize"])) {$pointsize=$_POST["pointsize"];} elseif ($latency_method==2) {$pointsize=5;} else {$pointsize=3;} // if ($latency_method==2) {$pointsize=5;} } // from the form below
if ($latency_method==2 && $pointsize<5) {$pointsize=5; echo "<p>Point size for this method of showing age must be at least $pointsize. Changing to $pointsize."; }
if ($latency_method==1 && $pointsize<($latency_multiple+1)) {$pointsize=$latency_multiple+1; echo "<p>Point size for this method of showing age must be larger than <i>n</i>. Changing to $pointsize."; }
if (isset($_POST['fillcolor'])) {$fillcolor=$_POST["fillcolor"];} else {$fillcolor="#00ff00";}  // from the form below 
/* commented out option for indexed color, we may decide to offer this in the future:
$customfillcolor=trim($_POST["customfillcolor"]);
if (strlen($customfillcolor)>0) {
	$customfillcolor = str_replace("#", "", $customfillcolor); //strip any #
	if (strlen($customfillcolor)==6) {
		$customfillcolor="#".$customfillcolor;  //add a leading #
		$fillcolor=$customfillcolor; // set the color to the valid custom color the user provided
	}
}*/

if (isset($_POST['strokecolor'])) {$strokecolor=$_POST["strokecolor"];} else {$strokecolor="#000000";}  // from the form below 
if (isset($_POST["rocknamecolor"])) {$rocknamecolor=$_POST["rocknamecolor"];} else {$rocknamecolor="#000000";} // default color for rock names & lines
if (isset($_POST["pointtype"])) {$pointtype=$_POST["pointtype"];} else {$pointtype="MARK_DIAMOND";} // from the form below
if (isset($_POST["icon"])) {$icon=$_POST["icon"]; if ($icon>"") {$pointtype=$icon;}} // If they chose an icon, override the pointtype with it 

//debug echo "<div><font size=1 color=red >latency_method $latency_method &bull; plot width $plotsize &bull; fillcolor $fillcolor &bull; strokecolor $strokecolor size $pointsize &bull; num_frames $num_frames num_frames_per_second $num_frames_per_second/sec &bull; age_direction $age_direction &bull; fillcolor $fillcolor &bull; strokecolor $strokecolor &bull; rocknamecolor $rocknamecolor </font></div>";

$latency_method_description_2="<font size=1 color='blue'>Datapoints grow in size over 4 frames, persist through 3 more frames, shrink over 3 frames.</font>"; 
$latency_method_description_1="<font size=1 color='green'>Datapoints appear at full size and persist through <i>n</i> frames. </font>";

$sidex=$plotsize; $sidey=floor($plotsize*4/5); // plot dimension is a 4:3 width:height ratio
$scale=$plotsize/100;
$margin=floor(($plotsize)/10); if ($margin>100) {$margin=100;} elseif ($margin<60) {$margin=60;}
$margin2=floor($margin*.75); // for the top and right margins, a little smaller
$sidex=$sidex+$margin+$margin2;$sidey=$sidey+$margin+margin2;
$axistitle_fontsize=5+$scale; //10; if ($plotsize<500) {$axistitle_fontsize-=2;} elseif ($plotsize>600) {$axistitle_fontsize+=2;}
$title_fontsize=7+$scale; //14; if ($plotsize<500) {$title_fontsize-=2;} elseif ($plotsize>600) {$title_fontsize+=2;}
$ticklabel_fontsize=7; if ($scale > 5) {$ticklabel_fontsize++;}
$xtitlemargin=32+$scale; $ytitlemargin=$xtitlemargin+$axistitle_fontsize+4; 
$gridcolor="#c7dedf";
$titlecolor="#363535";
$subtitlecolor="#4c4b4b";
$tickcolor=$subtitlecolor;
$rockname_fontsize=$plotsize/100; if ($rockname_fontsize<7) {$rockname_fontsize=7;}

// Scale for a TAS diagram is fixed. These limits were added to the sql.
//$ymin = 0; $ymax = 18; $xmin = 36; $xmax = 84;





// Start the page. We now have the calc for the player height which we need for the div that holds the movie. 
echo "<h1>Total Alkali vs. Silica Animated by Age</h1>";
// The div that will hold the movie: 
echo '<div style="height:50px"></div>';
echo '<a name="player"></a>'; // The form will jump to this anchor, so the user can see the menu bar on the player
echo '<div id="div_movie" style="text-align:center;width:100%;min-height:'.$sidey.'">';  //The div will contain the movie
echo '<img src="images/progress.gif" style="margin:100px 20px">'; // animated gif 
echo '</div>';
ob_flush();flush();




$rs = $db->get_results($tas_ani_sql) or die ("COULD NOT EXECUTE tas_ani_sql STATEMENT NEAR 131"); // execute the sql statement we got from navdat or earthchem 
$alldatax=array();$alldatay=array();$alldataz=array(); // put all the x,y,z values from our dataset of samples to plot, into 3 arrays 
foreach ($rs as $row) {
	//$SiO2=$row->sio2; $Alkali=$row->alkali; //$row["NA2O"] + $row["K2O"];
	//if ($SiO2 >= $xmin && $SiO2 <= $xmax && $Alkali > 0 && $Alkali <= $ymax) {
	$count++;
	$alldatax[]=$row->sio2 + 0; // SiO2
	$alldatay[]=$row->alkali + 0; // Total Alkali 
	$alldataz[]=$row->age + 0; // sample age 
	//}
}




$count=count($alldatax);
$age_max=max($alldataz); $age_min=min($alldataz); $sample_age_range=($age_max-$age_min); 
//echo "<p><font color=blue>$count samples, age range $age_min to $age_max, sample_age_range is $sample_age_range</font>";



if ($sample_age_range<=0) { // handle case if user entered, e.g., min=55 and max=55
echo "<p /><font color=red>Cannot make a TAS plot animated by Age from samples ranging in age from $age_min to $age_max</font><p />"; 
} else {

ob_flush();flush();

// get the min and max x, y values - so we can force the range for the plots, so they are all the same
$aYMin=min($alldatay); $aYMax=max($alldatay); 
$aXMin=min($alldatax); $aXMax=max($alldatax);
$age_min=min($alldataz);
$samplecount=sizeof($alldataz); 
$plot_age_range=$sample_age_range/$num_frames; // ceil would round up to a whole number - e.g. ceil($sample_age_range/10); 
/////debug echo "<p><font color=blue>$count samples, age range $age_min to $age_max</font>";
	$count=0; 
/////debug echo "<p>Make plots with these min ages ";
$n=$age_min;
while ($n<$age_max) {
$mins[]=$n; /////debug echo " ".$n;  
$n+=$plot_age_range;
}
if ($age_direction == 1) { 
$mins = array_reverse($mins); // oldest to youngest (highest number, i.e. years before present day) to youngest ( 0 = present day)
/////debug echo "<br /><font size=1 color=#cccccc><br>reversed mins array to "; print_r($mins); echo "</font>";
}

$xarray=array();$yarray=array();$zarray=array();
$ar=-1;
foreach ($mins as $plot_min) { $ar++;
	$plot_max=$plot_min+$plot_age_range;
	unset($x);unset($y);unset($z); 
	$x=array();$y=array();$z=array();
	/////debug echo "<hr>$plot_min to $plot_max | "; echo " | "; echo $zarray[0][0];  echo " | "; 
	foreach ($rs as $row) { 
	//echo " <font color=orange size=1> ".$row->age." </font>";
    	if ($row->age>=$plot_min && $row->age< $plot_max) 
		{   
			array_push($z,$row->age+0); // force to a numeric type 
			array_push($x,$row->sio2+0);
			array_push($y,$row->alkali+0);
		   /////debug echo " <br><font size=1>".$row->age." <font color=purple> ".$row->sio2." <font color=green>".$row->alkali."</font></font></font>";
		}
	}
	/////debug echo "<p>arrays z,x,y contain: <p>"; print_r($z); echo "<p>"; print_r($x); echo "<p>"; print_r($y); "<p>";  
	$xarray[$ar]=$x;$yarray[$ar]=$y;$zarray[$ar]=$z;
	unset($x);unset($y);unset($z);
}


// Make several image files, each containing a plot, each representing a subset of the samples (an age range). These will be the frames in an animation.
$currenttime=time();  // use this to create a unique filename or dirname 
$basedir='plots/'.$currenttime;
//$basedir = 'test'; 
//echo "<hr>$basedir contains: ";system ('ls '.$basedir.'/*.*'); // in testing and dev, temporarily use a simpler dir name - and nuke everything in it so we can make new plots/movie
$success=system('mkdir '.$basedir); //echo "<p>Made directory $basedir ? |$success| ";
//if ($success) {} else {echo "<p>mkdir not successful ";}
$success2 = system("chmod -R 777  \"/var/www/html/plots/$basedir\"  "); //echo "<p>chmod directory $basedir ? $success ";
$basefilename=$basedir.'/animation-'.$currenttime."-"; //echo "<p>226 basefilename $basefilename ";
//echo "<p>at 254, system directory: ";print system('dir');

//echo "<p>229 latency method latency_method ";

// THIS MAKES FRAMES WITH LAYERS OF SCATTER PLOTS, THE NEWEST LAYER HAVING THE SMALLEST POINT SIZE, SO THAT IN THE MOVIE THE POINTS APPEAR TO GROW WITH TIME, PERSIST ON THE SCREEN FOR A FEW FRAMES, THEN SHRINK - and it uses the 3 huge arrays we made, containing all the x,y,z values in the movie

// Make sure the pointsize is large enough to accommodate the number of reductions in size we need to make as the points shrink and grow, if not, reset it
$image_filename="";
for ($plot_num=0;$plot_num <= sizeof($mins)-1+$latency_multiple; $plot_num++) { // Make one plot image (frame for the movie) for each age range in this array
	// Make a filename for the image. The filename must end with the number in the sequence for the movie, e.g. 0000, 0001, 0002, etc. and the # of digits in this format must be consistent 
	//echo "<p><font color=orange size=3>plot_num $plot_num</font>";
	//echo "<p> |$fillcolor| |$strokecolor| |$rocknamecolor| |$subtitlecolor| |$gridcolor| |$tickcolor| ";
	// Make the filename including the dir: (the animation script must recognize an alphanumeric sequence, so we need the padding with 0s)
	$nn=sprintf("%05d", $plot_num); $image_filename=$basefilename.$nn.".jpg"; unset($nn);
	include "tasAni-frame.php"; // make the jpgraph plot with multiple scatter plots layered, showing age with different point sizes 
	if ($display_frames) { // show each frame
 	echo '<div style="padding:50px 0px;text-align:center"><span style="float:left">Frame '.$plot_num.'</span>'; 
	echo '<a style="float:right;text-decoration:none" href="#player">movie</a><br clear="all" />'; 	// The movie will be way down the page if we display all the frames, so provide a link
	echo '<a href="'.$image_filename.'"><img border="0" src="'.$image_filename.'"></a></div><hr>';
	//echo '<br>'.$image_filename.'<img src="'.$image_filename.'" alt="'.$image_filename."' width=300 hspace=20 vspace=25 border=1>";
	} // display the images (one still frame)
} // for $plot_num;
 
exec(" cp /var/www/html/plots/$image_filename /var/www/html/plots/$basedir/movie_last_frame.jpg",$foo,$bar); // Copy the last frame




// ********************************************** MAKE THE ANIMATION *************************************************************
// Now make the movie, using system calls to execute mencoder and ffmpeg. Use exec() because you can send the output to a variable instead of seeing it on the screen
// print the files in our animation subdirectory to test:   
echo "<font color=blue><p />";exec(" ls $basedir/*.* ",$foo,$bar);echo " </font>";
exec( "/usr/local/bin/mencoder 'mf://$basedir/*.jpg' -mf fps=$num_frames_per_second:type=jpg -ovc copy -oac copy -o /var/www/html/plots/$basedir/output.avi",$foo,$bar);  
exec( "/usr/local/bin/mencoder /var/www/html/plots/$basedir/output.avi -ovc lavc -oac lavc -ffourcc DX50 -o /var/www/html/plots/$basedir/newout.avi",$foo,$bar);  
exec( "/usr/bin/ffmpeg -i /var/www/html/plots/$basedir/newout.avi /var/www/html/plots/$basedir/movie.flv",$foo,$bar);   
exec( "/usr/bin/ffmpeg -i /var/www/html/plots/$basedir/movie.flv -f mjpeg -t 0.001 ",$foo,$bar);   // the splash page from the last frame does not work , work on this Eileen 
exec( "/usr/bin/flvtool2 -U /var/www/html/plots/$basedir/movie.flv",$foo,$bar);  
// link t the movie: echo '<br><a href="'.$basedir.'/movie.flv">'.$basedir.'/movie.flv</a>';
// show the last filename: echo "<p />last filename: $image_filename ";
// show the files in the dir: echo "<p><font color=orange size=0><p />frames: ";system(" ls -l /var/www/html/plots/$basedir/*.* ");echo "</font>";
// *************************************** LOAD THE PLAYER AND THE MOVIE *******************************************
?>
<script language="javascript"> 
/** 
 * flashembed 0.31. Adobe Flash embedding script
 * 
 * http://flowplayer.org/tools/flash-embed.html
 *
 * Copyright (c) 2008 Tero Piirainen (tipiirai@gmail.com)
 *
 * Released under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 * 
 * >> Basically you can do anything you want but leave this header as is <<
 *
 * version 0.01 - 03/11/2008 
 * version 0.31 - Tue Jul 22 2008 06:30:31 GMT+0200 (GMT+02:00)
 */
function flashembed(root,userParams,flashvars){function getHTML(){var html="";if(typeof flashvars=='function'){flashvars=flashvars();}if(navigator.plugins&&navigator.mimeTypes&&navigator.mimeTypes.length){html='<embed type="application/x-shockwave-flash" ';if(params.id){extend(params,{name:params.id});}for(var key in params){if(params[key]!==null){html+=[key]+'="'+params[key]+'"\n\t';}}if(flashvars){html+='flashvars=\''+concatVars(flashvars)+'\'';}html+='/>';}else{html='<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" ';html+='width="'+params.width+'" height="'+params.height+'"';if(!params.id&&document.all){params.id="_"+(""+Math.random()).substring(5);}if(params.id){html+=' id="'+params.id+'"';}html+='>';html+='\n\t<param name="movie" value="'+params.src+'" />';params.id=params.src=params.width=params.height=null;for(var k in params){if(params[k]!==null){html+='\n\t<param name="'+k+'" value="'+params[k]+'" />';}}if(flashvars){html+='\n\t<param name="flashvars" value=\''+concatVars(flashvars)+'\' />';}html+="</object>";if(debug){alert(html);}}return html;}function init(name){var timer=setInterval(function(){var doc=document;var el=doc.getElementById(name);if(el){flashembed(el,userParams,flashvars);clearInterval(timer);}else if(doc&&doc.getElementsByTagName&&doc.getElementById&&doc.body){clearInterval(timer);}},13);return true;}function extend(to,from){if(from){for(key in from){if(from.hasOwnProperty(key)){to[key]=from[key];}}}}var params={src:'#',width:'100%',height:'100%',version:null,onFail:null,expressInstall:null,debug:false,bgcolor:'#ffffff',allowfullscreen:true,allowscriptaccess:'always',quality:'high',type:'application/x-shockwave-flash',pluginspage:'http://www.adobe.com/go/getflashplayer'};if(typeof userParams=='string'){userParams={src:userParams};}extend(params,userParams);var version=flashembed.getVersion();var required=params.version;var express=params.expressInstall;var debug=params.debug;if(typeof root=='string'){var el=document.getElementById(root);if(el){root=el;}else{return init(root);}}if(!root){return;}if(!required||flashembed.isSupported(required)){params.onFail=params.version=params.expressInstall=params.debug=null;root.innerHTML=getHTML();return root.firstChild;}else if(params.onFail){var ret=params.onFail.call(params,flashembed.getVersion(),flashvars);if(ret){root.innerHTML=ret;}}else if(required&&express&&flashembed.isSupported([6,65])){extend(params,{src:express});flashvars={MMredirectURL:location.href,MMplayerType:'PlugIn',MMdoctitle:document.title};root.innerHTML=getHTML();}else{if(root.innerHTML.replace(/\s/g,'')!==''){}else{root.innerHTML="<h2>Flash version "+required+" or greater is required</h2>"+"<h3>"+(version[0]>0?"Your version is "+version:"You have no flash plugin installed")+"</h3>"+"<p>Download latest version from <a href='"+params.pluginspage+"'>here</a></p>";}}function concatVars(vars){var out="";for(var key in vars){if(vars[key]){out+=[key]+'='+asString(vars[key])+'&';}}return out.substring(0,out.length-1);}function asString(obj){switch(typeOf(obj)){case'string':return'"'+obj.replace(new RegExp('(["\\\\])','g'),'\\$1')+'"';case'array':return'['+map(obj,function(el){return asString(el);}).join(',')+']';case'function':return'"function()"';case'object':var str=[];for(var prop in obj){if(obj.hasOwnProperty(prop)){str.push('"'+prop+'":'+asString(obj[prop]));}}return'{'+str.join(',')+'}';}return String(obj).replace(/\s/g," ").replace(/\'/g,"\"");}function typeOf(obj){if(obj===null||obj===undefined){return false;}var type=typeof obj;return(type=='object'&&obj.push)?'array':type;}if(window.attachEvent){window.attachEvent("onbeforeunload",function(){__flash_unloadHandler=function(){};__flash_savedUnloadHandler=function(){};});}function map(arr,func){var newArr=[];for(var i in arr){if(arr.hasOwnProperty(i)){newArr[i]=func(arr[i]);}}return newArr;}return root;}if(typeof jQuery=='function'){(function($){$.fn.extend({flashembed:function(params,flashvars){return this.each(function(){flashembed(this,params,flashvars);});}});})(jQuery);}flashembed=flashembed||{};flashembed.getVersion=function(){var version=[0,0];if(navigator.plugins&&typeof navigator.plugins["Shockwave Flash"]=="object"){var _d=navigator.plugins["Shockwave Flash"].description;if(typeof _d!="undefined"){_d=_d.replace(/^.*\s+(\S+\s+\S+$)/,"$1");var _m=parseInt(_d.replace(/^(.*)\..*$/,"$1"),10);var _r=/r/.test(_d)?parseInt(_d.replace(/^.*r(.*)$/,"$1"),10):0;version=[_m,_r];}}else if(window.ActiveXObject){try{var _a=new ActiveXObject("ShockwaveFlash.ShockwaveFlash.7");}catch(e){try{_a=new ActiveXObject("ShockwaveFlash.ShockwaveFlash.6");version=[6,0];_a.AllowScriptAccess="always";}catch(ee){if(version[0]==6){return;}}try{_a=new ActiveXObject("ShockwaveFlash.ShockwaveFlash");}catch(eee){}}if(typeof _a=="object"){_d=_a.GetVariable("$version");if(typeof _d!="undefined"){_d=_d.replace(/^\S+\s+(.*)$/,"$1").split(",");version=[parseInt(_d[0],10),parseInt(_d[2],10)];}}}return version;};flashembed.isSupported=function(version){var now=flashembed.getVersion();var ret=(now[0]>version[0])||(now[0]==version[0]&&now[1]>=version[1]);return ret;};
</script>
<?php
$playerwidth=$sidex;$playerheight=$sidey+33;
echo '<script language="javascript">
flashembed("div_movie", 
		/* 
			first argument supplies standard Flash parameters. See full list:
			http://kb.adobe.com/selfservice/viewContent.do?externalId=tn_12701
		*/
		{
			src:"http://matisse.kgs.ku.edu/flowplayer/FlowPlayerDark.swf",
			width: '.$playerwidth.',
			height: '.$playerheight.'
		},
		
		/*
			second argument is Flowplayer specific configuration. See full list:
			http://flowplayer.org/player/configuration.html
		*/
		{config: {   
			autoPlay: false,
			loop: false, 
			showMenu: true, 
			autoBuffering: true,
			splashImageFile: "http://matisse.kgs.ku.edu/plots/'.$basedir.'/movie_last_frame.jpg", 
			usePlayOverlay: false, 
			controlBarGloss: "high", 
			initialScale: "scale",
			videoFile: "http://matisse.kgs.ku.edu/plots/'.$basedir.'/movie.flv"
		}} 
	);
	</script>	
	
';

/* This code that uses the player on neko works 
echo "<div align=center> ";
echo "<h1>1 FlowPlayerLP.swf on neko</h1>";

echo "<br /><a name='movie'></a><br />";
echo "  <object type=\"application/x-shockwave-flash\" data=\"http://neko.kgs.ku.edu/FlowPlayerLP.swf\" width=\"$playerwidth\" height=\"$playerheight\" id=\"FlowPlayer\">
        <param name=\"allowScriptAccess\" value=\"sameDomain\" />
        <param name=\"movie\" value=\"http://neko.kgs.ku.edu/FlowPlayerLP.swf\" />
        <param name=\"quality\" value=\"high\" />
        <param name=\"scale\" value=\"noScale\" />
        <param name=\"wmode\" value=\"transparent\" />
		";
		echo "
		<param name=\"flashvars\"  value=\"initialScale=orig&baseURL=http://matisse.kgs.ku.edu/plots/$basedir&videoFile=movie.flv&loop=false&autoBuffering=true \" /> ";
		// this version has a splash image      echo "<param name=\"flashvars\"  value=\"initialScale=orig&baseURL=http://neko.kgs.ku.edu/plots/$basedir&videoFile=movie.flv&splashImageFile=http://neko.kgs.ku.edu/plots/$basedir/movie_last_frame.jpg&loop=false&autoBuffering=true \" /> ";
echo "
</object>
</div>";
*/



echo '<div align="center"> ';
if ($latency_method==1) {
	$display_description = str_ireplace("<i>n</i>", $latency_multiple, $latency_method_description_1);echo "<p />$display_description";
} elseif ($latency_method==2) {
	echo "<p />$latency_method_description_2";
}
echo '<p />The movie plays on an embedded player. You can also play an <a href="http://matisse.kgs.ku.edu/plots/'.$basedir.'/newout.avi">AVI movie</a>.</div>'; // Offer the AVI version to click on, too








/* The old way to make the movie 
// **************************** MAKE THE MOVIE ******************************************************************
// Now make the movie, using system calls to execute mencoder and ffmpeg. Use exec() because you can send the output to a variable instead of seeing it on the screen
// print the files in our animation subdirectory   echo "<font color=blue size=0><p />frames: ";exec(" ls /var/www/html/plots/$basedir/*.* ",$foo,$bar);echo "</font>";
exec( "/usr/local/bin/mencoder 'mf://$basedir/ani*.jpg' -mf fps=$num_frames_per_second:type=jpg -ovc copy -oac copy -o /var/www/html/plots/$basedir/output.avi",$foo,$bar);  
exec( "/usr/local/bin/mencoder /var/www/html/plots/$basedir/output.avi -ovc lavc -oac lavc -ffourcc DX50 -o /var/www/html/plots/$basedir/newout.avi",$foo,$bar);  
exec( "/usr/bin/ffmpeg -i /var/www/html/plots/$basedir/newout.avi /var/www/html/plots/$basedir/movie.flv",$foo,$bar);   
exec( "/usr/bin/ffmpeg -i /var/www/html/plots/$basedir/movie.flv -f mjpeg -t 0.001 ",$foo,$bar);   // the splash page from the last frame does not work , work on this Eileen 
exec( "/usr/bin/flvtool2 -U /var/www/html/plots/$basedir/movie.flv",$foo,$bar);  
// Now show the Flash player and load the movie 
echo "<div align=center> 
";
$playerheight=$playerheight+23; $playerheight+=10; // initialized per $sidex and $sidey above 
echo "<br /><a name='movie'></a><br />";
echo "  <object type=\"application/x-shockwave-flash\" data=\"http://neko.kgs.ku.edu/FlowPlayerLP.swf\" width=\"$playerwidth\" height=\"$playerheight\" id=\"FlowPlayer\">
        <param name=\"allowScriptAccess\" value=\"sameDomain\" />
        <param name=\"movie\" value=\"http://neko.kgs.ku.edu/FlowPlayerLP.swf\" />
        <param name=\"quality\" value=\"high\" />
        <param name=\"scale\" value=\"noScale\" />
        <param name=\"wmode\" value=\"transparent\" />
		";
		echo "
		<param name=\"flashvars\"  value=\"initialScale=orig&baseURL=http://matisse.kgs.ku.edu/plots/$basedir&videoFile=movie.flv&loop=false&autoBuffering=true \" /> ";
		// this version has a splash image      echo "<param name=\"flashvars\"  value=\"initialScale=orig&baseURL=http://neko.kgs.ku.edu/plots/$basedir&videoFile=movie.flv&splashImageFile=http://neko.kgs.ku.edu/plots/$basedir/movie_last_frame.jpg&loop=false&autoBuffering=true \" /> ";
echo "
</object>
";

echo "  <object type=\"application/x-shockwave-flash\" data=\"http://matisse.kgs.ku.edu/flowplayer/FlowPlayerLP.swf\" width=\"$playerwidth\" height=\"$playerheight\" id=\"FlowPlayer\">
        <param name=\"allowScriptAccess\" value=\"sameDomain\" />
        <param name=\"movie\" value=\"http://matisse.kgs.ku.edu/flowplayer/FlowPlayerLP.swf\" />
        <param name=\"quality\" value=\"high\" />
        <param name=\"scale\" value=\"noScale\" />
        <param name=\"wmode\" value=\"transparent\" />
		";
		echo "
		<param name=\"flashvars\"  value=\"initialScale=orig&baseURL=http://matisse.kgs.ku.edu/plots/$basedir&videoFile=movie.flv&loop=false&autoBuffering=true \" /> ";
		// this version has a splash image      echo "<param name=\"flashvars\"  value=\"initialScale=orig&baseURL=http://neko.kgs.ku.edu/plots/$basedir&videoFile=movie.flv&splashImageFile=http://neko.kgs.ku.edu/plots/$basedir/movie_last_frame.jpg&loop=false&autoBuffering=true \" /> ";
echo "
</object>
";
/////debug echo " base directory is $basedir";
echo "<p /><a href=http://matisse.kgs.ku.edu/plots/$basedir/newout.avi>Play AVI movie</a>";
echo "</div>";
*/

// show all the files in the directory we made for this animation     echo "<p><font color=orange size=0><p />frames: ";system(" ls -l /var/www/html/plots/$basedir/*.* ");echo "</font>";



 

















// ******************************************************* INPUT FORM ************************************************************

// We defined the arrays for plot design in a single included file for all the plots.
include "includes/plot_design_arrays.php"; // arrays of some colors and shapes and icons accepted by jpgraph
//$icon=icons_array;
//$colors=$colors_array;
//$pointtypes=$pointtypes_array;

echo '<a name="form"></a>
<div width=100% align=center style="margin-top:50px">
<form id="theform" action="tasAni.php#player" method="post">
<table align=center border=0 cellspacing=10 cellpadding=0>';
// Title and submit button
echo '<tr valign=top><td valign="top" colspan=2>
<h4>Design and view TAS Diagram Animated by Age:</h4>&nbsp; </td>
<td valign="top" align="right">';
if ($earthchem) {
echo '
<input style="margin-left:10px" name="submit" type="image" value="submit" src="images/ec_animate.gif" alt="Animate" align="right" hspace="0" vspace="0" border="0" >
';
} elseif ($navdat) {
echo '
<input style="margin-left:10px" name="submit" type="image" value="submit" src="images/navdat_animate.gif" alt="Animate" align="right" hspace="0" vspace="0" border="0" >
';
}
echo '</td></tr>';

echo '<tr><td colspan=3 >&bull; Direction of age progression <font size=1><i>option is in development</i></font><br />
<!--<input name="age_direction" value="0" type="radio" ';
if ($age_direction==0) { echo ' checked '; }
echo '>youngest to oldest<br />-->
<input name="age_direction" value="1" type="radio" ';
if ($age_direction==1) { echo ' checked '; }
echo '>oldest to youngest
</td></tr>';
echo '<tr><td colspan=3>&bull; Choose width of plotting area  
<select name="plotsize">  ';
$plotsizes=array(600,700,800,900,1000,2000);
echo '<option selected value="'.$plotsize.'"> '.$plotsize;
foreach($plotsizes as $key=>$val) {
echo '<option value="'.$val.'"> '.$val;
}
echo '
</select> <font size=1>pixels (margins will be added)</font>
</td></tr>';
//echo '<tr><td colspan=3>&nbsp;</td></tr>';
// Do not offer the icons for the animated plots, as they cannot grow or shrink in size. echo '<tr valign=top><td colspan=3>&bull; Design plot points, or choose an icon for the plot points-->.</td></tr>';
echo '<tr valign=top><td>
<table border=0 cellspacing=10 cellpadding=0>';
echo '<tr valign=top><td colspan=3>';
//echo '<input type="radio" name="type" value="userdefined" checked> ';
echo '&bull; Design plot points:</td></tr>
<tr><td></td><td align=right>shape</td><td>
<select name="pointtype" style="width:120px"><option value=""> ';
foreach($pointtypes_array as $key=>$val){
	echo '<option ';
	if ($pointtype==$val) { echo ' selected '; }
	echo ' value="'.$val.'">'.$key ; 
	}
echo '</select>
</td></tr>';
echo '<tr valign=top><td></td><td align=right>fill color</td><td>
<select name="fillcolor" style="width:120px"> <option value=""> ';
foreach($colors_array as $colorname=>$hex) {
echo '<option ';
if ($fillcolor==$hex) {echo ' selected '; }
echo ' style="background-color:'.$hex.'" value="'.$hex.'">'.$colorname;}
echo '</select> ';
//commented out option for indexed color: echo '<font size=1>(name) <i>or</i></font>  <input size="10" type="text" name="customfillcolor" value=""> <font size=1>(index, e.g. d9d9ff)</font>';
echo '</td></tr>';
echo '<tr valign=top><td></td><td align=right>outline color</td><td>
<select name="strokecolor" style="width:120px"> <option value="">';
foreach($colors_array as $colorname=>$hex){
	echo '<option ';
	if ($strokecolor==$hex) {echo ' selected '; }
	echo ' style="background-color:'.$hex.'" value="'.$hex.'">'.$colorname; 
	}
echo '</select> ';
//commented out option for indexed color: echo '<font size=1>(name) <i>or</i></font>  <input size="10" type="text" name="customstrokecolor" value=""> <font size=1>(index, e.g. d9d9ff)</font>';
echo '</td></tr>';

echo '<tr valign=top><td></td><td align=right>size</td><td>
<select name="pointsize" style="width:120px"> ';
for ($i=1;$i<=10;$i++) {
echo '<option ';
if ($i==$pointsize) {echo ' selected ';}
echo ' value="'.$i.'"> '.$i;
}
echo '
</select> <font size=1>pixels</font>
</td></tr>';
echo '</table></td><td width=10></td><td>';
// Do not offer the icons for the animated plots as they cannot grow or shrink in size
echo '<!--<table>';
//commented out option for indexed color: echo '<tr><td align=center><i>or</i><td colspan=2>&nbsp;</td></tr>';
echo '<tr valign=top><td colspan=3 align=right><i>or</i> <input type="radio" name="type" value="stock_icon"> Choose icon for plot points:<p />
<select name="icon"><option value=""> ';
foreach($icons_array as $key=>$val)
{
	echo '<option ';
	if ($icon==$val) { echo ' selected '; }
	echo ' value="'.$val.'">'.$key;
}
echo '</select></td></tr>';
echo '</table>-->';
echo '</td></tr>'; 
echo '</td></tr>';
echo '<tr valign=top><td colspan=3>&bull; Choose color for rock names  
<select style="width:120px" name="rocknamecolor">';
foreach($colors_array as $colorname=>$hex) {
	echo '<option ';
	if ($rocknamecolor==$hex) { echo ' selected '; }
	echo 'style="color:'.$hex.'" value="'.$hex.'"> &bull; '.$colorname;
}
echo '</select> ';
echo '</td></tr>';

echo '<tr valign=bottom><td colspan=3 align=left valign=bottom>&bull; Number of frames in animation ';
echo '<select name="num_frames" onChange="alert(document.theform.num_frames.selectedIndex.value)">';
for ($i=1;$i<1000;$i++) {
	echo '<option '; if ($num_frames==$i) {echo ' selected ';} echo ' value="'.$i.'">'.$i;
}
echo  '</select></td></tr>';
echo '<tr valign=bottom><td colspan=3 align=left valign=bottom>&bull; Number of frames per second <input type="text" name="num_frames_per_second" value="'.$num_frames_per_second.'"></td></tr>';
echo '<tr valign=top><td colspan=3 align=left valign=bottom>&bull; Method to show age ';
// Methods of latency
echo '<br><input ';
if ($latency_method==1) { echo ' checked '; }
echo ' type=radio name="latency_method" value="1" > '.$latency_method_description_1;
echo ' &nbsp; n <select name="persist_frames"> ';
for ($i=1;$i<1000;$i++) {
	echo '<option '; if ($persist_frames==$i) {echo ' selected ';} echo ' value="'.$i.'">'.$i;
} // for 
echo '</select> <font size=1>(latency)</font>';
echo '<br><input ';
if ($latency_method==2) { echo ' checked '; }
echo ' type=radio name="latency_method" value="2"> '.$latency_method_description_2;
echo '</td></tr>';
// For developer mostly 
echo '<tr><td colspan=3>&bull; Display still frames?<input name="display_frames" value="true" type="checkbox" ';
if ($display_frames==true) { echo " checked "; }
echo '></td></tr>';
// for dev & test: echo '<tr><td colspan=3>&bull; Display SQL query?<input name="display_sql" value="true" type="checkbox"></td></tr>';

echo '</table>';

echo '
<input type="hidden" name="pkey" value="'.$pkey.'">
'; // Keep the sql statement we received from OutputData.cfm on geoportal

echo '
<input type="hidden" name="referring_website" value="'.$referring_website.'">  
<input type="hidden" name="tas_ani_sql" value="'.$tas_ani_sql.'">
';

echo '
</form>
<p />&nbsp;
</div>
';

} // if $sample_age_range > 0, else 

echo '</div>';
if ($navdat) {
include "../navdat/NewFooter.cfm";
}
if ($earthchem) {
include "../earthchemphp2/includes/ks_footer.html";
}

?>






