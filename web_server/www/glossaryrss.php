<?PHP
/**
 * glossaryrss.php
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

//print_r($_SERVER);
//exit();

include("db.php");

$myrows=$db->get_results("select glossary_name,description,pkey,search_query_pkey from glossary where published=1 order by pkey desc");

/*
<?xml version="1.0"?>
<rss version="2.0">
<channel>

<title>The Channel Title Goes Here</title>
<description>The explanation of how the items are related goes here</description>
<link>http://www.directoryoflinksgohere</link>

<item>
<title>The Title Goes Here</title>
<description>The description goes here</description>
<link>http://www.linkgoeshere.com</link>
</item>

<item>
<title>Another Title Goes Here</title>
<description>Another description goes here</description>
<link>http://www.anotherlinkgoeshere.com</link>
</item>

</channel>
</rss>
*/
header ("content-type: text/xml");

echo "<?xml version=\"1.0\"?>";
?>
<rss version="2.0">
<channel>
<title>EarthChem Glossary</title>
<description>The EarthChem Glossary is a system designed to allow users to define their own search terms and share them with the EarthChem community.</description>
<link>http://www.earthchemportal.org/glossary</link>
<?
foreach($myrows as $r){
?>
<item>
<title><?=$r->glossary_name?></title>
<description><?=$r->description?></description>
<link>http://www.earthchemportal.org/search.php?pkey=<?=$r->search_query_pkey?>&amp;glossary_pkey=<?=$r->pkey?></link>
</item>
<?
}
?>
</channel>
</rss>