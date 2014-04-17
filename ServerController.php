<?php

	/*
	 * The ServerController for the CTI Client
	 * 
	 * Uses the WebSocketServer from Adam Alexander @ 
	 * https://github.com/ghedipunk/PHP-Websockets
	 * 
	 * @author V. Vogelesang
	 * @email apex1001@home.nl
	 *
	 */

	require_once('./library/websockets2.php');
	require_once('./commandObject.php');
	
	/**
	 * The ServerController for the CTI Client 
	 *
	 */
	class ServerController extends WebSocketServer 
	{
		
		/**
		 * Process incoming messages
		 * 
		 * @param user id
		 * @param message
		 * 
		 */		
		protected function process ($user, $message) 
		{
			echo 'Received message ' . $message . ' from ' . $user->socket . "\r\n";
			if ($message !="ping")
			{
				$commandObject = json_decode($message);				
				//var_dump ($commandObject);
				$this->callSetup($commandObject->From, $commandObject->To, $commandObject->Pin);				
			}
			
			//$this->send($user,$user->id."typed: " . $message .'\r\n');	
		}
	
		/**
		 * Handle incoming connection
		 *
		 * @param user id
		 *
		 */
		protected function connected ($user) 
		{
			echo 'Connection opened'. $user->socket . " " . $user->id . "\r\n";
		}
		
		/**
		 * Handle closed connection
		 *
		 * @param user id
		 *
		 */	
		protected function closed ($user) 
		{
			echo 'Connection closed to: ';
		}
		
		/**
		 * Setup the call
		 * Based on : http://wiki.sipfoundry.org/display/sipXecs/Configuration+RESTful+Service
		 *
		 * @param A-party
		 * @param B-party
		 * @param A-pin
		 */
		function callSetup($from, $to, $pinFrom)
		{
		
			echo 'Calling from extension '. $from . ' to extension ' . $to . '.....'. "\r\n";
				
			// Init session
			$ch = curl_init();
		
			// Set url for A and B party, call via INVITE
			$url = "http://192.168.1.200:6667/callcontroller/" . $from . "/" . $to . "?sipMethod=INVITE";
			
			echo 'Execute command: ' . $url;
		
			// Set the options, use A/from credentials!
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_USERPWD, $from.":".$pinFrom);
		
			// Execute the command
			$startTime = microtime(true);
		
			$result = curl_exec($ch);
			$diff = microtime(true) - $startTime;
			$sec = intval($diff);
			$micro = $diff - $sec;
		
			echo 'Time spent: ' . $sec . ':' . $micro;
		
			curl_close($ch);
		
			
		}
	}
	
	// Run server forever until stopped from the console.
	while (true) {
		
		// Init
		$url = "localhost";
		$port = "7777";
		
		// Start server
		echo 'Starting Call Control Server on ' . $url . ' port ' . $port . '..';
		$server = new ServerController($url, $port);
		try
		{
			$server->run();
		}
		catch (Exception $e)
		{
			$echo->stdout($e->getMessage());
		}
	}
	