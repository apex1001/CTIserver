<?php

	$start = microtime(true);
	while (true) {		
		
		if (microtime(true) - $start > 2) {
			echo "2 seconden\r\n";
			$start = microtime(true);
		}
		
	}
