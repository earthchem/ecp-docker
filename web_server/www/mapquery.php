<?PHP
/**
 * mapquery.php
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

//Data Source
$legitem=$_GET['legitem'];

if($legitem=="" or $legitem=="undefined"){
	$legitem="Data Source";
}

//echo "$legitem<br>";

include('db.php');
include("datasources.php");
include('srcwhere.php');

include("get_pkey.php");

$mylon=$_GET['mylon'];
$mylat=$_GET['mylat'];
$myzoom=$_GET['zoom'];


//build methodarray
//$methrows


//build unitarray
//$unitrows


$itemnames=$db->get_results("SELECT DISTINCT(lower(field_name)) as field_name, display_name, DATA_ORDER, group_name
								FROM data_fields 
								WHERE showfield=TRUE and group_name!='LEPR'
								ORDER BY DATA_ORDER ASC");


/*
echo "navdat: $navdat<br>";
echo "petdb: $petdb<br>";
echo "georoc: $georoc<br>";
echo "usgs: $usgs<br>";
*/



if($_GET['sample_pkey']!=""){

	$sample_pkey=$_GET['sample_pkey'];
	
	//echo "sample_pkey=$sample_pkey<br>";
	
	$myrow=$db->get_row("select *
		FROM 	vmatreported
		WHERE
				sample_pkey = $sample_pkey limit 1");
	
	
	//check whether to show items
	foreach($itemnames as $it){
		eval("\$thisval = \$myrow->".$it->field_name.";");
		eval("\$thismethodname = \$myrow->".$it->field_name."_meth;");
		eval("\$thisunitname = \$myrow->".$it->field_name."_unit;");

		if($thisval!=""){
			//echo $it->display_name."= $thisval $thisunitname $thismethodname<br>";
			$showitems="yes";
		}
		
	
	}



?>
	<table width="350" cellpadding="2" cellspacing="1" bgcolor="#333333">
  	<tr>
    	<td bgcolor="#333333">
		<table width="100%">
		<tr>
		<td><font color="#FFFFFF"><strong>Sample Details: </strong></font></td>
		
		<?
		$jslink="$mylat,$mylon,$pkey";
		foreach($datasources as $ds){
			eval("\$thisval=\$".$ds->name.";");
			$jslink.=",$thisval";
		}
		$jslink.=",$myzoom,'$legitem'";
		
		?>
		
		<td><div align="right"><a href="javascript:show_my_form(<?=$jslink?>);" ><font color="#FFFFFF"><strong>back</strong></font></a></div></td>
		<td>&nbsp;</td>
		</tr>
		</table>
		</td>
  	</tr>
  	<tr>
  	</table>
	<table width="350" cellspacing="1" cellpadding="2">
		<tr>
			<td nowrap>Source:</td>
			<td><?=strtoupper($myrow->source)?></td>
		</tr>
		<tr>
			<td nowrap>Sample ID:</td>
			<td><?=$myrow->sample_id?></td>
		</tr>
		<tr>
			<td nowrap>Rock Type:</td>
			<td>
<?
$rockdelim="";
if($myrow->class1!=""){echo $rockdelim.$myrow->class1;$rockdelim=", ";}
if($myrow->class2!=""){echo $rockdelim.$myrow->class2;$rockdelim=", ";}
if($myrow->class3!=""){echo $rockdelim.$myrow->class3;$rockdelim=", ";}
if($myrow->class4!=""){echo $rockdelim.$myrow->class4;$rockdelim=", ";}
?>
			</td>
		</tr>
	</table>




<?
	foreach($itemnames as $it){
		eval("\$thisval = \$myrow->".$it->field_name.";");
		eval("\$thismethodname = \$myrow->".$it->field_name."_meth;");
		eval("\$thisunitname = \$myrow->".$it->field_name."_unit;");	
	
		if($thisval!=""){
			//echo "<tr><td bgcolor=\"#FFFFFF\">".$it->display_name."</td><td bgcolor=\"#FFFFFF\">$thisval $thisunitname</td><td bgcolor=\"#FFFFFF\">$thismethodname</td></tr>\n";
			$showitems="yes";
		}
		
	
	}
?>




	<div style="padding : 4px; width : 375px; height : 200px; overflow : auto; ">

<?
if($showitems=="yes"){
?>
	<table width="350" cellspacing="1" cellpadding="2">

	<tr><td>ITEM</td><td>VALUE</td><td>METHOD</td></tr>

<?


	foreach($itemnames as $it){
		eval("\$thisval = \$myrow->".$it->field_name.";");
		eval("\$thismethodname = \$myrow->".$it->field_name."_meth;");
		eval("\$thisunitname = \$myrow->".$it->field_name."_unit;");

		if($thisunitname!=""){
			$thisunitname="($thisunitname)";
		}
		
		if($thisval!=""){
			echo "<tr><td bgcolor=\"#FFFFFF\">".$it->display_name."</td><td bgcolor=\"#FFFFFF\">$thisval $thisunitname</td><td bgcolor=\"#FFFFFF\">$thismethodname</td></tr>\n";
			$showitems="yes";
		}
		
	
	}



?>

      	</table>
<?
}//end if showitems eq yes
?>
	</div>
	
	<br>
	
	<table width="350" cellpadding="2" cellspacing="1">
		<tr>
			<td>Detail Link:</td>
			<td><a href="<?=$myrow->url?>" target="_blank">Click</a></td>
		</tr>
	</table>




<?














	


}else{ //get sample_pkey is not set, let's query based on lat/long
	
	
	
	
	
	include('srcwhere.php');
	
	include('queries.php');
	
	/*
	if($myzoom==0){$mymult=3;}
	if($myzoom==1){$mymult=2;}
	if($myzoom==2){$mymult=1.5;}
	if($myzoom==3){$mymult=.8;}
	if($myzoom==4){$mymult=.4;}
	if($myzoom==5){$mymult=.2;}
	if($myzoom==6){$mymult=.07;}
	if($myzoom==7){$mymult=.05;}
	if($myzoom==8){$mymult=.025;}
	if($myzoom==9){$mymult=.0125;}
	if($myzoom==10){$mymult=.00625;}
	if($myzoom==11){$mymult=.003125;}
	if($myzoom==12){$mymult=.0015625;}
	if($myzoom==13){$mymult=.00078125;}
	if($myzoom==14){$mymult=.000390625;}
	if($myzoom==15){$mymult=.0001953125;}
	*/
	
	if($myzoom==0){$mymult=2.25;}
	if($myzoom==1){$mymult=1;}
	if($myzoom==2){$mymult=0.5;}
	if($myzoom==3){$mymult=0.25;}
	if($myzoom==4){$mymult=0.125;}
	if($myzoom==5){$mymult=0.07;}
	if($myzoom==6){$mymult=0.035;}
	if($myzoom==7){$mymult=0.0175;}
	if($myzoom==8){$mymult=0.009;}
	if($myzoom==9){$mymult=0.0045;}
	if($myzoom==10){$mymult=0.00225;}
	if($myzoom==11){$mymult=0.0012;}
	if($myzoom==12){$mymult=0.0006;}
	if($myzoom==13){$mymult=0.000325;}
	if($myzoom==14){$mymult=0.00015;}
	if($myzoom==15){$mymult=0.000075;}


	//$mymult=1;
	
	//$mymult=1-((66.6*$myzoom)/1000);
	//$mymult=66.6*$myzoom;
	//$mymult=$mymult/1000;
	//$mymult=1-$mymult;
	
	//echo "$mymult<br>";
	
	$left=$mylon-$mymult;
	$right=$mylon+$mymult;
	$top=$mylat+$mymult;
	$bottom=$mylat-$mymult;
	
	//ST_Contains(GeomFromText('Polygon((-124.31875 44.2875,-115.0375 46.3125,-114.75625 45.4125,-124.43125 42.99375,-124.31875 44.2875))'),loc.mypoint)
	
	$coordbox="$left $top,$right $top,$right $bottom,$left $bottom,$left $top";
	
	//echo "select * from vmatreported where longitude > $left and longitude < $right and latitude > $bottom and latitude < $top";
	
	//$mapstring.=" and ST_Contains(GeomFromText('Polygon(($coordbox))'),crd.mypoint)";
	
	//$mapstring.=" and GeomFromText('Polygon(($coordbox))') ~ crd.mypoint limit 25";
	
	
	//echo "$mapstring";
	
	//echo $mapstring;
	//echo $legendmapstring;

	if($legitem=="Data Source"){
		$myquerystring=$mapstring;
	}else{
		$myquerystring="select 
			longitude,
			latitude,
			source,
			sample_id,
			sample_pkey,
			$legitem
	from vmatreported
	where	sample_pkey in (
			$legendmapstring
			) and $legitem is not null";
	}
	
	//$myquerystring.=" and GeomFromText('Polygon(($coordbox))') ~ crd.mypoint limit 25";
	/*
	$myquerystring.=" and st_geomfromtext('Polygon(($coordbox))') ~ the_geom
						group by source, sample_id, sample_pkey, longitude, latitude";
	*/
		
	$myquerystring.=" and ST_Intersects(st_geomfromtext('Polygon(($coordbox))'),the_geom)
						group by source, sample_id, sample_pkey, longitude, latitude";
	

	
	
	
	//and ST_Intersects(st_geomfromtext('Polygon(($coordbox))'),the_geom)
	
	
	//echo nl2br($myquerystring);
	//exit();
	
	$myrows=$db->get_results("$myquerystring");
	
	$numrows=count($myrows);
	
	//var_dump($myrows);
	//echo "num rows: ".$db->num_rows."<br><br>";
	
	//var_dump($myrows);

	
	if($numrows==0){
	?>
		
		<table width="350" cellpadding="2" cellspacing="1" bgcolor="#333333">
  		<tr>
    		<td bgcolor="#333333"><font color="#FFFFFF"><strong>No Samples Found: </strong></font></td>
  		</tr>
  		<tr>
    		<td bgcolor="#FFFFFF"> No samples found. Please click on another point or
      		zoom in to a more detailed view.
    		</td>
  		</tr>
		</table>
		<br>

	
	<?
	}//end if rows = 0
	
	
	if($numrows==1){
	
	//echo "only one row";
	
	//var_dump($myrows);
	
	
	
	$sample_pkey=$myrows[0]->sample_pkey;

	//echo "sample_pkey: $sample_pkey";

/*
	echo nl2br("SELECT 	samp.*,
				(select class1 from sampletypeb where sample_pkey=samp.sample_pkey limit 1) as class1,
				(select class2 from sampletypeb where sample_pkey=samp.sample_pkey limit 1) as class2,
				(select class3 from sampletypeb where sample_pkey=samp.sample_pkey limit 1) as class3,
				(select class4 from sampletypeb where sample_pkey=samp.sample_pkey limit 1) as class4,
				crd.xval as longitude,
				crd.yval as latitude,
				schem.*
		FROM 	sample samp,
				location loc,
				coords crd,
				sample_chemistry schem
		WHERE
				samp.sample_pkey = $sample_pkey and
				samp.sample_pkey = schem.sample_pkey and 
				samp.sample_pkey = loc.sample_pkey AND
				loc.location_pkey = crd.location_pkey limit 1");
*/

	$myrow=$db->get_row("SELECT 	*
		FROM 	vmatreported
		WHERE
				sample_pkey = $sample_pkey limit 1");
				
	//var_dump($myrow);
	//var_dump($db);

	//check whether to show items
	foreach($itemnames as $it){
		eval("\$thisval = \$myrow->".$it->field_name.";");
	
		if($thisval!=""){
			//echo $it->display_name."= $thisval $thisunitname $thismethodname<br>";
			$showitems="yes";
		}
		
	
	}


?>


			<table width="350" cellpadding="2" cellspacing="1" bgcolor="#333333">
			<tr>
				<td bgcolor="#333333">
				<table width="100%">
				<tr>
				<td><font color="#FFFFFF"><strong>Sample Details: </strong></font></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				</tr>
				</table>
				</td>
			</tr>
			<tr>
			</table>

				<table width="350" cellspacing="1" cellpadding="2">

					<tr>
					<td>Source:</td>
					<td><?=strtoupper($myrow->source)?></td>
					</tr>
					<tr>
					<td>Sample ID:</td>
					<td><?=$myrow->sample_id?></td>
					</tr>
		
        	<tr>
          	<td>Rock Type:</td>
			<td>
<?
$rockdelim="";
if($myrow->class1!=""){echo $rockdelim.$myrow->class1;$rockdelim=", ";}
if($myrow->class2!=""){echo $rockdelim.$myrow->class2;$rockdelim=", ";}
if($myrow->class3!=""){echo $rockdelim.$myrow->class3;$rockdelim=", ";}
if($myrow->class4!=""){echo $rockdelim.$myrow->class4;$rockdelim=", ";}
?>
			</td>
        	</tr>
			</table>












			<div style="padding : 4px; width : 375px; height : 200px; overflow : auto; ">

<?
if($showitems=="yes"){
?>

	<table width="350" cellpadding="2" cellspacing="1" >

		
		
	<tr><td>ITEM</td><td>VALUE</td><td>METHOD</td></tr>

<?


	foreach($itemnames as $it){
		eval("\$thisval = \$myrow->".$it->field_name.";");
		eval("\$thismethodname = \$myrow->".$it->field_name."_meth;");
		eval("\$thisunitname = \$myrow->".$it->field_name."_unit;");

		if($thisunitname!=""){
			$thisunitname="($thisunitname)";
		}
	
	
	
		if($thisval!=""){
			echo "<tr><td bgcolor=\"#FFFFFF\">".$it->display_name."</td><td bgcolor=\"#FFFFFF\">$thisval $thisunitname</td><td bgcolor=\"#FFFFFF\">$thismethodname</td></tr>\n";
			$showitems="yes";
		}
		
	
	}

























		
		
		?>

				</table> </td>
			</tr>
			</table>
<?
}//end if showitems eq yes
?>
			</div>
			
			<table width="350" cellpadding="2" cellspacing="1" >
				<tr>
					<td>Detail Link:</td>
					<td><a href="<?=$myrow->url?>" target="_blank">Click</a></td>
				</tr>
			</table>
		<?


	}//end if rows = 1
	
	
	if($numrows>1){
	
?>
		<table width="350" cellpadding="2" cellspacing="1" bgcolor="#333333">
  		<tr>
    		<td bgcolor="#333333"><font color="#FFFFFF"><strong>Samples Found: </strong></font></td>
  		</tr>
		</table>
		More than one sample found:<br>
		<div style="width : 375px; height : 300px; overflow : auto; ">

      		<table width="350" cellspacing="1" cellpadding="2">

				<?
				$jslink=",$mylat,$mylon,$pkey";
				foreach($datasources as $ds){
					eval("\$thisval=\$".$ds->name.";");
					$jslink.=",$thisval";
				}
				$jslink.=",$myzoom,'$legitem'";
				
				?>        		

				<? foreach($myrows as $myrow){ ?>
				<tr>
          		<td><?=$myrow->sample_id?></td>
          		<td><?=strtoupper($myrow->source)?></td>
          		<td><a href="javascript:show_detail(<?=$myrow->sample_pkey?><?=$jslink?>);" >detail</a></td>
        		</tr>
        		<? } //end for each row ?>

				
				
				
				
      		</table>	
		</div>
	
	
	
	
	
	












<?
	}//end if rows > 1




}//end if sample_pkey is set


?>