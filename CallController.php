<?php

	/*
	 * The CallController for the Call Control Server
	 *
	 * @author V. Vogelesang
	 *
	 */

	// Used for updateThread since arrays are not thread safe
	class ActiveUserList extends Stackable { public function run() { } };
	class UserArray extends Stackable { public function run() { } };
	
	require_once('./commandObject.php');
	require_once('./RESTClient.php');
	
	class CallController
	{
		private $updateThread;
		private $restClient;
		private $activeUserList;
		private $controller;
		private $userArray;
		
		public function __construct($controller)
		{
			$this->controller = $controller;
			$this->activeUserList = new ActiveUserList();
			$this->userArray = new UserArray();
			$this->restClient = new RESTClient();
			$this->updateThread = new UpdateThread($this->activeUserList, $this);
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
					$this->sendCommand($commandObject, $user, $user->socket);
					return;
				}
			}
		
			// Add user to the active user list
			$this->addUser($commandObject, $user);				
		}
		
		public function callTerminate($commandObject, $user)
		{
			// Send bye
			// @todo
			
			// Remove from list
			// @todo
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
			$userArray = new UserArray();
			$userArray[] = $user->id;
			$userArray[] = $user;
			$userArray[] = $commandObject;
			$userArray[] = microtime(true);
			$userArray[] = $user->socket;
			$this->activeUserList[] = $userArray;
			$this->updateThread->setUserList($this->activeUserList);
		}
		
		/**
		 * Remove a user from the active user list, push to the
		 * status thread for continuous status polling
		 *
		 * @param $userList to remove user from
		 * @param $index of entry to remove
		 * @param $updateThread
		 * 
		 */		
		public function removeUser($userList, $index, $updateThread) 
		{
			unset($userList[$index]);
			$tempUserList = new ActiveUserList();
			
			// Make new userList from old and set it
			foreach($userList as $user)
			{
				$tempUserList[] = $user;
			}	
			$updateThread->setUserList($tempUserList);			
		}
		
		/**
		 * Send commandObject back to the serverController
		 * 
		 * @param $commandObject
		 * @param $user
		 * @param $socket
		 * 
		 */
		public function sendCommand($commandObject, $user, $socket)
		{
			$user->socket = $socket;
			$this->controller->sendCommand($commandObject, $user);						
		} 
	}
	
	/**
	 * Timer thread for call status polling
	 *
	 *
	 */	
	class UpdateThread extends Thread
	{
		private $controller;
		private $activeUserList;
		private $restClient;
		private $pollTimeout = 2;
		protected $glob;
	
		public function __construct(ActiveUserList $activeUserList, $controller)
		{
			$this->activeUserList = $activeUserList;
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
				if (count($this->activeUserList) > 0)
				{						
					for ($i = 0; $i < count($this->activeUserList); $i++)
					{
						$userArray = $this->activeUserList[$i];
															
						// Check for call status after pollTimeout
						if (microtime(true) - $userArray[3] > $this->pollTimeout)
						{	
							
							// Reset timer for user
							$this->activeUserList[$i][3] = microtime(true);		

							// Get call status
							$status = $this->getCallStatus($userArray);							
							if ($status != null && $status != "null")
							{								
								// Has the call status changed? Send new commandObject to client!
								$callStatus = $userArray[2]->Status;
								echo 'rest:' . $status . ' client co:' . $callStatus . "\r\n";
								
								if ($status != $callStatus)
								{
									echo 'Call status of user: ' . $userArray[0] . ' is: ' . $status . "\r\n";									
									
									// Get commandObject, user and update status
									$socket = $this->activeUserList[$i][4];									
									$commandObject = $this->activeUserList[$i][2];
																		
									$commandObject->Status = $status;
									$this->activeUserList[$i][2] = $commandObject;
									$this->activeUserList[$i][4] = $socket;
									
									// Send changed status
									//$socket = $this->activeUserList[$i][4];
									$this->controller->sendCommand($commandObject, $userArray[1], $socket);
									
									// Remove user from list if call terminated
									if ($status == "Terminated Dialog")
									{																		
										// Remove user from the list
										$this->controller->removeUser($this->activeUserList, $i, $this);	
									}							
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
		