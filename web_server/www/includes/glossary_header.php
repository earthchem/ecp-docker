<?php
// This file is included just after including the main header file in the search and other pages if the user (regardless of privilege level) is logged in. 
?>
<DIV style="float:right;margin:0 20;clear:left;padding:5px;border:1px solid #996600">
<?php
if (isset($user)) {
echo 'Logged in as '.$username.'&nbsp;|&nbsp;<A href="logout.php">Log out</A>&nbsp;|&nbsp;<A href="glossary_add.php">Add&nbsp;Glossary&nbsp;Entry</A>&nbsp;|&nbsp;<a href="glossaryrss.php"><img src="rss.gif" border="0">&nbsp;RSS</a>';
} else {
echo '<A href="login.php">Log in</A>&nbsp;to add/edit entries&nbsp;|&nbsp;<A href="http://geopass.iedadata.org:8080/josso/register.jsp">Register</A>&nbsp;|&nbsp;<a href="glossaryrss.php"><img src="rss.gif" border="0">&nbsp;RSS</a>';
} // if
?>
</DIV><br clear="left" />
