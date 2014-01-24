<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

define ("SPACE_PRESERVE", "1");
define ("SPACE_DEFAULT", "2");

/**
* QTI mattext class
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
*
* @package assessment
*/
class ilQTIMattext
{
	var $texttype;
	var $label;
	var $charset;
	var $uri;
	var $xmlspace;
	var $xmllang;
	var $entityref;
	var $width;
	var $height;
	var $x0;
	var $y0;
	var $content;
	
	function ilQTIMatimage()
	{
	}

	function setTexttype($a_texttype)
	{
		$this->texttype = $a_texttype;
	}
	
	function getTexttype()
	{
		return $this->texttype;
	}
	
	function setLabel($a_label)
	{
		$this->label = $a_label;
	}
	
	function getLabel()
	{
		return $this->label;
	}
	
	function setHeight($a_height)
	{
		$this->height = $a_height;
	}
	
	function getHeight()
	{
		return $this->height;
	}
	
	function setWidth($a_width)
	{
		$this->width = $a_width;
	}
	
	function getWidth()
	{
		return $this->width;
	}
	
	function setCharset($a_charset)
	{
		$this->charset = $a_charset;
	}
	
	function getCharset()
	{
		return $this->charset;
	}
	
	function setUri($a_uri)
	{
		$this->uri = $a_uri;
	}
	
	function getUri()
	{
		return $this->uri;
	}
	
	function setXmllang($a_xmllang)
	{
		$this->xmllang = $a_xmllang;
	}
	
	function getXmllang()
	{
		return $this->xmllang;
	}
	
	function setXmlspace($a_xmlspace)
	{
		switch (strtolower($a_xmlspace))
		{
			case "preserve":
			case "1":
				$this->xmlspace = SPACE_PRESERVE;
				break;
			case "default":
			case "2":
				$this->xmlspace = SPACE_DEFAULT;
				break;
		}
	}
	
	function getXmlspace()
	{
		return $this->xmlspace;
	}
	
	function setX0($a_x0)
	{
		$this->x0 = $a_x0;
	}
	
	function getX0()
	{
		return $this->x0;
	}
	
	function setY0($a_y0)
	{
		$this->y0 = $a_y0;
	}
	
	function getY0()
	{
		return $this->y0;
	}
	
	function setEntityref($a_entityref)
	{
		$this->entityref = $a_entityref;
	}
	
	function getEntityref()
	{
		return $this->entityref;
	}
	
	function setContent($a_content)
	{
		$this->content = $a_content;
	}
	
	function getContent()
	{
		return $this->content;
	}
}
?>
