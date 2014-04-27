<?php

	/*
	 * The RESTClient for the Call Control Server
	 * based on : 
	 * http://wiki.sipfoundry.org/display/sipXecs/Configuration+RESTful+Service
	 *
	 * @author V. Vogelesang
	 *
	 */	
	class RESTClient
	{	
		private $sipxHost;
		private $controller;		
		
		public function __construct($controller)
		{
			$this->controller = $controller;
			$this->sipxHost = $controller->getSettingsArray()['sipxHost'];
		}
		
		/**
		 * Setup the call
		 *
		 * @param commandObject
		 * @return xml document
		 * 
		 */
		function callSetup ($commandObject)
		{			
			try
			{
				$from = $commandObject->From;
				$to = $commandObject->To;
				$pin = $commandObject->Pin;
				
				echo 'Calling from extension '. $from . ' to extension ' . $to . '.....'. "\r\n";
				
				// Set url for A and B party, call via INVITE
				$url = "http://" . $this->sipxHost . ":6667/callcontroller/" . $from . "/" . $to . 
					   "?sipMethod=INVITE&subject=c2c&resultCacheTime=5";
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
		 * Terminate the call
		 * 
		 * @param commandObject
		 * @return xml document
		 */
		public function callTerminate($commandObject)
		{
			// Since BYE is not supported we transfer to a non existing number
			$commandObject->Target = "0999999999";
			return $this->callTransfer ($commandObject);
		}		
		
		/**
		 * Transfer the call	
		 *
		 * @param commandObject
		 * @return xml document
		 *
		 */
		public function callTransfer ($commandObject)
		{
			try
			{				
				$from = $commandObject->From;
				$to = $commandObject->To;
				$target = $commandObject->Target;
				$pin = $commandObject->Pin;			
				
				echo 'Transferring call from extension '. $to . ' to extension ' . $target . '.....'. "\r\n";				

				// Set url for A and B party, call via INVITE
				$url = "http://" . $this->sipxHost . ":6667/callcontroller/" . $from . "/" . $to . "?target=" . $target . "&action=transfer";
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
		 * Get the call statusrecords
		 *		
		 * @param commandObject
		 * @return xml document
		 *
		 */
		public function getStatus ($commandObject)
		{				
			try
			{
				$from = $commandObject->From;
				$to = $commandObject->To;
				$pin = $commandObject->Pin;
				// Set url for A and B party, call via INVITE
				$url = "http://" . $this->sipxHost . ":6667/callcontroller/" . $from . "/" . $to;
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


