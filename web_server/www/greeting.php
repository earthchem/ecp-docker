<?PHP
/**
 * greeting.php
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

// This page is part of the 'set method' feature for the 'set chemistry' page. If a user chooses a chemical (item), he can also choose one or more methods for that item. The chemical items are stored in searchquery.chemistry and the methods are stored in searchquery.chemmethods in the format  item:method;method;method;item:method;method 

// This evaluates and prints a form field - a select list that allows multiple options chosen. The javascript that calls this script will update elements on another page. 

// Note: Eileen has taken the liberty of slightly altering this logic. Instead of updating the database with the new methods here (before the user has clicked the APPLY button on the setchemistry page to save his edits), this page (called by a javascript script in ajax.js) merely displays the methods, which the javascript puts into a hidden form field as well as a display field on the setchemistry page. In this way the methods are not saved unless the user chooses to save the choices in setchemistry.php. 

$methodlist=urldecode($_GET['boxy']); // boxy is the name of the select type of form field in setmethod.php; it's a list of one or more methods for chemistry 
echo $methodlist; // The javascript that calls this will now replace elements in setchemistry.php with $methodlist 

/*<CFSET greeting=url.boxy>
<CFSET method=url.boxy>
<CFSET item=url.item>
<CFSET pkey=url.pkey>

<cfset method=urldecode(method)>

<CFQUERY NAME="getsearch" DATASOURCE="#datasource#">
	SELECT * FROM search_query WHERE pkey=#pkey#
</CFQUERY>

<CFSET newmethlist="">

<CFSET methlist=getsearch.chemmethods>
<!---
list: #methlist#<br><br>
--->
<CFSET methlistlen=ListLen(methlist)>

<CFLOOP INDEX="methnum" FROM="1" TO="#methlistlen#">

	<CFSET currentcell=ListGetAt(methlist, methnum)>

	<CFIF REFindNoCase("^#item#:", "#currentcell#") eq 0>

		<CFSET newmethlist=ListAppend(newmethlist, #currentcell#)>

	</CFIF>

</CFLOOP>

<CFSET LINE="#item#">

<CFLOOP INDEX="currmethod" LIST="#method#">
	<CFSET LINE=LINE&":#currmethod#">
</CFLOOP>

<CFSET newmethlist=ListAppend(newmethlist, "#LINE#")>
<!---
newmethlist:#newmethlist#<br><br>
--->
<CFQUERY NAME="updatesearch" DATASOURCE="#datasource#">
	UPDATE search_query SET chemmethods='#newmethlist#' WHERE pkey=#pkey#
</CFQUERY>


<CFOUTPUT>#ucase(urldecode(greeting))#</CFOUTPUT>
*/