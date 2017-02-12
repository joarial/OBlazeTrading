<?php
/*
    Function Name: date_validate
  
    Author: Eric Sammons, Vansam Software, Inc. (www.vansam.com)
    Email: eric@vansam.com

    Date: 2001-09-01
    Version 1.0.0

    Purpose:
        Receives various date field formats, validates them, and then and 
        converts them to MySQL standard date format

    Valid date fields:
        mm-dd-yyyy, mm/dd/yyyy, yyyy-mm-dd, yyyy/mm/dd

    Returns:
        if valid input, the date in MySQL standard format
        if invalid input, error message with "Error:" at the beginning of message

    Sample Use:
        $MySQLDate=date_validate($datefield);
        if (substr($MySQLDate, 0, 5)=="Error") {
            // Insert Error Code
        } else {
            // Insert Valid Date Code
        }
*/

function date_validate ($datefield) {

    // First check to see if the input ($datefield) is in one of the accepted formats
    
    // Check for delimiters ("-" or "/") and put three fields into an array
    if (strpos($datefield, "-")) {
      $datesplit = explode("-", $datefield);
    } elseif (strpos($datefield, "/")) {
      $datesplit = explode("/", $datefield);
    } else {
        $date_err="Error: Invalid date field. No proper delimiters (- or /) found";
        return $date_err;
    }

    // Check for three input fields (month, day, year)
    if (count($datesplit)>3) {
        $date_err="Error: Invalid date field. Too many fields (".count($datesplit).") found";
        return $date_err;
    }

    // Put date array into single format
    if (strlen($datesplit[2])==4) { // The year is listed last - switch fields around
        $newdatesplit[0]=$datesplit[2]; // Move Year to first field
        $newdatesplit[1]=$datesplit[0]; // Move Month to second field
        $newdatesplit[2]=$datesplit[1]; // Move Day to third field
        $datesplit=$newdatesplit;
    } elseif (strlen($datesplit[0])==4) { // The year is first listed - do nothing
        // nothing to be done
    } else { // Date entered is not valid; could not find year field
        $date_err="Error: Date not valid. No Year field found (Year must be 4 digits)";
        return $date_err;
    }
    
    // Main validation code

    if ($datesplit[1]>12) { // No valid month field
        $date_err="Error: Invalid Month field (".$datesplit[1].") ";
        return $date_err;
    } else {
       switch ($datesplit[1]) { // Check number of days in a month
           case 4:
           case 6:
           case 9:
           case 11:
                if ($datesplit[2]>30) {
                    $date_err="Error: Invalid # of days (".$datesplit[2].") for month ".$datesplit[1]." and year ".$datesplit[0];
                    return $date_err;
                }
                break;
           case 2: // February Check
                   if (($datesplit[0]/4)==(floor($datesplit[0]/4))) {
                    if (($datesplit[0]/100)==(floor($datesplit[0]/100))) {
                        if (($datesplit[0]==1600) or ($datesplit[0]==2000) or ($datesplit[0]==2400)) {
                            if ($datesplit[2]>29) {
                                $date_err="Error: Invalid # of days (".$datesplit[2].") for month ".$datesplit[1]." and year ".$datesplit[0];
                                return $date_err;
                            }
                        } else {
                            if ($datesplit[2]>28) {
                                $date_err="Error: Invalid # of days (".$datesplit[2].") for month ".$datesplit[1]." and year ".$datesplit[0];
                                return $date_err;
                            }
                        }
                    } else {
                        if ($datesplit[2]>29) {
                            $date_err="Error: Invalid # of days (".$datesplit[2].") for month ".$datesplit[1]." and year ".$datesplit[0];
                            return $date_err;
                        }
                    }
                } else {
                    if ($datesplit[2]>28) {
                        $date_err="Error: Invalid # of days (".$datesplit[2].") for month ".$datesplit[1]." and year ".$datesplit[0];
                        return $date_err;
                    }
                }
                break;
           default:
                if ($datesplit[2]>31) {
                    $date_err="Error: Invalid # of days (".$datesplit[2].") for month ".$datesplit[1]." and year ".$datesplit[0];
                    return $date_err;
                }
        }
      }
          // Add leading zero if month or day field is only one character
      if (strlen($datesplit[1])==1) {
          $datesplit[1]="0".$datesplit[1];
      }
      if (strlen($datesplit[2])==1) {
          $datesplit[2]="0".$datesplit[2];
      }
      
      // Create date field in MySQL format
      $newdate=$datesplit[0]."-".$datesplit[1]."-".$datesplit[2];
      return $newdate;    
      
} // End date_validate function 
?>