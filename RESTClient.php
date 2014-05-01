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
		private $agentExtension;
		private $agentPin;	
		private $terminateExtension;
		
		public function __construct($controller)
		{
			$this->controller = $controller;
			$this->sipxHost = $controller->getSettingsArray()['sipxHost'];
			$this->agentExtension = $controller->getSettingsArray()['agentExtension'];
			$this->agentPin = $controller->getSettingsArray()['agentPin'];
			$this->terminateExtension = $controller->getSettingsArray()['terminateExtension'];
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
				
				if ($commandObject->Target != "")
					$to = $commandObject->Target;
				
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
		 * Setup an external call with a non sip extension
		 * as the from party. Uses agent sip extension
		 *
		 * @param commandObject
		 * @return xml document
		 *
		 */
		function callSetupExternal ($commandObject)
		{
			try
			{
				$from = $commandObject->From;
				$to = $commandObject->To;
				$pin = $commandObject->Pin;
		
				echo 'Calling from extension '. $from . ' to extension ' . $to . '.....'. "\r\n";
		
				// Set url for A and B party, call via INVITE
				$url = "http://" . $this->sipxHost . ":6667/callcontroller/" . $from . "/" . $to .
				"?agent=" . $this->agentExtension . "&sipMethod=INVITE&subject=c2c&resultCacheTime=5";
				echo 'Execute command: ' . $url . "\r\n";
				// Init session
				$ch = curl_init();
		
				// Set the options, use A/from credentials!
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_USERPWD, $this->agentExtension.":".$this->agentPin);
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
			// Since BYE is not supported we transfer to an auto hangup attendant
			$commandObject->Target = $this->terminateExtension;
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
				$pin = $commandObject->Pin;
				$to = $commandObject->To;
				
				if ($commandObject->Target != "")
				{
					$to = $commandObject->Target;
				}
				
				if (strpos($commandObject->From, '0') == 0 )
				{
					$from = $this->agentExtension;
					$pin = $this->agentPin;
				}					
								
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


