<?php
class Debug
{
	public static function writeDebugInfo($debugInfo)
	{
		$handle = fopen("../logs/debug.log", 'a');
		fwrite($handle, $debugInfo . PHP_EOL);
	}
}
?>