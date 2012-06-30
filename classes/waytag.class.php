<?php
require_once("../classes/database.class.php");
require_once("../classes/debug.class.php");
class Waytag
{
	public static function getMyWaytags($username, $password)
	{
		return Waytag::makeRequest($username, $password, "GetMyWaytagsSQ");
	}
	
	public static function getMyMobileWaytag($username, $password)
	{
		// cResultLog = NO_MOBILE_WAYTAG_FOUND
		return array_pop(Waytag::makeRequest($username, $password, "GetMyMobileWaytags"));
	}
	
	public static function validUser($username, $password)
	{
		include("../config/config.inc.php");
		$querystring = "https://devzone.waytag.com/cgi-bin/wspd_cgi.sh/WService=wsb_wtdev/rest.w?rqDataMode=VAR/XML&rqAuthentication=user:".$username."|".$password."&rqversion=1&rqappkey=".$config["waytag_app_key"]."|".$config["waytag_app_password"]."&rqservice=wtutility:GetMyWaytagsSQ";
		$xml = new XMLReader;
		$xml->open($querystring);
		$attribute = $value = "";
		while ($xml->read())
		{
			if ($xml->nodeType == XMLReader::ELEMENT)
			{
				$value = "";
				$attribute = $xml->name;
			}
			else if ($xml->nodeType == XMLReader::TEXT)
			{
				$value = $xml->value;
			}
			else if ($xml->nodeType == XMLReader::END_ELEMENT)
			{
				if ($attribute == "rqErrorMessage" && strlen(trim($value)) == 0)
				{
					return true;
				}
			}
		}
		return false;
	}
	
	public static function getClosestBusinessWaytags($username, $password, $latitude, $longitude, $distance = 10)
	{
		$parameters["ipdSearchRange"] = $distance;
		$parameters["ipdRefLatitude"] = $latitude;
		$parameters["ipdRefLongitude"] = $longitude;
		$parameters["ipiNumberRequired"] = 10;
		return Waytag::makeRequest($username, $password, "FindWaytagsByProximitySrtSQ", $parameters);
	}
	
	public static function makeRequest($username, $password, $service, $parameters = array())
	{
		include("../config/config.inc.php");
		$xml = new XMLReader();
		$login = "rqAuthentication=user:$username|$password";
		if (isset($_SESSION["waytag_session_id"])){$login = "rqAuthentication=".$_SESSION["waytag_session_id"];}
		$queryString = "https://devzone.waytag.com/cgi-bin/wspd_cgi.sh/WService=wsb_wtdev/rest.w?rqDataMode=VAR/XML&$login&rqversion=1&rqappkey=".$config["waytag_app_key"]."|".$config["waytag_app_password"]."&rqservice=wtutility:".$service;
		foreach ($parameters as $key => $parameter)
		{
			$queryString .= "&" . urlencode($key) . "=" . urlencode($parameter);
		}
		$xml->open($queryString);
		$text = "";
		$types = array();
		$attributes = array();
		$wayTags = array();
		$attribute = "";
		$waytagIDs = array();
		while ($xml->read()) 
		{
			if ($xml->nodeType == XMLReader::END_ELEMENT && $xml->name == "ttResponseRow")
			{
				$wayTags[$attributes["dWayTagObj"]] = $attributes;
			}
			else if ($xml->nodeType == XMLReader::ELEMENT && $xml->name == "ttResponseRow")
			{
				$attributes = array();
			}
			else if ($xml->nodeType == XMLReader::ELEMENT)
			{
				$attribute = $xml->name;
			}
			if ($xml->nodeType == XMLReader::TEXT)
			{
				if ($attribute == "rqAuthentication")
				{
					$_SESSION["waytag_session_id"] = $xml->value;
				}
				else if($attribute == "rqErrorMessage")
				{
					if (strpos($xml->value, "mip_StMitseExp") !== false || strpos($xml->value, "mip_StMitseStale") )
					{
						unset($_SESSION["waytag_session_id"]);
						$wayTags = Waytag::makeRequest($username, $password, $service, $parameters);
						break;
					}
				}
				$attributes[$attribute] = $xml->value;
			}
		}
		return $wayTags;
	}
	
	public static function getPeopleCheckedInAt($waytagId)
	{
		$con = Database::connect();
		$return_result = array(-1, -1);
		if (is_numeric($waytagId))
		{
			$query = "SELECT count(user_id) AS no_of_checkins, SUM(user_id=".$_SESSION["user_id"].") FROM Checkins WHERE waytag_id LIKE '".$con->real_escape_string($waytagId)."' AND checked_out_at IS NULL";
			$result = $con->query($query);
			if($result && $row = $result->fetch_array())
			{
				$return_result[0] = $row[0];
				$return_result[1] = $row[1];
			}
		}
		$con->close();
		return $return_result;
	}
	
}
?>