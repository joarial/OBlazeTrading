<?php
require_once('../adodb5/adodb.inc.php');
require_once('../adodb5/adodb-active-record.inc.php');

//using MAMP locally
	$h = 'localhost';

	//test local
	$d = 'minvest';
	$u = 'root';
	$p = 'Isolde13';

	$dsn = "mysql://$u:$p@$h/$d?persist";
	$db = NewADOConnection($dsn);

	$db->debug = true;

ADOdb_Active_Record::SetDatabaseAdapter($db);

class issuer extends ADOdb_Active_Record{}

$issuer = new issuer();
var_dump($issuer->getAttributeNames());

?>
