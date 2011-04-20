<?php
	/**
	 * Generates a file named final_obj_data.json which contains all the
	 * information needed for the client-side of the map.
	 *
	 * @author Karl Sutt karl@sutt.ee
	 * @copyright Karl Sutt 2011
	 * @version 0.1
	 * @todo: do some error checking when fwriting stuff to disk
	 */
	
	// get the command line option for the language to get
	$lang = $_SERVER["argv"][1];
	
	
	define("MAAAMET_URL", "http://www.maaamet.ee/rr/gauss/");
	define("COORDS_DUMP_FILE", "coords_dump.$lang.txt");
	define("COORDS_LAT_LON", "latlon_coords.$lang.txt");
	define("DATA_FILE", "final_data.$lang.json");
	define("DEBUG", 1);
	
	
	// est.xml contains data from city24
	$xml_file = "$lang.xml";
	$xml_data = file_get_contents($xml_file);
	
	
	$regexps_based_on_lang = array(
		"est" => "#\s*<OBJECTTYPE>([[:print:]üõöäÜÕÖÄ]+)</OBJECTTYPE>\s*#i",
		"eng" => "#\s*<OBJECTTYPE>([[:print:]]+)</OBJECTTYPE>\s*#i",
		"fin" => "#\s*<OBJECTTYPE>([[:print:]üõöäÜÕÖÄ]+)</OBJECTTYPE>\s*#i",
		"rus" => "#\s*<OBJECTTYPE>([АаБбВвЕеЁёЖжЗзКкЛлМмНнОоПпРрДдЙйГгСсУуФфХхЦцЧчШшЩщЪъЫыЬьЭэЮюЯяИиТт\-<>;&ltgbr ]+)</OBJECTTYPE>\s*#i"
	);
	
	$transactions = array(
		"est" => "Müük",
		"eng" => "Sale",
		"fin" => "Myynti",
		"rus" => "Продажа"
	);
	
	
	dbg("Evaluating regexp\n");
	preg_match_all($regexps_based_on_lang[$lang], $xml_data, $tmp_objs);
	dbg("Found " . count($tmp_objs[1]) . " matches\n");
	dbg("---\n");
	
	
	/**
	* XXX: preg_match_all seems to give the strings of wrong length in the first
	*      subarray and the strings of correct length (subpatterns) in the second
	*      subarray. this is a bit daft, and perhaps my understanding of how php
	*      regexes work is flawed, but i don't have the time now and it seems to
	*      work fine.
	* TODO: investigate preg_match_all and the regex
	*/
	$object_types = array();
	foreach ($tmp_objs[1] as $key => $val)
	{
		if ($lang == "eng" && trim($val) == "House&lt;br&gt;share")
			$val = "Houseshare";
		if ($lang == "rus" && trim($val) == "Коммер-&lt;br&gt;ческий")
			$val = "Коммерческий";
		if ($lang == "rus" && trim($val) == "Часть&lt;br&gt;дома")
			$val = "Частьдома";
		if ($lang == "rus" && trim($val) == "Участок&lt;br&gt;земли")
			$val = "Участокземли";
		$object_types[trim($val)] = $key;
	}
	unset($tmp_objs);
	
	// Start building the $properties array
	dbg("Starting to build the object data array\n");
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
		/************************************************************************
		*    WARNING ! ! !
		*    This is a massive hack.
		*
		*    Because City24 has done something horrific to its xml export thing, the
		*    OBJECTTYPE of "House share" (specified in the technical document
		*    "City24 data exchange") comes back as "House<br>share", this awful hack
		*    is needed..
		*    Seriously, City24..?
		*************************************************************************/
		if ($lang == "eng" && $obj_type == "House<br>share")
			$obj_type = "Houseshare";
		
		if ($lang == "rus" && $obj_type == "Коммер-<br>ческий")
			$obj_type = "Коммерческий";
		if ($lang == "rus" && $obj_type == "Часть<br>дома")
			$obj_type = "Частьдома";
		if ($lang == "rus" && $obj_type == "Участок<br>земли")
			$obj_type = "Участокземли";
		
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
						"price"       => intval($row->HIND),
						"object_type" => $object_types["$obj_type"],
						"num_rooms"   => intval(empty($row->KIRJELDUS_TOAD)
													? "-1"
													: "$row->KIRJELDUS_TOAD"),
						"transaction_type" => $row->TEHING == $transactions[$lang] ? 0 : 1,
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
	dbg("Done building the array\n");
	dbg("---\n");
	
	// this is just so that we dump a string -- walk over every coordinate tuple
	// and append them to a string
	$coords_out = '';
	foreach ($coordinates as $c)
	{
		$coords_out .= $c;
	}
	// dump the coordinates into a file for later use in getting the lat and
	// longfrom maaamet
	dbg("Dumping coordinates from the xml file\n");
	$fp = fopen(COORDS_DUMP_FILE, 'w');
	fwrite($fp, (string)$coords_out);
	fclose($fp);
	unset($fp);
	dbg("Dumping done\n");
	dbg("---\n");
	
	
	dbg("Pulling data from Maaamet\n");
	// the data needed by maaamet, pretty self explanatory
	$postdata = array(
		"fail[]" => "@".dirname(__FILE__) . "/" . COORDS_DUMP_FILE,
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
	dbg("Pulling finished\n");
	dbg("---\n");
	
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
	dbg("Dumping converted coordinates\n");
	$fp = fopen(COORDS_LAT_LON, 'w');
	fwrite($fp, $latlong_coords);
	fclose($fp);
	unset($fp);
	
	dbg("Parsing out the converted coordinates\n");
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
		array_push($properties[$i][2], $converted_lat, $converted_lon);
		$i++;
	}
	dbg("Parsing done\n");
	dbg("---\n");
	
	$final_data = array(
		array(
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
		),
		array(
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
		)
	);
	
	
	dbg("Converting initial object data into client-side data-structure\n");
	foreach($properties as $object)
	{
		// the object is an apartment
		if ($object[1]["object_type"] == 0)
		{
			// if less than 4 rooms, can do # of rooms - 1 as the index
			if ($object[1]["num_rooms"] < 4)
				array_push($final_data[$object[1]["transaction_type"]]
				                      [$object[1]["num_rooms"] - 1], $object);
			// 4 or more rooms, the index is 3
			else
				array_push($final_data[$object[1]["transaction_type"]]
				                      [3], $object);
		}
		// the object is not an apartment, use object type ID + 3
		// it's +3, because there are 4 different types of apartment
		else
			array_push($final_data[$object[1]["transaction_type"]]
			                      [$object[1]["object_type"] + 3], $object);
	}
	dbg("Conversion done\n");
	dbg("---\n");
	
	dbg("Dumping final data\n");
	$final_data = json_encode($final_data);
	$fp = fopen(DATA_FILE, 'w');
	if (fwrite($fp, $final_data))
		dbg("Success\n");
	fclose($fp);
	unset($fp);
	dbg("Dumping done\n");
	
	function dbg($str)
	{
		if (DEBUG)
			echo($str);
	}
	
	
?>
