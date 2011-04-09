<?php
	header('Content-type: text/html; charset=utf-8');
	
	//$fp = fopen('latlong_coords_json', 'r');
	//$data = fread($fp, filesize('latlong_coords_json'));
	$data = file_get_contents('final_obj_data_json');
	$object_types = file_get_contents('object_types_json');
	//$xml_file = "est.xml";
	//$xml_data = file_get_contents($xml_file);
	//$xml = new SimpleXMLElement($xml_data);
	//$count = 0;
	//$rowcount = 0;
	//$ps = array();
	//// iterate over each rowset -- rowsets are types of different property
	//foreach ($xml->ROWSET as $rowset)
	//{
	//	// iterate over each row in each rowset -- a row corresponds to a property
	//	$obj_type = $rowset->OBJECTTYPE;
	//	foreach ($rowset->ROW as $row)
	//	{
	//		// let's build a string to represent the address of a property
	//		$property_address = $row->TANAV ." ". $row->MAJANR ." ". $row->LINN;
	//		//$properties[(int)$row->ID] = array((string)$obj_type => $property_address);
	//		//$ps[(int)$row->ID] = (string)$property_address;
	//		array_push($ps, (string)$property_address);
	//	}
	//}
	//
	//for ($i = 0; $i < 10; $i++)
	//{
	//	$data[$i] = json_decode(file_get_contents
	//	("http://maps.googleapis.com/maps/api/geocode/json?address=".urlencode($ps[$i])."&sensor=false"));
	//	usleep(100000);
	//}
	////$loc = "Karusselli 93 Pärnu";
	////$addr = "http://maps.googleapis.com/maps/api/geocode/json?address=".urlencode($loc)."&sensor=false";
	////$data = file_get_contents($addr);
	//$data = json_encode($data);
	////$flattened_data = "{";
	////foreach ($data as $geolocation)
	////	$flattened_data .= $geolocation . ", ";
	////$flattened_data .= "}";
	////var_dump($flattened_data);
?>
<html>
<head>
<title>LVM map</title>
<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
<style type="text/css">
  html { height: 100% }
  body { height: 100%; margin: 0px; padding: 0px }
  #map_canvas { height: 100% }
</style>
<script type="text/javascript"
				src="http://maps.google.com/maps/api/js?sensor=false">
</script>
<script type="text/javascript">
  function initialize() {
		var geocode_results = <?php echo $data; ?>;
		var object_types = <?php echo $object_types; ?>;
		var cntr = new google.maps.LatLng(
			58.386131,
			24.498632
		);
			
		var myOptions = {
			zoom: 11,
			center: cntr,
			mapTypeId: google.maps.MapTypeId.HYBRID
		};
		
		var map = new google.maps.Map(document.getElementById("map_canvas"),
			myOptions
		);
		var i;
		var markers = new Array();
		var latlng;
		var marker;
		var infowindow = null;
		
		infowindow = null;
		
		infowindow = new google.maps.InfoWindow({
			content: ""
		});
		
		for (i = 0; i < geocode_results.length; i++)
		{
			if (parseInt(geocode_results[i][1].object_type) == 1)
			{
				marker = null;
				latlng = null;
				latlng = new google.maps.LatLng(
					geocode_results[i][2][0],
					geocode_results[i][2][1]
				);
				
				markers[i] = new google.maps.Marker({
					position: latlng,
					title: geocode_results[i][0],
					map: map,
					obj_info: 
					"Link: <a href=\"http://city24.ee/kinnisvara/korter/"+geocode_results[i][0]+"\">korter</a>\n"+geocode_results[i][1].house_no+" "+geocode_results[i][1].street+" "+geocode_results[i][1].city+"<br>"
					+ object_types[parseInt(geocode_results[i][1].object_type)]
				});
			
				marker = markers[i];
				google.maps.event.addListener(marker, 'click', function() {
					infowindow.setContent(this.obj_info);
					infowindow.open(map, this);
				});
			}
		}// for
	}// initialize

</script>
</head>

<body onload="initialize()">
  <div id="map_canvas" style="width:50%; height:50%"></div>
</body>
</html>
