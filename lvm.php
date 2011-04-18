<?php
	header('Content-type: text/html; charset=utf-8');
	
	//$fp = fopen('latlong_coords_json', 'r');
	//$data = fread($fp, filesize('latlong_coords_json'));
	$data = file_get_contents('final_obj_data_json');
	//$object_types = file_get_contents('object_types_json');
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
<script type="text/javascript" src="markerclusterer_packed.js"></script>
<script type="text/javascript">
		//var markers = new Array(
		//	new Array(),
		//	new Array(),
		//	new Array(),
		//	new Array(),
		//	new Array(),
		//	new Array(),
		//	new Array(),
		//	new Array(),
		//	new Array(),
		//	new Array()
		//);
		var markers = [
			[],
			[],
			[],
			[],
			[],
			[],
			[],
			[],
			[],
			[],
		];
		var markers_clustered = [];
		var infowindow = null;
  function initialize() {
		var geocode_results = <?php echo $data; ?>;
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
		var latlng;
		var marker;
		var markerImage;
		infowindow = new google.maps.InfoWindow({
			content: ""
		});
		for (i = 0; i < geocode_results.length; i++)
		{
			markerImage = new google.maps.MarkerImage(
				"markers/" + i + ".png",
				null,
				null,
				new google.maps.Point(0, 23)
			);
			for (j = 0; j < geocode_results[i].length; j++)
			{
				latlng = null;
				latlng = new google.maps.LatLng(
					geocode_results[i][j][2][0],
					geocode_results[i][j][2][1]
				);
				marker = new google.maps.Marker({
					position: latlng,
					title: geocode_results[i][j][0],
					map: map,
					obj_info: geocode_results[i][j][1].additional_info,
					icon: markerImage
				});
				
				markers[i].push(marker);
				markers_clustered.push(marker);
				google.maps.event.addListener(marker, 'click', function() {
					infowindow.setContent(this.obj_info);
					infowindow.open(map, this);
				});
			}// for
		}// for
		var mcOpts = {gridSize: 30, maxZoom: 13};
		new MarkerClusterer(map, markers_clustered, mcOpts);
	}// initialize

	function process(box)
	{
		if (box.checked)
			for (i = 0; i < markers[box.value].length; i++)
				markers[box.value][i].setVisible(true);
		else
			for (i = 0; i < markers[box.value].length; i++)
				markers[box.value][i].setVisible(false);
	}
	
	function setAll(property)
	{
		for (i = 0; i < property.length; i++)
			property[i].checked = true;
		
		for (i = 0; i < markers.length; i++)
		{
			for (j = 0; j < markers[i].length; j++)
				markers[i][j].setVisible(true);
		}
	}
	function clearAll(property)
	{
		for (i = 0; i < property.length; i++)
			property[i].checked = false;
		
		for (i = 0; i < markers.length; i++)
		{
			for (j = 0; j < markers[i].length; j++)
				markers[i][j].setVisible(false);
		}
	}
</script>
</head>

<body onload="initialize()">
  <div id="map_canvas" style="float: left; width:50%; height:75%"></div>
	<div id="menu" style="float: left;">
		<form name="properties" action="">
		
		<input type=checkbox name="property" value="0" onclick="process(this)" checked="checked"><img src="markers/0.png" />1-toalised<br>
		<input type=checkbox name="property" value="1" onclick="process(this)" checked="checked"><img src="markers/1.png" />2-toalised<br>
		<input type=checkbox name="property" value="2" onclick="process(this)" checked="checked"><img src="markers/2.png" />3-toalised<br>
		<input type=checkbox name="property" value="3" onclick="process(this)" checked="checked"><img src="markers/3.png" />4-ja-enamatoalised<br>
		<input type=checkbox name="property" value="4" onclick="process(this)" checked="checked"><img src="markers/4.png" />Äripinnad<br>
		<input type=checkbox name="property" value="5" onclick="process(this)" checked="checked"><img src="markers/5.png" />Garaažid<br>
		<input type=checkbox name="property" value="6" onclick="process(this)" checked="checked"><img src="markers/6.png" />Suvilad<br>
		<input type=checkbox name="property" value="7" onclick="process(this)" checked="checked"><img src="markers/7.png" />Majad<br>
		<input type=checkbox name="property" value="8" onclick="process(this)" checked="checked"><img src="markers/8.png" />Majaosad<br>
		<input type=checkbox name="property" value="9" onclick="process(this)" checked="checked"><img src="markers/9.png" />Maad<br>
		
		<input type=button name="set" onclick="setAll(document.properties.property)" value="Sea kõik"><br>
		<input type=button name="clear" onclick="clearAll(document.properties.property)" value="Puhasta kõik"><br>
		
		</form>
	</div>
</body>
</html>
