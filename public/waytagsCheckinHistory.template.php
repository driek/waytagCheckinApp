<fieldset>
<legend>Checkin history</legend>
<ul>
<?php 
foreach ($history as $history_entry)
{
	include("waytagCheckinHistory.template.php");
}
?>
</ul>
</fieldset>