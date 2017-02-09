<?php
	//http://www.pontikis.net/blog/auto_post_on_twitter_with_php
	// require codebird
	require_once('codebird.php');
	require_once('config.inc');
	require_once('config_sql.inc');
	
	/*
	 * Define some strings that we can respond to the tweets with
	 */
//https://www.tutorialspoint.com/php/mysql_select_php.htm
//added mysql info to config_sql.inc. Call using $conn variable from that file. 

echo '<pre>';
	// First, create a callback function.
	// Will also be called with $message==NULL every second.
	function event_listener($message)
	{
		// So, check if we've actually been given a message to handle
		if ($message !== NULL) {
			// Examine the type of event, and do different things. Split out into their own functions only in order to keep things neater & more readable
			if( isset($message->event) && $message->event == "favorite" ) {
				handle_favourite( $message );
			} elseif( isset($message->event) && $message->event == "follow" ) {
				handle_follow( $message );
			} elseif( isset($message->text) ) {
				handle_message( $message );
			}
			// Push out our text to the page, don't wait for whole page execution to finish
			flush();
		}
		
		// return false to continue streaming
		// return true to close the stream
		
		// close streaming after 1 minute for this simple sample
		// don't rely on globals in your code!
		
		/*  My PHP times out after 30 seconds anyway :D  
		if (time() - $GLOBALS['time_start'] >= 60) {
			return true;
		}					*/
		
		return false;				
	}
	
	//I wrote this piece, and it works.  I know it won't be very elegant, but ... I was happy with it.  Then I realised that I needed it to be run each time if I wanted a fresh result in my replies, etc.  That's when I tried to make it a function.  However, it's not as easy as JS. ;)  I can't get my head around the () bit after you name the function.  Is this the argument? All the tutorials I find (I've found A LOT) are too basic.  I have lost the will to live over it and can't really see the wood for the trees any more, if that makes any sense.  


	  				$conn;              
        //$sql is the name of the query that will select the data
                    $sql = "SELECT text FROM tweets ORDER BY RAND() LIMIT 1";
                    $result = $conn->query($sql);

                    $text = mysqli_fetch_array($result);  /*I don't fully understand how all these instructions (mysqli_fetch_array) work, or why they are required.  */ 
                        $texts =$text['text'];
                        $texts;  /*simply to try to have one simple call */ 
                    	echo $texts;
                    
                         mysqli_close($conn); 
                    
              
       /*

       //couldn't get this to work at all.  I did spend a lot of time working it out, and think I get it, but I'm still flaky on completely understanding why there was the second call.  And... it looks like I deleted it here for some reason.  Brain fried.  Sorry.  

                         function select_reply( $id=0 ) {
  // our DB connection is defined outside this function, so we need to bring it in to our scope talk to it
  global $conn;

  // ID 0 is the default, and means we dont care which reply we get, so select a random one from db
  if( $id = 0 ) {
    return mysqli_fetch_array($conn->query("SELECT text FROM tweets ORDER BY RAND() LIMIT 1"));
  // If ID is something else, assume we want that particular row instead
  // NB - there's no user escaping here, you're open to SQL injection attacks. That's Bad(tm).
  } else {
    return mysqli_fetch_array($conn->query("SELECT text FROM tweets WHERE id = ".(int)$id." LIMIT 1"));
  }
}

echo select_reply();  */
//-------------------------------------------
	// This function deals with people favouriting our tweets.
	function handle_favourite( $event )
	{
		// Our Codebird object is out of this function's scope - bring it in...
		global $cb;
		
		echo '<p> Got favourite </p>';
		// If the tweeter isn't a willing victim, abort!
		$willing_victims = array( 'Phil_Tanner', 'N0RTHERNER' );
		if( array_search( $event->source->screen_name, $willing_victims ) === false ) 
		{
			echo '<p>Aborting reply to <a href="https://twitter.com/'.$event->source->screen_name.'">@'.$event->source->screen_name.'</a> - Not a willing victim!</p>';
			return false;
		}

				find_data ();
		// Parameters list here: https://dev.twitter.com/rest/reference/post/statuses/update
		$params = array(
		'status' => 'Hey @'.$event->source->screen_name . $texts . ', I love you too! (but not as much as I love @N0RTHERNER!!)'
		);
		// Actually perform our reply
		$reply_tweet = $cb->statuses_update($params);
		// HTTP status 200 means it all worked.
		if( isset($reply_tweet->httpstatus) && $reply_tweet->httpstatus == 200 ) {
			// So, print to the screen
			echo '<p>Thanked <a href="https://twitter.com/'.$event->source->screen_name.'">@'.$event->source->screen_name.'</a> for a favourite.</p>';
			// Then, also dump to the error log for future reference (even tho it's not an error).
			error_log('Favourite: @'.$event->source->screen_name);
		}
		flush();
	}
	
	// This function deals with people following us
	function handle_follow( $event )
	{
		// Our Codebird object is out of this function's scope - bring it in...
		global $cb;
		
		echo '<p> Got a follow </p>';
		// If the tweeter isn't a willing victim, abort!
		$willing_victims = array( 'Phil_Tanner', 'N0RTHERNER' );
		if( array_search( $event->source->screen_name, $willing_victims ) === false ) 
		{
			echo '<p>Aborting reply to <a href="https://twitter.com/'.$event->source->screen_name.'">@'.$event->source->screen_name.'</a> - Not a willing victim!</p>';
			return false;
		}			
		
   
			// Parameters list here: https://dev.twitter.com/rest/reference/post/statuses/update
		$params = array(
		  'status' => 'Hey @'.$event->source->screen_name . $texts . ', thanks for the follow!'.rand(0,99)
		);
		// Actually perform our reply
		$reply_tweet = $cb->statuses_update($params);
		// HTTP status 200 means it all worked.
		if( isset($reply_tweet->httpstatus) && $reply_tweet->httpstatus == 200 ) {
			// So, print to the screen
			echo '<p>Thanked <a href="https://twitter.com/'.$event->source->screen_name.'">@'.$event->source->screen_name.'</a> for a follow.</p>';
			// Then, also dump to the error log for future reference (even tho it's not an error).
			error_log('Follow: @'.$event->source->screen_name);
		}
		// Then, follow the user back. It's only polite....
		$params = array( 
		  'screen_name' => $event->source->screen_name
		);
		$follow_action = $cb->friendships_create($params);
		// HTTP status 200 means it all worked.
		if( isset($follow_action->httpstatus) && $follow_action->httpstatus == 200 ) {
			// So, print to the screen
			echo '<p>Followed <a href="https://twitter.com/'.$event->source->screen_name.'">@'.$event->source->screen_name.'</a> back.</p>';
			// Then, also dump to the error log for future reference (even tho it's not an error).
			error_log('Followed back: @'.$event->source->screen_name);
		}
		flush();
	}
		
	// This function deals with people favouriting our tweets.
	function handle_message( $tweet )
	{
		// Our Codebird object is out of this function's scope - bring it in...
		global $cb, $texts;
		
		// We get a notification about us tweeting. So avoid endless loops!
		if( $tweet->user->screen_name == 'newphpbot' )
		{
			echo '<p>Cowardly refusing to reply to myself....</p>';
			return false; 
		}
		
		// If the tweeter isn't a willing victim, abort!
		$willing_victims = array( 'Phil_Tanner', 'N0RTHERNER' );
		if( array_search( $tweet->user->screen_name, $willing_victims ) === false ) 
		{
			echo '<p>Aborting reply to <a href="https://twitter.com/'.$tweet->user->screen_name.'">@'.$tweet->user->screen_name.'</a> - Not a willing victim!</p>';
			return false;
		}
		
		// Randomly pick a response to send back...
		$random_reply_text= $texts;
		
		// Parameters list here: https://dev.twitter.com/rest/reference/post/statuses/update
		$params = array(
		  'in_reply_to_status_id' => $tweet->id_str, // What's the ID of the tweet we're replying to
		  'status' => '@'.$tweet->user->screen_name.' '.$random_reply_text // What text do we want to reply with? (NB *must* contain the screen name of the person you're responding to)
		);
		// Actually perform our reply
		$reply_tweet = $cb->statuses_update($params);
		// HTTP status 200 means it all worked again.
		if( isset($reply_tweet->httpstatus) && $reply_tweet->httpstatus == 200 ) {
			// So, print to the screen
			echo '<p>Reply sent to <a href="https://twitter.com/'.$tweet->user->screen_name.'">@'.$tweet->user->screen_name.'</a> - "'.$random_reply_text.'"</p>';
			// Then, also dump to the error log for future reference (even tho it's not an error). -                   Why?  And how is this accessed and used? 
			error_log('@'.$tweet->user->screen_name.' - "'.$random_reply_text.'"');
		}
		flush();
	}
	
	                                        //why is this at the end?  
	// not considered good practice in real world!
	$GLOBALS['time_start'] = time();
	
	
	// Tell Codebird what to do when it gets a notification
	$cb->setStreamingCallback('event_listener');
	// Then tell it to start listening for user notifications (https://dev.twitter.com/streaming/userstreams) 
	$listener = $cb->user();
	
	
	// See the *Mapping API methods to Codebird function calls* section for method names.
	// $reply = $cb->statuses_filter('track=Windows');   
	
?>