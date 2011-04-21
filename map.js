// Current transaction type
var transaction_type = 0; //sale

// Map
var map;
// Infowindow object
var infowindow = null;

// MarkerClusterer variables
var mc = null;
var mcOpts = {gridSize: 50, maxZoom: 12, averageCenter: true, zoomOnClick: false};

// Markers by transaction type
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

// Auxiliary arrays of sale and rent objects
var sale_objects;
var rent_objects;
/**
 * Create the map
 */
function initialize() {
	
	// Get the object data
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
					obj_info: "<p class='objinfo'><b><?php echo $infowindow_fields[$Lang]['address']; ?></b>: "
					+"<a href='http://www.lvm.ee/?op=body&zid="
					+geocode_results[h][i][j][0]+"&id=63&showLong=1' target='_blank'>"
					+geocode_results[h][i][j][1].street
					+" "+geocode_results[h][i][j][1].house_no
					+", "+geocode_results[h][i][j][1].city
					+"</a>"
					+"<br><b><?php echo $infowindow_fields[$Lang]['price']; ?></b>: "
					+geocode_results[h][i][j][1].price+" €"
					+"<br><b><?php echo $infowindow_fields[$Lang]['additional_info']; ?></b>: "
					+geocode_results[h][i][j][1].additional_info
					+"</p>",
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
