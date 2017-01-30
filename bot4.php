<?php

	//http://www.pontikis.net/blog/auto_post_on_twitter_with_php
	// require codebird
	require_once('codebird.php');
	//echo $test_import;
	
	
	/*
	\Codebird\Codebird::setConsumerKey("cons key", "cons key secret");
	$cb = \Codebird\Codebird::getInstance();
	$cb->setToken("access token", "token secret");
	*/
	require_once('config.inc');
	
	$r = rand(0,100); // to ensure a diff tweet is posted each time. 
	/*
	$yorkshire_array = array(
	    "This is really rather difficult.", 
	    "Think I'm going to give up and go sheep farming.", 
	    "Apologies for more random tweets.", 
	    "Northerners do it better",
	    "New Zealand is the best.",
	    "Everyone wants to talk like a Yorkshireman.",
	    "The more I learn, the more confused I get.",
	    "Really?! I think my head is full of wool.",
	    "T’ only way is Yorkshire.",
	    "That’s proper champion, that, lad."
			); 
	
	//this line works a treat. Try not to break it too much.  
	//$reply = $cb->statuses_update("status= " . $yorkshire_array[array_rand($yorkshire_array)] . " " . $r );
	//http://www.techrepublic.com/article/17-useful-functions-for-manipulating-arrays-in-php/5792851/
	//crucial for helpig find the right syntax for the array. 
	*/
	
	$params = array('q'=>'notaproblemnewphpbot','lang'=>'en');   // this is searching for 'coffee' in the english language. 
	$reply = (array) $cb->search_tweets($params);// tells codebird to save the results in an array called reply 
	
	$data = (array) $reply['statuses']; //data is in this array called reply.  
	
	//status actually means a tweet and all the metadata included. We store them inside an another array. called 'data'  NOT SURE what the point of that is, as reply and data appear to return the same array.  
	
	//print_r ($data); //this prints out all the data so we can see it in the terminal. 
	
	//orginal code from:  https://nerdyjunkyard.wordpress.com/2014/01/30/dealing-with-tweet-data-by-php/ - torn apart by me, but the basics are still recognisable. 
	
	//next we create a for loop to just find the names of the people tweeting.  $status->user->name and $status->user->screen_name gets us the name 
	
	$s = count($reply['statuses']);
	
	for ($a = 0; $a < $s; $a++) {
	
	      $status = $data[$a];
	
		    //to create the class, find the data in the array using the count and point to it.  Allocate the class name at the beginning, and don't forget the ; at the end. DON'T FORGET THIS! IT TOOK FOUR DAYS TO WORK OUT!  Can prob be done a better way, but I can't find it.
	
	      echo    $name = $status->user->name . "\n"; //this is the real name of the user. 
	      echo    $screen_name = $status->user->screen_name . "\n"; //this is the user or handle name of the person tweeting. 
	      echo    $location = $status->user->location . "\n";  
	      echo    $text = $status->text . "\n";
	      echo    $time_created= $status->created_at . "\n";
	      echo    $reply_from= $status->in_reply_to_screen_name . "\n";
	      
	
	   //    $reply = $cb->statuses_update("status= Hi, " . "@" . $screen_name .  " This is a borning test tweet. " . $r ); 
	;}
	
	//these are now classes, but they will only return one instance. It will be the last one.  This is why you really have to use the count function above. 
	/*
	echo   $name . "\n";
	echo   $screen_name  . "\n";
	echo   $location . "\n"; 
	echo   $text . "\n";
	echo   $time_created . "\n";
	*/
	
	// [in_reply_to_screen_name] =>
	
			
	 //$reply = $cb->statuses_update("status= Hi, " . "@" . $status->user->screen_name . " This is a borning test tweet." . $r );
					
	  
	// Post stuff - test:
	// Parameters list here: https://dev.twitter.com/rest/reference/post/statuses/update
	$params = array(
	  'status' => '.@N0RTHERNER Test reply?',
	  'in_reply_to_status_id' => "825936688031506433"
	  
	);
	$reply = $cb->statuses_update($params);
	
	//print_r ($data);
	
	//print_r(array_values($reply));  

?>