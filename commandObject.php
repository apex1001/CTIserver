<?php
	/*
	 * A POPO class for the Call COntrol Server
	 * 
	 * Contains the commandObject
	 */
	class CommandObject
	{
		private $command;
        private $from;
        private $to;
        private $target;
        private $pin;
        private $user;
        private $value;
        
        function __construct(
        		$command = "",
        		$from  = "", 
        		$to  = "", 
        		$target  = "", 
        		$pin  = "", 
        		$user  = "", 
        		$value  = "") 
        {
        	$this->command = $command;
	        $this->from = $from;
	        $this->to = $to;
	        $this->target = $target;
	        $this->pin = $pin;
	        $this->user = $user;
	        $this->value = $value;
        }
       
		public function getCommand()
		{
			return $this->command;
		}
		
		public function setCommand($command)
		{
			$this->command = command;
		}
		
		public function getFrom()
		{
			return $this->from;
		}
		
		public function setFrom($from)
		{
			$this->from = $from;
		}
		
		public function getTo()
		{
			return $this->to;
		}
		
		public function setTo($to)
		{
			$this->to = $to;
		}
		
		public function getTarget()
		{
			return $this->target;
		}
		
		public function setTarget($target)
		{
			$this->target = $target;
		}
		
		public function getPin()
		{
			return $this->pin;
		}
		
		public function setPin($pin)
		{
			$this->pin = $pin;
		}
		
		public function getUser()
		{
			return $this->user;
		}
		
		public function setUser($user)
		{
			$this->user = $user;
		}
		
		public function getValue()
		{
			return $this->value;
		}
		
		public function setValue($value)
		{
			$this->value = $value;
		}		
	}