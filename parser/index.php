#!/usr/bin/php
<?php

/**
 * @file
 * wip - parses and transforms a csv file - needs cleaning up
 */

$time_pre = microtime(TRUE);

$parserFilesPath = "./parser_test/";
$directory = new RecursiveDirectoryIterator($parserFilesPath);
$iterator = new RecursiveIteratorIterator($directory);

$files = [];
foreach ($iterator as $info) {
  if (substr($info->getPathname(), -1) !== "."
  && substr($info->getPathname(), -9) !== ".DS_Store"
  && substr($info->getPathname(), -4) !== ".ini"
  && substr($info->getPathname(), -4) !== ".csv") {
    $files[] = $info->getPathname();
  }
}
echo "Getting App Codes...\n";

// Lets assume app codes always has this path.
$appCodesPath = $parserFilesPath . "appCodes.ini";
// Lets store app codes in an array.
$appCodesArray = file($appCodesPath);

// Ok I think we need to parse this to make like an associative array e.g.
// x ->  "efs-test-app-net = "EFS Test app .net"
// becomes
// efs-test-app-net -> "EFS Test app .net"
// 1. removing the first item in the array.
array_shift($appCodesArray);

$assocAppCodesArray = [];
foreach ($appCodesArray as $value) {
  // We need to get the appcode before the "=".
  preg_match("/[A-Za-z0-9 -]+ =/", $value, $matchesequals);
  if (count($matchesequals)) {
    $appCode = $matchesequals[0];
    $appCode = trim($appCode, " =");
  }

  // We also need the first match the string between the "'s.
  preg_match("/\".+?\"/", $value, $matchesquotes);
  if (count($matchesquotes)) {
    $appTitle = $matchesquotes[0];
    $appTitle = trim($appTitle, "\"");
  }

  // Echo $appCode, $appTitle;.
  if ($appCode && $appTitle) {
    $assocAppCodesArray[$appCode] = $appTitle;
  }

}

// not sure how to match other tags - string replacement?
echo "recursing directory to get list/array of files...\n";

echo "starting load contents \n";
define("NEWHEADER", "id, appCode, deviceId, contactable, subscription_status"
          . ", has_downloaded_free_product_status, has_downloaded_iap_product_status , left_over_tags \n");

define("NEWHEADERARRAY", ["id", "appCode", "deviceId", "contactable",
  "subscription_status", "has_downloaded_free_product_status",
  "has_downloaded_iap_product_status", "left_over_tags"
]);

// For each file we want to get the filepath and use this to create
// a new file path for the csv file for the transformed data.
foreach ($files as $file) {
  // (A) OPEN FILE
  $handle = fopen($file, "r") or die("Error reading file!");

  $FileContentArray = fgetcsv($handle);

  // Get header.
  $count = 0;
  $indexedcount = 1;

  while (!feof($handle)) {
    // (B) READ LINE BY LINE
    $line = fgetcsv($handle);

    if (is_array($line)) {

      // Lets always handle the first line differently.
      if ($count === 0) {
        echo "path to file to be transformed: " . $file . " \n";

        // NEW FILE PATH.
        $new_path = substr_replace($file, "csv", -3, 3);
        echo "new csv file path: " . $new_path . " \n";

        // We now have handle to save new file with.
        echo "HEADER - to be substituted \n";
        echo "HEADER - new \n";
        echo NEWHEADER . "\n";
        echo "CONTENT \n";
        // Replace header
        // if file already exists - replace.
        if (file_exists($new_path)) {
          $newHandle = fopen($new_path, 'w');
          fputcsv($newHandle, NEWHEADERARRAY);
          fclose($newHandle);
        }
        else {
          // If file does not exist create it with BDM - excel requirement?
          $newHandle = fopen($new_path, 'w');
          // NEW LINE.
          fwrite($newHandle, $BOM);
          fputcsv($newHandle, NEWHEADERARRAY);
          fclose($newHandle);
        }
        // Make sure we increment so this code only runs once per file.
        $count++;
      }
      else {

        // AppID - Lookup assocAppCodesArray - First field
        $key = array_search($line[0], $assocAppCodesArray);
        $line[0] = $key;

        // contactable / field 3
        $line[2] = (int) $line[2];
        // lets  iterate though and extract all values  that contain status

        // Status - field 4
        $status_match = "/[A-Za-z_]+subscri+[A-Za-z]+/";
        preg_match_all($status_match, $line[3], $match_status_tags);
        $length_status_tags = count($match_status_tags[0]);
        // lets append a field for now as we need to keep $line[3] for now
        // lets assume that only one of these can be set i.e. the are mutally
        // exclusive
        if ($length_status_tags === 1) {
          $tag = $match_status_tags[0][0];
          $line[4] = $tag;
          // clean up
          $temp = str_replace($tag,"",$line[3]);
          $line[3] = $temp;
        } else {
          $line[4] = "subscription_unknown";
        }

        // Status - field 5 has_downloaded_free_product_status
        $has_downloaded_free_product_status_tags = "/[A-Za-z_]+free+[A-Za-z]+/";
        preg_match_all($has_downloaded_free_product_status_tags, $line[3], $free_status_tags);
        $length_free_tags = count($free_status_tags[0]);
        // lets append a field for now as we need to keep $line[3] for now
        // lets assume that only one of these can be set i.e. the are mutally
        // exclusive
        if ($length_free_tags === 1) {
          $tag = $free_status_tags[0][0];
          // set
          $line[5] = $tag;
          // clean up
          $temp = str_replace($tag,"",$line[3]);
          $line[3] = $temp;
        } else {
          $line[5] = "downloaded_free_product_unknown";
        }

        // Status - field 6 has_downloaded_free_product_status
        $has_downloaded_iap_product_status_tags = "/[A-Za-z_]+iap+[A-Za-z]+/";
        preg_match_all($has_downloaded_iap_product_status_tags, $line[3], $iap_status_tags);
        $length_iap_tags = count($iap_status_tags[0]);
        // lets append a field for now as we need to keep $line[3] for now
        // lets assume that only one of these can be set i.e. the are mutally
        // exclusive
        if ($length_iap_tags === 1) {
          $tag = $iap_status_tags[0][0];
          // set
          $line[6] = $tag;
          // clean up
          $temp = str_replace($tag,"",$line[3]);
          $line[3] = $temp;
        } else {
          $line[6] = "downloaded_iap_product_unknown";
        }

        // lets move leftovers to last column
        $line[7] = $line[3];

        //remove last column

        // lets move the 4th $line[3] to end
        array_splice($line, 3, 1);

        // We need an add index last
        array_unshift($line, $indexedcount);

        if (file_exists($new_path)) {
          $newHandle = fopen($new_path, 'a');
          fputcsv($newHandle, $line);
          fclose($newHandle);
        }
        else {
          $newHandle = fopen($new_path, 'w');
          // NEW LINE.
          fwrite($newHandle, $BOM);
          // fwrite($newHandle, $indexedLine);.
          fputcsv($newHandle, $line);
          fclose($newHandle);
        }
        $indexedcount++;
      }

    }

  }
  // (C) CLOSE FILE
  fclose($handle);
}
$time_post = microtime(TRUE);
$exec_time = $time_post - $time_pre;
echo "indexed " . count($files) . " csv files in $exec_time seconds \n";
