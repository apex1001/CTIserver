<?php

	/*
	 * Simple POPO class for CTIserver
	 *
	 * @author V. Vogelesang
	 *
	 */
	
	Class History
	{
		private $dialled_party;
		private $date_from;
		private $date_to;
		private $username;
	
		public function __construct()
		{
		}
	
		public function getDialledParty()
		{
			return $this->dialled_party;
		}
	
		public function setDialledParty($dialled_party)
		{
			$this->dialled_party = $dialled_party;
		}
		
		public function getDateFrom()
		{
			return $this->date_from;
		}
		
		public function setDateFrom($date_from)
		{
			$this->date_from = $date_from;
		}
		
		public function getDateTo()
		{
			return $this->date_to;
		}
		
		public function setDateTo($date_to)
		{
			$this->date_to = $date_to;
		}
		
		public function getUsername()
		{
			return $this->username;
		}
		
		public function setUsername($username)
		{
			$this->username = $username;
		}
	}