<?PHP
/**
 * updateholdings.php
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

//update holdings

if($_GET['pass']!="YWYNVh3M2nJQXRXm") exit();

include("db.php");

$rows=$db->get_results("select * from holdings order by pkey");

foreach($rows as $row){

	//do three queries for each source

	$source=$row->title;

	$pkey=$row->pkey;
	
	$citations=$db->get_var("select count(*) from (
							select title,authors
							from 
							citation cit,  
							sample samp 
							where samp.sample_pkey = cit.sample_pkey
							and samp.source='$source'
							group by title,authors
							)foo;");

	$samples=$db->get_var("select count(*) from sample where source='$source';");
	
	
	$analyses=$db->get_var("select count(*)
							from item it, sample samp
							where it.sample_pkey = samp.sample_pkey
							and samp.source='$source';");
	
	if($analyses=="0"){
		$analyses="n/a";
	}

	$db->query("update holdings set
				citations='$citations',
				samples='$samples',
				analyses='$analyses'
				where pkey = $pkey
				");



}


$pkey=9;

$citations=$db->get_var("select count(*) from (
						select title,authors
							from 
							citation cit,  
							sample samp 
							where samp.sample_pkey = cit.sample_pkey
							group by title,authors
						)foo;");

$samples=$db->get_var("select count(*) from sample;");


$analyses=$db->get_var("select count(*)
						from item it, sample samp
						where it.sample_pkey = samp.sample_pkey;");

if($analyses=="0"){
	$analyses="n/a";
}

$db->query("update holdings set
			citations='$citations',
			samples='$samples',
			analyses='$analyses'
			where pkey = $pkey
			");























?>
