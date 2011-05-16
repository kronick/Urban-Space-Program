<?
require_once("settings.php");

if(!isset($_FILES["facade_image"]) || !isset($_POST["lat"]) ||
   !isset($_POST["lng"]) || !isset($_POST["alt"]) || !isset($_POST["scale"])) {
  ?>
  <html>
  <body>
  <form enctype="multipart/form-data" action="new-facade.php" method="POST">
    <label for="file">Filename:</label>
    <input type="file" name="facade_image" id="file" />
    <br />
    <label for="lat">Latitude:</label>
    <input type="text" name="lat" id="lat" />
    <br />
    <label for="lng">Longitude:</label>
    <input type="text" name="lng" id="lng" />
    <br />
    <label for="alt">Altitude:</label>
    <input type="text" name="alt" id="alt" />
    <br />
    <label for="scale">Scale:</label>
    <input type="text" name="scale" id="scale" />
    <br />
    <input type="submit" name="submit" value="Upload" />
  </form>
  </body>
  </html>
  <?
}
else {
  require_once("db-connect.php");
  require_once("json-response.php");

  $uploadDirectory = $SETTINGS["UPLOAD_DIR"];
  $filename = basename($_FILES["facade_image"]["name"]);
  $uploadDestination = $uploadDirectory . $filename;
  
  $filename = mysql_escape_string($filename);

  $lat    = mysql_escape_string($_POST["lat"]);     
  $lng    = mysql_escape_string($_POST["lng"]);
  $alt    = mysql_escape_string($_POST["alt"]);
  $scale  = mysql_escape_string($_POST["scale"]);

  if(move_uploaded_file($_FILES["facade_image"]["tmp_name"],
                        $uploadDestination)) {
    $query =  "INSERT INTO `facades` SET `user` = 1, `lat` = $lat, " .
              "`lng` = $lng,`alt` = $alt, `scale` = $scale, " .
              "`timetaken` = NOW(), `timeuploaded` = NOW(), " . 
              "`imgurl` = '$uploadDestination', `processed` = 0";
    if(!mysql_query($query)) {
      json_error(500, "Could not insert a record into the database: " .
                mysql_error());
    }
    else {
      json_success(array("response" => "Success"));
    }
  }
  else {
    json_error(500, "Could not copy uploaded file.");
  }
}
?>
