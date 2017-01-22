



<?php
//http://www.pontikis.net/blog/auto_post_on_twitter_with_php

// require codebird
require_once('codebird.php');
//echo $test_import;
 
\Codebird\Codebird::setConsumerKey("cons key", "cons key secret");

$cb = \Codebird\Codebird::getInstance();

$cb->setToken("access token", "token secret");

$r = rand(0,100); // to ensure a diff tweet is posted each time. 

$data = array(
		"I like white", 
		"I like black", 
		"My favourite colour is red", 
		"Northerners do it better",
		"New Zealand is the best.",
		"Everyone wants to talk like a Yorkshireman."
		);

//this line works a treat. Try not to break it too much.  
$reply = $cb->statuses_update("status= " . $data[array_rand($data)] . " " . $r );

//http://www.techrepublic.com/article/17-useful-functions-for-manipulating-arrays-in-php/5792851/
//crucial for helpig find the right syntax for the array.  


?>