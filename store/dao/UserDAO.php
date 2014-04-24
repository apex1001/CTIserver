<?php

	/*
	 * The UserDAO for the Call Control Server
	 *
	 * @author V. Vogelesang
	 *
	 */	

	require_once(__DIR__.'/../domain/User.php');

	class UserDAO
	{
		private $connection;		
		
		public function __construct($connection)
		{
			$this->connection = $connection;
		}
		
		/**
		 * Get the user object
		 * 
		 * @param $username
		 * @return $user object
		 * 
		 */
		public function read($username)
		{
			echo $username;
			$obj = null;			
			$result = pg_query_params(
					$this->connection,
					'SELECT * FROM users WHERE username = $1',
					array($username));
			
			if ($result != null && $result != false && pg_num_rows($result) == 1) 
			{
				$obj = pg_fetch_object($result, 0, "User");
			}
			
			pg_free_result($result);
			return $obj;
		}
		
		/**
		 * Write the user object
		 *
		 * @param $user
		 *
		 */
		public function write(User $user)
		{
			$username = $user->getUsername();
			$role = $user->getRole();
			
			$result = pg_query_params(
					$this->connection,
					'INSERT INTO users values ($1, $2)',
					array($username, $role));

			if(!$result)
			{
				echo 'Error: object not saved';
				return null;
			}			
			
			pg_free_result($result);
			return $result;
		
		}
		
		/**
		 * Update the user object
		 *
		 * @param $user
		 *
		 */
		public function update(User $user)
		{
			$username = $user->getUsername();
			$role = $user->getRole();
				
			$result = pg_query_params(
					$this->connection,
					'UPDATE users SET role = $2 WHERE username = $1',
					array($username, $role));
		
			if(!$result)
			{
				echo 'Error: object not updated';
				return null;
			}
			
			pg_free_result($result);
			return $result;		
		}
		
		/**
		 * Delete the user object
		 *
		 * @param $user
		 *
		 */
		public function delete(User $user)
		{
			$username = $user->getUsername();			
		
			$result = pg_query_params(
					$this->connection,
					'DELETE FROM users WHERE username = $1',
					array($username));
		
			if(!$result)
			{
				echo 'Error: object not deleted';
				return null;
			}
			
			pg_free_result($result);
			return $result;
		}
	}
	
	