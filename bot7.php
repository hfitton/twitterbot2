<!-- Tell the PHP server to interpret this block -->
<?php
	//http://www.pontikis.net/blog/auto_post_on_twitter_with_php
	// require codebird
	/*
	 * require_once is useful. It stops the page progressing if it can't find the file (unlike include() which only gives a warning),
	 * and if you give it the same file twice (so, lets say that the config.inc file ALSO says it requires the codebird.php file), 
	 * it will only include it once (unlike require() which will include the whole file again each time you call it).
	 * require() is useful when you want to do the same thing over and over - think of it like a function call held inside a file.
	 * But it won't work for us here, because you'll get warnings about how the function names and class names and variables etc are
	 * already defined when you try to include it the second time.
	 * Also note this syntax looks for files in the current directory, but you could require_once('/home/helen/foo.php'); to pull ANY
	 * file on the PC into here, but then keeping track of them all gets tough :D
	 */ 
	require_once('codebird.php');
	require_once('config.inc');
	//added mysql info to config_sql.inc. Call using $conn variable from that file. 
	require_once('config_sql.inc');

	// This is just a pretty-print basically, so var_dump()s are readable. Can be removed if you like
	echo '<pre>';

	/*
	 * Right, you asked why stuff was at the end, but in reality, it makes sense to address that here.  Imagine the following code:
	 *
	 * echo hello_world();
	 * function hello_world() { return 'Hello World!'; }
	 *
	 * It's fairly easy for you to understand what you want it to do, right? But imagine the computer compiling the code in sequence.
	 * Line 1 says to print the result of the hello_world() function to the screen. But it has no idea what the hello_world() function is
	 * yet, it's not heard of it, so it gives up and throws an error, before it even has a chance to get to line 2 and work it out. 
	 * But swap the sections around, and everything's fine:
	 *
	 * function hello_world() { return 'Hello World!'; }
	 * echo hello_world();
	 * 
	 * Now, the first thing it sees is what do you want the hello_world() function to do, then you call it afterwards, and hey-presto, 
	 * everything works as you expect. Some programming languages are clever enough to work this out. Some aren't. So through habit 
	 * (read bored of pulling hair out trying to debug code) I now just declare all the functions before I want to use them anywhere, 
	 * so they all sit up at the very top of the code, nice and early. Before the page does any processing at all, I'm saying "when I 
	 * tell you to talk_to_phil(), here's the steps you need to take. Don't worry about _when_ to talk_to_phil(), I'll tell you that later,
	 * but for now, when I tell you to do that, here's what I want you to do...".
	 *
	 * So... This is just scene setting. Think of it as your RAMs, these are your mitigation steps. You need to communicate them to everyone
	 * before you go out on the tramp, because it's no good telling them what response to take *after* the event has happened.  We're not 
	 * actually doing anything yet. Just planning....
	 */
	
	/*
	 * And before we do that, let's look at functions themselves. Apologies if I teach you to suck eggs here, but we'll start right back
	 * at the beginning so we don't miss anything.
	 *
	 * Functions are building blocks of code. They don't *need* to exist. You can just handle everything through one big long page, and 
	 * include() and all sorts of nastyness, but in reality, they make your life easier. A good programmer is a lazy programmer. We don't
	 * like doing things over and over, so, if it's repetative, we'll build a function for it. Because, why not?
	 *
	 * First things first tho, you need to tell PHP that this is a code block you want to reuse. So you start the line with the keyword
	 * "function", (this is not quite true, but true enough for now) and then PHP starts to build the block. Next word is what you 
	 * want the function to be called ("hello_world", or "talk_to_phil" or "my_little_pony" it doesn't really matter, so long as the
	 * function doesn't already exist. So, you can't call your function "var_dump" or "echo" or "mysqli_connect" etc. (this is not 
	 * quite true, but true enough for now).
	 * 
	 * And then, you tell it the arguments you want to use. Arguments make functions more useful.  The hello_world() example above used
	 * no arguments, and does the same thing every damned time you run it. Useful - to a point. Imagine you want to build the "talk_to_phil()" 
	 * functionality tho. If all you ever say to me is "Hi", it won't be long before I stop talking to you, so:
	 *
	 * function talk_to_phil(){ echo 'Hi'; }
	 *
	 * Will get really old, really quickly. So, lets change our function, so we can control what we say. We could do this a few ways, this 
	 * would work, for instance:
	 *
	 * function talk_to_phil(){ global $chat; echo $chat; }
	 * $chat = "How are you?";
	 * talk_to_phil();
	 * $chat = "Isn't it nice today?";
	 * talk_to_phil();
	 *
	 * But it's a little clumsy, and quickly gets unwieldly. So instead, we can tell the function to expect a string to echo:
	 *
	 * function talk_to_phil( $words_to_say ){ echo $words_to_say; }
	 * $chat = "Hi";
	 * talk_to_phil( $chat );
	 * talk_to_phil( "My name is fred" );
	 *
	 * Both of those syntaxes work. And you will notice that the variable name you pass into the function doesn't need to match the
	 * argument name, it doesn't care. And you don't even need to give it a defined variable, you can just give it a string, all good.
	 * Hopefully, we're a little clearer now. But lets say you want to chose how to send the message each time, so you can develop the
	 * function to always talk to me, using different routes. We can set another variable for that and do in-function comparisons:
	 *
	 * function talk_to_phil( $words_to_say, $how_to_say_it='normal' ) {
	 * 	if( $how_to_say_it = 'normal' ) {
	 * 		echo $words_to_say;
	 * 	} elseif( $how_to_say_it = 'shout' ) {
	 *		echo strtoupper( $words_to_say );
	 *	}
	 * }
	 * talk_to_phil('Hi');
	 * talk_to_phil('Hey', 'shout');
	 * 
	 * This will result in:
	 * Hi
	 * HEY
	 * printing to screen, because the second argument, I have given a default value of 'normal', so if you don't pass it in, it assumes
	 * you mean normally. Otherwise you can choose to should. Default values in functions are useful, so that you can extend it - for something
	 * like "talk to phil through email" where you'd need to give your to address, from address, and subject line too. But shouting doesn't need 
	 * an email address - so you make it optional:
	 *
	 * function talk_to_phil( $words_to_say, $how_to_say_it='normal', $to='', $from='', $subject='' ) {
	 * 	if( $how_to_say_it = 'normal' ) {
	 * 		echo $words_to_say;
	 * 	} elseif( $how_to_say_it = 'shout' ) {
	 *		echo strtoupper( $words_to_say );
	 *	} elseif( $how_to_say_it = 'email' ) {
	 *		send_mail( $to, $from, $subject, $words_to_say );
	 * 	}
	 * }
	 * 
	 * And you can see you can daisy-chain your arguments from one to another here.  If you didn't give the $to='' part, whenever you just wanted
	 * to echo, you'd get a warning that the function wanted 5 arguments, and you'd only supplied 1.
	 */

	// First, create a callback function. This will receive the event sent from Twitter.
	// Will also be called with $message==NULL every second.
	// Remember, this isn't doing anything than defining the handler yet. It's not actually listening at this point, 
	// only being told what we want it to do when we do tell it to listen.
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

	/*
	 * So, yes. I created some functions to handle individual parts ( like follows, messagess etc).  So let's tweak 
	 * your code into a self-contained function.  
	 * This function is going to do one thing only, that's pull a single random text from the database and return it.
	 * So we don't need an argument at this point. And remember, we're only defining the function, not calling it yet
	 */
	function get_random_text_from_db()
	{
		// $conn is defined in your config file, so outside the scope of this function, so if we want to use it, 
		// we need to tell the function to import it in.
		global $conn;

		/*
		 * So, the various instructions & why they're required... They can be simplified down - but they're split out like this for readability.
		 * $sql = "select foo from bar limit 1";   // The text we'll send to the DB
		 * $res = $conn->query($sql);              // Send the SQL to the DB. $res now contains the result of the query. Success, failure, error message etc.
		 * $row = mysqli_fetch_array($res);        // This now takes that result, and pulls the data back from the table, in a named array
		 * $txt = $row['text'];                    // We now, finally, have the actual text stored in the DB available to us as $txt. Yay!
		 * 
		 * Or... we can remove each step and daisy chain them together, because we don't need to access $res or $row, 
		 * or $sql anymore, so why use the memory space? Just work it out, pass it to the next function in the chain... 
		 * So we end up with:
		 */
		$row = mysqli_fetch_array( $conn->query( "SELECT text FROM tweets ORDER BY RAND() LIMIT 1" ) );
		$txt = $row['text'];

		// And now, we send it out of the function, back to whoever asked for it...
		return $txt;

		// mysqli_close($conn);  // We don't want to close this off, otherwise we can't access the DB again next time..
	}


	//-------------------------------------------
	// This function deals with people favouriting our tweets. 
	// We're going to pass in the the event itself as an argument, so we can access the details of the event - who, when, what etc...
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
		global $cb;
		
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
		// Grab the text we want to send back from the database, using our newly defined function.
		$random_reply_text= get_random_text_from_db();
		
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

	/*
	 * Right. So... now, we have the functions all defined. But our page hasn't actually done anything yet. 
	 * We've not called any of the functions, we've not told it to do anything, we've not started any of the
	 * cogs in motion, only set up their shape.
	 * 
	 * So now, we're going to start the wheels turning. Codebird has a function that sets up a heartbeat
	 * to listen for Twitter events. It takes one argument, and the argument is the name of the function to
	 * pass the Twitter event to when it gets the notification.
	 * 
	 * And our main function that we created to know what to do with these events was the first function we
	 * created, right up at the top of the page - event_listener();  That function takes one argument, the
	 * event that Twitter sent thru. Codebird handles that argument passing, so it's not clear it happens in
	 * the code below, but it does. Trust me ;)
	 */
	$cb->setStreamingCallback('event_listener');

	/*
	 * We've still not actually done anything yet.  Only now told Codebird what to do when it gets something.
	 * But it's still not ACTUALLY listening. So... tell it to do so.
	 * /
	$listener = $cb->user();

?>
