<?PHP
/**
 * ecinteractivepolarmap.php
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


//print_r($_POST);
//exit();

include('includes/ks_head.html');
//var_dump($_POST);
// Need this for show_my_form function, below, even though we don't need it for the query (queries.php adds $srcwhere to $mapstring)
// Note: a value of false arrives as "" and breaks the javascript, so force the var to a string of true or false 



include('db.php');
include('datasources.php');
include('srcwhere.php');
include('queries.php');

$dsvars="";
foreach($datasources as $ds){
	eval("\$thisval=\$".$ds->name.";");
	$dsvars.=$dsdelim.$ds->name."=".$thisval;
	$dsdelim="&";
}

$myrow=$db->get_row("select min(latitude) as south,
			max(latitude) as north,
			min(longitude) as west,
			max(longitude) as east
		from ($mapstring) foo");

$north=$myrow->north;
$south=$myrow->south;
$east=$myrow->east;
$west=$myrow->west;
		
$north=$north+3200000;
$south=$south-3200000;
$east=$east+3200000;
$west=$west-3200000;

/*
if($north>90){$north=90;}
if($south<-90){$south=-90;}
if($east>180){$east=180;}
if($west<-180){$west=-180;}


echo "north: $north<br>";
echo "south: $south<br>";
echo "east: $east<br>";
echo "west: $west<br>";
*/

$mapbounds="$west,$south,$east,$north";
		
//exit();


/*
echo "navdat: $navdat<br>";
echo "petdb: $petdb<br>";
echo "georoc: $georoc<br>";
echo "usgs: $usgs<br>";
*/


include("get_pkey.php");

//<script src="http://openlayers.org/api/OpenLayers.js"></script>
//<script src="oldopenlayers/OpenLayers.js"></script>
?>


    
	
	
	<script src="openlayers/OpenLayers.js"></script>


    <script src="mapAjaxRequest.js" type="text/javascript"></script>
    <script src="mapajax.js?<?=$dsvars?>" type="text/javascript"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/proj4js/2.3.15/proj4-src.js" type="text/javascript"></script>

<table width="100%" cellpadding="5">
<tr><td>

	<table>
	<tr><td colspan="2"><h1>Earthchem Dynamic Southern Polar Map</h1></td></tr>
	<tr>
		<td colspan="3">
			<div class="mapdetails">
			The map below contains samples from the current search query. You can drag the map dynamically,
			as well as use the zoom bar to zoom in and out. Use shift-click to create a dynamic zoom range for
			more detailed zooming. To get sample details, click on the individual samples.
			</div>
			<img src="legend.jpg" border="0">
		</td>
	</tr>
	<tr>
	<td valign="top">
		<div id="mapDivdyn"></div>
	</td>
	<td>&nbsp;&nbsp;&nbsp;</td>
	<td valign="top">
	    <div id="animationbar" style="display:none;"><img src="loadingAnimation.gif"></div>
		<div id="results">&nbsp;</div>
	</td>
	</tr>
	</table>
	<br>
	<br>
	<div id="pointdetail"></div>

  </body>
    <script type="text/javascript">
        <!--

			map = new OpenLayers.Map('mapDivdyn',{
				numZoomLevels : 12,
				maxExtent:new OpenLayers.Bounds(-8200000, -8200000, 8200000, 8200000),
				projection: 'EPSG:3031'
			});

            //map = new OpenLayers.Map('mapDivdyn', {maxResolution: 'auto'});

			var topo = new OpenLayers.Layer.WMS( "IEDA/MGDS Bathymetry",
			"https://www.gmrt.org/services/mapserver/wms_SP?", {layers: 'South_Polar_Bathymetry',format: 'png', SRS : "3031" },{wrapDateLine: true});

			topo.addOptions({isBaseLayer: true});

			<?
			$mapdsstring="ecdynpolarmap.php?pkey=$pkey";
			foreach($datasources as $ds){
				eval("\$thisdsval=\$".$ds->name.";");
				$mapdsstring.="&".$ds->name."=$thisdsval";
			
			}
			$mapdsstring.="&";
			?>
            
            var foolayer = new OpenLayers.Layer.WMS( "dump tile", "<?=$mapdsstring?>", {layers: 'basic'}, { singleTile: true, ratio: 1 } ); foolayer.addOptions({isBaseLayer: false});


			//map.addLayers([topo,foolayer]);
			
			map.addLayers([topo, foolayer]);
		
			var bounds = new OpenLayers.Bounds(<?=$mapbounds?>);
		
			map.zoomToExtent(bounds);

			//proj4.defs["EPSG:3031"] = "+proj=stere +lat_0=-90 +lat_ts=-71 +lon_0=0 +k=1 +x_0=0 +y_0=0 +ellps=WGS84 +datum=WGS84 +units=m +no_defs";
			

          	map.events.register("click", map, function(e) {
               	
               	var polarprojection = "+proj=stere +lat_0=-90 +lat_ts=-71 +lon_0=0 +k=1 +x_0=0 +y_0=0 +ellps=WGS84 +datum=WGS84 +units=m +no_defs";
               	var toprojection = "+proj=longlat +ellps=WGS84 +datum=WGS84 +no_defs";
               	
               	//var lonlat = map.getLonLatFromViewPortPx(e.xy);
				var lonlat = map.getLonLatFromPixel(e.xy);

               	var bounds = map.getExtent().toBBOX();
				
				var myzoom=map.getZoom();
				
				//alert(lonlat.lon+' '+lonlat.lat);
				
				//alert(proj4(firstprojection,[-71,41]));

				//alert(proj4(polarprojection,toprojection).forward([-2290300.585 , 916024.20385323]));
				
				var fixedcoords = proj4(polarprojection,toprojection).forward([lonlat.lon, lonlat.lat]);
				
				polarlon = fixedcoords[0];
				polarlat = fixedcoords[1];

				<?
				//$jsstring="show_my_form(lonlat.lat, lonlat.lon, $pkey";
				$jsstring="show_my_form(polarlat, polarlon, $pkey";
				foreach($datasources as $ds){
					eval("\$thisdsval=\$".$ds->name.";");
					$jsstring.=", $thisdsval";
				
				}
				$jsstring.=", myzoom);";
				?>
				
				//show_my_form(lonlat.lat, lonlat.lon, <?=$pkey?>, <?=$navdat?>, <?=$petdb?>, <?=$georoc?>, <?=$usgs?>, <?=$seddb?>, <?=$ganseki?>, myzoom);
				
				<?=$jsstring?>
           	});


        // -->
    </script>

</td></tr>
</table>

<?php
include('includes/ks_footer.html');
?>