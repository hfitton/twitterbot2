



<?php
//http://www.pontikis.net/blog/auto_post_on_twitter_with_php

// require codebird
require_once('codebird.php');
//echo $test_import;
 
\Codebird\Codebird::setConsumerKey("tHVPUVF5ZmZOHlk4J6Uv3ZVGK", "SpNy5wdChSDjUV5reBnp6e3ZN53d8caEnadXKRD5uu6wGzhfmD");

$cb = \Codebird\Codebird::getInstance();

$cb->setToken("819014329710186496-xOMMQ9Mt1aMbAkYiun7KDq8Lzq9ToRE", "UT6SxcVPg2MoR3LMXPFlEMbNUqW7OsLWfG792LHgbkkvs");


$params = array(
  'status' => 'Stuff. #php #twitter'
);
$reply = $cb->statuses_update($params);

?>