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

	require_once('./library/websockets2.php');
	require_once('./commandObject.php');
	require_once('./CallController.php');
	require_once('./DAOFacade.php');
	
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
 			$this->daoFacade = new DAOFacade();
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
							// @todo: implement this
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
		}	
		
		/**
		 * Send commandObject back to the serverController
		 *
		 * @param $commandObject
		 *
		 */
		public function sendCommand($commandObject, $user)
		{
			//print_r($user);
			$message = json_encode($commandObject);
			$this->send($user, $message);
			echo ' Sending command to user :' . $user->id . " \r\n" . $message;	
		}		
	}
	

	