<?php

	$host = "localhost";
	$username = "root";
	$password = "root";
	$db = "ymca";
	
	if (!$conn = mysql_connect($host, $username, $password))
		echo "Problem connecting to database: " . mysql_error();
	$db = mysql_select_db($db, $conn);

	$fh = fopen("zips.txt", "r");

	while ($line = fgetcsv($fh))
	{
		$sql = "insert into zips (zip, state_iso_code, city) values
					('" . $line[1] . "', '" . $line[2] . "', '" . $line[3] . "')";
		mysql_query($sql);
	}
?>