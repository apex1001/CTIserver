<?php

	/*
	 * Simple POPO class for CTIserver
	 *
	 * @author V. Vogelesang
	 *
	 */
	
	Class Role
	{
		private $role;
	
		public function __construct()
		{
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