```javascript
var findstore;
var findstore.results;
var findstore.map;
var findstore.bounds;
var findstore.infoWindow;
var findstore.markers = [];
var findstore.mappings = [];
var findstore.dirMap;
var findstore.dirBounds;
var findstore.dirDisplay;
var findstore.dirService;
var findstore.dirMarkers = [];
var findstore.uriVars = {};

$(document).ready(function() {
	// limit loading findstore map
	if ($('#findstore-map').length != 0) {
		findstore.bounds = new google.maps.LatLngBounds();
		findstore.infoWindow = new google.maps.InfoWindow();
	    findstore.map = new google.maps.Map(document.getElementById("findstore-map"), {
	    	center: new google.maps.LatLng(0, 0),
	    	zoom: 2,
	    	mapTypeId: 'roadmap',
	    	mapTypeControlOptions: {style: google.maps.MapTypeControlStyle.DROPDOWN_MENU}
	    });
	    // get URI parameters
		var uriSegments = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
	        findstore.uriVars[key] = value;
	    });
	    if (typeof(findstore.uriVars["uri"]) !== 'undefined'){
			initCheckbox(findstore.uriVars["uri"]);
		}
	}

	// limit loading directions map
	if ($('#directions-map').length != 0) {
		findstore.dirBounds = new google.maps.LatLngBounds();
    	findstore.dirService = new google.maps.DirectionsService();
    	findstore.dirDisplay = new google.maps.DirectionsRenderer();
		findstore.dirMap = new google.maps.Map(document.getElementById("directions-map"), {
			center: new google.maps.LatLng(0, 0),
			zoom: 2,
			mapTypeId: 'roadmap',
			mapTypeControlOptions: {style: google.maps.MapTypeControlStyle.DROPDOWN_MENU}
		});
		findstore.dirDisplay.setMap(findstore.dirMap);
		initDirections(dirAddress, dirPosition);
	}

	// IE indexOf workaround
	// https://developer.mozilla.org/en-US/docs/JavaScript/Reference/Global_Objects/Array/indexOf
	if (!Array.prototype.indexOf) {
	    Array.prototype.indexOf = function (searchElement /*, fromIndex */ ) {
	        "use strict";
	        if (this == null) {
	            throw new TypeError();
	        }
	        var t = Object(this);
	        var len = t.length >>> 0;
	        if (len === 0) {
	            return -1;
	        }
	        var n = 0;
	        if (arguments.length > 1) {
	            n = Number(arguments[1]);
	            if (n != n) { // shortcut for verifying if it's NaN
	                n = 0;
	            } else if (n != 0 && n != Infinity && n != -Infinity) {
	                n = (n > 0 || -1) * Math.floor(Math.abs(n));
	            }
	        }
	        if (n >= len) {
	            return -1;
	        }
	        var k = n >= 0 ? n : Math.max(len - Math.abs(n), 0);
	        for (; k < len; k++) {
	            if (k in t && t[k] === searchElement) {
	                return k;
	            }
	        }
	        return -1;
	    }
	}
});

/* Click Events */

// Submit form
$('#findstore-submit').click(function() {
	$('#findstore-search').submit();
});

$('#findstore-search').submit(function(e) {
	e.preventDefault();
	$('#findstore-right-middle .message').remove();
	var form = this;
	validate(form).done(function(data) {
		findstore.results = data;
		initMappings();
	}).fail(function() {
		clearMap();
		clearResults();
	});
});

// Loading icon for any AJAX
$(".findstore-loading").bind("ajaxStart", function(){
	$(this).show();
}).bind("ajaxComplete", function(){
	$(this).hide();
});

// Show more results click event
$('#findstore-results .more-results').live("click", function(e) {
	var order = $(this).closest('tr').attr('data-order');
	var group = $(this).closest('tr').attr('data-group');
	findstore.mappings[order].open = true;
	showMore(order, group);
});

// Hide more results click event
$('#findstore-results .hide-results').live("click", function(e) {
	var order = $(this).closest('tr').attr('data-order');
	var group = $(this).closest('tr').attr('data-group');
	findstore.mappings[order].open = false;
	hideMore($(this).closest('tr').attr('data-order'), $(this).closest('tr').attr('data-group'));
});

$('#findstore-type-filters :input').live("click", function(e) {
	initMappings();
});

$('form[name="getDirections"] #btnGetDirections').live("click", function(e) {
	e.preventDefault();
	var start = $('form[name="getDirections"] input[name="fromAddress"]').val();
	var end = $('form[name="getDirections"] input[name="toAddress"]').val();
	calcRoute(start, end);
});

/* Functions */

// retreive the data
function validate(form){
	var valid = true;
	findstore.results = null;
	var response = $.Deferred();

	if (valid === true) {
		var selZip = $('#findstore-zip').val();
		var selRadius = $('#findstore-radius').val();
		var selFilters = new Array();
		$('input[name=findstore-filters]:checked').each(function(key,val){
			selFilters.push($(val).val());
		});
		var selModel = findstore.uriVars['model'];

		$.ajax({
			url: 'findastore?task=findstore.search&format=raw',
			data: { selZip: selZip, selFilters: selFilters, selRadius: selRadius, selModel: selModel }
		}).done(function(data) {
			if ( data.length > 0 ) {
				response.resolve(data);
			} else {
				response.reject();
			}
		}).fail(function() {
			response.reject();
		});
	} else {
		response.reject();
	}

	return response.promise();
}

// preselect checkboxes
function initCheckbox(uri) {
	var segments = uri.split("/");
	$.each(window.filters, function(f, filter){
		$.each(filter.mappings, function(m, mapping){
			if ($.isEmptyObject(mapping.uris)) {
				var matchAll = false;
			} else {
				var matchAll = true;
				var uris = $.parseJSON(mapping.uris);
				$.each(uris, function(u, uriString){
					var uriArray = uriString.split(',');
					$.each(uriArray, function(u, uri){
						uriTrimmed = uri.replace(/\s/g, '');
						if (segments.indexOf(uriTrimmed) != -1) {
						} else {
							matchAll = false;
						}
					});
				});
			}
			if(matchAll){
				$('#findstore-filter-' + mapping.id).attr('checked', 'checked');
			}
		});
	});
}

// prepare the directions iframe view
function initDirections(address, position){
	$('#findstore-direction-steps').empty();
	$('form[name="getDirections"] input[name="toAddress"]').val(address);

	for (i = 0; i < findstore.dirMarkers.length; i++) {
		findstore.dirMarkers[i].setMap(null);
	}
	findstore.dirMarkers.length = 0;
	findstore.dirDisplay.setMap(null);

	findstore.dirBounds = new google.maps.LatLngBounds();

	position = position.replace(/[()]/g,'');
	position = position.split(',');

	var marker = new google.maps.Marker({
		position: new google.maps.LatLng(position[0], position[1]),
		map: findstore.dirMap,
		icon: 'http://maps.google.com/mapfiles/ms/micons/green-dot.png'
	});

	findstore.dirMarkers.push(marker);
	findstore.dirBounds.extend(marker.position);
	findstore.dirMap.fitBounds(findstore.dirBounds);
	findstore.dirMap.setZoom(14);
}

function calcRoute(start, end) {
	var request = {
		origin:start,
		destination:end,
		travelMode: google.maps.TravelMode.DRIVING
	};
	findstore.dirService.route(request, function(response, status) {
		if (status == google.maps.DirectionsStatus.OK) {
			findstore.dirDisplay.setDirections(response);
			findstore.dirDisplay.setMap(findstore.dirMap);
			showSteps(response);
		}	
	});
}

function showSteps(directionResult) {
	$('#findstore-direction-steps').empty();

	for (i = 0; i < findstore.dirMarkers.length; i++) {
		findstore.dirMarkers[i].setMap(null);
	}
	findstore.dirMarkers.length = 0;

	var route = directionResult.routes[0].legs[0];

	$('#findstore-direction-steps').append(
		'<h5>' + route.start_address + '</h5>'	
	);

	for (var i = 0; i < route.steps.length; i++) {
		$('#findstore-direction-steps').append(
			'<li class="step"><span>' + (i + 1) + '.</span><div class="detail">' + route.steps[i].instructions + '</div></li>'
		);
	}

	$('#findstore-direction-steps').append(
		'<h5>' + route.end_address + '</h5>'	
	);
}

function initMappings(){
	findstore.mappings.length = 0;
	$.each(findstore.results, function(i, result) {
		var latlng = new google.maps.LatLng(
        	result[0].lat,
        	result[0].lon
        );
		var marker = new google.maps.Marker({
			position: latlng
		})
		var mapping = {
			details: result[0],
			marker: marker,
			group: i,
			open: false
		}
		if($(result).length > 1) {
			mapping.children = true;
		}
		findstore.mappings.push(mapping);
	});
	filterTypes();
	updateResults();
}

function showMore(order, group){
	$.each(findstore.results[group], function(s, store) {
		if (s > 0) {
			var latlng = new google.maps.LatLng(
	        	store.lat,
	        	store.lon
	        );	
			var marker = new google.maps.Marker({
				position: latlng
			});
			var mapping = {
				details: store,
				marker: marker,
				group: group,
				child: true
			}		
			findstore.mappings.splice(Number(order) + Number(s), 0, mapping);
		}
	});
	updateResults();
	var storeCount = findstore.results[group].length - 1;
	$('#findstore-results-body tr[data-order="' + order + '"] .more-results').replaceWith(
		'<div class="hide-results">- Hide more stores (' + storeCount + ')</div>'
	);
}

function hideMore(order, group){
	index = (Number(order) + 1);
	amount = $('#findstore-results-body tr[data-group="' + group + '"]').length - 1;
	findstore.mappings.splice(index, amount);
	updateResults();
	var storeCount = findstore.results[group].length - 1;
	$('#findstore-results-body tr[data-order="' + order + '"] .hide-results').replaceWith(
		'<div class="more-results">+ Show more stores (' + storeCount + ')</div>'
	);
}

function filterTypes(){
	var filterTypes = [];
	if ($("input[name='type-filter[]']:checked").length < 1) {
		filterTypes = ['Home Center', 'Window and Door Store', 'Lumber Yard']
	} else {
		$.each($("input[name='type-filter[]']:checked"), function() {;
			filterTypes.push($(this).val());
		});
	}
	var spliceMappings = [];
	$.each(findstore.mappings, function(i, mapping) {
		store = mapping.details;
		match = filterTypes.indexOf(store.type);
		if (match === -1) {
			spliceMappings.push(i);
		}
	});
	$.each(spliceMappings.reverse(), function(i, index) {
		findstore.mappings.splice(index, 1);
	});
}

function updateResults(){
	$('#findstore-right-bottom').show();
	$('#findstore-right-middle .message').remove();
	$('#findstore-results-body tr').not('.first').remove();
	$.each(findstore.mappings, function(i, mapping) {
		var store = mapping.details;
		if (i%2 == 0) { var oddeven = 'even' } else { var oddeven = 'odd' }
		var clone = $('#findstore-results-body tr.first').clone();
		clone.show();
		clone.removeClass('first').addClass(oddeven).attr('data-order', i).attr('data-group', mapping.group);
		clone.find('.marker img').attr('src', 'http://chart.apis.google.com/chart?chst=d_map_spin&chld=.75|0|CAD7FC|16|b|' + (i + 1));		
		clone.find('.details h5').html(store.name);
		clone.find('.details h6').html(store.type);
		clone.find('.details p').html(store.address + '<br>' + store.city + ', ' + store.state + ' ' + store.zip + '<br>' + store.phone);
		clone.find('.details span').html(store.distance + ' mi');
		clone.find('.directions a').attr('href', 'index.php?option=com_jw_findstore&layout=directions&address=' + store.address + ' ' + store.city + ', ' + store.state + ' ' + store.zip + '&position=' + mapping.marker.position + '&tmpl=hopup');
		clone.appendTo($('#findstore-results-body'));

		$.each(store.products, function(p, product) {
			clone.find('td[data-category='+product.category+']').append('<li data-match="'+product.match+'">' + product.name + '</li>');
		});

		if (mapping.children) {
			var storeCount = findstore.results[mapping.group].length -1;
			if (mapping.open){
				clone.find('td.store-info').append('<div class="hide-results">- Hide more stores (' + storeCount + ')</div>');
			} else {
				clone.find('td.store-info').append('<div class="more-results">+ Show more results (' + storeCount + ')</div>');
			}
		}

		if (mapping.child) {
			clone.addClass('child');
		}
	});

	if (findstore.mappings.length > 0){
		updateMap();
	} else {
		clearMap();
		clearResults();
	}

	$(".iframe").colorbox({iframe:true, width:"840px", height:"600px", scrolling: false});
}

function updateMap(){
	clearMap();
	findstore.bounds = new google.maps.LatLngBounds();
	$.each(findstore.mappings, function(i, mapping) {
		var store = mapping.details;
		var overlay = 
			'<div class="details">' +
				'<h4>' + store.name + '</h4>' +
				'<h6>' + store.type + '</h6>' +
				'<p>' + store.address + '</p>' +
				'<p>' + store.city + ', ' + store.state + ' ' + store.zip + '</p>' +
				'<p>' + store.phone + '</p>' +
				'<span>' + store.distance + ' mi</span>' +
			'</div>';
		mapping.marker.setIcon('http://chart.apis.google.com/chart?chst=d_map_spin&chld=.75|0|CAD7FC|16|b|' + ( i + 1 ));
		mapping.marker.setMap(map);
		google.maps.event.addListener(mapping.marker, 'click', function() {
			findstore.infoWindow.close();
			infoWindow.setContent(overlay);
      		map.setZoom(10);
          	map.panTo(mapping.marker.getPosition());
      		findstore.infoWindow.open(map, mapping.marker);
        });

		findstore.bounds.extend(mapping.marker.position);
		findstore.markers.push(mapping.marker);
	});

	map.fitBounds(findstore.bounds);
}

function clearResults(){
	$('#findstore-results-body tr').not('.first').remove();
	$('#findstore-right-bottom').hide();
	$('#findstore-right-middle .message').remove();
	$('#findstore-right-middle').append('<span class="message">There are no locations that match your criteria. Please try a different search.</span>');
}

function clearMap(){
	for (var i = 0; i < findstore.markers.length; i++) {
		findstore.markers[i].setMap(null);
	}
	findstore.markers.length = 0;
}
```