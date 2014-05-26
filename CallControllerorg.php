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
		private $updateThread2;
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
				$commandObject->Status = "Terminated Dialog";
				$commandObject->Value = array(array($commandObject->To));
				$this->sendCommand($commandObject, $user, $user->socket);
				
				// Write history for given call
				$this->writeHistory($commandObject);
				
				return;
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

			// Write history for given call
			$this->writeHistory($commandObject);
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
				// Transfer the call
				$xmlResponse = $this->restClient->callTransfer($commandObject);
				$xmlStripped = str_replace ("-","", $xmlResponse);
				$response = simplexml_load_string($xmlStripped);								

				// Terminate the original call if it was a two line transfer
				sleep(6);				
				$commandObject->To = $commandObject->Target;
				$xmlResponse = $this->restClient->callTerminate($commandObject);				
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
		 * 
		 */
		public function getController()
		{
			return $this->controller;
		}
		
		/**
		 * Write history for given call
		 * 
		 * @param $commandObject
		 * 
		 */
		public function writeHistory($commandObject)
		{
			// Write history entry to the database
			$history = new History();
			$dialledParty = $commandObject->To;
			if ($commandObject->Target != "")
				$dialledParty = $commandObject->Target;
			$history->setUsername($commandObject->User);
			$history->setDialledParty($dialledParty);
		
			$this->controller->getDaoFacade()->getHistoryDAO()->write($history);
		}
		
		/**
		 * Check the extension
		 *
		 * @param commandobject
		 * @param user
		 * @return boolean true if valid
		 * 
		 */
		public function checkExtension($commandObject, $user)
		{
			$extension = $commandObject->From;
			$response = strtolower($this->restClient->checkExtension($commandObject));
			//echo $response;
			return ($response != "pin mismatch" && $response != "user not found " . $extension );			
		}
	}
	
	/**
	 * Update thread for call status polling
	 *
	 */	
	class UpdateThread extends Thread
	{
		private $controller;
		private $daoFacade;
		private $activeUserList;
		private $restClient;
		private $listChanged;
	
		public function __construct($controller)
		{
			$this->activeUserList = new ActiveUserList();
			$this->controller = $controller;				
			$this->daoFacade = new DAOFacade($controller->getController());
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
					//echo count($this->activeUserList);			
					//sleep(1);					
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
								
								// Get call status for current extension								
								$extension = $userArray[2]->To;
								if ($userArray[2]->Target != "") 						
									$extension = $userArray[2]->Target;						
								$status = $this->getCallStatus($userArray, $extension);	
								
								if ($status != null && $status != "null")
								{								
									// Has the call status changed? Send new commandObject to client!
									$callStatus = $userArray[2]->Status;
									
									if ($status != $callStatus)
									{
										echo 'Call status of user: ' . $userArray[0] . ' is: ' . $status . "\r\n";									
										
										// Get commandObject, user and update status
										// Get then store again to get correct object reference. Don't change this
										$socket = $userArray[4];
										$commandObject = $userArray[2];																			
										$commandObject->Status = $status;
										$commandObject->Value = array(array($extension));
										$userArray[2] = $commandObject;
										//$userArray[4] = $socket;
										
										// If invalid socket, get it from original user object.
										if (gettype($socket) == "unknown type" || $socket == null)
										{
											$socket = $this->getSocketById($userArray[0]);
										}
										
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
		private function getCallStatus($userArray, $extension)
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
					if (is_array($response->dialog)) 
					{
						// Get first record of the dialog array matching the extension
						foreach ($response->dialog as $dialogEntry)
						{
							if (strpos($extension, $dialogEntry->remoteparty) !== false )
								return (string) $dialogEntry->dialogstate;
						}
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
		 * Get the Socket object for the given id
		 * 
		 * @param $idString
		 * @return $socket obejct
		 * 
		 */
		public function getSocketById($id)
		{
			foreach( $this->activeUserList as $item)
			{
				if ($item[0] == $id && gettype($item[4]) != "unknown type" && $item[4] != null)
				{
					return $item[4];
				}
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
		 * 
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
			// Pass socket object to 2nd line entry if applicable
			$socket = $this->activeUserList[$index][4];
			$userId = $this->activeUserList[$index][0];
			$commandObject = $this->activeUserList[$index][2];
			
			if (gettype($socket) != "unknown type" && $socket != null)
			{
				// Search for the same id with socket type unknown				
				foreach ($this->activeUserList as $key => $userArray)
				{
					if ($userArray[0] == $userId && (gettype($userArray[4]) == "unknown type" || $userArray[4] == null))
					{
						$userArray[4] = $socket;
						$this->activeUserList[$key] = $userArray;
						break;
					}
				}
			}
						
			unset($this->activeUserList[$index]);
			$this->listChanged = true;
			
			// Update history for given call			
			$this->updateHistory($commandObject);				
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
		

		/**
		 * Update the history for given call
		 *
		 * @param $commandObject
		 *
		 */
		public function updateHistory($commandObject)
		{
			// Update history entry in the database
			$history = new History();
			$dialledParty = $commandObject->To;
			if ($commandObject->Target != "")
				$dialledParty = $commandObject->Target;
			$history->setUsername($commandObject->User);
			$history->setDialledParty($dialledParty);

			// Make new connection object since it cannot be referenced from thread 0
			$connection = $this->daoFacade->getConnection();	
			$this->daoFacade->getHistoryDAO()->update($history, $connection);
		}
	}
		