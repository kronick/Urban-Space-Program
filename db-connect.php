<?
require_once("settings.php");
require_once("json-response.php");

$databaseConnection = mysql_connect($SETTINGS["DATABASE_HOST"],
                                    $SETTINGS["DATABASE_USER"],
                                    $SETTINGS["DATABASE_PASSWORD"]);

if(!$databaseConnection || !mysql_select_db($SETTINGS["DATABASE_NAME"])) {
  json_error(503, "Could not connect to database.");
}
?>
