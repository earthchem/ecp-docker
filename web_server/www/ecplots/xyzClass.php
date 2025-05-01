<?php
// Used by the xyz plotter, xyz.php. Used for both Navdat and EarthChem. Used to create a list of chemicals and other numeric values to offer 
// in the dropdown select boxes for x, y, z and the ratios, in the plotter. Lists of field names and parts of the sql for the query that retrieves the 
// data points for the plot are saved in hidden form fields, retrieved by javascript, passed back to the plotter in the form of url variables. 
// This is so that we only have to do the most time-consuming parts once. It makes the repopulation of the x, y, z select blxes faster. 

// functions may require global variables: $db (the database connection) and/or $obj 

/*/ Include a database driver in the script that includes this one. 
if ($earthchem) {
	require ("includes/earthchem_db.php"); // database driver, and db connect for EarthChem
}
elseif ($navdat) {
	require ("includes/navdat_db.php"); // database driver, and db connect for EarthChem
} else {
	echo "<p>No website has been identified. Please start a new search and try again.<p>"; exit;
}*/

class xyzClass { var $showwork = false; // for dev & test, to print out a message each time a function is called
	var $pkey; // primary key for the search_query table 
	var $referring_website; // Navdat or EarthChem
	var $navdat; // true if we came from Navdat
	var $navdatsqlstatement; // the search query sql if we came from Navdat
	var $earthchem; // true if we came from EarthChem
	var $alias; // alias for the main table in the sql - 'norm.' for Navdat because many fieldnames in view_data_output are ambiguous in the query, '' for EarthChem because all the fieldnames are unique in EarthChem. See more explanation below.
	var $srcwhere; // a bit of sql that limits the sources to whatever the user checked from 'navdat' 'petdb' 'usgs' 'georoc' (may be blank if they chose all 4)
	var $x; // x, y, z fieldnames correspond to $x_column in the main script
	var $y;
	var $z;
	var $xaxis; // x, y, z fieldnames and any ratio, e.g. 1/SiO2
	var $yaxis;
	var $zaxis;
	var $xtitle; // xaxis, but use the display names for the chemicals - for the plot titles 
	var $ytitle;
	var $ztitle;
	var $x_ratio; // an optional fieldname to use as a ratio with x
	var $y_ratio;
	var $z_ratio;
	var $x_ratio_part; // the ratio fieldname is a numerator or denominator 
	var $y_ratio_part;
	var $z_ratio_part;	
	var $master_fieldnames_list; // comma-separated list of all the fieldnames (columns in sample_chemistry table) which the user's query returns that have at least some data
	var $master_displaynames_list; // ditto but using the display_name from data_field_selection table 
	var $master_fieldnames_array; // an array version of the above
	var $select_xyz; // select the fieldnames & ratios the user chose for x, y, z 
	var $where; // where part of the user's search query 
	var $where_xyz; // sql to limit the search by the values of x, y, z & ratios 
	var $from; // from part of the user's search query
	var $plot_sql; // the sql that retrieves the x,y coordinates for the datapoints that we will plot 


function get_numeric_fieldnames_array() { // Return all the numeric fields in table sample_chemistry for EarthChem, or view_data_ouput for Navdat
if ($this->showwork) {echo "<br><font size=1 color=blue>get_numeric_fieldnames_array</font><br>";echo time();ob_flush();flush();}
// Get all column names from sample_chemistry with datatype of 'real' (should be checked against item_translation table)
global $db; 


if ($this->earthchem) {
$sql="SELECT
a.attname as column_name, 
pg_catalog.format_type(a.atttypid, a.atttypmod) as datatype 
FROM
pg_catalog.pg_attribute a
WHERE
a.attnum > 0
AND NOT a.attisdropped
AND a.attrelid = (
SELECT c.oid
FROM pg_catalog.pg_class c
LEFT JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace
WHERE c.relname ~ '^(sample_chemistry)$'
AND pg_catalog.pg_table_is_visible(c.oid)
)";
// The above works, but... Try a simpler one
$sql="select distinct column_name  from information_schema.columns where table_name='sample_chemistry'  and (data_type = 'real')"; // All the fields we want from sample_chemistry are 'real' 
} elseif ($this->navdat) {
$sql="select distinct column_name from information_schema.columns where table_name='view_data_output'  and (data_type = 'real' or data_type = 'integer')";	
}  
$rows=$db->get_results($sql);
$numeric_fieldnames_array=array(); 
foreach($rows as $row) {
	$numeric_fieldnames_array[]=$row->column_name; 
} // foreach 


/*
if($this->earthchem){
	$numeric_fieldnames_array[]="norm.ag";
	$numeric_fieldnames_array[]="norm.age";
	$numeric_fieldnames_array[]="norm.age_max";
	$numeric_fieldnames_array[]="norm.age_min";
	$numeric_fieldnames_array[]="norm.al";
	$numeric_fieldnames_array[]="norm.al2o3";
	$numeric_fieldnames_array[]="norm.ar36_ar39";
	$numeric_fieldnames_array[]="norm.ar37_ar39";
	$numeric_fieldnames_array[]="norm.ar37_ar40";
	$numeric_fieldnames_array[]="norm.ar38_ar36";
	$numeric_fieldnames_array[]="norm.ar39_ar36";
	$numeric_fieldnames_array[]="norm.ar40_ar36";
	$numeric_fieldnames_array[]="norm.ar40_ar39";
	$numeric_fieldnames_array[]="norm.ar40_k40";
	$numeric_fieldnames_array[]="norm.arsenic";
	$numeric_fieldnames_array[]="norm.au";
	$numeric_fieldnames_array[]="norm.b";
	$numeric_fieldnames_array[]="norm.ba";
	$numeric_fieldnames_array[]="norm.be";
	$numeric_fieldnames_array[]="norm.be10_be";
	$numeric_fieldnames_array[]="norm.be10_be9";
	$numeric_fieldnames_array[]="norm.bi";
	$numeric_fieldnames_array[]="norm.c";
	$numeric_fieldnames_array[]="norm.ca";
	$numeric_fieldnames_array[]="norm.cao";
	$numeric_fieldnames_array[]="norm.cd";
	$numeric_fieldnames_array[]="norm.ce";
	$numeric_fieldnames_array[]="norm.cl";
	$numeric_fieldnames_array[]="norm.cl36_cl";
	$numeric_fieldnames_array[]="norm.co";
	$numeric_fieldnames_array[]="norm.co2";
	$numeric_fieldnames_array[]="norm.cr";
	$numeric_fieldnames_array[]="norm.cr2o3";
	$numeric_fieldnames_array[]="norm.cs";
	$numeric_fieldnames_array[]="norm.cu";
	$numeric_fieldnames_array[]="norm.d18o";
	$numeric_fieldnames_array[]="norm.dy";
	$numeric_fieldnames_array[]="norm.epsilon_nd";
	$numeric_fieldnames_array[]="norm.er";
	$numeric_fieldnames_array[]="norm.eu";
	$numeric_fieldnames_array[]="norm.f";
	$numeric_fieldnames_array[]="norm.fe";
	$numeric_fieldnames_array[]="norm.fe2o3";
	$numeric_fieldnames_array[]="norm.fe2o3t";
	$numeric_fieldnames_array[]="norm.feo";
	$numeric_fieldnames_array[]="norm.feot";
	$numeric_fieldnames_array[]="norm.ga";
	$numeric_fieldnames_array[]="norm.gd";
	$numeric_fieldnames_array[]="norm.ger";
	$numeric_fieldnames_array[]="norm.h";
	$numeric_fieldnames_array[]="norm.h2o";
	$numeric_fieldnames_array[]="norm.h2o_minus";
	$numeric_fieldnames_array[]="norm.h2o_plus";
	$numeric_fieldnames_array[]="norm.h2o_total";
	$numeric_fieldnames_array[]="norm.he3_he4";
	$numeric_fieldnames_array[]="norm.he4_he3";
	$numeric_fieldnames_array[]="norm.he4_ne20";
	$numeric_fieldnames_array[]="norm.hf";
	$numeric_fieldnames_array[]="norm.hf176_hf177";
	$numeric_fieldnames_array[]="norm.hg";
	$numeric_fieldnames_array[]="norm.ho";
	$numeric_fieldnames_array[]="norm.i";
	$numeric_fieldnames_array[]="norm.ind";
	$numeric_fieldnames_array[]="norm.ir";
	$numeric_fieldnames_array[]="norm.k";
	$numeric_fieldnames_array[]="norm.k2o";
	$numeric_fieldnames_array[]="norm.k40_ar36";
	$numeric_fieldnames_array[]="norm.kr78_kr84";
	$numeric_fieldnames_array[]="norm.kr80_kr84";
	$numeric_fieldnames_array[]="norm.kr82_kr84";
	$numeric_fieldnames_array[]="norm.kr83_kr84";
	$numeric_fieldnames_array[]="norm.kr86_kr84";
	$numeric_fieldnames_array[]="norm.la";
	$numeric_fieldnames_array[]="norm.li";
	$numeric_fieldnames_array[]="norm.loi";
	$numeric_fieldnames_array[]="norm.lu";
	$numeric_fieldnames_array[]="norm.lu176_hf177";
	$numeric_fieldnames_array[]="norm.mg";
	$numeric_fieldnames_array[]="norm.mgo";
	$numeric_fieldnames_array[]="norm.mn";
	$numeric_fieldnames_array[]="norm.mno";
	$numeric_fieldnames_array[]="norm.mo";
	$numeric_fieldnames_array[]="norm.na2o";
	$numeric_fieldnames_array[]="norm.nb";
	$numeric_fieldnames_array[]="norm.nd";
	$numeric_fieldnames_array[]="norm.nd143_nd144";
	$numeric_fieldnames_array[]="norm.ne20_ne22";
	$numeric_fieldnames_array[]="norm.ne21_ne20";
	$numeric_fieldnames_array[]="norm.ne21_ne22";
	$numeric_fieldnames_array[]="norm.ne22_ne20";
	$numeric_fieldnames_array[]="norm.ni";
	$numeric_fieldnames_array[]="norm.nio";
	$numeric_fieldnames_array[]="norm.os";
	$numeric_fieldnames_array[]="norm.os184_os188";
	$numeric_fieldnames_array[]="norm.os186_os188";
	$numeric_fieldnames_array[]="norm.os187_os186";
	$numeric_fieldnames_array[]="norm.os187_os188";
	$numeric_fieldnames_array[]="norm.p";
	$numeric_fieldnames_array[]="norm.p2o5";
	$numeric_fieldnames_array[]="norm.pb";
}else{
	$numeric_fieldnames_array[]="norm.ag";
	$numeric_fieldnames_array[]="norm.age";
	$numeric_fieldnames_array[]="norm.age_max";
	$numeric_fieldnames_array[]="norm.age_min";
	$numeric_fieldnames_array[]="norm.al";
	$numeric_fieldnames_array[]="norm.al2o3";
	$numeric_fieldnames_array[]="norm.albite";
	$numeric_fieldnames_array[]="norm.anorthite";
	$numeric_fieldnames_array[]="norm.apatite";
	$numeric_fieldnames_array[]="norm.ar36";
	$numeric_fieldnames_array[]="norm.ar36_ar39";
	$numeric_fieldnames_array[]="norm.ar37_ar39";
	$numeric_fieldnames_array[]="norm.ar38_ar36";
	$numeric_fieldnames_array[]="norm.ar40";
	$numeric_fieldnames_array[]="norm.ar40_ar36";
	$numeric_fieldnames_array[]="norm.ar40_ar39";
	$numeric_fieldnames_array[]="norm.ar40_k40";
	$numeric_fieldnames_array[]="norm.arsenic";
	$numeric_fieldnames_array[]="norm.au";
	$numeric_fieldnames_array[]="norm.b";
	$numeric_fieldnames_array[]="norm.ba";
	$numeric_fieldnames_array[]="norm.ba_nb";
	$numeric_fieldnames_array[]="norm.bao";
	$numeric_fieldnames_array[]="norm.be";
	$numeric_fieldnames_array[]="norm.be10";
	$numeric_fieldnames_array[]="norm.be10_be9";
	$numeric_fieldnames_array[]="norm.bi";
	$numeric_fieldnames_array[]="norm.biotite";
	$numeric_fieldnames_array[]="norm.bp";
	$numeric_fieldnames_array[]="norm.br";
	$numeric_fieldnames_array[]="norm.c";
	$numeric_fieldnames_array[]="norm.ca";
	$numeric_fieldnames_array[]="norm.calculated_age";
	$numeric_fieldnames_array[]="norm.calculated_age_max";
	$numeric_fieldnames_array[]="norm.calculated_age_min";
	$numeric_fieldnames_array[]="norm.cao";
	$numeric_fieldnames_array[]="norm.cd";
	$numeric_fieldnames_array[]="norm.ce";
	$numeric_fieldnames_array[]="norm.cl";
	$numeric_fieldnames_array[]="norm.cl2";
	$numeric_fieldnames_array[]="norm.co";
	$numeric_fieldnames_array[]="norm.co2";
	$numeric_fieldnames_array[]="norm.cpx";
	$numeric_fieldnames_array[]="norm.cr";
	$numeric_fieldnames_array[]="norm.cr2o3";
	$numeric_fieldnames_array[]="norm.cs";
	$numeric_fieldnames_array[]="norm.cu";
	$numeric_fieldnames_array[]="norm.d11b";
	$numeric_fieldnames_array[]="norm.d180";
	$numeric_fieldnames_array[]="norm.d18o";
	$numeric_fieldnames_array[]="norm.d18o_smow";
	$numeric_fieldnames_array[]="norm.delta_d";
	$numeric_fieldnames_array[]="norm.dy";
	$numeric_fieldnames_array[]="norm.en";
	$numeric_fieldnames_array[]="norm.e_nd";
	$numeric_fieldnames_array[]="norm.epidote";
	$numeric_fieldnames_array[]="norm.epsilon_nd";
	$numeric_fieldnames_array[]="norm.er";
	$numeric_fieldnames_array[]="norm.e_sr";
	$numeric_fieldnames_array[]="norm.eu";
	$numeric_fieldnames_array[]="norm.f";
	$numeric_fieldnames_array[]="norm.f2";
	$numeric_fieldnames_array[]="norm.fa";
	$numeric_fieldnames_array[]="norm.fe";
	$numeric_fieldnames_array[]="norm.fe2o3";
	$numeric_fieldnames_array[]="norm.fe2o3t";
	$numeric_fieldnames_array[]="norm.fe2o3t_calculated";
	$numeric_fieldnames_array[]="norm.feo";
	$numeric_fieldnames_array[]="norm.feot";
	$numeric_fieldnames_array[]="norm.ferrosilite";
	$numeric_fieldnames_array[]="norm.fes2";
	$numeric_fieldnames_array[]="norm.fet";
	$numeric_fieldnames_array[]="norm.feto3";
	$numeric_fieldnames_array[]="norm.fo";
	$numeric_fieldnames_array[]="norm.fosterite";
	$numeric_fieldnames_array[]="norm.fs";
	$numeric_fieldnames_array[]="norm.ga";
	$numeric_fieldnames_array[]="norm.garnet";
	$numeric_fieldnames_array[]="norm.gd";
	$numeric_fieldnames_array[]="norm.ge";
	$numeric_fieldnames_array[]="norm.h2o";
	$numeric_fieldnames_array[]="norm.h2o_minus";
	$numeric_fieldnames_array[]="norm.h2o_plus";
	$numeric_fieldnames_array[]="norm.h2o_total";
	$numeric_fieldnames_array[]="norm.he3";
	$numeric_fieldnames_array[]="norm.he3_he4";
	$numeric_fieldnames_array[]="norm.he4";
	$numeric_fieldnames_array[]="norm.he4_he3";
	$numeric_fieldnames_array[]="norm.he4_ne20";
	$numeric_fieldnames_array[]="norm.hf";
	$numeric_fieldnames_array[]="norm.hf176_hf177";
	$numeric_fieldnames_array[]="norm.hg";
	$numeric_fieldnames_array[]="norm.ho";
	$numeric_fieldnames_array[]="norm.hornblende";
	$numeric_fieldnames_array[]="norm.hy";
	$numeric_fieldnames_array[]="norm.i";
	$numeric_fieldnames_array[]="norm.ir";
	$numeric_fieldnames_array[]="norm.k";
	$numeric_fieldnames_array[]="norm.k2o";
	$numeric_fieldnames_array[]="norm.k_feldspar";
	$numeric_fieldnames_array[]="norm.kr78_kr84";
	$numeric_fieldnames_array[]="norm.kr80_kr84";
	$numeric_fieldnames_array[]="norm.kr82_kr84";
	$numeric_fieldnames_array[]="norm.kr83_kr84";
	$numeric_fieldnames_array[]="norm.kr86_kr84";
	$numeric_fieldnames_array[]="norm.la";
	$numeric_fieldnames_array[]="norm.latitude";
	$numeric_fieldnames_array[]="norm.li";
	$numeric_fieldnames_array[]="norm.loi";
	$numeric_fieldnames_array[]="norm.longitude";
	$numeric_fieldnames_array[]="norm.lu";
	$numeric_fieldnames_array[]="norm.lu176_hf177";
	$numeric_fieldnames_array[]="norm.mg";
	$numeric_fieldnames_array[]="norm.mg_number";
	$numeric_fieldnames_array[]="norm.mgo";
	$numeric_fieldnames_array[]="norm.mn";
	$numeric_fieldnames_array[]="norm.mno";
	$numeric_fieldnames_array[]="norm.mo";
	$numeric_fieldnames_array[]="norm.mode_accessory_minerals";
	$numeric_fieldnames_array[]="norm.mode_acmite";
	$numeric_fieldnames_array[]="norm.mode_actinolite";
	$numeric_fieldnames_array[]="norm.mode_adularia";
	$numeric_fieldnames_array[]="norm.mode_aenigmatite";
	$numeric_fieldnames_array[]="norm.mode_albite";
	$numeric_fieldnames_array[]="norm.mode_allanite";
	$numeric_fieldnames_array[]="norm.mode_alt";
	$numeric_fieldnames_array[]="norm.mode_alteration";
	$numeric_fieldnames_array[]="norm.mode_amph";
	$numeric_fieldnames_array[]="norm.mode_amphibole";
	$numeric_fieldnames_array[]="norm.mode_anal";
	$numeric_fieldnames_array[]="norm.mode_analcime";
	$numeric_fieldnames_array[]="norm.mode_analcite";
	$numeric_fieldnames_array[]="norm.mode_anorthite";
	$numeric_fieldnames_array[]="norm.mode_anorthoclase";
	$numeric_fieldnames_array[]="norm.mode_anthopyllite";
	$numeric_fieldnames_array[]="norm.mode_apatite";
	$numeric_fieldnames_array[]="norm.mode_arfvedsonite";
	$numeric_fieldnames_array[]="norm.mode_baddelyite";
	$numeric_fieldnames_array[]="norm.mode_barite";
	$numeric_fieldnames_array[]="norm.mode_biotite";
	$numeric_fieldnames_array[]="norm.mode_calc";
	$numeric_fieldnames_array[]="norm.mode_calcite";
	$numeric_fieldnames_array[]="norm.mode_carb";
	$numeric_fieldnames_array[]="norm.mode_carbonate";
	$numeric_fieldnames_array[]="norm.mode_cats";
	$numeric_fieldnames_array[]="norm.mode_ccr";
	$numeric_fieldnames_array[]="norm.mode_chalcedony";
	$numeric_fieldnames_array[]="norm.mode_chalcopyrite";
	$numeric_fieldnames_array[]="norm.mode_chevkinite";
	$numeric_fieldnames_array[]="norm.mode_chlorite";
	$numeric_fieldnames_array[]="norm.mode_chloritoid";
	$numeric_fieldnames_array[]="norm.mode_chrom";
	$numeric_fieldnames_array[]="norm.mode_chromite";
	$numeric_fieldnames_array[]="norm.mode_clay_mineral";
	$numeric_fieldnames_array[]="norm.mode_clinoptilolite";
	$numeric_fieldnames_array[]="norm.mode_clinopyroxene";
	$numeric_fieldnames_array[]="norm.mode_cly";
	$numeric_fieldnames_array[]="norm.mode_corundum";
	$numeric_fieldnames_array[]="norm.mode_cpx";
	$numeric_fieldnames_array[]="norm.mode_cris";
	$numeric_fieldnames_array[]="norm.mode_cristobalite";
	$numeric_fieldnames_array[]="norm.mode_cti";
	$numeric_fieldnames_array[]="norm.mode_dolomite";
	$numeric_fieldnames_array[]="norm.mode_enstatite";
	$numeric_fieldnames_array[]="norm.mode_ep";
	$numeric_fieldnames_array[]="norm.mode_epidote";
	$numeric_fieldnames_array[]="norm.mode_fa";
	$numeric_fieldnames_array[]="norm.mode_fayalite";
	$numeric_fieldnames_array[]="norm.mode_feldspar";
	$numeric_fieldnames_array[]="norm.mode_felsic_minerals";
	$numeric_fieldnames_array[]="norm.mode_ferrosilite";
	$numeric_fieldnames_array[]="norm.mode_fe_ti_oxide_minerals";
	$numeric_fieldnames_array[]="norm.mode_fluorite";
	$numeric_fieldnames_array[]="norm.mode_forsterite";
	$numeric_fieldnames_array[]="norm.mode_fosterite";
	$numeric_fieldnames_array[]="norm.mode_fsp";
	$numeric_fieldnames_array[]="norm.mode_galena";
	$numeric_fieldnames_array[]="norm.mode_garnet";
	$numeric_fieldnames_array[]="norm.mode_gl";
	$numeric_fieldnames_array[]="norm.mode_glass";
	$numeric_fieldnames_array[]="norm.mode_gm";
	$numeric_fieldnames_array[]="norm.mode_gonn";
	$numeric_fieldnames_array[]="norm.mode_gonnardite";
	$numeric_fieldnames_array[]="norm.mode_groundmass";
	$numeric_fieldnames_array[]="norm.mode_groundmass_minerals";
	$numeric_fieldnames_array[]="norm.mode_hornblende";
	$numeric_fieldnames_array[]="norm.mode_ilm";
	$numeric_fieldnames_array[]="norm.mode_ilmenite";
	$numeric_fieldnames_array[]="norm.mode_iron_oxide";
	$numeric_fieldnames_array[]="norm.mode_jadeite";
	$numeric_fieldnames_array[]="norm.mode_jarosite";
	$numeric_fieldnames_array[]="norm.mode_jd";
	$numeric_fieldnames_array[]="norm.mode_kaolinite";
	$numeric_fieldnames_array[]="norm.mode_k_feldspar";
	$numeric_fieldnames_array[]="norm.mode_lithic";
	$numeric_fieldnames_array[]="norm.mode_mafic_minerals";
	$numeric_fieldnames_array[]="norm.mode_mafics";
	$numeric_fieldnames_array[]="norm.mode_mafics_undivided";
	$numeric_fieldnames_array[]="norm.mode_magnetite";
	$numeric_fieldnames_array[]="norm.mode_matrix";
	$numeric_fieldnames_array[]="norm.mode_metamorphic_minerals";
	$numeric_fieldnames_array[]="norm.mode_mica";
	$numeric_fieldnames_array[]="norm.mode_microcline";
	$numeric_fieldnames_array[]="norm.mode_mon";
	$numeric_fieldnames_array[]="norm.mode_monazite";
	$numeric_fieldnames_array[]="norm.mode_muscovite";
	$numeric_fieldnames_array[]="norm.mode_nac";
	$numeric_fieldnames_array[]="norm.mode_nacrite";
	$numeric_fieldnames_array[]="norm.mode_na_rich_mafic_minerals";
	$numeric_fieldnames_array[]="norm.mode_natr";
	$numeric_fieldnames_array[]="norm.mode_natrolite";
	$numeric_fieldnames_array[]="norm.mode_olivine";
	$numeric_fieldnames_array[]="norm.mode_opal";
	$numeric_fieldnames_array[]="norm.mode_opaques";
	$numeric_fieldnames_array[]="norm.mode_opq";
	$numeric_fieldnames_array[]="norm.mode_orthoclase";
	$numeric_fieldnames_array[]="norm.mode_orthopyroxene";
	$numeric_fieldnames_array[]="norm.mode_other";
	$numeric_fieldnames_array[]="norm.mode_oxides";
	$numeric_fieldnames_array[]="norm.mode_phil";
	$numeric_fieldnames_array[]="norm.mode_phillipsite";
	$numeric_fieldnames_array[]="norm.mode_phlog";
	$numeric_fieldnames_array[]="norm.mode_phlogopite";
	$numeric_fieldnames_array[]="norm.mode_pigeonite";
	$numeric_fieldnames_array[]="norm.mode_plag";
	$numeric_fieldnames_array[]="norm.mode_plagioclase";
	$numeric_fieldnames_array[]="norm.mode_preh";
	$numeric_fieldnames_array[]="norm.mode_prehnite";
	$numeric_fieldnames_array[]="norm.mode_psilomelane";
	$numeric_fieldnames_array[]="norm.mode_pum";
	$numeric_fieldnames_array[]="norm.mode_pumice";
	$numeric_fieldnames_array[]="norm.mode_pumpellyite";
	$numeric_fieldnames_array[]="norm.mode_py";
	$numeric_fieldnames_array[]="norm.mode_pyrite";
	$numeric_fieldnames_array[]="norm.mode_pyroclasts";
	$numeric_fieldnames_array[]="norm.mode_pyroxene";
	$numeric_fieldnames_array[]="norm.mode_pyrrhotite";
	$numeric_fieldnames_array[]="norm.mode_quartz";
	$numeric_fieldnames_array[]="norm.mode_rhodochrosite";
	$numeric_fieldnames_array[]="norm.mode_riebeckite";
	$numeric_fieldnames_array[]="norm.mode_rutile";
	$numeric_fieldnames_array[]="norm.mode_san";
	$numeric_fieldnames_array[]="norm.mode_sanidine";
	$numeric_fieldnames_array[]="norm.mode_sap";
	$numeric_fieldnames_array[]="norm.mode_scap";
	$numeric_fieldnames_array[]="norm.mode_scapolite";
	$numeric_fieldnames_array[]="norm.mode_sec_minerals_textures";
	$numeric_fieldnames_array[]="norm.mode_serp";
	$numeric_fieldnames_array[]="norm.mode_serpentine";
	$numeric_fieldnames_array[]="norm.mode_slfa";
	$numeric_fieldnames_array[]="norm.mode_slfi";
	$numeric_fieldnames_array[]="norm.mode_smec";
	$numeric_fieldnames_array[]="norm.mode_smectite";
	$numeric_fieldnames_array[]="norm.mode_soda";
	$numeric_fieldnames_array[]="norm.mode_sodalite";
	$numeric_fieldnames_array[]="norm.mode_sp";
	$numeric_fieldnames_array[]="norm.mode_sphalerite";
	$numeric_fieldnames_array[]="norm.mode_sphene";
	$numeric_fieldnames_array[]="norm.mode_spinel";
	$numeric_fieldnames_array[]="norm.mode_sulfides";
	$numeric_fieldnames_array[]="norm.mode_sulphates";
	$numeric_fieldnames_array[]="norm.mode_sulphides";
	$numeric_fieldnames_array[]="norm.mode_topaz";
	$numeric_fieldnames_array[]="norm.mode_tourmaline";
	$numeric_fieldnames_array[]="norm.mode_tri";
	$numeric_fieldnames_array[]="norm.mode_tridymite";
	$numeric_fieldnames_array[]="norm.mode_ulvospinel";
	$numeric_fieldnames_array[]="norm.mode_unknown";
	$numeric_fieldnames_array[]="norm.mode_uranothorianite";
	$numeric_fieldnames_array[]="norm.mode_vein";
	$numeric_fieldnames_array[]="norm.mode_veins";
	$numeric_fieldnames_array[]="norm.mode_ves";
	$numeric_fieldnames_array[]="norm.mode_vesicles";
	$numeric_fieldnames_array[]="norm.mode_whole_rock";
	$numeric_fieldnames_array[]="norm.mode_wr";
	$numeric_fieldnames_array[]="norm.mode_wustite";
	$numeric_fieldnames_array[]="norm.mode_zeol";
	$numeric_fieldnames_array[]="norm.mode_zeolite";
	$numeric_fieldnames_array[]="norm.mode_zirc";
	$numeric_fieldnames_array[]="norm.mode_zircon";
	$numeric_fieldnames_array[]="norm.mt";
	$numeric_fieldnames_array[]="norm.muscovite";
	$numeric_fieldnames_array[]="norm.na";
	$numeric_fieldnames_array[]="norm.na2o";
	$numeric_fieldnames_array[]="norm.nb";
	$numeric_fieldnames_array[]="norm.nbn_kn";
	$numeric_fieldnames_array[]="norm.nd";
	$numeric_fieldnames_array[]="norm.nd143_nd144";
	$numeric_fieldnames_array[]="norm.nd143_nd144_initial";
	$numeric_fieldnames_array[]="norm.ne20_ne22";
	$numeric_fieldnames_array[]="norm.ne21_ne20";
	$numeric_fieldnames_array[]="norm.ne21_ne22";
	$numeric_fieldnames_array[]="norm.ne22";
	$numeric_fieldnames_array[]="norm.ne22_ne20";
	$numeric_fieldnames_array[]="norm.ni";
	$numeric_fieldnames_array[]="norm.nio";
	$numeric_fieldnames_array[]="norm.o18o";
	$numeric_fieldnames_array[]="norm.olivine";
	$numeric_fieldnames_array[]="norm.orthoclase";
	$numeric_fieldnames_array[]="norm.orthopyroxene";
	$numeric_fieldnames_array[]="norm.os";
	$numeric_fieldnames_array[]="norm.os184_os188";
	$numeric_fieldnames_array[]="norm.os186_os188";
	$numeric_fieldnames_array[]="norm.os187_os186";
	$numeric_fieldnames_array[]="norm.os187_os188";
	$numeric_fieldnames_array[]="norm.p";
	$numeric_fieldnames_array[]="norm.p2o5";
	$numeric_fieldnames_array[]="norm.pb";
	$numeric_fieldnames_array[]="norm.pb204_pb206";
	$numeric_fieldnames_array[]="norm.pb206_pb204";
	$numeric_fieldnames_array[]="norm.pb206_pb207";
	$numeric_fieldnames_array[]="norm.pb206_pb208";
	$numeric_fieldnames_array[]="norm.pb206_u238";
	$numeric_fieldnames_array[]="norm.pb207_pb204";
	$numeric_fieldnames_array[]="norm.pb207_pb206";
	$numeric_fieldnames_array[]="norm.pb207_u235";
	$numeric_fieldnames_array[]="norm.pb208_pb204";
	$numeric_fieldnames_array[]="norm.pb208_pb204_stdev";
	$numeric_fieldnames_array[]="norm.pb208_pb206";
	$numeric_fieldnames_array[]="norm.pb210_act";
	$numeric_fieldnames_array[]="norm.pb210_u238";
	$numeric_fieldnames_array[]="norm.pd";
	$numeric_fieldnames_array[]="norm.plagioclase";
	$numeric_fieldnames_array[]="norm.po210_act";
	$numeric_fieldnames_array[]="norm.po210_rn222";
	$numeric_fieldnames_array[]="norm.po210_th230";
	$numeric_fieldnames_array[]="norm.po210_th230_act";
	$numeric_fieldnames_array[]="norm.pr";
	$numeric_fieldnames_array[]="norm.pt";
	$numeric_fieldnames_array[]="norm.quartz";
	$numeric_fieldnames_array[]="norm.ra226";
	$numeric_fieldnames_array[]="norm.ra226_act";
	$numeric_fieldnames_array[]="norm.ra226_th230";
	$numeric_fieldnames_array[]="norm.ra226_th230_act";
	$numeric_fieldnames_array[]="norm.ra228_act";
	$numeric_fieldnames_array[]="norm.rb";
	$numeric_fieldnames_array[]="norm.rb87_sr86";
	$numeric_fieldnames_array[]="norm.rb_sr";
	$numeric_fieldnames_array[]="norm.re";
	$numeric_fieldnames_array[]="norm.re187_os186";
	$numeric_fieldnames_array[]="norm.re187_os188";
	$numeric_fieldnames_array[]="norm.rh";
	$numeric_fieldnames_array[]="norm.rn222_th230";
	$numeric_fieldnames_array[]="norm.s";
	$numeric_fieldnames_array[]="norm.sample_num";
	$numeric_fieldnames_array[]="norm.sb";
	$numeric_fieldnames_array[]="norm.sc";
	$numeric_fieldnames_array[]="norm.se";
	$numeric_fieldnames_array[]="norm.sio2";
	$numeric_fieldnames_array[]="norm.sm";
	$numeric_fieldnames_array[]="norm.sm147_nd144";
	$numeric_fieldnames_array[]="norm.sm_nd";
	$numeric_fieldnames_array[]="norm.sn";
	$numeric_fieldnames_array[]="norm.so2";
	$numeric_fieldnames_array[]="norm.so3";
	$numeric_fieldnames_array[]="norm.sphene";
	$numeric_fieldnames_array[]="norm.sr";
	$numeric_fieldnames_array[]="norm.sr87_sr86";
	$numeric_fieldnames_array[]="norm.sr87_sr86_initial";
	$numeric_fieldnames_array[]="norm.sro";
	$numeric_fieldnames_array[]="norm.sr_pn";
	$numeric_fieldnames_array[]="norm.suma_100";
	$numeric_fieldnames_array[]="norm.ta";
	$numeric_fieldnames_array[]="norm.tb";
	$numeric_fieldnames_array[]="norm.te";
	$numeric_fieldnames_array[]="norm.th";
	$numeric_fieldnames_array[]="norm.th228_act";
	$numeric_fieldnames_array[]="norm.th230";
	$numeric_fieldnames_array[]="norm.th230_act";
	$numeric_fieldnames_array[]="norm.th230_th232";
	$numeric_fieldnames_array[]="norm.th230_th232_act";
	$numeric_fieldnames_array[]="norm.th230_u238";
	$numeric_fieldnames_array[]="norm.th230_u238_act";
	$numeric_fieldnames_array[]="norm.th232_act";
	$numeric_fieldnames_array[]="norm.th232_pb204";
	$numeric_fieldnames_array[]="norm.th232_th230";
	$numeric_fieldnames_array[]="norm.th232_u238";
	$numeric_fieldnames_array[]="norm.th238_th232";
	$numeric_fieldnames_array[]="norm.ti";
	$numeric_fieldnames_array[]="norm.tio2";
	$numeric_fieldnames_array[]="norm.tl";
	$numeric_fieldnames_array[]="norm.tm";
	$numeric_fieldnames_array[]="norm.total";
	$numeric_fieldnames_array[]="norm.u";
	$numeric_fieldnames_array[]="norm.u234_u238";
	$numeric_fieldnames_array[]="norm.u234_u238_act";
	$numeric_fieldnames_array[]="norm.u238_act";
	$numeric_fieldnames_array[]="norm.u238_pb204";
	$numeric_fieldnames_array[]="norm.u238_pb206";
	$numeric_fieldnames_array[]="norm.u238_th230";
	$numeric_fieldnames_array[]="norm.u238_th230_act";
	$numeric_fieldnames_array[]="norm.u238_th232";
	$numeric_fieldnames_array[]="norm.u238_th232_act";
	$numeric_fieldnames_array[]="norm.ulvospinel";
	$numeric_fieldnames_array[]="norm.v";
	$numeric_fieldnames_array[]="norm.v2o3";
	$numeric_fieldnames_array[]="norm.w";
	$numeric_fieldnames_array[]="norm.wo";
	$numeric_fieldnames_array[]="norm.xe124_xe130";
	$numeric_fieldnames_array[]="norm.xe124_xe132";
	$numeric_fieldnames_array[]="norm.xe126_xe130";
	$numeric_fieldnames_array[]="norm.xe126_xe132";
	$numeric_fieldnames_array[]="norm.xe128_xe130";
	$numeric_fieldnames_array[]="norm.xe128_xe132";
	$numeric_fieldnames_array[]="norm.xe129_xe130";
	$numeric_fieldnames_array[]="norm.xe129_xe132";
	$numeric_fieldnames_array[]="norm.xe130_xe132";
	$numeric_fieldnames_array[]="norm.xe131_xe130";
	$numeric_fieldnames_array[]="norm.xe131_xe132";
	$numeric_fieldnames_array[]="norm.xe132_xe130";
	$numeric_fieldnames_array[]="norm.xe134_xe130";
	$numeric_fieldnames_array[]="norm.xe134_xe132";
	$numeric_fieldnames_array[]="norm.xe136_xe130";
	$numeric_fieldnames_array[]="norm.xe136_xe132";
	$numeric_fieldnames_array[]="norm.y";
	$numeric_fieldnames_array[]="norm.yb";
	$numeric_fieldnames_array[]="norm.yb_stdev";
	$numeric_fieldnames_array[]="norm.zn";
	$numeric_fieldnames_array[]="norm.zno";
	$numeric_fieldnames_array[]="norm.zr";
	$numeric_fieldnames_array[]="norm.zro2";
}
*/


//print_r($numeric_fieldnames_array); ob_flush();flush();
//$column_list = implode(", ", $numeric_fieldnames_array); echo " column_list length is "; echo strlen($column_list); echo "<p />column_list:<br />"; print_r($column_list);
return $numeric_fieldnames_array; // Array of all field names of datatype 'real' in sample_chemistry table
} // end function 


 


function navdat_make_sql_from_where() { // Make the "from" and "where" parts of the sql statement that retrieves data to plot
if ($this->showwork) {echo "<br><font size=1 color=green>navdat_make_sql_from_where</font><br>";echo time();ob_flush();flush();}
global $db;
global $obj;
$navdatsqlstatement=$this->navdatsqlstatement; 
$pos2 = stripos($navdatsqlstatement, 'WHERE '); 
$pos1 = stripos($navdatsqlstatement, 'FROM ')+4; // add a spaces, to avoid picking up a function with the word 'from' in it 
$n = $pos2-$pos1;
$pos4=$pos2+5;
$from = substr($navdatsqlstatement, $pos1, $n );
$where = substr($navdatsqlstatement, $pos4); 
$this->from=$from;
$this->where=$where; 
} 












function make_sql_from_where() { // For EarthChem only. Retrieve the search query parameters, and build the sql for the search 
if ($this->showwork) {echo "<br><font size=1 color=green>make_sql_from_where</font><br>";echo time();ob_flush();flush();}

// This file builds a basic query for the plots. It returns an array of the sql parts, e.g. FROM, WHERE, list of columns to SELECT
global $db;
global $obj;

// Get the record for the user's search query 
$getsearch = $db->get_row('select * from search_query where pkey = '.$this->pkey); //'.$this->pkey);

$where = ""; // build the string that follows WHERE in the sql statement
$from = ""; // build the string that follow FROM in the sql statement 

// Author 
$theauthor = "";
if ($getsearch->author > "") {
	//$theauthor .= " AND contains(ca.authors, '%".$getsearch->author."%',4)>0 ";
$theauthor .= " AND schem.sample_pkey in (select cr.sample_pkey FROM citation_relate cr, citation_authors ca WHERE cr.sample_pkey=schem.sample_pkey and ca.citation_pkey=cr.citation_pkey and ca.vector @@ to_tsquery('".$getsearch->author."') ) ";
	$simplestuff .= $theauthor;
}
$where .= $theauthor;

// Title 
$thetitle = "";
if ( $getsearch->title != "") { 
		$thetitle .= " AND schem.sample_pkey IN (SELECT cr.sample_pkey FROM citation_relate cr, citation c where c.citation_pkey = cr.citation_pkey AND c.titlevector @@ to_tsquery('".$getsearch->title."') ) ";
}
$where .= $thetitle; 

// Journal 
$thejournal = '';
if ($getsearch->journal != '') {
	$thejournal .= " AND schem.sample_pkey IN (select cr.sample_pkey FROM citation_relate cr, citation c WHERE c.citation_pkey = cr.citation_pkey AND c.journalvector @@ to_tsquery('".$getsearch->journal."') ) ";
}
$where .= $thejournal;

// Keyword 
$thekeyword = ''; 
if ($getsearch->advkeyword1 > '') {
	$thekeyword .= " AND schem.sample_pkey IN (SELECT cr.sample_pkey FROM citation_relate cr, sample samp WHERE samp.gendescvector @@ to_tsquery('".$getsearch->advkeyword1."') ) ";
}
$where .= $thekeyword;

// Publication Year 
$thepubyear = '';
if ( $getsearch->yearmin != '' && $getsearch->yearmax != '' ) {
	$thepubyear .= " AND schem.sample_pkey IN (SELECT cr.sample_pkey FROM citation_relate cr, citation c WHERE c.citation_pkey = cr.citation_pkey AND c.pubyear BETWEEN ".$getsearch->yearmin." AND ".$getsearch->yearmax.")";
}
$where .= $thepubyear;

// Polygon  (location polygon set with interactive map or by explicitly inputting coordinates)
//$thepolygon = '';
//if ($getsearch->polygon > '') {
//	$thepolygon .= " AND samp.sample_pkey in (SELECT sample_pkey FROM polygon_samples WHERE search_query_pkey = $pkey ) ";
//	$simplestuff .= $thepolygon;
//}

// Coordinates - Latitude, Longitude  
$thecoordinates = '';
if ($getsearch->polygon > '') {
	$coordlist=$getsearch->polygon;
	$coordarray=explode(";",$coordlist);
	foreach($coordarray as $currcoord){
		$mybox.=$currcoord.",";
	}
	$mybox.=$coordarray[0];
	$thecoordinates = " and ST_Contains(GeomFromText('Polygon(($mybox))'),crd.mypoint)";
} elseif ($getsearch->longitudeeast != '') {
	$top=$getsearch->latitudenorth;
	$bottom=$getsearch->latitudesouth;
	$right=$getsearch->longitudeeast;
	$left=$getsearch->longitudewest;
	$mybox="$left $top,$right $top,$right $bottom,$left $bottom,$left $top";
	$thecoordinates = " and GeomFromText('Polygon(($mybox))') ~ crd.mypoint";
}
$where .= $thecoordinates; 

// Chemistry 
$thechemistry = '';
if ($getsearch->chemistry != '') { 
	$delim=''; 
	$chemistry_array=split(',',$getsearch->chemistry); 
	foreach($chemistry_array as $currchem) { 
		$chemname=''; 
		if (strpos($currchem,':EXISTS') > 0) { 
			$chemname = substr($currchem,0,strpos($currchem,':EXISTS')); 
			$thechemistry .= " $delim ( $chemname IS NOT NULL ) "; 
			$delim = " AND ";
		} elseif (substr_count($currchem,':') > 1 ) { 
			$currchem_array=split(':',$currchem); 
			$i=strpos($chemname,':'); 
			$lowerbound=$currchem_array[0];
			$upperbound=$currchem_array[2];
			$chemname=$currchem_array[1];
			$thechemistry .= " $delim ( $chemname > $lowerbound AND $chemname < $upperbound ) ";
			$delim = " AND ";		
		} // if     
		$methstring=''; 
		$methdelim=''; 
		$chemmethods_array=split(',',$getsearch->chemmethods); 
		foreach($chemmethods_array as $currmeth) { 
			if (strpos($currmeth,'$chemname:') == 0 ) { 
				$loopflag=false; 
				$currmeth_array=split(':',$currmeth);
				foreach($currmeth_array as $mycurr) { 
					if ($loopflag) {      
						$currmethlist=explode(";",$mycurr);
						foreach($currmethlist as $methname){ //needed to add this loop here because code wasn't digging deep enough - JMA
							$methstring .= " $methdelim chem.method = '$methname' "; 
							$methdelim = " OR ";      
						}
					} // if     
					$loopflag=true;   
				} // foreach     
			} // if     
		} // foreach        
		if ($methstring > '') {      
			$thechemistry .= " AND ( $methstring ) ";     
		} // if     
		$delim = " OR ";          
	} // foreach chemistry_array    
	$thechemistry = " AND ( $thechemistry ) ";
} // if               
$where .= $thechemistry;             

// Rock Names 
$therocknames='';
$rocknames_array=array();
if ($getsearch->tasname != '') {
	$string= " UPPER(st.tasname) IN ( ";
	$delim="";  
	$tasname_array = split(',',$getsearch->tasname);
	foreach ($tasname_array as $currlev) { 
		$string .= $therocknames.$delim."'".$currlev."'";
		$delim=",";
	}  
	$string .= ' ) ';
	$rocknames_array[]=$string;
} //echo "<p>$getsearch->level1 $getsearch->level2 $getsearch->level3 $getsearch->level4 ";
if ( $getsearch->level1 != '') { // Fields in sampletype are named CLASS1 CLASS2 CLASS3 CLASS4 TASNAME. There is a discrepancy with uppercase/lowercase between search_query table and sampletype table, so force both to uppercase in this query
	$string = " st.class1 IN ( ";
	$delim="";
	$LEVEL1_array = split(',',$getsearch->level1);
	foreach ($LEVEL1_array as $currlev) { 
		$string = $string.$delim."'".strtolower($currlev)."'";
		$delim=",";
	}  
	$string .= ' ) ';
	$rocknames_array[]=$string;
}
if ( $getsearch->level2 != '') {
	$string = " st.class2 IN ( ";
	$delim="";
	$LEVEL2_array = split(',',$getsearch->level2);
	foreach ($LEVEL2_array as $currlev) { 
		$string = $string.$delim."'".strtolower($currlev)."'";
		$delim=",";
	} 
	$string .= ' ) ';
	$rocknames_array[]=$string; 
}
if ( $getsearch->level3 != '') {
	$string = " st.class3 in ( ";
	$delim="";
	$LEVEL3_array = split(',',$getsearch->level3);
	foreach ($LEVEL3_array as $currlev) { 
		$string = $string.$delim."'".strtolower($currlev)."'";
		$delim=",";
	}  
	$string .= ' ) ';
	$rocknames_array[]=$string;
}
if ( $getsearch->level4 != '') {
	$string = " st.class4 in ( ";
	$delim="";
	$LEVEL4_array = split(',',$getsearch->level4);
	foreach ($LEVEL4_array as $currlev) { 
		$string = $string.$delim."'".strtolower($currlev)."'";
		$delim=",";
	}  
	$string .= ' ) ';
	$rocknames_array[]=$string;
}   //echo "<p>at 299 rocknames_array ";print_r($rocknames_array);
if (sizeof($rocknames_array)>0) {
	$therocknames .= implode(" AND ",$rocknames_array);  
	$therocknames = " AND schem.sample_pkey IN (SELECT st.sample_pkey FROM sampletype st WHERE  $therocknames  ) ";
}





// Redo the rock name data because Jason reworked all this. December 2008. This is basically cut and pasted from queries.php. 

//LEPR values for simplestuff
if ($getsearch->lepr != '') { 
	$delim=''; 
	$lepr_array=split(';',$getsearch->lepr); 
	foreach($lepr_array as $currlepr) {  
		$leprname='';  
		if (strpos($currlepr,':checked') > 0) {  
			$leprname = substr($currlepr,0,strpos($currlepr,':checked'));  
			$leprstring .= " $delim ( $leprname > -99 ) ";  
			$delim = " OR ";
		} elseif (substr_count($currlepr,':') > 1 ) {  
			$currlepr_array=split(':',$currlepr);  
			$i=strpos($leprname,':'); 
			$lowerbound=$currlepr_array[0];
			$upperbound=$currlepr_array[2];
			$leprname=$currlepr_array[1];
			$leprstring .= " $delim ( $leprname > $lowerbound AND $leprname < $upperbound ) ";
			$delim = " OR ";
		} // if
		
	}// end foreach

	//$leprstring="and samp.sample_pkey in (select sample_pkey from lepr_norms where $leprstring)";
	$leprstring=" and ($leprstring)";
	$simplestuff .= $leprstring;
	$thelepr = $leprstring;
}

// Rock Names for simplestuff
$therocknames='';
if ($getsearch->tasname != '') {
	$therocknames .= " and upper(st.tasname) in ( ";
	$delim="";
	$tasname_array = split(',',$getsearch->tasname);
	foreach ($tasname_array as $currlev) {  
		$therocknames = $therocknames.$delim."'".$currlev."'";
		$delim=",";
	}  
	$therocknames .= ' ) ';
}
if ( $getsearch->level1 != '') { // Fields in sampletype are named CLASS1 CLASS2 CLASS3 CLASS4 TASNAME. There is a discrepancy with uppercase/lowercase between search_query table and sampletype table, so force both to uppercase in this query
	$therocknames .= " and st.class1 in ( ";
	$delim="";
	$LEVEL1_array = split(',',$getsearch->level1);
	foreach ($LEVEL1_array as $currlev) {  
		$therocknames = $therocknames.$delim."'".strtolower($currlev)."'";
		$delim=",";
	}  
	$therocknames .= ' ) ';
}
if ( $getsearch->level2 != '') {
	$therocknames .= " and st.class2 in ( ";
	$delim="";
	$LEVEL2_array = split(',',$getsearch->level2);
	foreach ($LEVEL2_array as $currlev) {  
		$therocknames = $therocknames.$delim."'".strtolower($currlev)."'";
		$delim=",";
	} 
	$therocknames .= ' ) '; 
}
if ( $getsearch->level3 != '') {
	$therocknames .= " and st.class3 in ( ";
	$delim="";
	$LEVEL3_array = split(',',$getsearch->level3);
	foreach ($LEVEL3_array as $currlev) {  
		$therocknames = $therocknames.$delim."'".strtolower($currlev)."'";
		$delim=",";
	}  
	$therocknames .= ' ) ';
}
if ( $getsearch->level4 != '') {
	$therocknames .= " and st.class4 in ( ";
	$delim="";
	$LEVEL4_array = split(',',$getsearch->level4);
	foreach ($LEVEL4_array as $currlev) { 
		$therocknames = $therocknames.$delim."'".strtolower($currlev)."'";
		$delim=",";
	} 
	$therocknames .= ' ) '; 
}


/*
if ( $getsearch->rockclass != '') {
	$therocknames .= " and st.mainclasses in ( ";
	$delim="";
	$rockclass_array = split(',',$getsearch->rockclass);
	foreach ($rockclass_array as $currlev) { 
		$therocknames = $therocknames.$delim."'".strtolower($currlev)."'";
		$delim=",";
	} 
	$therocknames .= ' ) '; 
}
*/


if ( $getsearch->rockclass != '') {
	$therocknames .= " and ( ";
	$delim="";
	$rockclass_array = split(',',$getsearch->rockclass);
	foreach ($rockclass_array as $currlev) { 
	
		$therocknames .= "(";
		
		$rockdelim="";
		$rockx="1";
		$classarray=split(' : ',$currlev);
		foreach($classarray as $currclass){
			
			$therocknames .= "$rockdelim st.class$rockx = '$currclass'";
			$rockdelim = " and ";
			$rockx++;
			
		}
		
		$delim=" or ";
		$therocknames .= ")";

	} 
	$therocknames .= ' ) '; 
}

if ( $getsearch->rockname != '') {
	$therocknames .= " and st.class4 in ( ";
	$delim="";
	$rockname_array = split(',',$getsearch->rockname);
	foreach ($rockname_array as $currlev) { 
		$therocknames = $therocknames.$delim."'".strtolower($currlev)."'";
		$delim=",";
	} 
	$therocknames .= ' ) '; 
}

if ($therocknames != "") {
	// Handle any leading "and"
	$therocknames = " AND schem.sample_pkey IN (SELECT st.sample_pkey FROM sampletypeb st WHERE 1=1  $therocknames  ) ";
}
// End redo the rock names data per Jason's recent changes 





$where .= $therocknames;
// end Rock Names 

// Material 
$thematerial = ""; //echo  "<h1>$getsearch->material</h1>";
if ($getsearch->material != "") {
//get normalized material here to use below:
	if (is_numeric($getsearch->material)){
		$mynormmaterial=$getsearch->material;
	} else {
		$mynormmaterial=1; // if the old database column's default was applied, because user didn't choose one, the old default is 'rock', we can safely use 1=rock
	} 
	// integers 1 through 4, correspond to items like 1=rock, mineral, inclusion, glass...
	$thematerial .= " AND schem.material_pkey in (SELECT material_pkey FROM material WHERE normtype = $mynormmaterial) ";
}
$where .= $thematerial;
// end Material 

// for latitude, longitude which come from a different table, not sample_chemistry
$where .= " AND loc.sample_pkey = schem.sample_pkey AND crd.location_pkey = loc.location_pkey ";

// Received as a form variable from the Results page
$srcwhere = $this->srcwhere;
if ($srcwhere>"") { $srcwhere = str_replace("AND ", "", $srcwhere ); // $srcwhere begins with " AND ( ";
	$srcwhere = " AND schem.sample_pkey in (SELECT sample_pkey FROM sample WHERE $srcwhere ) "; // srchwere is something like " AND (...) ";
	$where .= $srcwhere; //echo "<p>at 329 srcwhere $srcwhere ";
} 
// end sources 

$where = substr($where,4); // strip the leading AND
// the FROM part of the sql
$from .= " sample_chemistry schem, location loc, coords crd ";
$this->from = $from;
$this->where = $where; //echo "<p><font color=green>xyzClass 316: <br>from: $this->from <br>where: $this->where </font>";
$sql_parts_array=array('select'=>$select,'where'=>$where,'from'=>$from); //,'fieldnames_list'=>$fieldnames_list,'fieldnames_min_list'=>$fieldnames_min_list,'fieldnames_to_offer_list'=>$fieldnames_to_offer_list,'fieldnames_to_offer_array'=>$fieldnames_to_offer_array,'fieldnames_to_offer_count'=>$fieldnames_to_offer_count);
//echo "<p>at 485<br>select: $select<p>from: $from<p>where: $where<p>";
return $sql_parts_array; 
} // end function 








function make_sql_select_where_xyz() { // Make parts of sql that limit the search by the user's selections for x, y, z and ratios
if ($this->showwork) {echo "<br><font size=1 color=green>331 make_sql_select_where_xyz</font><br>";echo time();ob_flush();flush();}
/* There is a special problem with the select and where parts of the sql that use the user's selections for x, y, z. 
The Navdat navdatsqlstatement contains more than one instance of many fields, so we need an alias (norm) for view_data_output for ALL the fields.
But EarthChem uses xval and yval for latitude, longitude which come from the location table; all the other fields come from sample_chemistry alias schem .
The solution is to always preface the fieldname with the alias for Navdat only.  
*/  
$n=sizeof($this->master_fieldnames_array); //echo "<p>xyzClass: at 332 n = $n "; //print_r($this->master_fieldnames_array); ob_flush(); flush();
if ($n<1) {
	global $obj; //echo "<h3>making master_fieldnames_array</h3>"; ob_flush();flush();
	$master_fieldnames_array=$obj->make_master_fieldnames_array; // We should have made this before. We need it to look up the display_name for the chemicals
} else {
	$master_fieldnames_array=$this->master_fieldnames_array;
}
$master_fieldnames_array['1']='1'; // Need this for the titles, built below 
// Make strings of sql that limit by x, y, z, and the ratios if they exist, and prevent 0 in any denominator. Also build the titles (display version of x,y,z): xtitle, ytitle, ztitle
$axis_array=array(); //echo "<p>xyzClass: <font color=red>$this->x $this->x_ratio $this->x_ratio_part </font>";
if ($this->x != '') { //echo "<p>xyzClass: at 339 $this->x"; ob_flush();flush();
	$stuff = $this->x; // The item in the sql, e.g. sio2/co2 (use field_name, not display_name)
	$title=$master_fieldnames_array[$this->x]; // Look up the display_name from our master lookup array
	//$where .= " AND $this->alias$this->x IS NOT NULL "; //echo $title; echo  $where; ob_flush();flush();
	$where .= " AND $this->alias$this->x IS NOT NULL "; //echo $title; echo  $where; ob_flush();flush();
	if ($this->x_ratio != '') {
		$where .= " AND $this->alias$this->x_ratio IS NOT NULL ";
		if ($this->x_ratio_part == 'denominator') {
			$where .= " AND $this->alias$this->x_ratio != 0 ";
			$stuff = '('.$this->alias.$this->x.' / '.$this->alias.$this->x_ratio.')';
			$title = '('.$master_fieldnames_array[$this->x].' / '.$master_fieldnames_array[$this->x_ratio].')';
		} else {
			$where .= " AND $this->alias$this->x != 0 ";
			$stuff = '('.$this->alias.$this->x_ratio.' / '.$this->alias.$this->x.')';
			$title = '('.$master_fieldnames_array[$this->x_ratio].' / '.$master_fieldnames_array[$this->x].')';
		}
	}
	$axis_array['x']=$stuff;
	$this->xaxis=$stuff;
	$this->xtitle=$title; 
} 
if ($this->y != '') {
	$stuff = $this->y; // The item in the sql, e.g. sio2/co2 (use field_name, not display_name)
	$title=$master_fieldnames_array[$this->y]; // Look up the display_name from our master lookup array
	$where .= " AND $this->alias$this->y IS NOT NULL ";
	if ($this->y_ratio != '') {
		$where .= " AND $this->alias$this->y_ratio IS NOT NULL ";
		if ($this->y_ratio_part == 'denominator') {
			$where .= " AND $this->alias$this->y_ratio != 0 ";
			$stuff = '('.$this->alias.$this->y.' / '.$this->alias.$this->y_ratio.')';
			$title = '('.$master_fieldnames_array[$this->y].' / '.$master_fieldnames_array[$this->y_ratio].')';
		} else {
			$where .= " AND $this->alias$this->y != 0 ";
			$stuff = '('.$this->alias.$this->y_ratio.' / '.$this->alias.$this->y.')';
			$title = '('.$master_fieldnames_array[$this->y_ratio].' / '.$master_fieldnames_array[$this->y].')';
		}
	}
	$axis_array['y']=$stuff;
	$this->yaxis=$stuff;
	$this->ytitle=$title; 
}
if ($this->z != '') {
	$stuff = $this->z; // The item in the sql, e.g. sio2/co2 (use field_name, not displaz_name)
	$title=$master_fieldnames_array[$this->z]; // Look up the displaz_name from our master lookup array
	$where .= " AND $this->alias$this->z IS NOT NULL ";
	if ($this->z_ratio != '') {
		$where .= " AND $this->alias$this->z_ratio IS NOT NULL ";
		if ($this->z_ratio_part == 'denominator') {
			$where .= " AND $this->alias$this->z_ratio != 0 ";
			$stuff = '('.$this->alias.$this->z.' / '.$this->alias.$this->z_ratio.')';
			$title = '('.$master_fieldnames_array[$this->z].' / '.$master_fieldnames_array[$this->z_ratio].')';
		} else {
			$where .= " AND $this->alias$this->z != 0 ";
			$stuff = '('.$this->alias.$this->z_ratio.' / '.$this->alias.$this->z.')';
			$title = '('.$master_fieldnames_array[$this->z_ratio].' / '.$master_fieldnames_array[$this->z].')';
		}
	}
	$axis_array['z']=$stuff;
	$this->zaxis=$stuff;
	$this->ztitle=$title; 
}
$select_xyz=""; 
$select_xyz_min_max="";
foreach($axis_array as $key=>$val) {

	if($val=="latitude" or $val=="longitude"){
		$select_xyz .= ", $this->alias$val AS $key ";
	}else{
		$select_xyz .= ", $val AS $key ";
	}

	//$select_xyz .= ", $this->alias$val AS $key ";
	
	//**************************************************************************************************************
	//**************************************************************************************************************
	//**************************************************************************************************************
	//$select_xyz_min_max .= ", min($this->alias$val) as min_$key, max($this->alias$val) as max_$key ";
	if($val=="latitude" or $val=="longitude"){
		$select_xyz_min_max .= ", min($this->alias$val) as min_$key, max($this->alias$val) as max_$key ";
	}else{
		$select_xyz_min_max .= ", min($val) as min_$key, max($val) as max_$key ";
	}
} //echo "<p>Class 434: select_xyz_min_max $select_xyz_min_max select_xyz $select_xyz ";
$select_xyz=substr($select_xyz,1); // remove leading comma
$select_xyz_min_max=substr($select_xyz_min_max,1); 
$sql_array=array('select_xyz'=>$select_xyz, 'select_xyz_min_max'=>$select_xyz_min_max, 'where_xyz'=>$where ); 
$this->select_xyz=$select_xyz;
$this->select_xyz_min_max=$select_xyz_min_max;
$this->where_xyz=$where; 
return $sql_array;
} // end function


























function make_master_fieldnames_array() {
if ($this->showwork) {echo "<br><font color=blue size=1>make_master_fieldnames_array</font><br>";echo time();ob_flush();flush();}
// Returns an array of fieldnames for which the user's search query returns at least one record with a non-null value 
// If we have a list of fieldnames (hidden in a form field, retrieved by javascript and passed back as a url variable), start with that:
if (strlen($this->master_fieldnames_list)>1) { 
	$master_fieldnames_array=array();
	$myarray=explode(",",$this->master_fieldnames_list);
	foreach($myarray as $key=>$val) {
		$master_fieldnames_array[$val]=$val;
	}
	$this->master_fieldnames_array=$master_fieldnames_array;
	return $master_fieldnames_array;
}
// If not, start from scratch. We should only have to do this the first time div_xyz is written to the screen. 
global $db;
global $obj; 
if ($this->from=="" or $this->where =="") { 	
	if ($this->earthchem) {
		$myarray=$obj->make_sql_from_where(); // writes the $this variables for from and where
	} elseif ($this->navdat) {
		$myarray=$obj->navdat_make_sql_from_where();
	} 
}
$all_fieldnames_array=$obj->get_numeric_fieldnames_array(); //echo "<p>493 all_fieldnames_array<p>";print_r($all_fieldnames_array);ob_flush();flush();
$min_list = ""; 
foreach($all_fieldnames_array as $a) {
	//*******************************************************************************************************************
	//*******************************************************************************************************************
	//*******************************************************************************************************************

	$min_list .= ", min($this->alias$a) AS $a"; // alias is either norm on Navdat, or "" on EarthChem(alias for a table) 
	//$min_list .= ", min($a) AS $a"; // alias is either norm on Navdat, or "" on EarthChem(alias for a table) 
}
$min_list=substr($min_list,1);
$sql = "SELECT $min_list FROM $this->from WHERE $this->where "; //echo "<p>644<br>$sql<p>";ob_flush();flush(); 
$min_row = $db->get_row($sql);
$master_fieldnames_array=array(); //print_r($min_row);
$fieldlist = "";
foreach($min_row as $key=>$val) {
	if ($val != "") {
		$master_fieldnames_array[$key]=$key;
		$fieldlist .= ",'".$key."'";
	}
} // foreach
// Get the display names for all those field names and make an array of display_name=>field_name. We always show the user the display_name.
$fieldlist=substr($fieldlist,1);$fieldlist=strtolower($fieldlist); // field names in database are lowercase but in data_field_selection table they are uppercase
$sql = "SELECT lower(field_name) AS field_name, display_name FROM data_field_selection WHERE lower(field_name) in ($fieldlist) "; 
//echo  "<p>511 <br>$sql<p>";ob_flush();flush();
$rows=$db->get_results($sql) or die("could not execute near 511 in xyzClass.php"); //print_r($rows);
foreach($rows as $row) { 
	$field_name=$row->field_name;$display_name=$row->display_name;
	if (isset($master_fieldnames_array[$field_name])) {
		$master_fieldnames_array[$field_name]=$display_name;
	} // if
} // if
// Store the keys and values from master_fieldnames_array in 2 strings, to hide in hidden form fields, retrieve in javascript, and pass back to xyz-write_form_xyz.php from javascript as url variables in the http:// call 
$keys=array_keys($master_fieldnames_array); $master_fieldnames_list=implode(",",$keys); // list of field_name
$vals=array_values($master_fieldnames_array); $master_displaynames_list=implode(",",$vals); // list of display_name
$this->master_fieldnames_array=$master_fieldnames_array; 
$this->master_fieldnames_list=$master_fieldnames_list; // We hide the lists in hidden form fields to be retrieved by javascript and passed back to the scripts as $_GET variables in the url
$this->master_displaynames_list=$master_displaynames_list; // ...then we rebuild this array. We only need to build the array from scratch the first time.
return $master_fieldnames_array; // array of fieldnames for which the user's basic search query returned some non-null data
} // end function






function make_fieldnames_to_offer_array() { // Make an array of field names to populate the dropdown select boxes for x, y, z and the ratios
if ($this->showwork) {echo "<br><font size=1 color=blue>make_fieldnames_to_offer_array</font><br>";echo time();ob_flush();flush();}
// Get an array and a list of the fieldnames for which at least one sample has a non-null value 
global $obj;
global $db;
$temp_array=explode(",",$this->master_fieldnames_list);$temp_array2=explode(",",$this->master_displaynames_list); 
$n=sizeof($temp_array); 
$m=sizeof($temp_array2); 
if ($n<2 or $n!=$m) { 
	$foo=$obj->make_master_fieldnames_array(); 
	// We may have passed this array from the javascript that triggers xyz-write_form_xyz.php, and set the $this-> variable
} else {	
	$master_fieldnames_array=array();
	for ($i=0;$i<$n;$i++) { 
		$master_fieldnames_array[$temp_array[$i]]=$temp_array2[$i];
	}
	$this->master_fieldnames_array=$master_fieldnames_array;
} 
unset($temp_array);unset($temp_array2);
asort($this->master_fieldnames_array);
if ($this->x != "" or $this->y != "" or $this->z != "") {  
	if ($this->from=="" or $this->where =="") {  
		$foo=$obj->make_sql_from_where(); // writes the $this-> vars
	}
	if ($this->where_xyz=="") {  
		$foo=$obj->make_sql_select_where_xyz(); unset($foo); // writes the $this-> var
	} 
	$min_list="";
	foreach($this->master_fieldnames_array as $key=>$val) {  
		$min_list .= ", min($this->alias$key) AS $key ";
	} // foreach
	$min_list=substr($min_list,1);
	$sql = "SELECT $min_list FROM ".$this->from." WHERE ".$this->where." ".$this->where_xyz ; 	
	//echo "$sql";
	$mins=$db->get_row($sql) or die("could not execute near 559 in class");  
	$fieldnames_to_offer_array=array();
	foreach($mins as $key=>$val){
		if ($val != "") {
			$fieldnames_to_offer_array[$key]=$this->master_fieldnames_array[$key]; // 2D array is field_name=>display_name, look it up from the master lookup array
		}
	} // foreach
} else { 
	$fieldnames_to_offer_array=$this->master_fieldnames_array; 
} // if x, y or z is defined
if ($this->earthchem) { // Navdat already picked up these fields from view_data_output (alias norm). EarthChem uses sample_chemistry that does not include this column. All EarthChem samples have lat/long data so we don't have to check for non-null along with the other columns (chemicals, age)
	$fieldnames_to_offer_array['xval']='longitude'; // All samples have xval and yval values, so it's safe to just add them here without checking for non-null data 
	$fieldnames_to_offer_array['yval']='latitude';
}
$this->fieldnames_to_offer_array=$fieldnames_to_offer_array;
return $fieldnames_to_offer_array; 
} // end function

function make_plot_sql() { // Make the sql to retrieve the data for plotting datapoints. If they have not defined at least 2 axes, return false.
if ($this->showwork) {echo "<br><font size=1 color=blue>make_plot_sql</font><br>";echo time();ob_flush();flush();}
global $obj;
if ($this->where_xyz=="" or $this->select_xyz=="") {
	$myarray=$obj->make_sql_select_where_xyz(); // writes the $this var where_xyz and select_xyz
}
if ($this->where =="" or $this->from=="") {
	$myarray=$obj->make_sql_from_where(); // writes the $this var where and from 
} 
$sql = "SELECT $this->select_xyz    FROM $this->from    WHERE $this->where $this->where_xyz "; 
$this->plot_sql=$sql;
return $sql;
} // end function






function make_count_sql() { // Make the sql to retrieve the data for plotting datapoints. If they have not defined at least 2 axes, return false.
if ($this->showwork) {echo "<br><font size=1 color=blue>make_count_sql</font><br>";echo time();ob_flush();flush();}
global $obj;
if ($this->where_xyz=="") {
	$myarray=$obj->make_sql_select_where_xyz(); // writes the $this var where_xyz and select_xyz
}
if ($this->where =="" or $this->from=="") {
	$myarray=$obj->make_sql_from_where(); // writes the $this var where and from 
} 
$sql = "SELECT count(*) FROM $this->from WHERE $this->where $this->where_xyz "; 
$this->count_sql=$sql;
return $sql;
} // end function






function make_min_max_count_sql() { // Make the sql to retrieve the data for plotting datapoints. If they have not defined at least 2 axes, return false.
if ($this->showwork) {echo "<br><font size=1 color=blue>make_min_max_count_sql</font><br>";echo time();ob_flush();flush();}
global $obj;
if ($this->where_xyz=="" or $this->select_xyz_min_max=="") {
	$myarray=$obj->make_sql_select_where_xyz(); // writes the $this var where_xyz and select_xyz_min_max
}
if ($this->where =="" or $this->from=="") {
	$myarray=$obj->make_sql_from_where(); // writes the $this var where and from 
} 
$sql = "SELECT count(*), $this->select_xyz_min_max FROM $this->from WHERE $this->where $this->where_xyz "; 
$this->min_max_count_sql=$sql;
return $sql;
} // end function






} //end class 

?>