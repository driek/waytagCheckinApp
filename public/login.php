<?php
include("../classes/waytag.class.php");
include("../classes/user.class.php");
session_start();
$errors = "";
if (isset($_POST["username"]) && isset($_POST["password"]) )
{
	$username = $_POST["username"];
	$password = $_POST["password"];
	$result = Waytag::validUser($username, $password);
	if ($result)
	{
		$errors = "Valid user";
		$_SESSION["username"] = $username;
		$_SESSION["password"] = $password;
		$_SESSION["authorized"] = true;
		$user = User::getUserByWaytagUsername($username);
		$_SESSION["user_id"] = $user->getId();
		$_SESSION["user"] = $user;
		header('Location: index.php');
	}
	else
	{
		session_destroy();
		$errors = "Invalid user";
	}
}
?>
<html>
<head>
<meta charset="UTF-8">
<title>WayTag</title>
<script src="http://maps.google.com/maps/api/js?sensor=false"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
</head>
<body>
<div style="color: red;" id="warning"><?=$errors?></div>
<form action="login.php" method="post">
Username:<br />
<input type="text" name="username" /><br />
Password:<br />
<input type="password" name="password" /><br />
<input type="submit" value="Login" />
</form>
</body>
</html>