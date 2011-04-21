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
	html {
		height: 100%;
		font-size: 16px;
		font-family: Arial, Helvetica, Verdana;
	}
	
	body {
		height: 100%;
		margin: 0px;
		padding: 0px;
	}
	
	p.objinfo {
		margin: 0;
		font-size: 0.8em;
	}
	
	p.objinfo a {
		color: #F18E00;
		text-decoration: none;
	}
	
	p.objinfo a:hover {
		text-decoration: underline;
	}
	
	form {
		margin: 0;
	}
	
	#map_canvas {
		margin-top: 5px;
		height: 100%;
	}
</style>
<link rel="stylesheet" type="text/css" href="jquery-ui/css/smoothness/jquery-ui-1.8.11.custom.css" />
<script type="text/javascript" src="jquery-ui/js/jquery-1.5.1.min.js"></script>
<script type="text/javascript" src="jquery-ui/js/jquery-ui-1.8.11.custom.min.js"></script>
<script type="text/javascript"
				src="http://maps.google.com/maps/api/js?sensor=false">
</script>
<script type="text/javascript" src="markerclusterer_packed.js"></script>
<script type="text/javascript">
	// The whole of object data
	var geocode_results = <?php echo $data; ?>;
</script>
<script type="text/javascript" src="map.js"></script>
</head>

<body>
	<div id="tabs" style="width: 666px; font-size: 0.7em;">
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
						</tr>
						<tr>
						<td><input type=checkbox name="saleproperty" value="8" onclick="process(this)" checked="checked">
						<img src="markers/8.png" /><?php echo $obj_type_string_pool[$Lang][8]; ?><br>
						</td>
						<td><input type=checkbox name="saleproperty" value="7" onclick="process(this)" checked="checked">
						<img src="markers/7.png" /><?php echo $obj_type_string_pool[$Lang][7]; ?><br>
						</td>
						<td><input type=checkbox name="saleproperty" value="5" onclick="process(this)" checked="checked">
						<img src="markers/5.png" /><?php echo $obj_type_string_pool[$Lang][5]; ?><br>
						</td>
						<td><input type=checkbox name="saleproperty" value="6" onclick="process(this)" checked="checked">
						<img src="markers/6.png" /><?php echo $obj_type_string_pool[$Lang][6]; ?><br>
						</td>
						</tr>
						<tr>
						<td><input type=checkbox name="saleproperty" value="4" onclick="process(this)" checked="checked">
						<img src="markers/4.png" /><?php echo $obj_type_string_pool[$Lang][4]; ?><br>
						</td>
						<td>
						<input type=checkbox name="saleproperty" value="9" onclick="process(this)" checked="checked">
						<img src="markers/9.png" /><?php echo $obj_type_string_pool[$Lang][9]; ?><br>
						</td>
						<td><input type=button name="set" onclick="setAll(document.saleproperties.saleproperty)"
						           value="<?php echo $obj_type_string_pool[$Lang][10]; ?>"><br>
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
  <div id="map_canvas" style="width:666px; height:600px"></div>
</body>
</html>
