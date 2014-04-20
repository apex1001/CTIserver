<?php

	/*
	 * The CallController for the Call Control Server
	 * 
	 * @author V. Vogelesang
	 *
	 */

	require_once('./commandObject.php');
	require_once('./RESTClient.php');
	
	class CallController
	{
		private $updateThread;
		private $restClient;
		private $activeUserList;
		private $controller;
		
		public function __construct($controller)
		{
			$this->controller = $controller;
			$this->activeUserList = array();
			$this->restClient = new RESTClient();
			$this->updateThread =  new UpdateThread($this);
			$this->updateThread->start();	
		}
		
		/**
		 * Setup the call
		 * 
		 * @param $commandObject
		 * @param $user
		 * 
		 */
		public function callSetup($commandObject, $user)
		{					
			$xmlResponse = $this->restClient->callSetup($commandObject);
			$xmlStripped = str_replace ("-","", $xmlResponse);
			$response = simplexml_load_string($xmlStripped);
			
			// Check for busy
			if ($response != null && property_exists($response, 'errorcode'))
			{
				if ($response->errorcode == "403")
				{
					echo 'Call status of user: ' . $user->id . " is: busy\r\n";
					$commandObject->Status = "Busy Dialog";
					$this->sendCommand($commandObject, $user);
					return;
				}
			}
		
			// Add user to the active user list
			$this->addUser($commandObject, $user);				
		}
		
		public function callTerminate($commandObject, $user)
		{
			//
		}
		
		public function callTransfer($commandObject, $user)
		{
			//
		}
		
		/**
		 * Add a user to the active user list, push to the
		 * status thread for continuous status polling 
		 * 
		 * @param $commandObject
		 * @param $user
		 */
		public function addUser($commandObject, $user) 
		{
			$userArray = array ($user->id, $user, $commandObject, microtime(true));
			$this->activeUserList[] = $userArray;
			$this->updateThread->setUserList($this->activeUserList);
		}
		
		/**
		 * Remove a user from the active user list, push to the
		 * status thread for continuous status polling
		 *
		 * @param $userList to remove user from
		 * @param $index of entry to remove
		 * 
		 */		
		public function removeUser($userList, $index, $updateThread) 
		{
			unset($userList[$index]);
			$userList = array_values($userList);
			$updateThread->setUserList($userList);
		}
		
		/**
		 * Send commandObject back to the serverController
		 * 
		 * @param $commandObject
		 * 
		 */
		public function sendCommand($commandObject, $user)
		{
			$this->controller->sendCommand($commandObject, $user);						
		} 
	}
	
	/**
	 * Timer thread for call status polling
	 * @author apex
	 *
	 */	
	class UpdateThread extends Thread
	{
		private $controller;
		private $activeUserList;
		private $restClient;
		private $pollTimeout = 2;
		protected $glob;
	
		public function __construct($controller)
		{
			$this->controller = $controller;
			$this->restClient = new RESTClient();			
		}
	
		/**
		 * Main updateThread routine
		 * 
		 */
		public function run()
		{
			while (true)
			{
				$tempUserList = $this->activeUserList;	
				if (count($tempUserList) > 0)
				{					
					for ($i = 0; $i < count($tempUserList); $i++)
					{
						$userArray = $tempUserList[$i];						
						
						// Check for call status after pollTimeout
						if (microtime(true) - $userArray[3] > $this->pollTimeout)
						{	
							$status = $this->getCallStatus($userArray);							
							if ($status != null && $status != "null")
							{
								// Has the call status changed? Send new commandObject to client!
								$callStatus = $userArray[2]->Status;
								if ($status != $callStatus)
								{
									echo 'Call status of user: ' . $userArray[0] . ' is: ' . $status . "\r\n";
									
									// Remove user from list if call terminated
									if ($status == "Terminated Dialog")
									{
										// Get commandObject, user and update status
										$commandObject = $tempUserList[$i][2];
										$commandObject->Status = $status;
										
										// Remove user from the polling list & signal client
										$this->controller->removeUser($tempUserList, $i, $this);
										$tempUserList = $this->activeUserList;										
										$this->controller->sendCommand($commandObject, $userArray[1]);										
										continue;
									}
									
									$tempUserList[$i][2]->Status = $status;
									$commandObject = $tempUserList[$i][2];									
									$this->controller->sendCommand($commandObject, $userArray[1]);						
									$this->activeUserList = $tempUserList;
								}
									
								// Has the call status not changed? Wait for next pollTimeout
								if ($status == $callStatus)
								{
									$tempUserList[$i][3] = microtime(true);
									$this->activeUserList = $tempUserList;
								}
							}							
 						}
					}						
				}			
			}
		}		
		
		/**
		 * Get the actual status of a call
		 * 
		 * @param userArray
		 * @return call status string
		 * 
		 */
		private function getCallStatus($userArray)
		{			
			// Get the XML response and turn it into a response object
			$xmlResponse = $this->restClient->getStatus($userArray[2]);			
			$xmlStripped = str_replace ("-","", $xmlResponse);
			$response = simplexml_load_string($xmlStripped);
			if (count($response) > 0)
			{				
				// Get first record of the dialog array
				$status = $response->dialog[0]->dialogstate;				
				return (string) $response->dialog[0]->dialogstate;
			}			
			return null;		
		}
		
		/**
		 * Sets the activeUserlist for this object/thread.
		 * 
		 * @param activeUserList
		 * 
		 */
		public function setUserList($activeUserList)
		{
			$this->activeUserList = $activeUserList;
		}
	}
		