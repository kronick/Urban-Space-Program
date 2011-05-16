<?
function json_error($statusCode, $reason, $sendHeaders = TRUE) {
  if($sendHeaders)
    header("Content-type: application/json");
  $response = array("status" => $statusCode, "reason" => $reason);
  echo json_encode($response);
  die();
}

function json_success($content, $sendHeaders = TRUE) {
  if($sendHeaders)
    header("Content-type: application/json");
  $content["status"] = 200;
  echo json_encode($content);
}
?>
