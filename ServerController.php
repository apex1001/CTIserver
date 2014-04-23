<?php

	/*
	 * The ServerController for the Call Control Server
	 * 
	 * Uses the WebSocketServer from Adam Alexander @ 
	 * https://github.com/ghedipunk/PHP-Websockets
	 * 
	 * @author V. Vogelesang
	 *
	 */

	require_once('./CallController.php');
	require_once('./library/websockets2.php');
	require_once('./store/domain/CommandObject.php');
	require_once('./store/dao/DAOFacade.php');
	
	/**
	 * The ServerController for the CTI Client 
	 *
	 */
	class ServerController extends WebSocketServer 
	{
		
		private $callController;
		private $daoFacade;
		
		public function __construct($url, $port)
		{
			parent::__construct($url, $port);
 			$this->callController = new CallController($this);
 			$this->daoFacade = new DAOFacade($this);
 		}	
		
		/**
		 * Process incoming messages
		 * 
		 * @param user
		 * @param message
		 * 
		 */		
		protected function process ($user, $message) 
		{
			//echo 'Received message ' . $message . ' from ' . $user->socket . "\r\n";
			if ($message !="ping")
			{
				$commandObject = json_decode($message);
				
				// If decode succesfull parse the commandObject
				if ($commandObject != null) 
				{
					$command = $commandObject->Command;
					switch ($command) 
					{
						case "call":
							$this->callController->callSetup($commandObject, $user);
							break;
	
						case "hangup":
							$this->callController->callTerminate($commandObject, $user);
							break;
	
						case "transfer":
							$this->callController->callTransfer($commandObject,  $user);
							break;
							
						case "getSettings":
							$this->getUserSettings($commandObject, $user);
							break;
							
						case "putSettings":
							// @todo: implement this
							break;
								
						case "getHistory":
							// @todo: implement this
							break;
					}
				}				
			}			
		}
	
		/**
		 * Handle incoming connection
		 *
		 * @param user
		 *
		 */
		protected function connected ($user) 
		{
			echo 'Connection opened to: ' . $user->socket . " " . $user->id . "\r\n";
		}
		
		/**
		 * Handle closed connection
		 *
		 * @param user
		 *
		 */	
		protected function closed ($user) 
		{
			echo 'Connection closed to: ' . $user->socket . " " . $user->id . "\r\n";
			$this->callController->callTerminate(null, $user);
		}	
		
		/**
		 * Send commandObject back to the serverController
		 *
		 * @param $commandObject
		 *
		 */
		public function sendCommand($commandObject, $user)
		{
			$message = json_encode($commandObject);
			$this->send($user, $message);
			echo 'Sending command to user :' . $user->id . " \r\n" . $message . " \r\n";	
		}	

		/**
		 * Get user settings
		 * 
		 * @param $commandObject
		 * @param $user (websocket user!)
		 */
		private function getUserSettings($commandObject, $user)
		{
			$from = "";
			$pin = "";
			$role = "user";
			$value = "";
			$extensionArray = null;			
			
			// Check if user exists in database
			$username = $commandObject->User;
			$userObject = $this->daoFacade->getUserDAO()->read($username);
			
			if ($userObject != null) 
			{
				$role = $userObject->getRole();
				$extensionArray = $this->daoFacade->getExtensionDAO()->getExtensionList($username);			
					
				// find primary extension
				foreach($extensionArray as $item)
				{
					if ($item->getPrimaryNumber() == 't' || $item->getPrimaryNumber() == true)
					{
						$from = $item->getExtensionNumber();
						$pin = $item->getPin();
						break;
					}
				}
			}
			
			var_dump($extensionArray);
			
			$commandObject->Command = "settingsList";
			//$commandObject->Value = $extensionArray;
			$commandObject->From = $from;
			$commandObject->Pin = $pin;
			$commandObject->Role = $role;
			
			// Send to client
			$this->sendCommand($commandObject, $user);				
		}
	}
	

	