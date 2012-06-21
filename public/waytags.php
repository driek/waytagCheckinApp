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
}
?>