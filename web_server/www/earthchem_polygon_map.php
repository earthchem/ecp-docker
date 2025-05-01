<?PHP
/**
 * earthchem_polygon_map.php
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

include('includes/ks_head.html');
include('get_pkey.php');

//<script src="http://openlayers.org/api/OpenLayers.js"></script>

?>






		<script src="openlayers/OpenLayers.js"></script>




        <h1 id="title">EarthChem Polygon Map</h1><br>



		

	<div style="margin-left:0px; margin-right:20px;">
  <table align="center" style=" cellspacing=0 cellpadding=0 border-collapse:collapse;">
  	<tr>

		<td align="left" class="mapTxt">
			<div class="mapdetails">
			Click on the map below to define a search polygon. Click on each vertex of your polygon, double-clicking
			on the last vertex to close the shape. You can use the zoom bar to zoom in and out. Use shift-click to create a smooth polygon
			with many vertices.
			</div>
		</td>

	</tr>
  </table></div>        

        <div id="mapdiv" class="smallmap"></div><br>
        




		<table>
			<tr>
				<td>
					<FORM name="mapform" id="mapform" action="searchupdate.php" method="post">
					<input type="hidden" id="polygon" name="polygon">
					<input type="hidden" id="pkey" name="pkey" value="<?=$pkey?>">
					<input type="hidden" id="glossary_pkey" name="glossary_pkey" value="<?=$glossary_pkey?>">
					<INPUT type="submit" value="Submit">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					</form>
				</td>
				<td>
					<button onclick="clearpoly();">Clear</button>
				</td>
			</tr>
		</table>

		<fieldset id="latlonbox" style="border: 1px solid #CDCDCD; padding: 8px; padding-bottom:0px; margin: 8px 0;width:785px;display:none;">
			<legend><strong>Current Polygon Coordinates</strong></legend>
			<div id="latlonlist" style="font-size:.8em;">
			
			</div>
		</fieldset>


        <script type="text/javascript">
            var map, vectors, controls;
            
				map = new OpenLayers.Map('mapdiv',{
					numZoomLevels : 12,
					maxExtent:new OpenLayers.Bounds(-20037508.3427, -15496570.7397, 20037508.3427, 18764656.2314),
					projection: 'EPSG:3395'
				});
				
				//singleTile: true,
				
				var wms = new OpenLayers.Layer.WMS( "IEDA/MGDS Bathymetry",
				"https://www.gmrt.org/services/mapserver/wms_merc?&", {layers: 'topo',format: 'png', SRS : "3395" },{wrapDateLine: true});

				var lines = new OpenLayers.Layer.WMS( "Lines",
                      "https://www.geochron.org/cgi-bin/mapserv?map=/public/mgg/web/www.geochron.org/htdocs/lines.map&",
                      {layers: 'state_line,country_line', transparent: true, format: 'png', SRS : "AUTO" },{wrapDateLine: true});

                // allow testing of specific renderers via "?renderer=Canvas", etc
                var renderer = OpenLayers.Util.getParameters(window.location.href).renderer;
                renderer = (renderer) ? [renderer] : OpenLayers.Layer.Vector.prototype.renderers;

				var styleMap = new OpenLayers.StyleMap({pointRadius: 3, strokeWidth: 1, fillColor: '#ff0000', fillOpacity: .3});
                
                vectors = new OpenLayers.Layer.Vector("Vector Layer", {renderers: renderer, styleMap: styleMap});

                map.addLayers([wms, lines, vectors]);
                map.addControl(new OpenLayers.Control.LayerSwitcher());
                map.addControl(new OpenLayers.Control.MousePosition());

				mypolygon = new OpenLayers.Control.DrawFeature(vectors, OpenLayers.Handler.Polygon, {eventListeners:{"featureadded": newPolygonAdded}})
				
				map.addControl(mypolygon);
				
				mypolygon.activate();

				function newPolygonAdded (evt) {
						//alert('Polygon completed');
						mystring = vectors.features[0].geometry.getVertices();
						//var mystringb = mystring.replace("POINT(","foo"); 
						//alert(mystringb);
						
						//console.debug(mystring);

						var coordlist="";
						var coorddelim="";
						for (var i = 0; i < mystring.length; i++) {
							//alert(mystring[i]['x']);
							coordlist=coordlist+coorddelim+mystring[i]['x']+" "+mystring[i]['y'];
							coorddelim="; ";
						}

						document.getElementById('polygon').value = coordlist;
						
						document.getElementById('latlonlist').innerHTML = coordlist;
						
						document.getElementById('latlonbox').style.display = "inline";

						mypolygon.deactivate(); //stops the drawing
					} 

				
                map.setCenter(new OpenLayers.LonLat(-101, 38), 0);

            

				function clearpoly(){
					//alert("clearpoly");
					vectors.removeAllFeatures();
					document.getElementById('polygon').value = "";				
					document.getElementById('latlonlist').innerHTML = "";
					document.getElementById('latlonbox').style.display = "none";
					mypolygon.activate();
				}

        </script>




<?php
include('includes/ks_footer.html');
?>
