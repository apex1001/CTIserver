<?php

	/*
	 * Simple POPO class for CTIserver
	 *
	 * @author V. Vogelesang
	 *
	 */
	
	Class Extension
	{
		private $username;
		private $extension_number;
		private $primary_number;
		private $pin;
		private $userEdit;
	
		public function __construct()
		{
		}
	
		public function getUsername()
		{
			return $this->username;
		}
	
		public function setUsername($username)
		{
			$this->username = $username;
		}
	
		public function getExtensionNumber()
		{
			return $this->extension_number;
		}
	
		public function setExtensionNumber($extension_number)
		{
			$this->extension_number = $extension_number;
		}
		
		public function getPrimaryNumber()
		{
			return $this->primary_number;
		}
		
		public function setPrimaryNumber($primary_number)
		{
			$this->primary_number = $primary_number;
		}
		
		public function getPin()
		{
			return $this->pin;
		}
		
		public function setPin($pin)
		{
			$this->pin = $pin;
		}
		
		public function getUserEdit()
		{
			return $this->userEdit;
		}
		
		public function setUserEdit($userEdit)
		{
			$this->userEdit = $userEdit;
		}
	}