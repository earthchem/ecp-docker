<?PHP
/**
 * log_citation_stats.php
 *
 * longdesc
 *
 * LICENSE: This source file is subject to version 4.0 of the Creative Commons
 * license that is available through the world-wide-web at the following URI:
 * https://creativecommons.org/licenses/by/4.0/
 *
 * @category   Geochemistry
 * @package    EarthChem Portal
 * @author     Sean Cao <scao@ldeo.columbia.edu>
 * @copyright  IEDA (http://www.iedadata.org/)
 * @license    https://creativecommons.org/licenses/by/4.0/  Creative Commons License 4.0
 * @version    GitHub: $
 * @link       http://ecp.iedadata.org
 * @see        EarthChem, Geochemistry
 */

include("db.php");

$today=getdate();

$now_year=(int)$today['year'];
$now_month=(int)$today['mon'];

$last_date = $db->get_var("SELECT end_date FROM cronstats ORDER BY end_date DESC LIMIT 1;");
$last_date_split=explode("-",$last_date);

$year=(int)$last_date_split[0];
$month=(int)$last_date_split[1];

if($argv[1]=="reset"){
	$db->query("DELETE FROM cronstats WHERE type='norm'");
	$year=2019;
	$month=8;
}

while(($year<$now_year || ($year==$now_year && $month<$now_month)) && !($year==$now_year && $month==$now_month)){ //while before this month

	$next_month=$month+1;
	$next_year=$year;
	if($next_month==13){
		$next_month=1;
		$next_year++;
	}

	//get unique_downloads
	$unique_downloads=$db->get_var("SELECT count(*) FROM (
									SELECT timestamp, remote_addr
									FROM search_query_downloads 
									WHERE timestamp >= '$year-$month-1'::date and timestamp < '$next_year-$next_month-1'::date
									GROUP BY timestamp, remote_addr
									) unique_downloads;");
	//get unique ips
	$unique_ips=$db->get_var("SELECT count(*) FROM (
								SELECT
								remote_addr
								FROM search_query_downloads WHERE timestamp >= '$year-$month-1'::date and timestamp < '$next_year-$next_month-1'::date
								GROUP BY remote_addr
								) unique_ips;");
	//get list of downloads
	$downloads = $db->get_results("SELECT download_sql FROM search_query_downloads WHERE timestamp >= '$year-$month-1'::date and timestamp < '$next_year-$next_month-1'::date");
	
	//for each download, use download_sql to find the sample record count and get the sum
	$sample_records = 0;
	foreach($downloads as $d) {
		$download_sql=$d->download_sql;

		$count_sql="SELECT count(*) FROM ($download_sql) download_count";
		$count_sql=str_replace("--samp.sample_pkey = lepr.sample_pkey","",$count_sql);
		$count_sql=str_replace("--citation_authors ca,","",$count_sql);
		$count_sql=str_replace("--ca.citation_pkey = st.citation_pkey AND","",$count_sql);

		$download_count = $db->get_var($count_sql);

		$sample_records += $download_count;
	}

	$db->query("insert into cronstats (start_date,end_date,unique_downloads,unique_ips,sample_records,type,foodate) values 
									(
									'$year"."-"."$month"."-1',
									'$next_year"."-"."$next_month"."-1',
									'$unique_downloads',
									'$unique_ips',
									'$sample_records',
									'norm',
									'$year"."-"."$month"."-1'
									)
									");

	print "$year-$month: $unique_downloads $unique_ips $sample_records\n";
	$year = $next_year;
	$month = $next_month;
}

?>