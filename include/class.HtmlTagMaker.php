<?php
/*
	Class HtmlTagMaker by Karsten J. Mikkelsen (kargo@teliamail.dk)
	Version 1.1, August 2002
	A class for formatting HTML tags
	See documentation in HTMLTagMakerClass.html
	Mainly intended for use by other classes, see TableMaker.inc

	Version 1.0, June 2002
	Version 1.1, August 13, 2002: allow for parameters that are just keywords
	rather than key/value pairs, e.g. selected in option tags. Thanks to J. C—rdoba
	
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

*/
class HtmlTagMaker {
	var $tagType;
	var $tagAttributes;
	var $startTag;
	var $endTag='';
	var $qMark = '"'; // Quotation mark
	
	function HtmlTagMaker($tagType, $tagAttributes=array(), $hasEndTag=true) {
		$this -> tagType = $tagType;
		$this -> tagAttributes = $tagAttributes;
		$this -> makeStartTag();
		if ($hasEndTag) $this -> makeEndTag();
	} // constructor
	
	function makeStartTag() {
		$tmpTag = '<' . $this -> tagType;
		// add contents of $this -> tagAttributes
		if (is_array($this -> tagAttributes)) {
			$ak = array_keys($this -> tagAttributes);
			$j = sizeof($ak);
			for ($i = 0; $i < $j; $i++) {
				if ($ak[$i]) {
					$tmpTag .= ' ' . $ak[$i] . '=';
					$tmpTag .= $this -> qMark . $this -> tagAttributes[$ak[$i]] . $this -> qMark;
				} // Just a keyword, e.g. selected
				else $tmpTag .= ' ' . $this -> tagAttributes[$ak[$i]];
			}
		}
		$tmpTag .= '>';
		$this -> startTag = $tmpTag;
	} // makeStartTag()

	function makeEndTag() {
		$this -> endTag = '</' . $this -> tagType . '>';
	} // makeEndTag()
	
	function setAttributes($tagAttributes) {
		// Change or add tag attributes
		$ak = array_keys($tagAttributes);
		$j = sizeof($ak);
		for ($i = 0; $i < $j; $i++) {
			$this -> tagAttributes[$ak[$i]] = $tagAttributes[$ak[$i]];
		}
		$this -> makeStartTag();
	} // setAttributes()

	function setSingleQuotes() {
		// Use single quotes rather than double quotes (the default)
		$this -> qMark = "'";
		$this -> makeStartTag();
	}
	
	function setDoubleQuotes() {
		// Reverse preceding call to setSingleQuotes()
		$this -> qMark = '"';
		$this -> makeStartTag();
	}
	
	function getStartTag() {
		// Return the opening tag with all attributes
		return $this -> startTag;
	}
	
	function getEndTag() {
		// Return the closing tag, if any
		return $this -> endTag;
	}
	
	function getTag($contents) {
		// Return $contents within an opening and closing tag pair
		return $this -> startTag . $contents . $this -> endTag;
	}
	
	function toString() {
		return htmlentities($this -> startTag . $this -> endTag);
	}
} // class HtmlTagMaker
?>