<?PHP
/**
 * hist2dtas.php
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

// This page displays a 2D Histogram of data with associated search parameters  
include('get_pkey.php'); // get primary key for search 
include('db.php'); // database drivers, and connect

if($pkey==""){echo "no id provided";exit();}

$tas_hist_sql = pg_escape_string($_POST['tas_hist_sql']);

//echo nl2br($tas_hist_sql);exit();

//echo "pkey:$pkey";

$db->query("update search_query set earthchemwheretext='$tas_hist_sql' where pkey=$pkey");

//echo "/usr/bin/python hist2dtas.py $pkey";exit();


exec("/usr/bin/python3 hist2dtas3.py $pkey");

include 'includes/ks_head.html';


?>

<h2>TAS 2D Histogram</h2>

<div style="border:1px dashed #BBBBBB; padding:5px; background:#EEEEEE;">
The 2D Histogram adds a third dimension (color) to the standard graph. This allows graphs with large
number of samples to be more easily examined, as sample density becomes readily apparent.
</div>

<img src="hist2dplots/tas_<?=$pkey?>.png" width="950">















<?
include 'includes/ks_footer.html';
?>
