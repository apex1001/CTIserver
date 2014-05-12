<?php

	/*
	 * The ExtensionDAO for the Call Control Server
	 *
	 * @author V. Vogelesang
	 *
	 */	

	require_once(__DIR__.'/../domain/Extension.php');

	class ExtensionDAO
	{
		private $connection;		
		
		public function __construct($connection)
		{
			$this->connection = $connection;
		}
		
		/**
		 * Get the extension object
		 * 
		 * @param $extension
		 * @return $extension object
		 * 
		 */
		public function read($username)
		{
			$obj = null;			
			$result = pg_query_params(
					$this->connection,
					'SELECT * FROM Extensions WHERE username = $1',
					array($extension));
			
			if ($result != null && $result != false && pg_num_rows($result) != 0) 
			{
				$obj = pg_fetch_object($result, 0, "Extension");
			}
			
			pg_free_result($result);
			return $obj;
		}
		
		/**
		 * Write the extension object
		 *
		 * @param $extension
		 *
		 */
		public function write(Extension $extension)
		{
			$extension_number = $extension->getExtensionNumber();
			$primary_number = $extension->getPrimaryNumber();
			$username = $extension->getUsername();
			$pin = $extension->getPin();
			$userEdit = $extension->getUserEdit();
			
			$result = pg_query_params(
					$this->connection,
					'INSERT INTO extensions (extension_number, primary_number, username, pin, useredit) values ($1, $2, $3, $4, $5)',
					array($extension_number, $primary_number, $username, $pin, $userEdit));

			if(!$result)
			{
				echo 'Error: object not saved';
				return null;
			}	
					
			pg_free_result($result);
			return $result;
		
		}
		
		/**
		 * Update the extension object
		 *
		 * @param $extension
		 *
		 */
		public function update(Extension $extension)
		{
			$extension_number = $extension->getExtensionNumber();
			$primary_number = $extension->getPrimaryNumber();
			$username = $extension->getUsername();
		
			$result = pg_query_params(
					$this->connection,
					'UPDATE extensions SET primary_number = $1 WHERE extension_number = $2 AND username = $3',
					array($primary_number, $extension_number, $username));
		
			if(!$result)
			{
				echo 'Error: object not updated';
				return null;
			}
			
			pg_free_result($result);
			return $result;
		}
					
		/**
		 * Delete the extension object
		 *
		 * @param $extension
		 *
		 */
		public function delete(Extension $extension)
		{
			$extension_number = $extension->getExtensionNumber();			
			$username = $extension->getUsername();	
		
			$result = pg_query_params(
					$this->connection,
					'DELETE FROM extensions WHERE username = $1 AND extension_number = $2',
					array($username, $extension_number));
		
			if(!$result)
			{
				echo 'Error: object not deleted';
				return null;
			}
			
			pg_free_result($result);
			return $result;
		}
		
		/**
		 * Delete all the extensions of a user
		 *
		 * @param $username
		 *
		 */
		public function deleteAll($username)
		{
			
			$result = pg_query_params(
					$this->connection,
					'DELETE FROM extensions WHERE username = $1',
					array($username));
		
			if(!$result)
			{
				echo 'Error: objects not deleted';
				return null;
			}
				
			pg_free_result($result);
			return $result;
		}
		
		/**
		 * Get a list of extension objects
		 *
		 * @param $username
		 *
		 */
		public function getExtensionList($username)
		{
					
			$result = pg_query_params(
					$this->connection,
					'SELECT * FROM extensions WHERE username = $1',
					array($username));
			
			if(!$result)
			{
				echo 'Error: objects not found';
				return null;
			}
			
			$resultArray = array();
			$rows =  pg_num_rows($result);
			
			for ($i = 0; $i < $rows; $i++)
			{
				$resultArray[] = pg_fetch_row($result, $i);
			}
			
			pg_free_result($result);			
			return $resultArray;			
		}
	}
	
	
