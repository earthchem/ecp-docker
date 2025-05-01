# EarthChem Portal (ECP)

The EarthChem Portal (ECP) is a one-stop shop for geochemistry data of the solid earth. It provides integrated search results by federating multiple data systems—including **PetDB**, **SedDB**, **GEOROC**, **NavDat**, **USGS**, and **GANSEKI**—and features mapping and visualization tools.

## About This Repository

This repository offers a portable, Docker-based infrastructure to run the ECP frontend applications, which include both the ECP Web and ECP WFS servers.

## Setting Up the Web Server

Follow these steps to configure and deploy the ECP web server:

1. **Obtain ECP Database Credentials**  
   Ensure you have the necessary database credentials for connecting to the ECP database.

2. **Create `config.inc.php`**  
   In the `web_server/dbconfig` directory, create a file named `config.inc.php` with the following contents:

   ```php
   <?
       $dbusername = "****";
       $dbpassword = "******";
       $dbname     = "****";
       $dbserver   = "******";
       $readonly   = "";
   ?>
   ```
3. **Create `config.inc.python.json`**  
   In the `web_server/dbconfig` directory, create a file named `config.inc.python.json` with the following contents:
   ```json
    {
        "pg_connection": {
            "host": "******",
            "user": "****",
            "passwd": "****",
            "db": "****"
        }
    }
   ```
4. **Update Docker Compose Configuration In the `docker-compose.yml`**  
    modify the source paths (e.g., on lines 11 and 14) to point to the correct directories:
    ```yml
        source: web_server_data/plots
        source: web_server_data/hist2dplots
    ```
5. **Deploy the Web Server Container Build and run the web server container using Docker Compose**
    ```bash
        docker compose -f docker-compose.yml up -d --build ecp_web
    ```
## Setting Up the Web Server

To configure the ECP WFS server, complete the following steps:
1. **Modify the `Dockerfile` In the `wfs_server`**  
    comment out line 12 by changing it to:
    ```Dockerfile
        # COPY ./wfs_server/geoserver-users.xml /usr/local/tomcat/webapps/wfs/data/security/usergroup/default/users.xml
    ```
2. **Update Docker Compose Configuration In the `docker-compose.yml`**  
    update the source path on line 26 to match your directory structure:
    ```yml
        source: wfs_server_data/logs
    ```
3. **Deploy the WFS Server Container Build and run the web server container using Docker Compose**
    ```bash
        docker compose -f docker-compose.yml up -d --build ecp_wfs
    ```
4. **Change the `admin` password in GeoServer**
    - Log into the GeoServer UI with the default `admin` account(username:admin, password:geoserver).
    - Change the `admin` password
5. **Update ECP database credential**
    - Navigate to the `Data` tab on the left navigation panel and click on the `Stores` link.
    - You will find a record(workspace:ECP,Store Name:ECP Postgis, Type:PostGIS) on the page 'Stores' and clik on the 'ECP Postgis' link.
    - Update `Connection Parameters` on the page `Edit Vector Data Source`
    
## Documentation (ecp_web source code:web_server/www)

### fonts
Contains fonts for various map interfaces.

### geopass
Contains code for interacting with geopass system.

### includes
Contains headers/footers for all ECP pages.

### js
Contains client-side scripts.

### svggraph
Library for creating SVG graphs.


### advancedoptions.php
Set advanced options for search output.

### advancedoutput.php
Advanced search output.

### alterationrocknameresponse.php
Response for alteration rock Ajax.

### bathy.map
Map file for overlaying MGDS bathymetry.

### bathy.php
Mapscript for overlaying MGDS bathymetry.

### bathypolar.map
Map file for overlaying MGDS bathymetry on south pole.

### citation_stats.php
Return monthly citation statistics.

### citation_stats_quarter.php
Return quarterly citation statistics.

### clearage.php
Clear age for search query.

### clearchemistry.php
Clear chemistry for search query.

### clearcruiseid.php
Clear cruise id for search query.

### cleargeology.php
Clear geology for search query.

### clearigsn.php
Clear igsn for search query.

### clearkeyword.php
Clear keyword for search query.

### clearlocation.php
Clear location for search query.

### clearmineral.php
Clear mineral for search query.

### clearoceanfeature.php
Clear ocean feature for search query.

### clearreference.php
Clear reference for search query.

### clearrocktype.php
Clear rocktype for search query.

### clearsampleid.php
Clear sample ID for search query.

### clearvolcano.php
Clear volcano for search query.

### datasources.php
Helper for getting list of datasources

### db.php
Database abstraction class loader.

### deletesavedquery.php
Delete saved user query.

### dynmap.map
Map file for creating dynamic map with search results.

### dynpolarmap.map
Map file for creating dynamic map with search results on south pole.

### earthchem_polygon_map.php
Map search interface.

### earthchem_south_polar_polygon_map.php
South pole map search interface.

### ecdynmap.php
Dynamic mapscript for 4326 projection.

### ecdynpolarmap.php
Dynamic mapscript for polar projection.

### ecstaticmap.php
Output static map for search query.

### ecstaticpolarmap.php
Output static polar map for search query.

### get_pkey.php
Helper for fetching pkey on page.

### getlevel2.php
Helper for rock classification Ajax calls.

### getlevel3.php
Helper for rock classification Ajax calls.

### getlevel4.php
Helper for rock classification Ajax calls.

### getoddlevels.php
Helper for rock classification Ajax calls.

### getrocknames.php
Helper for rock classification Ajax calls.

### glossary.php
ECP glossary system.

### glossary_add.php
Add glossary entry.

### glossary_delete.php
Delete glossary entry.

### glossary_publish.php
Publish glossary entry.

### glossary_unpublish.php
Unpublish glossary entry.

### glossaryrss.php
Glossary RSS feed.

### harker.php
Output harker diagram from search results.

### harkersvg.php
Output SVG harker diagram from search results.

### hist2dharker.php
Output 2D histogram harker diagram from search results.

### hist2dharker3.py
Output 2D histogram harker diagram from search results(python 3).

### hist2dtas.php
Output 2D histogram TAS diagram from search results.

### hist2dtas3.py
Output 2D histogram harker diagram from search results(python 3).

### holdings.php
Output current ECP holdings for inclusion on IEDA page.

### index.php
ECP landing page.

### legend.php
Legend for Google Earth.

### lepr_count_wrapper.php
Wrapper for LEPR counts.

### lepr_slider.php
Slider interfaces for LEPR.

### log_download.php
Log download from search query.

### log_downloads_citations.php
Log citation information for downloads.

### login.php
Login to the ECP system via GeoPass.

### logout.php
Logout of ECP system via GeoPass.

### mapquery.php
Detail for map interface.

### meltsinterface.php
Interface for MELTS interoperability.

### metamorphicrocknameresponse.php
Response for metamorphic rock Ajax.

### oceanfeaturepoly.php
Output image of ocean feature polygon.

### orerocknameresponse.php
Response for ore rock Ajax.

### polarbathy.map
Mapserver file for MGDS bathymetry on south polar projection.

### queries.php
Helper script for creating various queries for given search pkey.

### quickresults.php
Output basick html results with no output options.

### refoutput.php
Output html table with all references for given query.

### refoutputxls.php
Output XLS file with all references for given query.

### results.php
Output result options for given query pkey.

### rocknameresponse.php
Response for rockname Ajax.

### savedqueries.php
Saved queries user interface.

### savequery.php
Save query for saved queries interface.

### search.php
Main search landing page.

### searchaction.php
Update search query with form input.

### searchlist.php
Helper file for keeping track of current search criteria.

### searchpagepolarpoly.php
Display polygon for search page (Polar projection).

### searchpagepoly.php
Display polygon for search page.

### searchupdate.php
Update search query with form input.

### sedrocknameresponse.php
Response for sedimentary rockname Ajax.

### setage.php
UI for setting age parameters.

### setchemistry.php
UI for setting chemistry parameters.

### setcruiseid.php
UI for setting cruise ID.

### setdescription.php
UI for setting description.

### setgeology.php
UI for setting geology parameters.

### setigsn.php
UI for setting IGSN.

### setkeyword.php
UI for setting keyword.

### setlocation.php
UI for setting location parameters.

### setmapitem.php
UI for setting map parameters.

### setmaterial.php
UI for setting material parameters.

### setmethod.php
UI for setting method parameters.

### setmineral.php
UI for setting mineral.

### setnormalization.php
UI for setting normalization parameters.

### setoceanfeature.php
UI for setting ocean feature.

### setreference.php
UI for setting reference parameters.

### setrocktype.php
UI for setting rock type parameters.

### setsampleid.php
UI for setting sample ID.

### setvolcano.php
UI for setting volcano parameters.

### showecindividualmap.php
Display single sample on map.

### showstaticmap.php
Static map of search results.

### showstaticpolarmap.php
Static polar map of search results.

### srcwhere.php
Helper file for limiting results to specific federated partner databases.

### start_new_search.php
Landing page for setting up new search.

### svgtas.php
Output SVG TAS diagram for given query.

### tas.php
Output raster TAS diagram for give query.

### updateholdings.php
Update holding statistics for ECP database.

### updatesearch.php
Update query items for search UI.

### veinrocknameresponse.php
Response for vein rockname Ajax.

### volcanoimage.php
Output map with volcano.

### webservices.php
Documentation for WFS services.

### xenolithrocknameresponse.php
Response for vein rockname Ajax.

