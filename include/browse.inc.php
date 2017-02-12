<?php

define(ROWS, 8);

// Browse through the $connection by the running $query.
//
// Begin the display of data with row $rowOffset.
// Put a header on the page, $pageHeader
//
// Use the array $header[][“header”] for headers on 
// each <table> column
// Use the array $header[][“attrib”] for the names 
// of the database attributes to show in each column
//
// Use $browseString to prefix an embedded link 
// to the previous, next, and other pages

function browse($scriptName,
                $connection,
                $browseString,
                $rowOffset,
                $query,
                $pageHeader,
                $header,
                $href)
{

  // (1) Run the query on the database through the
  // connection
  if (!($result = @ mysql_query ($query, $connection)))
     showerror();
     
  // Find out how many rows there are
  $rowsFound = @ mysql_num_rows($result);

  // Is there any data?
  if ($rowsFound != 0)
  {
     // Yes, there is data.

     // (2a) The “Previous” page begins at the current 
     // offset LESS the number of ROWS per page
     $previousOffset = $rowOffset - ROWS;

     // (2b) The “Next” page begins at the current offset
     // PLUS the number of ROWS per page
     $nextOffset = $rowOffset + ROWS;

     // (3) Seek to the current offset
     if (!mysql_data_seek($result, $rowOffset))
        showerror();

     // (4a) Output the header and start a table
     echo "\n<font face=\"Lucida Grande,verdana,sans-serif\" size=\"2\">";
     echo $pageHeader;
     echo "<table border=\"1\">\n<tr>";

     // (4b) Print out the column headers from $header
     foreach ($header as $element)
        echo "\n\t<th>" . $element["header"] . "</th>";

     echo "\n</tr>";

     // (5a) Fetch one page of results (or less if on the
     // last page)
     for ( $rowCounter = 0;
         (($rowCounter < ROWS) &&
          ($row = @ mysql_fetch_array($result)) );
         $rowCounter++)
     {
        // Print out a row
        echo "\n<tr>";

        // (5b) For each of the attributes in a row
        foreach($header as $element)
        {
           echo "\n\t<td>";

           // Get the database attribute name for the
           // current attribute
           $temp = $element["attrib"];
		   	
           // Print out the value of the current
           // attribute
           // Include HREF if current item is the one which we want to refer to
           
	   	   if ($temp == $href)
		   {
		   	$temp = trim($row["$temp"]);
		   	
		   	$echoString = "<a href=\"programmeform.php?" . $href . "=";
		   	$echoString .= $temp;
		   	$echoString .= "\">Edit</a>";
		   }
		   else
		   {
           	$temp = trim($row["$temp"]);

		   	if ((is_numeric($temp)) && ($element["attrib"] == "amount"))
		   		$temp = number_format($temp, 0, '.', ',');

			$echoString = $temp;
		   }

		   echo $echoString;

           echo "</td>";
        } // end foreach attribute

        echo "\n</tr>\n";
     } // end for rows in the page

     // Finish the results table, and start a footer
     echo "\n</table>\n<br>";

     // (6) Show the row numbers that are being viewed
     echo ($rowOffset + 1) .  "-" . 
          ($rowCounter + $rowOffset) . " of ";
     echo "$rowsFound records found matching " .
          "your criteria\n<br>";

     // (7a) Are there any previous pages?
     if ($rowOffset > 0)
       // Yes, so create a previous link
       if ($browseString == NULL)
       	echo "\n\t<a href=\"" . $scriptName .
       	 	"?offset=" . rawurlencode($previousOffset) .
       	    "\">Previous</a> ";
       else
       	echo "\n\t<a href=\"" . $scriptName . 
            "?offset=" . rawurlencode($previousOffset) .
            "&amp;" . $browseString .
            "\">Previous</a> ";
     else
       // No, there is no previous page so don’t 
       // print a link
       echo "Previous ";

     // Output the page numbers as links
     // Count through the number of pages in the results
     for($x=0, $page=1;
         $x<$rowsFound; 
         $x+=ROWS, $page++)
        // Is this the current page?
        if ($x < $rowOffset || 
            $x > ($rowOffset + ROWS - 1))
           // No, so print out a link
           if ($browseString == NULL)
          	 echo "<a href=\"" . $scriptName . 
          	      "?offset=" . rawurlencode($x) .
          	      "\">" . $page  . "</a> ";
           else	
          	 echo "<a href=\"" . $scriptName . 
          	      "?offset=" . rawurlencode($x) .
          	      "&amp;" . $browseString . 
          	      "\">" . $page  . "</a> ";
           else
              // Yes, so don’t print a link
         echo $page  . " ";  

     // (7b) Are there any Next pages?
     if (($row != false) && ($rowsFound > $nextOffset))
       // Yes, so create a next link
       if ($browseString == NULL)
	       echo "\n\t<a href=\"" . $scriptName . 
    	        "?offset=" . rawurlencode($nextOffset) .
    	        "\">Next</a> ";
       else
    	   echo "\n\t<a href=\"" . $scriptName . 
    	        "?offset=" . rawurlencode($nextOffset) .
    	        "&amp;" . $browseString .
    	        "\">Next</a> ";
     else
       // No,  there is no next page so don’t 
       // print a link
       echo "Next ";

  } // end if rowsFound != 0
  else
  {
    echo "<br>No rows found matching your criteria.\n";
  }
  // (7c) Create a link back to the query input page
  if ($browseString != NULL)
	  echo "<br><a href=\"" . $scriptName . 
    	   "\">Back to Search</a><br>";
}  
