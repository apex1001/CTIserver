<?php



//A Global Storage that will be shared between threads.

class GlobalUserList extends Stackable { public function run() { } };
class Arr extends Stackable { public function run() { } };

//Store {count} in Global Storage





/*
 * For Each Site, spawn a new thread, process the site and store result in Global Storage.
*/

$test = new Test();


class Test
{
	private $storage;
	private $socket;
	
	public function __construct()
	{
		$this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)  or die("Failed: socket_create()");
		socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1) or die("Failed: socket_option()");
		socket_bind($this->socket, "localhost", "7777")                      or die("Failed: socket_bind()");
		socket_listen($this->socket,20)                               or die("Failed: socket_listen()");
		
		
		
		$this->storage = new GlobalUserList();
		$this->storage[] = $this->socket;
		
		var_dump ($this->storage);
		$Thread = new SiteProcess($this->storage);
		$Thread->start();
		$this->proces();
	}
	
	public function proces()
	{
		sleep(5);
		$arr = new Arr();
		$arr[] = $this->socket;
		var_dump($arr);
	}
}

//A thread class
class SiteProcess extends Thread
{
	private $storage;

	public function __construct(GlobalUserList $storage)
	{
		$this->storage = $storage;
	}

	public function run()
	{
		var_dump($this->storage);
	}
}




