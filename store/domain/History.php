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
		private $datetime;
		private $duration;
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
		
		public function getDatetime()
		{
			return $this->datetime;
		}
		
		public function setDatetime($datetime)
		{
			$this->datetime = $datetime;
		}
		
		public function getDuration()
		{
			return $this->duration;
		}
	
		public function setDuration($duration)
		{
			$this->duration = $duration;
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