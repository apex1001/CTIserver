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
			$username = $history->getUsername();			
			
			$result = pg_query_params(
					$this->connection,
					'INSERT INTO history (dialled_party, date_from, date_to, username) values ($1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, $2)',
					array($dialled_party, $username));

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
		public function update(History $history, $connection)
		{
			$dialled_party = $history->getDialledParty();
			$username = $history->getUsername();	
								
			$result = pg_query_params(
					$connection,
					'UPDATE history SET date_to = CURRENT_TIMESTAMP WHERE id = ' .
						'(SELECT id from history where dialled_party = $1 AND ' .
							'username = $2 AND date_to = date_from ' .
							'ORDER BY id DESC limit 1)', array($dialled_party, $username));
		
			if(!$result)
			{
				echo 'Error: object not updated';
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
		public function getHistoryList($username, $limit = 100)
		{
				
			$result = pg_query_params(
					$this->connection,
					'SELECT dialled_party, date_from, date_to FROM history WHERE username = $1 ' .
					'ORDER BY date_from desc LIMIT $2',
					array($username, $limit));
				
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
	
	
