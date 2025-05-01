<?PHP
/**
 * log_download.php
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

// Insert a record in table search_query_downloads. 
// This should occur when the user downloads sample data from their search query result set in either xls or txt format (but not html output). 
// The field search_query_downloads.citations_logged is boolean, and is initially set to false. 
// When a regularly scheduled script runs and logs the citations in another table, the record in search_query_downloads is either deleted or marked citations_logged=true (not sure how Jason will handle this). 
// We will never need this record again, but keep the search_query_downloads table - in case we want to look up additional info about the download.

//$timestamp=time(); 
//$month = date("m",$timestamp);  2 digits with 0 padding
//$day = date("d",$timestamp);  2 digits with 0 padding
//$year = date("y",$timestamp);  4 digits
//$date = $month."/".$day."/".$year; // Won"t log this because the timestamp column contains the date and I can extract it
//$sources = $srcwhere; // The sources the user chose, e.g. PETDB, GEOROC, USGS, and/or NAVDAT
//$sql = "INSERT INTO search_query_downloads SELECT * FROM search_query WHERE pkey = ".$pkey;
//$sql = $db->query($sql) or die("Could not insert into search_query_downloads");
//$download_pkey = $db->get_var("SELECT MAX(download_pkey) FROM search_query_downloads WHERE pkey = $pkey") or die("Could not get the download_pkey");
//Update the additional fields that we need for logging the citations. Update the latest inserted records with this pkey; there could be more than 1 if the user downloaded more than 1 report.
//I think we do not need all of the search_query record"s fields, just the sql statement for the report which I store in download_sql.

$download_sql = $sql_getsample; // $sql_getsample is defined in the file that includes this one 
$download_sql = str_replace("\r"," ",$download_sql); // strip CR because these foil the insert attempt
$download_sql = str_replace("\n"," ",$download_sql); // strip newline because these foil the insert attempt
$download_sql = str_replace("\t"," ",$download_sql); // strip tab because these foil the insert attempt
$download_sql = str_replace("'","''",$download_sql); // Postgresql needs single quotes escaped 
// The column named timestamp defaults to now()
$insert_sql="INSERT INTO search_query_downloads (search_query_pkey,download_sql, citations_logged, output_type, rowtype, remote_host, remote_addr) 
VALUES ($pkey,'$download_sql',FALSE,'$dispmode','$rowtype','".$_SERVER["HTTP_HOST"]."','".$_SERVER["REMOTE_ADDR"]."') ";
$db->query($insert_sql) or die("Could not insert into search_query_downloads");

?>
