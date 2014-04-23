<?php

	/*
	 * The RoleDAO for the Call Control Server
	 *
	 * @author V. Vogelesang
	 *
	 */	

	require_once(__DIR__.'/../domain/Role.php');

	class RoleDAO
	{
		private $connection;		
		
		public function __construct($connection)
		{
			$this->connection = $connection;
		}
		
		/**
		 * Get the role object
		 * 
		 * @param $role
		 * @return $role object
		 * 
		 */
		public function read($role)
		{
			$obj = null;			
			$result = pg_query_params(
					$this->connection,
					'SELECT * FROM Roles WHERE role = $1',
					array($role));
			
			if ($result != null && $result != false && pg_num_rows($result) != 0) 
			{
				$obj = pg_fetch_object($result, 0, "Role");
			}
			
			pg_free_result($result);
			return $obj;
		}
		
		/**
		 * Write the role object
		 *
		 * @param $role
		 *
		 */
		public function write(Role $role)
		{
			$role = $role->getRole();
			
			$result = pg_query_params(
					$this->connection,
					'INSERT INTO roles values ($1)',
					array($role));

			if(!$result)
			{
				echo 'Error: object not saved';
				return null;
			}		
				
			pg_free_result($result);
			return $result;
		
		}
					
		/**
		 * Delete the role object
		 *
		 * @param $role
		 *
		 */
		public function delete(Role $role)
		{
			$rolename = $role->getRole();			
		
			$result = pg_query_params(
					$this->connection,
					'DELETE FROM roles WHERE role = $1',
					array($role));
		
			if(!$result)
			{
				echo 'Error: object not deleted';
				return null;
			}
			
			pg_free_result($result);
			return $result;
		}
	}
	
	
