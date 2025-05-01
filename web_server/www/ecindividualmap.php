<?PHP
/**
 * ecindividualmap.php
 *
 * longdesc
 *
 * LICENSE: This source file is subject to version 4.0 of the Creative Commons
 * license that is available through the world-wide-web at the following URI:
 * https://creativecommons.org/licenses/by/4.0/
 *
 * @category   Geochemistry
 * @package    ECDB Search
 * @author     Jason Ash <jasonash@ku.edu>
 * @copyright  IEDA (http://www.iedadata.org/)
 * @license    https://creativecommons.org/licenses/by/4.0/  Creative Commons License 4.0
 * @version    GitHub: $
 * @link       https://www.iedadata.org
 * @see        EarthChem, Geochemistry
 */

include('db.php');

include("get_pkey.php");

$myrand=rand(10000,99999);

//$myrow=$db->get_row("select * from coords where location_pkey=$pkey");

$myrow=$db->get_row("
select 
longitude as xval,
latitude as yval,
sample_id,
(select showname from datasources where name=lower(source)) as source
from material_level_metadata
where sample_pkey=$pkey limit 1
");




$longitude=$myrow->xval;
$latitude=$myrow->yval;

?>
<!doctype html>
<html lang="en">
  <head>
    <link rel="stylesheet" href="https://openlayers.org/en/v4.6.5/css/ol.css" type="text/css">
    <style>

		body{
			margin:0px;
			width:800px;
		}
		.map {
			height: 400px;
			width: 800px;
		}

		#latlonlabel {
			position: absolute;
			bottom: 10px;
			left: 10px;
			font-weight:bold;
			color:#CCC;
		}
    </style>
    <script src="https://openlayers.org/en/v4.6.5/build/ol.js" type="text/javascript"></script>
    <title>EarthChem Map</title>
  </head>
  <body>
    <div id="map" class="map"></div>
    <div id="latlonlabel"><?=$longitude?> <?=$latitude?></div>
    <script type="text/javascript">
      var iconFeature = new ol.Feature({
        geometry: new ol.geom.Point(ol.proj.transform([<?=$longitude?>,<?=$latitude?>], 'EPSG:4326', 'EPSG:3857')),
        name: 'EarthChem Point'
      });

      var iconStyle = new ol.style.Style({
        image: new ol.style.Icon(/** @type {olx.style.IconOptions} */ ({
          anchor: [0.5, 0.5],
          anchorXUnits: 'fraction',
          anchorYUnits: 'fraction',
          scale: 0.1,
          src: '/ectemplatefiles/images/circle-icon.png'
        }))
      });

      iconFeature.setStyle(iconStyle);

      var vectorSource = new ol.source.Vector({
        features: [iconFeature]
      });

      var vectorLayer = new ol.layer.Vector({
        source: vectorSource
      });
      
		//var osmLayer = new ol.layer.Tile({
		//source: new ol.source.OSM()
		//})

		var osmLayer = new ol.layer.Tile({
			title: "Global Imagery",
			source: new ol.source.TileWMS({
				url: 'https://www.gmrt.org/services/mapserver/wms_merc',
				params: {LAYERS: 'GMRT', VERSION: '1.1.1'}
			})
		})

      var map = new ol.Map({
        target: 'map',
        layers: [osmLayer, vectorLayer],
        view: new ol.View({
          center: ol.proj.fromLonLat([<?=$longitude?>, <?=$latitude?>]),
          zoom: 4
        })
      });
    </script>
  </body>
</html>

