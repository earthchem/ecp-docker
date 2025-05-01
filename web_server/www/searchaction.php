<?PHP
/**
 * searchaction.php
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

// This file is included in search.php, which should include get_key.php (gets the primary key for table search_query record) and db.php (includes the database drivers and connects to database)

//echo 'SELECT * from search_query where pkey = '.$pkey.'<br><br>';

//$searchquery=$db->get_row('SELECT * from search_query where pkey = '.$pkey) or die ('could not select record from search_query table in searchaction.php'); 

$searchquery=$db->get_row('SELECT * from search_query where pkey = '.$pkey);

$normalization = trim($searchquery->majorelementnormalization);
switch ($normalization) {
case 'Major_Elements_as_reported':
    $NormalizationTable = "view_data_output";
    break;
case 'FeO_as_Total':
    $NormalizationTable = "view_norm_feo_total";
    break;
case 'Fe203_as_Total':
    $NormalizationTable = "view_norm_fe2o3_total";
    break;
case 'FeO_and_Fe2O3':
    $NormalizationTable = "view_norm_feo_plus_fe2o3";
    break;
default:
    $NormalizationTable = "view_data_output"; 
}


$SQLSelect = "SELECT norm.*, samp.sample_id, samp.sample_num, age.age, age.age_max, age.age_min, age.calculated_age, age.calculated_age_max, age.calculated_age_min, age.geol_age_prefix, age.geol_age, loc.latitude, loc.longitude, ref.georef, ref.authors, ref.book_authors, ref.collection_authors, loc.state, meth.method_comment, meth.technique ";


$SQLCountSelect = 
"SELECT	sum(major_data_count) as smdc,
		sum(samp_trace_data_count) as stdc,
		sum(samp_isotope_data_count) as sidc,
		sum(samp_majtrace_data_count) as ssmtd,
		sum(samp_majtriso_data_count) as ssmdc,
		sum(samp_total_data_count) as total
FROM
(SELECT
	(CASE WHEN samp.major_data = 'y' THEN 1 ELSE 0 END) AS major_data_count,
	(CASE WHEN samp.trace_data = 'y' THEN 1 ELSE 0 END) AS samp_trace_data_count,
	(CASE WHEN samp.isotope_data = 'y' THEN 1 ELSE 0 END) AS samp_isotope_data_count,
	(CASE when samp.major_data='y' and samp.trace_data='y' then 1 else 0 end) as samp_majtrace_data_count,
	(CASE when samp.major_data='y' and samp.trace_data='y' and samp.isotope_data='y' then 1 else 0 end) as samp_majtriso_data_count,
	(CASE WHEN samp.sample_num > 0 then 1 else 0 end) as samp_total_data_count";

$SQLDescription = '';
$SQLQueryFormVariables = '';

$SQLFrom = " FROM sample samp, reference ref ";

$SQLWhere = '';
$ecwhere = '';




/*******************************************
Chemistry
*******************************************/
if ($searchquery->chemistry > '') { 
	$ecwhere .= ' AND chemistry_set ';
	$SQLWhere .= " AND $searchquery->CHEMISTRY ";
} 

if ($searchquery->lepr > '') { 
	$ecwhere .= ' AND lepr_set ';
	$SQLWhere .= " AND $searchquery->lepr ";
} 


/*******************************************
Age
*******************************************/
if ($searchquery->age_min != "" or $searchquery->age_max != "" or $searchquery->age != "" or $searchquery->geoage != "" or $searchquery->ageexists != "" ) { 
	$ecwhere .= ' AND age_set ';
	$SQLWhere .= " AND $searchquery->age ";
} 


/*******************************************
Geology
*******************************************/
if ($searchquery->geology != "" ) { 
	$ecwhere .= ' AND geology_set ';
	$SQLWhere .= " AND $searchquery->geology ";
} 

/*******************************************
Ocean Feature
*******************************************/
if ($searchquery->oceanfeature != "" ) { 
	$ecwhere .= ' AND oceanfeature_set ';
	$SQLWhere .= " AND $searchquery->oceanfeature ";
} 

/*******************************************
Volcano
*******************************************/
if ($searchquery->volcano != "" ) { 
	$ecwhere .= ' AND volcano_set ';
	$SQLWhere .= " AND $searchquery->volcano ";
} 



/**********************************
Location 
***********************************/
if ($searchquery->latitudenorth != '' and $searchquery->latitudesouth != '' and $searchquery->longitudewest != '' and $searchquery->longitudeeast != '') {
   $SQLFrom = 'FROM '.$NormalizationTable.' norm, sample samp, reference ref, batch bat, method meth, data_quality data_qual, analyses anal, batch bat';
	$SQLWhere .= ' AND 
	loc.latitude <= '.$searchquery->latitudenorth.'
	AND loc.latitude >= '.$searchquery->latitudesouth.'
	AND loc.longitude >= '.$searchquery->longitudewest.'
	AND loc.longitude <= '.$searchquery->longitudeeast.'
	AND loc.location_num = .samp.location_num ';
	$ecwhere .= ' AND 
	latitude >= '.$searchquery->latitudesouth.' 
	AND latitude <= '.$searchquery->latitudenorth.' 
	AND longitude >= '.$searchquery->longitudewest.' 
	AND longitude <= '.$searchquery->longitudeeast;
} // if


/******************************************************
Begin Rock Mode Here
******************************************************/
if ($searchquery->rockmode > '') { 
	$rockmode_array=split(',',$searchquery->rockmode);
	foreach($rockmode_array as $r) {
		$rocksql .= ' AND norm.mode_'.$r.' is not null ';
	} // foreach
	$rocksql = substr($rocksql,3); // remove the leading AND
	$sqlwhere .= " AND $rocksql ";
}



/**************************************
Title
**************************************/
if ($searchquery->title != '') {
	$ecwhere .= " and lower(title) like ''%".strtolower($searchquery->title)."%''";
}
// end Title


/**************************************
Journal
**************************************/
if ($searchquery->journal != '') { 
	$ecwhere .= " and lower(journal) like ''%".strtolower($searchquery->journal)."%''";
}
// end Journal



/******************************************************
Rock Mode
******************************************************/
if ($searchquery->rocktype != '') {
	if ($searchquery->rocktype == 'plutonic' or $searchquery->rocktype == 'volcanic') {
		$ecwhere .= " and lower(rock_type) = '".$searchquery->rocktype."'";
	}
}
// end Rock Mode

/******************************************************
rockname
******************************************************/
if ($searchquery->rockname > '') { 
	if (strlen($searchquery->rockname) > 0) {
		$rocknames=split(",", $searchquery->rockname);
		$rocknamesql = '';
		foreach ($rocknames as $t) {
			$rocknamesql.= " OR upper(rockname) = ''".strtoupper($t)."'' "; 
		}
		$rocknamesql = substr($rocknamesql,3); // remove the leading OR
   		$ecwhere .= ' AND ';
		$ecwhere .= $rocknamesql; 
	}
}// end Tasname


/******************************************************
rockclass
******************************************************/
if ($searchquery->rockclass > '') { 
	if (strlen($searchquery->rockclass) > 0) {
		$rocknames=split(",", $searchquery->rockclass);
		$rocknamesql = '';
		foreach ($rocknames as $t) {
			$rocknamesql.= " OR upper(rockclass) = ''".strtoupper($t)."'' "; 
		}
		$rocknamesql = substr($rocknamesql,3); // remove the leading OR
   		$ecwhere .= ' AND ';
		$ecwhere .= $rocknamesql; 
	}
}// end Tasname



/******************************************************
Tasname
******************************************************/
if ($searchquery->tasname > '') { 
	if (strlen($searchquery->tasname) > 0) {
		$tasnames=split(",", $searchquery->tasname);
		$tasnamesql = '';
		foreach ($tasnames as $t) {
			$tasnamesql.= " OR upper(tasname) = ''".strtoupper($t)."'' "; 
		}
		$tasnamesql = substr($tasnamesql,3); // remove the leading OR
   		$ecwhere .= ' AND ';
		$ecwhere .= $tasnamesql; 
	}
}// end Tasname


/******************************************************
Level1
******************************************************/
if ($searchquery->level1 > '') {
	$level1s=split(',', $searchquery->level1);
	$level1sql = '';
	foreach($level1s as $l) {
		$level1sql .= " OR upper(ec_level_1) = ''".strtoupper($l)."'' ";
	}
	$level1sql = substr($level1sql,3); // remove the leading OR 
	$ecwhere .= ' and ('.$level1sql.') '; 
}
// end Level1




/******************************************************
Level2 
******************************************************/
if ($searchquery->level2 > '') {
   $level2s=split(",", $searchquery->level2);
   $level2sql = '';
   foreach($level2s as $l) {
		$level2sql .= " OR upper(ec_level_2) = ''".strtoupper($l)."'' ";
		$level2sql = substr($level2sql,3); // remove the leading OR
	}
	$ecwhere .= ' AND ('.$level2sql.') '; 
}
// end Level2



/******************************************************
Level3  
******************************************************/
if ($searchquery->level3 > '') {
	$level3s=split(",", $searchquery->level3);
	$level3sql = '';
	foreach($level3s as $l) {
		$level3sql .= " OR upper(ec_level_3) = ''".strtoupper($l)."'' ";
	}
	$level3sql = substr($level3sql,3); // remove the leading OR 
	$ecwhere .= ' and ('.$level3sql.') '; 
}
// end Level3


/******************************************************
Level4
******************************************************/
if ($searchquery->level4 != '') {
	$level4s=split(",", $searchquery->level4);
	$level4sql = '';
	foreach($level4s as $l) {
		$level4sql .= " OR upper(ec_level_4) = ''".strtoupper($l)."'' ";
	}
	$level4sql = substr($level4sql,3); // remove the leading OR 
	$ecwhere .= ' ('.$level4sql.') '; 
}
// end Level4


/******************************************************
ECH
******************************************************/
if ($searchquery->ech != '') {
	$echs=split(",", $searchquery->ech);
	$echsql = '';
	foreach($echs as $ech) {
		$echsql .= " OR upper(ech) = ''".strtoupper($ech)."'' ";
	}
	$echsql = substr($echsql,3); // remove the leading OR 
	$ecwhere .= ' ('.$echsql.') '; 
}
// end Level4


/******************************************************
ChemMethods
******************************************************/
if ($searchquery->chemmethods > '') {
   $ecwhere .= " AND method IN ''".$searchquery->chemmethods."'') "; 
}
// end ChemMethods


/*********************************
Age - MinimumAge and MaximumAge
**********************************/
if ($searchquery->minimumage > '' or $searchquery->maximumage > '' AND !($searchquery->minimumage==0 AND $searchquery->maximumage==0) ) {
   $SampleAge = true;
   $GeologicalAgeData=$db->get_results('
      SELECT geol_age,
             geol_age_prefix,
             level,
             max_age,
             min_age
      FROM geological_time
      WHERE NOT (max_age < '.$searchquery->minimumage.' OR  min_age > '.$searchquery->maximumage.')' );
   $GeologicalAgePrefixData=$db->get_results("
      SELECT geol_age,
             geol_age_prefix,
             level,
             max_age,
             min_age
      FROM geological_time
      WHERE NOT (max_age < ".$searchquery->minimumage." OR  min_age > ".$searchquery->maximumage.") 
      AND NOT (geol_age_prefix = '')" );

   $Epoch = "x";
   $EpochPrefix = "x";

   //<!--- Loop Over the Data, and put one name in each of the geological name levels --->
   foreach($GeologicalAgePrefixData as $g) {
      if ($g['level'] == 'epoch') {
         $Epoch = $g['geol_age'];
         $EpochPrefix = $g['geol_age_prefix'];
      }
  	}
   // Make a list of names without any prefix (ie late, middle ...)
	foreach($GeologicalAgeList as $g) {
		$GeologicalAgeList .= ','.$g['geol_age'];
	}
	$GeologicalAgeList = substr($GeologicalAgeList,1); // remove the leading comma
   // Add ages with prefixes 
   foreach($GeologicalAgePrefixData as $g) {
      $GeologicalAgeList .= ', '.$g['geol_age_prefix'].' '.$g['geol_age']; 
   }

$GeologicalAgeList = str_replace(",", "','", $GeologicalAgeList); // Put the words in quotes 
   $SQLWhere .= ' AND age.sample_num = samp.sample_num and (age.calculated_age >= '.$searchquery->minimumage.' AND age.calculated_age <= '.$searchquery->maximumage.')'; 
}
// end Age



/******************************************
 Georef
******************************************/
if ($searchquery->georef > '') {
   $SQLWhere .= ' and ref.GEOREF = '.$searchquery->georef.' and bat.ref_num = ref.ref_num and samp.sample_num = bat.sample_num';
}
// end Georef


/******************************************
 Mineralname
******************************************/
//if ($searchquery->mineralname > '') {
if ($searchquery->mineralname != '') {
   //$ecwhere .= " and mineralname = ".$searchquery->mineralname." ";
//make case-insensitive for mineral name(by Peng)
   $ecwhere .= " and lower(mineralname) like ".$searchquery->mineralname." ";
}
// end Georef


/************************************
Author
************************************/
if ($searchquery->author != '') {
   $searchquery->author = trim($searchquery->author);
   $SQLWhere .= " AND (lower(ref.authors) LIKE ''%".strtolower($searchquery->author)."%'' OR lower(ref.book_authors) LIKE ''%".strtolower($searchquery->author)."%'' OR lower(ref.book_editors) LIKE ''%".strtolower($searchquery->author)."%'' OR lower(ref.collection_authors) LIKE ''%".strtolower($searchquery->author)."%'' OR lower(ref.corporate_author) LIKE ''%".strtolower($searchquery->author)."%'' )  AND bat.ref_num = ref.ref_num AND samp.sample_num = bat.sample_num ";
	$ecwhere .= " AND lower(author) LIKE ''%".strtolower($searchquery->author)."%'' ";
}
// end Author


/************************************
DOI
************************************/
if ($searchquery->doi != '') {
   $searchquery->doi = trim($searchquery->doi);
	$ecwhere .= " AND cit.doi = ".$searchquery->doi." ";
}
// end DOI


/*****************************************
Keyword 
*****************************************/
if ($searchquery->advkeyword1 > '') {
   $ecwhere .= " AND ADVKEYWORD1 like ''%".$searchquery->advkeyword1."%'' ";
   $SQLWhere .= " AND (ref.abstract LIKE ''%".$searchquery->advkeyword1."%'' OR ref.descriptors LIKE ''%".$searchquery->advkeyword1."%'' OR ref.title LIKE ''%".$searchquery->advkeyword1."%'' OR ref.notes LIKE ''%".$searchquery->advkeyword1."%'' OR ref.degree_granted LIKE ''%".$searchquery->advkeyword1."%'' OR ref.degree_granting_institution LIKE '%".$searchquery->advkeyword1."%' OR ref.book_title LIKE ''%".$searchquery->advkeyword1."%'' OR ref.journal LIKE ''%".$searchquery->advkeyword1."%'' ) AND bat.ref_num = ref.ref_num AND samp.sample_num = bat.sample_num ";
}
// end Keyword

/*****************************************
Sample ID 
*****************************************/
if ($searchquery->sampleid > '') {
   $ecwhere .= " AND sampleid like ''%".$searchquery->sampleid."%'' ";
   $SQLWhere .= " AND (ref.abstract LIKE ''%".$searchquery->sampleid."%'' OR ref.descriptors LIKE ''%".$searchquery->advkeyword1."%'' OR ref.title LIKE ''%".$searchquery->advkeyword1."%'' OR ref.notes LIKE ''%".$searchquery->advkeyword1."%'' OR ref.degree_granted LIKE ''%".$searchquery->advkeyword1."%'' OR ref.degree_granting_institution LIKE '%".$searchquery->advkeyword1."%' OR ref.book_title LIKE ''%".$searchquery->advkeyword1."%'' OR ref.journal LIKE ''%".$searchquery->advkeyword1."%'' ) AND bat.ref_num = ref.ref_num AND samp.sample_num = bat.sample_num ";
}
// end Sample ID 

/*****************************************
IGSN
*****************************************/
if ($searchquery->igsn > '') {
   $ecwhere .= " AND igsn like ''%".$searchquery->igsn."%'' ";
   $SQLWhere .= " AND (ref.abstract LIKE ''%".$searchquery->igsn."%'' OR ref.descriptors LIKE ''%".$searchquery->advkeyword1."%'' OR ref.title LIKE ''%".$searchquery->advkeyword1."%'' OR ref.notes LIKE ''%".$searchquery->advkeyword1."%'' OR ref.degree_granted LIKE ''%".$searchquery->advkeyword1."%'' OR ref.degree_granting_institution LIKE '%".$searchquery->advkeyword1."%' OR ref.book_title LIKE ''%".$searchquery->advkeyword1."%'' OR ref.journal LIKE ''%".$searchquery->advkeyword1."%'' ) AND bat.ref_num = ref.ref_num AND samp.sample_num = bat.sample_num ";
}
// end Sample ID 

/*****************************************
Cruise ID 
*****************************************/
if ($searchquery->cruiseid > '') {
   $ecwhere .= " AND cruiseid like ''%".$searchquery->cruiseid."%'' ";
   $SQLWhere .= " AND (ref.abstract LIKE ''%".$searchquery->sampleid."%'' OR ref.descriptors LIKE ''%".$searchquery->advkeyword1."%'' OR ref.title LIKE ''%".$searchquery->advkeyword1."%'' OR ref.notes LIKE ''%".$searchquery->advkeyword1."%'' OR ref.degree_granted LIKE ''%".$searchquery->advkeyword1."%'' OR ref.degree_granting_institution LIKE '%".$searchquery->advkeyword1."%' OR ref.book_title LIKE ''%".$searchquery->advkeyword1."%'' OR ref.journal LIKE ''%".$searchquery->advkeyword1."%'' ) AND bat.ref_num = ref.ref_num AND samp.sample_num = bat.sample_num ";
}
// end Sample ID 


/******************************************
Publication Year - Yearmin and Yearmax
******************************************/
if ($searchquery->yearmax != '' and $searchquery->yearmin != '') {
   $SQLWhere .= " AND ref.pub_year >= ".$searchquery->yearmin." AND ref.pub_year <= ".$searchquery->yearmax." AND bat.ref_num = ref.ref_num AND samp.sample_num = bat.sample_num ";
   $ecwhere .= " AND year >= ".$searchquery->yearmin." AND year <= ".$searchquery->yearmax;
}
// end Publication Year


$SQLFrom = " FROM $NormalizationTable norm, sample samp, location loc, sample_age age, batch bat, reference ref, method meth ";

$SQLWhere .= " AND norm.sample_num = samp.sample_num AND age.sample_num = samp.sample_num AND loc.location_num = samp.location_num AND bat.sample_num = samp.sample_num 
AND ref.ref_num = bat.ref_num AND meth.method_num = age.method_num  ";

// Finish these WHERE statements 
if (trim($SQLWhere) > '') {
	$SQLWhere = substr($SQLWhere,4); // remove the leading AND
	$SQLWhere = ' WHERE '.$SQLWhere;
}
if (trim($ecwhere)>'') {
	$ecwhere = substr($ecwhere,4); // remove the leading AND
	// We don't need the WHERE for this one which gets stored in the search_query table 
}

$additional='';

$getmethods=$db->get_row('select * from search_query where pkey = '.$pkey); //or die('could not execute getmethods query in searchaction.php');


































/* METHODS - Eileen the set method is not working yet, do this later 
<CFSET methodlist="">
<CFSET firstgo="yes">
<CFIF ListLen(methodlist) neq 0>
<CFSET additional=" and ( ">
		<CFLOOP INDEX="currcell" LIST="#methodlist#">
			<CFSET itemmethodlist="">
			<!--- parse here to test --->
			<CFSET curritem=listgetat(currcell, 1, ":")>
			<CFLOOP index="itemmethod" FROM="2" TO="#ListLen(currcell, ":")#">
				<CFSET itemmethodlist=ListAppend(itemmethodlist, listgetat(currcell, itemmethod, ":"))>
			</CFLOOP>
			<CFIF firstgo neq "yes">
			<CFSET additional=additional&" and ">
			<CFELSE>
			<CFSET firstgo="no">
			</CFIF>
			<CFSET itemlist="">
			<CFSET firstthismeth="yes">
			<CFLOOP INDEX="thismeth" LIST="#itemmethodlist#">
				<CFIF firstthismeth eq "yes">
					<CFSET itemlist="'#thismeth#'">
					<CFSET firstthismeth="no">
				<CFELSE>
					<CFSET itemlist=itemlist&",'#thismeth#'">
				</CFIF>
			</CFLOOP>
	<CFSET additional=
additional&" samp.sample_num IN(
SELECT DISTINCT(sample_num) FROM batch bat, chemistry chem, analyses anal, data_quality dq, method meth WHERE
chem.item_measured='#curritem#' and chem.analyses_num = anal.analyses_num and
anal.data_quality_num = dq.data_quality_num and dq.method_num = meth.method_num and meth.technique IN 
(SELECT method_in_datafile FROM method_translation
WHERE method_group IN (#itemlist#)) and dq.ref_num = bat.ref_num)">
		</CFLOOP>
		<CFSET additional=additional&")">
</CFIF>










<CFIF Trim(SQLWhere) neq "WHERE">
   <CFSET NavdatSQL = "#SQLSelect# #SQLFrom# #SQLWhere# #additional#">
   <CFSET NavdatCountSQL = "#SQLCountSelect# #SQLFrom# #SQLWhere# #additional#)">

<CFELSE>
   <CFSET NavdatSQL = "">
</CFIF>

<!---
<CFABORT>
--->

*/


/*
	<CFQUERY NAME="updateecwhere" DATASOURCE="#datasource#">
	  	update search_query set earthchemwhere = '#ecwhere#' where pkey = #search_query_pkey#
	</CFQUERY>
*/


$sql = "update search_query set earthchemwhere = '".$ecwhere."' WHERE pkey = ".$pkey;

$updateecwhere = $db->query($sql); // or die('could not update earthchemwhere in searchaction.php');



echo '</font>'; 
?>
