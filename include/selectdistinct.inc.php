<?
function selectDistinct    (&$db,
							$tableName,
							$columnName,
							$pulldownName,
							$additionalOption,
							$defaultValue)
{
	$defaultWithinResultSet = FALSE;
    // Query to find distinct values of $columnName
    // in $tableName
    $distinctQuery = "SELECT DISTINCT ticker, $columnName
                      FROM $tableName";
    // Run the distinctQuery
    $resultId = $db->Execute($distinctQuery);

	if (!$resultId)
    	print $db->ErrorMsg();
	else
	{
	    $i = 0;
	    while (!$resultId->EOF)
    	{
    		$resultBuffer[$i++] = $resultId->fields[1]
    							  . " ("
    							  . $resultId->fields[0]
    							  . ")";
    		$resultId->MoveNext();
   		}
    	// Start the select widget
// 	   echo "\n<font face=\"Lucida Grande,verdana,sans-serif\" size=\"2\">";
 	   echo "\n<select name=\"$pulldownName\">";       
 	   // Is there an additional option?
 	   if (isset($additionalOption))
 	   // Yes, but is it the default option?
 	 	  	$aO = explode("&", $additionalOption);
 	   		if (count($aO) > 1)
 	   			$optionString = $aO[0] . "&amp;" . $aO[1];
 	   		else
 	   			$optionString = $additionalOption;
 	   			
 	   		if ($defaultValue == $additionalOption)
 	   	// Show the additional option as selected
 	   			echo "\n\t<option selected=\"selected\">$optionString</option>";
 	    	else
        	// Just show the additional option
        		echo "\n\t<option>$optionString</option>";
     
	    // check for a default value
    	if (isset($defaultValue))
    	{
 	   // Yes, there's a default value specified
    	// Check if the defaultValue is in the 
    	// database values
    		foreach ($resultBuffer as $result)
    		{
    			$aO = explode("&", $result);
 	   			if (count($aO) > 1)
	 	   			$optionString = $aO[0] . "&amp;" . $aO[1];
				else
					$optionString = $result;
					
    			if ($result == $defaultValue)
    			// Yes, show as selected
    				echo "\n\t<option selected=\"selected\">$optionString</option>";
    			else
    	        // No, just show as an option
    	          	echo "\n\t<option>$optionString</option>";
			}
		}// end if defaultValue
    	else 
    	{ 
    	// No defaultValue
    	// Show database values as options
    		foreach ($resultBuffer as $result)
    		{
	    	   	$aO = explode("&", $result);
 		   		if (count($aO) > 1)
	 		   		$optionString = $aO[0] . "&amp;" . $aO[1];
	 		   	else
	 		   		$optionString = $result;
	 		   		
   	       		echo "\n\t<option>$optionString</option>";
   	       	}
    	}
    	echo "\n</select>";
    }
  } // end of function