<?PHP
/**
 * login.php
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

// User login. 
session_start();
include('db.php'); // database drivers, and connect

//include("get_pkey.php");
if($_GET['search']!=""){$gotosearch=$_GET['search'];}else{$gotosearch=$_POST['search'];}
if($_GET['savequery']!=""){$savequery=$_GET['savequery'];}else{$savequery=$_POST['savequery'];}
if($_GET['savedqueries']!=""){$savedqueries=$_GET['savedqueries'];}else{$savedqueries=$_POST['savedqueries'];}
if($_GET['searchpkey']!=""){$searchpkey=$_GET['searchpkey'];}else{$searchpkey=$_POST['searchpkey'];}

$timestamp = time();
// Get ip address and brower for logging --->
$ip = $_SERVER['REMOTE_ADDR'];
$browser= $SERVER['HTTP_USER_AGENT'];

require_once "geopass/josso.php";

// Get current SSO User and SSO Session information,
$user = $josso_agent->getUserInSession();
$sessionId = $josso_agent->getSessionId();

/*
if (isset($_SESSION['username'])) {

	//echo "already session logged in. goto index<br>";

	header("Location: index.php");
	exit();
}
*/

if (isset($user)) {

	/*
	echo "already geopass logged in.<br>
			set session username<br>
			get userpkey and put in new<br>
			user if necessary.<br><br>";
	*/
	
	//print_r($user);
	
	$username=$user->name;
	$firstname=$user->properties[3]['!value'];
	$lastname=$user->properties[4]['!value'];
	
	//echo "username: $username<br>";
	//echo "firstname: $firstname<br>";
	//echo "lastname: $lastname<br>";

	$getuser=$db->get_row("select * from users where email='$username'"); 
	// If a record exists then the user is valid --->
	if (count($getuser)>0) {           

		// Store the user id in session variables and cookies. --->
		//$_SESSION['users_pkey'] = $getuser->pkey; $_SESSION['userpkey'] = $getuser->pkey; $_SESSION['user_pkey'] = $getuser->pkey; // I think navdat uses users_pkey spelling. Until I can make this match everywhere, use both. (Some pages on neko are shared by both earthchem and navdat, e.g. the admin pages for users and help, the plots, etc. --EEJones March 2008 --->
		//$_SESSION['user_level'] = $getuser->user_level; $_SESSION['userlevel'] = $getuser->user_level; 
		// get the last time of a successful login and store it is a session var --->
		//$_SESSION['username'] = $getuser->email;
		
		//if ($getuser->EDITOR) {$_SESSION['editor']=true;} else {$_SESSION['editor']=false;}

		$userpkey=$getuser->pkey;
		$user_level=$getuser->user_level;
		$username=$getuser->email;
		if ($getuser->EDITOR) {$editor=true;} else {$editor=false;}

		$update=$db->query("UPDATE users SET ipaddress = '$ip', browser = '$browser', Last_Login = '$today()' WHERE pkey = $getuser->pkey");

		// Log successful login --->
		$logaction=$db->query("INSERT INTO logs (pkey, time_stamp,username,message,ipaddress,browser) VALUES (nextval('logs_seq'), $timestamp, '$username', 'Succcessful login','$ip','$browser')") or die("could not insert into logs in login.php");

		//echo "You are logged in $username ";
		if ($_SESSION['user_level'] != 'user') {
			//echo $_SESSION['user_level'];
			//print_r($_SESSION);
		}


	}else{

		//echo "user $username does not exist yet... let's put it in here...<br><br>";
		$db->query("insert into users 	(pkey,
											firstname,
											lastname,
											email,
											active,
											user_level,
											editor
										)values(
											nextval('users_seq'),
											'$firstname',
											'$lastname',
											'$username',
											'yes',
											'user',
											0)");
		
		$getuser=$db->get_row("select * from users where email='$username'");
		// Store the user id in session variables and cookies. --->
		//$_SESSION['users_pkey'] = $getuser->pkey; $_SESSION['userpkey'] = $getuser->pkey; $_SESSION['user_pkey'] = $getuser->pkey; // I think navdat uses users_pkey spelling. Until I can make this match everywhere, use both. (Some pages on neko are shared by both earthchem and navdat, e.g. the admin pages for users and help, the plots, etc. --EEJones March 2008 --->
		//$_SESSION['user_level'] = $getuser->user_level; $_SESSION['userlevel'] = $getuser->user_level; 
		// get the last time of a successful login and store it is a session var --->
		//$_SESSION['username'] = $getuser->email;
		
		//if ($getuser->EDITOR) {$_SESSION['editor']=true;} else {$_SESSION['editor']=false;}

		$userpkey=$getuser->pkey;
		$user_level=$getuser->user_level;
		$username=$getuser->email;
		if ($getuser->EDITOR) {$editor=true;} else {$editor=false;}

		$update=$db->query("UPDATE users SET ipaddress = '$ip', browser = '$browser', Last_Login = '$today()' WHERE pkey = $getuser->pkey");

		// Log successful login --->
		$logaction=$db->query("INSERT INTO logs (pkey, time_stamp,username,message,ipaddress,browser) VALUES (nextval('logs_seq'), $timestamp, '$username', 'Succcessful login','$ip','$browser')") or die("could not insert into logs in login.php");


	}



	
	if($gotosearch=="yes"){
		if($searchpkey!=""){
			header("Location:search.php?pkey=$searchpkey");
		}else{
			header("Location:index.php");
		}
	}elseif($savedqueries=="yes"){
		header("Location:savedqueries.php");
	}elseif($savequery=="yes"){
		header("Location:savequery.php?pkey=$pkey");
	}else{
		header("Location:glossary.php");
	}
	

	/*
	foreach($_SESSION as $key=>$value){
		echo "$key - $value<br>";
	}
	*/
	
	/*
	?>
	<br><br><a href="http://matisse.kgs.ku.edu/earthchemphp4/geopass/josso-logout.php?josso_current_url=http://matisse.kgs.ku.edu/earthchemphp4">Logout</a>
	<?
	*/
	
	exit();
	
}



//if we get this far, we need to go to geopass

$myuri=urlencode($_SERVER['REQUEST_URI']);

//echo $myuri;

//echo "<br><br><a href=\"geopass/josso-login.php?josso_current_url=$myuri\">here</a>";

header("Location: geopass/josso-login.php?josso_current_url=$myuri");








exit();



?>

