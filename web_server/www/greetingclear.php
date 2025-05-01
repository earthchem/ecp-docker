<?PHP
/**
 * greetingclear.php
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

/*<!--- url.item url.pkey --->

<CFQUERY NAME="getsearch" DATASOURCE="#datasource#">
	SELECT * FROM search_query WHERE pkey=#url.pkey#
</CFQUERY>

<CFSET newmethlist="">

<CFSET methlist=getsearch.chemmethods>
<!---
list: #methlist#<br><br>
--->
<CFSET methlistlen=ListLen(methlist)>

<CFLOOP INDEX="methnum" FROM="1" TO="#methlistlen#">

	<CFSET currentcell=ListGetAt(methlist, methnum)>

	<CFIF REFindNoCase("^#url.item#:", "#currentcell#") eq 0>

		<CFSET newmethlist=ListAppend(newmethlist, #currentcell#)>

	</CFIF>

</CFLOOP>

<!---
newmethlist:#newmethlist#<br><br>
--->
<CFQUERY NAME="updatesearch" DATASOURCE="#datasource#">
	UPDATE search_query SET chemmethods='#newmethlist#' WHERE pkey=#url.pkey#
</CFQUERY>

<CFOUTPUT>ooo</CFOUTPUT>
*/
?>