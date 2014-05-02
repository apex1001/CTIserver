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
	require_once('./library/websocket/websockets2.php');
	require_once('./store/domain/CommandObject.php');
	require_once('./store/domain/User.php');
	require_once('./store/domain/Extension.php');
	require_once('./store/dao/DAOFacade.php');

	/**
	 * The ServerController for the CTI Client 
	 *
	 */
	class ServerController extends WebSocketServer
	{		
		private $callController;
		private $daoFacade;
		private $settingsArray;
		private $userList;
		
		public function __construct($url, $port)
		{
			parent::__construct($url, $port);
			$this->readSettings();
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
			echo 'Received message ' . $message . ' from ' . $user->socket . "\r\n";
			if ($message !="ping")
			{
				$message = $this->cleanMsg($message);
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
	
						case "terminate":
							$this->callController->callTerminate($commandObject, $user);
							break;
	
						case "transfer":
							$this->callController->callTransfer($commandObject,  $user);
							break;
							
						case "getSettings":
							$this->getUserSettings($commandObject, $user);
							break;
							
						case "updateSettings":
							$this->putUserSettings($commandObject, $user);
							break;
							
						case "deleteSettings":
								$this->deleteUserSettings($commandObject, $user);
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
					if ($item[2] == 't')
					{
						$from = $item[1];
						$pin = $item[4];
						break;
					}
				}
			}
						
			$commandObject->Command = "settingsList";
			$commandObject->Value = $extensionArray;
			$commandObject->From = $from;
			$commandObject->Pin = $pin;
			$commandObject->Role = $role;
			
			// Send to client
			$this->sendCommand($commandObject, $user);				
		}
		
		/**
		 * Save or update user settings
		 *
		 * @param $commandObject
		 * @param $user (websocket user!)
		 */
		private function putUserSettings($commandObject, $user)
		{
			$extensionArray = $commandObject->Value;
				
			// Check if user exists in database
			$username = $commandObject->User;
			$userObject = $this->daoFacade->getUserDAO()->read($username);
			
			// Create user if it does not exist yet
			if ($userObject == null) 
			{
				$user = new User();
				$user->setUsername($username);
				$user->setRole("user");
				$this->daoFacade->getUserDAO()->write($user);				
			}

			// Iterate all the extensions, create new if not existing
			foreach ($extensionArray as $item)
			{
				$extension = $this->createExtension($item);
				if ($item[0] == null)
				{					
					$this->daoFacade->getExtensionDAO()->write($extension);
					continue;
				}
				$this->daoFacade->getExtensionDAO()->update($extension);				
			}
		}
		
		/**
		 * Delete user settings
		 *
		 * @param $commandObject
		 * @param $user (websocket user!)
		 */
		private function deleteUserSettings($commandObject, $user)
		{
			$extensionArray = $commandObject->Value;		
		
			// Iterate all the extensions, create new if not existing
			foreach ($extensionArray as $item)
			{
				$extension = $this->createExtension($item);
				$this->daoFacade->getExtensionDAO()->delete($extension);				
			}
		}
		
		/**
		 * Read the settings file and apply values
		 * 
		 */
		private function readSettings()
		{
			$this->settingsArray = parse_ini_file('./conf/settings.ini');
		}
		
		/**
		 * Return the current settings array
		 * 
		 * @return settingsArray
		 */
		public function getSettingsArray()
		{
			return $this->settingsArray;
		}
		
		/**
		 * Create an extension object from array item
		 * 
		 * @param $extensionItem in array format
		 * @return $extension object
		 */
		private function createExtension($extensionItem)
		{
			$extension = new Extension();	
			$extension->setExtensionNumber($extensionItem[1]);
			$extension->setPrimaryNumber($extensionItem[2]);
			$extension->setUsername($extensionItem[3]);
			$extension->setPin($extensionItem[4]);
			$extension->setUserEdit($extensionItem[5]);	
			return $extension;
		}
		
		/**
		 * Clean any unwanted characters from a message.
		 * 
		 * @param $message
		 * @return $cleanedMessage
		 */
		private function cleanMsg($message)
		{
			$regex = '/[\x01-\x1F\x7F-\xFF]/';
			$message = preg_replace($regex, '', $message);
			return substr($message,0,strrpos($message, "}")+1);
		}
	}
	

	