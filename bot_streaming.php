<?php

	//http://www.pontikis.net/blog/auto_post_on_twitter_with_php
	// require codebird
	require_once('codebird.php');
	/* Config.inc needs to contain:
		<?php
			\Codebird\Codebird::setConsumerKey("cons key", "cons key secret");
			$cb = \Codebird\Codebird::getInstance();
			$cb->setToken("access token", "token secret");
	*/
	require_once('config.inc');
	
	/*
	 * Define some strings that we can respond to the tweets with
	 */
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
		}
		*/
		return false;
	}
	
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
		
		// Parameters list here: https://dev.twitter.com/rest/reference/post/statuses/update
		$params = array(
		  'status' => 'Hey @'.$event->source->screen_name.', I love you too! (but not as much as I love @N0RTHERNER!!)'
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
	
	// This function deals with people favouriting our tweets.
	function handle_message( $tweet )
	{
		// Our Codebird object is out of this function's scope - bring it in...
		global $cb, $yorkshire_array;
		
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
		$random_reply_text = $yorkshire_array[ rand(0, count($yorkshire_array)-1) ];
		
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
			// Then, also dump to the error log for future reference (even tho it's not an error).
			error_log('@'.$tweet->user->screen_name.' - "'.$random_reply_text.'"');
		}
		flush();
	}
	
	/*
	// not considered good practice in real world!
	$GLOBALS['time_start'] = time();
	*/
	
	// Tell Codebird what to do when it gets a notification
	$cb->setStreamingCallback('event_listener');
	// Then tell it to start listening for user notifications (https://dev.twitter.com/streaming/userstreams) 
	$listener = $cb->user();
	
	
	// See the *Mapping API methods to Codebird function calls* section for method names.
	// $reply = $cb->statuses_filter('track=Windows');
	
?>