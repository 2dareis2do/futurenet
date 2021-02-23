#!/usr/bin/php
<?php

/**
 * @file
 * Parses and transforms a csv file. Enjoy!
 */

$time_pre = microtime(TRUE);

define("PARSERFILESPATH", "./parser_test/");
define("APPCODESPATH", "appCodes.ini");

/**
 * @class returns @array Files set as a string in PARSERFILESPATH constant
 */
class GetFiles {
  private $directory;
  private $iterator;
  private $files;

  public function __construct() {
    echo "recursing directory to get list/array of files...\n";
    $this->directory = new RecursiveDirectoryIterator(PARSERFILESPATH);
    $this->iterator = new RecursiveIteratorIterator($this->directory);
    $this->iterateFiles();
  }

  public function get() {
    return $this->files;
  }

  private function iterateFiles() {
    foreach ($this->iterator as $info) {
      if (substr($info->getPathname(), -1) !== "."
      && substr($info->getPathname(), -9) !== ".DS_Store"
      && substr($info->getPathname(), -4) !== ".ini"
      && substr($info->getPathname(), -4) !== ".csv") {
        $this->files[] = $info->getPathname();
      }
    }
  }
}

/**
 * @class returns @array AppCodes. Uses both PARSERFILESPATH and APPCODESPATH s
 * constants
 */
class AppCodes {
  private $appCodesPath; //string
  private $appCodesArray; // original array
  private $assocAppCodesArray; // new assoc array

  public function __construct() {
    echo "Getting App Codes...\n";
    $this->appCodesPath = PARSERFILESPATH . APPCODESPATH;
    $this->appCodesArray = file($this->appCodesPath);
    $this->removeHeader();
    $this->transformAssoc();
  }

  /**
   * removes header (first line)
   */
  private function removeHeader() {
    array_shift($this->appCodesArray);
  }

  /**
   * getter returns an assoc array of app codes
   */
  public function get() {
    return $this->assocAppCodesArray;
  }

  /**
   * parses and extracts both key and value to make an asscociative array
   */
  private function transformAssoc() {
    // $items = $this->appCodesArray;
    $this->assocAppCodesArray = [];

    foreach ($this->appCodesArray as $item) {
      // We need to get the appcode before the "=".
      preg_match("/[A-Za-z0-9 -]+ =/", $item, $matchesequals);
      if (count($matchesequals)) {
        $appCode = $matchesequals[0];
        $appCode = trim($appCode, " =");
      }

      // We also need the first match the string between the "'s.
      preg_match("/\".+?\"/", $item, $matchesquotes);
      if (count($matchesquotes)) {
        $appTitle = $matchesquotes[0];
        $appTitle = trim($appTitle, "\"");
      }

      // Add $appCode (key), $appTitle (value)
      if ($appCode && $appTitle) {
        $this->assocAppCodesArray[$appCode] = $appTitle;
      }
    }

  }

}

// define("NEWHEADER", "id, appCode, deviceId, contactable, subscription_status"
//           . ", has_downloaded_free_product_status, has_downloaded_iap_product_status , left_over_tags \n");

define("NEWHEADERARRAY", ["id", "appCode", "deviceId", "contactable",
  "subscription_status", "has_downloaded_free_product_status",
  "has_downloaded_iap_product_status", "left_over_tags",
]);

// For each file we want to get the filepath and use this to create
// a new file path for the csv file for the transformed data.

class TransformCSV {

  private $files;

  private $assocAppCodesArray;

  public function __construct( array $files, array $assocAppCodesArray) {
    echo "Starting transform contents... \n";
    $this->files = $files;
    $this->assocAppCodesArray = $assocAppCodesArray;
    $this->transformFiles();
  }

  private function extractTags(string $regex, array &$line, int $out, string $default) {
    // Lets iterate though and extract all values  that contain status
    $status_match = $regex; // 1. reg exp string
    preg_match_all($status_match, $line[3], $match_status_tags); // 2. line
    $length_status_tags = count($match_status_tags[0]);
    // Lets append a field for now as we need to keep $line[3] for now
    // lets assume that only one of these can be set i.e. the are mutally
    // exclusive.
    if ($length_status_tags === 1) {
      $tag = $match_status_tags[0][0];
      $line[$out] = $tag; // 3. field number to be replaced
      // Clean up.
      $temp = str_replace($tag, "", $line[3]);
      $line[3] = $temp;
    }
    else {
      $line[$out] = $default; //4. string with default value
    }
  }

  private function transformFiles() {

    foreach ($this->files as $file) {
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

            // AppID - Lookup assocAppCodesArray -  field 1
            $key = array_search($line[0], $this->assocAppCodesArray);
            // set
            $line[0] = $key;

            // Contactable / field 3.
            $line[2] = (int) $line[2];

            $status_match = "/[A-Za-z_]+subscri+[A-Za-z]+/";

            $this->extractTags($status_match, $line, 4, "subscription_unknown");

            // Status - field 5 has_downloaded_free_product_status.
            $free_match = "/[A-Za-z_]+free+[A-Za-z]+/";

            $this->extractTags($free_match, $line, 5, "downloaded_free_product_unknown");

            // Status - field 6 has_downloaded_free_product_status.
            $iap_tags = "/[A-Za-z_]+iap+[A-Za-z]+/";

            $this->extractTags($iap_tags, $line, 6, "downloaded_iap_product_unknown");

            // Lets move leftovers to last column.
            $line[7] = $line[3];

            // Remove last column
            // Lets move the 4th $line[3] to end.
            array_splice($line, 3, 1);

            // We need an add index last.
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

  }

}

// recursively get log files
$get_files = new GetFiles();
$files = $get_files->get();
// get an index of and assoc array of app codes
$assocAppCodes = new AppCodes();
$assocAppCodesArray = $assocAppCodes->get();
// start tarnsformation by initiating and passing both files and hash index
$tcsv = new TransformCSV($files, $assocAppCodesArray);

$time_post = microtime(TRUE);
$exec_time = $time_post - $time_pre;
echo "indexed " . count($files) . " csv files in $exec_time seconds \n";
