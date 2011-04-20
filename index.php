<?php
	header('Content-type: text/html; charset=utf-8');
	$data = file_get_contents('final_obj_data.json');
?>
<html>
<head>
<title>LVM map</title>
<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
<style type="text/css">
 html { height: 100%; font-size: 0.7em;}
 body { height: 100%; margin: 0px; padding: 0px;}
  #map_canvas { height: 100% }
</style>
<link rel="stylesheet" type="text/css" href="jquery-ui/css/smoothness/jquery-ui-1.8.11.custom.css" />
<script type="text/javascript" src="jquery-ui/js/jquery-1.5.1.min.js"></script>
<script type="text/javascript" src="jquery-ui/js/jquery-ui-1.8.11.custom.min.js"></script>
<script type="text/javascript"
				src="http://maps.google.com/maps/api/js?sensor=false">
</script>
<script type="text/javascript" src="markerclusterer_packed.js"></script>
<script type="text/javascript">
	
	// Current transaction type
	var transaction_type = 0; //sale
	
	// Map
	var map;
	// Infowindow object
	var infowindow = null;
	
	// MarkerClusterer variables
	var mc = null;
	var mcOpts = {gridSize: 50, maxZoom: 12, averageCenter: true, zoomOnClick: false};
	
	// ???
	var markers = [
		[
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
		],
		[
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
		]
	];
	
	// A flattened array of marker objects
	var markers_flattened = [[],[]];
	
	// The whole of object data
	var geocode_results;
	
	// Auxiliary arrays of sale and rent objects
	var sale_objects;
	var rent_objects;
	/**
	 * Create the map
	 */
  function initialize() {
		
		// Get the object data
		geocode_results = <?php echo $data; ?>;
		// Split it into sale and rent data
		sale_objects = geocode_results[0];
		rent_objects = geocode_results[1];
		
		// Create the options object for the map
		var myOptions = {
			zoom: 7,
			center: new google.maps.LatLng(58.745407, 25.290527),
			mapTypeId: google.maps.MapTypeId.HYBRID
		};
		
		// Create the map
		map = new google.maps.Map(
			document.getElementById("map_canvas"),
			myOptions
		);
		
		
		var i;
		var latlng;
		var marker;
		var markerImage;
		
		// Infowindow object
		infowindow = new google.maps.InfoWindow({
			content: ""
		});
		
		// Loop through the objects
		for (h = 0; h < geocode_results.length; h++)
		{
			for (i = 0; i < geocode_results[h].length; i++)
			{
				markerImage = new google.maps.MarkerImage(
					"markers/" + i + ".png",
					null,
					null,
					new google.maps.Point(0, 23)
				);
				
				
				for (j = 0; j < geocode_results[h][i].length; j++)
				{
					latlng = null;
					
					latlng = new google.maps.LatLng(
						geocode_results[h][i][j][2][0],
						geocode_results[h][i][j][2][1]
					);
					
					
					marker = new google.maps.Marker({
						position: latlng,
						title: geocode_results[h][i][j][0],
						map: map,
						obj_info: geocode_results[h][i][j][1].additional_info,
						icon: markerImage
					});
					
					
					markers[h][i].push(marker);
					markers_flattened[h].push(marker);
					
					
					google.maps.event.addListener(marker, 'click', function() {
						infowindow.setContent(this.obj_info);
						infowindow.open(map, this);
					});
				}// for
			}// for
		}// for
		
		// Create the MarkerClusterer
		mc = new MarkerClusterer(map, null, mcOpts);
		
		
		// Create a new calculator -- this one calculates the clusters based on the
		// visibility of the markers
		mc.setCalculator(function(markers, numStyles)
			{
				var index = 0;
				var count = 0;
				for (i = 0; i < markers.length; i++)
					if (markers[i].getVisible())
						count++;
				var dv = count;
				while (dv !== 0) {
					dv = parseInt(dv / 10, 10);
					index++;
				}

				index = Math.min(index, numStyles);
				return {
					text: count,
					index: index
				};
				
			});
	}// initialize

	//function process(box)
	//{
	//	var sum = 0;
	//	for (i = 0; i < box; i++)
	//		sum += geocode_results[i].lenght;
	//	for (i = sum; i < geocode_results[box.value].length; i++)
	//		sale_markers_flat[i].setVisible(box.checked);
	//	mc.resetViewport();
	//	mc.redraw();
	//}
	
	function process(box)
	{
		for (i = 0; i < markers[transaction_type][box.value].length; i++)
			markers[transaction_type][box.value][i].setVisible(box.checked);
		mc.resetViewport(true);
		mc.redraw();
	}
	
	function setAll(property)
	{
		for (i = 0; i < property.length; i++)
			property[i].checked = true;
		
		for (i = 0; i < markers[transaction_type].length; i++)
		{
			for (j = 0; j < markers[transaction_type][i].length; j++)
				markers[transaction_type][i][j].setVisible(true);
		}
		mc.resetViewport();
		mc.redraw();
	}
	
	function clearAll(property)
	{
		for (i = 0; i < property.length; i++)
			property[i].checked = false;
		
		for (i = 0; i < markers[transaction_type].length; i++)
		{
			for (j = 0; j < markers[transaction_type][i].length; j++)
				markers[transaction_type][i][j].setVisible(false);
		}
		mc.resetViewport();
		mc.redraw();
	}
	
	
	function switch_transaction(t_type)
	{
		if (t_type == 0)
			setAll(document.saleproperties.saleproperty);
		else
			setAll(document.rentproperties.rentproperty);
		
		transaction_type = t_type;
		mc.clearMarkers();
		for (i = 0; i < markers_flattened[Math.abs(t_type - 1)].length; i++)
			markers_flattened[Math.abs(t_type - 1)][i].setVisible(false);
		for (i = 0; i < markers_flattened[t_type].length; i++)
			markers_flattened[t_type][i].setVisible(true);
		mc.addMarkers(markers_flattened[t_type]);
		mc.resetViewport();
		mc.redraw();
	}
	
	$(function() {
		initialize();
		$("#tabs").tabs({
			show: function(event, ui)
			{
				switch_transaction(ui.index);
			}
		});
	});
	
</script>
</head>

<body>
	<div id="tabs" style="width: 75%;">
		<ul>
			<li><a href="#tabs-1">Müük</a></li>
			<li><a href="#tabs-2">Üür</a></li>
		</ul>
		<div id="tabs-1">
			<form id="sale-form" name="saleproperties" action="">
				<table id="objects-selection" style="font-size: 1em;">
					<tr>
						<td><input type=checkbox name="saleproperty" value="0" onclick="process(this)" checked="checked">
						<img src="markers/0.png" />1-toalised<br>
						</td>
						<td><input type=checkbox name="saleproperty" value="1" onclick="process(this)" checked="checked">
						<img src="markers/1.png" />2-toalised<br>
						</td>
						<td><input type=checkbox name="saleproperty" value="2" onclick="process(this)" checked="checked">
						<img src="markers/2.png" />3-toalised<br>
						</td>
						<td><input type=checkbox name="saleproperty" value="3" onclick="process(this)" checked="checked">
						<img src="markers/3.png" />4-ja-enamatoalised<br>
						</td>
						<td><input type=checkbox name="saleproperty" value="4" onclick="process(this)" checked="checked">
						<img src="markers/4.png" />Äripinnad<br>
						</td>
						<td><input type=button name="set" onclick="setAll(document.saleproperties.saleproperty)" value="Sea kõik"><br>
						</td>
						</tr>
						<tr>
						<td><input type=checkbox name="saleproperty" value="6" onclick="process(this)" checked="checked">
						<img src="markers/6.png" />Suvilad<br>
						</td>
						<td><input type=checkbox name="saleproperty" value="7" onclick="process(this)" checked="checked">
						<img src="markers/7.png" />Majad<br>
						</td>
						<td><input type=checkbox name="saleproperty" value="8" onclick="process(this)" checked="checked">
						<img src="markers/8.png" />Majaosad<br>
						</td>
						<td>
						<input type=checkbox name="saleproperty" value="9" onclick="process(this)" checked="checked">
						<img src="markers/9.png" />Maad<br>
						</td>
						<td><input type=checkbox name="saleproperty" value="5" onclick="process(this)" checked="checked">
						<img src="markers/5.png" />Garaažid<br>
						</td>
						<td>
						<input type=button name="clear" onclick="clearAll(document.saleproperties.saleproperty)" value="Puhasta kõik"><br>
						</td>
					</tr>
				</table>
			</form>
		</div>
		<div id="tabs-2">
			<form id="rent-form" name="rentproperties" action="">
				<table id="objects-selection" style="font-size: 1em;">
					<tr>
						<td><input type=checkbox name="rentproperty" value="0" onclick="process(this)" checked="checked">
						<img src="markers/0.png" />1-toalised<br>
						</td>
						<td><input type=checkbox name="rentproperty" value="1" onclick="process(this)" checked="checked">
						<img src="markers/1.png" />2-toalised<br>
						</td>
						<td><input type=checkbox name="rentproperty" value="2" onclick="process(this)" checked="checked">
						<img src="markers/2.png" />3-toalised<br>
						</td>
						<td><input type=checkbox name="rentproperty" value="3" onclick="process(this)" checked="checked">
						<img src="markers/3.png" />4-ja-enamatoalised<br>
						</td>
						<td><input type=checkbox name="rentproperty" value="4" onclick="process(this)" checked="checked">
						<img src="markers/4.png" />Äripinnad<br>
						</td>
						<td><input type=button name="set" onclick="setAll(document.rentproperties.rentproperty)" value="Sea kõik"><br>
						</td>
						</tr>
						<tr>
						<td><input type=checkbox name="rentproperty" value="6" onclick="process(this)" checked="checked">
						<img src="markers/6.png" />Suvilad<br>
						</td>
						<td><input type=checkbox name="rentproperty" value="7" onclick="process(this)" checked="checked">
						<img src="markers/7.png" />Majad<br>
						</td>
						<td><input type=checkbox name="rentproperty" value="8" onclick="process(this)" checked="checked">
						<img src="markers/8.png" />Majaosad<br>
						</td>
						<td>
						<input type=checkbox name="rentproperty" value="9" onclick="process(this)" checked="checked">
						<img src="markers/9.png" />Maad<br>
						</td>
						<td><input type=checkbox name="rentproperty" value="5" onclick="process(this)" checked="checked">
						<img src="markers/5.png" />Garaažid<br>
						</td>
						<td>
						<input type=button name="clear" onclick="clearAll(document.rentproperties.rentproperty)" value="Puhasta kõik"><br>
						</td>
					</tr>
				</table>
			</form>
	</div>
	</div>
  <div id="map_canvas" style="width:75%; height:75%"></div>
</body>
</html>
