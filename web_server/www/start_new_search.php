<?PHP
/**
 * start_new_search.php
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




//start db
include('db.php');

$pkey=$db->get_var("SELECT nextval('search_seq')");// or die('could not get pkey from search_seq in start_new_search.php');

$_POST['pkey']=$pkey;

$search_query_pkey=$pkey;

$TimeStamp=date("l, m,d,Y H:i:s");

// Get ip address and browers
$ip=$_SERVER['REMOTE_ADDR'];
$browser=$_SERVER['HTTP_USER_AGENT'];

// KGS database not setup to auto increment, so hit the table and grab the last record
// default values, add for KGS migration

$CurrentPkey=$pkey;

$MajorElementNormalization="Major_Elements_as_Reported";

$SearchDataSource = "NAVDAT";
$SearchOptions = "REGULAR";
$QueryName = "Untitled";
$UserPkey = 0; 
$LongitudeEast = 0.0;
$LongitudeWest = 0.0;
$LatitudeNorth = 0.0;
$LatitudeSouth = 0.0;
$MaximumAge = 0;
$MinimumAge = 0;
$YearMax = 0;
$YearMin = 0;
$NavdatSampleRecordCount = 0;
$DDS14SampleRecordCount = 0;
$NewMexicoSampleRecordCount = 0;
$IGS_DADSampleRecordCount = 0;
$PetrosSampleRecordCount = 0;
$USGSJNGSampleRecordCount = 0;
$Number_of_samples = 5;

// insert a new record into the search_query table

//echo "VALUES ($CurrentPkey,'$TimeStamp','$ip','$browser','$MajorElementNormalization','$SearchDataSource','$SearchOptions','$QueryName',$UserPkey,$LongitudeEast,$LongitudeWest,$LatitudeNorth,$LatitudeSouth,$MaximumAge,$MinimumAge,$Number_of_samples,$YearMax,$YearMin,$NavdatSampleRecordCount,$DDS14SampleRecordCount,$NewMexicoSampleRecordCount,$IGS_DADSampleRecordCount,$PetrosSampleRecordCount,$USGSJNGSampleRecordCount)";

/*
$db->query("INSERT INTO search_query (pkey,time_stamp,ip,browser,MajorElementNormalization,SearchDataSource,SearchOptions,QueryName,UserPkey,LongitudeEast,LongitudeWest,LatitudeNorth,LatitudeSouth,MaximumAge,MinimumAge,number_of_samples,YearMax,YearMin,NavdatSampleRecordCount,DDS14SampleRecordCount,NewMexicoSampleRecordCount,IGS_DADSampleRecordCount,PetrosSampleRecordCount,USGSJNGSampleRecordCount)
   VALUES ($CurrentPkey,'$TimeStamp','$ip','$browser','$MajorElementNormalization','$SearchDataSource','$SearchOptions','$QueryName',$UserPkey,$LongitudeEast,$LongitudeWest,$LatitudeNorth,$LatitudeSouth,$MaximumAge,$MinimumAge,$Number_of_samples,$YearMax,$YearMin,$NavdatSampleRecordCount,$DDS14SampleRecordCount,$NewMexicoSampleRecordCount,$IGS_DADSampleRecordCount,$PetrosSampleRecordCount,$USGSJNGSampleRecordCount)
	");*/
	
	// Eileen try leaving the fields null that are actually null instead of using 0 which is misleading 
	
	$db->query("INSERT INTO search_query (pkey,time_stamp,ip,browser,MajorElementNormalization,SearchDataSource,SearchOptions,QueryName)
   VALUES ($CurrentPkey,'$TimeStamp','$ip','$browser','$MajorElementNormalization','$SearchDataSource','$SearchOptions','$QueryName')
	");
	


?>