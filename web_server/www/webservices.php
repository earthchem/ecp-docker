<?PHP
/**
 * webservices.php
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
?>



<h1>EarthChem WFS Service</h1>

<div style="margin-left:20px;margin-top:20px;border:1px solid;padding:8px;background-color:#EEEEEE;">
The EarthChem WFS Service is a full transactional implementation of the OpenGIS Consortium's Web Feature Server specification.<br><br>
The following layers are available:
<ul>
	<li>Isotopes</li>
	<li>NobleGas</li>
	<li>RareEarth_Element</li>
	<li>StableIsotopes</li>
	<li>Trace_Element</li>
	<li>U-Series</li>
	<li>VolatileGas</li>
	<li>WR_Major_Element</li>
</ul>

The EarthChem WFS Service can be found at:<br>

<a href="http://ecp.iedadata.org/wfs/ows?service=wfs&version=1.0.0&request=GetCapabilities" target="_blank">
http://ecp.iedadata.org/wfs/ows?service=wfs&version=1.0.0&request=GetCapabilities
</a><br>

</div>
<br><br>

<div style="margin-left:20px;">

	<h1>Field List</h1>
	Each of the above layers contains the following common fields, along with associated chemical values.<br><br>
	
	<table cellpadding="3" cellspacing="1" bgcolor="#333333" width="665px";>
		<tr>
			<td bgcolor="#FBE6BF"><b>
				AnalysisURI
			</b></td>
			<td bgcolor="#FBE6BF">
				The unique identifier for the sample analysis.  
			</td>
		</tr>
		<tr>
			<td bgcolor="#FFFFFF"><b>
				AnalysisName
			</b></td>
			<td bgcolor="#FFFFFF">
				The human-intelligible name of the analysis.
			</td>
		</tr>
		<tr>
			<td bgcolor="#FBE6BF"><b>
				OtherAnalysisID
			</b></td>
			<td bgcolor="#FBE6BF">
				Other identifiers associated with this specific analysis.
			</td>
		</tr>
		<tr>
			<td bgcolor="#FFFFFF"><b>
				DetectionLimitURI
			</b></td>
			<td bgcolor="#FFFFFF">
				URI dereferencing to a representation of detection limits and uncertainty for analysis performed.
			</td>
		</tr>
		<tr>
			<td bgcolor="#FBE6BF"><b>
				Notes
			</b></td>
			<td bgcolor="#FBE6BF">
				Any notes about the geologic unit/formation associated with this analysis, the reporting methods, analysis method, additional information about the results, etc.
			</td>
		</tr>
		<tr>
			<td bgcolor="#FFFFFF"><b>
				SamplingFeatureName
			</b></td>
			<td bgcolor="#FFFFFF">
				Human-intelligible name of the feature.
			</td>
		</tr>
		<tr>
			<td bgcolor="#FBE6BF"><b>
				SamplingFeatureURI
			</b></td>
			<td bgcolor="#FBE6BF">
				Unique identifier for the geologic unit/formation described by the elements in this table.
			</td>
		</tr>
		<tr>
			<td bgcolor="#FFFFFF"><b>
				SpecimenID
			</b></td>
			<td bgcolor="#FFFFFF">
				ID given to the specimen by the collector(s).  May be an IGSN.
			</td>
		</tr>
		<tr>
			<td bgcolor="#FBE6BF"><b>
				OtherSpecimenID
			</b></td>
			<td bgcolor="#FBE6BF">
				Other IDs given to the specimen by the collector(s).
			</td>
		</tr>
		<tr>
			<td bgcolor="#FFFFFF"><b>
				SpecimenURI
			</b></td>
			<td bgcolor="#FFFFFF">
				Unique identifier for the specimen.
			</td>
		</tr>
		<tr>
			<td bgcolor="#FBE6BF"><b>
				ParentSampleURI
			</b></td>
			<td bgcolor="#FBE6BF">
				Unique resource identifier for the parent sample for the specimen that is the subject of the record.
			</td>
		</tr>
		<tr>
			<td bgcolor="#FFFFFF"><b>
				Label
			</b></td>
			<td bgcolor="#FFFFFF">
				Label for map portrayal
			</td>
		</tr>
		<tr>
			<td bgcolor="#FBE6BF"><b>
				SpecimenCollectionDate
			</b></td>
			<td bgcolor="#FBE6BF">
				Date the specimen was collected.
			</td>
		</tr>
		<tr>
			<td bgcolor="#FFFFFF"><b>
				SpecimenLabel
			</b></td>
			<td bgcolor="#FFFFFF">
				Label associated with the specimen.
			</td>
		</tr>
		<tr>
			<td bgcolor="#FBE6BF"><b>
				SpecimenDescription
			</b></td>
			<td bgcolor="#FBE6BF">
				Description of the specimen.
			</td>
		</tr>
		<tr>
			<td bgcolor="#FFFFFF"><b>
				SampleCollectionMethod
			</b></td>
			<td bgcolor="#FFFFFF">
				Method used to collect specimen.
			</td>
		</tr>
		<tr>
			<td bgcolor="#FBE6BF"><b>
				SpecimenType
			</b></td>
			<td bgcolor="#FBE6BF">
				Term to describe the type of specimen.
			</td>
		</tr>
		<tr>
			<td bgcolor="#FFFFFF"><b>
				SpecimenCollector
			</b></td>
			<td bgcolor="#FFFFFF">
				Field to indicate the party(s) responsible for collecting the sample.
			</td>
		</tr>
		<tr>
			<td bgcolor="#FBE6BF"><b>
				MaterialClass
			</b></td>
			<td bgcolor="#FBE6BF">
				Category from simple material classification scheme e.g. {rock, sediment, aqueous liquid, other liquid, gas, biological material}
			</td>
		</tr>
		<tr>
			<td bgcolor="#FFFFFF"><b>
				LithologyTerms
			</b></td>
			<td bgcolor="#FFFFFF">
				This field may be used for the type of material, mineral composition, or additional terms related to the rock type.
			</td>
		</tr>
		<tr>
			<td bgcolor="#FBE6BF"><b>
				RockName
			</b></td>
			<td bgcolor="#FBE6BF">
				Free text name for lithology category of sample.
			</td>
		</tr>
		<tr>
			<td bgcolor="#FFFFFF"><b>
				SampledGeologicUnit
			</b></td>
			<td bgcolor="#FFFFFF">
				Name of the geologic unit that the sample represents.
			</td>
		</tr>
		<tr>
			<td bgcolor="#FBE6BF"><b>
				SampledGeologicUnitAge
			</b></td>
			<td bgcolor="#FBE6BF">
				Approximate or known age of the geologic unit from which the sample was collected.
			</td>
		</tr>
		<tr>
			<td bgcolor="#FFFFFF"><b>
				County
			</b></td>
			<td bgcolor="#FFFFFF">
				County name.
			</td>
		</tr>
		<tr>
			<td bgcolor="#FBE6BF"><b>
				State
			</b></td>
			<td bgcolor="#FBE6BF">
				State name.
			</td>
		</tr>
		<tr>
			<td bgcolor="#FFFFFF"><b>
				LatDegree
			</b></td>
			<td bgcolor="#FFFFFF">
				Latitude of sample collection location in decimal degrees.
			</td>
		</tr>
		<tr>
			<td bgcolor="#FBE6BF"><b>
				LongDegree
			</b></td>
			<td bgcolor="#FBE6BF">
				Longitude of sample collection location in decimal degrees.
			</td>
		</tr>
		<tr>
			<td bgcolor="#FFFFFF"><b>
				SRS
			</b></td>
			<td bgcolor="#FFFFFF">
				Spatial reference system for latitude and longitude.
			</td>
		</tr>
		<tr>
			<td bgcolor="#FBE6BF"><b>
				LocationUncertaintyStatement
			</b></td>
			<td bgcolor="#FBE6BF">
				Free text statement on uncertainty of location coordinates.
			</td>
		</tr>
		<tr>
			<td bgcolor="#FFFFFF"><b>
				LocalityTerms
			</b></td>
			<td bgcolor="#FFFFFF">
				One or more geographic names associated with sample collection location.
			</td>
		</tr>
		<tr>
			<td bgcolor="#FBE6BF"><b>
				DatumElevation
			</b></td>
			<td bgcolor="#FBE6BF">
				Datum used for elevation.
			</td>
		</tr>
		<tr>
			<td bgcolor="#FFFFFF"><b>
				Depth
			</b></td>
			<td bgcolor="#FFFFFF">
				Depth should be included with the record if the sample was obtained at depth from a borehole or well - if known.
			</td>
		</tr>
		<tr>
			<td bgcolor="#FBE6BF"><b>
				Elevation
			</b></td>
			<td bgcolor="#FBE6BF">
				Elevation (AMSL).
			</td>
		</tr>
		<tr>
			<td bgcolor="#FFFFFF"><b>
				VerticalUnits
			</b></td>
			<td bgcolor="#FFFFFF">
				Meters or Feet.
			</td>
		</tr>
		<tr>
			<td bgcolor="#FBE6BF"><b>
				Source
			</b></td>
			<td bgcolor="#FBE6BF">
				Reference, citation, or URI for data source.
			</td>
		</tr>
		<tr>
			<td bgcolor="#FFFFFF"><b>
				Citation
			</b></td>
			<td bgcolor="#FFFFFF">
				Citation for sample;
			</td>
		</tr>
		<tr>
			<td bgcolor="#FBE6BF"><b>
				MetadataURI
			</b></td>
			<td bgcolor="#FBE6BF">
				URL redirecting the user to a web accessible page with metadata related to the analysis.
			</td>
		</tr>
		<tr>
			<td bgcolor="#FFFFFF"><b>
				RelatedResource
			</b></td>
			<td bgcolor="#FFFFFF">
				Any materials or resources related to the sample analysis that is the subject of this record.
			</td>
		</tr>
		<tr>
			<td bgcolor="#FBE6BF"><b>
				AnalysisType
			</b></td>
			<td bgcolor="#FBE6BF">
				Type of analysis used to obtain the results in this record.
			</td>
		</tr>
		<tr>
			<td bgcolor="#FFFFFF"><b>
				AnalysisDate
			</b></td>
			<td bgcolor="#FFFFFF">
				Date that analysis was obtained.
			</td>
		</tr>
		<tr>
			<td bgcolor="#FBE6BF"><b>
				Laboratory
			</b></td>
			<td bgcolor="#FBE6BF">
				Laboratory that performed the analysis that is the subject of this record.
			</td>
		</tr>
		<tr>
			<td bgcolor="#FFFFFF"><b>
				ProcedureSummary
			</b></td>
			<td bgcolor="#FFFFFF">
				Summary of Analytical technique for  this line of data.
			</td>
		</tr>
		<tr>
			<td bgcolor="#FBE6BF"><b>
				UpdateTimeStamp
			</b></td>
			<td bgcolor="#FBE6BF">
				Date of last time line of data was updated or corrected by the service provider.
			</td>
		</tr>






		
	</table>

	<br><br>

	<b>In addition to the above fields, each layer also contains chemical values for each sample:</b>
	
	<br><br>

	<table cellpadding="3" cellspacing="1" bgcolor="#333333" width="665px";>
		<tr>
			<td bgcolor="#FBE6BF"><b>
				Isotopes
			</b></td>
			<td bgcolor="#FBE6BF">
				Be10_Be, Be10_Be9, Cl36_Cl, Epsilon_Nd, Hf176_Hf177, K40_Ar36, Lu176_Hf177, Nd143_Nd144, Os184_Os188, Os186_Os188, Os187_Os186, Os187_Os188, Pb206_Pb204, Pb206_Pb207, Pb206_Pb208, Pb207_Pb204, Pb208_Pb204, Pb208_Pb206, Rb87_Sr86, Re187_Os186, Re187_Os188, Sm147_Nd144, Sr87_Sr86
			</td>
		</tr>
		<tr>
			<td bgcolor="#FFFFFF"><b>
				NobleGas
			</b></td>
			<td bgcolor="#FFFFFF">
				Ar36_Ar39, Ar37_Ar39, Ar37_Ar40, Ar38_Ar36, Ar39_Ar36, Ar40, Ar40_Ar36, Ar40_Ar39, Ar40_K40, He3_He4, He4_He3, He4_Ne20, Kr78_Kr84, Kr80_Kr84, Kr82_Kr84, Kr83_Kr84, Kr86_Kr84, Ne20_Ne22, Ne21_Ne20, Ne21_Ne22, Ne22_Ne20, Xe124_Xe130, Xe124_Xe132, Xe126_Xe130, Xe126_Xe132, Xe128_Xe130, Xe128_Xe132, Xe129_Xe130, Xe129_Xe132, Xe130_Xe132, Xe131_Xe130, Xe131_Xe132, Xe132_Xe130, Xe134_Xe130, Xe134_Xe132, Xe136_Xe130, Xe136_Xe132
			</td>
		</tr>
		<tr>
			<td bgcolor="#FBE6BF"><b>
				RareEarth_Element
			</b></td>
			<td bgcolor="#FBE6BF">
				Ce_ppm, Dy_ppm, Er_ppm, Eu_ppm, Gd_ppm, Hf_ppm, Ho_ppm, La_ppm, Lu_ppm, Nd_ppm, Pr_ppm, Sm_ppm, Ta_ppm, Tb_ppm, Tm_ppm, Yb_ppm
			</td>
		</tr>
		<tr>
			<td bgcolor="#FFFFFF"><b>
				StableIsotopes
			</b></td>
			<td bgcolor="#FFFFFF">
				Delta_18O
			</td>
		</tr>
		<tr>
			<td bgcolor="#FBE6BF"><b>
				Trace_Element
			</b></td>
			<td bgcolor="#FBE6BF">
				Ag_ppm, As_ppm, Ba_ppm, Be_ppm, Bi_ppm, Co_ppm, Cr_ppm, Cs_ppm, Cu_ppm, Dy_ppm, Ga_ppm, Ge_ppm, In_ppm, Mo_ppm, Nb_ppm, Ni_ppm, Pb_ppm, Rb_ppm, Sb_ppm, Sc_ppm, Sn_ppm, Sr_ppm, Ti_ppm, U_ppm, V_ppm, W_ppm, Y_ppm, Zn_ppm, Zr_ppm
			</td>
		</tr>
		<tr>
			<td bgcolor="#FFFFFF"><b>
				U-Series
			</b></td>
			<td bgcolor="#FFFFFF">
				Pb210_Ra226, Pb210_u238, Po210_Rn222, Po210_Th230, Ra226_Th228, Ra226_Th230, Ra228_Th232, Rn222_Th230, Th230_Ra226, Th230_Ra232, Th230_U238, Th232_Pb204, Th232_Th230, Th232_U238, Th238_U238, U234_U238, U235_Pb204, U238_Pb204, U238_Th230, U238_Th232
			</td>
		</tr>
		<tr>
			<td bgcolor="#FBE6BF"><b>
				VolatileGas
			</b></td>
			<td bgcolor="#FBE6BF">
				CO2, F, Cl, H
			</td>
		</tr>
		<tr>
			<td bgcolor="#FFFFFF"><b>
				WR_Major_Element
			</b></td>
			<td bgcolor="#FFFFFF">
				Al2O3_WtPct, CaO_WtPct, Fe2O3_WtPct, H2O_WtPct, K2O_WtPct, LOI_WtPct, MgO_WtPct, MnO_WtPct, Na2O_WtPct, P2O5_WtPct, SiO2_WtPct, TiO2_WtPct, Total_WtPct
			</td>
		</tr>
	</table>



</div>


<br><br><br><br><br><br><br><br><br>



<?
include('includes/ks_footer.html');
?>