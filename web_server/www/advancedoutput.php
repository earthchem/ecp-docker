<?PHP
/**
 * advancedoutput.php
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

include('get_pkey.php'); 
print_r($_GET);
/*
<CFSETTING requesttimeout="600">
<CFOUTPUT>
<CFPARAM name="url.dispmode" default="html">
<CFIF url.dispmode eq "xls">
<CFLOCATION url="http://neutrino.kgs.ku.edu/navdatdb/neutrinoxlsoutput.cfm?pkey=#url.pkey#&georoc=#url.georoc#&navdat=#url.navdat#&petdb=#url.petdb#&usgs=#url.usgs#&rowtype=#url.rowtype#&showmethods=#url.showmethods#&showunits=#url.showunits#" addtoken="no">
<CFABORT>
</CFIF>
*/
if (isset($_GET['dispmode']) && $_GET['dispmode'] == 'xls') {
//header(Location:"http://neutrino.kgs.ku.edu/navdatdb/neutrinoxlsoutput.cfm?pkey=#url.pkey#&georoc=#url.georoc#&navdat=#url.navdat#&petdb=#url.petdb#&usgs=#url.usgs#&rowtype=#url.rowtype#&showmethods=#url.showmethods#&showunits=#url.showunits#" addtoken="no");
exit;
}

/*
	<CFPARAM name="url.sort" default="">
	<CFSET sort=url.sort>
	<CFPARAM name="url.dispmode" default="html">
	<CFQUERY name="getfields" datasource="#datasource#">
select * from search_query where pkey=#url.pkey#
	</CFQUERY>
	<CFQUERY name="SearchQuery" datasource="#datasource#">
select * from search_query where pkey=#url.pkey#
	</CFQUERY>
	<CFSET allfields="#getfields.majors#,#getfields.traces#,#getfields.isotopes#"><!--- Do we need this? --->
	<CFSET allfields="#getfields.MAJOR_OXIDES#,#getfields.ISOTOPE_RATIO#,#getfields.NOBLE_GAS#,#getfields.REE#,#getfields.U_SERIES#,#getfields.VOLATILE#,#getfields.TRACE_ELEMENTS#,#getfields.STABLE_ISOTOPE#">
	<CFIF isdefined("url.numresults")>
		<CFSET numtoshow=url.numresults>
	<CFELSE>
		<CFSET numtoshow=50>
	</CFIF>
	<CFIF isdefined("form.rowtype")>
		<CFSET rowtype=form.rowtype>
	<CFELSE>
		<CFSET rowtype=url.rowtype>
	</CFIF>
	<CFIF isdefined("url.page")>
		<CFSET page=url.page>
	<CFELSE>
		<CFSET page=1>
	</CFIF>
	<CFSET startrow=((page-1)*numtoshow)+1>
	<CFSET endrow=(page*numtoshow)>
	<CFSET srcwhere="">
	<CFSET srcstart="no">
	<CFIF url.georoc eq "true">
		<CFIF srcstart eq "no">
			<CFSET srcwhere="( #srcwhere# source = 'GEOROC'">
			<CFSET srcstart="yes">
		<CFELSE>
			<CFSET srcwhere="#srcwhere# OR source='GEOROC'">
		</CFIF>
	</CFIF>
	<CFIF url.navdat eq "true">
		<CFIF srcstart eq "no">
			<CFSET srcwhere="( #srcwhere# source = 'navdat'">
			<CFSET srcstart="yes">
		<CFELSE>
			<CFSET srcwhere="#srcwhere# OR source='navdat'">
		</CFIF>
	</CFIF>
	<CFIF url.petdb eq "true">
		<CFIF srcstart eq "no">
			<CFSET srcwhere="( #srcwhere# source = 'PETDB'">
			<CFSET srcstart="yes">
		<CFELSE>
			<CFSET srcwhere="#srcwhere# OR source='PETDB'">
		</CFIF>
	</CFIF>
	<CFIF url.usgs eq "true">
		<CFIF srcstart eq "no">
			<CFSET srcwhere="( #srcwhere# source = 'USGS'">
			<CFSET srcstart="yes">
		<CFELSE>
			<CFSET srcwhere="#srcwhere# OR source='USGS'">
		</CFIF>
	</CFIF>
	<CFIF srcwhere neq "">
		<CFSET srcwhere=" AND #srcwhere# )">
	</CFIF>
	<CFSET source=srcwhere><!--- This variable is for the log_downloads_citations.cfm. We want to log the source(s). --->
	<CFIF url.navdat eq "true" and url.georoc eq "true" and url.petdb eq "true" and url.usgs eq "true">
		<CFSET srcwhere="">
	</CFIF>
	<CFINCLUDE template="querytest.cfm">
	<CFIF isdefined("url.count")>
		<CFSET totalcount=url.count>
	<CFELSE>
		<CFIF rowtype eq "method">
			<CFQUERY name="getcount" datasource="#datasource#">
#preservesinglequotes(advcount)#
			</CFQUERY>
		<CFELSE>
			<CFQUERY name="getcount" datasource="#datasource#">
#preservesinglequotes(samplechemcount)#
			</CFQUERY>
		</CFIF>
		<CFSET totalcount=getcount.count>
	</CFIF>
	<CFIF url.dispmode eq "html">
		<CFQUERY name="getsample" datasource="#datasource#">
Select * from (
			Select a.*, rownum rnum From (
			<CFIF rowtype eq "method">#preservesinglequotes(advstring)#
				<CFIF sort neq "">order by #sort#
				</CFIF>
			<CFELSE>
#preservesinglequotes(samplechemstring)#
				<CFIF sort neq "">order by #sort#
				</CFIF>
			</CFIF>
) a where rownum <= #endrow#
			) where rnum >= #startrow#
		</CFQUERY>
	<CFELSE>
		<CFQUERY name="getsample" datasource="#datasource#">
			<CFIF rowtype eq "method">#preservesinglequotes(advstring)#
			<CFELSE>
#preservesinglequotes(samplechemstring)#
			</CFIF>
		</CFQUERY>
	</CFIF>
	<CFSET numpages=int((totalcount/numtoshow)+1)>
	<CFSET showlist=""><!---
	<CFQUERY name="getfieldnames" datasource="#datasource#">
SELECT DISTINCT(field_name), DATA_ORDER FROM navdatdb.data_field_selection WHERE (group_name ='MAJOR') OR (group_name ='TRACE') OR (group_name ='ISOTOPE') OR (group_name ='REE') ORDER BY DATA_ORDER ASC
	</CFQUERY>
--->
	<CFQUERY name="getfieldnames" datasource="#datasource#">
SELECT DISTINCT(field_name), DATA_ORDER FROM data_fields ORDER BY DATA_ORDER ASC
	</CFQUERY>
	<CFLOOP query="getfieldnames">
		<CFIF ListFindNoCase(allfields, field_name) gt 0>
			<CFSET showlist=ListAppend(showlist, field_name)>
		</CFIF>
	</CFLOOP>
	<CFSET fieldlist=valuelist(getfieldnames.field_name)>
	<CFIF getsample.recordcount gt 0>
		<CFIF url.dispmode eq "html"><!--- ******************* this is the start of the html portion ********************** --->
			<CFINCLUDE template="includes/ks_head.html">
<SCRIPT type="text/javascript">function showwhere()
		{
			var obj_batch = document.getElementById('wherestate');
			if (obj_batch.style.display == "none")
				obj_batch.style.display = "block";
			else
				obj_batch.style.display = "none";
		}
</SCRIPT>
			<CFIF rowtype eq "method"><H1>			Sample Data Output: #totalcount# methods found.
</H1>
			<CFELSE>
				<H1>				Sample Data Output: #totalcount# samples found.
</H1>
			</CFIF>
<!---<A href="index.cfm"><IMG border="0" src="images/newsearch.gif"></A>
					--->
					
					<!---
			<DIV align="left"><A href="gotomapper.cfm?pkey=#url.pkey#&georoc=#url.georoc#&navdat=#url.navdat#&petdb=#url.petdb#&usgs=#url.usgs#" target="_blank"><IMG border="0" src="images/interactivemap.gif"></A>&nbsp;&nbsp;&nbsp;&nbsp;<A href="xlsoutput.cfm?pkey=#url.pkey#&georoc=#url.georoc#&navdat=#url.navdat#&petdb=#url.petdb#&usgs=#url.usgs#"><IMG border="0" src="images/xlsoutput.gif"></A>&nbsp;&nbsp;&nbsp;&nbsp;<A href="tabdelimitedoutput.cfm?pkey=#url.pkey#&georoc=#url.georoc#&navdat=#url.navdat#&petdb=#url.petdb#&usgs=#url.usgs#"><IMG border="0" src="images/txtoutput.gif"></A>
			</DIV><BR>--->
			<DIV style="margin:5px">
				<CFIF page gt 1><A href="advancedoutputc.cfm?pkey=#url.pkey#&georoc=#url.georoc#&navdat=#url.navdat#&petdb=#url.petdb#&usgs=#url.usgs#&page=#page-1#&numresults=#numtoshow#&count=#totalcount#&sort=#sort#&rowtype=#rowtype#&showmethods=#showmethods#&showunits=#showunits#"><B>				&lt;&lt;PREV
</B></A>
				</CFIF>
				<CFIF (page-2) gt 1><A href="advancedoutputc.cfm?pkey=#url.pkey#&georoc=#url.georoc#&navdat=#url.navdat#&petdb=#url.petdb#&usgs=#url.usgs#&page=1&numresults=#numtoshow#&count=#totalcount#&sort=#sort#&rowtype=#rowtype#&showmethods=#showmethods#&showunits=#showunits#">				1...
</A>
				</CFIF>
				<CFIF (page-2 gt 0)><A href="advancedoutputc.cfm?pkey=#url.pkey#&georoc=#url.georoc#&navdat=#url.navdat#&petdb=#url.petdb#&usgs=#url.usgs#&page=#page-2#&numresults=#numtoshow#&count=#totalcount#&sort=#sort#&rowtype=#rowtype#&showmethods=#showmethods#&showunits=#showunits#">				#page-2#
</A>
				</CFIF>
				<CFIF (page-1) gt 0><A href="advancedoutputc.cfm?pkey=#url.pkey#&georoc=#url.georoc#&navdat=#url.navdat#&petdb=#url.petdb#&usgs=#url.usgs#&page=#page-1#&numresults=#numtoshow#&count=#totalcount#&sort=#sort#&rowtype=#rowtype#&showmethods=#showmethods#&showunits=#showunits#">				#page-1#
</A>
				</CFIF>
				<CFIF numpages gt 1>#page#
				</CFIF>
				<CFIF numpages gt page><A href="advancedoutputc.cfm?pkey=#url.pkey#&georoc=#url.georoc#&navdat=#url.navdat#&petdb=#url.petdb#&usgs=#url.usgs#&page=#page+1#&numresults=#numtoshow#&count=#totalcount#&sort=#sort#&rowtype=#rowtype#&showmethods=#showmethods#&showunits=#showunits#">				#page+1#
</A>
				</CFIF>
				<CFIF numpages gt page+1><A href="advancedoutputc.cfm?pkey=#url.pkey#&georoc=#url.georoc#&navdat=#url.navdat#&petdb=#url.petdb#&usgs=#url.usgs#&page=#page+2#&numresults=#numtoshow#&count=#totalcount#&sort=#sort#&rowtype=#rowtype#&showmethods=#showmethods#&showunits=#showunits#">				#page+2#
</A>
				</CFIF>
				<CFIF numpages gt page+2><A href="advancedoutputc.cfm?pkey=#url.pkey#&georoc=#url.georoc#&navdat=#url.navdat#&petdb=#url.petdb#&usgs=#url.usgs#&page=#numpages#&numresults=#numtoshow#&count=#totalcount#&sort=#sort#&rowtype=#rowtype#&showmethods=#showmethods#&showunits=#showunits#">				...#numpages#
</A>
				</CFIF>
				<CFIF totalcount gt (page * numtoshow)><A href="advancedoutputc.cfm?pkey=#url.pkey#&georoc=#url.georoc#&navdat=#url.navdat#&petdb=#url.petdb#&usgs=#url.usgs#&page=#page+1#&numresults=#numtoshow#&count=#totalcount#&sort=#sort#&rowtype=#rowtype#&showmethods=#showmethods#&showunits=#showunits#"><B>				NEXT&gt;&gt;
</B></A>
				</CFIF>
			</DIV>
			<CFIF rowtype eq "method"><!--- this table needs alternate code for sample type rows --->
				<TABLE class="ku_htmloutput">
					<TR valign="bottom">
						<TH><H4><A href="advancedoutputc.cfm?showmethods=#showmethods#&showunits=#showunits#&rowtype=#rowtype#&pkey=#url.pkey#&georoc=#url.georoc#&navdat=#url.navdat#&petdb=#url.petdb#&usgs=#url.usgs#&numresults=#numtoshow#&count=#totalcount#&sort=sample_id">						SAMPLE&nbsp;ID
</A></H4>
						</TH>
						<TH><H4><A href="advancedoutputc.cfm?showmethods=#showmethods#&showunits=#showunits#&rowtype=#rowtype#&pkey=#url.pkey#&georoc=#url.georoc#&navdat=#url.navdat#&petdb=#url.petdb#&usgs=#url.usgs#&numresults=#numtoshow#&count=#totalcount#&sort=source">						SOURCE
</A></H4>
						</TH>
						<TH><H4>						DETAIL
</H4>
						</TH>
						<TH><H4>						MAP
</H4>
						</TH>
						<TH><H4>						GOOGLE<BR>SCHOLAR
</H4>
						</TH>
						<TH><H4><A href="advancedoutputc.cfm?showmethods=#showmethods#&showunits=#showunits#&rowtype=#rowtype#&pkey=#url.pkey#&georoc=#url.georoc#&navdat=#url.navdat#&petdb=#url.petdb#&usgs=#url.usgs#&numresults=#numtoshow#&count=#totalcount#&sort=title">						TITLE
</A></H4>
						</TH>
						<TH><H4><A href="advancedoutputc.cfm?showmethods=#showmethods#&showunits=#showunits#&rowtype=#rowtype#&pkey=#url.pkey#&georoc=#url.georoc#&navdat=#url.navdat#&petdb=#url.petdb#&usgs=#url.usgs#&numresults=#numtoshow#&count=#totalcount#&sort=journal">						JOURNAL
</A></H4>
						</TH>
						<TH><H4><A href="advancedoutputc.cfm?showmethods=#showmethods#&showunits=#showunits#&rowtype=#rowtype#&pkey=#url.pkey#&georoc=#url.georoc#&navdat=#url.navdat#&petdb=#url.petdb#&usgs=#url.usgs#&numresults=#numtoshow#&count=#totalcount#&sort=author">						AUTHOR
</A></H4>
						</TH><!---
						<TH><H4>						YEAR
</H4>
						</TH>--->
						<TH><H4><A href="advancedoutputc.cfm?showmethods=#showmethods#&showunits=#showunits#&rowtype=#rowtype#&pkey=#url.pkey#&georoc=#url.georoc#&navdat=#url.navdat#&petdb=#url.petdb#&usgs=#url.usgs#&numresults=#numtoshow#&count=#totalcount#&sort=latitude">						LATITUDE
</A></H4>
						</TH>
						<TH><H4><A href="advancedoutputc.cfm?showmethods=#showmethods#&showunits=#showunits#&rowtype=#rowtype#&pkey=#url.pkey#&georoc=#url.georoc#&navdat=#url.navdat#&petdb=#url.petdb#&usgs=#url.usgs#&numresults=#numtoshow#&count=#totalcount#&sort=longitude">						LONGITUDE
</A></H4>
						</TH>
						<TH><H4><A href="advancedoutputc.cfm?showmethods=#showmethods#&showunits=#showunits#&rowtype=#rowtype#&pkey=#url.pkey#&georoc=#url.georoc#&navdat=#url.navdat#&petdb=#url.petdb#&usgs=#url.usgs#&numresults=#numtoshow#&count=#totalcount#&sort=method">						METHOD
</A></H4>
						</TH>
						<TH><H4><A href="advancedoutputc.cfm?showmethods=#showmethods#&showunits=#showunits#&rowtype=#rowtype#&pkey=#url.pkey#&georoc=#url.georoc#&navdat=#url.navdat#&petdb=#url.petdb#&usgs=#url.usgs#&numresults=#numtoshow#&count=#totalcount#&sort=ec_level_1">						MATERIAL
</A></H4>
						</TH>
						<TH><H4><A href="advancedoutputc.cfm?showmethods=#showmethods#&showunits=#showunits#&rowtype=#rowtype#&pkey=#url.pkey#&georoc=#url.georoc#&navdat=#url.navdat#&petdb=#url.petdb#&usgs=#url.usgs#&numresults=#numtoshow#&count=#totalcount#&sort=ec_level_2">						TYPE
</A></H4>
						</TH>
						<TH><H4><A href="advancedoutputc.cfm?showmethods=#showmethods#&showunits=#showunits#&rowtype=#rowtype#&pkey=#url.pkey#&georoc=#url.georoc#&navdat=#url.navdat#&petdb=#url.petdb#&usgs=#url.usgs#&numresults=#numtoshow#&count=#totalcount#&sort=ec_level_3">						COMPOSITION
</A></H4>
						</TH>
						<TH nowrap><H4><A href="advancedoutputc.cfm?showmethods=#showmethods#&showunits=#showunits#&rowtype=#rowtype#&pkey=#url.pkey#&georoc=#url.georoc#&navdat=#url.navdat#&petdb=#url.petdb#&usgs=#url.usgs#&numresults=#numtoshow#&count=#totalcount#&sort=ec_level_4">						ROCK NAME
</A></H4>
						</TH>
						<CFLOOP list="#showlist#" index="field_name">
							<CFIF trim(field_name) eq "ARSENIC">
								<CFSET showfield="AS">
							<CFELSE>
								<CFSET showfield=field_name>
							</CFIF>
							<TH><H4><A href="advancedoutputc.cfm?showmethods=#showmethods#&showunits=#showunits#&rowtype=#rowtype#&pkey=#url.pkey#&georoc=#url.georoc#&navdat=#url.navdat#&petdb=#url.petdb#&usgs=#url.usgs#&numresults=#numtoshow#&count=#totalcount#&sort=#showfield#">							#showfield#
</A></H4>
							</TH>
						</CFLOOP>
					</TR>
					<CFSET row=1>
					<CFLOOP query="getsample">
						<CFIF row eq 1>
							<CFSET bgcolor="##FFFFFF">
							<CFSET row=2>
						<CFELSE>
							<CFSET bgcolor="##FDE6C2">
							<CFSET row=1>
						</CFIF>
						<CFIF url neq ""><!---
							<CFSET urlstring="<a href=""#url#"" target=""_blank""><font color=""##F3683E"">DETAILS</h3></a>">--->
						<!---
							<CFSET urlstring="<a href=""javascript:popsample('#url#');"">DETAILS</a>">--->
							<CFSET urlstring="<a href=""#url#"" target=""_blank"">DETAILS</a>">
						<CFELSE>
							<CFSET urlstring="&nbsp;">
						</CFIF>
						<TR>
							<TD   bgcolor="#bgcolor#">#sample_id#
							</TD>
							<TD   bgcolor="#bgcolor#">#ucase(source)#
							</TD>
							<TD   bgcolor="#bgcolor#">#urlstring#
							</TD>
							<TD   bgcolor="#bgcolor#"><A href="http://navdat.kgs.ku.edu/maps/ecmap.cfm?lat=#latitude#&long=#longitude#&source=#source#&samplenum=#sample_num#">							MAP
</A>
							</TD><!--- Google link here --->
							<TD   bgcolor="#bgcolor#"><!--- link to google scholar --->


<!--- process the authors a bit for GoogleScholar advanced search link url, displayed below --->
								<CFSET googlescholarauthor = #author#>
								<CFSET googlescholarauthor = Replace('#googlescholarauthor#','"','','all')><!--- strip quotes --->
								<CFSET googlescholarauthor = Replace("#googlescholarauthor#",";"," ","all")><!--- replace ;  --->
								<CFSET googlescholarauthor = Replace("#googlescholarauthor#",","," ","all")><!--- replace ,  --->
								<CFSET googlescholarauthor = Replace("#googlescholarauthor#","-"," ","all")><!--- replace -  --->
								<CFSET googlescholarauthor = Replace("#googlescholarauthor#",":"," ","all")><!--- replace :  --->
								<CFSET googlescholarauthor = Replace("#googlescholarauthor#","+"," ","all")><!--- replace + --->
								<CFIF REFind(" ","#googlescholarauthor#",0) gt 0>
									<CFSET googlescholarauthor = RemoveChars(googlescholarauthor,   REFind(" ","#googlescholarauthor#",0) , 1000)><!--- Get just the first author name, a last name. This is because EarthChem authors in the view often have initials concatenated with lastnames, and Google Scholar cannot find them --->
								</CFIF>
<!--- process the titles a bit for GoogleScholar advanced search link url, displayed below --->
								<CFSET googlescholartitle = #title#>
								<CFSET googlescholartitle = Replace('#googlescholartitle#','"','','all')><!--- strip quotes --->
								<CFSET googlescholartitle = Replace("#googlescholartitle#","'","","all")><!--- strip quotes --->
								<CFSET googlescholartitle = Replace("#googlescholartitle#",";"," ","all")><!--- replace ;  --->
								<CFSET googlescholartitle = Replace("#googlescholartitle#",","," ","all")><!--- replace ,  --->
								<CFSET googlescholartitle = Replace("#googlescholartitle#","-"," ","all")><!--- replace -  --->
								<CFSET googlescholartitle = Replace("#googlescholartitle#",":"," ","all")><!--- replace :  --->
								<CFSET googlescholartitle = Replace("#googlescholartitle#","title"," ","all")><!--- replace 'title' --->
								<CFIF len(#googlescholartitle#) gt 0 and refind(" ",#googlescholartitle#,26) gt 0><!--- Get the first 26 letters, then round up to nearest whole word --->
									<CFSET googlescholartitle = trim( RemoveChars(googlescholartitle,   REFind(" ","#googlescholartitle#",26) , 1000) ) >
									<CFSET googlescholartitle = Replace("#googlescholartitle#","  ","","all")><!--- eliminate double spaces --->
									<CFSET googlescholartitle = Replace("#googlescholartitle#"," ","+","all")><!--- replace + --->
								</CFIF>
<!--- the year is easy --->
								<CFSET googlescholaryear = #year#><!--- If we got any parameters to pass to GoogleScholar, post a link --->
								<CFIF googlescholartitle gt "" and googlescholarauthor gt "" and googlescholaryear gt ""><A target="_blank" href="http://scholar.google.com/scholar?as_q=&num=10&btnG=Search+Scholar&as_epq=#googlescholartitle#&as_sauthors=#googlescholarauthor#&as_publication=&as_ylo=#googlescholaryear#&as_yhi=#googlescholaryear#&as_allsubj=all&hl=en&lr=">								Reference
</A>
								</CFIF>
<!---<FONT size="0">#year#
</H3>								--->
							</TD>
							<TD style="white-space:nowrap"  bgcolor="#bgcolor#">
								<CFIF len(title) gt 24>
									<DIV onMouseOver="return escape('#title#')">#mid(title, 1, 25)#...
									</DIV>
								<CFELSE>
#title#
								</CFIF>
							</TD>
							<TD style="white-space:nowrap"  bgcolor="#bgcolor#">
								<CFIF len(journal) gt 24>
									<DIV onMouseOver="return escape('#journal#')">#mid(journal, 1, 25)#...
									</DIV>
								<CFELSE>
#journal#
								</CFIF>
							</TD>
							
							<CFIF len(author) gt 24>
								<TD style="white-space:nowrap"  bgcolor="#bgcolor#">
									<DIV onMouseOver="return escape('#author#')">#mid(author, 1, 25)#...
									</DIV>
								 
							<CFELSE>
								<TD   bgcolor="#bgcolor#">#author#
								 
							</CFIF>
							</td>
<!---
							<TD   bgcolor="#bgcolor#">#year#
							</TD>--->
							<TD   bgcolor="#bgcolor#">#latitude#
							</TD>
							<TD   bgcolor="#bgcolor#">#longitude#
							</TD>
							<TD   bgcolor="#bgcolor#">#method#
							</TD><!---
							<CFIF volcanic eq "true">
								<CFSET rocktype="volcanic">
							<CFELSEIF plutonic eq "true">
								<CFSET rocktype="plutonic">
							<CFELSE>
								<CFSET rocktype="">
							</CFIF>
--->
							<TD   bgcolor="#bgcolor#">#EC_LEVEL_1#
							</TD>
							<TD   bgcolor="#bgcolor#">#EC_LEVEL_2#
							</TD>
							<TD   bgcolor="#bgcolor#">#EC_LEVEL_3#
							</TD>
							<TD   bgcolor="#bgcolor#">#EC_LEVEL_4#
							</TD>
							<CFLOOP list="#showlist#" index="currentfield">
								<TD   bgcolor="#bgcolor#">
									<CFIF isdefined("#currentfield#")>
										<CFIF evaluate("#currentfield#") neq "">#evaluate("#currentfield#")#
										<CFELSE>
&nbsp;
										</CFIF>
									<CFELSE>
&nbsp;
									</CFIF>
								</TD>
							</CFLOOP>
						</TR>
					</CFLOOP>
				</TABLE><!--- above table needs alternate code for sample type rows --->
			<CFELSE>
<!--- ***************************************************************************************** --->

			<!--- here is the table for sample-level rows --->
				<TABLE class="ku_htmloutput">
					<TR valign="bottom">
						<TH nowrap><H4><A href="advancedoutputc.cfm?showmethods=#showmethods#&showunits=#showunits#&rowtype=#rowtype#&pkey=#url.pkey#&georoc=#url.georoc#&navdat=#url.navdat#&petdb=#url.petdb#&usgs=#url.usgs#&numresults=#numtoshow#&count=#totalcount#&sort=sample_id">						SAMPLE&nbsp;ID
</A></H4>
						</TH>
						<TH nowrap><H4><A href="advancedoutputc.cfm?showmethods=#showmethods#&showunits=#showunits#&rowtype=#rowtype#&pkey=#url.pkey#&georoc=#url.georoc#&navdat=#url.navdat#&petdb=#url.petdb#&usgs=#url.usgs#&numresults=#numtoshow#&count=#totalcount#&sort=source">						SOURCE
</A></H4>
						</TH>
						<TH nowrap><H4>						DETAIL
</H4>
						</TH>
						<TH nowrap><H4>						MAP
</H4>
						</TH>
						<TH nowrap><H4><A href="advancedoutputc.cfm?showmethods=#showmethods#&showunits=#showunits#&rowtype=#rowtype#&pkey=#url.pkey#&georoc=#url.georoc#&navdat=#url.navdat#&petdb=#url.petdb#&usgs=#url.usgs#&numresults=#numtoshow#&count=#totalcount#&sort=latitude">						LATITUDE
</A></H4>
						</TH>
						<TH nowrap><H4><A href="advancedoutputc.cfm?showmethods=#showmethods#&showunits=#showunits#&rowtype=#rowtype#&pkey=#url.pkey#&georoc=#url.georoc#&navdat=#url.navdat#&petdb=#url.petdb#&usgs=#url.usgs#&numresults=#numtoshow#&count=#totalcount#&sort=longitude">						LONGITUDE
</A></H4>
						</TH><!--- these 4 are commented out, because at a sample level, there could be more than one possibility here
						<TH nowrap><H4><A href="advancedoutputc.cfm?showmethods=#showmethods#&showunits=#showunits#&rowtype=#rowtype#&pkey=#url.pkey#&georoc=#url.georoc#&navdat=#url.navdat#&petdb=#url.petdb#&usgs=#url.usgs#&numresults=#numtoshow#&count=#totalcount#&sort=ec_level_1">						MATERIAL
</A></H4>
						</TH>
						<TH nowrap><H4><A href="advancedoutputc.cfm?showmethods=#showmethods#&showunits=#showunits#&rowtype=#rowtype#&pkey=#url.pkey#&georoc=#url.georoc#&navdat=#url.navdat#&petdb=#url.petdb#&usgs=#url.usgs#&numresults=#numtoshow#&count=#totalcount#&sort=ec_level_2">						TYPE
</A></H4>
						</TH>
						<TH nowrap><H4><A href="advancedoutputc.cfm?showmethods=#showmethods#&showunits=#showunits#&rowtype=#rowtype#&pkey=#url.pkey#&georoc=#url.georoc#&navdat=#url.navdat#&petdb=#url.petdb#&usgs=#url.usgs#&numresults=#numtoshow#&count=#totalcount#&sort=ec_level_3">						COMPOSITION
</A></H4>
						</TH>
						<TH nowrap><H4><A href="advancedoutputc.cfm?showmethods=#showmethods#&showunits=#showunits#&rowtype=#rowtype#&pkey=#url.pkey#&georoc=#url.georoc#&navdat=#url.navdat#&petdb=#url.petdb#&usgs=#url.usgs#&numresults=#numtoshow#&count=#totalcount#&sort=ec_level_4">						ROCK NAME
</A></H4>
						</TH>--->
						<CFLOOP list="#showlist#" index="field_name">
							<CFIF trim(field_name) eq "ARSENIC">
								<CFSET showfield="AS">
							<CFELSE>
								<CFSET showfield=field_name>
							</CFIF>
							<TH><H4><A href="advancedoutputc.cfm?showmethods=#showmethods#&showunits=#showunits#&rowtype=#rowtype#&pkey=#url.pkey#&georoc=#url.georoc#&navdat=#url.navdat#&petdb=#url.petdb#&usgs=#url.usgs#&numresults=#numtoshow#&count=#totalcount#&sort=#field_name#">							#showfield#
</A></H4>
							</TH>
							<CFIF showunits eq "yes">
								<TH nowrap><H4><A href="advancedoutputc.cfm?showmethods=#showmethods#&showunits=#showunits#&rowtype=#rowtype#&pkey=#url.pkey#&georoc=#url.georoc#&navdat=#url.navdat#&petdb=#url.petdb#&usgs=#url.usgs#&numresults=#numtoshow#&count=#totalcount#&sort=#showfield#_unit">								#showfield# UNIT
</A></H4>
								</TH>
							</CFIF>
							<CFIF showmethods eq "yes">
								<TH nowrap><H4><A href="advancedoutputc.cfm?showmethods=#showmethods#&showunits=#showunits#&rowtype=#rowtype#&pkey=#url.pkey#&georoc=#url.georoc#&navdat=#url.navdat#&petdb=#url.petdb#&usgs=#url.usgs#&numresults=#numtoshow#&count=#totalcount#&sort=#showfield#_meth">								#showfield# METH
</A></H4>
								</TH>
							</CFIF>
						</CFLOOP>
					</TR>
					<CFSET row=1>
					<CFLOOP query="getsample">
						<CFIF row eq 1>
							<CFSET bgcolor="##FFFFFF">
							<CFSET row=2>
						<CFELSE>
							<CFSET bgcolor="##FDE6C2">
							<CFSET row=1>
						</CFIF>
						<CFIF url neq ""><!---
							<CFSET urlstring="<a href=""#url#"" target=""_blank""><font color=""##F3683E"">DETAILS</h3></a>">--->
							<CFSET urlstring="<a href=""javascript:popsample('#url#');"">DETAILS</a>">
							<CFSET urlstring="<a href=""#url#"" target=""_blank"">DETAILS</a>">
						<CFELSE>
							<CFSET urlstring="&nbsp;">
						</CFIF>
						<TR>
							<TD   bgcolor="#bgcolor#">#sample_id#
							</TD>
							<TD   bgcolor="#bgcolor#">#ucase(source)#
							</TD>
							<TD   bgcolor="#bgcolor#">#urlstring#
							</TD>
							<TD   bgcolor="#bgcolor#"><A href="javascript:popwindow('http://navdat.kgs.ku.edu/maps/ecmap.cfm?lat=#latitude#&long=#longitude#&source=#source#&samplenum=#sample_num#');">							MAP
</A>
							</TD>
							<TD   bgcolor="#bgcolor#">#latitude#
							</TD>
							<TD   bgcolor="#bgcolor#">#longitude#
							</TD><!--- these 4 are commented out, because at a sample level, there could be more than one possibility here
							<TD   bgcolor="#bgcolor#">#EC_LEVEL_1#
							</TD>
							<TD   bgcolor="#bgcolor#">#EC_LEVEL_2#
							</TD>
							<TD   bgcolor="#bgcolor#">#EC_LEVEL_3#
							</TD>
							<TD   bgcolor="#bgcolor#">#EC_LEVEL_4#
							</TD>--->
							<CFLOOP list="#showlist#" index="currentfield">
								<TD   bgcolor="#bgcolor#">
									<CFIF isdefined("#currentfield#")>
										<CFIF evaluate("#currentfield#") neq "">#evaluate("#currentfield#")#
										<CFELSE>
&nbsp;
										</CFIF>
									<CFELSE>
&nbsp;
									</CFIF>
								</TD>
								<CFIF showunits eq "yes">
									<TD   bgcolor="#bgcolor#">
										<CFIF isdefined("#currentfield#_UNIT")>
											<CFIF evaluate("#currentfield#_UNIT") neq "">#evaluate("#currentfield#_UNIT")#
											<CFELSE>
&nbsp;
											</CFIF>
										<CFELSE>
&nbsp;
										</CFIF>
									</TD>
								</CFIF>
								<CFIF showmethods eq "yes">
									<TD   bgcolor="#bgcolor#">
										<CFIF isdefined("#currentfield#_METH")>
											<CFIF evaluate("#currentfield#_METH") neq "">#evaluate("#currentfield#_METH")#
											<CFELSE>
&nbsp;
											</CFIF>
										<CFELSE>
&nbsp;
										</CFIF>
									</TD>
								</CFIF>
							</CFLOOP>
						</TR>
					</CFLOOP>
				</TABLE><!--- ***************************************************************************************** --->
			</CFIF>
<!--- end if method row options --->
			<DIV style="margin:5px">
				<CFIF page gt 1><A href="advancedoutputc.cfm?pkey=#url.pkey#&georoc=#url.georoc#&navdat=#url.navdat#&petdb=#url.petdb#&usgs=#url.usgs#&page=#page-1#&numresults=#numtoshow#&sort=#sort#&rowtype=#rowtype#&showmethods=#showmethods#&showunits=#showunits#"><B>				&lt;&lt;PREV
</B></A>
				<CFELSE>
 
				</CFIF>
				<CFIF (page-2) gt 1><A href="advancedoutputc.cfm?pkey=#url.pkey#&georoc=#url.georoc#&navdat=#url.navdat#&petdb=#url.petdb#&usgs=#url.usgs#&page=1&numresults=#numtoshow#&count=#totalcount#&sort=#sort#&rowtype=#rowtype#&showmethods=#showmethods#&showunits=#showunits#">				1...
</A>
				<CFELSE>
 
				</CFIF>
				<CFIF (page-2 gt 0)><A href="advancedoutputc.cfm?pkey=#url.pkey#&georoc=#url.georoc#&navdat=#url.navdat#&petdb=#url.petdb#&usgs=#url.usgs#&page=#page-2#&numresults=#numtoshow#&count=#totalcount#&sort=#sort#&rowtype=#rowtype#&showmethods=#showmethods#&showunits=#showunits#">				#page-2#
</A>
				<CFELSE>
 
				</CFIF>
				<CFIF (page-1) gt 0><A href="advancedoutputc.cfm?pkey=#url.pkey#&georoc=#url.georoc#&navdat=#url.navdat#&petdb=#url.petdb#&usgs=#url.usgs#&page=#page-1#&numresults=#numtoshow#&count=#totalcount#&sort=#sort#&rowtype=#rowtype#&showmethods=#showmethods#&showunits=#showunits#">				#page-1#
</A>
				<CFELSE>
 
				</CFIF>
				<CFIF numpages gt 1>#page#
				<CFELSE>
 
				</CFIF>
				<CFIF numpages gt page><A href="advancedoutputc.cfm?pkey=#url.pkey#&georoc=#url.georoc#&navdat=#url.navdat#&petdb=#url.petdb#&usgs=#url.usgs#&page=#page+1#&numresults=#numtoshow#&count=#totalcount#&sort=#sort#&rowtype=#rowtype#&showmethods=#showmethods#&showunits=#showunits#">				#page+1#
</A>
				<CFELSE>
 
				</CFIF>
				<CFIF numpages gt page+1><A href="advancedoutputc.cfm?pkey=#url.pkey#&georoc=#url.georoc#&navdat=#url.navdat#&petdb=#url.petdb#&usgs=#url.usgs#&page=#page+2#&numresults=#numtoshow#&count=#totalcount#&sort=#sort#&rowtype=#rowtype#&showmethods=#showmethods#&showunits=#showunits#">				#page+2#
</A>
				<CFELSE>
 
				</CFIF>
				<CFIF numpages gt page+2><A href="advancedoutputc.cfm?pkey=#url.pkey#&georoc=#url.georoc#&navdat=#url.navdat#&petdb=#url.petdb#&usgs=#url.usgs#&page=#numpages#&numresults=#numtoshow#&count=#totalcount#&sort=#sort#&rowtype=#rowtype#&showmethods=#showmethods#&showunits=#showunits#">				...#numpages#
</A>
				<CFELSE>
 
				</CFIF>
				<CFIF totalcount gt (page * numtoshow)><A href="advancedoutputc.cfm?pkey=#url.pkey#&georoc=#url.georoc#&navdat=#url.navdat#&petdb=#url.petdb#&usgs=#url.usgs#&page=#page+1#&numresults=#numtoshow#&count=#totalcount#&sort=#sort#&rowtype=#rowtype#&showmethods=#showmethods#&showunits=#showunits#"><B>				NEXT&gt;&gt;
</B></A>
				<CFELSE>
 
				</CFIF>
			</DIV>
<SCRIPT language="JavaScript" type="text/javascript" src="wz_tooltip.js"></SCRIPT><BR><BR><!---
			<CFIF isdefined("url.debug") >select count(*) as count from combined_output<BR>where<BR>#preservesinglequotes(ecwhere)#<BR>#preservesinglequotes(srcwhere)#<BR>#preservesinglequotes(extrawhere)#<BR><BR><BR>Select * from (<BR>Select a.*, rownum rnum From (<BR>select * from combined_output where<BR>#preservesinglequotes(ecwhere)#<BR>#preservesinglequotes(srcwhere)#<BR>#preservesinglequotes(extrawhere)#<BR>) a where rownum <= #endrow#<BR>) where rnum >= #startrow#<BR><BR><BR>
			</CFIF>
--->
			<DIV id="wherestate" style="display: none">#preservesinglequotes(advstring)#<BR><BR><BR>#preservesinglequotes(refstring)#<BR><BR><BR><!---#preservesinglequotes(samplechemstring)#<BR><BR><BR>--->
				<CFSET foo=preservesinglequotes(samplechemstring)>
				<CFSET foo=replace(foo,",",",<br>","All")>
				<CFSET foo=replace(foo,"AND","AND<br>","All")>#preservesinglequotes(foo)#<BR><BR><BR>
				<CFSET foo=preservesinglequotes(advcount)>
				<CFSET foo=replace(foo,",",",<br>","All")>
				<CFSET foo=replace(foo,"AND","AND<br>","All")>#preservesinglequotes(foo)#<BR><BR><BR>
			</DIV><!---Note: We do not log citations for html output - only for text and xls output.--->
			<CFINCLUDE template="ecfooter.cfm"><!--- ******************* this is the end of the html portion ********************** --->
		<CFELSEIF url.dispmode eq "text"><!--- ******************* this is the start of the text portion ********************** --->
			<CFIF rowtype eq "method">
				<CFSET randnum=randrange(0,99999)>
				<CFSET filename="D:\webware\Apache\Apache2\htdocs\navdatdb\temp/earthchemOutput#randnum#.txt">
				<CFSET newline="">
				<CFFILE action="write" file="#filename#" output="" addnewline="no">
				<CFSET newline="SAMPLE ID#chr(9)#SOURCE#chr(9)#AUTHOR#chr(9)#TITLE#chr(9)#YEAR#chr(9)#LATITUDE#chr(9)#LONGITUDE#chr(9)#METHOD#chr(9)#MATERIAL#chr(9)#TYPE#chr(9)#COMPOSITION#chr(9)#ROCK NAME"><!---
				<CFSET newline="SAMPLE ID#chr(9)#SOURCE#chr(9)#LATITUDE#chr(9)#LONGITUDE#chr(9)#METHOD#chr(9)#MATERIAL#chr(9)#TYPE#chr(9)#COMPOSITION#chr(9)#ROCK NAME">--->
				<CFLOOP list="#showlist#" index="field_name">
					<CFIF trim(field_name) eq "ARSENIC">
						<CFSET showfield="AS">
					<CFELSE>
						<CFSET showfield=field_name>
					</CFIF>
					<CFSET newline="#newline##chr(9)##showfield#">
				</CFLOOP>
				<CFFILE action="append" file="#filename#" output="#newline#" addnewline="yes">
				<CFLOOP query="getsample">
					<CFSET newline="#sample_id##chr(9)##ucase(source)##chr(9)##author##chr(9)##title##chr(9)##year##chr(9)##latitude##chr(9)##longitude##chr(9)##method##chr(9)##EC_LEVEL_1##chr(9)##EC_LEVEL_2##chr(9)##EC_LEVEL_3##chr(9)##EC_LEVEL_4#">
					<CFLOOP list="#showlist#" index="currentfield">
						<CFIF isdefined("#currentfield#")>
							<CFSET newline="#newline##chr(9)##evaluate("#currentfield#")#">
						<CFELSE>
							<CFSET newline="#newline##chr(9)#">
						</CFIF>
					</CFLOOP>
<!---
					<CFSET newline = "#newline##chr(9)##citation_pkey#">eej debug 8/19/2007 --->
					<CFFILE action="append" file="#filename#" output="#newline#" addnewline="yes">
				</CFLOOP>
				<CFINCLUDE template="log_downloads_citations.cfm"><!--- Log the author citations. (We do this for xls or text output, but not for html output) --->
				<CFLOCATION url="http://geoportal.kgs.ku.edu/navdatdb/temp/earthchemOutput#randnum#.txt" addtoken="no">
				<CFABORT><!--- ******************* this is the end of the text portion ********************** --->
			<CFELSE>
<!--- below section is sample level text --->
				<CFSET randnum=randrange(0,99999)>
				<CFSET filename="D:\webware\Apache\Apache2\htdocs\navdatdb\temp/earthchemOutput#randnum#.txt">
				<CFSET newline="">
				<CFFILE action="write" file="#filename#" output="" addnewline="no"><!---
				<CFSET newline="SAMPLE ID#chr(9)#SOURCE#chr(9)#LATITUDE#chr(9)#LONGITUDE#chr(9)#MATERIAL#chr(9)#TYPE#chr(9)#COMPOSITION#chr(9)#ROCK NAME">--->
				<CFSET newline="SAMPLE ID#chr(9)#SOURCE#chr(9)#LATITUDE#chr(9)#LONGITUDE">
				<CFLOOP list="#showlist#" index="field_name">
					<CFIF trim(field_name) eq "ARSENIC">
						<CFSET showfield="AS">
					<CFELSE>
						<CFSET showfield=field_name>
					</CFIF>
					<CFSET newline="#newline##chr(9)##showfield#">
					<CFIF showunits eq "yes">
						<CFSET newline="#newline##chr(9)##showfield# UNIT">
					</CFIF>
					<CFIF showmethods eq "yes">
						<CFSET newline="#newline##chr(9)##showfield# METH">
					</CFIF>
				</CFLOOP>
				<CFFILE action="append" file="#filename#" output="#newline#" addnewline="yes">
				<CFLOOP query="getsample">
<!---
					<CFSET newline="#sample_id##chr(9)##ucase(source)##chr(9)##latitude##chr(9)##longitude##chr(9)##EC_LEVEL_1##chr(9)##EC_LEVEL_2##chr(9)##EC_LEVEL_3##chr(9)##EC_LEVEL_4#">--->
					<CFSET newline="#sample_id##chr(9)##ucase(source)##chr(9)##latitude##chr(9)##longitude#">
					<CFLOOP list="#showlist#" index="currentfield">
						<CFIF isdefined("#currentfield#")>
							<CFSET newline="#newline##chr(9)##evaluate("#currentfield#")#">
						<CFELSE>
							<CFSET newline="#newline##chr(9)#">
						</CFIF>
						<CFIF showunits eq "yes">
							<CFIF isdefined("#currentfield#_UNIT")>
								<CFSET newline="#newline##chr(9)##evaluate("#currentfield#_UNIT")#">
							<CFELSE>
								<CFSET newline="#newline##chr(9)#">
							</CFIF>
						</CFIF>
						<CFIF showmethods eq "yes">
							<CFIF isdefined("#currentfield#_METH")>
								<CFSET newline="#newline##chr(9)##evaluate("#currentfield#_METH")#">
							<CFELSE>
								<CFSET newline="#newline##chr(9)#">
							</CFIF>
						</CFIF>
					</CFLOOP>
					<CFFILE action="append" file="#filename#" output="#newline#" addnewline="yes">
				</CFLOOP>
				<CFIF rowtype neq "sample">
					<CFINCLUDE template="log_downloads_citations.cfm">
				</CFIF>
<!--- Log the author citations. (We do this for xls or text output, but not for html output) --->
				<CFLOCATION url="http://geoportal.kgs.ku.edu/navdatdb/temp/earthchemOutput#randnum#.txt" addtoken="no">
				<CFABORT><!--- ******************* this is the end of the text portion ********************** --->
			</CFIF>
		<CFELSEIF url.dispmode eq "xls"><!--- ******************* this is the start of the xls portion ********************** --->
			<CFIF rowtype eq "method">
				<CFSET randnum=randrange(0,99999)>
				<CFSET filename="D:\webware\Apache\Apache2\htdocs\navdatdb\temp/earthchemOutput#randnum#.xls">
				<CFSET newline="">
				<CFFILE action="write" file="#filename#" output="" addnewline="no">
				<CFSET newline="SAMPLE ID#chr(9)#SOURCE#chr(9)#AUTHOR#chr(9)#TITLE#chr(9)#YEAR#chr(9)#LATITUDE#chr(9)#LONGITUDE#chr(9)#METHOD#chr(9)#MATERIAL#chr(9)#TYPE#chr(9)#COMPOSITION#chr(9)#ROCK NAME">
				<CFLOOP list="#showlist#" index="field_name">
					<CFIF trim(field_name) eq "ARSENIC">
						<CFSET showfield="AS">
					<CFELSE>
						<CFSET showfield=field_name>
					</CFIF>
					<CFSET newline="#newline##chr(9)##showfield#">
				</CFLOOP>
				<CFFILE action="append" file="#filename#" output="#newline#" addnewline="yes">
				<CFLOOP query="getsample">
					<CFSET newline="#sample_id##chr(9)##ucase(source)##chr(9)##author##chr(9)##title##chr(9)##year##chr(9)##latitude##chr(9)##longitude##chr(9)##method##chr(9)##EC_LEVEL_1##chr(9)##EC_LEVEL_2##chr(9)##EC_LEVEL_3##chr(9)##EC_LEVEL_4#">
					<CFLOOP list="#showlist#" index="currentfield">
						<CFIF isdefined("#currentfield#")>
							<CFSET newline="#newline##chr(9)##evaluate("#currentfield#")#">
						<CFELSE>
							<CFSET newline="#newline##chr(9)#">
						</CFIF>
					</CFLOOP>
					<CFFILE action="append" file="#filename#" output="#newline#" addnewline="yes">
				</CFLOOP>
				<CFINCLUDE template="log_downloads_citations.cfm"><!--- Log the citations. (We do this only for xls or text output, not html output.) --->
				<CFLOCATION url="http://geoportal.kgs.ku.edu/navdatdb/temp/earthchemOutput#randnum#.xls" addtoken="no">
				<CFABORT><!--- ******************* this is the end of the xls portion ********************** --->
			<CFELSE>
<!--- section below is xls sample level output --->
				<CFSET randnum=randrange(0,99999)>
				<CFSET filename="D:\webware\Apache\Apache2\htdocs\navdatdb\temp/earthchemOutput#randnum#.xls">
				<CFSET newline="">
				<CFFILE action="write" file="#filename#" output="" addnewline="no">
				<CFSET newline="SAMPLE ID#chr(9)#SOURCE#chr(9)#LATITUDE#chr(9)#LONGITUDE#chr(9)#MATERIAL#chr(9)#TYPE#chr(9)#COMPOSITION#chr(9)#ROCK NAME">
				<CFLOOP list="#showlist#" index="field_name">
					<CFIF trim(field_name) eq "ARSENIC">
						<CFSET showfield="AS">
					<CFELSE>
						<CFSET showfield=field_name>
					</CFIF>
					<CFSET newline="#newline##chr(9)##showfield#">
					<CFIF showmethods eq "yes">
						<CFSET newline="#newline##chr(9)##showfield# METH">
					</CFIF>
				</CFLOOP>
				<CFFILE action="append" file="#filename#" output="#newline#" addnewline="yes">
				<CFLOOP query="getsample">
					<CFSET newline="#sample_id##chr(9)##ucase(source)##chr(9)##latitude##chr(9)##longitude##chr(9)##EC_LEVEL_1##chr(9)##EC_LEVEL_2##chr(9)##EC_LEVEL_3##chr(9)##EC_LEVEL_4#">
					<CFLOOP list="#showlist#" index="currentfield">
						<CFIF isdefined("#currentfield#")>
							<CFSET newline="#newline##chr(9)##evaluate("#currentfield#")#">
						<CFELSE>
							<CFSET newline="#newline##chr(9)#">
						</CFIF>
						<CFIF showmethods eq "yes">
							<CFIF isdefined("#currentfield#_METH")>
								<CFSET newline="#newline##chr(9)##evaluate("#currentfield#_METH")#">
							<CFELSE>
								<CFSET newline="#newline##chr(9)#">
							</CFIF>
						</CFIF>
					</CFLOOP>
					<CFFILE action="append" file="#filename#" output="#newline#" addnewline="yes">
				</CFLOOP>
				<CFIF rowtype neq "sample">
					<CFINCLUDE template="log_downloads_citations.cfm"><!--- Log the citations. (We do this only for xls or text output, not html output.) --->
				</CFIF>
				<CFLOCATION url="http://geoportal.kgs.ku.edu/navdatdb/temp/earthchemOutput#randnum#.xls" addtoken="no">
				<CFABORT><!--- ******************* this is the end of the xls portion ********************** --->
			</CFIF>
		</CFIF>
	<CFELSE>
<!--- end if samplecount gt 0 --->
		<CFINCLUDE template="includes/ks_head.html">
	<CENTER><H2>	Sorry, No Records Found.
</H2></CENTER>
<SCRIPT language="JavaScript" type="text/javascript" src="wz_tooltip.js"></SCRIPT>
		<CFINCLUDE template="includes/ks_footer.html">
	</CFIF>
</CFOUTPUT>
