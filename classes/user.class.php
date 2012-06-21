<?php
require_once("../classes/database.class.php");
class User
{
	private $id, $last_name, $first_name, $waytag_user_name, $nickname;
	
	private function __construct($id, $last_name, $first_name, $waytag_username, $nickname)
	{
		$this->id = $id;
		$this->last_name = $last_name;
		$this->first_name = $first_name;
		$this->waytag_user_name = $waytag_username;
		$this->nickname = $nickname;
	}
	
	public static function getUserByWaytagUsername($waytag_username)
	{
		$con = Database::connect();
		$result = $con->query("SELECT * FROM Users WHERE waytag_user_name LIKE '".$waytag_username."'");
		$user_id = -1;
		if($result && $row = $result->fetch_array())
		{
			$user = new User($row["ID"], $row["last_name"], $row["first_name"], $row["waytag_user_name"], $row["nickname"]);
		}
		else
		{
			$user = User::createUserByWaytagUsername($waytag_username);
		}
		$con->close();
		return $user;
	}
	
	public function getId()
	{
		return $this->id;
	}
	
	public static function createUserByWaytagUsername($waytag_username)
	{
		$con = Database::connect();
		$query = "INSERT INTO Users (waytag_user_name) VALUES ('".$con->real_escape_string($waytag_username)."')";
		$con->query($query);
		$user_id = $con->insert_id;
		$user = new User($user_id, "", "", $waytag_username, "");
		$con->close();
		return $user;
	}
	
	public function checkOut($waytagId = null)
	{
		$con = Database::connect();
		$waytagId = $con->real_escape_string($waytagId);
		$timeZone = new DateTimeZone("Europe/Amsterdam");
		$currentTime = new DateTime(null, $timeZone);
		$time = $currentTime->format("Y-m-d H:i:s");
		$user_id = $_SESSION["user_id"];
		$query = "UPDATE Checkins SET checked_out_at = '$time' WHERE checked_out_at IS NULL AND user_id = $user_id";
		if ($waytagId){ $query .= " AND waytag_id LIKE '$waytagId'";}
		$con->query($query);
	}
	
	public function checkIn($waytagId, $waytagReference)
	{
		$this->checkOut();
		$con = Database::connect();
		$waytagId = $con->real_escape_string($waytagId);
		$waytagReference = $con->real_escape_string($waytagReference);
		$timeZone = new DateTimeZone("Europe/Amsterdam");
		$currentTime = new DateTime(null, $timeZone);
		$time = $currentTime->format("Y-m-d H:i:s");
		$user_id = $_SESSION["user_id"];
		$query = "INSERT INTO Checkins (waytag_id, waytag_reference, user_id, checked_in_at) VALUES ('$waytagId', '$waytagReference', $user_id, '$time')";
		$con->query($query);
	}
	
	public function getCheckinHistory()
	{
		$con = Database::connect();
		$query = "SELECT * FROM Checkins WHERE user_id = ".$this->id." ORDER BY checked_in_at DESC";
		$return_result = array();
		$result = $con->query($query);
		while ($result && $row = $result->fetch_array())
		{
			$return_result[] = $row;
		}
		$con->close();
		return $return_result;
	}
	
}
?>