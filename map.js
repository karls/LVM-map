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
