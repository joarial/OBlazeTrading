<?php
require_once('../adodb5/adodb.inc.php');
require_once('../adodb5/adodb-active-record.inc.php');

//using MAMP locally
	$h = 'localhost';

	//test local
	$d = 'mInvest';
	$u = 'root';
	$p = 'Isolde13';

	$dsn = "mysql://$u:$p@$h/$d?persist";
	$db = NewADOConnection($dsn);

	$db->debug = true;
//	$db->debug = false;

ADOdb_Active_Record::SetDatabaseAdapter($db);

class quote extends ADOdb_Active_Record{}

$quote = new quote();
var_dump($quote->getAttributeNames());

?>


