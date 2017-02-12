<?php
///////////////////////
// Author: Trevin Chow
// Email: t1@mail.com
// Date: February 21, 2000
// 
// Description:
//  Abstracts both the php function calls and the server information to POSTGRES
//  databases.  Utilizes class variables to maintain connection information such 
//  as number of rows, result id of last operation, etc.
//
// Sample Usage:
//  include("include/dblib.php");
//  $db = new phpDB();
//  $db->connect("foobar");
//  $db->exec("SELECT * from TREVIN");
//  while ($db->nextRow()) {
//          $rs = $db->fobject();
//          echo "$rs->description : $rs->color : $rs->price <br>\n";
//  }
//
// Modification History:
//  - v1.04, 07/29/2001, Trevin Chow, t1@mail.com
//
//	  Added Following function(s):
//	  * currRow() - return current row
//
//  - v1.03, 05/18/2001, Lee Pang, wleepang@hotmail.com
//
//    Added the following functions:
//    * moveNext() - same as nextRow(), better syntax for VB/ASP converts like myself.
//    * movePrevious() - like nextRow(), just in the opposite direction.
//    * recordCount() - same as numRows(), better syntax for VB/ASP converts
//    * columnCount() - same as numFields, better syntax
//    * querySafe() - removes "\r" and "\n" and replaces "\'" with "'" in input query
//    * sqlSafe() - replaces "\'" with "\'\'"
//
//    Added more comprehensive error handling:
//    * internal error code $errorCode
//    * in connect()
//    * in errorMsg()
//
//    Modified following functions:
//    * connect() - generates connection string based on available data
//
//    Fixed the following bugs:
//    * Syntax error in numAffected() - if ($this->result = null) ... to if ($this->result == null) ...
///////////////////////

class phpDB {

    // set when connect() is called, defined in set_db_info()
    var $hostName = '';
    var $port = '';
    var $userName = ''; 
    var $password = '';  
    var $databaseName = '';  
    var $connectionID = -1;
    var $row = -1; // a row counter, needed to loop through records in postgres.
    var $result = null; // point to result set.
	var $errorCode = 0; // internal error code

    ////////////////////////////////////////////
    // Core primary connection/database function
    ////////////////////////////////////////////

    // Set appropriate parameters for database connection
    function set_db_info($DataBaseReference){
        switch ($DataBaseReference){
            case "bmeweb":
                $this->hostName = "localhost";
                $this->port = "5432";
                $this->userName = "nobody" ;
                $this->password = ""; 
                $this->databaseName = "someDBname"; 
                break;
            case "test":
                $this->hostName = "";
                $this->port = "";
                $this->userName = "";
                $this->password = "" ; 
                $this->databaseName = "test"; 
                break;
			default:
				// FATAL ERROR - DB REFERENCE UNDEFINED
        }
    }

    // connection function
    function connect($DataBaseReference){
		if (isset($DataBaseReference)) {
			$this->set_db_info($DataBaseReference);
			
			// build connection string based on internal settings.
			$connStr = '';
			($this->hostName != '')		? ($connStr .= "host=" . $this->hostName . " ")			: ($connStr = $connStr);
			($this->port != '')			? ($connStr .= "port=" . $this->port . " ")				: ($connStr = $connStr);
			($this->databaseName != '')	? ($connStr .= "dbname=" . $this->databaseName . " ")	: ($connStr = $connStr);
			($this->userName != '')		? ($connStr .= "user=" . $this->userName . " ")			: ($connStr = $connStr);
			($this->password != '')		? ($connStr .= "password=" . $this->userName . " ")		: ($connStr = $connStr);
			$connStr = trim($connStr);
			
			$connID = @pg_connect($connStr);
			if ($connID != "") {
				$this->connectionID = $connID;
				$this->exec("set datestyle='ISO'");
				return $this->connectionID ;
			} else {
				// FATAL ERROR - CONNECTI0N ERROR
				$this->errorCode = -1;
				$this->connectionID = -1;
				return 0;
			}
		} else {
			// FATAL ERROR - FUNCTION CALLED WITH NO PARAMETERS
			$this->connectionID = -1;
			return 0;
		}
    }   

	// standard method to close connection
	function close() {
		if ($this->connectionID != "-1") {
			$this->RollbackTrans(); // rollback transaction before closing
			$closed = pg_close($this->connectionID);
			return $closed;
		} else {
			// connection does not exist
			return null;
		}
	}

    // function to execute sql queries
    function exec($query){
		if ($this->connectionID != "-1") {
			$this->result = @pg_exec($this->connectionID, $query);
			return $this->result;
		}
		else return 0;
    }

    // get last error message for db connection
    function errorMsg() {
        if ($this->connectionID == "-1") {
			switch ($this->errorCode) {
				case -1:
					return "FATAL ERROR - CONNECTION ERROR: RESOURCE NOT FOUND";
					break;
				case -2:
					return "FATAL ERROR - CLASS ERROR: FUNCTION CALLED WITHOUT PARAMETERS";
					break;
				default:
					return null;
			}
        } else {
			return pg_errormessage($this->connectionID);
		}
    }

    ////////////////////
    // Cursor movement
    ////////////////////

    // move pointer to first row of result set
    function moveFirst() {
        if ($this->result == null) return false;
        else {
                $this->setRow(0);
                return true;
        }
    }

    // move pointer to last row of result set
    function moveLast() {
        if ($this->result == null) return false;
        else {
                $this->setRow($this->numRows()-1);
                return true;
        }
    }

	// point to the next row, return false if no next row
	function moveNext() {
		// If more rows, then advance row pointer
		if ($this->row < $this->numRows()-1) {
			$this->setRow($this->row +1);
			return true;
		}
		else return false;
	}

	// point to the previous row, return false if no previous row
	function movePrevious() {
		// If not first row, then advance row pointer
		if ($this->row > 0) {
			$this->setRow($this->row -1);
			return true;
		}
		else return false;
	}

    // point to the next row, return false if no next row
    function nextRow() {
        // If more rows, then advance row pointer
        if ($this->row < $this->numRows()-1) {
                $this->setRow($this->row +1);
                return true;
        }
        else return false;
    }

    // can be used to set a pointer to a perticular row
    function setRow($row){
        $this->row = $row; 
    }

    ///////////////////////
    // Result set related
    ///////////////////////

    // used to pull the results back
    function fobject() {
        if ($this->result == null || $this->row == "-1") return null;
        else {
                $object = pg_fetch_object($this->result,$this->row);
                return $object;
        }
    }

    // another method to obtain results
    function farray(){
        if ($this->result == null || $this->row == "-1") return null;
        else {
                $arr = pg_fetch_array($this->result,$this->row);
                return $arr;
        }
    }

    // return number of affected rows by a DELETE, UPDATE, INSERT
    function numAffected() {
        if ($this->result == null) return 0; // no result to return result from!
        else return pg_cmdtuples ($this->result);
    }

    // get the number of rows in a result
    function numRows(){
        if ($this->result == null) return 0;
        else {
                $this->numrows = pg_numrows($this->result);
                return $this->numrows;
        }
    }

    // return current row
    function currRow(){
        return $this->row;
    }

	function recordCount() {
		return $this->numRows();
	}

    // get the number of fields in a result
    function numFields() {
        if ($this->result == null) return 0;
        else return pg_numfields ($this->result);
    }

	function columnCount() {
		return $this->numFields();
	}

    // get last OID (object identifier) of last INSERT statement
    function lastOID() {
        if ($this->result == null) return null;
        else return pg_getlastoid ($this->result);
    }

	// get result field name
	function fieldname($fieldnum) {
		if ($this->result == null) return null;
		else return pg_FieldName($this->result, $fieldnum);
	}

    ////////////////////////
    // Transaction related
    ////////////////////////

    function beginTrans() {
        return @pg_exec($this->connectionID, "begin");
    }

    function commitTrans() {
        return @pg_exec($this->connectionID, "commit");
    }

    // returns true/false
    function rollbackTrans() { 
        return @pg_exec($this->connectionID, "rollback");
    }

	////////////////////////
	// SQL String Related
	////////////////////////
	function querySafe($string) {
		// replace \' with '
		$string = str_replace("\'", "'", $string);
		
		// replace line-break characters
		$string = str_replace("\n", "", $string);
		$string = str_replace("\r", "", $string);
		
		return $string;
	}

	function sqlSafe($string) {
		// replace \' with \'\'
		// use this function only for text fields that may contain "'"'s
		$string = str_replace("\'", "\'\'", $string);
		return $string;
	}
} // end class phpDB

?>

