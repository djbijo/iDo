<?php 
# Connect to iDODB

function getLink(){
	$link = mysqli_connect('localhost', 'root', 'password'); 	#implement for iDO
	if (!$link) { 
		$output = 'Unable to connect to the database server.'; 
		include 'output.html.php'; 
		exit(); 
	} 

	if (!mysqli_set_charset($link, 'utf8')) { 
		$output = 'Unable to set database connection encoding.'; 
		include 'output.html.php'; 
		exit(); 
	} 
	
	if (!mysqli_select_db($link, 'iDODB')) { 
		$output = 'Unable to locate the iDO database.'; 
		include 'output.html.php'; 
		exit(); 
	} 
	
	$output = 'iDO Database connection established.'; 
	include 'output.html.php'; 
	
	return $link;
}

# remove magic qoutes
function removeMagicQuotes(){
	if (get_magic_quotes_gpc()) {   
		function stripslashes_deep($value) {   
			$value = is_array($value) ?   
				array_map('stripslashes_deep', $value) :   
				stripslashes($value);   
			return $value;   
		}   
	
		$_POST = array_map('stripslashes_deep', $_POST);   
		$_GET = array_map('stripslashes_deep', $_GET);   
		$_COOKIE = array_map('stripslashes_deep', $_COOKIE);   
		$_REQUEST = array_map('stripslashes_deep', $_REQUEST);   
	} 
}

?>