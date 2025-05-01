<? 
/* Building this file to make animations for a Harker plot, on navdat. We arrive here from the Output Data page on Navdat or Results page on EarthChem.
*/
// test: foreach($_POST as $key=>$val) {echo "<br>$key &bull; $val ";}
// First: Determine whether we came from EarthChem or NAVDAT
isset($_POST["referring_website"]) ? $referring_website=$_POST["referring_website"] : $referring_website="";
if ($referring_website=="") { // try to figure it out from the referring page
	if (stripos('earthchem',$_SERVER['HTTP_REFERER'])>0) {
		$referring_website='earthchem';
	} elseif (stripos('navdat',$_SERVER['HTTP_REFERER'])>0) { 
		$referring_website=='navdat';
	}
}
$earthchem=false;$navdat=false; 
if ($referring_website=='earthchem') {$earthchem=true;} elseif ($referring_website=='navdat') {$navdat=true;} //echo $referring_website;
// Page header and load the database drivers and connect to db
if ($earthchem) {
	include "../earthchemphp3/includes/ks_head.html";
	include "includes/earthchem_db.php";
} elseif ($navdat) {
	include "../navdat/NewHeader.cfm";
	include "includes/navdat_db.php";
} else {
	echo "<p>There is a problem identifying the website you are working on. Please start a new search and try again.<p>"; 
	exit;
}
// Title
echo '<div name="page_div" '; if ($navdat) {echo ' style="margin:20px" ';} echo '>';
echo "<h1>Harker Diagram Animated by Age</h1><br>";

if($earthchem){
	$pkey=$_POST['pkey'];
	?>
	<div style="width:800px;text-align:right;">
	<?
	echo "<input type=button value=\"Back to EarthChem Output\" onClick=\"window.location = 'http://matisse.kgs.ku.edu/earthchemnew/results.php?pkey=$pkey';\">";
	?>
	</div>
	<?
}

$chemicals_array['al2o3'] = "Al2O3";
$chemicals_array['cao'] = "CaO";
//$chemicals_array["$Iron_Field"] = "Fe2O3"; // the field in the database is not called Fe2O3
$chemicals_array["k2o"] = "K2O";
$chemicals_array["mgo"] = "MgO";
$chemicals_array["na2o"] = "Na2O";
$chemicals_array["p2o5"] = "P2O5";
$chemicals_array["sio2"] = "SiO2"; // need this for the xtitle at least 
$chemicals_array["tio2"] = "TiO2";
if ($navdat) {
	$chemicals_array["fe2o3t_calculated"] = "Fe2O3 total calculated"; // the field in the view normalized for iron total
} elseif ($earthchem) {
	$chemicals_array["feot"] = "FeO Total";
}

isset($_POST["chem1"]) ? $chem2=$_POST["chem1"] : $chem1="sio2";
isset($_POST["chem2"]) ? $chem2=$_POST["chem2"] : $chem2="sio2"; // This comes either from OutputData.cfm on geoportal, or this script calling itself via the form at the bottom of the page

if (isset($_POST['harker_ani_sql'])) {
	$harker_ani_sql = $_POST['harker_ani_sql']; // From Earthchem Results page or the form below, we should have this value. 
} elseif ($navdat) {
$navdatsqlstatement=$_POST['navdatsqlstatement'];
// We could get it with the pkey if it did not come in the form variable: $navdatsqlstatement = $db->get_var("SELECT navdatsqlstatement FROM search_query WHERE pkey = $pkey");
// Rewrite the SELECT portion of the statement preceding 'FROM'... - to shorten and speed up the query, getting only exactly what we need
$from_position = stripos($navdatsqlstatement,'FROM '); 
$from_sql=substr($navdatsqlstatement,$from_position);
$from_sql = str_ireplace("view_data_output ","view_norm_fe2o3_total ",$from_sql); // For navdat, for the TAS and Harker plots, get chemicals that are normalized
//$from_sql .= " AND norm.sio2 BETWEEN 0 AN 100 AND norm.al2o3 BETWEEN 0 AND 100 AND norm.mgo BETWEEN 0 AND 100 AND norm.tio2 BETWEEN 0 AND 100 AND norm.na2o BETWEEN 0 AND 100 AND norm.cao BETWEEN 0 AND 100 AND norm.k2o BETWEEN 0 AND 100 AND norm.p2o5 BETWEEN 0 AND 100";
$harker_ani_sql="SELECT age.calculated_age as age ";
foreach($chemicals_array as $key=>$val) {  
	$harker_ani_sql .= ", norm.$key AS $key ";
	$from_sql .= " AND norm.$key BETWEEN 0 AND 100 ";
}
//$harker_ani_sql = substr($harker_ani_sql,-1); // lose the leading comma 
$harker_ani_sql .= $from_sql;  
} // if navdat
else {
echo "<p>There is a problem. No information is available for the query. <p>"; exit;
}

isset($_POST["plotsize"]) ? $plotsize=$_POST["plotsize"] : $plotsize=500;

// The div that will hold the movie. 
echo '<div style="height:50px"></div>'; // vertical space 
echo '<p /><a name="player"></a>'; // The form will jump to this anchor, so the user can see the menu bar on the player
echo '<div id="div_movie" style="block:inline; margin:0; text-align:center; min-height:'.$plotsize.'">';  //The div will contain the movie
echo '<img src="images/progress.gif" style="margin:100px 20px">'; // animated gif 
echo '</div>';
ob_flush();flush();

$pkey=$_POST["pkey"];  
isset($_POST["age_direction"]) ? $age_direction=$_POST["age_direction"] : $age_direction=1; // 0 is youngest to oldest, 1 is the reverse
isset($_POST["num_frames"]) ? $num_frames=$_POST["num_frames"] : $num_frames = 10; if ($num_frames < 1 or $num_frames > 1000) {echo "<p>Number of frames must be between 1 and 1000. Setting the number of frames to 10.";}  //; if (!isset($num_frames)) {$num_frames = 10;} elseif ($num_frames<1 || $num_frames>1000) {$num_frames=10; echo "<p><font color=green size=1>Number of frames must be between 1 and 1000. Changing to $num_frames.</font>"; }
isset($_POST["num_frames_per_second"]) ? $num_frames_per_second = $_POST["num_frames_per_second"] : $num_frames_per_second = 5; if ($num_frames_per_second<1 || $num_frames_per_second>25) {$num_frames_per_second=1; echo "<p>Number of frames per second must be between 1 and 25. Changing to $num_frames_per_second."; }
isset($_POST["latency_multiple"]) ? $latency_multiple=$_POST["latency_multiple"] : $latency_multiple=1;  if ($latency_multiple<1) {$latency_multiple=1;}
isset($_POST["latency_method"]) ? $latency_method=$_POST["latency_method"] : $latency_method=2;
if ($latency_method==2 && $num_frames<10) {$num_frames=10; echo "<p>Number of frames for this method of showing age must be at least $num_frames. Changing to $num_frames."; }
$display_frames=$_POST["display_frames"]; // Show the individual frames as well as the movie? 
//$Iron_SQL = "AND norm.fe2o3t_calculated > 0 AND norm.fe2o3t_calculated < 100";
//$Iron_Field = "fe2o3t_calculated";

$fillcolor=$_POST["fillcolor"];  /* don't offer the user-specified color, e.g. #d9d9ff, for now, per Doug - but keep it just in case 
$customfillcolor=trim($_POST["customfillcolor"]);
if (strlen($customfillcolor)>0) {
	$customfillcolor = str_replace("#", "", $customfillcolor); //strip any #
	if (strlen($customfillcolor)==6) {
		$customfillcolor="#".$customfillcolor;  //add a leading #
		$fillcolor=$customfillcolor; // set the color to the valid custom color the user provided
	}
} */
if (strlen($fillcolor) == 0) {$fillcolor='#00ff00';} // default is red 

$strokecolor=$_POST["strokecolor"]; 
if (strlen($strokecolor) == 0) {$strokecolor='#000000';}  
if (isset($_POST['pointsize'])) {$pointsize = $_POST["pointsize"]; } elseif ($latency_method==2) {$pointsize=5;} else {$pointsize=3;} 
if ($latency_method==2 && $pointsize<5) {$pointsize=5; echo "<p>Point size for this method of showing age must be at least $pointsize. Changing to $pointsize."; }
if ($latency_method==1 && $pointsize<($latency_multiple+1)) {$pointsize=$latency_multiple+1; echo "<p>Point size for this method of showing age must be greater than than <i>n</i>. Changing to $pointsize.</font>"; }
$type = $_POST["type"];
$pointtype = $_POST["pointtype"]; if ($pointtype=="") {$pointtype='MARK_DIAMOND';}
//if ($type == 'predefined') {$pointtype_predefined=$_POST["pointtype_predefined"]; if ($pointtype_predefined > "") {$pointtype=$pointtype_predefined;}}
//echo "<p /><font color=purple>Parameters &bull; latency_method $latency_method &bull; plot size $plotsize &bull; point color $fillcolor/$strokecolor size $pointsize &bull; $chem2 vs $chem1 &bull; frames $num_frames $num_frames_per_second/sec &bull; age_direction $age_direction &bull;";

// We aren't offering this method anymore     $latency_method_description_10="<font size=1 color='purple'>10. Datapoints appear, grow over <i>n</i> frame(s), disappear. </font>";  
$latency_method_description_1="<font size=1 color='green'>Datapoints persist through <i>n</i> frames.</font>";
$latency_method_description_2="<font size=1 color='blue'>Datapoints grow over 4 frames, persist through 3 more frames, shrink over 3 frames. 
</font>"; 





// ********************************************** MAKE THE FRAMES (each frame is image containing a plot or multiple plots layered) 
include "harkerAni-make_frames.php";  // Make all the individual frames, stored in their own uniquely named subdir 
 





// ********************************************** MAKE THE ANIMATION *************************************************************
// Now make the movie, using system calls to execute mencoder and ffmpeg. Use exec() because you can send the output to a variable instead of seeing it on the screen
// print the files in our animation subdirectory to test:   echo "<font color=blue><p />ls $basedir/ ";exec(" ls $basedir/ ",$foo,$bar);echo $bar; print_r($foo); echo " </font>";
exec( "/usr/bin/mencoder 'mf://$basedir/*.jpg' -mf fps=$num_frames_per_second:type=jpg -ovc copy -oac copy -o /var/www/plots/$basedir/output.avi",$foo,$bar);  
exec( "/usr/bin/mencoder /var/www/plots/$basedir/output.avi -ovc lavc -oac lavc -ffourcc DX50 -o /var/www/plots/$basedir/newout.avi",$foo,$bar);  
exec( "/usr/bin/ffmpeg -i /var/www/plots/$basedir/newout.avi /var/www/plots/$basedir/movie.flv",$foo,$bar);   
exec( "/usr/bin/ffmpeg -i /var/www/plots/$basedir/movie.flv -f mjpeg -t 0.001 ",$foo,$bar);   // the splash page from the last frame does not work , work on this Eileen 
exec( "/usr/bin/flvtool2 -U /var/www/plots/$basedir/movie.flv",$foo,$bar);  
// link to movie: echo '<br><a href="'.$basedir.'/movie.flv">'.$basedir.'/movie.flv</a>';
// show last frame: echo "<p />last filename: $image_filename ";
// show all the files in the directory we made for this animation: echo "<p><font color=orange size=0><p />frames: ";system(" ls -l /var/www/html/plots/$basedir/*.* ");echo "</font>";


// *********************************************** LOAD THE PLAYER AND MOVIE ******************************************************
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
$playerwidth=$plotsize;$playerheight=$plotsize;$playerheight+=33;
echo '<script language="javascript">
flashembed("div_movie", 	
		/* 
			first argument supplies standard Flash parameters. See full list:
			http://kb.adobe.com/selfservice/viewContent.do?externalId=tn_12701
		*/
		{
			src:"http://hopper.kgs.ku.edu/flowplayer/FlowPlayerDark.swf",
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
			splashImageFile: "http://hopper.kgs.ku.edu/plots/'.$basedir.'/movie_last_frame.jpg", 
			usePlayOverlay: false, 
			controlBarGloss: "high", 
			initialScale: "scale",
			videoFile: "http://hopper.kgs.ku.edu/plots/'.$basedir.'/movie.flv"
		}} 
	);
	</script>		
';

/* This code that uses the player on neko works but plays jerkily at the end 
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

echo '<div align="center">';
// Display the method of showing latency that we used 
if ($latency_method==1) {
	$display_description = str_ireplace("<i>n</i>", $latency_multiple, $latency_method_description_1);echo "<p />$display_description";
} elseif ($latency_method==2) {
	echo "<p />$latency_method_description_2";
}
// Offer the AVI file which they can play on their own local player Windows Media or whatever  
echo '<p />The movie plays on an embedded player. You can also play an <a href="http://matisse.kgs.ku.edu/plots/'.$basedir.'/newout.avi">AVI movie</a>.'; // Offer the AVI version to click on, too
echo '</div>';








// ******************************************************* INPUT FORM ************************************************************

include "includes/plot_design_arrays.php"; // contains the arrays of colors, pointtypes and icons we will offer for the datapoint design

echo '<div id="form" style="margin:50px 0px">
<form action="harkerAni.php#player" method="post">
<table border=0 cellspacing=10 cellpadding=0><tr><td valign="top"><h4>Design and view Harker Diagram Animated by Age:</h4></td><td align="right" valign="top">';
// Submit button
if ($earthchem) {
// Use plain html button for EarthChem, per Justin/Kerstin     echo '<input style="margin-left:10px" name="submit" type="image" value="submit" src="images/ec_animate.gif" alt="Animate" align="right" hspace="0" vspace="0" border="0" >';
echo '<input style="margin-left:10px" name="submit" type="submit" value="Animate">';
} elseif ($navdat) {
echo '
<input style="margin-left:10px" name="submit" type="image" value="submit" src="images/navdat_animate.gif" alt="Animate" align="right" hspace="0" vspace="0" border="0" >
';
}
echo '<tr valign=top><td colspan=3 align=left>&bull; Chemical to plot vs SiO2 
<select name="chem2">';  
foreach($chemicals_array as $key=>$val) { // field_name=>display_name
	echo '<option '; if ($chem2==$key) {echo ' selected ';} echo 'value="'.$key.'">'.$val;
}
echo '</select> <font size=1>normalized for iron</font> </td></tr>';
echo '<tr><td colspan=3 valign=top>&bull; Direction of age progression <font size=1><i>option is in development</i></font><br />
<!--<input name="age_direction" value="0" type="radio">youngest to oldest<br />-->
<input name="age_direction" value="1" type="radio" checked>oldest to youngest
</td></tr>';
echo '<tr><td colspan=3>&bull; Plot size ';
echo '<select name="plotsize">  ';
for ( $i = 200; $i <= 1000; $i += 100) { //  Note: jpgraph generates a "too small" error with $plotsize=100, 200 works
echo "<option value='$i'"; if ($plotsize==$i) {echo " selected ";} echo "> $i x $i ";
}
echo '
</select> <font size=1>pixels</font>
</td></tr>';
echo '<tr valign=top><td colspan=3 align=left>&bull; Design datapoints</td></tr>
<tr valign=top><td colspan=3>
<table border=0 style="margin-left:20px">';
echo '
<tr>
<td align=right>point shape</td><td align=left>
<select name="pointtype" style="width:120px"><option value=""> ';
foreach ($pointtypes_array as $key=>$val) {echo '<option value="'.$val.'"'; if ($val==$pointtype) {echo ' selected ';} echo '>'.$key.'</option>';}
echo '</select>';
echo '</td></tr>';
echo '<tr valign=top>
<td align=right>point fill color</td><td align=left>
<select name="fillcolor" style="width:120px"><option value="">';
foreach($colors_array as $key=>$val){echo '<option style="background-color:'.$val.'" value="'.$val.'"'; if ($fillcolor==$val) {echo ' selected ';} echo '>'.$key;}
echo '</select>';
echo '<tr valign=top><td align=right>point outline color</td><td align=left><select name="strokecolor" style="width:120px"> <option value="">';
foreach($colors_array as $key=>$val){echo '<option style="color:'.$val.'" value="'.$val.'"'; if ($strokecolor==$val) {echo ' selected ';} echo '>'.$key;}
echo '</select>';
echo '<tr valign=top><td align=right>point size</td><td align=left>
<select name="pointsize" style="width:120px">
<option value=""> ';
for ( $i = 1; $i <= 10; $i++) {
echo "<option value='$i' "; if ($pointsize==$i) {echo " selected ";} echo " > $i";
}
echo '</select> <font size=1>pixels</font>
</td></tr></table></td>';
echo '</tr>'; 
echo '<tr valign=bottom><td colspan=3 align=left valign=bottom>&bull; Number of frames in animation <input type="text" name="num_frames" value="'.$num_frames.'"></td></tr>';
echo '<tr valign=bottom><td colspan=3 align=left valign=bottom>&bull; Number of frames per second <input type="text" name="num_frames_per_second" value="'.$num_frames_per_second.'"></td></tr>';
echo '<tr valign=top><td colspan=3 align=left valign=bottom>&bull; Method to show age ';
echo '<br><input type=radio name="latency_method" value="1" '; if ($latency_method==1) {echo ' checked ';} echo '> '.$latency_method_description_1;
echo ' &nbsp; n <input type="text" size="5" name="latency_multiple" value="'.$latency_multiple.'"> <font size=1>(latency)</font>';
echo '<br><input type=radio name="latency_method" value="2" '; if ($latency_method==2) {echo ' checked ';} echo '> '.$latency_method_description_2;
echo '</td></tr>';
echo '<tr><td colspan=3>&bull; Display frames?<input name="display_frames" value="true" type="checkbox" '; if ($display_frames) {echo ' checked ';} echo '></td></tr>';
echo '<tr valign=bottom><td colspan=3 align=left valign=bottom><font size=1><i>All selections are optional. Defaults apply.</td></tr>';
echo '</table>';
echo '<input type="hidden" name="harker_ani_sql" value="'.$harker_ani_sql.'">'; 
echo '<input type="hidden" name="referring_website" value="'.$referring_website.'">';
echo '</form></div>';

//echo "<p>test at 306: <br>$harker_ani_sql<p>";  

if ($earthchem) {
	include "../earthchemphp3/includes/ks_footer.html";
} elseif ($navdat) {
	include "../navdat/NewFooter.cfm";
}
?>

