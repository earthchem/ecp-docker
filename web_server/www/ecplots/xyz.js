<SCRIPT LANGUAGE="JavaScript"><!--

function openChild(i) {
	var zpointtype = document.form_xyz.elements['zpointtype['+i+']'].value;
	var zpointsize = document.form_xyz.elements['zpointsize['+i+']'].value;
	var zstrokecolor = document.form_xyz.elements['zstrokecolor['+i+']'].value;
	zstrokecolor=escape(zstrokecolor); // encode the # in the hex color value, for the url string
	var zfillcolor = document.form_xyz.elements['zfillcolor['+i+']'].value;
	zfillcolor=escape(zfillcolor); // encode the # in the hex color value, for the url string
 	var timestamp = document.form_xyz.elements['timestamp'].value;
	var url='xyz-child_design_datapoint.php?i=' + i + '&zpointtype=' + zpointtype + '&zpointsize=' + zpointsize + '&zfillcolor=' + zfillcolor + '&zstrokecolor=' + zstrokecolor + '&timestamp=' + timestamp;
 childWindow=open(url,window,'resizable=yes,width=250,height=350,left=300,top=300');
 if (childWindow.opener == null) childWindow.opener = self;
	childWindow.focus();
 }
// open the popup help window
function openHelpWindow(anchor) {
	var zpointtype = document.form_xyz.elements['zpointtype['+i+']'].value;
	var zpointsize = document.form_xyz.elements['zpointsize['+i+']'].value;
	var zstrokecolor = document.form_xyz.elements['zstrokecolor['+i+']'].value;
	zstrokecolor=escape(zstrokecolor); // encode the # in the hex color value, for the url string
	var zfillcolor = document.form_xyz.elements['zfillcolor['+i+']'].value;
	zfillcolor=escape(zfillcolor); // encode the # in the hex color value, for the url string
 	var timestamp = document.form_xyz.elements['timestamp'].value;
	var url='xyz-help.php#' + anchor;
 childWindow=open(url,window,'resizable=yes,width=600,height=600,left=300,top=300');
 if (childWindow.opener == null) childWindow.opener = self;
	childWindow.focus();
 }
	
// The following function hides a div layer 
function hide(layer_ref) {
state = 'hidden';
if (document.all) { //IS IE 4 or 5 (or 6 beta)
eval( "document.all." + layer_ref + ".style.visibility = state");
}
if (document.layers) { //IS NETSCAPE 4 or below
document.layers[layer_ref].visibility = state;
}
if (document.getElementById && !document.all) {
maxwell_smart = document.getElementById(layer_ref);
maxwell_smart.style.visibility = state;
}
}

function alertselected(msg){
var msg
alert(msg)
}


// The following function hides a div layer 
function hide(layer_ref) {
state='hidden';
if (document.all) { //IS IE 4 or 5 (or 6 beta)
eval( "document.all." + layer_ref + ".style.visibility=state");
}
if (document.layers) { //IS NETSCAPE 4 or below
document.layers[layer_ref].visibility=state;
}
if (document.getElementById && !document.all) {
maxwell_smart=document.getElementById(layer_ref);
maxwell_smart.style.visibility=state;
}
}

/* The following function creates an XMLHttpRequest object... */
function createRequestObject(){
	var request_o; //declare the variable to hold the object.
	if (window.XMLHttpRequest) { // most browsers
 		request_o=new XMLHttpRequest();
	}
	else if (window.ActiveXObject) { // Internet Explorer
		request_o=new ActiveXObject("Msxml2.XMLHTTP");
	}
	return request_o;
}

var http=createRequestObject(); // var to hold the XMLHttpRequest object for redoColumnSelections_xyz();
function redoColumnSelections_xyz(axis){
	// First get the selection for the x, y and z axes (possible not all are selected)
	var axis=axis; // We are not actually using this for anything. -- Eileen
	var i;
	i=document.form_xyz.x_column_name.selectedIndex; // the index of the option selected by the user 
	var x_column_name=document.form_xyz.x_column_name.options[i].value || ""; // the value of the option the user selected for x
	i=document.form_xyz.y_column_name.selectedIndex; 
	var y_column_name=document.form_xyz.y_column_name.options[i].value || ""; // the value of the option the user selected for y
	i=document.form_xyz.z_column_name.selectedIndex; 
	var z_column_name=document.form_xyz.z_column_name.options[i].value || ""; // the value of the option the user selected for z
	i=document.form_xyz.x_ratio.selectedIndex;
	var x_ratio=document.form_xyz.x_ratio.options[i].value || "";
	i=document.form_xyz.y_ratio.selectedIndex;
	var y_ratio=document.form_xyz.y_ratio.options[i].value || "";
	i=document.form_xyz.z_ratio.selectedIndex;
	var z_ratio=document.form_xyz.z_ratio.options[i].value || "";
	var x_ratio_part="denominator"; // the default for this ratio button is denominator
	for (i=0; i < document.form_xyz.x_ratio_part.length; i++)
	{
		if (document.form_xyz.x_ratio_part[i].checked)
			{
			x_ratio_part=document.form_xyz.x_ratio_part[i].value;
			}
	}
	var y_ratio_part="denominator"; // the default for this ratio button is denominator
	for (i=0; i < document.form_xyz.y_ratio_part.length; i++)
	{
		if (document.form_xyz.y_ratio_part[i].checked)
			{
			y_ratio_part=document.form_xyz.y_ratio_part[i].value;
			}
	}
	var z_ratio_part="denominator"; // the default for this ratio button is denominator
	for (i=0; i < document.form_xyz.z_ratio_part.length; i++)
	{
		if (document.form_xyz.z_ratio_part[i].checked)
			{
			z_ratio_part=document.form_xyz.z_ratio_part[i].value;
			}
	}
	var from = document.form_xyz.from.value;
	var where = document.form_xyz.where.value; //debug: alert(from + where );
	var master_fieldnames_list   = document.form_xyz.master_fieldnames_list.value; 
	var master_displaynames_list = document.form_xyz.master_displaynames_list.value;
	var referring_website = document.form_xyz.referring_website.value; 
	var pkey = document.form_xyz.pkey.value; 
	// Repopulate the div containing the input fields for x,y,z and their ratios
	/*document.write('xyz-write_form_xyz.php?x_column_name=' + x_column_name 
			+ '&y_column_name=' + y_column_name + '&z_column_name=' + z_column_name
			+ '&x_ratio=' + x_ratio + '&y_ratio=' + y_ratio + '&z_ratio=' + z_ratio
			+ '&x_ratio_part=' + x_ratio_part + '&y_ratio_part=' + y_ratio_part + '&z_ratio_part=' + z_ratio_part
			+ '&from=' + from + '&where=' + where 
			+ '&master_fieldnames_list=' + master_fieldnames_list );*/
	//alert(master_fieldnames_list); alert(x_column_name); alert(from); alert(where); 
	http.open('get', 'xyz-write_form_xyz.php?x_column_name=' + x_column_name 
			+ '&y_column_name=' + y_column_name + '&z_column_name=' + z_column_name
			+ '&x_ratio=' + x_ratio + '&y_ratio=' + y_ratio + '&z_ratio=' + z_ratio
			+ '&x_ratio_part=' + x_ratio_part + '&y_ratio_part=' + y_ratio_part + '&z_ratio_part=' + z_ratio_part
			+ '&from=' + from + '&where=' + where 
			+ '&master_fieldnames_list=' + master_fieldnames_list 
			+ '&master_displaynames_list=' + master_displaynames_list 
			+ '&referring_website=' + referring_website
			+ '&pkey=' + pkey 
	);
	http.onreadystatechange=rewrite_div_xyz; // rewrite the div that contains form_xyz and the selections for x,y,z and their ratios 
	http.send(null);
	// If the user has not defined at least x and y, disable the plot button 
	//alert(document.form_xyz.plotbutton.disabled); alert('x = ' + x_column_name); alert('y = '+y_column_name);
	//if (x_column_name == '' || y_column_name == '') { 
		//alert('disable it!'); document.getElementById('plotbutton').disabled = true;
	//} else {
		//alert('do not disable it!'); document.getElementById('plotbutton').disabled = false;
	//}
	// Erase the data ranges for x, y, z since xyz has changed 
	document.getElementById('show_x_range').innerHTML="";
	document.getElementById('show_y_range').innerHTML="";
	document.getElementById('show_z_range').innerHTML="";
	document.form_xyz.xmin.value="";document.form_xyz.xmax.value="";document.form_xyz.x_precision.selectedIndex=0;
	document.form_xyz.ymin.value="";document.form_xyz.ymax.value="";document.form_xyz.y_precision.selectedIndex=0;
	document.form_xyz.zmin.value="";document.form_xyz.zmax.value="";document.form_xyz.z_precision.selectedIndex=0;
	// Reset the z options to their defaults 
	 
	reset_z_intervals_mins_maxes(); // Erase any min/max set by the user for any z interval 
	resetz(10); // Reset any z interval point design made by the user 
	/*/ Update some hidden form variables in form_xyz. These ought to be a separate function, really, Eileen.
	document.form_xyz.x_column_name.value=x_column_name;
	document.form_xyz.y_column_name.value=y_column_name;
	document.form_xyz.z_column_name.value=z_column_name;
	document.form_xyz.x_ratio.value=x_ratio;
	document.form_xyz.y_ratio.value=y_ratio;
	document.form_xyz.z_ratio.value=z_ratio;
	document.form_xyz.x_ratio_part.value=x_ratio_part;
	document.form_xyz.y_ratio_part.value=y_ratio_part;
	document.form_xyz.z_ratio_part.value=z_ratio_part;*/
}
function rewrite_div_xyz(){
	if(http.readyState == 4){ // 4=finished loading the response
		var response=http.responseText; // Let's see what the response is, from the server-side script
		document.getElementById('div_xyz').innerHTML=response; // change the div content
	}
}

function set_default_z_intervals_num(n) {
if (n == '') {n=2;}
// Set a default minimum value for z_intervals_num
if (document.form_xyz.z_intervals_num.selectedIndex ==0) {
	document.form_xyz.z_intervals_num.selectedIndex = n; 
}
} // end function

function show_hide(div_id,state) {
// div_id is the hidden div to show
if (state == 'visible') {} else {state = 'hidden'};
if (document.all) { //IS IE 4 or 5 (or 6 beta)
eval( "document.all." + div_id + ".style.visibility = state");
}
if (document.layers) { //IS NETSCAPE 4 or below
document.layers[div_id].visibility = state;
}
if (document.getElementById && !document.all) {
document.getElementById(div_id).style.visibility = state;
}
} // end function
function resetz(n) { // Reset individual z interval point designs to nothing (plot will default to the main point design)
var i;
//for (i=0;i<n;i++) { JUST DO THEM ALL
for (i=0;i<10;i++) {
	document.form_xyz.elements[ 'zpointtype['+i+']'].value = '';
	document.form_xyz.elements[ 'zpointsize['+i+']'].value = '';
	document.form_xyz.elements['zstrokecolor['+i+']'].value = '';
	document.form_xyz.elements[ 'zfillcolor['+i+']'].value = '';
	document.form_xyz.elements[    'z_order['+i+']'].selectedIndex = i; // Reset the plotting order for the z interval scatter plots
	//document.form_xyz.elements['z_interval_mins['+i+']'].value = '';
	//document.form_xyz.elements['z_interval_maxes['+i+']'].value = '';
	var zdivname='div_z_' + i;
	document.getElementById(zdivname).innerHTML=''; // remove the single-point plot image for the z interval
	}
} // end function
function reset_z_intervals_mins_maxes(z_intervals_num) { // Reset individual z interval point designs to nothing (plot will default to the main point design)
var i;
for (i=0;i<10;i++) {
	document.form_xyz.elements['z_interval_mins['+i+']'].value = '';
	document.form_xyz.elements['z_interval_maxes['+i+']'].value = '';	
}
} // end function

function reset_two_z_options(z_intervals_num) { 
// Combines resetz() and reset_z_intervals_min_maxes() into one function. Also hides the layers that show the now-undependable min/maxes. 
var i;
	//for (i=0;i<z_intervals_num;i++) { JUST DO THEM ALL
	for (i=0;$i<10;$i++) {
		document.form_xyz.elements[ 'zpointtype['+i+']' ].value = '';
		document.form_xyz.elements[ 'zpointsize['+i+']' ].value = '';
		document.form_xyz.elements['zstrokecolor['+i+']' ].value = '';
		document.form_xyz.elements[ 'zfillcolor['+i+']' ].value = '';
		document.form_xyz.elements[ 'z_order['+i+']' ].value = i; // Reset the plotting order for the z interval scatter plots
		var zdivname='div_z_' + i;
		document.getElementById(zdivname).innerHTML=''; // remove the single-point plot image for the z interval
		document.form_xyz.elements[ 'z_interval_mins['+i+']' ].value = '';
		document.form_xyz.elements[ 'z_interval_maxes['+i+']' ].value = '';
		hide('show_x_range');hide('show_y_range');hide('show_z_range'); 
	}
} // end function

function show_hide_z_options() {
// If z_column_name has a value, show the div_z_options div; otherwise hide it. We may not use this.
var state 
if (document.form_xyz.z_column_name.value == "") {state = 'hidden'} else {state = 'visible'}
var div_id = 'div_z_options'
if (document.all) { //IS IE 4 or 5 (or 6 beta)
eval( "document.all." + div_id + ".style.visibility = state");
}
if (document.layers) { //IS NETSCAPE 4 or below
document.layers[div_id].visibility = state;
}
if (document.getElementById && !document.all) {
document.getElementById(div_id).style.visibility = state;
}
} // end function

// Populate the fields that set the min/max for the z intervals. This is run onChange when the user selects the number of z intervals in the dropdown select box.
function pop_z_interval_definitions(zmin,zmax) {
/* We call this function if the user just changed the number of z intervals (z_intervals_num).
It populates the min/max for the z intervals.
We do not want to do this if the user just made a selection for z (because we do not yet have the min/max for the sample set and will not have it until the user plots, which causes the query to execute. If the user just made a selection for z, we disabled the min/max fields, so this is a good test. */
	if (!document.form_xyz.elements['z_interval_mins[1]'].disabled) {
	var z_intervals_num = document.form_xyz.z_intervals_num.value 
	var i
	var j
	var m
	var interval_size 	
	// Clear any existing values from the form fields
	for (i=0;i<10;i++) {
		document.form_xyz.elements['z_interval_mins['+i+']'].value=""
		document.form_xyz.elements['z_interval_maxes['+i+']'].value=""
	} // for
	if ((zmin == '') || (zmax == '')) {} else {
	// Populate the min and max - if the parameters passed in are not blank
	interval_size = (parseFloat(zmax) - parseFloat(zmin)) / parseFloat(z_intervals_num)
	m = z_intervals_num-1
	//document.form_xyz.elements['z_interval_maxes['+m+']'].value=zmax
	for (j=0;j<z_intervals_num;j++) {
		document.form_xyz.elements['z_interval_mins['+j+']'].value=parseFloat(zmin) + (j*parseFloat(interval_size))
		document.form_xyz.elements['z_interval_maxes['+j+']'].value=parseFloat(zmin) + (j*parseFloat(interval_size)) + parseFloat(interval_size)
	} // for
	document.form_xyz.elements['z_interval_mins[0]'].value=zmin 
	document.form_xyz.elements['z_interval_maxes['+m+']'].value=zmax
	} // if
	} // if
} // end function

function showorhide_div_z_options() {
// If there is a column name for z, show the div with the z options; otherwise hide it. Why does this not work more than once?
//document.write(document.form_xyz.z_column_name.value)
var i 
var z
i = document.form_xyz.z_column_name.selectedIndex
z = z_column_name=document.form_xyz.z_column_name.options[i].value
//document.write(z)
//document.form_xyz.z_column_name.value = z

z = document.form_xyz.z_column_name.value.length
//document.write(z)
//document.form_xyz.test3.value=l
showlayer('div_z_options')	
if (z < 1) {
	hidelayer('div_z_options')
} else {
	showlayer('div_z_options')
}
} // end function



function hidelayer(lay) {
var ie4 = (document.all) ? true : false;
var ns4 = (document.layers) ? true : false;
var ns6 = (document.getElementById && !document.all) ? true : false;
if (ie4) {document.all[lay].style.visibility = "hidden";}
if (ns4) {document.layers[lay].visibility = "hide";}
if (ns6) {document.getElementById([lay]).style.display = "none";}
}
function showlayer(lay) {
var ie4 = (document.all) ? true : false;
var ns4 = (document.layers) ? true : false;
var ns6 = (document.getElementById && !document.all) ? true : false;
if (ie4) {document.all[lay].style.visibility = "visible";}
if (ns4) {document.layers[lay].visibility = "show";}
if (ns6) {document.getElementById([lay]).style.display = "block";}
}
function writetolayer(lay,txt) {
if (ie4) {
document.all[lay].innerHTML = txt;
}
if (ns4) {
document[lay].document.write(txt);
document[lay].document.close();
}
if (ns6) {
over = document.getElementById([lay]);
range = document.createRange();
range.setStartBefore(over);
domfrag = range.createContextualFragment(txt);
while (over.hasChildNodes()) {
over.removeChild(over.lastChild);
}
over.appendChild(domfrag);
   }
}
//  end function



-->
</script>