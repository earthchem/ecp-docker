<?php
// This file is meant to be included in a .php plotting tool script. It contains arrays of options we offer in the plots. 


$colors_array["lime"]="#00ff00";
$colors_array["pale green"]="#98fb98";
$colors_array["medium green"]="#009900";
$colors_array["dark green"]="#006400";
$colors_array["forest green"]="#228b22";
$colors_array["olive"]="#808000";
$colors_array["yellow green"]="#9acd32";
$colors_array["teal"]="#008080";
$colors_array["cyan"]="#00ffff";  
$colors_array["turquoise"]="#40e0d0";
$colors_array["sky blue"]="#87ceeb";
$colors_array["grid lt blue"]='#c7dedf'; // the color of the grids in the tufte graph 
$colors_array["blue"]="#0000ff";
$colors_array["navy"]="#000080";
$colors_array["indigo"]="#4b0082";
$colors_array["purple"]="#800080";
$colors_array["orchid"]="#ba55d3";
$colors_array["magenta"]="#ff00ff";
$colors_array["deep pink"]="#ff1493";
$colors_array["light pink"]="#ffb6c1";
$colors_array["red"]="#ff0000"; // slightly darker, richer green than 'green'
$colors_array["maroon"]="#800000"; 
$colors_array["orange red"]="#ff4500";
$colors_array["orange"]="#ffa500";
$colors_array["yellow"]="#ffff00";
$colors_array["charcoal"]='#333333'; 
$colors_array["gray"]='#808080';
$colors_array["silver"]='#c0c0c0';
$colors_array["black"]="#000000";
$colors_array["white"]='#ffffff';

// The various jpgraph pointtypes drawn on the fly (a subset of ones jpgraph offers)
$pointtypes_array=array();
$pointtypes_array['diamond']="MARK_DIAMOND";
$pointtypes_array['triangle up']="MARK_UTRIANGLE";
$pointtypes_array['triangle down']="MARK_DTRIANGLE";
$pointtypes_array['square']="MARK_SQUARE";
$pointtypes_array['circle, outline']="MARK_CIRCLE";
$pointtypes_array['circle, filled']="MARK_FILLEDCIRCLE";
$pointtypes_array['cross, outline']="MARK_CROSS";
$pointtypes_array['star, outline']="MARK_STAR";
$pointtypes_array['X, outline']="MARK_X";

$icons_array=array();
$icons_array['ball red small']="MARK_IMG_SBALL";
$icons_array['ball red medium']="MARK_IMG_MBALL";
$icons_array['ball red large']="MARK_IMG_LBALL";
$icons_array['square 3D red']="MARK_IMG_SQUARE";
$icons_array['star 3D blue']="MARK_IMG_STAR";  
$icons_array['diamond 3D red']="MARK_IMG_DIAMOND"; 
if ($navdat) { // These icons represent the schools involved in navdat. This variable should be set in parent script, before this script is included. 
	$icons_array['UNC tarheel']="TARHEEL";
	$icons_array['CU buffalo']="BUFFALO";  
}
$icons_array['KU jayhawk']="JAYHAWK"; // Jayhawk gets included either way 


// Arrays in use by the Harker plots 

// The flat dynamic shapes available for plot points, and the colors we offer
$pointshapes_colors_array=array( 
'MARK_DIAMOND'=>
array( 
pointtype_display_name=> 'diamond',
pointtype=>'MARK_DIAMOND',
colors => 'pink,cyan'
),
'MARK_UTRIANGLE'=>
array( 
pointtype_display_name=> 'triangle up',
pointtype=>'MARK_UTRIANGLE',
colors => 'pink,cyan'
),
'MARK_DTRIANGLE'=>
array( 
pointtype_display_name=> 'triangle down',
pointtype=>'MARK_DTRIANGLE',
colors => 'pink,cyan'
),
'MARK_SQUARE'=>
array( 
pointtype_display_name=> 'square',
pointtype=>'MARK_SQUARE',
colors => 'pink,cyan'
),
'MARK_CIRCLE'=>
array( 
pointtype_display_name=> 'circle, outline',
pointtype=>'MARK_CIRCLE',
colors => 'pink,cyan',
fillcolor => false,
),
'MARK_FILLEDCIRCLE'=>
array( 
pointtype_display_name=> 'circle, filled',
pointtype=>'MARK_FILLEDCIRCLE',
colors => 'pink,cyan'
),
array( 
pointtype_display_name=> 'cross, outline',
pointtype=>'MARK_CROSS',
colors => 'pink,cyan',
fillcolor => false,
),
array( 
pointtype_display_name=> 'star, outline',
pointtype=>'MARK_STAR',
colors => 'pink,cyan',
fillcolor => false,
),
array( 
pointtype_display_name=> 'X, outline',
pointtype=>'MARK_X',
colors => 'pink,cyan',
fillcolor => false,
),
);

// The custom icons that can be used for plot points, like the KU jayhawk, CU buffalo, UMC tarheel
if ($navdat) {
$custom_icons_array = array(
array( 
pointtype_display_name => "UNC tarheel", 
pointtype => "TARHEEL",
colors => "",
),
array( 
pointtype_display_name => "CU buffalo", 
pointtype => "BUFFALO",
colors => "",
),
array( 
pointtype_display_name => "KU jayhawk", 
pointtype => "JAYHAWK",
colors => "",
),
);
} elseif ($earthchem) {
$custom_icons_array=array(
array( 
pointtype_display_name => "KU jayhawk", 
pointtype => "JAYHAWK",
colors => "",
)
);

}
// The built-in jpgraph icons that can be used for plot points, and their possible colors in jpgraph
$icons_colors_array = array( 
array( 
pointtype_display_name => "3D ball - small", 
pointtype => "MARK_IMG_SBALL",
Colors => "red,green",
),
array( 
pointtype_display_name => "3D ball - medium", 
pointtype => "MARK_IMG_MBALL",
colors => "red,green",
),
array( 
pointtype_display_name => "3D ball - large", 
pointtype => "MARK_IMG_LBALL",
colors => "blue,lightblue,brown,darkgreen,green,purple,red,gray,yellow,silver,gray",
),
array( 
pointtype_display_name => "3D square", 
pointtype => "MARK_IMG_SQUARE",
colors => "red,green",
),
array( 
pointtype_display_name => "3D star", 
pointtype => "MARK_IMG_STAR",
colors => "bluegreen,lightblue,purple,blue,green,pink,red,yellow",
),
array( 
pointtype_display_name => "3D diamond", 
pointtype => "MARK_IMG_DIAMOND",
colors => "lightblue,darkblue,gray,blue,pink,purple,red,yellow",
),
array( 
pointtype_display_name => "pushpin - small", 
pointtype => "MARK_IMG_SPUSHPIN",
colors => "red,blue,green,pink,orange",
),
array( 
pointtype_display_name => "pushpin - large", 
pointtype => "MARK_IMG_LPUSHPIN",
colors => "red,blue,green,pink,orange",
),
);
			 
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			 



?>

<!--
<P> Built-in images and available colors:</P>
<TABLE border="1">
<TR><TH>Type</TH><TH>Description</TH><TH>Colors</TH></TR>
<TR><TD>MARK_IMG_PUSHPIN, MARK_IMG_SPUSHPIN</TD><TD> Push-pin image</TD><TD>
'red','blue','green','pink','orange'</TD></TR>

<TR><TD>MARK_IMG_LPUSHPIN</TD><TD> A larger Push-pin image</TD><TD>
'red','blue','green','pink','orange'</TD></TR>
<TR><TD>MARK_IMG_BALL, MARK_IMAGE_SBALL</TD><TD>A round 3D rendered ball</TD><TD>
'bluegreen','cyan','darkgray','greengray',
 'gray','graypurple','green','greenblue','lightblue',
 'lightred','navy','orange','purple','red','yellow'</TD></TR>
<TR><TD>MARK_IMAGE_MBALL</TD><TD>A medium sized round 3D rendered ball</TD><TD>
 'blue','bluegreen','brown','cyan',
 'darkgray','greengray','gray','green',
 'greenblue','lightblue','lightred', 'purple','red','white','yellow'</TD>

</TR>
<TR><TD>MARK_IMAGE_LBALL</TD><TD>A large sized round 3D rendered ball</TD><TD>
 'blue','lightblue','brown','darkgreen',
 'green','purple','red','gray','yellow','silver','gray'</TD></TR>
<TR><TD>MARK_IMAGE_SQUARE</TD><TD>A 3D rendered square</TD><TD>
'bluegreen','blue','green', 'lightblue','orange','purple','red','yellow'</TD>
</TR>
<TR><TD>MARK_IMG_STAR</TD><TD>A 3D rendered star image</TD><TD>
'bluegreen','lightblue','purple','blue','green','pink','red','yellow'</TD>

</TR>
<TR><TD>MARK_IMG_DIAMOND</TD><TD>A 3D rendered diamond</TD><TD>
'lightblue','darkblue','gray', 'blue','pink','purple','red','yellow'</TD>
</TR>
<TR><TD>MARK_IMG_BEVEL</TD><TD>A 3D rendered bevel style round ring</TD><TD>
'green','purple','orange','red','yellow'</TD></TR>
</TABLE>
-->