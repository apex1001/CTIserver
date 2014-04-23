<?php

	$dbh = pg_connect("host=192.168.1.200 dbname=ctidb user=ctiuser password=ctiftw01");	
	
	if (!$dbh) {
	    die("Error in connection: " . pg_last_error());
	}
	
	// execute query
	$sql = "SELECT * FROM Users";
	$result = pg_query($dbh, $sql);
	
	if (!$result) {
	    die("Error in SQL query: " . pg_last_error());
	}
	
	while ($row = pg_fetch_array($result)) {
	    echo "Name: " . $row[0] . "\r\n";
	    echo "Role: " . $row[1] . "\r\n";
	}
	
	pg_free_result($result);
	
	$result = pg_query_params($dbh, 'SELECT * FROM Users WHERE username like $1', array("%"));
	
	if (!$result) {
		die("Error in SQL query: " . pg_last_error());
	}
	
	echo 'Parameterized:' . "\r\n";
	
	while ($row = pg_fetch_array($result)) {
		echo "Name: " . $row[0] . "\r\n";
		echo "Role: " . $row[1] . "\r\n";
	}
	
	pg_close($dbh);


