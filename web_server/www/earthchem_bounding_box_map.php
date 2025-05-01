<?PHP
/**
 * earthchem_bounding_box_map.php
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

include("../earthchemphp/includes/ks_head.html");
include('get_pkey.php');
?>
<h1>Set Location with Bounding Box on Interactive Map</h1>
<div id="mapwrapper">Define the envelope by clicking on two location points (opposite corners of the bounding box) on the map.
<br /><br />To remove a marked location, click on the marker <img src="images/marker.png" alt="Marker" hspace="5" vspace="0" border="0">. To remove all marked locations, click a <input type="image" name="clear1" src="images/clear.gif" alt="Clear" style="margin:0px 5px;border:none;vertical-align;bottom" onClick="javascript: fnClear();"> button.
<br /><br />
<input type="image" style="margin:0px 0px 0px 0px"  name="clear1" src="images/clear.gif" alt="Clear" hspace="0" vspace="0" border="0" onClick="javascript: fnClear();">
<input type="image" style="margin:0px 0px 0px 0px"  name="submit2" src="images/submit.gif" alt="Submit" hspace="0" vspace="0" border="0" onClick="javascript: submitmap();">
<DIV id="map" style="width:700px;height:500px;border:solid 1px black;margin:5px 0px 5px 0px;padding:0px"></DIV>
<input type="image" style="margin:0px 0px 0px 0px"  name="clear2" src="images/clear.gif" alt="Clear" hspace="0" vspace="0" border="0" onClick="javascript: fnClear();">
<input type="image" style="margin:0px 0px 0px 0px"  name="submit2" src="images/submit.gif" alt="Submit" hspace="0" vspace="0" border="0" onClick="javascript: submitmap();">
</div>




  <div id="urls"></div>

    <script type="text/javascript">


    var num_markers = 0;
	var marker = new Array();
	marker[1] = null;
	marker[2] = null;
	var ptLat = new Array();
	ptLat[1] = -1;
	ptLat[2] = -1;
	var ptLong = new Array();
	ptLong[1] = -1;
	ptLong[2] = -1;
	env = '';
	var tminx, tmaxx;
	var tminy, tmaxy;
	var cnt_x, cnt_y;

	var map = new GMap2(document.getElementById("map"), {draggableCursor: 'crosshair', draggingCursor: 'crosshair'});

    // Create tile layers
    // Topography from ETOPO2 and GTOPO30
    var topography= new GTileLayer(new GCopyrightCollection(""),1,17);
	topography.myBaseURL='http://geoportal.kgs.ku.edu/googlemaps/arcims/arcims_image.cfm?servername=geoportal.kgs.ku.edu&mapservice=OBIS_XML_PNG&layer_id=6,7,0&cache_name=global_topog';
    topography.getTileUrl=CustomGetTileUrl;

	//topography.getOpacity = function() {return 0.5;}

    // Generalized geological map of the world (1995)
    var geology= new GTileLayer(new GCopyrightCollection(""),1,17);
	geology.myBaseURL='http://geoportal.kgs.ku.edu/googlemaps/arcims/arcims_image.cfm?servername=geoportal.kgs.ku.edu&mapservice=WORLD_GEOLOGY&layer_id=5,4,0&cache_name=global_geology&image_type=png';
    geology.getTileUrl=CustomGetTileUrl;
	//geology.getOpacity = function() {return 0.5;}


 	var tileDoq= new GTileLayer(new GCopyrightCollection(""),1,17);
	tileDoq.myLayers='topo';
	tileDoq.myFormat='image/jpeg';
    tileDoq.myBaseURL='http://www.marine-geo.org/services/wms?';
 	tileDoq.getTileUrl=CustomGetTileUrlb;
	tileDoq.getOpacity = function() {return 1.0;}

	var layer1=[geology];
	var layer2=[geology,G_HYBRID_MAP.getTileLayers()[1]];
	var layer3=[topography];
	var layer4=[topography,G_HYBRID_MAP.getTileLayers()[1]];
	var layer5=[tileDoq];
	var layer6=[tileDoq,G_HYBRID_MAP.getTileLayers()[1]];

    var geologyOnly = new GMapType(layer1, G_SATELLITE_MAP.getProjection(), "Geology", G_SATELLITE_MAP);
    var geologyHybrid = new GMapType(layer2, G_SATELLITE_MAP.getProjection(), "Geology Hybrid", G_SATELLITE_MAP);
	var topogOnly = new GMapType(layer3, G_SATELLITE_MAP.getProjection(), "Topography", G_SATELLITE_MAP);
    var topogHybrid = new GMapType(layer4, G_SATELLITE_MAP.getProjection(), "Topography Hybrid", G_SATELLITE_MAP);
	var bathy = new GMapType(layer5, G_SATELLITE_MAP.getProjection(), "Topo", G_SATELLITE_MAP);
	var bathyhybrid = new GMapType(layer6, G_SATELLITE_MAP.getProjection(), "Topo Hybrid", G_SATELLITE_MAP);

 	map.getMapTypes().length = 0;

	map.addMapType(bathy);
	map.addMapType(bathyhybrid);
	map.addMapType(G_HYBRID_MAP);
	map.addMapType(G_SATELLITE_MAP);

    var newZoom = 17 - 15;

	map.setCenter(new GLatLng(0,0), newZoom);
    map.addControl(new GLargeMapControl());
    map.addControl(new GMapTypeControl());
    map.addControl(new GScaleControl());

	var thisEnv = null;

	GEvent.addListener(map, 'click', selectEnvelope);

	function selectEnvelope(overlay, point)
	{
		if (overlay)
		{
			num_markers--;
			if (num_markers == 1) fnRemovePolyline();
			var swap = 0;
			if (overlay == marker[1])
				swap = 1;
			map.removeOverlay(overlay);
			document.getElementById('lat2').innerHTML = '';
			document.getElementById('long2').innerHTML = '';
			if (swap == 1)
			{
				marker[1] = marker[2];
				ptLat[1] = ptLat[2];
				ptLong[1] = ptLong[2];
				if (num_markers == 1)
				{
					document.getElementById('lat1').value = ptLat[1];
					document.getElementById('long1').value = ptLong[1];
					document.getElementById('latitudenorth').value = ptLong[1];
				}
				else
				{
					document.getElementById('lat1').value = '';
					document.getElementById('long1').value = '';
				}
			}
		}
		else if (point)
		{
			if (num_markers == 2) return;
			num_markers++;

			marker[num_markers] = new GMarker(point);
			map.addOverlay(marker[num_markers]);

			ptLat[num_markers] = point.y;
			ptLong[num_markers] = point.x;
			if (num_markers == 2) drawPolylines();

			document.getElementById('lat'+num_markers).value = point.y;
			document.getElementById('long'+num_markers).value = point.x;
		}
	}

	function drawPolylines()
	{
		var mid1_lat = -1;
		var mid1_long = -1;
		var mid2_lat = -1;
		var mid2_long = -1;
		mid1_lat = ptLat[1];
		mid1_long = ptLong[2];
		mid2_lat = ptLat[2];
		mid2_long = ptLong[1];

		var points = [];
		points.push(new GPoint(ptLong[1], ptLat[1]));
		points.push(new GPoint(mid1_long, mid1_lat));
		points.push(new GPoint(ptLong[2], ptLat[2]));
		points.push(new GPoint(mid2_long, mid2_lat));
		points.push(new GPoint(ptLong[1], ptLat[1]));
		thisEnv = new GPolyline(points, "#ff0000");
		map.addOverlay(thisEnv);
	}

	function fnClear()
	{
		num_markers = 0;
		map.clearOverlays();
		document.getElementById('lat1').innerHTML = '';
		document.getElementById('long1').innerHTML = '';
		document.getElementById('lat2').innerHTML = '';
		document.getElementById('long2').innerHTML = '';
	}

	function fnRemovePolyline()
	{
		map.removeOverlay(thisEnv);
	}

	function submitmap()
	{
		if (num_markers != 2)
		{
			alert("Please define the envelope.");
			return;
		}

  		document.mapform.submit();
	}

	function fnSearch()
	{
		if (num_markers != 2)
		{
			alert("Please define the envelope.");
			return;
		}

		var thisminx = -1;
		var thismaxx = -1;
		var thisminy = -1;
		var thismaxy = -1;

		if (ptLong[1] < ptLong[2]) { thisminx = ptLong[1]; thismaxx = ptLong[2]; }
		   else { thisminx = ptLong[2]; thismaxx = ptLong[1]; }
		if (ptLat[1] < ptLat[2]) { thisminy = ptLat[1]; thismaxy = ptLat[2]; }
		   else { thisminy = ptLat[2]; thismaxy = ptLat[1]; }

		if (window.opener.frmSearch == undefined)
		{
			if (window.opener.tab == 'adr')
			{
				window.opener.env_from_gmap = 1;
				window.opener.env_from_afinder = 0;
				window.opener.document.search.street.value='';
				window.opener.document.search.place.value='';
				window.opener.document.search.zip.value='';
				window.opener.fnSearchAddress(thisminx, thismaxx, thisminy, thismaxy);
			}
		}
		else
		{
			if (window.opener.frmSearch.tab == 'adr')
			{
				window.opener.frmSearch.env_from_gmap = 1;
				window.opener.frmSearch.env_from_afinder = 0;
				window.opener.frmSearch.document.search.street.value='';
				window.opener.frmSearch.document.search.place.value='';
				window.opener.frmSearch.document.search.zip.value='';
				window.opener.frmSearch.fnSearchAddress(thisminx, thismaxx, thisminy, thismaxy);
			}
		}
		window.opener.focus();
	}

    </script>



	<form name="mapform" id="mapform" action="search.php" method="post">
	<input type="hidden" id="lat1" name="lat1">
	<input type="hidden" id="lat2" name="lat2">
	<input type="hidden" id="long1" name="long1">
	<input type="hidden" id="long2" name="long2">

	<input type="hidden" id="pkey" name="pkey" value="<?=$pkey?>">
	<input type="hidden" name="search_query_pkey" value="<?=$pkey?>">
	<input type="hidden" name="glossary_pkey" value="<?=$glossary_pkey?>">
	</form>

	<script type="text/javascript">

		if (env != '')
		{
			GEvent.trigger(map, 'click', null, new GPoint(tminx, tmaxy));
			GEvent.trigger(map, 'click', null, new GPoint(tmaxx, tminy));
		}

	</script>


</div>
<!-- end pad -->

<?
include("includes/ks_footer.html");
?>