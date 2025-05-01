<?PHP
/**
 * holdings.php
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


//holdings.php
include("db.php");


$disp=$_GET['disp'];

if($disp==""){
	$disp="html";
}

//echo json_encode($_SERVER);

$rows=$db->get_results("select * from holdings where displaythis=true order by pkey");

if($disp=="html"){

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>EarthChem Holdings</title>
<style type="text/css">
form{display:inline; }
a:hover{color:#000000; text-decoration:underline; font-weight: bold; }
a:link,a:visited{color:#000000; text-decoration:none; font-weight: bold; }
a:hover{color:#000000; text-decoration:underline; font-weight: bold; }
body,table,td,th,p,div,input {

	font-family: verdana, sans-serif;

	font-size: 10pt;
	
	line-height: 13pt;

}

body {
	background-color: #FFFFFF;
	margin-left: 5px;
	margin-top: 5px;
	margin-right: 0px;
	margin-bottom: 0px;
}
</style>
</head>

<body>
<table border="0" cellpadding="3" cellspacing="1" bgcolor="#999999">

	<tr>
		<td bgcolor="#EEEEEE" nowrap><div style="color:#000000;font-weight:bold;">Partner Database</div></td>
		<td bgcolor="#EEEEEE" nowrap><div style="color:#000000;font-weight:bold;">Total References</div></td>
		<td bgcolor="#EEEEEE" nowrap><div style="color:#000000;font-weight:bold;">Total Samples</div></td>
		<td bgcolor="#EEEEEE" nowrap><div style="color:#000000;font-weight:bold;">Total Chemical Values</div></td>
	</tr>
<?




	foreach($rows as $row){
	$showtitle=$row->showtitle;
	$citations=number_format($row->citations);
	$samples=number_format($row->samples);
	$analyses=number_format($row->analyses);
	
	if($analyses==""){
		$analyses="n/a";
	}
	
?>

	<tr>
		<td bgcolor="#FFFFFF" nowrap><div style="color:#000000;"><?=$showtitle?></div></td>
		<td bgcolor="#FFFFFF" nowrap><div style="color:#000000;"><?=$citations?></div></td>
		<td bgcolor="#FFFFFF" nowrap><div style="color:#000000;"><?=$samples?></div></td>
		<td bgcolor="#FFFFFF" nowrap><div style="color:#000000;"><?=$analyses?></div></td>
	</tr>

<?
	
	
	
	

	
	
	
	}
	
?>

</table>
</body>
</html>

<?
	
}elseif($disp=="xml"){

	$xml="<results>\n";

	foreach($rows as $row){
		
		$xml.="\t<row>\n";
	
		$showtitle=$row->showtitle;
		$citations=number_format($row->citations);
		$samples=number_format($row->samples);
		$analyses=number_format($row->analyses);
		
		if($analyses==""){
			$analyses="n/a";
		}
		
		$xml.="\t\t<name>$showtitle</name>\n";
		$xml.="\t\t<citations>$citations</citations>\n";
		$xml.="\t\t<samples>$samples</samples>\n";
		$xml.="\t\t<analyses>$analyses</analyses>\n";
		
		$xml.="\t</row>\n";
	}
	
	//$xml.="\t\t<lastdataingestion>12-15-2011</lastdataingestion>\n";
	
	$xml.="</results>";
	
	header("Content-type: text/xml");
	
	echo $xml;

}

/*

	<tr>
		<td colspan="4" bgcolor="#FFFFFF" nowrap><div style="color:#000000;">
			<div align="center">Last Data Ingestion: 12-15-2011</div>
		</td>
	</tr>



*/
?>
