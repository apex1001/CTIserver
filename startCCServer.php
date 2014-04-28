<?php
	
	require_once('ServerController.php');
	
	// Run server forever until stopped from the console.
	while (true) 
	{
		// Init
		$options = getopt("h:");		
		$url = $options['h'];		
		$port = "7777";
	
		// Start server
		echo 'Starting Call Control Server on ' . $url . ' port ' . $port . "\r\n";		
		$serverController = new ServerController($url, $port);		
		
		try
		{
			$serverController->run();
		}
		catch (Exception $e)
		{
			echo $e->getMessage();
		}
	}
