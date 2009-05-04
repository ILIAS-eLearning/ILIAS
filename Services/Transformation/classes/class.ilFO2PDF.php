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
* Adapter class for communication between ilias and ilRPCServer
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*
* @package ilias
*/
include_once 'Services/WebServices/RPC/classes/class.ilRPCServerAdapter.php';

define("MODE_FO2PDF",1);

class ilFO2PDF extends ilRPCServerAdapter
{
	var $log = null;
	var $mode = MODE_FO2PDF;
	var $fo_string = '';
	
	function ilFO2PDF()
	{
		global $ilLog;

		parent::ilRPCServerAdapter();

	}

	function setFOString($a_fo)
	{
		$this->fo_string = $a_fo;
	}
	function getFOString()
	{
		return $this->fo_string;
	}


	function setMode($a_mode)
	{
		$this->mode = $a_mode;
	}
	function getMode()
	{
		return $this->mode;
	}

	function send()
	{
		$this->__initClient();
		switch($this->getMode())
		{
			case MODE_FO2PDF:
				$this->__prepareFO2PDFParams();
				break;

			default:
				$this->log->write('ilFO2PDF(): No valid mode given');
				return false;

		}
		return parent::send();
	}
	function __prepareFO2PDFParams()
	{
		$this->__initMessage('transform.ilFO2PDF',array(new XML_RPC_Value($this->getFOString(),"string")));

		return true;
	}
}
?>
