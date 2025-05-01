<?PHP
/**
 * log_downloads_citations.php
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


//ob_flush();flush();
include ("db.php");
$downloads = $db->get_results("SELECT * FROM search_query_downloads WHERE NOT citations_logged");
if (sizeof($downloads)<1) {
	//echo "<p>There are no new downloads to log.<p>";
	exit;
} // No downloads to log citations for 

//echo sizeof($downloads)." ";

//exit();
//echo "<p>".sizeof($downloads)." downloads to log <p>";
foreach($downloads as $d) { 
	
	//echo "foo ";
	
	//echo "<p>download_pkey: $d->download_pkey &bull; download_logged $d->download_logged <br>$d->download_sql "; //print_r($row);
	
	$download_pkey=$d->download_pkey; 
	$download_sql=$d->download_sql; 
	$timestamp=$d->timestamp;
	$search_query_pkey=$d->search_query_pkey;
	
	$citations_sql="SELECT citation_pkey, count(*) AS count_samples_cited FROM 
	($download_sql) AS foo 
	GROUP BY citation_pkey ORDER BY citation_pkey
	";
	
	$citations_sql=str_replace("--samp.sample_pkey = lepr.sample_pkey","",$citations_sql);
	
	$citations_sql=str_replace("--citation_authors ca,","",$citations_sql);
	
	$citations_sql=str_replace("--ca.citation_pkey = st.citation_pkey AND","",$citations_sql);
	

	
	

	
	//echo nl2br($citations_sql);exit();
	//echo $search_query_pkey." ".$citations_sql."<br><br><br>";

	//exit();
	
	//echo "<p>citations_sql<br>$citations_sql<br> "; 
	$citations = $db->get_results($citations_sql);// or die('dead here'); 
	//echo "<p>".sizeof($citations)." distinct citations<p>";
	
	//echo "/n/nmade it here.";
	//exit();
	
	
	foreach($citations as $c) { 
		//echo "<hr>citation_pkey $c->citation_pkey<p>"; 
		$thesql="SELECT citation_pkey, authors, title, journal, pubyear, authors_vector as vector 
		FROM citation
		WHERE citation_pkey = $c->citation_pkey";
		//echo "<p>$thesql<p>"; ob_flush();flush();
		//exit();
		$citation_data = $db->get_row($thesql);
		
		if($citation_data->citation_pkey!=""){

			// lots of single quotes in here need to be escaped
			$vector=str_replace("'","''",$citation_data->vector);  
			$authors=str_replace("'","''",$citation_data->authors);
			$title=str_replace("'","''",$citation_data->title);
			$journal=str_replace("'","''",$citation_data->journal);
			$pubyear=$citation_data->pubyear;
			$oldcitation_pkey=$c->citation_pkey;
			
			if($pubyear==""){
				$pubyear="9999";
			}
	
			$insert_sql="INSERT INTO downloads_citations 
			(download_timestamp, download_pkey, citation_pkey, count_samples_cited, authors, pubyear, title, journal, vector, remote_host, remote_addr) 
			VALUES 
			('$d->timestamp', $download_pkey, $c->citation_pkey, $c->count_samples_cited, $c->authors, $citation_data->pubyear, '$title', '$journal', '$vector','$d->remote_host','$d->remote_addr')";
			//echo "<p>$insert_sql<p>";		
			
			$findcitation_pkey=$db->get_var("select d_citation_pkey from d_citations where title='$title' and journal='$journal' and pubyear=$pubyear");
			
			
			//first let's take care of the citation portion of the entry
			if($findcitation_pkey == ""){
				//entry doesn't exist yet, so let's get a new one and put the record in.
				$mycitation_pkey=$db->get_var("select nextval('d_citations_seq')");
				$db->query("insert into d_citations (
													d_citation_pkey,
													title,
													journal,
													authors,
													vector,
													pubyear
												) values (
													$mycitation_pkey,
													'$title',
													'$journal',
													'$authors',
													'$vector',
													$pubyear
												)
				");
			
			}else{
				$mycitation_pkey=$findcitation_pkey;
			}

			//insert details here
			$db->query("insert into d_details (
											d_detail_pkey,
											d_citation_pkey,
											count_samples_cited,
											download_timestamp,
											remote_host,
											remote_addr
										)values(
											nextval('downloads_citations_downloads_citations_pkey_seq'),
											$mycitation_pkey,
											$c->count_samples_cited,
											'$d->timestamp',
											'$d->remote_host',
											'$d->remote_addr'
											)
									");

			
			//$query=$db->query($insert_sql) or die;

		}


	} // foreach citation
	// Now mark the download as logged
	$sql="UPDATE search_query_downloads SET citations_logged = true WHERE download_pkey=$d->download_pkey";
	$update=$db->query($sql) or die("Could not set citations_logged to true");
} // foreach downloads

