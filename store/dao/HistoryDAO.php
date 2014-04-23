<?php

	/*
	 * The HistoryDAO for the Call Control Server
	 *
	 * @author V. Vogelesang
	 *
	 */	

	require_once(__DIR__.'/../domain/History.php');

	class HistoryDAO
	{
		private $connection;		
		
		public function __construct($connection)
		{
			$this->connection = $connection;
		}
		
		/**
		 * Get the history object
		 * 
		 * @param $username to search for
		 * @return $history object
		 * 
		 */
		public function read($username)
		{
			$obj = null;			
			$result = pg_query_params(
					$this->connection,
					'SELECT * FROM history WHERE username = $1',
					array($username));
			
			if ($result != null && $result != false && pg_num_rows($result) != 0) 
			{
				$obj = pg_fetch_object($result, 0, "History");
			}
			
			pg_free_result($result);
			return $obj;
		}
		
		/**
		 * Write the history object
		 *
		 * @param $history
		 *
		 */
		public function write(History $history)
		{
			$dialled_party = $history->getDialledParty();
			$datetime = $history->getDatetime();
			$duration = $history->getDuration();
			$username = $history->getUsername();			
			
			$result = pg_query_params(
					$this->connection,
					'INSERT INTO history (dialled_party, datetime, duration, username) values ($1, $2, $3, $4)',
					array($dialled_party, $datetime, $duration, $username));

			if(!$result)
			{
				echo 'Error: object not saved';
				return null;
			}	
					
			pg_free_result($result);
			return $result;
		
		}
		
		/**
		 * Get a list of history objects
		 *
		 * @param $username
		 *
		 */
		public function getHistoryList($username)
		{
				
			$result = pg_query_params(
					$this->connection,
					'SELECT * FROM history WHERE username = $1',
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
				$resultArray[] = pg_fetch_object($result, $i, "History");
			}
					
			pg_free_result($result);			
			return $resultArray;
		}
	}
	
	
