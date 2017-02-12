<?php

	include_once('../adodb5/adodb.inc.php');
	include_once('../adodb5/adodb-active-record.inc.php');

#	if (PHP_VERSION >= 5) include('../adodb5/adodb-exceptions.inc.php');

	$db = NewADOConnection('mysql://root@localhost/minvest');
	$db->debug=1;
	ADOdb_Active_Record::SetDatabaseAdapter($db);

	$db->Execute("CREATE TEMPORARY TABLE `persons` (
    	            `id` int(10) unsigned NOT NULL auto_increment,
        	        `name_first` varchar(100) NOT NULL default '',
            	    `name_last` varchar(100) NOT NULL default '',
                	`favorite_color` varchar(100) NOT NULL default '',
    	            PRIMARY KEY  (`id`)
        	    ) ENGINE=MyISAM;
          	 ");
		   
class person extends ADOdb_Active_Record{}
$person = new person();

echo "<p>Output of getAttributeNames: ";
var_dump($person->getAttributeNames());

/**
 * Outputs the following:
 * array(4) {
 *    [0]=>
 *    string(2) "id"
 *    [1]=>
 *    string(9) "name_first"
 *    [2]=>
 *    string(8) "name_last"
 *    [3]=>
 *    string(13) "favorite_color"
 *  }
 */

$person = new person();
$person->name_first = 'Andi';
$person->name_last  = 'Gutmans';

/*
try {
	$person->save(); // this save() will fail on INSERT as favorite_color is a must fill...
} catch(exceptions $e) {
	echo $e->getMessage();
}

$ok = $person->Save();
if (!$ok) $err = $rec->ErrorMsg();
*/


$person = new person();
$person->name_first     = 'Andi';
$person->name_last      = 'Gutmans';
$person->favorite_color = 'blue';
$person->save(); // this save will perform an INSERT successfully

echo "<p>The Insert ID generated:"; print_r($person->id);

$person->favorite_color = 'red';
$person->save(); // this save() will perform an UPDATE

$person = new person();
$person->name_first     = 'John';
$person->name_last      = 'Lim';
$person->favorite_color = 'lavender';
$person->save(); // this save will perform an INSERT successfully

// load record where id=2 into a new ADOdb_Active_Record
$person2 = new person();
$person2->Load('id=2');
var_dump($person2);

// retrieve an array of records
$activeArr = $db->GetActiveRecordsClass($class = "person",$table = "persons","id=".$db->Param(0),array(2));
$person2 = $activeArr[0];
echo "<p>Name first (should be John): ",$person->name_first, "<br>Class = ",get_class($person2);	

?>