<?php
session_start();
if (!$_SESSION["authorized"] || strlen($_SESSION["username"]) == 0 || strlen($_SESSION["user_id"]) == 0)
{
	header('Location: login.php');
}
$username = $_SESSION["username"];
$password = $_SESSION["password"];
require_once("../classes/waytag.class.php");
require_once("../config/config.inc.php");
require_once("../classes/user.class.php");
$myMobileWaytag = Waytag::getMyMobileWaytag($username, $password);
$closestBusinesses = Waytag::getClosestBusinessWaytags($username, $password, $myMobileWaytag["dWayTagLatitude"], $myMobileWaytag["dWayTagLongitude"]);
?>
<html>
<head>
<meta charset="UTF-8">
<title>WayTag</title>
<script src="http://maps.google.com/maps/api/js?sensor=false"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
<style type="text/css">
.visiting_location
{
	color: red;
}
.normal_location
{
	color: black;
}
</style>
</head>
<body onload="">
<a href="logout.php">Log out</a>
<div id="debug"></div>
<div id="map" style="float:left"></div>
<div id="checkinHistory"></div>
<script type="text/javascript">
lat = <?=$myMobileWaytag["dWayTagLatitude"]?>;// 51.758537;
lon = <?=$myMobileWaytag["dWayTagLongitude"]?>;//5.50724;
latlon=new google.maps.LatLng(lat, lon);
mapholder=document.getElementById('map');
mapholder.style.height="400px";
mapholder.style.width="500px";

var myOptions={
center:latlon,zoom:14,
mapTypeId:google.maps.MapTypeId.ROADMAP,
mapTypeControl:false,
navigationControlOptions:{style:google.maps.NavigationControlStyle.SMALL}
};
var map=new google.maps.Map(mapholder, myOptions);
var myMobileWaytag = new Array();
var markers = new Array();
var previousSelected;

myMobileWaytag = new Array();
myMobileWaytag["displayName"] = "<?= $myMobileWaytag["cDisplayName"]?>";
myMobileWaytag["latitude"] = "<?= $myMobileWaytag["dWayTagLatitude"]?>";
myMobileWaytag["longitude"] = "<?= $myMobileWaytag["dWayTagLongitude"]?>";
myMobileWaytag["waytagTag"] = "<?= $myMobileWaytag["cCustomReference"]?>";

makerlatlon=new google.maps.LatLng(myMobileWaytag["latitude"], myMobileWaytag["longitude"]);
mobileMarkerImage = new google.maps.MarkerImage("http://maps.google.com/mapfiles/kml/pal3/icon52.png", new google.maps.Size(32, 32), new google.maps.Point(0,0), new google.maps.Point(16,16));
mobileMarkerImageShadow = new google.maps.MarkerImage("http://maps.google.com/mapfiles/kml/pal3/icon52s.png", new google.maps.Size(59, 32), new google.maps.Point(0,0), new google.maps.Point(17,16));
marker=new google.maps.Marker({position:makerlatlon,map:map,title:myMobileWaytag["displayName"],icon:mobileMarkerImage, shadow:mobileMarkerImageShadow, flat:true});

infoWindow = new google.maps.InfoWindow();
myMobileWaytag["marker"] = marker;
<?php //google.maps.event.addListener(marker, 'dragend', function(event){changeMarker(wayTag, event.latLng);}); 
?>

function panMapToWayTag(wayTagID)
{
	var markerlatlon = new google.maps.LatLng(wayTags[wayTagID]["latitude"], wayTags[wayTagID]["longitude"]);
	map.panTo(markerlatlon);
}

var businesses = new Array();
<?php 
foreach ($closestBusinesses as $business)
{?>
	var business = new Array();
	<?php 
	foreach ($business as $key => $value)
	{?>
	business["<?=$key?>"] = "<?=$value?>";
	<?php }?>
	businesses["<?=$business["dWayTagObj"] ?>"] = business;
	<?php 
}
?>

function displayBusinesses()
{
	for (i in businesses)
	{
		var business = businesses[i];
		
		makerlatlon=new google.maps.LatLng(business["dWayTagLatitude"], business["dWayTagLongitude"]);
		marker=new google.maps.Marker({position:makerlatlon,map:map,title:business["cDisplayName"]});

		var contentString = '<div class="content">'+
		  '<div class="siteNotice">'+
		  '</div>' +
		  '<h2 id="firstHeading">Here is: '+ myMobileWaytag["cCustomReference"] + '</h2>'+
		  'Description: ' + business["cDisplayName"] +
		  '</div>'+ 
		  '</div>';	
		marker.set("waytagId", business["dWayTagObj"]);
		google.maps.event.addListener(marker, 'click', function() {
			onMarkerClick(this);
			});

		business["marker"] = marker;
		business["contentString"] = contentString;
	}
}

function createBusinessInfoContent(business)
{
	var business = businesses[business];
	var peopleCheckedInAt = getPeopleCheckedInAt(business["dWayTagObj"]);
	var currentlyCheckedIn = peopleCheckedInAt[1]>0;
	var cssClass = currentlyCheckedIn?"visiting_location":"normal_location";
	var result = "<div class='" + cssClass + "' id='infoWindow'>";
	result += "<h3>" + business["cCustomReference"] + "</h3>";
	result +=  peopleCheckedInAt[0] + " people are checked in at this location" + (currentlyCheckedIn?", including you!":".") + "<br />";
	var waytagId = business["dWayTagObj"];
	var waytagReference = business["cCustomReference"];
	//result += "<button type='button' onclick="+(currentlyCheckedIn?"checkOut":"checkIn")+"('"+business["dWayTagObj"]+"');>"+(currentlyCheckedIn?"Checkout":"Checkin")+"</button>";
	var functionCall = (currentlyCheckedIn?"checkOut(\""+business['dWayTagObj']+"\");":"checkIn(\""+business['dWayTagObj']+"\", \""+business['cCustomReference']+"\");");
	result += "<button type='button' onclick='"+ functionCall +"');>"+(currentlyCheckedIn?"Checkout":"Checkin")+"</button>";
	result += "</div>";
	return result;
}

function getPeopleCheckedInAt(waytagId)
{
	var xmlhttp;
	if (window.XMLHttpRequest)
	{// code for IE7+, Firefox, Chrome, Opera, Safari
	  xmlhttp=new XMLHttpRequest();
	}
	else
	{// code for IE6, IE5
	  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	xmlhttp.open("POST","waytags.php",false);
	xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	xmlhttp.send("function=getPeopleCheckedInAt&waytagId="+waytagId);
	return xmlhttp.responseText.split(";");
}

function highlightMarker(marker)
{
	if (previousSelected)
	{
		previousSelected.setIcon("http://google.com/mapfiles/ms/micons/red-dot.png");
	}
	marker.setIcon("http://google.com/mapfiles/ms/micons/blue-dot.png");
	previousSelected = marker;
}

function checkIn(waytag, waytagReference)
{
	var resultFunction=function()
	{
	  if (this.readyState==4 && this.status==200)
	  {
		  document.getElementById("debug").innerHTML = this.responseText;
		  infoWindow.setContent(createBusinessInfoContent(waytag));
		  getCheckinHistory();
	  }
	};
	ajaxCall("waytags.php", {"function":"checkIn", "waytagId" : waytag, "waytagReference" : waytagReference}, resultFunction);	
}

function checkOut(waytag)
{
	var resultFunction=function()
	{
	  if (this.readyState==4 && this.status==200)
	  {
		  document.getElementById("debug").innerHTML = this.responseText;
		  infoWindow.setContent(createBusinessInfoContent(waytag));
		  getCheckinHistory();
	  }
	};
	ajaxCall("waytags.php", {"function":"checkOut", "waytagId" : waytag}, resultFunction);
}

function onMarkerClick(marker)
{
	highlightMarker(marker);
	infoWindow.setContent(createBusinessInfoContent(marker.get("waytagId")));
	infoWindow.open(map, marker);
}

function getCheckinHistory()
{
	resultFunction = function()
	{
		  if (this.readyState==4 && this.status==200)
		  {
			  document.getElementById("checkinHistory").innerHTML = this.responseText;
		  }
	};
	ajaxCall("waytags.php", {"function":"getCheckinHistory"}, resultFunction);
}

function ajaxCall(url, parameters, resultFunction)
{
	var xmlhttp;
	if (window.XMLHttpRequest)
	{// code for IE7+, Firefox, Chrome, Opera, Safari
	  xmlhttp=new XMLHttpRequest();
	}
	else
	{// code for IE6, IE5
	  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	xmlhttp.onreadystatechange=resultFunction;
	xmlhttp.open("POST",url,true);
	xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	parametersUrl = "";
	count = 0;
	for (i in parameters)
	{
		if (count > 0)
			parametersUrl += "&";
		parametersUrl += i + "=" +parameters[i];
		count++;
	}
	xmlhttp.send(parametersUrl);
}

function panMapToWayTag(wayTagID)
{
	var markerlatlon = new google.maps.LatLng(businesses[wayTagID]["dWayTagLatitude"], businesses[wayTagID]["dWayTagLongitude"]);
	map.panTo(markerlatlon);
}

getCheckinHistory();
displayBusinesses();
</script>
</body>
</html>
