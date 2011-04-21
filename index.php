<?php
	session_start();
	session_register("Lang");
	$Lang = "est";
	header('Content-type: text/html; charset=utf-8');
	$data = file_get_contents("final_data.$Lang.json");
	
	$obj_type_string_pool = array(
		"est" => array(
			"1-toalised",
			"2-toalised",
			"3-toalised",
			"4-toalised",
			"Äripinnad",
			"Garaažid",
			"Suvilad",
			"Majad",
			"Majaosad",
			"Maad",
			"Vali kõik",
			"Puhasta kõik"
		),
		"eng" => array(
			"One-room",
			"Two-room",
			"Three-room",
			"Four-room",
			"Commercial",
			"Garages",
			"Summer cottages",
			"Houses",
			"House shares",
			"Lands",
			"Select all",
			"Clear all"
		),
		"rus" => array(
			"1-комнатные",
			"2-комнатные",
			"3-комнатные",
			"4-комнатные",
			"Коммерческий",
			"гаражы",
			"Дача",
			"Дом",
			"Часть дома",
			"Участок земли",
			"Выбрать все",
			"Очистить все"
		),
		"fin" => array(
			"1 huoneen asunto",
			"2 huoneen asunto",
			"3 huoneen asunto",
			"4 huoneen asunto",
			"Liiketila",
			"Autotalli",
			"Mökki",
			"Talo",
			"Paritalo",
			"Tontti",
			"Valitse kaikki",
			"Puhdista kaikki"
		)
	);
	
	$infowindow_fields = array(
		"est" => array(
			"address" => "Aadress",
			"price" => "Hind",
			"additional_info" => "Lisainfo"
		),
		"eng" => array(
			"address" => "Address",
			"price" => "Price",
			"additional_info" => "Additional info"
		),
		"fin" => array(
			"address" => "Адрес",
			"price" => "Щена",
			"additional_info" => "Дополнцтелбная информация"
		),
		"rus" => array(
			"address" => "Lähiosoite",
			"price" => "Hinta",
			"additional_info" => "Lisätietoa"
		)
	);
	
	$transaction_type = array(
		"est" => array(
			"Müük",
			"Üürile anda"
		),
		"eng" => array(
			"Sale",
			"For rent",
		),
		"fin" => array(
			"Myynti",
			"Annetaan vuokralle"
		),
		"rus" => array(
			"Продажа",
			"Сдам в аренду"
		)
	);
?>
<html>
<head>
<title>LVM map</title>
<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
<style type="text/css">
 html { height: 100%; font-size: 16px;}
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
						obj_info: "<?php echo $infowindow_fields[$Lang]["address"]; ?>: "+geocode_results[h][i][j][1].street
						+" "+geocode_results[h][i][j][1].house_no
						+", "+geocode_results[h][i][j][1].city
						+"<br><?php echo $infowindow_fields[$Lang]["price"]; ?>: "+geocode_results[h][i][j][1].price+" €"
						+"<br><?php echo $infowindow_fields[$Lang]["link"]; ?>: <a href='http://www.lvm.ee/?op=body&zid="+geocode_results[h][i][j][0]+"&id=63&showLong=1' target='_blank'>"+geocode_results[h][i][j][0]+"</a>"
						+"<br><?php echo $infowindow_fields[$Lang]["additional_info"]; ?>: "+geocode_results[h][i][j][1].additional_info,
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

	function process(box)
	{
		for (i = 0; i < markers[transaction_type][box.value].length; i++)
			markers[transaction_type][box.value][i].setVisible(box.checked);
		mc.resetViewport(true);
		mc.redraw();
	}
	
	function checkAll(property)
	{
		for (i = 0; i < property.length; i++)
			property[i].checked = true;
	}
	
	function uncheckAll(property)
	{
		for (i = 0; i < property.length; i++)
			property[i].checked = false;
	}
	
	function setAll(property)
	{
		checkAll(property);
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
		uncheckAll(property);
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
			checkAll(document.saleproperties.saleproperty);
		else
			checkAll(document.rentproperties.rentproperty);
		
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
	<div id="tabs" style="width: 75%; font-size: 0.7em;">
		<ul>
			<li><a href="#tabs-1"><?php echo $transaction_type[$Lang][0]; ?></a></li>
			<li><a href="#tabs-2"><?php echo $transaction_type[$Lang][1]; ?></a></li>
		</ul>
		<div id="tabs-1">
			<form id="sale-form" name="saleproperties" action="">
				<table id="objects-selection" style="font-size: 1em;">
					<tr>
						<td><input type=checkbox name="saleproperty" value="0" onclick="process(this)" checked="checked">
						<img src="markers/0.png" /><?php echo $obj_type_string_pool[$Lang][0]; ?><br>
						</td>
						<td><input type=checkbox name="saleproperty" value="1" onclick="process(this)" checked="checked">
						<img src="markers/1.png" /><?php echo $obj_type_string_pool[$Lang][1]; ?><br>
						</td>
						<td><input type=checkbox name="saleproperty" value="2" onclick="process(this)" checked="checked">
						<img src="markers/2.png" /><?php echo $obj_type_string_pool[$Lang][2]; ?><br>
						</td>
						<td><input type=checkbox name="saleproperty" value="3" onclick="process(this)" checked="checked">
						<img src="markers/3.png" /><?php echo $obj_type_string_pool[$Lang][3]; ?><br>
						</td>
						<td><input type=checkbox name="saleproperty" value="4" onclick="process(this)" checked="checked">
						<img src="markers/4.png" /><?php echo $obj_type_string_pool[$Lang][4]; ?><br>
						</td>
						<td><input type=button name="set" onclick="setAll(document.saleproperties.saleproperty)"
						           value="<?php echo $obj_type_string_pool[$Lang][10]; ?>"><br>
						</td>
						</tr>
						<tr>
						<td><input type=checkbox name="saleproperty" value="6" onclick="process(this)" checked="checked">
						<img src="markers/6.png" /><?php echo $obj_type_string_pool[$Lang][6]; ?><br>
						</td>
						<td><input type=checkbox name="saleproperty" value="7" onclick="process(this)" checked="checked">
						<img src="markers/7.png" /><?php echo $obj_type_string_pool[$Lang][7]; ?><br>
						</td>
						<td><input type=checkbox name="saleproperty" value="8" onclick="process(this)" checked="checked">
						<img src="markers/8.png" /><?php echo $obj_type_string_pool[$Lang][8]; ?><br>
						</td>
						<td>
						<input type=checkbox name="saleproperty" value="9" onclick="process(this)" checked="checked">
						<img src="markers/9.png" /><?php echo $obj_type_string_pool[$Lang][9]; ?><br>
						</td>
						<td><input type=checkbox name="saleproperty" value="5" onclick="process(this)" checked="checked">
						<img src="markers/5.png" /><?php echo $obj_type_string_pool[$Lang][5]; ?><br>
						</td>
						<td>
						<input type=button name="clear" onclick="clearAll(document.saleproperties.saleproperty)"
						       value="<?php echo $obj_type_string_pool[$Lang][11]; ?>"><br>
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
						<img src="markers/0.png" /><?php echo $obj_type_string_pool[$Lang][0]; ?><br>
						</td>
						<td><input type=checkbox name="rentproperty" value="1" onclick="process(this)" checked="checked">
						<img src="markers/1.png" /><?php echo $obj_type_string_pool[$Lang][1]; ?><br>
						</td>
						<td><input type=checkbox name="rentproperty" value="2" onclick="process(this)" checked="checked">
						<img src="markers/2.png" /><?php echo $obj_type_string_pool[$Lang][2]; ?><br>
						</td>
						<td><input type=checkbox name="rentproperty" value="3" onclick="process(this)" checked="checked">
						<img src="markers/3.png" /><?php echo $obj_type_string_pool[$Lang][3]; ?><br>
						</td>
						<td><input type=checkbox name="rentproperty" value="4" onclick="process(this)" checked="checked">
						<img src="markers/4.png" /><?php echo $obj_type_string_pool[$Lang][4]; ?><br>
						</td>
						<td><input type=button name="set" onclick="setAll(document.rentproperties.rentproperty)"
						       value="<?php echo $obj_type_string_pool[$Lang][10]; ?>"><br>
						</td>
						</tr>
						<tr>
						<td><input type=checkbox name="rentproperty" value="6" onclick="process(this)" checked="checked">
						<img src="markers/6.png" /><?php echo $obj_type_string_pool[$Lang][6]; ?><br>
						</td>
						<td><input type=checkbox name="rentproperty" value="7" onclick="process(this)" checked="checked">
						<img src="markers/7.png" /><?php echo $obj_type_string_pool[$Lang][7]; ?><br>
						</td>
						<td><input type=checkbox name="rentproperty" value="8" onclick="process(this)" checked="checked">
						<img src="markers/8.png" /><?php echo $obj_type_string_pool[$Lang][8]; ?><br>
						</td>
						<td>
						<input type=checkbox name="rentproperty" value="9" onclick="process(this)" checked="checked">
						<img src="markers/9.png" /><?php echo $obj_type_string_pool[$Lang][9]; ?><br>
						</td>
						<td><input type=checkbox name="rentproperty" value="5" onclick="process(this)" checked="checked">
						<img src="markers/5.png" /><?php echo $obj_type_string_pool[$Lang][5]; ?><br>
						</td>
						<td>
						<input type=button name="clear" onclick="clearAll(document.rentproperties.rentproperty)"
						       value="<?php echo $obj_type_string_pool[$Lang][11]; ?>"><br>
						</td>
					</tr>
				</table>
			</form>
	</div>
	</div>
  <div id="map_canvas" style="width:75%; height:75%"></div>
</body>
</html>
