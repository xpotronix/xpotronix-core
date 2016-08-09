function calculate() {

	var long = $F('long');
	var sLong = 1;

	if (long < 0) {
		sLong = -1;
		long = long * -1;
	}

	var long_deg = Math.floor(long);
	var long_min = Math.floor((long - Math.floor(long)) * 60);
	var long_sec = Math.round((((long - Math.floor(long)) * 60) - Math.floor(((long - Math.floor(long)) * 60))) * 60 * 1000) / 1000;

	$('long_deg').innerHTML = long_deg * sLong;
	$('long_min').innerHTML = long_min;
	$('long_sec').innerHTML = long_sec;

	var lat = $F('lat');
	var sLat = 1;

	if (lat < 0) {
		sLat = -1;
		lat = lat * -1;
	}

	var lat_deg = Math.floor(lat);
	var lat_min = Math.floor((lat - Math.floor(lat)) * 60);
	var lat_sec = Math.round((((lat - Math.floor(lat)) * 60) - Math.floor(((lat - Math.floor(lat)) * 60))) * 60 * 10000) / 10000;

	$('lat_deg').innerHTML = lat_deg * sLat;
	$('lat_min').innerHTML = lat_min;
	$('lat_sec').innerHTML = lat_sec;


	var msg = "http://maps.google.com/maps?f=q&hl=en&geocode=&q=" + lat * sLat + "," + long * sLong + "&ie=UTF8&ll=" + lat * sLat + "," + long * sLong + "&spn=0.027108,0.109177&z=10";
	$('googleMaps').innerHTML = "<a href=\"" + msg + "\" target=\"new\">Show position in Google Maps!</a>";


	var long_degB = $F('long_degB') * 1;
	var sLong_degB = 1;

	if (long_degB < 0) {
		sLong_degB = -1;
		long_degB = long_degB * -1;
	}

	var longB = Math.round((long_degB + $F('long_minB') / 60 + $F('long_secB') / 3600) * 1000000) / 1000000;
	longB = longB * sLong_degB;
	$('longB').innerHTML = longB;

	var lat_degB = $F('lat_degB') * 1;
	var sLat_degB = 1;

	if (lat_degB < 0) {
		sLat_degB = -1;
		lat_degB = lat_degB * -1;
	}

	var latB = Math.round((lat_degB + $F('lat_minB') / 60 + $F('lat_secB') / 3600) * 1000000) / 1000000;

	latB = latB * sLat_degB;
	$('latB').innerHTML = latB;

	var msg = "http://maps.google.com/maps?f=q&hl=en&geocode=&q=" + latB + "," + longB + "&ie=UTF8&ll=" + latB + "," + longB + "&spn=0.027108,0.109177&z=10";
	$('googleMapsB').innerHTML = "<a href=\"" + msg + "\" target=\"new\">Show position in Google Maps!</a>";


}
