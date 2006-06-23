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


/**
* Generic class for transformation from xml to xsl-fo
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*
* @package ilias
*/

class ilXML2FO
{
	var $xslt = null;
	var $xml = null;
	var $fo_string = null;

	var $xslt_handler = null;
	var $xslt_args = null;

	function ilXML2FO()
	{

	}

	function setXMLString($a_xml)
	{
		$this->xml = $a_xml;
	}
	function getXMLString()
	{
		return $this->xml;
	}
	function setXSLTLocation($xslt_location)
	{
		$this->xslt = $xslt_location;
	}
	function getXSLTLocation()
	{
		return $this->xslt;
	}
	function getFOString()
	{
		#return file_get_contents('./Services/Transformation/fo.xml');
		return $this->fo_string;
	}
	

	function transform()
	{
		global $ilLog;

		$this->__init();

		$this->fo_string = @xslt_process($this->xslt_handler,
										"arg:/_xml",
										"arg:/_xsl",
										null,
										$this->xslt_args,
										array());


		if(strlen($error_msg = xslt_error($this->xslt_error)))
		{
			$ilLog->write("Error generating pdf: ".$error_msg);
			return false;
		}

		xslt_free($this->xslt_handler);

		#var_dump("<pre>",htmlentities($this->getFOString()),"<pre>");

		return true;
	}

	// Private
	function __init()
	{
		$this->xslt_handler = xslt_create();
		$this->xslt_args = array('/_xml' => $this->getXMLString(),
								 '/_xsl' => file_get_contents($this->getXSLTLocation()));


		return true;
	}
		
		

	
}
?>
