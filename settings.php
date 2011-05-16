<?
$SETTINGS = array();
$SETTINGS["DATABASE_HOST"]					= "localhost";
$SETTINGS["DATABASE_NAME"]					= "framefusion";
$SETTINGS["DATABASE_USER"]					= "framefusion";
$SETTINGS["DATABASE_PASSWORD"]			= "framefusion";

$SETTINGS["API_DIR"]								= "/var/www/";
$SETTINGS["UPLOAD_DIR"]							= $SETTINGS["API_DIR"] . "uploads/";
$SETTINGS["FERN_OUTPUT_DIR"] 				= $SETTINGS["API_DIR"] . "ferns/";
$SETTINGS["FERN_PROCESSOR_COMMAND"]	= $SETTINGS["API_DIR"] . "exec/ferns-processor";
$SETTINGS["PROCESSOR_NICE_LEVEL"]		= "5";
?>
