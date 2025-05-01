<?PHP
/**
 * advancedoutputc.php
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


//echo "<!--";
//echo "petdb: ".$_POST['petdb'];
//echo "-->";
ini_set('memory_limit','64000M');
ini_set('max_execution_time', 7200);

function fixstring($string){
	$string=htmlspecialchars($string);
	//$string=str_replace("'","&#039;",$string);
	$string=str_replace("'","",$string);
	return $string;
}

//echo fixstring("o'neil");

include "get_pkey.php"; // get primary key for this search 

include "db.php"; // load database drivers and connect to the database

include("datasources.php");

//include "srcwhere.php"; // get the sources 

$srcwhere = $db->get_var("select srcwhere from search_query where pkey=$pkey");

//echo "srcwhere: $srcwhere";exit();

/*
foreach($datasources as $ds){
	eval("\$thisdsval=\$".$ds->name.";");
	$urldsstring.="&".$ds->name."=".$thisdsval;
}
*/

//echo $urldsstring;
//exit();

//if (strlen($srcwhere)>0) {$source = $srcwhere;} else {$source="all";}  // $srcwhere defined in srcwhere.php above. This variable is for the log_download.php.
$dispmode = (isset($_GET['dispmode'])) ? $_GET['dispmode'] : 'html'; // default display mode is html output; others are text and xls 
$showmethods = (isset($_GET['showmethods']) && $_GET['showmethods']) ? $_GET['showmethods'] : false;
$showunits = (isset($_GET['showunits']) && $_GET['showunits']) ? $_GET['showunits'] : false;
if (isset($_POST['rowtype'])) {
	$rowtype=$_POST['rowtype'];
} elseif (isset($_GET['rowtype'])) {
	$rowtype=$_GET['rowtype'];
} else {
	$rowtype = ''; // error
} 

$csvlink="advancedoutputc.php?pkey=$pkey&dispmode=text&rowtype=$rowtype&showmethods=$showmethods&showunits=$showunits";
$xlslink="advancedoutputc.php?pkey=$pkey&dispmode=xlsx&rowtype=$rowtype&showmethods=$showmethods&showunits=$showunits";


/*
Do this after the search query is executed, below.
if ($dispmode != 'html' and $rowtype != 'sample') { // For xls and text downloads with rowtype of method, log this download. An automatically scheduled php program will periodically use this record to log the author citations for the samples in this report. 'Search Citations' lets the user look at these citations.
	include "log_download.php";  
}
*/

$searchquery=$db->get_row("SELECT * FROM search_query WHERE pkey = $pkey") or die("could not execute searchquery in advancedoutputc.php"); // Get the search record

// Make a concatenated list of all the chemicals in the 'group' fields e.g. search_query.MAJOR_OXIDES 


$allfields_list="$searchquery->major_oxide,$searchquery->ratio,$searchquery->noble_gas,$searchquery->rare_earth_element,$searchquery->uranium_series,$searchquery->volatile,$searchquery->trace_element,$searchquery->stable_isotopes,$searchquery->lepr_items,$searchquery->radiogenic_isotopes";

$allfields_array_temp=explode(',',trim($allfields_list));
$allfields_array=array();
for($i=0;$i<=sizeof($allfields_array_temp);$i++) { 
	if ($allfields_array_temp[$i]=='') {} else {$allfields_array[]=$allfields_array_temp[$i];}
}
unset($allfields_array_temp);

include('queries.php'); // All the queries we may need are in here 

$numtoshow = (isset($_GET['numresults'])) ? $_GET['numresults'] : 50 ; // Show $numtoshow records per html page in the report
$currentpage = (isset($_GET['currentpage']) && is_numeric($_GET['currentpage']) ) ? $_GET['currentpage'] : 1; // We are on page $currentpage of a multipage report 
$startrow=($currentpage-1)*$numtoshow+1; 
$endrow=$currentpage*$numtoshow;

//echo nl2br($advstring);
//exit();

//echo '<script>' . 'console.log(' . json_encode($samplechemstring, JSON_HEX_TAG) . ');' . '</script>';



 
// Get the list of all the data fields 
$showlist_array=array();

//$getfieldnames=$db->get_results("SELECT DISTINCT(field_name), DATA_ORDER FROM data_fields ORDER BY DATA_ORDER ASC") or die('could not execute getfieldnames query in advancedoutputc.php');

$getfieldnames=$db->get_results("SELECT upper(useval) as field_name, display_name, display_order as data_order 
								FROM petdb_vars
								group by useval, display_name, display_order ORDER BY display_order ASC") or die('could not execute getfieldnames query in advancedoutputc.php');


$fieldlist_array=array();
$display_list=array();
foreach($getfieldnames as $g) { 
	if (in_array($g->field_name,$allfields_array)) { 
		$showlist_array[] = $g->field_name;
		$display_list[] = $g->display_name;
	} // if
	$fieldlist_array[] = $g->field_name;
} // foreach $getfieldnames

//******************************************************************************************
// Added by PJ on Mar 7 2024 to generate ordered variable-units combined column header
//******************************************************************************************
$get_variableunits = $db->get_results($samplechemaggstring) or die("could not execute getsample query in advancedoutputc.php");
$vu_list = array();
foreach($get_variableunits as $g){
	foreach($g as $key=>$value){
		$vu_list[]=$value;
	}
} // foreach $get_variableunits
//******************************************************************************************

$sql_getsample='';


//******************************************************************************************
//******************************************************************************************
//******************************************************************************************



// XLS OUTPUT
// If the display mode is Excel spreadsheet, redirect (where the script will create an xls file) and exit this page.
if ($dispmode == "xlsx") {

	if (isset($_GET['totalcount'])) { 
		$totalcount=$_GET['totalcount'];
	} else { 
		if ($rowtype == "method") {
			//$sql_totalcount = " SELECT COUNT(*) as totalcount FROM ( $advstring ) foo "; 
			$sql_totalcount = $advcount; 
		} else { // rowtype == 'sample' 
		
		
		
			$sql_totalcount = $samplecount;

		} // if, else 
		//debug: echo "<p>sql_totalcount is <br>$sql_totalcount ";
	
		//echo " $sql_totalcount <br><br><br>";
	
		//exit();
	
	
		//echo nl2br($sql_totalcount);
		$totalcount_query=$db->get_row($sql_totalcount) or die("could not execute totalcount query in advancedoutputc.php");
		$totalcount=$totalcount_query->totalcount;
	} // if, else 
	//echo "<p>totalcount is $totalcount";

	// If there no records (samples) were returned from our search, show a message and inelegantly exit. 
	if ($totalcount < 1) {
		include "includes/ks_head.html";
		echo "<h2>Sorry, no records found.</h2>";
	?>
	<div id="debug" style="display:none;">
	<?
	echo nl2br($sql_totalcount);
	?>
	</div>
	<?
	//echo "<!-- query";
	//echo nl2br($sql_totalcount);
	//echo "query -->";
		include "includes/ks_footer.html";
		exit; // Eileen change this inelegant logic. For now, it's more readable for me to do it this way. 
	}

	//echo "entering into xlsx";exit();
	
	$collabels[0]="A";
	$collabels[1]="B";
	$collabels[2]="C";
	$collabels[3]="D";
	$collabels[4]="E";
	$collabels[5]="F";
	$collabels[6]="G";
	$collabels[7]="H";
	$collabels[8]="I";
	$collabels[9]="J";
	$collabels[10]="K";
	$collabels[11]="L";
	$collabels[12]="M";
	$collabels[13]="N";
	$collabels[14]="O";
	$collabels[15]="P";
	$collabels[16]="Q";
	$collabels[17]="R";
	$collabels[18]="S";
	$collabels[19]="T";
	$collabels[20]="U";
	$collabels[21]="V";
	$collabels[22]="W";
	$collabels[23]="X";
	$collabels[24]="Y";
	$collabels[25]="Z";
	$collabels[26]="AA";
	$collabels[27]="AB";
	$collabels[28]="AC";
	$collabels[29]="AD";
	$collabels[30]="AE";
	$collabels[31]="AF";
	$collabels[32]="AG";
	$collabels[33]="AH";
	$collabels[34]="AI";
	$collabels[35]="AJ";
	$collabels[36]="AK";
	$collabels[37]="AL";
	$collabels[38]="AM";
	$collabels[39]="AN";
	$collabels[40]="AO";
	$collabels[41]="AP";
	$collabels[42]="AQ";
	$collabels[43]="AR";
	$collabels[44]="AS";
	$collabels[45]="AT";
	$collabels[46]="AU";
	$collabels[47]="AV";
	$collabels[48]="AW";
	$collabels[49]="AX";
	$collabels[50]="AY";
	$collabels[51]="AZ";
	$collabels[52]="BA";
	$collabels[53]="BB";
	$collabels[54]="BC";
	$collabels[55]="BD";
	$collabels[56]="BE";
	$collabels[57]="BF";
	$collabels[58]="BG";
	$collabels[59]="BH";
	$collabels[60]="BI";
	$collabels[61]="BJ";
	$collabels[62]="BK";
	$collabels[63]="BL";
	$collabels[64]="BM";
	$collabels[65]="BN";
	$collabels[66]="BO";
	$collabels[67]="BP";
	$collabels[68]="BQ";
	$collabels[69]="BR";
	$collabels[70]="BS";
	$collabels[71]="BT";
	$collabels[72]="BU";
	$collabels[73]="BV";
	$collabels[74]="BW";
	$collabels[75]="BX";
	$collabels[76]="BY";
	$collabels[77]="BZ";
	$collabels[78]="CA";
	$collabels[79]="CB";
	$collabels[80]="CC";
	$collabels[81]="CD";
	$collabels[82]="CE";
	$collabels[83]="CF";
	$collabels[84]="CG";
	$collabels[85]="CH";
	$collabels[86]="CI";
	$collabels[87]="CJ";
	$collabels[88]="CK";
	$collabels[89]="CL";
	$collabels[90]="CM";
	$collabels[91]="CN";
	$collabels[92]="CO";
	$collabels[93]="CP";
	$collabels[94]="CQ";
	$collabels[95]="CR";
	$collabels[96]="CS";
	$collabels[97]="CT";
	$collabels[98]="CU";
	$collabels[99]="CV";
	$collabels[100]="CW";
	$collabels[101]="CX";
	$collabels[102]="CY";
	$collabels[103]="CZ";
	$collabels[104]="DA";
	$collabels[105]="DB";
	$collabels[106]="DC";
	$collabels[107]="DD";
	$collabels[108]="DE";
	$collabels[109]="DF";
	$collabels[110]="DG";
	$collabels[111]="DH";
	$collabels[112]="DI";
	$collabels[113]="DJ";
	$collabels[114]="DK";
	$collabels[115]="DL";
	$collabels[116]="DM";
	$collabels[117]="DN";
	$collabels[118]="DO";
	$collabels[119]="DP";
	$collabels[120]="DQ";
	$collabels[121]="DR";
	$collabels[122]="DS";
	$collabels[123]="DT";
	$collabels[124]="DU";
	$collabels[125]="DV";
	$collabels[126]="DW";
	$collabels[127]="DX";
	$collabels[128]="DY";
	$collabels[129]="DZ";
	$collabels[130]="EA";
	$collabels[131]="EB";
	$collabels[132]="EC";
	$collabels[133]="ED";
	$collabels[134]="EE";
	$collabels[135]="EF";
	$collabels[136]="EG";
	$collabels[137]="EH";
	$collabels[138]="EI";
	$collabels[139]="EJ";
	$collabels[140]="EK";
	$collabels[141]="EL";
	$collabels[142]="EM";
	$collabels[143]="EN";
	$collabels[144]="EO";
	$collabels[145]="EP";
	$collabels[146]="EQ";
	$collabels[147]="ER";
	$collabels[148]="ES";
	$collabels[149]="ET";
	$collabels[150]="EU";
	$collabels[151]="EV";
	$collabels[152]="EW";
	$collabels[153]="EX";
	$collabels[154]="EY";
	$collabels[155]="EZ";
	$collabels[156]="FA";
	$collabels[157]="FB";
	$collabels[158]="FC";
	$collabels[159]="FD";
	$collabels[160]="FE";
	$collabels[161]="FF";
	$collabels[162]="FG";
	$collabels[163]="FH";
	$collabels[164]="FI";
	$collabels[165]="FJ";
	$collabels[166]="FK";
	$collabels[167]="FL";
	$collabels[168]="FM";
	$collabels[169]="FN";
	$collabels[170]="FO";
	$collabels[171]="FP";
	$collabels[172]="FQ";
	$collabels[173]="FR";
	$collabels[174]="FS";
	$collabels[175]="FT";
	$collabels[176]="FU";
	$collabels[177]="FV";
	$collabels[178]="FW";
	$collabels[179]="FX";
	$collabels[180]="FY";
	$collabels[181]="FZ";
	$collabels[182]="GA";
	$collabels[183]="GB";
	$collabels[184]="GC";
	$collabels[185]="GD";
	$collabels[186]="GE";
	$collabels[187]="GF";
	$collabels[188]="GG";
	$collabels[189]="GH";
	$collabels[190]="GI";
	$collabels[191]="GJ";
	$collabels[192]="GK";
	$collabels[193]="GL";
	$collabels[194]="GM";
	$collabels[195]="GN";
	$collabels[196]="GO";
	$collabels[197]="GP";
	$collabels[198]="GQ";
	$collabels[199]="GR";
	$collabels[200]="GS";
	$collabels[201]="GT";
	$collabels[202]="GU";
	$collabels[203]="GV";
	$collabels[204]="GW";
	$collabels[205]="GX";
	$collabels[206]="GY";
	$collabels[207]="GZ";
	$collabels[208]="HA";
	$collabels[209]="HB";
	$collabels[210]="HC";
	$collabels[211]="HD";
	$collabels[212]="HE";
	$collabels[213]="HF";
	$collabels[214]="HG";
	$collabels[215]="HH";
	$collabels[216]="HI";
	$collabels[217]="HJ";
	$collabels[218]="HK";
	$collabels[219]="HL";
	$collabels[220]="HM";
	$collabels[221]="HN";
	$collabels[222]="HO";
	$collabels[223]="HP";
	$collabels[224]="HQ";
	$collabels[225]="HR";
	$collabels[226]="HS";
	$collabels[227]="HT";
	$collabels[228]="HU";
	$collabels[229]="HV";
	$collabels[230]="HW";
	$collabels[231]="HX";
	$collabels[232]="HY";
	$collabels[233]="HZ";
	$collabels[234]="IA";
	$collabels[235]="IB";
	$collabels[236]="IC";
	$collabels[237]="ID";
	$collabels[238]="IE";
	$collabels[239]="IF";
	$collabels[240]="IG";
	$collabels[241]="IH";
	$collabels[242]="II";
	$collabels[243]="IJ";
	$collabels[244]="IK";
	$collabels[245]="IL";
	$collabels[246]="IM";
	$collabels[247]="IN";
	$collabels[248]="IO";
	$collabels[249]="IP";
	$collabels[250]="IQ";
	$collabels[251]="IR";
	$collabels[252]="IS";
	$collabels[253]="IT";
	$collabels[254]="IU";
	$collabels[255]="IV";
	$collabels[256]="IW";
	$collabels[257]="IX";
	$collabels[258]="IY";
	$collabels[259]="IZ";
	$collabels[260]="JA";
	$collabels[261]="JB";
	$collabels[262]="JC";
	$collabels[263]="JD";
	$collabels[264]="JE";
	$collabels[265]="JF";
	$collabels[266]="JG";
	$collabels[267]="JH";
	$collabels[268]="JI";
	$collabels[269]="JJ";
	$collabels[270]="JK";
	$collabels[271]="JL";
	$collabels[272]="JM";
	$collabels[273]="JN";
	$collabels[274]="JO";
	$collabels[275]="JP";
	$collabels[276]="JQ";
	$collabels[277]="JR";
	$collabels[278]="JS";
	$collabels[279]="JT";
	$collabels[280]="JU";
	$collabels[281]="JV";
	$collabels[282]="JW";
	$collabels[283]="JX";
	$collabels[284]="JY";
	$collabels[285]="JZ";
	$collabels[286]="KA";
	$collabels[287]="KB";
	$collabels[288]="KC";
	$collabels[289]="KD";
	$collabels[290]="KE";
	$collabels[291]="KF";
	$collabels[292]="KG";
	$collabels[293]="KH";
	$collabels[294]="KI";
	$collabels[295]="KJ";
	$collabels[296]="KK";
	$collabels[297]="KL";
	$collabels[298]="KM";
	$collabels[299]="KN";
	$collabels[300]="KO";
	$collabels[301]="KP";
	$collabels[302]="KQ";
	$collabels[303]="KR";
	$collabels[304]="KS";
	$collabels[305]="KT";
	$collabels[306]="KU";
	$collabels[307]="KV";
	$collabels[308]="KW";
	$collabels[309]="KX";
	$collabels[310]="KY";
	$collabels[311]="KZ";
	$collabels[312]="LA";
	$collabels[313]="LB";
	$collabels[314]="LC";
	$collabels[315]="LD";
	$collabels[316]="LE";
	$collabels[317]="LF";
	$collabels[318]="LG";
	$collabels[319]="LH";
	$collabels[320]="LI";
	$collabels[321]="LJ";
	$collabels[322]="LK";
	$collabels[323]="LL";
	$collabels[324]="LM";
	$collabels[325]="LN";
	$collabels[326]="LO";
	$collabels[327]="LP";
	$collabels[328]="LQ";
	$collabels[329]="LR";
	$collabels[330]="LS";
	$collabels[331]="LT";
	$collabels[332]="LU";
	$collabels[333]="LV";
	$collabels[334]="LW";
	$collabels[335]="LX";
	$collabels[336]="LY";
	$collabels[337]="LZ";
	$collabels[338]="MA";
	$collabels[339]="MB";
	$collabels[340]="MC";
	$collabels[341]="MD";
	$collabels[342]="ME";
	$collabels[343]="MF";
	$collabels[344]="MG";
	$collabels[345]="MH";
	$collabels[346]="MI";
	$collabels[347]="MJ";
	$collabels[348]="MK";
	$collabels[349]="ML";
	$collabels[350]="MM";
	$collabels[351]="MN";
	$collabels[352]="MO";
	$collabels[353]="MP";
	$collabels[354]="MQ";
	$collabels[355]="MR";
	$collabels[356]="MS";
	$collabels[357]="MT";
	$collabels[358]="MU";
	$collabels[359]="MV";
	$collabels[360]="MW";
	$collabels[361]="MX";
	$collabels[362]="MY";
	$collabels[363]="MZ";
	$collabels[364]="NA";
	$collabels[365]="NB";
	$collabels[366]="NC";
	$collabels[367]="ND";
	$collabels[368]="NE";
	$collabels[369]="NF";
	$collabels[370]="NG";
	$collabels[371]="NH";
	$collabels[372]="NI";
	$collabels[373]="NJ";
	$collabels[374]="NK";
	$collabels[375]="NL";
	$collabels[376]="NM";
	$collabels[377]="NN";
	$collabels[378]="NO";
	$collabels[379]="NP";
	$collabels[380]="NQ";
	$collabels[381]="NR";
	$collabels[382]="NS";
	$collabels[383]="NT";
	$collabels[384]="NU";
	$collabels[385]="NV";
	$collabels[386]="NW";
	$collabels[387]="NX";
	$collabels[388]="NY";
	$collabels[389]="NZ";
	$collabels[390]="OA";
	$collabels[391]="OB";
	$collabels[392]="OC";
	$collabels[393]="OD";
	$collabels[394]="OE";
	$collabels[395]="OF";
	$collabels[396]="OG";
	$collabels[397]="OH";
	$collabels[398]="OI";
	$collabels[399]="OJ";
	$collabels[400]="OK";
	$collabels[401]="OL";
	$collabels[402]="OM";
	$collabels[403]="ON";
	$collabels[404]="OO";
	$collabels[405]="OP";
	$collabels[406]="OQ";
	$collabels[407]="OR";
	$collabels[408]="OS";
	$collabels[409]="OT";
	$collabels[410]="OU";
	$collabels[411]="OV";
	$collabels[412]="OW";
	$collabels[413]="OX";
	$collabels[414]="OY";
	$collabels[415]="OZ";
	$collabels[416]="PA";
	$collabels[417]="PB";
	$collabels[418]="PC";
	$collabels[419]="PD";
	$collabels[420]="PE";
	$collabels[421]="PF";
	$collabels[422]="PG";
	$collabels[423]="PH";
	$collabels[424]="PI";
	$collabels[425]="PJ";
	$collabels[426]="PK";
	$collabels[427]="PL";
	$collabels[428]="PM";
	$collabels[429]="PN";
	$collabels[430]="PO";
	$collabels[431]="PP";
	$collabels[432]="PQ";
	$collabels[433]="PR";
	$collabels[434]="PS";
	$collabels[435]="PT";
	$collabels[436]="PU";
	$collabels[437]="PV";
	$collabels[438]="PW";
	$collabels[439]="PX";
	$collabels[440]="PY";
	$collabels[441]="PZ";
	$collabels[442]="QA";
	$collabels[443]="QB";
	$collabels[444]="QC";
	$collabels[445]="QD";
	$collabels[446]="QE";
	$collabels[447]="QF";
	$collabels[448]="QG";
	$collabels[449]="QH";
	$collabels[450]="QI";
	$collabels[451]="QJ";
	$collabels[452]="QK";
	$collabels[453]="QL";
	$collabels[454]="QM";
	$collabels[455]="QN";
	$collabels[456]="QO";
	$collabels[457]="QP";
	$collabels[458]="QQ";
	$collabels[459]="QR";
	$collabels[460]="QS";
	$collabels[461]="QT";
	$collabels[462]="QU";
	$collabels[463]="QV";
	$collabels[464]="QW";
	$collabels[465]="QX";
	$collabels[466]="QY";
	$collabels[467]="QZ";
	$collabels[468]="RA";
	$collabels[469]="RB";
	$collabels[470]="RC";
	$collabels[471]="RD";
	$collabels[472]="RE";
	$collabels[473]="RF";
	$collabels[474]="RG";
	$collabels[475]="RH";
	$collabels[476]="RI";
	$collabels[477]="RJ";
	$collabels[478]="RK";
	$collabels[479]="RL";
	$collabels[480]="RM";
	$collabels[481]="RN";
	$collabels[482]="RO";
	$collabels[483]="RP";
	$collabels[484]="RQ";
	$collabels[485]="RR";
	$collabels[486]="RS";
	$collabels[487]="RT";
	$collabels[488]="RU";
	$collabels[489]="RV";
	$collabels[490]="RW";
	$collabels[491]="RX";
	$collabels[492]="RY";
	$collabels[493]="RZ";
	$collabels[494]="SA";
	$collabels[495]="SB";
	$collabels[496]="SC";
	$collabels[497]="SD";
	$collabels[498]="SE";
	$collabels[499]="SF";
	$collabels[500]="SG";
	$collabels[501]="SH";
	$collabels[502]="SI";
	$collabels[503]="SJ";
	$collabels[504]="SK";
	$collabels[505]="SL";
	$collabels[506]="SM";
	$collabels[507]="SN";
	$collabels[508]="SO";
	$collabels[509]="SP";
	$collabels[510]="SQ";
	$collabels[511]="SR";
	$collabels[512]="SS";
	$collabels[513]="ST";
	$collabels[514]="SU";
	$collabels[515]="SV";
	$collabels[516]="SW";
	$collabels[517]="SX";
	$collabels[518]="SY";
	$collabels[519]="SZ";
	$collabels[520]="TA";
	$collabels[521]="TB";
	$collabels[522]="TC";
	$collabels[523]="TD";
	$collabels[524]="TE";
	$collabels[525]="TF";
	$collabels[526]="TG";
	$collabels[527]="TH";
	$collabels[528]="TI";
	$collabels[529]="TJ";
	$collabels[530]="TK";
	$collabels[531]="TL";
	$collabels[532]="TM";
	$collabels[533]="TN";
	$collabels[534]="TO";
	$collabels[535]="TP";
	$collabels[536]="TQ";
	$collabels[537]="TR";
	$collabels[538]="TS";
	$collabels[539]="TT";
	$collabels[540]="TU";
	$collabels[541]="TV";
	$collabels[542]="TW";
	$collabels[543]="TX";
	$collabels[544]="TY";
	$collabels[545]="TZ";
	$collabels[546]="UA";
	$collabels[547]="UB";
	$collabels[548]="UC";
	$collabels[549]="UD";
	$collabels[550]="UE";
	$collabels[551]="UF";
	$collabels[552]="UG";
	$collabels[553]="UH";
	$collabels[554]="UI";
	$collabels[555]="UJ";
	$collabels[556]="UK";
	$collabels[557]="UL";
	$collabels[558]="UM";
	$collabels[559]="UN";
	$collabels[560]="UO";
	$collabels[561]="UP";
	$collabels[562]="UQ";
	$collabels[563]="UR";
	$collabels[564]="US";
	$collabels[565]="UT";
	$collabels[566]="UU";
	$collabels[567]="UV";
	$collabels[568]="UW";
	$collabels[569]="UX";
	$collabels[570]="UY";
	$collabels[571]="UZ";
	$collabels[572]="VA";
	$collabels[573]="VB";
	$collabels[574]="VC";
	$collabels[575]="VD";
	$collabels[576]="VE";
	$collabels[577]="VF";
	$collabels[578]="VG";
	$collabels[579]="VH";
	$collabels[580]="VI";
	$collabels[581]="VJ";
	$collabels[582]="VK";
	$collabels[583]="VL";
	$collabels[584]="VM";
	$collabels[585]="VN";
	$collabels[586]="VO";
	$collabels[587]="VP";
	$collabels[588]="VQ";
	$collabels[589]="VR";
	$collabels[590]="VS";
	$collabels[591]="VT";
	$collabels[592]="VU";
	$collabels[593]="VV";
	$collabels[594]="VW";
	$collabels[595]="VX";
	$collabels[596]="VY";
	$collabels[597]="VZ";
	$collabels[598]="WA";
	$collabels[599]="WB";
	$collabels[600]="WC";
	$collabels[601]="WD";
	$collabels[602]="WE";
	$collabels[603]="WF";
	$collabels[604]="WG";
	$collabels[605]="WH";
	$collabels[606]="WI";
	$collabels[607]="WJ";
	$collabels[608]="WK";
	$collabels[609]="WL";
	$collabels[610]="WM";
	$collabels[611]="WN";
	$collabels[612]="WO";
	$collabels[613]="WP";
	$collabels[614]="WQ";
	$collabels[615]="WR";
	$collabels[616]="WS";
	$collabels[617]="WT";
	$collabels[618]="WU";
	$collabels[619]="WV";
	$collabels[620]="WW";
	$collabels[621]="WX";
	$collabels[622]="WY";
	$collabels[623]="WZ";
	$collabels[624]="XA";
	$collabels[625]="XB";
	$collabels[626]="XC";
	$collabels[627]="XD";
	$collabels[628]="XE";
	$collabels[629]="XF";
	$collabels[630]="XG";
	$collabels[631]="XH";
	$collabels[632]="XI";
	$collabels[633]="XJ";
	$collabels[634]="XK";
	$collabels[635]="XL";
	$collabels[636]="XM";
	$collabels[637]="XN";
	$collabels[638]="XO";
	$collabels[639]="XP";
	$collabels[640]="XQ";
	$collabels[641]="XR";
	$collabels[642]="XS";
	$collabels[643]="XT";
	$collabels[644]="XU";
	$collabels[645]="XV";
	$collabels[646]="XW";
	$collabels[647]="XX";
	$collabels[648]="XY";
	$collabels[649]="XZ";
	$collabels[650]="YA";
	$collabels[651]="YB";
	$collabels[652]="YC";
	$collabels[653]="YD";
	$collabels[654]="YE";
	$collabels[655]="YF";
	$collabels[656]="YG";
	$collabels[657]="YH";
	$collabels[658]="YI";
	$collabels[659]="YJ";
	$collabels[660]="YK";
	$collabels[661]="YL";
	$collabels[662]="YM";
	$collabels[663]="YN";
	$collabels[664]="YO";
	$collabels[665]="YP";
	$collabels[666]="YQ";
	$collabels[667]="YR";
	$collabels[668]="YS";
	$collabels[669]="YT";
	$collabels[670]="YU";
	$collabels[671]="YV";
	$collabels[672]="YW";
	$collabels[673]="YX";
	$collabels[674]="YY";
	$collabels[675]="YZ";
	$collabels[676]="ZA";
	$collabels[677]="ZB";
	$collabels[678]="ZC";
	$collabels[679]="ZD";
	$collabels[680]="ZE";
	$collabels[681]="ZF";
	$collabels[682]="ZG";
	$collabels[683]="ZH";
	$collabels[684]="ZI";
	$collabels[685]="ZJ";
	$collabels[686]="ZK";
	$collabels[687]="ZL";
	$collabels[688]="ZM";
	$collabels[689]="ZN";
	$collabels[690]="ZO";
	$collabels[691]="ZP";
	$collabels[692]="ZQ";
	$collabels[693]="ZR";
	$collabels[694]="ZS";
	$collabels[695]="ZT";
	$collabels[696]="ZU";
	$collabels[697]="ZV";
	$collabels[698]="ZW";
	$collabels[699]="ZX";
	$collabels[700]="ZY";
	$collabels[701]="ZZ";
	$collabels[702]="AAA";
	$collabels[703]="AAB";
	$collabels[704]="AAC";
	$collabels[705]="AAD";
	$collabels[706]="AAE";
	$collabels[707]="AAF";
	$collabels[708]="AAG";
	$collabels[709]="AAH";
	$collabels[710]="AAI";
	$collabels[711]="AAJ";
	$collabels[712]="AAK";
	$collabels[713]="AAL";
	$collabels[714]="AAM";
	$collabels[715]="AAN";
	$collabels[716]="AAO";
	$collabels[717]="AAP";
	$collabels[718]="AAQ";
	$collabels[719]="AAR";
	$collabels[720]="AAS";
	$collabels[721]="AAT";
	$collabels[722]="AAU";
	$collabels[723]="AAV";
	$collabels[724]="AAW";
	$collabels[725]="AAX";
	$collabels[726]="AAY";
	$collabels[727]="AAZ";
	$collabels[728]="ABA";
	$collabels[729]="ABB";
	$collabels[730]="ABC";
	$collabels[731]="ABD";
	$collabels[732]="ABE";
	$collabels[733]="ABF";
	$collabels[734]="ABG";
	$collabels[735]="ABH";
	$collabels[736]="ABI";
	$collabels[737]="ABJ";
	$collabels[738]="ABK";
	$collabels[739]="ABL";
	$collabels[740]="ABM";
	$collabels[741]="ABN";
	$collabels[742]="ABO";
	$collabels[743]="ABP";
	$collabels[744]="ABQ";
	$collabels[745]="ABR";
	$collabels[746]="ABS";
	$collabels[747]="ABT";
	$collabels[748]="ABU";
	$collabels[749]="ABV";
	$collabels[750]="ABW";
	$collabels[751]="ABX";
	$collabels[752]="ABY";
	$collabels[753]="ABZ";
	$collabels[754]="ACA";
	$collabels[755]="ACB";
	$collabels[756]="ACC";
	$collabels[757]="ACD";
	$collabels[758]="ACE";
	$collabels[759]="ACF";
	$collabels[760]="ACG";
	$collabels[761]="ACH";
	$collabels[762]="ACI";
	$collabels[763]="ACJ";
	$collabels[764]="ACK";
	$collabels[765]="ACL";
	$collabels[766]="ACM";
	$collabels[767]="ACN";
	$collabels[768]="ACO";
	$collabels[769]="ACP";
	$collabels[770]="ACQ";
	$collabels[771]="ACR";
	$collabels[772]="ACS";
	$collabels[773]="ACT";
	$collabels[774]="ACU";
	$collabels[775]="ACV";
	$collabels[776]="ACW";
	$collabels[777]="ACX";
	$collabels[778]="ACY";
	$collabels[779]="ACZ";
	$collabels[780]="ADA";
	$collabels[781]="ADB";
	$collabels[782]="ADC";
	$collabels[783]="ADD";
	$collabels[784]="ADE";
	$collabels[785]="ADF";
	$collabels[786]="ADG";
	$collabels[787]="ADH";
	$collabels[788]="ADI";
	$collabels[789]="ADJ";
	$collabels[790]="ADK";
	$collabels[791]="ADL";
	$collabels[792]="ADM";
	$collabels[793]="ADN";
	$collabels[794]="ADO";
	$collabels[795]="ADP";
	$collabels[796]="ADQ";
	$collabels[797]="ADR";
	$collabels[798]="ADS";
	$collabels[799]="ADT";
	$collabels[800]="ADU";
	$collabels[801]="ADV";
	$collabels[802]="ADW";
	$collabels[803]="ADX";
	$collabels[804]="ADY";
	$collabels[805]="ADZ";
	$collabels[806]="AEA";
	$collabels[807]="AEB";
	$collabels[808]="AEC";
	$collabels[809]="AED";
	$collabels[810]="AEE";
	$collabels[811]="AEF";
	$collabels[812]="AEG";
	$collabels[813]="AEH";
	$collabels[814]="AEI";
	$collabels[815]="AEJ";
	$collabels[816]="AEK";
	$collabels[817]="AEL";
	$collabels[818]="AEM";
	$collabels[819]="AEN";
	$collabels[820]="AEO";
	$collabels[821]="AEP";
	$collabels[822]="AEQ";
	$collabels[823]="AER";
	$collabels[824]="AES";
	$collabels[825]="AET";
	$collabels[826]="AEU";
	$collabels[827]="AEV";
	$collabels[828]="AEW";
	$collabels[829]="AEX";
	$collabels[830]="AEY";
	$collabels[831]="AEZ";
	$collabels[832]="AFA";
	$collabels[833]="AFB";
	$collabels[834]="AFC";
	$collabels[835]="AFD";
	$collabels[836]="AFE";
	$collabels[837]="AFF";
	$collabels[838]="AFG";
	$collabels[839]="AFH";
	$collabels[840]="AFI";
	$collabels[841]="AFJ";
	$collabels[842]="AFK";
	$collabels[843]="AFL";
	$collabels[844]="AFM";
	$collabels[845]="AFN";
	$collabels[846]="AFO";
	$collabels[847]="AFP";
	$collabels[848]="AFQ";
	$collabels[849]="AFR";
	$collabels[850]="AFS";
	$collabels[851]="AFT";
	$collabels[852]="AFU";
	$collabels[853]="AFV";
	$collabels[854]="AFW";
	$collabels[855]="AFX";
	$collabels[856]="AFY";
	$collabels[857]="AFZ";
	$collabels[858]="AGA";
	$collabels[859]="AGB";
	$collabels[860]="AGC";
	$collabels[861]="AGD";
	$collabels[862]="AGE";
	$collabels[863]="AGF";
	$collabels[864]="AGG";
	$collabels[865]="AGH";
	$collabels[866]="AGI";
	$collabels[867]="AGJ";
	$collabels[868]="AGK";
	$collabels[869]="AGL";
	$collabels[870]="AGM";
	$collabels[871]="AGN";
	$collabels[872]="AGO";
	$collabels[873]="AGP";
	$collabels[874]="AGQ";
	$collabels[875]="AGR";
	$collabels[876]="AGS";
	$collabels[877]="AGT";
	$collabels[878]="AGU";
	$collabels[879]="AGV";
	$collabels[880]="AGW";
	$collabels[881]="AGX";
	$collabels[882]="AGY";
	$collabels[883]="AGZ";
	$collabels[884]="AHA";
	$collabels[885]="AHB";
	$collabels[886]="AHC";
	$collabels[887]="AHD";
	$collabels[888]="AHE";
	$collabels[889]="AHF";
	$collabels[890]="AHG";
	$collabels[891]="AHH";
	$collabels[892]="AHI";
	$collabels[893]="AHJ";
	$collabels[894]="AHK";
	$collabels[895]="AHL";
	$collabels[896]="AHM";
	$collabels[897]="AHN";
	$collabels[898]="AHO";
	$collabels[899]="AHP";
	$collabels[900]="AHQ";
	$collabels[901]="AHR";
	$collabels[902]="AHS";
	$collabels[903]="AHT";
	$collabels[904]="AHU";
	$collabels[905]="AHV";
	$collabels[906]="AHW";
	$collabels[907]="AHX";
	$collabels[908]="AHY";
	$collabels[909]="AHZ";
	$collabels[910]="AIA";
	$collabels[911]="AIB";
	$collabels[912]="AIC";
	$collabels[913]="AID";
	$collabels[914]="AIE";
	$collabels[915]="AIF";
	$collabels[916]="AIG";
	$collabels[917]="AIH";
	$collabels[918]="AII";
	$collabels[919]="AIJ";
	$collabels[920]="AIK";
	$collabels[921]="AIL";
	$collabels[922]="AIM";
	$collabels[923]="AIN";
	$collabels[924]="AIO";
	$collabels[925]="AIP";
	$collabels[926]="AIQ";
	$collabels[927]="AIR";
	$collabels[928]="AIS";
	$collabels[929]="AIT";
	$collabels[930]="AIU";
	$collabels[931]="AIV";
	$collabels[932]="AIW";
	$collabels[933]="AIX";
	$collabels[934]="AIY";
	$collabels[935]="AIZ";
	$collabels[936]="AJA";
	$collabels[937]="AJB";
	$collabels[938]="AJC";
	$collabels[939]="AJD";
	$collabels[940]="AJE";
	$collabels[941]="AJF";
	$collabels[942]="AJG";
	$collabels[943]="AJH";
	$collabels[944]="AJI";
	$collabels[945]="AJJ";
	$collabels[946]="AJK";
	$collabels[947]="AJL";
	$collabels[948]="AJM";
	$collabels[949]="AJN";
	$collabels[950]="AJO";
	$collabels[951]="AJP";
	$collabels[952]="AJQ";
	$collabels[953]="AJR";
	$collabels[954]="AJS";
	$collabels[955]="AJT";
	$collabels[956]="AJU";
	$collabels[957]="AJV";
	$collabels[958]="AJW";
	$collabels[959]="AJX";
	$collabels[960]="AJY";
	$collabels[961]="AJZ";
	$collabels[962]="AKA";
	$collabels[963]="AKB";
	$collabels[964]="AKC";
	$collabels[965]="AKD";
	$collabels[966]="AKE";
	$collabels[967]="AKF";
	$collabels[968]="AKG";
	$collabels[969]="AKH";
	$collabels[970]="AKI";
	$collabels[971]="AKJ";
	$collabels[972]="AKK";
	$collabels[973]="AKL";
	$collabels[974]="AKM";
	$collabels[975]="AKN";
	$collabels[976]="AKO";
	$collabels[977]="AKP";
	$collabels[978]="AKQ";
	$collabels[979]="AKR";
	$collabels[980]="AKS";
	$collabels[981]="AKT";
	$collabels[982]="AKU";
	$collabels[983]="AKV";
	$collabels[984]="AKW";
	$collabels[985]="AKX";
	$collabels[986]="AKY";
	$collabels[987]="AKZ";
	$collabels[988]="ALA";
	$collabels[989]="ALB";
	$collabels[990]="ALC";
	$collabels[991]="ALD";
	$collabels[992]="ALE";
	$collabels[993]="ALF";
	$collabels[994]="ALG";
	$collabels[995]="ALH";
	$collabels[996]="ALI";
	$collabels[997]="ALJ";
	$collabels[998]="ALK";
	$collabels[999]="ALL";
	$collabels[1000]="ALM";
	$collabels[1001]="ALN";
	$collabels[1002]="ALO";
	$collabels[1003]="ALP";
	$collabels[1004]="ALQ";
	$collabels[1005]="ALR";
	$collabels[1006]="ALS";
	$collabels[1007]="ALT";
	$collabels[1008]="ALU";
	$collabels[1009]="ALV";
	$collabels[1010]="ALW";
	$collabels[1011]="ALX";
	$collabels[1012]="ALY";
	$collabels[1013]="ALZ";
	$collabels[1014]="AMA";
	$collabels[1015]="AMB";
	$collabels[1016]="AMC";
	$collabels[1017]="AMD";
	$collabels[1018]="AME";
	$collabels[1019]="AMF";
	$collabels[1020]="AMG";
	$collabels[1021]="AMH";
	$collabels[1022]="AMI";
	$collabels[1023]="AMJ";
	$collabels[1024]="AMK";
	$collabels[1025]="AML";
	$collabels[1026]="AMM";
	$collabels[1027]="AMN";
	$collabels[1028]="AMO";
	$collabels[1029]="AMP";
	$collabels[1030]="AMQ";
	$collabels[1031]="AMR";
	$collabels[1032]="AMS";
	$collabels[1033]="AMT";
	$collabels[1034]="AMU";
	$collabels[1035]="AMV";
	$collabels[1036]="AMW";
	$collabels[1037]="AMX";
	$collabels[1038]="AMY";
	$collabels[1039]="AMZ";
	$collabels[1040]="ANA";
	$collabels[1041]="ANB";
	$collabels[1042]="ANC";
	$collabels[1043]="AND";
	$collabels[1044]="ANE";
	$collabels[1045]="ANF";
	$collabels[1046]="ANG";
	$collabels[1047]="ANH";
	$collabels[1048]="ANI";
	$collabels[1049]="ANJ";
	$collabels[1050]="ANK";
	$collabels[1051]="ANL";
	$collabels[1052]="ANM";
	$collabels[1053]="ANN";
	$collabels[1054]="ANO";
	$collabels[1055]="ANP";
	$collabels[1056]="ANQ";
	$collabels[1057]="ANR";
	$collabels[1058]="ANS";
	$collabels[1059]="ANT";
	$collabels[1060]="ANU";
	$collabels[1061]="ANV";
	$collabels[1062]="ANW";
	$collabels[1063]="ANX";
	$collabels[1064]="ANY";
	$collabels[1065]="ANZ";
	$collabels[1066]="AOA";
	$collabels[1067]="AOB";
	$collabels[1068]="AOC";
	$collabels[1069]="AOD";
	$collabels[1070]="AOE";
	$collabels[1071]="AOF";
	$collabels[1072]="AOG";
	$collabels[1073]="AOH";
	$collabels[1074]="AOI";
	$collabels[1075]="AOJ";
	$collabels[1076]="AOK";
	$collabels[1077]="AOL";
	$collabels[1078]="AOM";
	$collabels[1079]="AON";
	$collabels[1080]="AOO";
	$collabels[1081]="AOP";
	$collabels[1082]="AOQ";
	$collabels[1083]="AOR";
	$collabels[1084]="AOS";
	$collabels[1085]="AOT";
	$collabels[1086]="AOU";
	$collabels[1087]="AOV";
	$collabels[1088]="AOW";
	$collabels[1089]="AOX";
	$collabels[1090]="AOY";
	$collabels[1091]="AOZ";
	$collabels[1092]="APA";
	$collabels[1093]="APB";
	$collabels[1094]="APC";
	$collabels[1095]="APD";
	$collabels[1096]="APE";
	$collabels[1097]="APF";
	$collabels[1098]="APG";
	$collabels[1099]="APH";
	$collabels[1100]="API";
	$collabels[1101]="APJ";
	$collabels[1102]="APK";
	$collabels[1103]="APL";
	$collabels[1104]="APM";
	$collabels[1105]="APN";
	$collabels[1106]="APO";
	$collabels[1107]="APP";
	$collabels[1108]="APQ";
	$collabels[1109]="APR";
	$collabels[1110]="APS";
	$collabels[1111]="APT";
	$collabels[1112]="APU";
	$collabels[1113]="APV";
	$collabels[1114]="APW";
	$collabels[1115]="APX";
	$collabels[1116]="APY";
	$collabels[1117]="APZ";
	$collabels[1118]="AQA";
	$collabels[1119]="AQB";
	$collabels[1120]="AQC";
	$collabels[1121]="AQD";
	$collabels[1122]="AQE";
	$collabels[1123]="AQF";
	$collabels[1124]="AQG";
	$collabels[1125]="AQH";
	$collabels[1126]="AQI";
	$collabels[1127]="AQJ";
	$collabels[1128]="AQK";
	$collabels[1129]="AQL";
	$collabels[1130]="AQM";
	$collabels[1131]="AQN";
	$collabels[1132]="AQO";
	$collabels[1133]="AQP";
	$collabels[1134]="AQQ";
	$collabels[1135]="AQR";
	$collabels[1136]="AQS";
	$collabels[1137]="AQT";
	$collabels[1138]="AQU";
	$collabels[1139]="AQV";
	$collabels[1140]="AQW";
	$collabels[1141]="AQX";
	$collabels[1142]="AQY";
	$collabels[1143]="AQZ";
	$collabels[1144]="ARA";
	$collabels[1145]="ARB";
	$collabels[1146]="ARC";
	$collabels[1147]="ARD";
	$collabels[1148]="ARE";
	$collabels[1149]="ARF";
	$collabels[1150]="ARG";
	$collabels[1151]="ARH";
	$collabels[1152]="ARI";
	$collabels[1153]="ARJ";
	$collabels[1154]="ARK";
	$collabels[1155]="ARL";
	$collabels[1156]="ARM";
	$collabels[1157]="ARN";
	$collabels[1158]="ARO";
	$collabels[1159]="ARP";
	$collabels[1160]="ARQ";
	$collabels[1161]="ARR";
	$collabels[1162]="ARS";
	$collabels[1163]="ART";
	$collabels[1164]="ARU";
	$collabels[1165]="ARV";
	$collabels[1166]="ARW";
	$collabels[1167]="ARX";
	$collabels[1168]="ARY";
	$collabels[1169]="ARZ";
	$collabels[1170]="ASA";
	$collabels[1171]="ASB";
	$collabels[1172]="ASC";
	$collabels[1173]="ASD";
	$collabels[1174]="ASE";
	$collabels[1175]="ASF";
	$collabels[1176]="ASG";
	$collabels[1177]="ASH";
	$collabels[1178]="ASI";
	$collabels[1179]="ASJ";
	$collabels[1180]="ASK";
	$collabels[1181]="ASL";
	$collabels[1182]="ASM";
	$collabels[1183]="ASN";
	$collabels[1184]="ASO";
	$collabels[1185]="ASP";
	$collabels[1186]="ASQ";
	$collabels[1187]="ASR";
	$collabels[1188]="ASS";
	$collabels[1189]="AST";
	$collabels[1190]="ASU";
	$collabels[1191]="ASV";
	$collabels[1192]="ASW";
	$collabels[1193]="ASX";
	$collabels[1194]="ASY";
	$collabels[1195]="ASZ";
	$collabels[1196]="ATA";
	$collabels[1197]="ATB";
	$collabels[1198]="ATC";
	$collabels[1199]="ATD";
	$collabels[1200]="ATE";
	$collabels[1201]="ATF";
	$collabels[1202]="ATG";
	$collabels[1203]="ATH";
	$collabels[1204]="ATI";
	$collabels[1205]="ATJ";
	$collabels[1206]="ATK";
	$collabels[1207]="ATL";
	$collabels[1208]="ATM";
	$collabels[1209]="ATN";
	$collabels[1210]="ATO";
	$collabels[1211]="ATP";
	$collabels[1212]="ATQ";
	$collabels[1213]="ATR";
	$collabels[1214]="ATS";
	$collabels[1215]="ATT";
	$collabels[1216]="ATU";
	$collabels[1217]="ATV";
	$collabels[1218]="ATW";
	$collabels[1219]="ATX";
	$collabels[1220]="ATY";
	$collabels[1221]="ATZ";
	$collabels[1222]="AUA";
	$collabels[1223]="AUB";
	$collabels[1224]="AUC";
	$collabels[1225]="AUD";
	$collabels[1226]="AUE";
	$collabels[1227]="AUF";
	$collabels[1228]="AUG";
	$collabels[1229]="AUH";
	$collabels[1230]="AUI";
	$collabels[1231]="AUJ";
	$collabels[1232]="AUK";
	$collabels[1233]="AUL";
	$collabels[1234]="AUM";
	$collabels[1235]="AUN";
	$collabels[1236]="AUO";
	$collabels[1237]="AUP";
	$collabels[1238]="AUQ";
	$collabels[1239]="AUR";
	$collabels[1240]="AUS";
	$collabels[1241]="AUT";
	$collabels[1242]="AUU";
	$collabels[1243]="AUV";
	$collabels[1244]="AUW";
	$collabels[1245]="AUX";
	$collabels[1246]="AUY";
	$collabels[1247]="AUZ";
	$collabels[1248]="AVA";
	$collabels[1249]="AVB";
	$collabels[1250]="AVC";
	$collabels[1251]="AVD";
	$collabels[1252]="AVE";
	$collabels[1253]="AVF";
	$collabels[1254]="AVG";
	$collabels[1255]="AVH";
	$collabels[1256]="AVI";
	$collabels[1257]="AVJ";
	$collabels[1258]="AVK";
	$collabels[1259]="AVL";
	$collabels[1260]="AVM";
	$collabels[1261]="AVN";
	$collabels[1262]="AVO";
	$collabels[1263]="AVP";
	$collabels[1264]="AVQ";
	$collabels[1265]="AVR";
	$collabels[1266]="AVS";
	$collabels[1267]="AVT";
	$collabels[1268]="AVU";
	$collabels[1269]="AVV";
	$collabels[1270]="AVW";
	$collabels[1271]="AVX";
	$collabels[1272]="AVY";
	$collabels[1273]="AVZ";
	$collabels[1274]="AWA";
	$collabels[1275]="AWB";
	$collabels[1276]="AWC";
	$collabels[1277]="AWD";
	$collabels[1278]="AWE";
	$collabels[1279]="AWF";
	$collabels[1280]="AWG";
	$collabels[1281]="AWH";
	$collabels[1282]="AWI";
	$collabels[1283]="AWJ";
	$collabels[1284]="AWK";
	$collabels[1285]="AWL";
	$collabels[1286]="AWM";
	$collabels[1287]="AWN";
	$collabels[1288]="AWO";
	$collabels[1289]="AWP";
	$collabels[1290]="AWQ";
	$collabels[1291]="AWR";
	$collabels[1292]="AWS";
	$collabels[1293]="AWT";
	$collabels[1294]="AWU";
	$collabels[1295]="AWV";
	$collabels[1296]="AWW";
	$collabels[1297]="AWX";
	$collabels[1298]="AWY";
	$collabels[1299]="AWZ";
	$collabels[1300]="AXA";
	$collabels[1301]="AXB";
	$collabels[1302]="AXC";
	$collabels[1303]="AXD";
	$collabels[1304]="AXE";
	$collabels[1305]="AXF";
	$collabels[1306]="AXG";
	$collabels[1307]="AXH";
	$collabels[1308]="AXI";
	$collabels[1309]="AXJ";
	$collabels[1310]="AXK";
	$collabels[1311]="AXL";
	$collabels[1312]="AXM";
	$collabels[1313]="AXN";
	$collabels[1314]="AXO";
	$collabels[1315]="AXP";
	$collabels[1316]="AXQ";
	$collabels[1317]="AXR";
	$collabels[1318]="AXS";
	$collabels[1319]="AXT";
	$collabels[1320]="AXU";
	$collabels[1321]="AXV";
	$collabels[1322]="AXW";
	$collabels[1323]="AXX";
	$collabels[1324]="AXY";
	$collabels[1325]="AXZ";
	$collabels[1326]="AYA";
	$collabels[1327]="AYB";
	$collabels[1328]="AYC";
	$collabels[1329]="AYD";
	$collabels[1330]="AYE";
	$collabels[1331]="AYF";
	$collabels[1332]="AYG";
	$collabels[1333]="AYH";
	$collabels[1334]="AYI";
	$collabels[1335]="AYJ";
	$collabels[1336]="AYK";
	$collabels[1337]="AYL";
	$collabels[1338]="AYM";
	$collabels[1339]="AYN";
	$collabels[1340]="AYO";
	$collabels[1341]="AYP";
	$collabels[1342]="AYQ";
	$collabels[1343]="AYR";
	$collabels[1344]="AYS";
	$collabels[1345]="AYT";
	$collabels[1346]="AYU";
	$collabels[1347]="AYV";
	$collabels[1348]="AYW";
	$collabels[1349]="AYX";
	$collabels[1350]="AYY";
	$collabels[1351]="AYZ";
	$collabels[1352]="AZA";
	$collabels[1353]="AZB";
	$collabels[1354]="AZC";
	$collabels[1355]="AZD";
	$collabels[1356]="AZE";
	$collabels[1357]="AZF";
	$collabels[1358]="AZG";
	$collabels[1359]="AZH";
	$collabels[1360]="AZI";
	$collabels[1361]="AZJ";
	$collabels[1362]="AZK";
	$collabels[1363]="AZL";
	$collabels[1364]="AZM";
	$collabels[1365]="AZN";
	$collabels[1366]="AZO";
	$collabels[1367]="AZP";
	$collabels[1368]="AZQ";
	$collabels[1369]="AZR";
	$collabels[1370]="AZS";
	$collabels[1371]="AZT";
	$collabels[1372]="AZU";
	$collabels[1373]="AZV";
	$collabels[1374]="AZW";
	$collabels[1375]="AZX";
	$collabels[1376]="AZY";
	$collabels[1377]="AZZ";


	/** Include PHPExcel */
	require_once 'Classes/PHPExcel.php';

	

	// Create new PHPExcel object
	$objPHPExcel = new PHPExcel();

	$boldstyle = new PHPExcel_Style();
	$yellowstyle = new PHPExcel_Style();
	$bluestyle = new PHPExcel_Style();
	$whitestyle = new PHPExcel_Style();
	$blackstyle = new PHPExcel_Style();



	$boldstyle->applyFromArray(
		array(
			'font'    => array(
				'bold'      => true 
			)
		)
			 );

	$yellowstyle->applyFromArray(
		array(
			'borders' => array(
				'top' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
				'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
				'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
				'left' => array('style' => PHPExcel_Style_Border::BORDER_THIN)
			),
			'fill' => array(
				'type'       => PHPExcel_Style_Fill::FILL_SOLID,
				'startcolor' => array(
					'argb' => 'FFFFFFCC'
				)
			)
		)
			 );

	$bluestyle->applyFromArray(
		array(
			'borders' => array(
				'top' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
				'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
				'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
				'left' => array('style' => PHPExcel_Style_Border::BORDER_THIN)
			),
			'fill' => array(
				'type'       => PHPExcel_Style_Fill::FILL_SOLID,
				'startcolor' => array(
					'argb' => 'FFCCFFFF'
				)
			)
		)
	);

	$whitestyle->applyFromArray(
		array(
			'borders' => array(
				'top' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
				'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
				'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
				'left' => array('style' => PHPExcel_Style_Border::BORDER_THIN)
			),
			'fill' => array(
				'type'       => PHPExcel_Style_Fill::FILL_SOLID,
				'startcolor' => array(
					'argb' => 'FFFFFFFF'
				)
			)
		)
	);

	$blackstyle->applyFromArray(
		array(
			'font'    => array(
				'color'      => array(
					'argb' => 'FFFFFFFF'
				)
			),
			'borders' => array(
				'top' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
				'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
				'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
				'left' => array('style' => PHPExcel_Style_Border::BORDER_THIN)
			),
			'fill' => array(
				'type'       => PHPExcel_Style_Fill::FILL_SOLID,
				'startcolor' => array(
					'argb' => 'FF333333'
				)
			)
		)
	);	




	$randnum=rand(10000,99999);
	
	$downloadfilename="earthchem_download_$randnum.xlsx";
	
    $mydate=date("l, M. j, Y g:i:s a");
	
	$objPHPExcel->getActiveSheet()->setTitle('Data');

	$objPHPExcel->getActiveSheet()->setCellValue("A1", "EarthChem Download: $mydate");
	$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(20);
	$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setItalic(true);



	if ($rowtype == "method") {
		$sql_getsample .= $advstring; 
	} else { // rowtype = 'sample' 
		$sql_getsample .= $samplechemstring;  
	} // if rowtype 
	// Retrieve the samples in the result set, to display in the text file
	
	//echo nl2br($sql_getsample);exit();
	//exit();
	
	//echo nl2br($refstring);exit();

	//$sql_getsample.=" limit 2";

	$getsample=$db->get_results($sql_getsample); 

	//echo "made it past search";exit();
	
	include("searchlist.php");
	
	$objPHPExcel->getActiveSheet()->setCellValue("A3", "Search Criteria:");
	
	$myrow=4;
	
	foreach($srchlist as $key=>$value){
		$objPHPExcel->getActiveSheet()->setCellValue("A".$myrow, "$key");
		$objPHPExcel->getActiveSheet()->setCellValue("B".$myrow, "$value");
		$myrow++;
	}
	
	$myrow++;

	$title="EarthChem_Search_Query_Download";

	// Set document properties
	$objPHPExcel->getProperties()->setCreator("EarthChem")
								 ->setLastModifiedBy("EarthChem")
								 ->setTitle("$title")
								 ->setSubject("$title")
								 ->setDescription("$title")
								 ->setKeywords("EarthChem $title")
								 ->setCategory("$title");
	
	$objPHPExcel->getDefaultStyle()->getFont()->setName('Arial')
											  ->setSize(10);

	// Create a first sheet, representing sales data
	$objPHPExcel->setActiveSheetIndex(0);




	
	if ($rowtype == "method" || $rowtype == "sample") {		
		include "log_download.php"; // Log the download, so that a scheduled php procedure can log the citation for each sample later on. We do this only for text and xls output and rowtype - method

	}
	if ($rowtype == "method") {
	
		$columnnames=array("SAMPLE ID","IGSN","SOURCE","DOI","TITLE","JOURNAL","AUTHOR","CRUISEID","LATITUDE","LONGITUDE","LOC PREC","MIN AGE","AGE","MAX AGE","METHOD","MATERIAL","TYPE","COMPOSITION","ROCK NAME","MINERAL");

		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(14);

		$colnum=0;
		foreach($columnnames as $columnname){
			$thisheader=$columnname;
			$thiswidth=strlen($thisheader)-1;

			if($thiswidth<14){
				$thiswidth=14;
			}
			$objPHPExcel->getActiveSheet()->getColumnDimension($collabels[$colnum])->setWidth($thiswidth);

			$colnum++;
		}

		
		$colnum=0;
		foreach($columnnames as $columnname){
			$thisheader=$columnname;
			$objPHPExcel->getActiveSheet()->setCellValue($collabels[$colnum].$myrow, $thisheader);
			$objPHPExcel->getActiveSheet()->getStyle($collabels[$colnum].$myrow)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
			$objPHPExcel->getActiveSheet()->getStyle($collabels[$colnum].$myrow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$objPHPExcel->getActiveSheet()->getStyle($collabels[$colnum].$myrow)->getFill()->getStartColor()->setARGB('FF333333');
			$colnum++;
		}

                //*******************************************************************************************************
                // start generating variable_unit combined column headers,rowtype=method (refactored by PJ on Mar 8 2024)
                //*******************************************************************************************************
                $colnum=20;
                $vu_column_list=array();
                $only_null_arr=array("*");
                $space_and_null_arr=array("","*");
                foreach($showlist_array as $field_name) {
                        foreach($get_variableunits as $g) {
                                foreach($g as $key=>$value){
                                        if(strtoupper($key)==$field_name){
                                                $vu_str=$value;
                                                break;
                                        }
                                }
                        }

                        $vu_arr = explode (",", $vu_str);
                        if(count(array_diff($vu_arr,$only_null_arr))==0 or count(array_diff($vu_arr,$space_and_null_arr))==0){
                                $showfield=$field_name;
                                $vu_column_list[]=$field_name;
                                $objPHPExcel->getActiveSheet()->setCellValue($collabels[$colnum].$myrow, $showfield);
                                $objPHPExcel->getActiveSheet()->getStyle($collabels[$colnum].$myrow)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
                                $objPHPExcel->getActiveSheet()->getStyle($collabels[$colnum].$myrow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                                $objPHPExcel->getActiveSheet()->getStyle($collabels[$colnum].$myrow)->getFill()->getStartColor()->setARGB('FF333333');
                                $colnum++;
                        }
                        else{
                                $new_vu_arr = array_diff($vu_arr,$only_null_arr);
                                for($i=0;$i<count($new_vu_arr);$i++){
                                        $showfield=$field_name."_".$new_vu_arr[$i];
                                        $vu_column_list[]=$field_name."_".$new_vu_arr[$i];
                                        $objPHPExcel->getActiveSheet()->setCellValue($collabels[$colnum].$myrow, $showfield);
                                        $objPHPExcel->getActiveSheet()->getStyle($collabels[$colnum].$myrow)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
                                        $objPHPExcel->getActiveSheet()->getStyle($collabels[$colnum].$myrow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                                        $objPHPExcel->getActiveSheet()->getStyle($collabels[$colnum].$myrow)->getFill()->getStartColor()->setARGB('FF333333');
                                        $colnum++;
                                }
                        }
                } // foreach showlist_array
                //***********************************************************
                // end gengeration variable_unit combined column headers
                //***********************************************************


		$myrow++;
		foreach($getsample as $g) {
			
			if($g->pubyear!=""){
				$xlsyear=", ".$g->pubyear;
			}else{
				$xlsyear="";
			}
			

			$objPHPExcel->getActiveSheet()->setCellValue($collabels[0].$myrow, "$g->sample_id");
			$objPHPExcel->getActiveSheet()->setCellValue($collabels[1].$myrow, "$g->igsn");
			$objPHPExcel->getActiveSheet()->setCellValue($collabels[2].$myrow, strtoupper($g->source));
			$objPHPExcel->getActiveSheet()->setCellValue($collabels[3].$myrow, $g->doi);
			$objPHPExcel->getActiveSheet()->setCellValue($collabels[4].$myrow, $g->title);
			$objPHPExcel->getActiveSheet()->setCellValue($collabels[5].$myrow, $g->journal);
			$objPHPExcel->getActiveSheet()->setCellValue($collabels[6].$myrow, $g->authorlist);
			$objPHPExcel->getActiveSheet()->setCellValue($collabels[7].$myrow, $g->expeditionid);
			$objPHPExcel->getActiveSheet()->setCellValue($collabels[8].$myrow, $g->latitude);
			$objPHPExcel->getActiveSheet()->setCellValue($collabels[9].$myrow, $g->longitude);
			$objPHPExcel->getActiveSheet()->setCellValue($collabels[10].$myrow, $g->locprecision);
			$objPHPExcel->getActiveSheet()->setCellValue($collabels[11].$myrow, $g->age_min);
			$objPHPExcel->getActiveSheet()->setCellValue($collabels[12].$myrow, $g->age);
			$objPHPExcel->getActiveSheet()->setCellValue($collabels[13].$myrow, $g->age_max);
			$objPHPExcel->getActiveSheet()->setCellValue($collabels[14].$myrow, $g->method);
			$objPHPExcel->getActiveSheet()->setCellValue($collabels[15].$myrow, strtoupper($g->class1));
			$objPHPExcel->getActiveSheet()->setCellValue($collabels[16].$myrow, strtoupper($g->class2));
			$objPHPExcel->getActiveSheet()->setCellValue($collabels[17].$myrow, strtoupper($g->class3));
			$objPHPExcel->getActiveSheet()->setCellValue($collabels[18].$myrow, strtoupper($g->class4));
			$objPHPExcel->getActiveSheet()->setCellValue($collabels[19].$myrow, strtoupper($g->mineralname));

			//$objPHPExcel->getActiveSheet()->setSharedStyle($whitestyle, $collabels[0].$myrow.":".$collabels[19].$myrow);

                        //************************************************************************************************************************
                        // start filling the value for the specific variable_unit combined column, rowtype=method refactored by PJ on Mar 8 2024
                        //************************************************************************************************************************
                        $mycol=20;
                        $countcols=count($vu_column_list);
                        for($j=0;$j<$countcols;$j++){
                                $ismatch = false;
                                foreach ($showlist_array as $currentfield) {
                                        $currentfield=strtolower($currentfield);
                                        $currentunit = $currentfield."_unit";
                                        if(strtolower($vu_column_list[$j]) == $currentfield."_".strtolower($g->$currentunit)){
                                                $thisval=$g->$currentfield;

                                                if ($thisval!="") {
                                                        if($thisval<0){
                                                                $thisval=$thisval*-1;
                                                                $thisval="< ".$thisval;
                                                        }
                                                }
                                                $objPHPExcel->getActiveSheet()->setCellValue($collabels[$mycol].$myrow, $thisval);
                                                $mycol++;
                                                $ismatch=true;
                                                break;
                                        }

                                } // foreach showlist_array
                                if(!$ismatch){
                                        $objPHPExcel->getActiveSheet()->setCellValue($collabels[$mycol].$myrow, "");
                                        $mycol++;
                                }
                        } // for vu_column_list
                        //***********************************************************************
                        // end filling the value for the specific variable_unit combined column
                        //***********************************************************************	

			$maxcol=$mycol;

			$myrow++;
		} // foreach $getsample

		//$objPHPExcel->getActiveSheet()->setSharedStyle($whitestyle, "A5:".$collabels[$maxcol].$myrow);



		// now do the references as methods
		$objPHPExcel->createSheet();
	
		$objPHPExcel->setActiveSheetIndex(1);
		$objPHPExcel->getActiveSheet()->setTitle('References');
		$objPHPExcel->getActiveSheet()->setCellValue('A1', 'References');
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(20);
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setItalic(true);

		$columnnames=array("AUTHOR","YEAR","TITLE","JOURNAL");

		$myrow=3;

		$colnum=0;
		foreach($columnnames as $columnname){
			$thisheader=$columnname;
			$objPHPExcel->getActiveSheet()->setCellValue($collabels[$colnum].$myrow, $thisheader);
			$objPHPExcel->getActiveSheet()->getStyle($collabels[$colnum].$myrow)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
			$objPHPExcel->getActiveSheet()->getStyle($collabels[$colnum].$myrow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$objPHPExcel->getActiveSheet()->getStyle($collabels[$colnum].$myrow)->getFill()->getStartColor()->setARGB('FF333333');
			$colnum++;
		}




		$myrow=4;
		
		$refs=$db->get_results("$refstring");
		
		foreach($refs as $ref){

			$objPHPExcel->getActiveSheet()->setCellValue($collabels[0].$myrow, strtoupper($ref->author));
			$objPHPExcel->getActiveSheet()->setCellValue($collabels[1].$myrow, $ref->pubyear);
			$objPHPExcel->getActiveSheet()->setCellValue($collabels[2].$myrow, $ref->title);
			$objPHPExcel->getActiveSheet()->setCellValue($collabels[3].$myrow, $ref->journal);
			$myrow++;
		}
		
		
		
		
		
		$objPHPExcel->setActiveSheetIndex(0);

		require_once 'Classes/PHPExcel/IOFactory.php';

		// Redirect output to a client’s web browser (Excel2007)
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="'.$downloadfilename.'"');
		header('Cache-Control: max-age=0');

		// Save Excel 2007 file
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
	
		exit();


	} else { // if rowtype = method, else - Below section is sample level text, rowtype = 'sample' 
		
	
		$columnnames=array("SAMPLE ID","IGSN","SOURCE","DOI","TITLE","JOURNAL","AUTHOR","CRUISEID","LATITUDE","LONGITUDE","LOC PREC","MIN AGE","AGE","MAX AGE","METHOD","MATERIAL","TYPE","COMPOSITION","ROCK NAME","MINERAL");

		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(14);

		$colnum=0;
		foreach($columnnames as $columnname){
			$thisheader=$columnname;
			$thiswidth=strlen($thisheader)-1;

			if($thiswidth<14){
				$thiswidth=14;
			}
			$objPHPExcel->getActiveSheet()->getColumnDimension($collabels[$colnum])->setWidth($thiswidth);

			$colnum++;
		}

		
		$colnum=0;
		foreach($columnnames as $columnname){
			$thisheader=$columnname;
			$objPHPExcel->getActiveSheet()->setCellValue($collabels[$colnum].$myrow, $thisheader);
			$objPHPExcel->getActiveSheet()->getStyle($collabels[$colnum].$myrow)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
			$objPHPExcel->getActiveSheet()->getStyle($collabels[$colnum].$myrow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$objPHPExcel->getActiveSheet()->getStyle($collabels[$colnum].$myrow)->getFill()->getStartColor()->setARGB('FF333333');
			$colnum++;
		}



                //*********************************************************************************************************
                // start generating variable_unit combined column headers,rowtype=sample (refactored by PJ on Mar 7 2024)
                //*********************************************************************************************************
                $colnum=20;
                $vu_column_list=array();
                $only_null_arr=array("*");
                $space_and_null_arr=array("","*");
                foreach($showlist_array as $field_name) {
			foreach($get_variableunits as $g) {
   				foreach($g as $key=>$value){
   			        	if(strtoupper($key)==$field_name){
		                 		$vu_str=$value;
   				                break;
   					}
   				}
   			}

                        $vu_arr = explode (",", $vu_str);
                        if(count(array_diff($vu_arr,$only_null_arr))==0 or count(array_diff($vu_arr,$space_and_null_arr))==0){
                                $showfield=$field_name;
                                $vu_column_list[]=$field_name;
                                $objPHPExcel->getActiveSheet()->setCellValue($collabels[$colnum].$myrow, $showfield);
                                $objPHPExcel->getActiveSheet()->getStyle($collabels[$colnum].$myrow)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
                                $objPHPExcel->getActiveSheet()->getStyle($collabels[$colnum].$myrow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                                $objPHPExcel->getActiveSheet()->getStyle($collabels[$colnum].$myrow)->getFill()->getStartColor()->setARGB('FF333333');
                                $colnum++;
                        	if ($showmethods) {
                                	$objPHPExcel->getActiveSheet()->setCellValue($collabels[$colnum].$myrow, "$field_name Method");
                                	$objPHPExcel->getActiveSheet()->getStyle($collabels[$colnum].$myrow)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
                                	$objPHPExcel->getActiveSheet()->getStyle($collabels[$colnum].$myrow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                                	$objPHPExcel->getActiveSheet()->getStyle($collabels[$colnum].$myrow)->getFill()->getStartColor()->setARGB('FF333333');
                                	$colnum++;
                        	}
                        }
                        else{
                                $new_vu_arr = array_diff($vu_arr,$only_null_arr);
                                for($i=0;$i<count($new_vu_arr);$i++){
                                        $showfield=$field_name."_".$new_vu_arr[$i];
                                        $vu_column_list[]=$field_name."_".$new_vu_arr[$i];
                                        $objPHPExcel->getActiveSheet()->setCellValue($collabels[$colnum].$myrow, $showfield);
                                        $objPHPExcel->getActiveSheet()->getStyle($collabels[$colnum].$myrow)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
                                        $objPHPExcel->getActiveSheet()->getStyle($collabels[$colnum].$myrow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                                        $objPHPExcel->getActiveSheet()->getStyle($collabels[$colnum].$myrow)->getFill()->getStartColor()->setARGB('FF333333');
                                        $colnum++;
                        		if ($showmethods) {
						$objPHPExcel->getActiveSheet()->setCellValue($collabels[$colnum].$myrow, "$field_name Method");
                                		$objPHPExcel->getActiveSheet()->getStyle($collabels[$colnum].$myrow)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
                               			$objPHPExcel->getActiveSheet()->getStyle($collabels[$colnum].$myrow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                                		$objPHPExcel->getActiveSheet()->getStyle($collabels[$colnum].$myrow)->getFill()->getStartColor()->setARGB('FF333333');
                                		$colnum++;
                        		}
                                }
                        }
                } // foreach showlist_array
                //***********************************************************
                // end gengeration variable_unit combined column headers
                //***********************************************************
		$myrow++;





		foreach($getsample as $g) {
			$objPHPExcel->getActiveSheet()->setCellValue($collabels[0].$myrow, "$g->sample_id");
			$objPHPExcel->getActiveSheet()->setCellValue($collabels[1].$myrow, "$g->igsn");
			$objPHPExcel->getActiveSheet()->setCellValue($collabels[2].$myrow, strtoupper($g->source));
			$objPHPExcel->getActiveSheet()->setCellValue($collabels[3].$myrow, $g->doi);
			$objPHPExcel->getActiveSheet()->setCellValue($collabels[4].$myrow, $g->title);
			$objPHPExcel->getActiveSheet()->setCellValue($collabels[5].$myrow, $g->journal);
			$objPHPExcel->getActiveSheet()->setCellValue($collabels[6].$myrow, $g->authorlist);
			$objPHPExcel->getActiveSheet()->setCellValue($collabels[7].$myrow, $g->expeditionid);
			$objPHPExcel->getActiveSheet()->setCellValue($collabels[8].$myrow, $g->latitude);
			$objPHPExcel->getActiveSheet()->setCellValue($collabels[9].$myrow, $g->longitude);
			$objPHPExcel->getActiveSheet()->setCellValue($collabels[10].$myrow, $g->locprecision);
			$objPHPExcel->getActiveSheet()->setCellValue($collabels[11].$myrow, $g->age_min);
			$objPHPExcel->getActiveSheet()->setCellValue($collabels[12].$myrow, $g->age);
			$objPHPExcel->getActiveSheet()->setCellValue($collabels[13].$myrow, $g->age_max);
			$objPHPExcel->getActiveSheet()->setCellValue($collabels[14].$myrow, $g->method);
			$objPHPExcel->getActiveSheet()->setCellValue($collabels[15].$myrow, strtoupper($g->class1));
			$objPHPExcel->getActiveSheet()->setCellValue($collabels[16].$myrow, strtoupper($g->class2));
			$objPHPExcel->getActiveSheet()->setCellValue($collabels[17].$myrow, strtoupper($g->class3));
			$objPHPExcel->getActiveSheet()->setCellValue($collabels[18].$myrow, strtoupper($g->class4));
			$objPHPExcel->getActiveSheet()->setCellValue($collabels[19].$myrow, strtoupper($g->mineralname));

                        //************************************************************************************************************************
                        // start filling the value for the specific variable_unit combined column,rowtype=sample refactored by PJ on Mar 7 2024
                        //************************************************************************************************************************
			$mycol=20;
                        $countcols=count($vu_column_list);
                        for($j=0;$j<$countcols;$j++){
                                $ismatch = false;
                                foreach ($showlist_array as $currentfield) {
                                        $currentfield=strtolower($currentfield);
                                        $currentunit = $currentfield."_unit";
                                        if(strtolower($vu_column_list[$j]) == $currentfield."_".strtolower($g->$currentunit)){
                                                $thisval=$g->$currentfield;

                                                if ($thisval!="") {
                                                        if($thisval<0){
                                                                $thisval=$thisval*-1;
                                                                $thisval="< ".$thisval;
                                                        }
                                                }
                                                $objPHPExcel->getActiveSheet()->setCellValue($collabels[$mycol].$myrow, $thisval);
                                                $mycol++;
                                		if ($showmethods) {
                                        		$fldname=$currentfield."_meth";
                                        		if (isset($g->$fldname)) {
                                                		$objPHPExcel->getActiveSheet()->setCellValue($collabels[$mycol].$myrow, $g->$fldname);
                                        		} else {
								$objPHPExcel->getActiveSheet()->setCellValue($collabels[$mycol].$myrow, "");
                                        		}
							$mycol++;
                                		}
                                                $ismatch=true;
						break;
                                        }

                                } // foreach showlist_array
                                if(!$ismatch){
					$objPHPExcel->getActiveSheet()->setCellValue($collabels[$mycol].$myrow, "");
					$mycol++;
					if($showmethods) {
						$objPHPExcel->getActiveSheet()->setCellValue($collabels[$mycol].$myrow, "");
						$mycol++;
                                        }
                                }
                        } // for vu_column_list
                        //***********************************************************************
                        // end filling the value for the specific variable_unit combined column
                        //***********************************************************************
			$myrow++;
		} // foreach getsample



		// now do the references as methods
		$objPHPExcel->createSheet();
	
		$objPHPExcel->setActiveSheetIndex(1);
		$objPHPExcel->getActiveSheet()->setTitle('References');
		$objPHPExcel->getActiveSheet()->setCellValue('A1', 'References');
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(20);
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setItalic(true);

		$columnnames=array("AUTHOR","YEAR","TITLE","JOURNAL");

		$myrow=3;

		$colnum=0;
		foreach($columnnames as $columnname){
			$thisheader=$columnname;
			$objPHPExcel->getActiveSheet()->setCellValue($collabels[$colnum].$myrow, $thisheader);
			$objPHPExcel->getActiveSheet()->getStyle($collabels[$colnum].$myrow)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
			$objPHPExcel->getActiveSheet()->getStyle($collabels[$colnum].$myrow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$objPHPExcel->getActiveSheet()->getStyle($collabels[$colnum].$myrow)->getFill()->getStartColor()->setARGB('FF333333');
			$colnum++;
		}






		$myrow=4;
		
		$refs=$db->get_results("$refstring");
		
		foreach($refs as $ref){

			$objPHPExcel->getActiveSheet()->setCellValue($collabels[0].$myrow, strtoupper($ref->author));
			$objPHPExcel->getActiveSheet()->setCellValue($collabels[1].$myrow, $ref->pubyear);
			$objPHPExcel->getActiveSheet()->setCellValue($collabels[2].$myrow, $ref->title);
			$objPHPExcel->getActiveSheet()->setCellValue($collabels[3].$myrow, $ref->journal);
			$myrow++;
		}
		
		$objPHPExcel->setActiveSheetIndex(0);

		require_once 'Classes/PHPExcel/IOFactory.php';

		// Redirect output to a client’s web browser (Excel2007)
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="'.$downloadfilename.'"');
		header('Cache-Control: max-age=0');

		// Save Excel 2007 file
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
	
		exit();
		
	}//end rowtype switch
	
	
}
// end XLS OUTPUT

//******************************************************************************************
//******************************************************************************************
//******************************************************************************************



if ($dispmode == "html") { // This is the start of the html output

	
	include('includes/ks_head.html'); // load the page header (and later, the page footer)
	
	if (isset($_GET['totalcount'])) { 
		$totalcount=$_GET['totalcount'];
	} else { 
		if ($rowtype == "method") {
			//$sql_totalcount = " SELECT COUNT(*) as totalcount FROM ( $advstring ) foo "; 
			$sql_totalcount = $advcount; 
		} else { // rowtype == 'sample' 
		
		
		
			$sql_totalcount = $samplecount;

		} // if, else 
		//debug: echo "<p>sql_totalcount is <br>$sql_totalcount ";
	
		//echo " $sql_totalcount <br><br><br>";
	
		//exit();
	
	
		//echo nl2br($sql_totalcount);
		$totalcount_query=$db->get_row($sql_totalcount) or die("could not execute totalcount query in advancedoutputc.php");
		$totalcount=$totalcount_query->totalcount;
	} // if, else 
	//echo "<p>totalcount is $totalcount";
        
	// If there no records (samples) were returned from our search, show a message and inelegantly exit. 
	if ($totalcount < 1) {
		include "includes/ks_head.html";
		echo "<h2>Sorry, no records found.</h2>";
	?>
	<div id="debug" style="display:none;">
	<?
	echo nl2br($sql_totalcount);
	?>
	</div>
	<?
	//echo "<!-- query";
	//echo nl2br($sql_totalcount);
	//echo "query -->";
		include "includes/ks_footer.html";
		exit; // Eileen change this inelegant logic. For now, it's more readable for me to do it this way. 
	}
	
	
	//str_repeat(" ",2048);flush();
	
	if ($rowtype == "method") {
		$sql_getsample .= $advstring;  
	} else { // rowtype = "sample"
		$sql_getsample .= $samplechemstring;  
	} // if
	$sort = ($_GET['sort']!='') ? $_GET['sort'] : 'material_pkey'; // An optional sort order might be passed if the user clicked on a table heading link in the html output.
	if ($sort > '') {
		$sql_getsample .= " ORDER BY $sort ";
	} // if
	//$sql_getsample = " SELECT * FROM (SELECT a.*, rowNUM rnum FROM ( $sql_getsample ) a where rownum <= $endrow ) WHERE RNUM >= $startrow ";
	
        

	$sql_getsample = "$sql_getsample limit $numtoshow offset $startrow-1 ";
	
	//echo nl2br($sql_getsample);
	//exit();
	
	//echo nl2br($sql_getsample);
	
	//echo "<div>$sql_getsample</div>";
	
        //print_r($sql_getsample);
	$getsample=$db->get_results($sql_getsample) or die("could not execute getsample query in advancedoutputc.php");
	$numpages=floor( ($totalcount+$numtoshow-1) / $numtoshow );
	if (count($getsample) > 0) {
		// The page heading for html output, for either method or sample rowtype
		echo '<a name="topoftable"></a>';
		echo "<div style=\"float:left;\"><H1>Sample Data Output: $totalcount ";
		if ($rowtype == "method") {echo "result";} else {echo "result";}
		if ($totalcount != 1) {echo "s";} 
		/*
		echo " found</H1></div><div style=\"float:left;\">
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<a href=\"htmlhelp.php\" data-fancybox data-type=\"ajax\" data-caption=\"EarthChem Help\"><img src=\"help.jpg\" border=\"0\"></a></div><div style=\"clear:left;\"></div>";
		*/
		if ($totalcount <=50000){
		echo " found</H1></div><div style=\"float:left;\">
		</div><div style=\"clear:left;\"></div>";
		} elseif($totalcount >50000 and $totalcount <=80000){
		echo " found. Your request reached the xls download limit, only csv download is available.</H1></div><div style=\"float:left;\">
		</div><div style=\"clear:left;\"></div>";
		} else {
		echo " found. Your request reached the system limit, directly download is not available, please contact us.</H1></div><div style=\"float:left;\">
		</div><div style=\"clear:left;\"></div>";
		}
		
		?>
		<? if ($totalcount <=50000) { ?>
			<button class="submitbutton" type="cancel" onClick="window.location.href='/'">New Search</button>
			<button class="submitbutton" type="cancel" onClick="window.location.href='<?=$xlslink?>'">Download XLS</button>
			<button class="submitbutton" type="cancel" onClick="window.location.href='<?=$csvlink?>'">Download CSV</button>
		<?}?>

		<? if ($totalcount > 50000 and $totalcount <=100000) { ?>
			<button class="submitbutton" type="cancel" onClick="window.location.href='/'">New Search</button>
			<button class="submitbutton" type="cancel" onClick="window.location.href='<?=$csvlink?>'">Download CSV</button>
		<?}?>
		<? if ($totalcount > 100000) {  ?>

			<button class="submitbutton" type="cancel" onClick="window.location.href='/'">New Search</button>
		<?}?>
<!--
		<button class="submitbutton" type="cancel" onClick="window.location.href='/'">New Search</button>
		<button class="submitbutton" type="cancel" onClick="window.location.href='<?=$xlslink?>'">Download XLS</button>
		<button class="submitbutton" type="cancel" onClick="window.location.href='<?=$csvlink?>'">Download CSV</button>
		-->
		<?
		
		// Show links to all the pages of this report, unless there are very many of them, in which case show the current page plus a few before and after. Also add NEXT PAGE and PREV PAGE links.
		$pagelinks = ''; // Store the code for the links to other pages in a variable, so it is easy to display it twice on this page. 
		if ($currentpage>1) {
			$p=$currentpage-1;
			$pagelinks .= "<a HREF='advancedoutputc.php?pkey=$pkey&totalcount=$totalcount&currentpage=$p&rowtype=$rowtype&numresults=$numtoshow&showmethods=$showmethods&showunits=$showunits&sort=$sort".$urldsstring."'>&lt;&lt;PREV</a> &nbsp; ";
		}
		$ellipse=false;
		for ($p=1;$p<=$numpages;$p++) {
			if ($p==$currentpage) {
				$pagelinks .= " $p ";
			} elseif ( 
				( $p>=($currentpage-3) && $p<=($currentpage+3) ) ||
				( $p>=($numpages-3) ) || 
				( $p == 1 ) ||
				( $numpages < 20 ) 
				) { // Show links for page 1, and links for about three pages on either side of the current page, and each of the first 20 pages 
				$pagelinks .= "<a HREF='advancedoutputc.php?pkey=$pkey&totalcount=$totalcount&currentpage=$p&rowtype=$rowtype&numresults=$numtoshow&showmethods=$showmethods&showunits=$showunits&sort=$sort".$urldsstring."'> $p </a>";
			} else {
				if (!$ellipse) {$pagelinks .= '...'; $ellipse=true;} // Only add the ... once 
			}
		}
		$pagelinks .= "&nbsp;of $numpages ";
		if ($currentpage<$numpages) {
			$p=$currentpage+1;
			$pagelinks .= " &nbsp; <a HREF='advancedoutputc.php?pkey=$pkey&totalcount=$totalcount&currentpage=$p&rowtype=$rowtype&numresults=$numtoshow&showmethods=$showmethods&showunits=$showunits&sort=$sort".$urldsstring."'>NEXT&gt;&gt;</a>";
		}
		$pagelinks='<div style="margin:5px">'.$pagelinks."</div>";
		echo $pagelinks;

		// For html output, where rowtype = method 
		if ($rowtype == "method") { 
						$sort_url_base="advancedoutputc.php?showmethods=$showmethods&showunits=$showunits&rowtype=$rowtype&pkey=$pkey&numresults=$numtoshow&totalcount=$totalcount".$urldsstring; // Some column headings are a link, that will refresh the page and sort results on that column


/*
sample_id
igsn
source
doi
title
journal
authorlist
cruiseid
latitude
longitude
locprecision
age_min
age
age_max
method
materialtype
class1
class2
class3
class4
mineralname
*/

if($sort=="sample_id"){$sortsample_id="sample_id desc";}else{$sortsample_id="sample_id";}
if($sort=="igsn"){$sortigsn="igsn desc";}else{$sortigsn="igsn";}
if($sort=="source"){$sortsource="source desc";}else{$sortsource="source";}
if($sort=="doi"){$sortdoi="doi desc";}else{$sortdoi="doi";}
if($sort=="title"){$sorttitle="title desc";}else{$sorttitle="title";}
if($sort=="journal"){$sortjournal="journal desc";}else{$sortjournal="journal";}
if($sort=="authorlist"){$sortauthorlist="authorlist desc";}else{$sortauthorlist="authorlist";}
if($sort=="expeditionid"){$sortexpeditionid="expeditionid desc";}else{$sortexpeditionid="expeditionid";}
if($sort=="latitude"){$sortlatitude="latitude desc";}else{$sortlatitude="latitude";}
if($sort=="longitude"){$sortlongitude="longitude desc";}else{$sortlongitude="longitude";}
if($sort=="locprecision"){$sortlocprecision="locprecision desc";}else{$sortlocprecision="locprecision";}
if($sort=="age_min"){$sortage_min="age_min desc";}else{$sortage_min="age_min";}
if($sort=="age"){$sortage="age desc";}else{$sortage="age";}
if($sort=="age_max"){$sortage_max="age_max desc";}else{$sortage_max="age_max";}
if($sort=="method"){$sortmethod="method desc";}else{$sortmethod="method";}
if($sort=="materialtype"){$sortmaterialtype="materialtype desc";}else{$sortmaterialtype="materialtype";}
if($sort=="class1"){$sortclass1="class1 desc";}else{$sortclass1="class1";}
if($sort=="class2"){$sortclass2="class2 desc";}else{$sortclass2="class2";}
if($sort=="class3"){$sortclass3="class3 desc";}else{$sortclass3="class3";}
if($sort=="class4"){$sortclass4="class4 desc";}else{$sortclass4="class4";}
if($sort=="mineralname"){$sortmineralname="mineralname desc";}else{$sortmineralname="mineralname";}

?>


		<table class="ku_htmloutput">
		<tr valign=top VALIGN="bottom">
		<th>
		 <h4>
		 <?php
		echo '<a href="'.$sort_url_base.'&sort='.$sortsample_id.'" onMouseOver="return escape(\'Unique identifier provided by the source database. \')" >SAMPLE&nbsp;ID</a>';
		?>
		 </h4>
		 </th>

		<th>
		 <h4>
		 <?php
		echo '<a href="'.$sort_url_base.'&sort='.$sortigsn.'" onMouseOver="return escape(\'IGSN \')" >IGSN</a>';
		?>
		 </h4>
		 </th>


		 <th>
		 <h4>
		 <?php
		echo '<div onMouseOver="return escape(\'Source database of data. \')">SOURCE</div>';
		?>
		 </h4>
		 </th>
		 <th>
		 <h4>
		 <?php
		echo '<div onMouseOver="return escape(\'Provides a link to the source database details page for the given sample. \')">DETAIL</div>';
		?>
		 </h4>
		 </th>
		 
		 
		 <th>
		 <h4><div onMouseOver="return escape('This link provides an inter-operable interface between EarthChem and the LEPR ( Library of Experimental Phase Relations ) database. The link opens a page which contains sliders which allow users to change the chemical constraints used to query the LEPR database. The page also contains a nearest neighbor result which shows the LEPR sample which most closely resembles the sample in question. ')">LEPR</div></h4>
		 </th>

		 
		 <th>
		 <h4><div onMouseOver="return escape('This link provides an inter-operable interface between EarthChem and the MELTS database.  ')">MELTS</div></h4>
		 </th>
		 
		 
		 <th>
		 <h4><div onMouseOver="return escape('Provides a link to a static map of the sample location. ')">MAP</div></h4>
		 </th>

		 <th>
		 <h4><div onMouseOver="return escape('Find samples in the database that are chemically similar to this sample. ')">SIMILAR</div></h4>
		 </th>


		<!--
		 <th>
		 <h4><div onMouseOver="return escape('Provides a link to Google Scholar, using reference information from the sample to search for the source reference. ')">GOOGLE&nbsp;SCHOLAR</div></h4>
		 </th>
		-->


		<th>
		 <h4>
		 <?php
		echo '<a href="'.$sort_url_base.'&sort='.$sortdoi.'" onMouseOver="return escape(\'If provided, provides a link back to the original citation.  \')" >DOI</a>';
		?>
		 </h4>
		 </th>


		 <th>
		 <h4>
		 <?php
		echo '<a href="'.$sort_url_base.'&sort='.$sorttitle.'" onMouseOver="return escape(\'Title of the sample reference. \')">TITLE</a>';
		?>
		 </h4>
		 </th>
		 <th>
		 <h4>
		 <?php
		echo '<a href="'.$sort_url_base.'&sort='.$sortjournal.'" onMouseOver="return escape(\'Journal of the sample reference. \')">JOURNAL</a>';
		?>
		 </h4>
		 </th>
		 <th>
		 <h4>
		 <?php
		echo '<a href="'.$sort_url_base.'&sort='.$sortauthorlist.'" onMouseOver="return escape(\'Author of the sample reference. \')">AUTHOR</a>';
		?>
		 </h4>
		 </th>

		 <th>
		 <h4>
		 <?php
		echo '<a href="'.$sort_url_base.'&sort='.$sortexpeditionid.'" onMouseOver="return escape(\'Cruise ID of the sample. \')">CRUISEID</a>';
		?>
		 </h4>
		 </th>

		 <th>
		 <h4>
		 <?php
		echo '<a href="'.$sort_url_base.'&sort='.$sortlatitude.'" onMouseOver="return escape(\'Latitude of the sample. \')">LATITUDE</a>';
		?>
		 </h4>
		 </th>

		 <th>
		 <h4>
		 <?php
		echo '<a href="'.$sort_url_base.'&sort='.$sortlongitude.'" onMouseOver="return escape(\'Longitude of the sample. \')">LONGITUDE</a>';
		?>
		 </h4>
		 </th>
		 <th>
		 <h4>
		 <?php
		echo '<a href="'.$sort_url_base.'&sort='.$sortlocprecision.'" onMouseOver="return escape(\'Location Precision of the sample. \')">LOC&nbsp;PREC</a>';
		?>
		 </h4>
		 </th>

		<th nowrap>
		<h4>
		<?php
		echo '<a href="'.$sort_url_base.'&sort='.$sortage_min.'" onMouseOver="return escape(\'Minimum age of the sample. \')">MIN AGE</a>';
		?>
		</h4>
		</th>
		<th nowrap>
		<h4>
		<?php
		echo '<a href="'.$sort_url_base.'&sort='.$sortage.'" onMouseOver="return escape(\'Age of the sample. \')">AGE</a>';
		?>
		</h4>
		</th>
		<th nowrap>
		<h4>
		<?php
		echo '<a href="'.$sort_url_base.'&sort='.$sortage_max.'" onMouseOver="return escape(\'Maximum age of the sample. \')">MAX AGE</a>';
		?>
		</h4>
		</th>
		 <th>
		 <h4>
		 <?php
		echo '<a href="'.$sort_url_base.'&sort='.$sortmethod.'" onMouseOver="return escape(\'Method used in determining chemical values. \')">METHOD</a>';
		?>
		 </h4>
		 </th>
		 <th>
		 <h4>
		 <?php
		echo '<a href="'.$sort_url_base.'&sort='.$sortclass1.'" onMouseOver="return escape(\'Material of the sample. \')">MATERIAL</a>';
		?>
		 </h4>
		 </th>
		 <th>
		 <h4>
		 <?php
		echo '<a href="'.$sort_url_base.'&sort='.$sortclass2.'" onMouseOver="return escape(\'Rock type of the sample. \')">TYPE</a>';
		?>
		 </h4>
		 </th>
		 <th>
		 <h4>
		 <?php
		echo '<a href="'.$sort_url_base.'&sort='.$sortclass3.'" onMouseOver="return escape(\'Composition of the sample. \')">COMPOSITION</a>';
		?>
		 </h4>
		 </th>
		 <th nowrap>
		 <h4>
		 <?php
		echo '<a href="'.$sort_url_base.'&sort='.$sortclass4.'" onMouseOver="return escape(\'Rock name provided by source database for the sample. \')">ROCK&nbsp;NAME</a>';
		?>
		</h4>
		</th>
		<?
		if($searchquery->material=="3"){
		echo "<th><h4>";
		echo '<a href="'.$sort_url_base.'&sort='.$sortmineral.'" onMouseOver="return escape(\'Mineral name of the sample. \')">MINERAL</a>';
		echo "</h4></tx>";
		}
		
		?>
		<?PHP
 //*******************************************************************************************************
 // start generating variable_unit combined column headers,rowtype=method (refactored by PJ on Mar 5-11 2024)
 //*******************************************************************************************************

		 	$dlnum=0;
			$vu_column_list=array();
                        $only_null_arr=array("*");
                        $space_and_null_arr=array("","*");
			foreach($showlist_array as $field_name) {
   				foreach($get_variableunits as $g) {
   					foreach($g as $key=>$value){
   						if(strtoupper($key)==$field_name){
   							$vu_str=$value;
   							break;
   						}
   					}       
   				} 
				$vu_arr = explode (",", $vu_str);
                                if(count(array_diff($vu_arr,$only_null_arr))==0 or count(array_diff($vu_arr,$space_and_null_arr))==0){
					$showfield=$field_name;
					$vu_column_list[]=$field_name;
					$displayfield=$display_list[$dlnum]."_".$vu_arr[0];
					if($sort==$field_name){
						$sortstring="$field_name desc";
					}else{
						$sortstring="$field_name";
					}
			?>
		 <th nowrap>
		 <h4>
		 <?php
		//echo '<a href="'.$sort_url_base.'&sort='.$field_name.'">'.$showfield.'</a>';
		echo '<a href="'.$sort_url_base.'&sort='.$sortstring.'">'.$displayfield.'</a>';
		?>
		 </h4>
		 </th>
		 <?PHP
                                } //endif
                                else {
                                	$new_vu_arr = array_diff($vu_arr,$only_null_arr);
                                        for($i=0;$i<count($new_vu_arr);$i++){
                                        	$showfield=$field_name;
						$vu_column_list[]=$field_name;
                                        	$displayfield=$display_list[$dlnum]."_".$new_vu_arr[$i];
                                        	if($sort==$field_name){
                                                	$sortstring="$field_name desc";
                                        	}else{
                                                	$sortstring="$field_name";
                                        	}
                        ?>
                 <th nowrap>
                 <h4>
                 <?php
                echo '<a href="'.$sort_url_base.'&sort='.$sortstring.'">'.$displayfield.'</a>';
                ?>
                 </h4>
                 </th>
                 <?PHP
                                        } //endfor
                                } //endelse
		 		$dlnum++;
			} // foreach
//***********************************************************
// end gengeration variable_unit combined column headers
//*********************************************************** 
			?>
		</tr>
		<?php	// continued from if rowtype == method, above 			
			$row=1;
			foreach ($getsample as $g) {
			
			//print_r($g);
			//exit();

				if ($row == 1) {
					$bgcolor="#FFFFFF";
					$row=2;
				} else {
					$bgcolor="#FFF5F5"; //FDE6C2
					$row=1;
				} // if
				if ($g->url != '') {
					
					//make accomodations for petdb mess here
					if($g->source == 'petdb'){
						//$urlstring='<a href="http://isotope.ldeo.columbia.edu:7001/petdbWeb/search/sample_info.jsp?singlenum='.$g->samplenumber.'" target="_blank">DETAILS</a>';
						$urlstring='<a href="http://www.petdb.org/petdbWeb/search/sample_info.jsp?singlenum='.$g->sample_num.'" target="_blank">DETAILS</a>';
					}elseif($g->source == 'navdat'){
						$urlstring = '<a href="http://www.navdat.org/NavdatPortal/GetSample.cfm?sample_num='.$g->samplenumber.'" target="_blank">DETAILS</a>'; 
					}else{
						$urlstring = '<a href="'.$g->url.'" target="_blank">DETAILS</a>'; 
					}
				} else {
					$urlstring=' ';
				} // if
				
		 		echo "<tr style=\"background-color:$bgcolor;\" valign=top>";
				
				echo "<td nowrap>$g->sample_id</TD><td nowrap>$g->igsn</TD><td>".strtoupper($g->source)."</TD><td>$urlstring</TD><td>";
				//echo "<a target='_blank' href='http://navdat.kgs.ku.edu/maps/ecmap.cfm?lat=$g->latitude&long=$g->longitude&source=$source&samplenum=$g->sample_num'>MAP</a>"
				
				
				
				if($g->lepr_num!=""){
				?>				
				<!--
				<a href="lepr_slider.php?lepr_num=<?=$g->lepr_num?>" target="_blank">LEPR</a>
				-->
				
				<a href="lepr_slider.php?material_pkey=<?=$g->material_pkey?>" target="_blank">LEPR</a>
				<?
				}else{
					echo "&nbsp;";
				}
				

				
				echo "</td><td>";

				

				if($g->lepr_num!="" && $g->sio2!=""){
				?>				
				<a href="meltsinterface.php?method_pkey=<?=$g->method_pkey?>" target="_blank">MELTS</a>
				<?
				}else{
					echo "&nbsp;";
				}
				
				echo "</td><td>";

				

				/*<a href="ecindividualmap.php?pkey=<?=$g->sample_pkey?>" data-fancybox data-caption="EarthChem Sample Map">MAP</a>*/
				?>
				
				
				<a data-fancybox data-type="iframe" data-src="ecindividualmap.php?pkey=<?=$g->sample_pkey?>" href="javascript:;">MAP</a>
				
				<?
				
				echo "</td><td>";

				if($g->lepr_num!=""){
				?>				
				<a href="ecneighbor.php?material_pkey=<?=$g->material_pkey?>" target="_blank">SIMILAR</a>
				<?
				}else{
					echo "&nbsp;";
				}
				
				echo "</td>";




			
		// GoogleScholar code
		// Process the authors a bit for GoogleScholar advanced search link url, displayed below. Replace quotes, semicolons, commas, colons, +
		$googlescholarauthor = $g->authorlist;
		$googlescholarauthor = trim($googlescholarauthor);
		$googlescholarauthor = str_replace('"','',$googlescholarauthor); 
		$googlescholarauthor = str_replace(";"," ",$googlescholarauthor); 
		$googlescholarauthor = str_replace(","," ",$googlescholarauthor); 
		$googlescholarauthor = str_replace("-"," ",$googlescholarauthor); 
		$googlescholarauthor = str_replace(":"," ",$googlescholarauthor); 
		$googlescholarauthor = str_replace("+"," ",$googlescholarauthor); 
		// Get just the first author name, a last name. This is because EarthChem authors in the view often have initials concatenated with lastnames, and Google Scholar cannot find them
		$n=strpos($googlescholarauthor," ");
		if ($n>0) {
		$googlescholarauthor = trim(substr($googlescholarauthor, 0, $n));
		}
		// Pocess the titles a bit for GoogleScholar advanced search link url, displayed below --->
		$googlescholartitle = $g->title; 
		$googlescholartitle = str_replace('"','',$googlescholartitle); 
		$googlescholartitle = str_replace(";"," ",$googlescholartitle); 
		$googlescholartitle = str_replace(","," ",$googlescholartitle); 
		$googlescholartitle = str_replace("-"," ",$googlescholartitle); 
		$googlescholartitle = str_replace(":"," ",$googlescholartitle); 
		$googlescholartitle = str_replace("+"," ",$googlescholartitle); 
		$googlescholartitle = str_replace("title"," ",$googlescholartitle);
		$googlescholartitle = str_replace(" "," ",$googlescholartitle);
		$googlescholartitle = trim($googlescholartitle);
		if (strlen($googlescholartitle) > 0) { //Get the first 26 letters, then round up to nearest whole word 
		$n = strpos(substr($googlescholartitle,45),' ') + 45;
		$googlescholartitle = substr($googlescholartitle,0,$n);
		$googlescholartitle = str_replace(" ","+",$googlescholartitle); // Replace spaces with + for the google scholar url
		} // if
		$googlescholaryear = $g->pubyear; 
		//<!--- if we got any parameters to pass to GoogleScholar, post a link --->
		if ($googlescholartitle > '' && $googlescholarauthor > '' && $googlescholaryear > '') {
		/*
		echo "
		<A target='_blank' href='http://scholar.google.com/scholar?as_q=&num=10&btnG=Search+Scholar&as_epq=$googlescholartitle&as_sauthor=$googlescholarauthor&as_publication=&as_ylo$googlescholaryear&as_yhi$googlescholaryear&as_allsubj=all&hl=en&lr='>Reference</A>
		";
		*/
		//echo"<A target='_blank' href='http://scholar.google.com/scholar?hl=en&q=$googlescholartitle+"."$googlescholarauthor"."&btnG=&as_sdt=1%2C5&as_sdtp='>Reference</A>";
		} // if
		?>
		
		
		<TD>
			<?
			if($g->doi!=""){
				echo "<a href=\"http://dx.doi.org/".$g->doi."\" target=\"_blank\">".$g->doi."</a>";
			}else{
				echo "&nbsp;";
			}
			?>
		</TD>
		
		<td style="white-space:nowrap" >
		 <?php
			if (strlen($g->title) > 24) { 
			/*onMouseOver="return escape('<?=fixstring($g->title) ?>')"*/
			?>
		 <div onMouseOver="return escape('<?=fixstring($g->title) ?>')">
		 <?=substr($g->title, 0, 25) ?>
		 ... </div>
		 <?php
		} else {
			echo fixstring($g->title);
		} // if
		?>
		 </td>
		 <td style="white-space:nowrap" >
		 <?php
		if (strlen($g->journal) > 24) {
		?>
		 <div onMouseOver="return escape('<?=fixstring($g->journal) ?>')">
		 <?=substr($g->journal, 0, 25) ?>
		 ... </div>
		 <?php
		} else {
		echo fixstring($g->journal);
		} // if
		?>
		 </td>
		 <td style="white-space:nowrap">
		 <?php					
		if (strlen($g->authorlist) > 24) {
		?>
		 <div onMouseOver="return escape('<?=fixstring(strtoupper($g->authorlist)) ?>')">
		 <?=substr(strtoupper(fixstring($g->authorlist)),0,25) ?>
		 ... </div>
		 <?php
		} else {
		echo strtoupper(fixstring($g->authorlist));
		} // if
		?>
		 </td>

		 <td nowrap>
		 <?
		 if($g->expeditionurl!=""){
		 ?>
		 <a href="<?=$g->expeditionurl?>" target="_blank"><?=$g->expeditionid?></a>
		 <?
		 }else{
		 ?>
		 <?=$g->expeditionid ?>
		 <?
		 }
		 ?>
		 </td>

		 <td>
		 <?=$g->latitude ?>
		 </td>

		 <td>
		 <?=$g->longitude ?>
		 </td>
		 <td>
		 <?=$g->locprecision ?>
		 </td>
		<TD ><?=$g->age_min?></TD>
		<TD ><?=$g->age?></TD>
		<TD ><?=$g->age_max?></TD>
		 <td>

		 <?=$g->method ?>

		 </td>
		 <td nowrap>
		 <?=strtoupper($g->class1) ?>
		 </td>
		 <td>
		 <?=strtoupper($g->class2) ?>
		 </td>
		 <td>
		 <?=strtoupper($g->class3) ?>
		 </td>
		 <td nowrap>
		 <?=strtoupper($g->class4) ?>
		 </td>
		 <?PHP
		 

		if($searchquery->material=="3"){
		echo "<td nowrap>";
		if($g->mineralname!=""){
			echo strtoupper($g->mineralname);
		}else{
			echo "NOT GIVEN";
		}
		echo "</td>";
		}
//************************************************************************************************************************
// start filling the value for the specific variable_unit combined column, rowtype=method refactored by PJ on Mar 5-11 2024
//************************************************************************************************************************
		$countcols=count($vu_column_list);
		for($j=0;$j<$countcols;$j++){
                        $ismatch = false;		 
			foreach($showlist_array as $currentfield) {
				$currentfield=strtolower($currentfield);
                                $currentunit = $currentfield."_unit";
				if(strtolower($vu_column_list[$j]) == $currentfield."_".strtolower($g->$currentunit)){
					echo "<TD style='white-space:nowrap'>";

					$thisval=$g->$currentfield;			
					if($thisval < 0){
						$thisval=$thisval*-1;
						$thisval="< ".$thisval;
					}
			
					echo $thisval;
					echo "</TD>";
                                        $ismatch=true;
                                        break;
                                }
			} // foreach showlist_array
                        if(!$ismatch){
                        	echo "<TD></TD>";
                        }
                }
//***********************************************************************
// end filling the value for the specific variable_unit combined column
//***********************************************************************
		echo "</TR>";
		} // foreach
		echo "</table>";
		echo $pagelinks;
		echo '<div style="margin:5px"><a href="#topoftable">top</a></div>'; 
		
	} else { // if $rowtype == 'method', else: Here is the table for html output, sample-level rows 
	
$sort_url_base="advancedoutputc.php?showmethods=$showmethods&showunits=$showunits&rowtype=$rowtype&pkey=$pkey&numresults=$numtoshow&totalcount=$totalcount".$urldsstring; // some column headings are links, that will re-search and resort the samples on the page

/*
sample_id
igsn
source
doi
title
journal
authorlist
cruiseid
latitude
longitude
locprecision
age_min
age
age_max
method
materialtype
class1
class2
class3
class4
mineralname
*/

if($sort=="sample_id"){$sortsample_id="sample_id desc";}else{$sortsample_id="sample_id";}
if($sort=="igsn"){$sortigsn="igsn desc";}else{$sortigsn="igsn";}
if($sort=="source"){$sortsource="source desc";}else{$sortsource="source";}
if($sort=="doi"){$sortdoi="doi desc";}else{$sortdoi="doi";}
if($sort=="title"){$sorttitle="title desc";}else{$sorttitle="title";}
if($sort=="journal"){$sortjournal="journal desc";}else{$sortjournal="journal";}
if($sort=="authorlist"){$sortauthorlist="authorlist desc";}else{$sortauthorlist="authorlist";}
if($sort=="expeditionid"){$sortexpeditionid="expeditionid desc";}else{$sortexpeditionid="expeditionid";}
if($sort=="latitude"){$sortlatitude="latitude desc";}else{$sortlatitude="latitude";}
if($sort=="longitude"){$sortlongitude="longitude desc";}else{$sortlongitude="longitude";}
if($sort=="locprecision"){$sortlocprecision="locprecision desc";}else{$sortlocprecision="locprecision";}
if($sort=="age_min"){$sortage_min="age_min desc";}else{$sortage_min="age_min";}
if($sort=="age"){$sortage="age desc";}else{$sortage="age";}
if($sort=="age_max"){$sortage_max="age_max desc";}else{$sortage_max="age_max";}
if($sort=="method"){$sortmethod="method desc";}else{$sortmethod="method";}
if($sort=="materialtype"){$sortmaterialtype="materialtype desc";}else{$sortmaterialtype="materialtype";}
if($sort=="class1"){$sortclass1="class1 desc";}else{$sortclass1="class1";}
if($sort=="class2"){$sortclass2="class2 desc";}else{$sortclass2="class2";}
if($sort=="class3"){$sortclass3="class3 desc";}else{$sortclass3="class3";}
if($sort=="class4"){$sortclass4="class4 desc";}else{$sortclass4="class4";}
if($sort=="mineralname"){$sortmineralname="mineralname desc";}else{$sortmineralname="mineralname";}

?>
<table class="ku_htmloutput">
<tr valign=top VALIGN="bottom">
<th nowrap>
<h4>
<?php
echo '<a href="'.$sort_url_base.'&sort='.$sortsample_id.'">SAMPLE&nbsp;ID</a>';
?>
</h4>
</th>

<th nowrap>
<h4>
<?php
echo '<a href="'.$sort_url_base.'&sort='.$sortigsn.'">IGSN</a>';
?>
</h4>
</th>

<th nowrap>
<h4>
<?php
echo '<div>SOURCE</div>';
?>
</h4>
</th>
<th nowrap>
<h4> DETAIL </h4>
</th>
		 <th>
		 <h4>LEPR</h4>
		 </th>
<th nowrap>
<h4> MAP </h4>
</th>

<th nowrap>
<h4> SIMILAR </h4>
</th>


<!--
<th>
<h4><div onMouseOver="return escape('Provides a link to Google Scholar, using reference information from the sample to search for the source reference. ')">GOOGLE&nbsp;SCHOLAR</div></h4>
</th>
-->

<th nowrap>
<h4>
<?php
echo '<a href="'.$sort_url_base.'&sort='.$sortdoi.'">DOI</a>';
?>
</h4>
</th>

<th nowrap>
<h4>
<?php
echo '<a href="'.$sort_url_base.'&sort='.$sorttitle.'">TITLE</a>';
?>
</h4>
</th>

<th nowrap>
<h4>
<?php
echo '<a href="'.$sort_url_base.'&sort='.$sortjournal.'">JOURNAL</a>';
?>
</h4>
</th>

<th nowrap>
<h4>
<?php
echo '<a href="'.$sort_url_base.'&sort='.$sortauthorlist.'">AUTHOR</a>';
?>
</h4>
</th>



<th nowrap>
<h4>
<?php
echo '<a href="'.$sort_url_base.'&sort='.$sortexpeditionid.'">CRUISE&nbsp;ID</a>';
?>
</h4>
</th>

<th nowrap>
<h4>
<?php
echo '<a href="'.$sort_url_base.'&sort='.$sortlatitude.'">LATITUDE</a>';
?>
</h4>
</th>

<th nowrap>
<h4>
<?php
echo '<a href="'.$sort_url_base.'&sort='.$sortlongitude.'">LONGITUDE</a>';
?>
</h4>
</th>
<th nowrap>
<h4>
<?php
echo '<a href="'.$sort_url_base.'&sort='.$sortlocprecision.'">LOC PREC</a>';
?>
</h4>
</th>

<th nowrap>
<h4>
<?php
echo '<a href="'.$sort_url_base.'&sort='.$sortage_min.'">MIN AGE</a>';
?>
</h4>
</th>
<th nowrap>
<h4>
<?php
echo '<a href="'.$sort_url_base.'&sort='.$sortage.'">AGE</a>';
?>
</h4>
</th>
<th nowrap>
<h4>
<?php
echo '<a href="'.$sort_url_base.'&sort='.$sortage_max.'">MAX AGE</a>';
?>
</h4>
</th>
		 <th>
		 <h4>
		 <?php
		echo '<a href="'.$sort_url_base.'&sort='.$sortclass1.'">MATERIAL</a>';
		?>
		 </h4>
		 </th>
		 <th>
		 <h4>
		 <?php
		echo '<a href="'.$sort_url_base.'&sort='.$sortclass2.'">TYPE</a>';
		?>
		 </h4>
		 </th>
		 <th>
		 <h4>
		 <?php
		echo '<a href="'.$sort_url_base.'&sort='.$sortclass3.'">COMPOSITION</a>';
		?>
		 </h4>
		 </th>
		 <th nowrap>
		 <h4>
		 <?php
		echo '<a href="'.$sort_url_base.'&sort='.$sortclass4.'">ROCK&nbsp;NAME</a>';
		?>
		 </h4>
		 </th>
		<?
		if($searchquery->material=="3"){
		echo "<th><h4>";
		echo '<a href="'.$sort_url_base.'&sort='.$sortmineral.'">MINERAL</a>';
		echo "</h4></th>";
		}
		?>
<?PHP
//************************************************************************************************************
// start generating variable_unit combined column headers,rowtype=sample (refactored by PJ on Mar 5-11 2024)
//************************************************************************************************************
$dlnum=0;
$vu_column_list=array();
$only_null_arr=array("*");
$space_and_null_arr=array("","*");
foreach($showlist_array as $field_name) {
	foreach($get_variableunits as $g) {
		foreach($g as $key=>$value){
			if(strtoupper($key)==$field_name){
        			$vu_str=$value;
				break;
        		}
		}
	}
	$vu_arr = explode (",", $vu_str);
	if(count(array_diff($vu_arr,$only_null_arr))==0 or count(array_diff($vu_arr,$space_and_null_arr))==0){ 
		$showfield=$field_name;
		$displayfield=$display_list[$dlnum];
		$vu_column_list[]=$field_name;

		if($sort==$field_name){
			$sortstring="$field_name desc";
		}else{
			$sortstring="$field_name";
		}


		if($sort==$field_name."_meth"){
			$methsortstring="$field_name"."_meth"." desc";
		}else{
			$methsortstring="$field_name"."_meth";
		}

?>
		<th nowrap>
		<h4>
		<?php
		echo '<a href="'.$sort_url_base.'&sort='.$sortstring.'">'.$displayfield.'</a>';
		?>
		</h4>
		</th>
<?PHP
		 if ($showmethods) {
?>
			<th nowrap>
   			<h4>
<?php
   			echo '<a href="'.$sort_url_base.'&sort='.$methsortstring.'">'.$displayfield.' METHOD</a>';
?>
   			</h4>
   			</th>
<?PHP
		 } // if $showmethods
   	} // endif
   	else {
   		$new_vu_arr = array_diff($vu_arr,$only_null_arr);
   		for($i=0;$i<count($new_vu_arr);$i++){
  			$showfield=$field_name;
   			$displayfield=$display_list[$dlnum]."_".$new_vu_arr[$i];
                	$vu_column_list[]=$field_name."_".$new_vu_arr[$i];
   			if($sort==$field_name){
   				$sortstring="$field_name desc";
   			}else{
   				$sortstring="$field_name";
   			}
?>
			<th nowrap>
			<h4>
			<?php
			echo '<a href="'.$sort_url_base.'&sort='.$sortstring.'">'.$displayfield.'</a>';
			?>
			</h4>
			</th>
<?PHP
	                if ($showmethods) {
?>
                         	<th nowrap>
	                        <h4>
                                <?php
                                echo '<a href="'.$sort_url_base.'&sort='.$methsortstring.'">'.$displayfield.' METHOD</a>';
                                ?>
                                </h4>
                                </th>
<?PHP
                         } // if $showmethods
	} //endelse
} //endfor
$dlnum++;
} // foreach $showlist_array
//************************************************************************************************************
// end generating variable_unit combined column headers,rowtype=sample
//************************************************************************************************************
?>
</tr>
<?PHP
$row=1;
foreach($getsample AS $g) { 
	if ($row == 1) {
		$bgcolor="#FFFFFF";
		$row=2;
	} else {
		$bgcolor="#FFF5F5";
		$row=1;
	}
	if ($g->url > '') {

		//make accomodations for petdb mess here
		if($g->source == 'petdb'){
			//$urlstring='<a href="http://isotope.ldeo.columbia.edu:7001/petdbWeb/search/sample_info.jsp?singlenum='.$g->samplenumber.'" target="_blank">DETAILS</a>';
			$urlstring='<a href="http://www.petdb.org/petdbWeb/search/sample_info.jsp?singlenum='.$g->sample_num.'" target="_blank">DETAILS</a>';
		}elseif($g->source == 'navdat'){
			$urlstring = '<a href="http://www.navdat.org/NavdatPortal/GetSample.cfm?sample_num='.$g->samplenumber.'" target="_blank">DETAILS</a>'; 
		}else{
			$urlstring = '<a href="'.$g->url.'" target="_blank">DETAILS</a>'; 
		}

		// old here: $urlstring = "<A HREF='".$g->url."' target='_blank'>DETAILS</A>";
	} else {
		$urlstring=' ';
	}
	echo "
	<tr valign='top' style=\"background-color:$bgcolor;\">
	<TD nowrap>$g->sample_id </td>
	<TD nowrap>$g->igsn </td>
	<TD>".strtoupper($g->source)." </TD>
	<TD >$urlstring</TD>
	<TD>";

	if($g->lepr_num!=""){
	?>				
	<!--
	<a href="lepr_slider.php?lepr_num=<?=$g->lepr_num?>" target="_blank">LEPR</a>
	-->
	<a href="lepr_slider.php?material_pkey=<?=$g->material_pkey?>" target="_blank">LEPR</a>
	<?
	}else{
		echo "&nbsp;";
	}

	echo "</TD>
	<TD>";
	?>
		<!--<a href=\"ecindividualmap.php?pkey=".$g->sample_pkey."\" data-fancybox data-caption=\"EarthChem Sample Map\">MAP</a>-->
		<a data-fancybox data-type="iframe" data-src="ecindividualmap.php?pkey=<?=$g->sample_pkey?>" href="javascript:;">MAP</a>
	<?	
	echo "
	</TD>
	";

	echo "<TD>";
	if($g->lepr_num!=""){
	?>				
	<a href="ecneighbor.php?material_pkey=<?=$g->material_pkey?>" target="_blank">SIMILAR</a>
	<?
	}else{
		echo "&nbsp;";
	}
	echo "</TD>";

		// GoogleScholar code
		// Process the authors a bit for GoogleScholar advanced search link url, displayed below. Replace quotes, semicolons, commas, colons, +
		$googlescholarauthor = $g->authorlist;
		$googlescholarauthor = trim($googlescholarauthor);
		$googlescholarauthor = str_replace('"','',$googlescholarauthor); 
		$googlescholarauthor = str_replace(";"," ",$googlescholarauthor); 
		$googlescholarauthor = str_replace(","," ",$googlescholarauthor); 
		$googlescholarauthor = str_replace("-"," ",$googlescholarauthor); 
		$googlescholarauthor = str_replace(":"," ",$googlescholarauthor); 
		$googlescholarauthor = str_replace("+"," ",$googlescholarauthor); 
		// Get just the first author name, a last name. This is because EarthChem authors in the view often have initials concatenated with lastnames, and Google Scholar cannot find them
		$n=strpos($googlescholarauthor," ");
		if ($n>0) {
		$googlescholarauthor = trim(substr($googlescholarauthor, 0, $n));
		}
		// Pocess the titles a bit for GoogleScholar advanced search link url, displayed below --->
		/*
		$googlescholartitle = $g->title; 
		$googlescholartitle = str_replace('"','',$googlescholartitle); 
		$googlescholartitle = str_replace(";"," ",$googlescholartitle); 
		$googlescholartitle = str_replace(","," ",$googlescholartitle); 
		$googlescholartitle = str_replace("-"," ",$googlescholartitle); 
		$googlescholartitle = str_replace(":"," ",$googlescholartitle); 
		$googlescholartitle = str_replace("+"," ",$googlescholartitle); 
		$googlescholartitle = str_replace("title"," ",$googlescholartitle);
		$googlescholartitle = str_replace(" "," ",$googlescholartitle);
		$googlescholartitle = trim($googlescholartitle);
		if (strlen($googlescholartitle) > 0) { //Get the first 26 letters, then round up to nearest whole word 
		$n = strpos(substr($googlescholartitle,26),' ') + 26;
		$googlescholartitle = substr($googlescholartitle,0,$n);
		$googlescholartitle = str_replace(" ","+",$googlescholartitle); // Replace spaces with + for the google scholar url
		*/
		$googlescholartitle = $g->title; 
		$googlescholartitle = str_replace('"','',$googlescholartitle); 
		$googlescholartitle = str_replace(";"," ",$googlescholartitle); 
		$googlescholartitle = str_replace(","," ",$googlescholartitle); 
		$googlescholartitle = str_replace("-"," ",$googlescholartitle); 
		$googlescholartitle = str_replace(":"," ",$googlescholartitle); 
		$googlescholartitle = str_replace("+"," ",$googlescholartitle); 
		$googlescholartitle = str_replace("title"," ",$googlescholartitle);
		$googlescholartitle = str_replace(" "," ",$googlescholartitle);
		$googlescholartitle = trim($googlescholartitle);
		if (strlen($googlescholartitle) > 0) { //Get the first 26 letters, then round up to nearest whole word 
		$n = strpos(substr($googlescholartitle,45),' ') + 45;
		$googlescholartitle = substr($googlescholartitle,0,$n);
		$googlescholartitle = str_replace(" ","+",$googlescholartitle); // Replace spaces with + for the google scholar url

		} // if
		$googlescholaryear = $g->pubyear; 
		//<!--- if we got any parameters to pass to GoogleScholar, post a link --->
		if ($googlescholartitle > '' && $googlescholarauthor > '' && $googlescholaryear > '') {
		/*
		echo "<td>
		<A target='_blank' href='http://scholar.google.com/scholar?as_q=&num=10&btnG=Search+Scholar&as_epq=$googlescholartitle&as_sauthor=$googlescholarauthor&as_publication=&as_ylo$googlescholaryear&as_yhi$googlescholaryear&as_allsubj=all&hl=en&lr='>Reference</A>
		</td>";
		*/
		//echo "<td><A target='_blank' href='http://scholar.google.com/scholar?hl=en&q=$googlescholartitle+"."$googlescholarauthor"."&btnG=&as_sdt=1%2C5&as_sdtp='>Reference</A></td>";
		}else{
			//echo "<td>&nbsp;</td>";
		} // if



	
		if($g->doi!=""){
			echo "<td><a href=\"http://dx.doi.org/".$g->doi."\" target=\"_blank\">".$g->doi."</a></td>";
		}else{
			echo "<td>&nbsp;</td>";
		}

	?>
		<td style="white-space:nowrap" >
		 <?php
			if (strlen($g->title) > 24) {
			/*onMouseOver="return escape('<?=fixstring($g->title) ?>')"*/
			?>
		 <div onMouseOver="return escape('<?=fixstring($g->title) ?>')">
		 <?=substr(fixstring($g->title), 0, 25) ?>
		 ... </div>
		 <?php
		} else {
			echo $g->title;
		} // if
		?>
		 </td>
		 <td style="white-space:nowrap" >
		 <?php
		if (strlen($g->journal) > 24) {
		?>
		 <div onMouseOver="return escape('<?=fixstring($g->journal) ?>')">
		 <?=substr(fixstring($g->journal), 0, 25) ?>
		 ... </div>
		 <?php
		} else {
		echo fixstring($g->journal);
		} // if
		?>
		 </td>
		 <td style="white-space:nowrap">
		 <?php					
		if (strlen(strtoupper($g->authorlist)) > 24) {
		?>
		 <div onMouseOver="return escape('<?=strtoupper(fixstring($g->authorlist)) ?>')">
		 <?=substr(strtoupper(fixstring($g->authorlist)),0,25) ?>
		 ... </div>
		 <?php
		} else {
		echo strtoupper(fixstring($g->authorlist));
		} // if
		?>
		 </td>

		 <td nowrap>
		 <?
		 if($g->expeditionurl!=""){
		 ?>
		 <a href="<?=$g->expeditionurl?>" target="_blank"><?=$g->expeditionid?></a>
		 <?
		 }else{
		 ?>
		 <?=$g->expeditionid ?>
		 <?
		 }
		 ?>
		 </td>

<?
	echo "
	<TD >$g->latitude</TD>
	<TD >$g->longitude</TD>
	<TD >$g->locprecision</TD>
	<TD >$g->age_min</TD>
	<TD >$g->age</TD>
	<TD >$g->age_max</TD>
	";
?>
		 <td nowrap>
		 <?=strtoupper($g->class1) ?>
		 </td>
		 <td>
		 <?=strtoupper($g->class2) ?>
		 </td>
		 <td>
		 <?=strtoupper($g->class3) ?>
		 </td>
		 <td>
		 <?=strtoupper($g->class4) ?>
		 </td>
		 
		<?
		if($searchquery->material=="3"){
		echo "<td>";
		echo strtoupper($g->mineralname);
		echo "</td>";
		}
		?>

<?
//************************************************************************************************************************
// start filling the value for the specific variable_unit combined column,rowtype=sample refactored by PJ on Mar 5-11 2024
//************************************************************************************************************************
        for($j=0;$j<count($vu_column_list);$j++){
        	$ismatch = false;
        	echo "<TD nowrap>";
		foreach($showlist_array as $currentfield) { 
			$currentfield=strtolower($currentfield);
			//echo "<TD nowrap>";
               		$currentunit = $currentfield."_unit";
                	//print_r($vu_column_list[$j]);
                	//print_r($currentfield."_".$g->$currentunit);
                	if(strtolower($vu_column_list[$j]) == $currentfield."_".strtolower($g->$currentunit)){
				$thisval=$g->$currentfield;			
				if($thisval < 0){
					$thisval=$thisval*-1;
					$thisval="< ".$thisval;
				}
		
				echo $thisval;
		                echo "</TD>";
				if ($showmethods) {
					$thefieldname=$currentfield."_meth"; 
					$x=str_replace('%',' &#037;',$g->$thefieldname); // a % in this field blows up this bit of code, substitute 
					echo "<td>$x </td>";
				}
        	        	$ismatch=true;
                		break; 
               		}
		} // foreach showlist_array
        	if(!$ismatch){
                        if ($showmethods) {
                                $thefieldname=$currentfield."_meth";
                                $x=str_replace('%',' &#037;',$g->$thefieldname); // a % in this field blows up this bit of code, substitute
                                echo "<td>$x </td>";
                        }
        		echo "</TD>";
		}
        }
//************************************************************************************************************************
// end filling the value for the specific variable_unit combined column,rowtype=sample
//************************************************************************************************************************
	echo "</TR>";
} // foreach get_sample
echo "</TABLE>";
echo $pagelinks;
	} // end if method row options
	} // if count($getsample) > 0
?>
<div id="debug" style="display:none;">
<?
echo nl2br($sql_getsample);


?>


<br><br>

<?
foreach($allfields_array as $ar){
	echo "\"$ar\","; 
}
?>


</div>
<?
//&height=125&width=250
?>

<?
include "includes/ks_footer.html";
?>
<script type="text/javascript"> 
<!-- showwhere() is needed by wz_tooltip.js -->
function showwhere()
{ 
var obj_batch = document.getElementById('wherestate');
if (obj_batch.style.display == "none")
obj_batch.style.display = "block";
else
obj_batch.style.display = "none";
}
</script>
<!-- Contains function that shows full author, title, etc. on mouseover: -->
<script type="text/javascript" src="wz_tooltip.js"></script>
<?

} // if dispmode == 'html' 
//Note: We do not log citations for html output - only for text and xls output.
//******************* this is the end of the html portion ********************** 
// end HTML OUTPUT














// TEXT OUTPUT
if ($dispmode == "text") { //******************* this is the start of the text portion ********************** 

	if (isset($_GET['totalcount'])) { 
		$totalcount=$_GET['totalcount'];
	} else { 
		if ($rowtype == "method") {
			//$sql_totalcount = " SELECT COUNT(*) as totalcount FROM ( $advstring ) foo "; 
			$sql_totalcount = $advcount; 
		} else { // rowtype == 'sample' 
		
		
		
			$sql_totalcount = $samplecount;

		} // if, else 
		//debug: echo "<p>sql_totalcount is <br>$sql_totalcount ";
	
		//echo " $sql_totalcount <br><br><br>";
	
		//exit();
	
	
		//echo nl2br($sql_totalcount);
		$totalcount_query=$db->get_row($sql_totalcount) or die("could not execute totalcount query in advancedoutputc.php");
		$totalcount=$totalcount_query->totalcount;
	} // if, else 
	//echo "<p>totalcount is $totalcount";

	// If there no records (samples) were returned from our search, show a message and inelegantly exit. 
	if ($totalcount < 1) {
		include "includes/ks_head.html";
		echo "<h2>Sorry, no records found.</h2>";
	?>
	<div id="debug" style="display:none;">
	<?
	echo nl2br($sql_totalcount);
	?>
	</div>
	<?
	//echo "<!-- query";
	//echo nl2br($sql_totalcount);
	//echo "query -->";
		include "includes/ks_footer.html";
		exit; // Eileen change this inelegant logic. For now, it's more readable for me to do it this way. 
	}

	if ($rowtype == "method") {
		$sql_getsample .= $advstring; 
	} else { // rowtype = 'sample' 
		$sql_getsample .= $samplechemstring;  
	} // if rowtype 
	
	
	// Retrieve the samples in the result set, to display in the text file 
	$getsample=$db->get_results($sql_getsample); 
	// Log the download, so that a scheduled php procedures can log the citations for the samples
	
	if($rowtype=="method" || $rowtype=="sample"){
		include "log_download.php"; // Only for text or xls output, and rowtype = method 
	}
	
	$mycontent="";

	// Make a new text file, and open it. 
	$randnum=rand(0,99999);
	//$filename="temp/earthchemOutput".$randnum.".txt"; //"D:\webware\Apache\Apache2\htdocs\navdatdb\temp/earthchemOutput".$randnum.".txt";
	$filename="earthchem_download_".$randnum.".txt";
	//$fh = fopen($filename, 'w') or die("can't open file"); 
	$newline="";
	//$newline .= "\n "; $mycontent.=$newline;//fwrite($fh,$newline); //<CFFILE ACTION="write" FILE="#filename#" OUTPUT="" ADDNEWLINE="no">
	if ($rowtype == "method") {
		$newline = "SAMPLE ID\tIGSN\tSOURCE\tREFERENCE\tCRUISE ID\tLATITUDE\tLONGITUDE\tLOC PREC\tMIN AGE\tAGE\tMAX AGE\tMETHOD\tMATERIAL\tTYPE\tCOMPOSITION\tROCK NAME\tMINERAL";
                //*********************************************************************************************************
                // start generating variable_unit combined column headers, rowtype=method (refactored by PJ on Mar 8 2024) 
                //********************************************************************************************************
                $vu_column_list=array();
                $only_null_arr=array("*");
                $space_and_null_arr=array("","*");
                foreach($showlist_array as $field_name) { 
                        foreach($get_variableunits as $g) {
                                foreach($g as $key=>$value){
                                        if(strtoupper($key)==$field_name){
                                                $vu_str=$value;
                                                break;  
                                        }       
                                }       
                        }       
                        $vu_arr = explode (",", $vu_str);
                        if(count(array_diff($vu_arr,$only_null_arr))==0 or count(array_diff($vu_arr,$space_and_null_arr))==0){
                                $showfield=$field_name;
                                $vu_column_list[]=$field_name;
                                $newline .= "\t"; $newline .= $showfield;
                        }       
                        else{   
                                $new_vu_arr = array_diff($vu_arr,$only_null_arr);
                                for($i=0;$i<count($new_vu_arr);$i++){
                                        $showfield=$field_name."_".$new_vu_arr[$i];
                                        $vu_column_list[]=$field_name."_".$new_vu_arr[$i];
                                        $newline .= "\t"; $newline .= $showfield;
                                }       
                        }       
                } // foreach $showlist_array
                //***********************************************************
                // end gengeration variable_unit combined column headers 
                //***********************************************************  
		$newline .= "\n"; $mycontent.=$newline; //fwrite($fh,$newline); //<CFFILE ACTION="append" FILE="#filename#" OUTPUT="#newline#" ADDNEWLINE="yes">
		foreach($getsample as $g) {
			$newline ="$g->sample_id\t$g->igsn\t".strtoupper($g->source)."\t".strtoupper($g->authorlist).", $g->pubyear\t$g->expeditionid\t$g->latitude\t$g->longitude\t$g->locprecision\t$g->age_min\t$g->age\t$g->age_max\t$g->method\t".strtoupper($g->class1)."\t".strtoupper($g->class2)."\t".strtoupper($g->class3)."\t".strtoupper($g->class4)."\t".strtoupper($g->mineralname);
                        //*************************************************************************************************************************
                        // start filling the value for the specific variable_unit combined column, rowtype=method (refactored by PJ on Mar 8 2024)
                        //*************************************************************************************************************************
                        $countcols=count($vu_column_list);
                        for($j=0;$j<$countcols;$j++){
                                $ismatch = false;
                                foreach ($showlist_array as $currentfield) {
                                        $currentfield=strtolower($currentfield);
                                        $currentunit = $currentfield."_unit";
                                        if(strtolower($vu_column_list[$j]) == $currentfield."_".strtolower($g->$currentunit)){
                                                $thisval=$g->$currentfield;

                                                if ($thisval!="") {
                                                        if($thisval<0){
                                                                $thisval=$thisval*-1;
                                                                $thisval="< ".$thisval;
                                                        }
                                                }
                                                $newline.="\t"; $newline .= $thisval;
                                                $ismatch=true;
                                                break;
                                        }

                               } // foreach showlist_array
                                if(!$ismatch){
                                        $newline .= "\t";
                                }
                        } // for vu_column_list
                        //***********************************************************************
                        // end filling the value for the specific variable_unit combined column
                        //***********************************************************************
			$newline .= "\n"; $mycontent.=$newline; //fwrite($fh,$newline); //<CFFILE ACTION="append" FILE="#filename#" OUTPUT="#newline#" ADDNEWLINE="yes">
		} // foreach $getsample
	} else { // if rowtype = method, else - Below section is sample level text, rowtype = 'sample' 
		$newline = "SAMPLE ID\tIGSN\tSOURCE\tREFERENCE\tCRUISE ID\tLATITUDE\tLONGITUDE\tLOC PREC\tMIN AGE\tAGE\tMAX AGE\tMATERIAL\tTYPE\tCOMPOSITION\tROCK NAME\tMINERAL"; 
   		//*********************************************************************************************************
   		// start generating variable_unit combined column headers, rowtype=sample (refactored by PJ on Mar 7 2024)
   		//********************************************************************************************************
                $vu_column_list=array();
                $only_null_arr=array("*");
                $space_and_null_arr=array("","*");
		foreach($showlist_array as $field_name) { 
			foreach($get_variableunits as $g) {
				foreach($g as $key=>$value){
					if(strtoupper($key)==$field_name){
						$vu_str=$value;
						break;
					}
				}
			}
   			$vu_arr = explode (",", $vu_str);
   			if(count(array_diff($vu_arr,$only_null_arr))==0 or count(array_diff($vu_arr,$space_and_null_arr))==0){
   				$showfield=$field_name;
   				$vu_column_list[]=$field_name;
				$newline .= "\t"; $newline .= $showfield;
                        	if ($showmethods) {
                                	$newline = "$newline \t$field_name METH ";
                        	}
			}
 			else{
				$new_vu_arr = array_diff($vu_arr,$only_null_arr);
   				for($i=0;$i<count($new_vu_arr);$i++){
   					$showfield=$field_name."_".$new_vu_arr[$i];
   					$vu_column_list[]=$field_name."_".$new_vu_arr[$i];
					$newline .= "\t"; $newline .= $showfield;
                        		if ($showmethods) {
                                		$newline = "$newline \t$field_name METH ";
                        		}
				}
			}
		} // foreach $showlist_array
   		//***********************************************************
   		// end gengeration variable_unit combined column headers
   		//***********************************************************		
		$newline .= "\n"; $mycontent.=$newline; //fwrite($fh,$newline); 
		foreach($getsample as $g) {
			$newline="$g->sample_id\t$g->igsn\t".strtoupper($g->source)."\t".strtoupper($g->authorlist).", $g->pubyear\t$g->expeditionid\t$g->latitude\t$g->longitude\t$g->locprecision\t$g->age_min\t$g->age\t$g->age_max\t$g->class1\t$g->class2\t$g->class3\t$g->class4\t".strtoupper($g->mineralname); 
			

                        //*************************************************************************************************************************
                        // start filling the value for the specific variable_unit combined column, rowtype=sample (refactored by PJ on Mar 7 2024)
                        //*************************************************************************************************************************
                        $countcols=count($vu_column_list);
                        for($j=0;$j<$countcols;$j++){
                                $ismatch = false;
                                foreach ($showlist_array as $currentfield) {
                                        $currentfield=strtolower($currentfield);
                                        $currentunit = $currentfield."_unit";
                                        if(strtolower($vu_column_list[$j]) == $currentfield."_".strtolower($g->$currentunit)){
                                                $thisval=$g->$currentfield;

                                                if ($thisval!="") {
                                                        if($thisval<0){
                                                                $thisval=$thisval*-1;
                                                                $thisval="< ".$thisval;
                                                        }
                                                }
                                                $newline.="\t"; $newline .= $thisval;
                                                if ($showmethods) {
                                                        $fldname=$currentfield."_meth";
                                                        if (isset($g->$fldname)) {
								$newline .= "\t"; $newline .= $g->$fldname;
                                                        } else {
                                                                $newline .= "\t";
                                                        }
                                                }
                                                $ismatch=true;
                                                break;
                                        }

                                } // foreach showlist_array
                                if(!$ismatch){
                                	$newline .= "\t";
					if($showmethods) {
						$newline .= "\t";
                                        }
	                        }
                        } // for vu_column_list
                        //***********************************************************************
                        // end filling the value for the specific variable_unit combined column
                        //***********************************************************************			
			$newline .= "\n"; $mycontent.=$newline; //fwrite($fh,$newline); 
		} // foreach getsample
	}
    // Send Header
    //header("Pragma: public");
    //header("Expires: 0");
    //header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    //header("Content-Type: application/force-download");
    //header("Content-Type: application/octet-stream");
    //header("Content-Type: application/download");
    header("Content-type: text/plain");
	//header("Cache-Control: no-store, no-cache");
    header("Content-Disposition: attachment;filename=$filename "); 
    //header("Content-Transfer-Encoding: binary ");
    print $mycontent;	
	//echo "<p><a href='$filename' target='_blank'>$filename</a> ";
	//exit();
} // end TEXT output
?>



