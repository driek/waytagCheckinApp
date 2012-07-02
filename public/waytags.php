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
		$latitude = $_POST["latitude"];
		$longitude = $_POST["longitude"];
		$username = $_SESSION["username"];
		$password = $_SESSION["password"];
		Waytag::updateMobileLocation($username, $password, $latitude, $longitude);
		break;
	case "getClosestBusinesses":
		$username = $_SESSION["username"];
		$password = $_SESSION["password"];
		$businesses = Waytag::getMyClosestBusinessWaytags($username, $password);
		header("Content-type: application/json");
		echo "{";
		$i = 0;
		foreach ($businesses as $business)
		{
			$j = 0;
			echo (($i>0)?",":"") . '"'.$business["dWayTagObj"].'" : ' . "{";
			foreach ($business as $attribute => $value)
			{
				echo (($j>0)?",":"");
				echo "\"$attribute\" : \"$value\"";
				$j++;
			}
			echo "}";
			$i++;
		}
		echo "}";
		break;
}
?>