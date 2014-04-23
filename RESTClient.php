<?php

	/*
	 * The RESTClient for the Call Control Server
	 *
	 * @author V. Vogelesang
	 *
	 */
	
	class RESTClient
	{
	
		public function __construct()
		{
				
		}
		
		/**
		 * Setup the call
		 * Curl commands based on : http://wiki.sipfoundry.org/display/sipXecs/Configuration+RESTful+Service
		 *
		 * @param commandObject
		 * 
		 */
		function callSetup ($commandObject)
		{
			echo 'Calling from extension '. $commandObject->From . ' to extension ' . $commandObject->To . '.....'. "\r\n";
			
			try
			{
				$from = $commandObject->From;
				$to = $commandObject->To;
				$pin = $commandObject->Pin;
				// Set url for A and B party, call via INVITE
				$url = "http://192.168.1.200:6667/callcontroller/" . $from . "/" . $to . "?sipMethod=INVITE";
				echo 'Execute command: ' . $url . "\r\n"; 
				// Init session
				$ch = curl_init();	
						
				// Set the options, use A/from credentials!
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_USERPWD, $from.":".$pin);	
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				
				// Execute the command	
				$result = curl_exec($ch);
				//echo $result;		
				curl_close($ch);	
				return $result;	
				
			}
			catch (Exception $e) 
			{
				echo $e->getMessage();
			}
		}
		
		/**
		 * Setup the call
		 * Curl commands based on : http://wiki.sipfoundry.org/display/sipXecs/Configuration+RESTful+Service
		 *
		 * @param commandObject
		 *
		 */
		function getStatus ($commandObject)
		{
				
			try
			{
				$from = $commandObject->From;
				$to = $commandObject->To;
				$pin = $commandObject->Pin;
				// Set url for A and B party, call via INVITE
				$url = "http://192.168.1.200:6667/callcontroller/" . $from . "/" . $to;
				//echo 'Execute command: ' . $url .  "\r\n";
					
				// Init session
				$ch = curl_init();
				
				// Set the options, use 'from' credentials!
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
				curl_setopt($ch, CURLOPT_USERPWD, $from.":".$pin);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				
				// Execute the command
				$result = curl_exec($ch);
				curl_close($ch);
				//echo $result;
				return $result;
					
			}
			catch (Exception $e)
			{
				echo $e->getMessage();
			}
		}
	}


