<?php
require_once('../adodb5/adodb.inc.php');
require_once('../adodb5/adodb-active-record.inc.php');

//using MAMP locally
	$h = 'localhost';
//production
//	$d = 'uks13350mInvest';
//	$u = 'uks13350ajj';
//	$p = '273295';

//test local
	$d = 'minvest';
	$u = 'root';
	$p = 'Isolde13';

//	$dsn = "mysql://$u:$p@$h/$d?persist";
$db = NewADOConnection("mysql://$u:$p@$h/$d?persist");

//$db = NewADOConnection('mysql://root@localhost/minvest');

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

/*
 * Calling the save() method will successfully INSERT
 * this $person into the database table.
 */
$person = new person();
$person->name_first     = 'Andi';
$person->name_last      = 'Gutmans';
$person->favorite_color = 'blue';
$person->save();

var_dump($person);

/**
 * Outputs the following:
 * string(1)
 */
$person->favorite_color = 'red';
$person->save();

var_dump($person);
?>


