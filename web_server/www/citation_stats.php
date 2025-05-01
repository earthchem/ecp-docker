<?PHP
/**
 * citation_stats.php
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


///citation_stats?v=xml&start_month=2013-8

include("db.php");

$today=getdate(); // all the possible parts of a date are in this array: print_r($today); 

$nowyear=$today['year']+1;
$nowmonth=$today['mon'];

//echo "nowyear: $nowyear nowmonth: $nowmonth <br>";



//2007-9-1

if($_GET['start_month']!=""){

	$start_date=$_GET['start_month']."-1";

}elseif($_GET['start_date']!=""){

	$start_date=$_GET['start_date'];

}else{

	$start_date="2007-9-1";

}


if($start_date!=""){

	$parts=explode("-",$start_date);
	
	//print_r($parts);exit();
	
	$year=$parts[0];
	$month=$parts[1];
	$month=$month+0;

	//echo "month: $month  year:$year ";exit();
	
	if((!is_numeric($year))||(!is_numeric($month))||($year<2007)){
		echo "invalid date specified";exit();
	}

}else{

	$year=2007;
	$month=9;
	
}

if($_GET['v']=="csv"){

	
	header('Content-Type: application/csv');
	header('Content-Disposition: attachment; filename=earthchem_citation_stats.csv');
	header('Pragma: no-cache');
	


	
	$go="yes";
	
	echo "start_date,end_date,unique_downloads,unique_ips,sample_records\n";
	

	$rows=$db->get_results("select * from cronstats where foodate >= '$start_date' order by pkey");
	foreach($rows as $row){
	
		//print_r($row);
	
	$start_date=$row->start_date;
	$end_date=$row->end_date;
	$unique_downloads=$row->unique_downloads;
	$unique_ips=$row->unique_ips;
	$sample_records=$row->sample_records;

			echo "$start_date,$end_date,$unique_downloads,$unique_ips,$sample_records\n";
	

	}//end foreach row
			

		

	


}elseif($_GET['v']=="xml"){

	header("Content-type: text/xml"); 
	
	
	$go="yes";
	
	echo "<results>\n";
	
	$rows=$db->get_results("select * from cronstats where foodate >= '$start_date' order by pkey");
	foreach($rows as $row){
	
	
	
	$start_date=$row->start_date;
	$end_date=$row->end_date;
	$unique_downloads=$row->unique_downloads;
	$unique_ips=$row->unique_ips;
	$sample_records=$row->sample_records;



	
			echo "\t<row>\n";
			
			echo "\t\t<start_date>$start_date</start_date>\n";
			echo "\t\t<end_date>$end_date</end_date>\n";
			echo "\t\t<unique_downloads>$unique_downloads</unique_downloads>\n";
			echo "\t\t<unique_ips>$unique_ips</unique_ips>\n";
			echo "\t\t<sample_records>$sample_records</sample_records>\n";

			
			echo "\t</row>\n";
	

	}//end foreach row
			

			


	echo "</results>";







}elseif($_GET['v']=="cron" || $argv[1]=="cron"){ //$argv[1]; == cron

	$db->query("delete from cronstats where type='norm'");
	
	
	$go="yes";
	
	$year=2019;
	$month=7;
	
	while($go == "yes"){
		
		//echo "year: $year month: $month\n";
		
		$nextmonth=$month+1;
		$nextyear=$year;
		if($nextmonth==13){
			$nextmonth=1;
			$nextyear++;
		}
		
		$thisdate = DateTime::createFromFormat('Y-n-j', $year."-".$month."-1");
		
		$nowdate = new DateTime("now");
		
		//$datediff = date_diff($thisdate, $nowdate);
		
		//$datediff = $datediff->days;
		
		if($thisdate > $nowdate){
			$go="no";
		}
		
		//echo "nowdate: \n"; print_r($nowdate); echo "\n\n";
		
		if($go=="yes"){
		
			//echo "year: $year month: $month\n";
			
			//get uniquedownloads, uniqueips, samplerecords
			
			
			
			$uniquedownloads=$db->get_var("select count(*) from (
											select
											download_timestamp,
											remote_addr
											from d_details where download_timestamp >= '$year-$month-1'::date and download_timestamp < '$nextyear-$nextmonth-1'::date
											group by download_timestamp, remote_addr
											) foo");
											
			$uniqueips=$db->get_var("select count(*) from (
										select
										remote_addr
										from d_details where download_timestamp >= '$year-$month-1'::date and download_timestamp < '$nextyear-$nextmonth-1'::date
										group by remote_addr
										) foo
										;");
			
			$samplerecords=$db->get_var("select
											sum(count_samples_cited)
											from d_details where download_timestamp >= '$year-$month-1'::date and download_timestamp < '$nextyear-$nextmonth-1'::date;");
			
		
			if($samplerecords==""){
				$samplerecords=0;
			}
			

			
			$db->query("insert into cronstats (start_date,end_date,unique_downloads,unique_ips,sample_records,type,foodate) values 
											(
											'$year"."-"."$month"."-1',
											'$nextyear"."-"."$nextmonth"."-1',
											'$uniquedownloads',
											'$uniqueips',
											'$samplerecords',
											'norm',
											'$year"."-"."$month"."-1'
											)
											");
		
		}
		
		$month++;
		
		if($month==13){
			$year++;
			$month=1;
		}
		
		if($year==$nowyear){
			$go="no";
		}
	
	}






















}else{	 
	include ('includes/ks_head.html');
	?>
	<style type="text/css">
	table.aliquot, table.sample  {
		border-width: 1px 1px 1px 1px;
		border-spacing: 2px;
		border-style: none none none none;
		border-color: #999999; /*#636363;*/
		border-collapse: collapse;
		background-color: white;
	}
	table.aliquot th, table.sample th  {
		font-family:arial,verdana,sans-serif;
		font-size:9pt;
		font-weight: 500;
		color:#333333;
		text-transform:uppercase;
		text-align:left;
		/*color: #666699; #636363; #FFFFFF;*/
		border-color: #999999;
		border-width: 1px 1px 1px 1px;
		padding: 5px 5px 5px 5px;
		border-style: solid solid solid solid;
		background-color: #f0f4f5; /* NYTimes tabs background blue. Tried others: #d7e6fc; 325280 #003366;*/
	}
	table.sample th {
		background-color:antiquewhite;text-transform:none;
		}
	table.aliquot td, table.sample td  {
		border-width: 1px 1px 1px 1px;
		border-color: #999999;
		padding: 2px 5px 2px 5px;
		border-style: solid solid solid solid;
		background-color: white;
	}
	</style>
	<?
	

	
	
	?>
	<h1>EarthChem Citation Statistics</h1><br>
	<table class="aliquot">
		<tr>
			<th>Start Date</th>
			<th>End Date</th>
			<th>Unique Downloads</th>
			<th>Unique IPs</th>
			<th>Sample Records</th>
		</tr>
	
	<?
	$rows=$db->get_results("select * from cronstats where foodate >= '$start_date' order by pkey");
	foreach($rows as $row){
	$start_date=$row->start_date;
	$end_date=$row->end_date;
	$unique_downloads=$row->unique_downloads;
	$unique_ips=$row->unique_ips;
	$sample_records=$row->sample_records;


	?>
	
		<tr>
			<td><?=$start_date?></td>
			<td><?=$end_date?></td>
			<td><?=$unique_downloads?></td>
			<td><?=$unique_ips?></td>
			<td><?=$sample_records?></td>

		</tr>
	
	<?
	}//end foreach row
	?>
	</table>
	
	<?
	include ('includes/ks_footer.html');
}//end if csv

























?>