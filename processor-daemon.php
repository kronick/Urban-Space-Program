#!/usr/bin/php -q
<?
require_once('System/Daemon.php');
$options = array(
    'appName' => 'fernproc',
    'appDir' => dirname(__FILE__),
    'appDescription' => 'Generates Ferns classifiers for images in a database.',
    'authorName' => 'Sam Kronick',
    'authorEmail' => 'sam.kronick@gmail.com',
    'sysMaxExecutionTime' => '0',
    'sysMaxInputTime' => '0',
    'sysMemoryLimit' => '512M',
    'appRunAsGID' => 0,
    'appRunAsUID' => 0);

System_Daemon::setOptions($options);
System_Daemon::start();

if(($initd_location = System_Daemon::writeAutoRun()) === false) {
  System_Daemon::notice('Unable to write init.d script');
}
else {
  System_Daemon::info("Successfully written startup script: %s",
                      $initd_location);
}


// Actual Daemon begins here
require_once("settings.php");
require_once("db-connect.php");
while(true) {
  // Get facade entries from the DB that have not been processed, FIFO order
  $query = "SELECT * FROM `facades` WHERE `processed` = 0 ORDER BY " .
            "`timeuploaded` ASC";
  $result = mysql_query($query);

  // Go through results and run the ferns classifier program on them
  while($facade = mysql_fetch_assoc($result)) {
    System_Daemon::info("Processing \"{$facade['imgurl']}\"");
    $returnedValue = -1;
    $returnedLines = array();
    $fernLocation = exec("nice -n " . $SETTINGS["PROCESSOR_NICE_LEVEL"] . " " .
                          $SETTINGS["FERNS_PROCESSOR_COMMAND"] . "  " .
                          $facade['imgurl'] . " " .
                          $SETTINGS["FERN_OUTPUT_DIR"] .
                          " -warps " . $SETTINGS["FERN_WARPS"] .
                          " -features " . $SETTINGS["FERN_FEATURES"], 
                          $returnedLines,
                          $returnedValue);
    $fernLocation = mysql_escape_string($fernLocation);

    if($returnedValue == 0) {
      $update = "UPDATE `facades` SET `processed` = 1, " .
                "`fernurl` = '$fernLocation' " .
                "WHERE `id` = {$facade['id']} LIMIT 1";
      if(!mysql_query($update))
        System_Daemon::notice("Could not update database: " . mysql_error());

    }
    else {
      System_Daemon::notice("Could not process fern: " .
                            implode("\n", $returnedLines));
    }
  }
  
  // Wait 5 seconds before checking again
  sleep(5);
}
System_Daemon::info("Running some stuff now in a daemon.");

System_Daemon::stop();
?>
