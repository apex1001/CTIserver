<?php 
	
	$userArray = array();
	$userArray[3] = microtime(true);
	while (true) 
	{
		if ((microtime(true) - $userArray[3]) > 2)
		{
			echo "2 sec verlopen";
			$userArray[3] = microtime(true);
		}
			
	}

//     try
//     {
//     	require_once('./library/PHPSip/PhpSIP.class.php');
	
//     	$from = "sip:220@192.168.1.200";
//     	$to = "sip:210@192.168.1.200";
//     	$target = "sip:200@192.168.1.200";

// 	    $api = new PhpSIP();
	    
// 	    // if you get "Failed to obtain IP address to bind. Please set bind address manualy."
// 	    // error, use the line below instead
// 	    // $api = new PhpSIP('you_server_IP_address');
// 		$api->setDebug(true);
	    
// 	    // if your SIP service doesn't accept anonymous inbound calls uncomment two lines below
// 	    $api->setUsername('220');
// 	    $api->setPassword('1234');
	
// 	    $api->addHeader('Subject: click2call');
// 	    $api->setMethod('INVITE');
// 	    $api->setFrom('sip:c2c@'.$api->getSrcIp());
// 	    $api->setUri($from);
// 	    $res = $api->send();

// 	    sleep(10);		
	    
//  	    if ($res == 200)
//  	    { 
//  	      	echo $api->getCallId();  
	        
// 	        $api->setUri($from);
// 	        $api->setMethod('REFER');
// 	        $api->addHeader('Refer-to: '.$to);
// 	        $api->addHeader('Referred-By: sip:c2c@'.$api->getSrcIp());
// 	        $api->send();
	        
// 			echo 'Sending BYE!!';
// 	        $api->setMethod('BYE');
// 	        $api->send();
	
// // 	        $api->listen('NOTIFY');
// // 	        $api->reply(481,'Call Leg/Transaction Does Not Exist');
// //       	}

// //       	if ($res == 'No final response in 5 seconds.')
// //       	{
// //         	$api->setMethod('CANCEL');
// //         	$res = $api->send();
//       	}

// //       	echo $res;

//     } 
//     catch (Exception $e) 
//     {
// 	   	echo "Opps... Caught exception:";
// 	  	echo $e;
//     }

?>