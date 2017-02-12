<?php
/*
********************************************************************************

TableMaker.inc
Version 1.0
A PHP class for producing HTML tables

  ****************************************************************************
  * Copyright (C) 2002 Karsten Juul Mikkelsen                                *
  *                                                                          *
  * This PHP class is free software; you can redistribute it and/or          *
  * modify it under the terms of the GNU Lesser General Public               *
  * License as published by the Free Software Foundation; either             *
  * version 2.1 of the License, or (at your option) any later version.       *
  *                                                                          *
  * This PHP class is distributed in the hope that it will be useful,        *
  * but WITHOUT ANY WARRANTY; without even the implied warranty of           *
  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU         *
  * Lesser General Public License for more details.                          *
  *                                                                          *
  * You should have received a copy of the GNU Lesser General Public         *
  * License along with this PHP class; if not, write to the Free Software    *
  * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA  *
  *                                                                          *
  * Author:                                                                  *
  * Karsten Juul Mikkelsen, 8900 Randers, Denmark, kjm@arachnodata.dk        *
  *                                                                          *
  ****************************************************************************

See TableMakerClass.html for documentation

********************************************************************************
*/

// require_once ('../PhpInclude/HtmlTagMaker.inc');

class TableMaker {
	var $tableTag;
	var $tableCaption = '';
	var $captionString = '';
	var $tableRows = array();
	var $tableHeaders = array();
	var $tableCells = array();
	var $returnString = '';
	var $blockEnd = '';
	var $tableIndent = "\t";
	var $rowIndent = "\t";
	var $cellIndent = "\t";
	var $dataIndent = "\t";
	var $tagCase = 'strtoupper';
	var $newLine = "\r\n";
	
	function TableMaker ($tableAttributes = '') {
		$cf = $this -> tagCase;
		$this -> tableTag = new HtmlTagMaker($cf('table'), $tableAttributes);
	} // constructor

	function setIndentation($indentString, $tblIndent, $trIndent, $tdIndent, $dataIndent) {
		// Define indentation for table HTML code
		$this -> tableIndent = str_repeat($indentString, $tblIndent);
		if ($trIndent > $tblIndent) $this -> rowIndent = str_repeat($indentString, $trIndent - $tblIndent);
		else $this -> rowIndent = '';
		if ($tdIndent > ($trIndent)) $this -> cellIndent = str_repeat($indentString, $tdIndent - $trIndent);
		else $this -> cellIndent = '';
		if ($dataIndent > $tdIndent) $this -> dataIndent = str_repeat($indentString, $dataIndent - $tdIndent);
		else $this -> dataIndent = '';
	} // setIndentation()


	function setCase($newCase) {
		// Change tag case to uppercase, lowercase or uppercase first letter
		// u or upper = uppercase (the default)
		// l or lower = lowercase
		// f or first = uppercase first letter
		$newCase = strtolower($newCase);
		switch($newCase) {
			case 'l': $this -> tagCase = 'strtolower'; break;
			case 'lower': $this -> tagCase = 'strtolower'; break;
			case 'u': $this -> tagCase = 'strtoupper'; break;
			case 'upper': $this -> tagCase = 'strtoupper'; break;
			case 'f': $this -> tagCase = 'ucfirst'; break;
			case 'first': $this -> tagCase = 'ucfirst';
		} // switch
		$cf = $this -> tagCase; 
		$this -> tableTag -> tagType = $cf($this -> tableTag -> tagType);
		$this -> tableTag -> makeStartTag();
		$this -> tableTag -> makeEndTag();
		if ($this -> tableCaption) {
			$this -> tableCaption -> tagType = $cf($this -> tableCaption -> tagType);
			$this -> tableCaption -> makeStartTag();
			$this -> tableCaption -> makeEndTag();
		}
	} // setCase()
	
	function getCaptionString() {
		// Return the table caption formatted for output
		if (!$this -> captionString) return '';
		else return $this -> tableIndent . $this -> rowIndent . $this -> tableCaption -> getStartTag() . $this -> newLine
					. $this -> tableIndent . $this -> rowIndent . $this -> cellIndent . $this -> captionString . $this -> newLine
					. $this -> tableIndent . $this -> rowIndent . $this -> tableCaption -> getEndTag() . $this -> newLine;
	} // getCaptionString()
	
	function defineCaption($captionString, $captionAttributes) {
		// Create or modify a table caption (<caption> tag)
		if(!$this -> tableCaption) {
			$cf = $this -> tagCase;
			$this -> tableCaption = new HtmlTagMaker($cf('caption'), $captionAttributes);
		}
		else if (is_array($captionAttributes)) {
			// Change or add caption attributes
			$this -> tableCaption -> setAttributes($captionAttributes);
		}
	} // defineCaption()

	function addCaption($captionString, $captionAttributes = '') {
		// Add a <caption> tag
		$this -> defineCaption($captionString, $captionAttributes);
		$this -> captionString = $captionString;
	} // addCaption()
	
	function defineRow($rowID, $rowAttributes) {
		// Create or modify a pre-defined row type (<tr> tag)
		if (!isset($this -> tableRows[$rowID])) {
			// Add new row definition
			$cf = $this -> tagCase;
			$this -> tableRows[$rowID] = new HtmlTagMaker($cf('tr'), $rowAttributes);
		}
		else if (is_array($rowAttributes)) {
			// Change or add row attributes
			$this -> tableRows[$rowID] -> setAttributes($rowAttributes);
		}
	} // defineRow()

	function addRow ($rowID = 'default', $rowAttributes = '') {
		// Add a <tr> tag
		$this -> defineRow($rowID, $rowAttributes);
		$this -> returnString .= $this -> blockEnd; // Terminate previous block
		$this -> blockEnd = $this -> tableIndent . $this -> rowIndent . $this -> tableRows[$rowID] -> getEndTag() . $this -> newLine;  // Prepare end of this block
		$this -> returnString .= $this -> tableIndent . $this -> rowIndent . $this -> tableRows[$rowID] -> getStartTag() . $this -> newLine; // Add <tr> tag
	} // addRow()

	function defineHeader ($headerID, $headerAttributes) {
		// Create or modify a pre-defined header type (<th> tag)
		if (!isset($this -> tableHeaders[$headerID])) {
			// Add new table header definition
			$cf = $this -> tagCase;
			$this -> tableHeaders[$headerID] = new HtmlTagMaker($cf('th'), $headerAttributes);
		}
		else if (is_array($headerAttributes)) {
			// Change or add header attributes
			$this -> tableHeaders[$headerID] -> setAttributes($headerAttributes);
		}
	} // defineHeader()

	function addHeader ($headerData = '&nbsp;', $headerID = 'default', $headerAttributes = '') {
		// Add <th></th> tag set with contents
		$this -> defineHeader($headerID, $headerAttributes);
		$this -> returnString .= $this -> tableIndent . $this -> rowIndent . $this -> cellIndent
				. $this -> tableHeaders[$headerID] -> getStartTag() . $this -> newLine
				. $this -> tableIndent . $this -> rowIndent . $this -> cellIndent . $this -> dataIndent
				. $headerData . $this -> newLine
				. $this -> tableIndent . $this -> rowIndent . $this -> cellIndent
				. $this -> tableHeaders[$headerID] -> getEndTag() . $this -> newLine;
	} // addHeader()
	
	function defineDataCell ($cellID, $cellAttributes) {
		// Create or modify a pre-defined data cell type (<td> tag)
		if (!isset($this -> tableCells[$cellID])) {
			// Add new table cell definition
			$cf = $this -> tagCase;
			$this -> tableCells[$cellID] = new HtmlTagMaker($cf('td'), $cellAttributes);
		}
		else if (is_array($cellAttributes)) {
			// Change or add cell attributes
			$this -> tableCells[$cellID] -> setAttributes($cellAttributes);
		}
	} // defineDataCell()

	function addData ($cellData = '&nbsp;', $cellID = 'default', $cellAttributes = '') {
		// Add <td></td> tag set with contents
		$this -> defineDataCell($cellID, $cellAttributes);
		$this -> returnString .= $this -> tableIndent . $this -> rowIndent . $this -> cellIndent
				. $this -> tableCells[$cellID] -> getStartTag() . $this -> newLine
				. $this -> tableIndent . $this -> rowIndent . $this -> cellIndent . $this -> dataIndent
				. $cellData . $this -> newLine
				. $this -> tableIndent . $this -> rowIndent . $this -> cellIndent
				. $this -> tableCells[$cellID] -> getEndTag() . $this -> newLine;
	} // addData()
	
	function getTable() {
		// Return the whole table as a string
		$this -> returnString .= $this -> blockEnd;
		$this -> blockEnd = '';
		return $this -> tableIndent . $this -> tableTag -> getStartTag() . $this -> newLine
					. $this -> getCaptionString()
					. $this -> returnString
					. $this -> tableIndent . $this -> tableTag -> getEndTag() . $this -> newLine;
	} // getTable()
	
	function writeTable() {
		// Write the table
		echo $this -> getTable();
	} // writeTable()
} // class Tablemaker
?>