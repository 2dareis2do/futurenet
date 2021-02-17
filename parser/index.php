#!/usr/bin/php
<?php

$time_pre = microtime(true);

$parserFilesPath = "./parser_test/";
$directory = new RecursiveDirectoryIterator($parserFilesPath);
$iterator = new RecursiveIteratorIterator($directory);

$files = array();
foreach ($iterator as $info) {
  if ( substr($info->getPathname(), -1) !== "."
  && substr($info->getPathname(), -9) !== ".DS_Store"
  && substr($info->getPathname(), -4) !== ".ini"
  && substr($info->getPathname(), -4) !== ".csv") {
    $files[] = $info->getPathname();
  }
}
echo "Getting App Codes...\n";


// lets assume app codes always has this path
$appCodesPath = $parserFilesPath."appCodes.ini";
// lets store app codes in an array
$appCodesArray = file($appCodesPath);

// ok I think we need to parse this to make like an associative array e.g.
// x ->  "efs-test-app-net = "EFS Test app .net"
// becomes
// efs-test-app-net -> "EFS Test app .net"
// 1. removing the first item in the array
array_shift($appCodesArray);
// $length = count($appCodesArray);
// $slicedAppCodesArray = array_slice($appCodesArray, 1, null, false);
// var_dump($test[0]);
// die();

$assocAppCodesArray = [];
foreach($appCodesArray as $value) {
  // echo $value;
  // we need to get the appcode before the "="
  preg_match("/[A-Za-z0-9 -]+ =/", $value, $matchesequals);
  if (count($matchesequals)) {
    $appCode = $matchesequals[0];
    $appCode = trim($appCode, " =");
  }

  // we also need the first match the string between the "'s
  preg_match("/\".+?\"/", $value, $matchesquotes);
  if (count($matchesquotes)) {
    $appTitle = $matchesquotes[0];
    $appTitle = trim($appTitle,"\"");
  }

  // echo $appCode, $appTitle;

  if ($appCode && $appTitle) {
    $assocAppCodesArray[$appCode] = $appTitle;
  }


}

var_dump($assocAppCodesArray);

die();

echo "recursing directory to get list/array of files...\n";

echo "starting load contents \n";
define("NEWHEADER","id, appCode, deviceId, contactable, subscription_status"
          .", has_downloaded_free_product_status has_downloaded_iap_product_status \n");

// for each file we want to get the filepath and use this to create
// a new file path for the csv file for the transformed data
foreach ($files as $file) {
// (A) OPEN FILE
  $handle = fopen($file, "r") or die("Error reading file!");
// get header
  $count = 0;
  $indexedcount = 1;

  while (! feof($handle)) {
    // (B) READ LINE BY LINE
    $line = fgets($handle);

    // lets always handle the first line differently
    if ($count === 0) {
      echo "path to file to be transformed: " . $file . " \n";

      // NEW FILE PATH
      $new_path = substr_replace($file, "csv", -3, 3);
      echo "new csv file path: " . $new_path . " \n";

      // we now have handle to save new file with.
      echo "HEADER - to be substituted \n";
      echo  $line;
      echo NEWHEADER . "\n";
      echo "CONTENT \n";
      // replace header
      // if file already exists - replace
      if(file_exists($new_path)) {
          $newHandle = fopen($new_path, 'w');
          fwrite($newHandle, NEWHEADER);
          fclose($newHandle);
      } else {
        // if file does not exist create it with BDM - excel requirement?
          $newHandle = fopen($new_path, 'w');
          fwrite($newHandle, $BOM); // NEW LINE
          fwrite($newHandle, NEWHEADER);
          fclose($newHandle);
      }
      // make sure we increment so this code only runs once per file
      $count++;
    }
    else {
    // everything else apart from first line

    // we need an add index only when content of line are not blank
        if($line) {
          $indexedLine = $indexedcount . ", " . $line;
        } else {
          $indexedLine = $line;
        }
        // echo $indexedLine;
        if(file_exists($new_path)) {
          $newHandle = fopen($new_path, 'a');

          fwrite($newHandle, $indexedLine );
          fclose($newHandle);
        }
        else {
            $newHandle = fopen($new_path, 'w');
            fwrite($newHandle, $BOM); // NEW LINE
            fwrite($newHandle, $indexedLine);
            fclose($newHandle);

        }
        $indexedcount++;
    }

  }
  // (C) CLOSE FILE
  fclose($handle);
}
$time_post = microtime(true);
$exec_time = $time_post - $time_pre;
echo "indexed ". count($files) . " csv files in $exec_time seconds \n";

?>
