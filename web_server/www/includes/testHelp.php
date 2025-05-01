
<!DOCTYPE html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head><title>EarthChem Portal</title>
<link href="http://geoportal.kgs.ku.edu/earthchem/portal/includes/ks_earthchem.css" rel="stylesheet" type="text/css" />
<script language="JavaScript1.2" src="http://earthchem.org:/earthchemWeb/scripts/page.js" type="text/javascript"></script>
<script language="JavaScript1.2" src="http://earthchem.org:/earthchemWeb/scripts/floatMenu.js" type="text/javascript"></script>
<!-- earthchem_help.js and earthchem-help-changeContent.js are required by the popup Help system. -->
<!--<script src="http://neko.kgs.ku.edu/harker/earthchem-help-changeContent.js" type="text/javascript"></script>
<script src="http://neko.kgs.ku.edu/earthchem/earthchem_help.js" type="text/javascript"></script>
-->
<script src="http://matisse.kgs.ku.edu/earthchemphp2/includes/earthchem-help-changeContent.js" type="text/javascript"></script>
<script src="http://matisse.kgs.ku.edu/earthchemphp2/includes/earthchem_help.js" type="text/javascript"></script>




</head>





<body style="min-height:100%;min-width:100%;margin:0;padding:0;position:absolute;top:0px;left:0px;"><a name="top"></a>

<div id="thecontent" style="min-height:100%;overflow:visible;
background:url(http://geoportal.kgs.ku.edu/earthchem/portal/images/earthchemLeftBack.gif) repeat-y;  position:absolute;top:0;left:0;position:absolute;top:0;left:0;margin-left:0px;margin-right:20px;">
<img src="http://geoportal.kgs.ku.edu/earthchem/portal/images/earthchemTop.gif" width="838" height="91" hspace="0" vspace="0" border="0" alt="EarthChem" ismap usemap="#topmap"><br>
<table id="themaintable" width="838" height="100%" border="0" cellspacing="0" cellpadding="0" style="">
<tr>

<td colspan=2 width="154" valign="top">
<img src="http://geoportal.kgs.ku.edu/earthchem/portal/images/earthchemTopCurve.gif" width="154" height="31" border="0" alt="" hspace="0" vspace="0"></td>
<td></td>
</tr>
<tr>
<td width="123" rowspan="2" valign="top" style="width:123px;background:url(http://geoportal.kgs.ku.edu/earthchem/portal/includes/leftBack.gif) repeat-y;padding:0px"><!-- The left-hand menu -->
<div id="menu" onMouseOut="hideAll()">

<a href="http://earthchem.org" class="menu1">Home</a>

<a href="#" name="data" id="data" class="menu1" onMouseOver="showBlock(this, 'data_sub')" onMouseOut="hideAll();" onClick="return false;">Data</a>
<div id="data_sub" class="menuLayer" onMouseOver="stayOn(this)">
<a href="http://www.earthchem.org/earthchemWeb/data.jsp" name="data_intro" id="data_intro" class="menu2"  onMouseOver="stayOn(this)" onMouseOut="hideAll();">
About Data</a>

<a href="http://www.earthchem.org/earthchemWeb/search.jsp" name="data_search" id="data_search" class="menu2" onMouseOver="stayOn(this)" onMouseOut="hideAll();">
Search Data
</a>
<a href="http://www.earthchem.org/earthchemWeb/contribute.jsp" name="data_cont" id="data_cont" class="menu2" onMouseOver="stayOn(this)" onMouseOut="hideAll();">
Contribute
</a>
<a href="http://www.earthchem.org/earthchemWeb/citation.jsp" name="data_cite" id="data_cite" class="menu2" onMouseOver="stayOn(this)" onMouseOut="hideAll();">
How to Cite Data
</a> 
</div><!-- end data_sub -->

<a href="#" name="comm" id="comm" class="menu1" onMouseOver="showBlock(this, 'comm_sub')" onMouseOut="hideAll();" onClick="return false;">Community</a>
<div id="comm_sub" class="menuLayer" onMouseOver="stayOn(this)">
<a href="http://www.earthchem.org/earthchemWeb/workshops.jsp" name="comm_workshops" id="comm_workshops" class="menu2" onMouseOver="stayOn(this)" onMouseOut="hideAll();">
Workshops</a>

<a href="http://www.earthchem.org/earthchemWeb//edu.jsp" name="comm_edu" id="comm_edu" class="menu2" onMouseOver="stayOn(this)" onMouseOut="hideAll();">
Education</a>
<a href="http://www.earthchem.org/earthchemWeb/partnerships.jsp" name="comm_part" id="comm_part" class="menu2" onMouseOver="stayOn(this)" onMouseOut="hideAll();">
Partnerships</a> 
<a href="http://www.earthchem.org/earthchemWeb/whatsnew.jsp#sub" name="comm_sub" id="comm_sub" class="menu2" onMouseOver="stayOn(this)" onMouseOut="hideAll();">
Subscribe</a> 
</div><!-- end comm_sub -->

<a href="#" name="svcs" id="svcs" class="menu1" onMouseOver="showBlock(this, 'svcs_sub')" onMouseOut="hideAll();" onClick="return false;">Services</a>
<div id="svcs_sub" class="menuLayer" onMouseOver="stayOn(this)">
<a href="http://www.earthchem.org/earthchemWeb/tools.jsp" name="svcs_tools" id="svcs_tools" class="menu2" onMouseOver="stayOn(this)" onMouseOut="hideAll();">
Tools</a>

<a href="http://www.earthchem.org/earthchemWeb/standards.jsp" name="svcs_stand" id="svcs_stand" class="menu2" onMouseOver="stayOn(this)" onMouseOut="hideAll();">
Standards</a>
<a href="http://www.earthchem.org/earthchemWeb/developers.jsp" name="svcs_dev" id="svcs_dev" class="menu2" onMouseOver="stayOn(this)" onMouseOut="hideAll();">
For Developers
</a> 
</div><!-- end svcs_sub -->

<a  href="#" name="supp" id="supp" class="menu1" onMouseOver="showBlock(this, 'supp_sub')" onClick="return false;" onMouseOut="hideAll();">Support</a>
<div id="supp_sub" class="menuLayer" onMouseOver="stayOn(this)">
<a href="http://www.earthchem.org/earthchemWeb/help.jsp" name="supp_help" id="supp_" class="menu2" onMouseOver="stayOn(this)" onMouseOut="hideAll();">
Help
</a>
<a href="http://www.earthchem.org/earthchemWeb/contact_us.jsp" name="supp_cont" id="supp_cont" class="menu2" onMouseOver="stayOn(this)" onMouseOut="hideAll();">
Contact Us
</a>

</div><!-- end supp_sub -->

<a  href="#" name="gen" id="gen" class="menu1" onMouseOver="showBlock(this, 'gen_sub')" onMouseOut="hideAll()" onClick="return false">General Info</a>
<div id="gen_sub" class="menuLayer" onMouseOver="stayOn(this)">
<a href="http://www.earthchem.org/earthchemWeb/about.jsp" name="gen_abt" id="gen_abt" class="menu2" onMouseOver="stayOn(this)" onMouseOut="hideAll();">
About EarthChem</a>
<a href="http://www.earthchem.org/earthchemWeb/whatsnew.jsp" name="gen_news" id="gen_news" class="menu2" onMouseOver="stayOn(this)" onMouseOut="hideAll();">
News</a>
<a href="http://www.earthchem.org/earthchemWeb/pubs.jsp" name="gen_pub" id="gen_pub" class="menu2" onMouseOver="stayOn(this)" onMouseOut="hideAll();">
Publications</a>
<a href="http://www.earthchem.org/earthchemWeb/advisory.jsp" class="menu2" name="gen_adv" id="gen_adv" class="menu2" onMouseOver="stayOn(this)" onMouseOut="hideAll();">Advisory Committee
</a>
</div><!-- end gen_sub -->

<a  href="#" name="gloss" id="gloss" class="menu1" onMouseOver="showBlock(this, 'gloss_sub')" onClick="return false;" onMouseOut="hideAll();">Glossary</a>
<div id="gloss_sub" class="menuLayer" onMouseOver="stayOn(this)">
<a href="http://geoportal.kgs.ku.edu/earthchem/portal/glossary.cfm" name="gloss_home" id="gloss_home" class="menu2" onMouseOver="stayOn(this)" onMouseOut="hideAll();">
Glossary Home
</a>
<a href="http://geoportal.kgs.ku.edu/earthchem/portal/login.cfm" name="gloss_login" id="gloss_login" class="menu2" onMouseOver="stayOn(this)" onMouseOut="hideAll();">
Log in
</a>
<a href="http://geoportal.kgs.ku.edu/earthchem/portal/logout.cfm" name="gloss_logout" id="gloss_logout" class="menu2" onMouseOver="stayOn(this)" onMouseOut="hideAll();">
Log out
</a>
<a href="http://geoportal.kgs.ku.edu/earthchem/portal/register.cfm" name="gloss_reg" id="gloss_reg" class="menu2" onMouseOver="stayOn(this)" onMouseOut="hideAll();">
Register
</a>
</div><!-- end gloss_sub -->

<a  href="http://geoportal.kgs.ku.edu/earthchem/portal/search_citations.cfm" class="menu1">Citations</a>

<a  href="#" name="login" id="login" class="menu1" onMouseOver="showBlock(this, 'account_sub')" onClick="return false;" onMouseOut="hideAll();">Account</a>
<div id="account_sub" class="menuLayer" onMouseOver="stayOn(this)">
<a href="http://geoportal.kgs.ku.edu/earthchem/portal/login.cfm" name="account_login" id="account_login" class="menu2" onMouseOver="stayOn(this)" onMouseOut="hideAll();">
Log in
</a>
<a href="http://geoportal.kgs.ku.edu/earthchem/portal/logout.cfm" name="account_logout" id="account_logout" class="menu2" onMouseOver="stayOn(this)" onMouseOut="hideAll();">
Log out
</a>
<a href="http://geoportal.kgs.ku.edu/earthchem/portal/register.cfm" name="account_reg" id="account_reg" class="menu2" onMouseOver="stayOn(this)" onMouseOut="hideAll();">
Register
</a>
</div><!-- end account_sub -->

<a  href="http://geoportal.kgs.ku.edu/earthchem/portal/index.cfm" class="menu1">Search Portal Data</a>

</div><!--div menu-->

</td>

<td rowspan="2" width="31" valign="top"><!--The white gutter and a kludge for Opera: vertical spacer, to force a min height for this table -->
<script type="text/javascript">
var minHeight = document.body.clientHeight  -155 // the client height minus the top banner, topCurve, footer if we are displaying it, and some extra
document.write('<img src="http://geoportal.kgs.ku.edu/earthchem/portal/images/spacer.gif" hspace=0 vspace=0 border=0 width=31 height=' + minHeight + ' alt="">')
</script>
</td>

<td valign="top">

<!-- The page header ends here. -->




<!----------------------just after the header------------------------------------------------------>


<img onClick="javascript:showHelp(event,'xyz-selectxyz')" src="help.gif" class="helpbutton" border="0" alt="xyz-selectxyz">



<!-- The page footer begins here. -->


<div id="helpLayer" style="position:absolute;left:600;top:50;width:300px;visibility:hidden">
	<table cellspacing="0" cellpadding="0" border="0" style="background-color:white;border:1px #003466 solid;">
		<tr>
			<td id="titleBar" style="background-color:#003466;cursor:move;padding:8px;">
				<div width="100%" onselectstart="return false">
					<div width="100%" onMouseOver="isHot=true;if (isN4) ddN4(helpLayer)" onMouseOut="isHot=false" style="font-size:1.2em;font:arial;font-weight:bold;color:white;">EarthChem Help
					</div>

				</div>
			</td>
			<td width="20" style="width:20px;background-color:#003466;cursor:move;border:1px #003466 solid;padding:8px;text-align:right"><a href="#" onClick="hideMe();return false" style="font:arial;font-weight:bold;color:white;padding-top:8px;padding-bottom:8px">			close
</a>
			</td>
		</tr>
		<tr>
			<td bgcolor="#FFFFFF" style="padding:8px;border:1px #003466 solid;background-color:#e9e9e9" colspan="2">
				<div id='TitleText' onClick="hideMe();return false">help contents
				</div>

			</td>
		</tr>
		<tr>
			<td colspan="2" style="padding:5 8 5 8;border:1px #003466 solid;font-size:1em;font-style:italic;background-color:#cccccc" onClick="hideMe();return false">To drag this box, place cursor in the blue bar.
			</td>
		</tr>
	</table>
</div><!--helpLayer-->



</body>

</html>
























<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Untitled Document</title>
</head>

<body>
</body>
</html>
