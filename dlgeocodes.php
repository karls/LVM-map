<?php
	
	/*
	 * TODO: do some error checking when fwriting stuff to disk
	 * TODO: the converted coordinates are still a bit off, probably due
	 *       to rounding errors and such
	 * TODO: 
	 */
	
	
	define("MAAAMET_URL", "http://www.maaamet.ee/rr/gauss/");
	
	// est.xml contains data from city24
	$xml_file = "est.xml";
	$xml_data = file_get_contents($xml_file);
	
	preg_match_all("#\s*<OBJECTTYPE>([[:print:]üõöäÜÕÖÄ]+)</OBJECTTYPE>\s*#i", $xml_data, $tmp_objs);
	//print_r($tmp_objs);
	//$json_object_types = json_encode($tmp_objs[1]);
	//$fp = fopen('object_types_json', 'w');
	//fwrite($fp, $json_object_types);
	//fclose($fp);
	//unset($fp);
	
	$object_types = array();
	/**
	* XXX: preg_match_all seems to give the strings of wrong length in the first subarray and the
	* strings of correct length (subpatterns) in the second subarray. this is a bit daft, and perhaps
	* my understanding of how php regexes work is flawed, but i don't have the time now and it seems
	* to work fine.
	* TODO: investigate preg_match_all and the regex
	*/
	foreach ($tmp_objs[1] as $key => $val)
		$object_types[trim($val)] = $key;
	unset($tmp_objs);
	
	$xml = new SimpleXMLElement($xml_data, LIBXML_NOCDATA);
	$count = 0;
	$rowcount = 0;
	$properties = array();
	$coordinates = array();
	// iterate over each rowset -- rowsets are types of different property
	foreach ($xml->ROWSET as $rowset)
	{
		// iterate over each row in each rowset -- a row corresponds to a property
		$obj_type = trim($rowset->OBJECTTYPE);
		foreach ($rowset->ROW as $row)
		{
			// let's build a string to represent the address of a property
			$x = $row->COORDINATE_X;
			$y = $row->COORDINATE_Y;
			// the $y . "5" is essentially a hack, since the data from
			// city24 is weird -- the Y coordinate comes as a number with one digit
			// less than needed (the last digit - 10m precision). weird stuff anyways.
			
			if ($x != '' && $y != '')
			{
				$coords = $row->ID." ".$y."5 ".$x." 0\r\n";
				// we only want data about objects that have coordinates for.
				// also TODO: this data is incomplete
				array_push($properties, array(
					"$row->ID",
					array(
						"city"        => "$row->LINN",
						"street"      => "$row->TANAV",
						"house_no"    => "$row->MAJANR",
						"object_type" => $object_types[$obj_type],
						"num_rooms"   => intval(empty($row->KIRJELDUS_TOAD) ? "-1" : "$row->KIRJELDUS_TOAD"),
						"additional_info" => "$row->LISAINFO_INFO"
					),
					array()
				));
				// push base-coordinates into $coordinates as well, because we'll use
				// this to get lat-lon from Maaamet
				array_push($coordinates, (string)$coords);
			}
		}// foreach
	}// foreach
	
	// this is just so that we dump a string -- walk over every coordinate tuple
	// and append them to a string
	$coords_out = '';
	foreach ($coordinates as $c)
	{
		$coords_out .= $c;
	}
	// dump the coordinates into a file for later use in getting the lat and
	//longitude from maaamet
	$fp = fopen('coords_dump', 'w');
	fwrite($fp, (string)$coords_out);
	fclose($fp);
	unset($fp);
	
	// the data needed by maaamet, pretty self explanatory
	$postdata = array(
		"fail[]" => "@".dirname(__FILE__) . "/coords_dump",
		"MAX_FILE_SIZE" => "15000000",
		"in_par" => "l_estkat.par",
		"out_par" => "eurefkat.par",
		"x" => "",
		"y" => "",
		"z" => "",
		"opentext" => ""
	);
	// set up curl to upload the file for conversion
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, MAAAMET_URL);
	curl_setopt($ch, CURLOPT_HEADER, FALSE);
	curl_setopt($ch, CURLOPT_VERBOSE, FALSE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_REFERER, MAAAMET_URL);
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (compatible;)");
	curl_setopt($ch, CURLOPT_POST, TRUE);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
	$page = curl_exec($ch);
	curl_close($ch);
	
	// get the filename containing the converted coordinates
	preg_match("/files\/[a-zA-Z0-9]+\.txt/", $page, $matches);
	$latlong_file = $matches[0];
	
	// now get the file from maaamet
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, MAAAMET_URL . $latlong_file);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, FALSE);
	$latlong_coords = curl_exec($ch);
	curl_close($ch);
	// write it to disk
	$fp = fopen('latlong_coords', 'w');
	fwrite($fp, $latlong_coords);
	fclose($fp);
	unset($fp);
	
	$latlong_coords_array = array();
	$i = 0;
	foreach(preg_split("/\r?\n/", $latlong_coords) as $line)
	{
		if ($line == "") break;
		list($id, $lat, $long, $h) = sscanf($line, "%d %f %f %f");
		/* DEBUGGING
		$lat = 58.222857;
		$long = 24.310573;*/
		//               DD       MM     SS
		$pattern = "/(\d{2})\.(\d{2})(\d{2})/";
		preg_match_all($pattern, $lat, $lat_matches, PREG_SET_ORDER);
		preg_match_all($pattern, $long, $lon_matches, PREG_SET_ORDER);
		$p = 5; //precision
		$lat_d = $lat_matches[0][1];
		$lat_m = $lat_matches[0][2];
		$lat_s = $lat_matches[0][3];
		$lon_d = $lon_matches[0][1];
		$lon_m = $lon_matches[0][2];
		$lon_s = $lon_matches[0][3];
		$converted_lat = bcadd($lat_d, bcdiv($lat_m * 60 + $lat_s, 3600.0, $p), $p);
		$converted_lon = bcadd($lon_d, bcdiv($lon_m * 60 + $lon_s, 3600.0, $p), $p);
		//echo "lat: $converted_lat, long: $converted_long\n";
		array_push($properties[$i][2], $converted_lat, $converted_lon);
		           //preg_replace($pattern, $replacement, $lat),
		           //preg_replace($pattern, $replacement, $long));
		$i++;
	}
	
	$final_data = array(
		array(),
		array(),
		array(),
		array(),
		array(),
		array(),
		array(),
		array(),
		array(),
		array(),
	);
	
	foreach($properties as $object)
	{
		// the object is an apartment
		if ($object[1]["object_type"] == 0)
		{
			if ($object[1]["num_rooms"] < 4)
				array_push($final_data[$object[1]["num_rooms"] - 1], $object);
			else
				array_push($final_data[3], $object);
		}
		else
			array_push($final_data[$object[1]["object_type"] + 3], $object);
	}
	
	
	
	//$final_data = json_encode($properties);
	$final_data = json_encode($final_data);
	$fp = fopen('final_obj_data_json', 'w');
	fwrite($fp, $final_data);
	fclose($fp);
	unset($fp);
	
	
?>
