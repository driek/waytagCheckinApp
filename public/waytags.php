<?php
require_once("../classes/waytag.class.php");
require_once("../classes/user.class.php");
session_start();

$method = $_POST["function"];
switch ($method)
{
	case "getPeopleCheckedInAt": 
		$waytagId = $_POST["waytagId"];
		echo(implode(";", Waytag::getPeopleCheckedInAt($waytagId)));
		break;
	case "checkIn":
		$waytagId = $_POST["waytagId"];
		$waytagReference = $_POST["waytagReference"];
		$user = $_SESSION["user"];
		$user->checkIn($waytagId, $waytagReference);
		break;
	case "checkOut":
		$waytagId = $_POST["waytagId"];
		$user = $_SESSION["user"];
		//$user->checkOut($waytagId);
		$user->checkOut();
		break;
	case "getCheckinHistory":
		$user = $_SESSION["user"];
		$history = $user->getCheckinHistory();
		include("waytagsCheckinHistory.template.php");
		break;
	case "updateMyMobileWaytag":
		$username = $_SESSION["username"];
		$password = $_SESSION["password"];
		$latitude = $_POST["latitude"];
		$longitude = $_POST["longitude"];
		$mobileWaytag = Waytag::getMyMobileWaytag($username, $password);
		$parameters = array();
		$parameters["ttWayTag"] = "<ttWayTag><ttWayTagRow><dWayTagObj>".$mobileWaytag["dWayTagObj"]."</dWayTagObj><cCustomReference>null</cCustomReference><dSubscriberObj>?</dSubscriberObj><cCountryKey>null</cCountryKey><cTypeAcronymKey>null</cTypeAcronymKey><cBusinessCategoryKey>null</cBusinessCategoryKey><cStatusKey>null</cStatusKey><cPhyAddressCountryKey>null</cPhyAddressCountryKey><cPosAddressCountryKey>null</cPosAddressCountryKey><dWayTagLatitude>".$latitude."</dWayTagLatitude><dWayTagLongitude>".$longitude."</dWayTagLongitude></ttWayTagRow></ttWayTag>";
		Waytag::makeRequest($username, $password, "UpdateWaytagTT", $parameters);
		break;
}
?>