<?PHP
/**
 * setmethod.php
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

include('get_pkey.php'); // the primary key(s)
include('db.php'); // database drivers, and connect
if (isset($_POST['method'])) { //<CFIF isdefined("form.method")>
	//<CFDUMP VAR="#form#">
	$getsearch=$db->get_row("SELECT * FROM search_query WHERE pkey=$pkey");
	$newmethlist_array=array();
	$methlist_array=split(':',$getsearch->chemmethods);
	//<CFSET methlistlen=ListLen(methlist)>
	//<CFLOOP INDEX="methnum" FROM="1" TO="#methlistlen#">
	foreach($methlist_array as $currentcell) {
		//<CFSET currentcell=ListGetAt(methlist, methnum)>
		//<CFIF REFindNoCase("^#form.item#:", "#currentcell#") eq 0>
		//<CFSET newmethlist=ListAppend(newmethlist, #currentcell#)>
		//</CFIF>
		if (in_array($_GET['item'],$methlist_array)) {
			$newmethlist_array[] = $currentcell;
		}
	} // foreach  //</CFLOOP>
	$line=$_POST['item']; //<CFSET LINE="#form.item#">
	foreach($method_array as $currmethod) { //<CFLOOP INDEX="currmethod" LIST="#form.method#">
		$line .= ":$currmethod"; //<CFSET LINE=LINE&":#currmethod#">
	} //</CFLOOP>
	$newmethlist_array[]=$line; //<CFSET newmethlist=ListAppend(newmethlist, "#LINE#")>
	$newmethlist=implode(':',$newmethlist_array);
	$updatesearch=$db->query("UPDATE search_query SET chemmethods='$newmethlist' WHERE pkey=$pkey");
	echo '<a href="Set.cfm?pkey=$pkey&SetParameter=Chemistry">return</a>';
} elseif (isset($_GET['item'])) { // if   //</CFIF>
$item=strtolower($_GET['item']);
$getchemmethods=$db->get_row("SELECT chemmethods from search_query where pkey=$pkey");
$getchemmethods_array=split(':',$getchemmethods->chemmethods); 
$mymethlist="";
//foreach($chemmethods_array as $currmeth) { //<cfloop list="#getchemmethods.chemmethods#" index="currmeth">
if (in_array($_GET['item'],$getchemmethods_array)) { // == $currmeth) { //<cfif REFindNoCase("^#url.item#:", "#currmeth#") gt 0>
		//<cfset myflag="no">
		//<cfset mydelim="">
		//<cfloop list="#currmeth#" index="mycurr" delimiters=":">
		//<cfif myflag eq "yes">
			//<cfset mymethlist="#mymethlist##mydelim##mycurr#">
			//<cfset mydelim=",">
		//</cfif>
		//<cfset myflag="yes">
		//</cfloop>
		//<cfset showblanks="no">
	$mymethlist = $mymethlist . ':'. $_GET['item'];
} // if    </cfif>
//} // foreach   </cfloop>

$dblist=$getchemmethods->chemmethods; //<CFSET dblist=getchemmethods.chemmethods>

$methodlist=''; //<CFSET methodlist="">
/*
<CFIF REFindNoCase("^#url.item#:", "#dblist#") gt 0>
	<CFSET dblistpos=ListContainsNoCase(dblist, url.item)>
	<CFIF dblistpos gt 0>
		<CFSET methodlist=ListGetAt(dblist, dblistpos)>
	</CFIF>
</CFIF>

<CFIF REFindNoCase(",#url.item#:", "#dblist#") gt 0>
	<CFSET dblistpos=ListContainsNoCase(dblist, url.item)>
	<CFIF dblistpos gt 0>
		<CFSET methodlist=ListGetAt(dblist, dblistpos)>
	</CFIF>
</CFIF>
<!---
methodlist: #methodlist#<br>
--->*/
$getmethodgroups=$db->get_results("SELECT DISTINCT(meth.methodname) FROM item it, method meth WHERE it.method_pkey = meth.method_pkey AND it.itemname='$item'");
?>
<br>
<form action="" method="post">
<table bgcolor="#FFFFFF" width="400" border="0" align="center" cellpadding="3" cellspacing="1">
<tr>
<td><br><div align="center"><FONT face="arial, sans-serif" size=3 color="black" style="font-weight:BOLD;text-decoration:none">Methods Available - <?=$item?>:</font><br>
<br>
<div align="center">Choose which method to search for below.<br>
To choose more than one method, hold down the CTRL key.</div><br>
<select name="boxy[]" id="boxy" size="10" multiple>
<?php
// name must be an array so php can receive the form variable as an array, but id should be scalar type so it doesn't confuse the javascript 
foreach($getmethodgroups as $g) {
	echo '<option ';
	if (in_array($g->methodname,$getchemmethods_array)) {echo ' selected ';}
	echo ' value="'.$g->methodname.'">'.$g->methodname.'</option>';
//<cfif listfind("#mymethlist#","#name#") gt 0> selected</cfif>>
}
?>
</select>
<br>
<br>
<input type="hidden" name="item" value="<?=$item?>"> 
<input type="hidden" name="pkey" value="<?=$pkey?>">

<input OnClick="greet('<?=strtoupper($item)?>',<?=$pkey?>); tb_remove();" type="button" name="Submit" value="Submit">

</div><br></td>
</tr>
</table>
</form>
<?php
} // if
?>

