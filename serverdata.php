<?php
 // CODE COURTESY OF /tg/station's WEBSITE
// THANKS FOR OPEN SOURCING EVERYTHING
// THIS ONE IS SLIGHTLY MODIFIED SINCE WE ONLY HAVE ONE SERVER,
// AND DO THINGS ON REFRESH INSTEAD OF SERVER JOBS.
$error = false;

$server_info = array();
$server_info["name"] = "Spacepunch Station";
//$server_info["addr"] = "spacepunch.org";
//Uncomment when on local game machine
$server_info["addr"] = "localhost";
$server_info["port"] = 1337;

function export($addr, $port, $str)
{
  global $error;
  // All queries must begin with a question mark (ie "?players")
  if ($str {
    0} != '?') $str = ('?' . $str);

  /* --- Prepare a packet to send to the server (based on a reverse-engineered packet structure) --- */
  $query = "\x00\x83" . pack('n', strlen($str) + 6) . "\x00\x00\x00\x00\x00" . $str . "\x00";

  /* --- Create a socket and connect it to the server --- */
  $server = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or exit("ERROR");
  stream_set_timeout($server, 2);
  socket_set_option($server, SOL_SOCKET, SO_RCVTIMEO, array('sec' => 2, 'usec' => 0));
  socket_set_option($server, SOL_SOCKET, SO_SNDTIMEO, array('sec' => 2, 'usec' => 0));
  if (!socket_connect($server, $addr, $port)) {
    $error = true;
    return "ERROR";
  }


  /* --- Send bytes to the server. Loop until all bytes have been sent --- */
  $bytestosend = strlen($query);
  $bytessent = 0;
  while ($bytessent < $bytestosend) {
    //echo $bytessent.'<br>';
    $result = socket_write($server, substr($query, $bytessent), $bytestosend - $bytessent);
    //echo 'Sent '.$result.' bytes<br>';
    if ($result === false) die(socket_strerror(socket_last_error()));
    $bytessent += $result;
  }

  /* --- Idle for a while until recieved bytes from game server --- */
  $result = socket_read($server, 10000, PHP_BINARY_READ);
  socket_close($server); // we don't need this anymore

  if ($result != "") {
    if ($result {
      0} == "\x00" || $result {
      1} == "\x83") { // make sure it's the right packet format

      // Actually begin reading the output:
      $sizebytes = unpack('n', $result {
        2} . $result {
        3}, 0); // array size of the type identifier and content
      $size = $sizebytes[1] - 1; // size of the string/floating-point (minus the size of the identifier byte)

      if ($result {
        4} == "\x2a") { // 4-byte big-endian floating-point
        $unpackint = unpack('f', $result {
          5} . $result {
          6} . $result {
          7} . $result {
          8}, 0); // 4 possible bytes: add them up together, unpack them as a floating-point
        return $unpackint[1];
      } else if ($result {
        4} == "\x06") { // ASCII string
        $unpackstr = ""; // result string
        $index = 5; // string index

        while ($size > 0) { // loop through the entire ASCII string
          $size--;
          $unpackstr .= $result {
            $index}; // add the string position to return string
          $index++;
        }
        return $unpackstr;
      }
    }
  }
  //if we get to this point, something went wrong;
  $error = true;
  return "ERROR";
}

$port = $server_info["port"];
$addr = $server_info["addr"];

$lastinfo = (is_array($cache) && count($cache) > $n ? $cache[$n] : array());
$n++;

$data = export($addr, $port, '?status');

if (is_string($data)) {
  //remove pesky null-terminating bytes
  $data = str_replace("\x00", "", $data);
}

$variable_value_array = array();

if ((!$data || strpos($data, "ERROR") !== false) && (array_key_exists("restarting", $lastinfo))) {
  $variable_value_array['restarting'] = $lastinfo['restarting'] + 1;
}

// Split the retrieved data into easily-accessible arrays
$data_array = explode("&", $data);

for ($i = 0; $i < count($data_array); $i++) {

  //Split the row by the = sign into the identifier at index 0 and the value at index 1 (if the value exists)
  $row = explode("=", $data_array[$i]);

  if (isset($row[1])) {
    //All should go here... but just in case.
    $variable_value_array[$row[0]] = $row[1];
  } else {
    $variable_value_array[$row[0]] = null;
  }
}

$variable_value_array['cachetime'] = time();

if (array_key_exists('gamestate', $variable_value_array)) {
  if ($variable_value_array['gamestate'] == 4) {
    $variable_value_array['restarting'] = 1;
  }
}
