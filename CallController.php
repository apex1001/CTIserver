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
	
	require_once('./store/domain/CommandObject.php');
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
			$this->userArray = new UserArray();
			$this->restClient = new RESTClient($controller);
			$this->updateThread = new UpdateThread($this);
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
			if (strpos($commandObject->From, '0') == 0 )
			{
				$xmlResponse = $this->restClient->callSetupExternal($commandObject);
			}
			else
			{
				$xmlResponse = $this->restClient->callSetup($commandObject);				
			}

			$xmlStripped = str_replace ("-","", $xmlResponse);
			$response = @simplexml_load_string($xmlStripped);
						
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
		
		/**
		 * Terminate a call
		 * 
		 * @param $commandObject
		 * @param $user
		 * 
		 */
		public function callTerminate($commandObject, $user)
		{
			if ($commandObject != null && $commandObject->Command == 'terminate')
			{				
				$xmlResponse = $this->restClient->callTerminate($commandObject);
				$xmlStripped = str_replace ("-","", $xmlResponse);
				$response = @simplexml_load_string($xmlStripped);					
			}	
			$this->updateThread->removeUserByObject($user);
		}
		
		/**
		 * Transfer a call
		 * 
		 * @param $commandObject
		 * @param $user
		 */
		public function callTransfer($commandObject, $user)
		{
			if ($commandObject != null && $commandObject->Command == 'transfer')
			{				
				$xmlResponse = $this->restClient->callTransfer($commandObject);
				$xmlStripped = str_replace ("-","", $xmlResponse);
				$response = simplexml_load_string($xmlStripped);					
			}	
			$this->updateThread->removeUserByObject($user);
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
			$this->updateThread->addUser($userArray);		
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
		
		/**
		 * Returns the Server controller instance
		 * 
		 * @return $serverController
		 */
		public function getController()
		{
			return $this->controller;
		}
	}
	
	/**
	 * Update thread for call status polling
	 *
	 */	
	class UpdateThread extends Thread
	{
		private $controller;
		private $activeUserList;
		private $restClient;
		private $listChanged;
	
		public function __construct($controller)
		{
			$this->activeUserList = new ActiveUserList();
			$this->controller = $controller;
			$this->restClient = new RESTClient($controller->getController());			
		}
	
		/**
		 * Main updateThread routine
		 * 
		 */
		public function run()
		{
			while (true)
			{					
				try 
				{				
					// echo count($this->activeUserList);			
					// sleep(1);
					if (count($this->activeUserList) > 0)
					{						
						foreach ($this->activeUserList as $key => $userArray)
						{
							if ($this->listChanged) 
							{
								$this->listChanged = false;
								break;
							}
																
							// Check for call status after pollTimeout
							if ((microtime(true) - $userArray[3]) > 2)
							{								
								// Reset timer for user
								$userArray[3] = microtime(true);
								
								// Get call status
								$status = $this->getCallStatus($userArray);							
								if ($status != null && $status != "null")
								{								
									// Has the call status changed? Send new commandObject to client!
									$callStatus = $userArray[2]->Status;
									
									if ($status != $callStatus)
									{
										echo 'Call status of user: ' . $userArray[0] . ' is: ' . $status . "\r\n";									
										
										// Get commandObject, user and update status
										$socket = $userArray[4];									
										$commandObject = $userArray[2];
																			
										$commandObject->Status = $status;
										$userArray[2] = $commandObject;
										$userArray[4] = $socket;
										
										// Send changed status
										$this->controller->sendCommand($commandObject, $userArray[1], $socket);
										
										// Remove user from list if call terminated
										if ($status == "Terminated Dialog")
										{																		
											// Remove user from the list
											$this->removeUser($key);	
										}							
									}						
								}
	
								if ($status == null)
								{
									echo 'Call status of user: ' . $userArray[0] . " is: Terminated dialog\r\n";
										
									// Get commandObject, user and update status
									$socket = $userArray[4];
									$commandObject = $userArray[2];
									
									$commandObject->Status = "Terminated Dialog";
									$userArray[2] = $commandObject;
									$userArray[4] = $socket;
									
									// Send changed status
									$this->controller->sendCommand($commandObject, $userArray[1], $socket);									
									
									// Remove user from the list
									$this->removeUser($key);
								}
	 						}
						}						
					}
				}
				catch (Exception $e)
				{
					echo $e->getMessage();
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
			try 
			{
				// Get the XML response and turn it into a response object
				$xmlResponse = $this->restClient->getStatus($userArray[2]);
				$xmlStripped = str_replace ("-","", $xmlResponse);
				//echo $xmlResponse;
				
				$response = @simplexml_load_string($xmlStripped);
				if ($response != null && property_exists($response, 'dialog') && count($response) > 0)
				{
					if (is_array($response->dialog)) {
						// Get first record of the dialog array
						$status = $response->dialog[0]->dialogstate;
						return (string) $status;
					}
					else
					{
						$status = $response->dialog->dialogstate;
						return (string) $status;
					}
				}				
			}
			catch (Exception $e)
			{
				echo 'Error:' . $e;
			}
		
			return null;		
		}
		
		/**
		 * Add a user to the activeUserlist 
		 *
		 * @param $userArray
		 *
		 */
		public function addUser($userArray)
		{						
			$this->activeUserList[] = $userArray;
			$this->listChanged = true;
			$this->setList($this->activeUserList);			
		}
		
		/**
		 * Set the active user list
		 * 
		 * @param $activeUserList
		 */
		public function setList($list)
		{
			$this->activeUserList = $list;
		}
		
		/**
 		 * Remove a user from the active user list
 		 *
 		 * @param $index of entry to remove
 		 * 
 		 */		
		public function removeUser($index)
		{
			unset($this->activeUserList[$index]);
			$this->listChanged = true;
		}
		
		/**
		 * Remove a user from the active user list
		 *
		 * @param $user object to remove
		 *
		 */
		public function removeUserByObject($user)
		{
			foreach($this->activeUserList as $key => $userArray)
			{
				if ($userArray[0] == $user->id)
				{
					$this->removeUser($key);
					break;
				}
			}
		}
	}
		