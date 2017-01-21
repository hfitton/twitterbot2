



<?php
//http://www.pontikis.net/blog/auto_post_on_twitter_with_php

// require codebird
require_once('codebird.php');
//echo $test_import;
 
\Codebird\Codebird::setConsumerKey("", "");

$cb = \Codebird\Codebird::getInstance();

$cb->setToken("", "");

$params = array(
  'status' => "Auto Post on Twitter with PHP. I'm awesome! #php #twitter"
);
$reply = $cb->statuses_update($params);

?>