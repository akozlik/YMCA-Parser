<?php
	$host = "localhost";
	$username = "root";
	$password = "root";
	$db = "ymca";
	
	if (!$conn = mysql_connect($host, $username, $password))
	echo "Problem connecting to database: " . mysql_error();
	$db = mysql_select_db($db, $conn);
	
	$sql = "select zip from zips where loaded = 0";
	$load_result = mysql_query($sql);

	while ($row = mysql_fetch_assoc($load_result))
	{
		$zip = sprintf("%05d", $row['zip']);
		$url = "http://www.ymca.net/find-your-y/?address=$zip&x=0&y=0";
		
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
		$result = curl_exec($ch);
		$last_pos = 0;

		$needle = "title:\"";
		
		while($pos = strpos($result, $needle, $last_pos))
		{
			// Get the Location Name
			$location_name = substr($result, $pos + strlen($needle), strpos($result, "\n", $pos) - $pos);
			$location_name = str_replace("\" ,", "", $location_name);
			
			$location_name = trim($location_name);
			$last_pos = $pos+1;
			$location_name = mysql_real_escape_string($location_name);
			
			// Get the website data
			// Links to YMCA.com website
			$website_needle = "href=\"";
			$website_pos = strpos($result, $website_needle, $last_pos);
$website = "http://www.ymca.net/" . substr($result, $website_pos + strlen($website_needle), strpos($result, "\">", $website_pos) - ($website_pos + 6));
			
			// Get Content
						// Get the Location Name
			$content_needle = "content:";
			$content_pos = strpos($result, $content_needle, $website_pos);
			$content = substr($result, $content_pos + strlen($content_needle), strpos($result, "\n", $content_pos) - $content_pos);
			$last_pos = $last_pos+1;
			
			$br_needle = "<br>";
			$br_pos = strpos($result, $br_needle, $content_pos);
			$address = substr($result, $br_pos + strlen($br_needle), strpos($result, "<br>", ($br_pos + 1)) - $br_pos);
			$address = str_replace("<br>", "", $address);
			
			$br_pos++;
			$br_pos = strpos($result, $br_needle, $br_pos);
			$temp = substr($result, $br_pos + strlen($br_needle), strpos($result, "<br>", ($br_pos + 1)) - $br_pos);
			
			$city = substr($temp, 0, strpos($temp, ","));
			
			$state = substr($temp, strpos($temp, ",") + 1, 3);
			$state = trim($state);
			
			$zip_code = substr($temp, strpos($temp, "&nbsp;") + 6, 5);
			
			$phone_needle = "Telephone: </b>";
			$phone_pos = strpos($result, $phone_needle, $br_pos);
			$phone = substr($result, $phone_pos + strlen($phone_needle), 12);
			
			$sql = "insert into ymcas (location_name, address, city, state, zip, phone, website)
					values ('$location_name', '$address', '$city', '$state', '$zip_code', '$phone', '$website')";
					
					
			if (!strpos($location_name, "}"))
			{
				$ymca_result = mysql_query($sql);
				if (!$ymca_result)
				{
					echo "<p>Saving $location_name for loaded zip $zip.</p>";
					echo "<p>" . mysql_error() . "</p>";
				}
			}
			
		}

		$zip_sql = "update zips set loaded = 1 where zip = '$zip'";
		$zip_result = mysql_query($zip_sql);
	}

?>