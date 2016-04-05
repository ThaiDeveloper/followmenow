/**
 * Locate store on gogle map
 * 
 * @category    design
 * @package     base_default
 * @author      Clarion Magento Team
 */

/**
 * Return parameters for back url
 *
 * @param  storeLat latitude
 * @param  storeLong logitude
 * @param  storeRadius store radius
 * @param  storeZoomLevel google map zoom level
 * @param  storeInfoText display infobox on google map
 * @return null
 */
function initialize(storeLat, storeLong, storeRadius, storeZoomLevel, storeInfoText, googleMapDivId)
{
    var address = '';
    var $result = '';
//    var latlng = new google.maps.LatLng(10.487812, -11.601563);
//    var mapOptions = {
//        zoom: 2,
//        center: latlng,
//        mapTypeId: google.maps.MapTypeId.ROADMAP
//    }
//    var map = new google.maps.Map(document.getElementById('store-list-map-canvas'), mapOptions);
    if (!storeLat && !storeLong && !storeRadius) {
        address = jQuery('#address').val();
        if (!address.length) {
            return;
        }
        var radius = jQuery('#radius').val();
        var num_results = jQuery('#num_results').val();
        var $url = jQuery('#controllerAction').val();
        geocoder = new google.maps.Geocoder();
        map = new google.maps.Map(document.getElementById("store-list-map-canvas"), {
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            streetViewControl: false,
            zoom: 7
        });
        var $addressAu = address + ',au';
        jQuery('#showResult').html('');
        jQuery('#stores-list-div').html('');
        geocoder.geocode({'address': $addressAu}, function (results, status) {
            function addMarker(data) {
                var position = new google.maps.LatLng(data.lat, data.lng);
                var marker = new google.maps.Marker({
                    position: position,
                    map: map,
                    title: data.name,
                    icon: data.icon,
                });
                // Create the infowindow with two DIV placeholders
                // One for a text string, the other for the StreetView panorama.
                var content = document.createElement("DIV");
                var title = document.createElement("DIV"),
                        info = document.createElement("DIV");
                title.innerHTML = data.name;
                info.innerHTML = data.content;
                content.appendChild(title);
                content.appendChild(info);
                content.setAttribute("class", "contentinfo");
                // Open the infowindow on marker click
                google.maps.event.addListener(marker, "click", function () {
                    if (infowindow) {
                        infowindow.close();
                    }
                    infowindow = new google.maps.InfoWindow({
                        content: content
                    });
                    infowindow.open(map, marker);
                });
            }
            status == google.maps.GeocoderStatus.OK;
            console.log(status);
            if (status == 'OK') {
                jQuery('#showResult').html('');
                jQuery('#stores-list-div').html('');
                var infoWindow = new google.maps.InfoWindow();
                jQuery.ajax({
                    type: "POST",
                    url: $url,
                    data: {'lat': results[0].geometry.location.lat(), 'lng': results[0].geometry.location.lng(), num_results: num_results, radius: radius},
                    success: function ($datas) {
                        if ($datas.length > 2) {
                            var $mypost = jQuery.parseJSON($datas);
                            if ($mypost.length > 0) {
                                map.setCenter(results[0].geometry.location);
                                var mapIconAdd = {
                                    url: _rootURL + 'images/red@2x.png',
                                    scaledSize: new google.maps.Size(24, 35), //retina format
                                    origin: new google.maps.Point(0, 0),
                                    anchor: new google.maps.Point(12, 35)
                                };
                                var markers = [
                                    {lat: results[0].geometry.location.lat(), lng: results[0].geometry.location.lng(), name: address, icon: mapIconAdd, content: ''}
                                ];
                                var $html = '';
                                var mapIcon = {
                                    url: _rootURL + 'images/green@2x.png',
                                    scaledSize: new google.maps.Size(24, 35), //retina format                                             origin: new google.maps.Point(0, 0),                                             anchor: new google.maps.Point(12, 35)
                                };
                                var $phone = '';
                                var $urlDealer = '';
                                jQuery.each($mypost, function ($key, $value) {
                                    if ($value.phone) {
                                        $phone = $value.phone;
                                    } else {
                                        $phone = '';
                                    }
                                    if ($value.url) {
                                        $urlDealer = $value.url;
                                    } else {
                                        $urlDealer = '#';
                                    }
                                    markers.push({icon: mapIcon, lat: $value.latitude, lng: $value.longitude, name: '<a href=" ' + $urlDealer + ' " target="_blank">' + $value.name + '</a>', content: $value.street_address + ' - ' + $value.city + ' - ' + $value.state + '<br><br><p>Phone: ' + $phone + '</p>'});
                                    $html += '<div class="stores-view"><p class="store-name"><a href="' + $urlDealer + '" target="_blank" >'
                                            + $value.name + '</a></p><p>'
                                            + $value.street_address + ' - '
                                            + $value.city + '</p><p>'
                                            + $value.state + '</p><p>' + $value.country + '</p><p>' + Math.round($value.distance * 100) / 100 + ' Km</p></div>';
                                });
                                jQuery('#stores-list-div').append($html);
//                                for (index in markers) {
//                                    addMarker(markers[index]);
//                                }
                                /*
                                 * ZOOM TO FIT ALL MARKER
                                 * @type google.maps.LatLngBounds
                                 */
                                var bounds = new google.maps.LatLngBounds();
                                for (var i = 0; i < markers.length; i++) {
                                    addMarker(markers[i]);
                                    var data = markers[i];
                                    bounds.extend(new google.maps.LatLng(data.lat, data.lng));
                                }
                                map.fitBounds(bounds);


                            }
                        } else {
                            geocoder.geocode({'address': address}, function (resultsEl, statusEl) {
                                statusEl == google.maps.GeocoderStatus.OK;
                                if (statusEl == 'OK') {
                                    var infoWindow = new google.maps.InfoWindow();
                                    jQuery.ajax({
                                        type: "POST",
                                        url: $url,
                                        data: {'lat': resultsEl[0].geometry.location.lat(), 'lng': resultsEl[0].geometry.location.lng(), num_results: num_results, radius: radius},
                                        success: function ($datas) {
                                            if ($datas.length > 2) {
                                                var $mypost = jQuery.parseJSON($datas);
                                                if ($mypost.length > 0) {
                                                    map.setCenter(resultsEl[0].geometry.location);
                                                    var mapIconAdd = {
                                                        url: _rootURL + 'images/red@2x.png',
                                                        scaledSize: new google.maps.Size(24, 35), //retina format
                                                        origin: new google.maps.Point(0, 0),
                                                        anchor: new google.maps.Point(12, 35)
                                                    };
                                                    markers = [
                                                        {lat: resultsEl[0].geometry.location.lat(), lng: resultsEl[0].geometry.location.lng(), name: address, icon: mapIconAdd, content: ''}
                                                    ];
                                                    var $html = '';
                                                    var mapIcon = {
                                                        url: _rootURL + 'images/green@2x.png',
                                                        scaledSize: new google.maps.Size(24, 35), //retina format
                                                        origin: new google.maps.Point(0, 0),
                                                        anchor: new google.maps.Point(12, 35)
                                                    };
                                                    var $phone = '';
                                                    var $urlDealer = '';
                                                    jQuery.each($mypost, function ($key, $value) {
                                                        if ($value.phone) {
                                                            $phone = $value.phone;
                                                        } else {
                                                            $phone = '';
                                                        }
                                                        if ($value.url) {
                                                            $urlDealer = $value.url;
                                                        } else {
                                                            $urlDealer = '#';
                                                        }
                                                        markers.push({icon: mapIcon, lat: $value.latitude, lng: $value.longitude, name: '<a href=" ' + $urlDealer + ' " target="_blank">' + $value.name + '</a>', content: $value.street_address + ' - ' + $value.city + ' - ' + $value.state + '<br><br><p>Phone: ' + $phone + '</p>'});
                                                        $html += '<div class="stores-view"><p class="store-name"><a href="' + $urlDealer + '" target="_blank">'
                                                                + $value.name + '</a></p><p>'
                                                                + $value.street_address + ' - '
                                                                + $value.city + '</p><p>'
                                                                + $value.state + '</p><p>' + $value.country + '</p><p>' + Math.round($value.distance * 100) / 100 + ' Km</p></div>';
                                                    });
                                                    jQuery('#stores-list-div').append($html);

//                                                    for (index in markers) {
//                                                        addMarker(markers[index]);
//                                                    }
                                                    var bounds = new google.maps.LatLngBounds();
                                                    for (var i = 0; i < markers.length; i++) {
                                                        addMarker(markers[i]);
                                                        var data = markers[i];
                                                        bounds.extend(new google.maps.LatLng(data.lat, data.lng));
                                                    }
                                                    map.fitBounds(bounds);
                                                    function addMarker(data) {
                                                        var position = new google.maps.LatLng(data.lat, data.lng);
                                                        // bounds.extend(position);
                                                        // bounds.extend(new google.maps.LatLng(data.lat, data.lng));
                                                        var marker = new google.maps.Marker({
                                                            position: position,
                                                            map: map,
                                                            title: data.name,
                                                            icon: data.icon,
                                                        });
                                                        // Create the infowindow with two DIV placeholders
                                                        // One for a text string, the other for the StreetView panorama.
                                                        var content = document.createElement("DIV");
                                                        var title = document.createElement("DIV"),
                                                                info = document.createElement("DIV");
                                                        title.innerHTML = data.name;
                                                        info.innerHTML = data.content;
                                                        content.appendChild(title);
                                                        content.appendChild(info);
                                                        content.setAttribute("class", "contentinfo");
                                                        // Open the infowindow on marker click
                                                        google.maps.event.addListener(marker, "click", function () {
                                                            if (infowindow) {
                                                                infowindow.close();
                                                            }
                                                            infowindow = new google.maps.InfoWindow({
                                                                content: content
                                                            });
                                                            infowindow.open(map, marker);
                                                        });
                                                        // map.fitBounds(bounds);
                                                    }
                                                }
                                            } else {
                                                $result = '<p>No results found!</p>';
                                                jQuery('#showResult').append($result);
                                                latlng = new google.maps.LatLng(10.487812, -11.601563);
                                                mapOptions = {
                                                    zoom: 2,
                                                    center: latlng,
                                                    mapTypeId: google.maps.MapTypeId.ROADMAP
                                                }
                                                map = new google.maps.Map(document.getElementById('store-list-map-canvas'), mapOptions);
                                            }
                                        }
                                    });
                                } else {
                                    $result = '<p>No results found!</p>';
                                    jQuery('#showResult').append($result);
                                    latlng = new google.maps.LatLng(10.487812, -11.601563);
                                    mapOptions = {
                                        zoom: 2,
                                        center: latlng,
                                        mapTypeId: google.maps.MapTypeId.ROADMAP
                                    }
                                    map = new google.maps.Map(document.getElementById('store-list-map-canvas'), mapOptions);
                                }
                            });
                        }
                    }
                });
            } else {
                geocoder.geocode({'address': address}, function (resultsEl, statusEl) {
                    statusEl == google.maps.GeocoderStatus.OK;
                    if (statusEl == 'OK') {
                        var infoWindow = new google.maps.InfoWindow();
                        jQuery.ajax({
                            type: "POST",
                            url: $url,
                            data: {'lat': resultsEl[0].geometry.location.lat(), 'lng': resultsEl[0].geometry.location.lng(), num_results: num_results, radius: radius},
                            success: function ($datas) {
                                var $mypost = jQuery.parseJSON($datas);
                                if ($mypost.length > 0) {
                                    map.setCenter(resultsEl[0].geometry.location);
                                    var mapIconAdd = {
                                        url: _rootURL + 'images/red@2x.png',
                                        scaledSize: new google.maps.Size(24, 35), //retina format
                                        origin: new google.maps.Point(0, 0),
                                        anchor: new google.maps.Point(12, 35)
                                    };
                                    markers = [
                                        {lat: resultsEl[0].geometry.location.lat(), lng: resultsEl[0].geometry.location.lng(), name: address, icon: mapIconAdd, content: ''}
                                    ];
                                    var $html = '';
                                    var mapIcon = {
                                        url: _rootURL + 'images/green@2x.png',
                                        scaledSize: new google.maps.Size(24, 35), //retina format
                                        origin: new google.maps.Point(0, 0),
                                        anchor: new google.maps.Point(12, 35)
                                    };
                                    var $phone = '';
                                    var $urlDealer = '';
                                    jQuery.each($mypost, function ($key, $value) {
                                        if ($value.phone) {
                                            $phone = $value.phone;
                                        } else {
                                            $phone = '';
                                        }
                                        if ($value.url) {
                                            $urlDealer = $value.url;
                                        } else {
                                            $urlDealer = '#';
                                        }
                                        markers.push({icon: mapIcon, lat: $value.latitude, lng: $value.longitude, name: '<a href=" ' + $urlDealer + ' " target="_blank" >' + $value.name + '</a>', content: $value.street_address + ' - ' + $value.city + ' - ' + $value.state + '<br><br><p>Phone: ' + $phone + '</p>'});
                                        $html += '<div class="stores-view"><p class="store-name"><a href="' + $urlDealer + '" target="_blank">'
                                                + $value.name + '</a></p><p>'
                                                + $value.street_address + ' - '
                                                + $value.city + '</p><p>'
                                                + $value.state + '</p><p>' + $value.country + '</p><p>' + Math.round($value.distance * 100) / 100 + ' Km</p></div>';
                                    });
                                    jQuery('#stores-list-div').append($html);
//                                    for (index in markers) {
//                                        addMarker(markers[index]);
//                                    }
                                    var bounds = new google.maps.LatLngBounds();
                                    for (var i = 0; i < markers.length; i++) {
                                        addMarker(markers[i]);
                                        var data = markers[i];
                                        bounds.extend(new google.maps.LatLng(data.lat, data.lng));
                                    }
                                    map.fitBounds(bounds);
                                    function addMarker(data) {
                                        var position = new google.maps.LatLng(data.lat, data.lng);
                                        // bounds.extend(position);
                                        // bounds.extend(new google.maps.LatLng(data.lat, data.lng));
                                        var marker = new google.maps.Marker({
                                            position: position,
                                            map: map,
                                            title: data.name,
                                            icon: data.icon,
                                        });
                                        // Create the infowindow with two DIV placeholders
                                        // One for a text string, the other for the StreetView panorama.
                                        var content = document.createElement("DIV");
                                        var title = document.createElement("DIV"),
                                                info = document.createElement("DIV");
                                        title.innerHTML = data.name;
                                        info.innerHTML = data.content;
                                        content.appendChild(title);
                                        content.appendChild(info);
                                        content.setAttribute("class", "contentinfo");
                                        // Open the infowindow on marker click
                                        google.maps.event.addListener(marker, "click", function () {
                                            if (infowindow) {
                                                infowindow.close();
                                            }
                                            infowindow = new google.maps.InfoWindow({
                                                content: content
                                            });
                                            infowindow.open(map, marker);
                                        });
                                        // map.fitBounds(bounds);
                                    }
                                }
                            }
                        });
                    } else {
                        $result = '<p>No results found!</p>';
                        jQuery('#showResult').append($result);
                        latlng = new google.maps.LatLng(10.487812, -11.601563);
                        mapOptions = {
                            zoom: 2,
                            center: latlng,
                            mapTypeId: google.maps.MapTypeId.ROADMAP
                        }
                        map = new google.maps.Map(document.getElementById('store-list-map-canvas'), mapOptions);
                    }
                });
            }
        });
    } else {
        //        var radius = storeRadius;
        //        var num_results = 100;
        //        var $url = jQuery('#controllerAction').val();
        //        geocoder = new google.maps.Geocoder();
//        map = new google.maps.Map(document.getElementById("store-list-map-canvas"), {
//            mapTypeId: google.maps.MapTypeId.ROADMAP,
//            streetViewControl: false,
//            zoom: 7
//        });
        //        if (map) {
        //            map.panTo(new google.maps.LatLng(storeLat, storeLong));
        //        }
//
        //        var mapIcon = {
        //            url: _rootURL + 'images/red@2x.png',
        //            scaledSize: new google.maps.Size(24, 35), //retina format
        //            origin: new google.maps.Point(0, 0),
//            anchor: new google.maps.Point(12, 35)
//        };
        //        markers = [
        //            {lat: storeLat, lng: storeLong, name: address, icon: mapIcon, content: 'Start Location'}
        //        ];
        //        var infoWindow = new google.maps.InfoWindow();
        //        jQuery.ajax({
        //            type: "POST",
        //            url: $url,
        //            data: {'lat': storeLat, 'lng': storeLong, num_results: num_results},
        //            success: function ($datas) {
//                // console.log($datas);
        //                // return false;
        //                var $mypost = jQuery.parseJSON($datas);
        //                var $html = '';
        //                var mapIcon = {
        //                    url: _rootURL + 'images/green@2x.png',
        //                    scaledSize: new google.maps.Size(24, 35), //retina format
        //                    origin: new google.maps.Point(0, 0),
        //                    anchor: new google.maps.Point(12, 35)
        //                }; //                jQuery.each($mypost, function ($key, $value) {                         //                    markers.push({icon: mapIcon, lat: $value.latitude, lng: $value.longitude, name: '<a href=" ' + $value.url + ' " >' + $value.name + '</a>', content: $value.street_address + ' - ' + $value.city + ' - ' + $value.country + '<br><br><p>Phone: ' + $value.phone + '</p>'}); //
        //                    $html += '<div class="stores-view"><p class="store-name"><a href="' + $value.url + '" >'
//                            + $value.name + '</a></p><p>'
//                            + $value.street_address + ' - '
//                            + $value.city + '</p><p>'
//                            + $value.state + '</p><p>' + $value.country + '</p><p>' + Math.round($value.distance * 100) / 100 + ' Km</p></div>';
//                });
//                jQuery('#stores-list-div').append($html);
//
//                console.log(markers);
//                // var bounds = new google.maps.LatLngBounds();
//                for (index in markers) {
//                    addMarker(markers[index]);
//                }
//
//                function addMarker(data) {
//                    var position = new google.maps.LatLng(data.lat, data.lng);
//                    // bounds.extend(position);
//                    // bounds.extend(new google.maps.LatLng(data.lat, data.lng));
//                    var marker = new google.maps.Marker({
//                        position: position,
//                        map: map,
//                        title: data.name,
//                        icon: data.icon,
//                    });
//                    // Create the infowindow with two DIV placeholders
//                    // One for a text string, the other for the StreetView panorama.
//                    var content = document.createElement("DIV");
//                    var title = document.createElement("DIV"),
//                            info = document.createElement("DIV");
//                    title.innerHTML = data.name;
//                    info.innerHTML = data.content;
//
//                    content.appendChild(title);
//                    content.appendChild(info);
//                    content.setAttribute("class", "contentinfo");
//
//                    // Open the infowindow on marker click
//                    google.maps.event.addListener(marker, "click", function () {
//                        if (infowindow) {
//                            infowindow.close();
//                        }
//                        infowindow = new google.maps.InfoWindow({
//                            content: content
//                        });
//                        infowindow.open(map, marker);
//                    });
//                    // map.fitBounds(bounds);
//                }
//            }
//        }).done(function (msg) {
//        });
    }

    // return false;
    var myCenter = new google.maps.LatLng(storeLat, storeLong);
    //convert distance from miles to meters
    var storeRadius = storeRadius * 1609.34;
    var mapProp = {
        center: myCenter,
        zoom: storeZoomLevel,
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    //Draw marker
//    var marker = new google.maps.Marker({
//        position: myCenter,
//        icon: {
//            url: _rootURL + 'images/red@2x.png',
//            scaledSize: new google.maps.Size(24, 35), //retina format
//            origin: new google.maps.Point(0, 0),
//            anchor: new google.maps.Point(12, 35)
//        }
//    });
//    marker.setMap(map);

    //Draw circle radius is in meter
//    if (storeRadius) {
//        var myCity = new google.maps.Circle({
//            center: myCenter,
//            radius: storeRadius,
//            strokeColor: "#FF00C4",
//            strokeOpacity: 0.8,
//            strokeWeight: 1,
//            fillColor: "#CF74C5",
//            fillOpacity: 0.4
//        });
//        myCity.setMap(map);
//    }

    // Open information window
    if (storeInfoText) {
        var infowindow = new google.maps.InfoWindow({
            content: storeInfoText
        });
        infowindow.open(map, marker);
    }
}

/**
 * add multiple stores on google map
 *
 * @param array markers markers
 * @param string googleMapDivId div id
 */
function place_multiple_markers(markers, googleMapDivId)
{
    var map;
    var bounds = new google.maps.LatLngBounds();
    var mapOptions = {
        mapTypeId: 'roadmap'
    };
    // Display a map on the page
    map = new google.maps.Map(document.getElementById('' + googleMapDivId), mapOptions);
    map.setTilt(45);
    // Display multiple markers on a map
    var infoWindow = new google.maps.InfoWindow(), marker, i;
    // Loop through our array of markers & place each one on the map  
    for (i = 0; i < markers.length; i++) {
        var position = new google.maps.LatLng(markers[i][1], markers[i][2]);
        bounds.extend(position);
        marker = new google.maps.Marker({
            position: position,
            map: map,
            title: markers[i][0]
        });
        // Allow each marker to have an info window    
        google.maps.event.addListener(marker, 'click', (function (marker, i) {
            return function () {
                infoWindow.setContent(markers[i][0]);
                infoWindow.open(map, marker);
            }
        })(marker, i));
        // Automatically center the map fitting all markers on the screen
        map.fitBounds(bounds);
    }
}

jQuery(document).ready(function () {
    initialize('-37.814215', '144.96323099999995');
});