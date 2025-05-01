<html>
<body>
<?php
function do_post_request($url, $data, $optional_headers = null)
{
	$params = array('http' => array(
		'method' => 'post',
		'content' => $data
	));
	
	if ($optional_headers!== null) {
		$params['http']['header'] = $optional_headers;
	}

	$ctx = stream_context_create($params);
	$fp = @fopen($url, 'rb', false, $ctx);

	if (!$fp) {
		throw new Exception("Problem with $url, $php_errormsg");
	}
	
	$response = @stream_get_contents($fp);
	
	if ($response === false) {
		throw new Exception("Problem reading data from $url, $php_errormsg");
	}
	return $response;
}

?>

<textarea rows="40" cols="100">
<?php echo do_post_request("http://geopass.iedadata.org:8080/josso/rest/auth.jsp", "username=footestuser@columbia.edu&password=".sha1("test")); ?>
</textarea>
</body>
</html>

