<?php 
// This file is called every 2 seconds by the front end
// It simply formats the raw information from the ping, 
// and returns it in JSON for the frontend to use.

include "serverdata.php";

global $variable_value_array;

$serverdata = $variable_value_array;

//Easy stuff
$round_time = formatTime($serverdata["round_duration"], true);
$players = $serverdata["players"];
$map = str_replace("+", "", $serverdata["map_name"]);
$map = str_replace("Station", "", $map);

//VERY THOROUGH
switch ($serverdata["gamestate"]) {
  case null:
    $gamestate = "Loading...";
    $round_time = "--:--";
    $shuttle_mode = "--";
    $shuttle_time = "--:--";
    $map = "--";
    $players = "--";
    break;
  case 0:
  case 1:
    $gamestate = "LOBBY";
    $gamestate_color = "blue";
    $shuttle_mode = "IDLE";
    $shuttle_time = "--:--";
    $shuttle_color = "blue";
    $round_time = 60 - $serverdata["round_duration"];
    break;
  case 2:
    $gamestate = "LOBBY";
    $gamestate_color = "blue";
    $shuttle_mode = "IDLE";
    $shuttle_time = "--:--";
    $shuttle_color = "blue";
    $round_time = "SOON";
    break;
  case 3:
    $gamestate = "PLAYING";
    $gamestate_color = "green";

    //SHUTTLE STUFF
    if ($serverdata["shuttle_mode"] == "idle") {
      $shuttle_mode = "IDLE";
      $shuttle_time = "--:--";
      $shuttle_color = "blue";
    } else if ($serverdata["shuttle_mode"] == "call") {
      $shuttle_mode = "CALLED";
      $shuttle_time = formatTime($serverdata["shuttle_timer"], false);
      $shuttle_color = "red";
    } else if ($serverdata["shuttle_mode"] == "docked") {
      $shuttle_mode = "DOCKED";
      $shuttle_time = formatTime($serverdata["shuttle_timer"], false);
      $shuttle_color = "red";
    } else if ($serverdata["shuttle_mode"] == "igniting") {
      $shuttle_mode = "IGNITION";
      $shuttle_time = formatTime($serverdata["shuttle_timer"], false);
      $shuttle_color = "green";
    } else if ($serverdata["shuttle_mode"] == "escape") {
      $shuttle_mode = "ESCAPING";
      $shuttle_time = formatTime($serverdata["shuttle_timer"], false);
      $shuttle_color = "green";
    } else if ($serverdata["shuttle_mode"] == "recall") {
      $shuttle_mode = "RECALLED";
      $shuttle_time = formatTime($serverdata["shuttle_timer"], false);
      $shuttle_color = "blue";
    } else if ($serverdata["shuttle_mode"] == "stranded") {
      $shuttle_mode = "ERROR";
      $shuttle_time = "--:--";
      $shuttle_color = "red";
    }
    break;
  case 4:
    $gamestate = "END";
    $gamestate_color = "red";
    $shuttle_mode = "CENTCOM";
    $shuttle_time = "--:--";
    $shuttle_color = "red";
    break;
}

//Build the "json" variable with arrays
$output["gamestate"] = $gamestate;
$output["gamestateColor"] = $gamestate_color;
$output["shuttle"] = $shuttle_mode;
$output["shuttleColor"] = $shuttle_color;

$output["roundTime"] = $round_time;
$output["shuttleTime"] = $shuttle_time;

$output["players"] = $players;
$output["map"] = $map;

//$output["raw"] = $variable_value_array;

//This file is AJAX'd so echo out some text
//Fun fact: there's no method check, so go to 
//https://spacepunch.org/serverdataformat.php
//for fun info
echo json_encode($output);

//Format time function
function formatTime($seconds_input, $show_hour)
{
  $hours = 0;
  $minutes = intdiv($seconds_input, 60);
  $seconds = $seconds_input % 60;

  if ($seconds <= 9) {
    $seconds = 0 . $seconds;
  }
  if ($minutes > 59) {
    $hours = intdiv($minutes, 60);
    $minutes = $minutes % 60;
  }
  if ($minutes <= 9) {
    $minutes = 0 . $minutes;
  }

  if ($show_hour) {
    return $hours . ":" . $minutes . ":" . $seconds;
  } else {
    return $minutes . ":" . $seconds;
  }
}
