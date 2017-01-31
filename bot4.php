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
	
	/* 
	 * First off, we're going to do a search for a set of tweets that have a given string 
	 */
	// What parameters do we want to search against?
	$params = array(
		'q' => 'notaproblemnewphpbot', // Our search string query - i.e. what words are we searching for?
		'lang' => 'en' // What language do we want results in?
	);
	// Perform the search, then dump the results into an array called $search_response
	$search_response = (array) $cb->search_tweets($params);
	
	// HTTP status 200 means "ok" - i.e. our search worked & the Twitter API was happy.  So we can continue.
	if( isset($search_response['httpstatus']) && $search_response['httpstatus'] == 200 ) {
		// At some point, we might care about the meta data, and the twttier API rate limits etc, but at the moment, we're only interested in the actual statuses (tweets), so filter our response down to that, purely for convieniance/readability
		$tweets = (array) $search_response['statuses']; 
		
		//orginal code from:  https://nerdyjunkyard.wordpress.com/2014/01/30/dealing-with-tweet-data-by-php/ - torn apart by me, but the basics are still recognisable. 
		
		/*
		 * So, now we step through each tweet we got back in the search response, so we can reply to it individually
		 */
		foreach( $tweets as $tweet ){
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
		} // end foreach $tweets
		
	} // end HTTP status 200 check

?>