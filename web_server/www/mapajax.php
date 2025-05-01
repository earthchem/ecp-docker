<?PHP
/**
 * mapajax.php
 *
 * longdesc
 *
 * LICENSE: This source file is subject to version 4.0 of the Creative Commons
 * license that is available through the world-wide-web at the following URI:
 * https://creativecommons.org/licenses/by/4.0/
 *
 * @category   Geochemistry
 * @package    EarthChem Portal
 * @author     Jason Ash <jasonash@ku.edu>
 * @copyright  IEDA (http://www.iedadata.org/)
 * @license    https://creativecommons.org/licenses/by/4.0/  Creative Commons License 4.0
 * @version    GitHub: $
 * @link       http://ecp.iedadata.org
 * @see        EarthChem, Geochemistry
 */

header("Content-type: text/javascript");
include("db.php");
include("datasources.php");
include('srcwhere.php');
?>
/*
	function getRadio(radioObj) {
		if(!radioObj)
			return "";
		var radioLength = radioObj.length;
		if(radioLength == undefined)
			if(radioObj.checked)
				return radioObj.value;
			else
				return "";
		for(var i = 0; i < radioLength; i++) {
			if(radioObj[i].checked) {
				return radioObj[i].value;
			}
		}
		return "";
	}

	function getCheckbox(checkboxObj) {
		if(!checkboxObj)
			return "";
		var checkboxLength = checkboxObj.length;
		if(checkboxLength == undefined)
			if(checkboxObj.checked)
				return checkboxObj.value;
			else
				return "";
		var mydelim="";
		var mychecklist="";
		for(var i = 0; i < checkboxLength; i++) {
			if(checkboxObj[i].checked) {
				mychecklist=mychecklist+mydelim+checkboxObj[i].value;
				mydelim=", ";
			}
		}
		return mychecklist;
	}
*/




	function getRadio(radioObj) {
		if(!radioObj)
			return "";
		var radioLength = radioObj.length;
		if(radioLength == undefined)
			if(radioObj.checked)
				return radioObj.value;
			else
				return "";
		for(var i = 0; i < radioLength; i++) {
			if(radioObj[i].checked) {
				return radioObj[i].value;
			}
		}
		return "";
	}

	function getCheckbox(checkboxObj) {
		if(!checkboxObj)
			return "";
		var checkboxLength = checkboxObj.length;
		if(checkboxLength == undefined)
			if(checkboxObj.checked)
				return checkboxObj.value;
			else
				return "";
		var mydelim="";
		var mychecklist="";
		for(var i = 0; i < checkboxLength; i++) {
			if(checkboxObj[i].checked) {
				mychecklist=mychecklist+mydelim+checkboxObj[i].value;
				mydelim=", ";
			}
		}
		return mychecklist;
	}




	function fetch_myresults(mypkey){

		myobj=document.getElementsByName('mymap_tool');
		mytool = getRadio(myobj);

		if(mytool == 'identify'){

			//alert('you clicked point '+mypkey);
			AjaxRequest.get(
				{
					'url':'detail.cfm'
					,'parameters':{ 'pkey':mypkey }
					//,'onSuccess':function(req){ updatebox.value=req.responseText; }
					//,'onSuccess':function(req){ alert('Success!'); }
					//,'onSuccess':function(req) { document.getElementById(updatebox).innerHTML = req.url; }
					,'onSuccess':function(req) { document.getElementById('pointdetail').innerHTML = req.responseText; }
					,'onError':function(req){ document.getElementById('pointdetail').innerHTML = req.responseText;}
					//,'onError':function(req){ alert('Error!\nStatusText='+req.statusText+'\nContents='+req.responseText);}
				}
			);

		}else{

		null;

		}

	}

	function showanimation(){
		document.getElementById('results').style.display="none";
		document.getElementById('animationbar').style.display="block";
	}

	function hideanimation(){
		document.getElementById('animationbar').style.display="none";
		document.getElementById('results').style.display="block";
	}

	<?
	$funcstring="function show_my_form(mylat,mylon,mysqpkey";
	foreach($datasources as $ds){
		$funcstring.=",my".$ds->name;
	}
	$funcstring.=",myzoom,myitem){";
	echo $funcstring;

	$funcstring=",'parameters':{ 'mylat':mylat, 'mylon':mylon, 'pkey':mysqpkey";
	foreach($datasources as $ds){
		$funcstring.=", '".$ds->name."':my".$ds->name;
	}
	$funcstring.=", 'zoom':myzoom, 'legitem':myitem }";
	?>

	//function show_my_form(mylat,mylon,mysqpkey,mynavdat,mypetdb,mygeoroc,myusgs,myseddb,myganseki,myzoom,myitem){

			//document.getElementById('results').innerHTML = '<img src="loadingAnimation.gif">';
			showanimation();

			//alert('you clicked point '+myseddb);
			AjaxRequest.get(
				{
					'url':'mapquery.php'
					<?=$funcstring?>
					
					//,'parameters':{ 'mylat':mylat, 'mylon':mylon, 'pkey':mysqpkey, 'navdat':mynavdat, 'petdb':mypetdb, 'georoc':mygeoroc, 'usgs':myusgs, 'seddb':myseddb, 'ganseki':myganseki, 'zoom':myzoom, 'legitem':myitem }
					//,'onSuccess':function(req){ updatebox.value=req.responseText; }
					//,'onSuccess':function(req){ alert('Success!'); }
					//,'onSuccess':function(req) { document.getElementById(updatebox).innerHTML = req.url; }
					,'onSuccess':function(req) { document.getElementById('results').innerHTML = req.responseText; hideanimation(); }
					,'onError':function(req){ document.getElementById('pointdetail').innerHTML = req.responseText;}
					//,'onError':function(req){ alert('Error!\nStatusText='+req.statusText+'\nContents='+req.responseText);}
				}
			);


	}








	<?
	$funcstring="function show_detail(mypkey,mylat,mylon,mysearch_query_pkey";
	foreach($datasources as $ds){
		$funcstring.=",my".$ds->name;
	}
	$funcstring.=",myzoom,myitem){";
	echo $funcstring;

	$funcstring=",'parameters':{ 'sample_pkey':mypkey, 'mylat':mylat, 'mylon':mylon, 'pkey':mysearch_query_pkey";
	foreach($datasources as $ds){
		$funcstring.=", '".$ds->name."':my".$ds->name;
	}
	$funcstring.=", 'zoom':myzoom, 'legitem':myitem }";
	?>


	//function show_detail(mypkey,mylat,mylon,mysearch_query_pkey,mynavdat,mypetdb,mygeoroc,myusgs,myseddb,myganseki,myzoom,myitem){
	
			showanimation();


			//alert('you clicked point '+mypkey);
			AjaxRequest.get(
				{
					'url':'mapquery.php'
					//,'parameters':{ 'sample_pkey':mypkey, 'mylat':mylat, 'mylon':mylon, 'pkey':mysearch_query_pkey, 'navdat':mynavdat, 'petdb':mypetdb, 'georoc':mygeoroc, 'usgs':myusgs, 'seddb':myseddb, 'ganseki':myganseki, 'zoom':myzoom, 'legitem':myitem }
					<?=$funcstring?>
					//,'onSuccess':function(req){ updatebox.value=req.responseText; }
					//,'onSuccess':function(req){ alert('Success!'); }
					//,'onSuccess':function(req) { document.getElementById(updatebox).innerHTML = req.url; }
					,'onSuccess':function(req) { document.getElementById('results').innerHTML = req.responseText;  hideanimation(); }
					,'onError':function(req){ document.getElementById('pointdetail').innerHTML = req.responseText;}
					//,'onError':function(req){ alert('Error!\nStatusText='+req.statusText+'\nContents='+req.responseText);}
				}
			);


	}


	function close_results(){


	document.getElementById('pointdetail').innerHTML = "";


	}

	function show_new_marker(mytext){

		mytext=mytext.replace(/ /,'');
		temp=mytext.split(',');

		mylon=temp[0];
		mylat=temp[1];
		mypkey=temp[2];

		//document.getElementById('foofoo').innerHTML = 'lon = '+mylon+' lat = '+mylat+' pkey = '+mypkey;

		AutoSizeAnchored = OpenLayers.Class(OpenLayers.Popup.Anchored, {
    		'autoSize': true
		});

		//anchored popup small contents autosize
		ll = new OpenLayers.LonLat(LonToGmaps(mylon),LatToGmaps(mylat));
		popupClass = AutoSizeAnchored;
		popupContentHTML = mypkey;
		addMarker(ll, popupClass, popupContentHTML);


	}


	function greetclear(updatebox, pkey){
		AjaxRequest.get(
			{
				'url':'greetingclear.cfm'
				,'parameters':{ 'item':updatebox, 'pkey':pkey }
				//,'onSuccess':function(req){ updatebox.value=req.responseText; }
				//,'onSuccess':function(req){ alert('Success!'); }
				//,'onSuccess':function(req) { document.getElementById(updatebox).innerHTML = req.url; }
				,'onSuccess':function(req) { document.getElementById(updatebox).innerHTML = '&nbsp;'; }
				,'onError':function(req){ alert('Error!\nStatusText='+req.statusText+'\nContents='+req.responseText);}
			}
		);

	}

