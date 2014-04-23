<?php

	/*
	 * The DAOFacade for the Call Control Server
	 * Creates all the DAO's for each database table.
	 * 
	 * @author V. Vogelesang
	 *
	 */	

	require_once('UserDAO.php');
	require_once('RoleDAO.php');
	require_once('ExtensionDAO.php');
	require_once('HistoryDAO.php');
	
	class DAOFacade
	{
		private $controller;
		private $userDAO;
		private $roleDAO;
		private $historyDAO;
		private $extensionDAO;
		private $connection;
		private $connectionString = "host=192.168.1.200 dbname=ctidb user=ctiuser password=ctiftw01";
		
		public function __construct($controller)
		{			
			$this->controller = $controller;
			$this->connection = $this->connect($this->connectionString);
			
			$this->userDAO = new UserDAO($this->connection);
			$this->roleDAO = new RoleDAO($this->connection);
			$this->historyDAO = new HistoryDAO($this->connection);
			$this->extensionDAO = new ExtensionDAO($this->connection);
		}		
		
		public function getUserDAO()
		{
			return $this->userDAO;
		}
		
		public function getRoleDAO()
		{
			return $this->roleDAO;
		}
		
		public function getExtensionDAO()
		{
			return $this->extensionDAO;
		}
		
		public function getHistoryDAO()
		{
			return $this->historyDAO;
		}
		
		/**
		 * Connect to the database
		 * 
		 * @param $connectString
		 * @return $connection resource
		 */
		private function connect($connectString)
		{
			$connection = null;
			
			try
			{					
				$connection = pg_connect($connectString);
				if (!$connection)
				{
					echo "Error in connection: " . pg_last_error() . "\r\n";
				}
			}
			catch (Exception $e)
			{
				echo "Error:" . $e->getMessage() . "\r\n";
			}
			
			return $connection;
		}			
	}