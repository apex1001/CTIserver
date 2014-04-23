<?php

	/*
	 * Simple POPO class for CTIserver
	 *
	 * @author V. Vogelesang
	 *
	 */
	
	Class User
	{
		private $username;
		private $role;
	
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
	
		public function getRole()
		{
			return $this->role;
		}
	
		public function setRole($role)
		{
			$this->role = $role;
		}
	}