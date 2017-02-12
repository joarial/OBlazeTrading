<?php

function DoRetrieveArray($param)
{
	global $db;

	$query = "Select * from data_arrays where data =\"$param\"";
	$rs = $db->Execute($query);

	$dataArray = explode(';', $rs->fields[1]);
	
//	echo "retrieved $param: " . count($dataArray) . "records<br />";
	
	return $dataArray;
}

function DoStoreDataArray($param, &$vector)
{
	global $db;
	
	$record = array();
	$record["data"] = $param;
	$record["array_data"] = "";

	foreach($vector as $value)
		$record["array_data"] .= is_numeric($value) ? number_format($value, 2,'.','') . ";" : $value . ";";

	$query = "Select * from data_arrays where data =\"$param\"";
	$rs = $db->Execute($query);

	if ($rs->RecordCount() != 1)
		$insertSQL = $db->GetInsertSQL($rs, $record);
	else
		$insertSQL = $db->GetUpdateSQL($rs, $record);
			
	$db->Execute($insertSQL);
}

?>