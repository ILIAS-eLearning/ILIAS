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
   * Soap grp administration methods
   *
   * @author Stefan Meyer <smeyer@databay.de>
   * @version $Id$
   *
   * @package ilias
   */
include_once './webservice/soap/classes/class.ilSoapAdministration.php';

class ilSoapGroupAdministration extends ilSoapAdministration
{
	function ilSoapGroupAdministration()
	{
		parent::ilSoapAdministration();
	}
		

	// Service methods
	function addGroup($sid,$target_id,$grp_xml)
	{
		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->sauth->getMessage(),$this->sauth->getMessageCode());
		}			

		if(!is_numeric($target_id))
		{
			return $this->__raiseError('No valid target id given. Please choose an existing reference id of an ILIAS category or course',
									   'Client');
		}

		// Include main header
		include_once './include/inc.header.php';

		if(!$rbacsystem->checkAccess('create',$target_id,'grp'))
		{
			return $this->__raiseError('Check access failed. No permission to create groups','Server');
		}

		// Start import
		include_once("classes/class.ilObjGroup.php");
		include_once 'classes/class.ilGroupImportParser.php';

		$xml_parser = new ilGroupImportParser($grp_xml,$target_id);
		$new_ref_id = $xml_parser->startParsing();

		return $new_ref_id ? $new_ref_id : 0;
	}

	function groupExists($sid,$title)
	{
		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->sauth->getMessage(),$this->sauth->getMessageCode());
		}			

		if(!$title)
		{
			return $this->__raiseError('No title given. Please choose an title for the group in question.',
									   'Client');
		}

		// Include main header
		include_once './include/inc.header.php';

		return ilUtil::groupNameExists($title);
	}

	function getGroup($sid,$ref_id)
	{
		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->sauth->getMessage(),$this->sauth->getMessageCode());
		}			

		// Include main header
		include_once './include/inc.header.php';

		if(!$grp_obj =& ilObjectFactory::getInstanceByRefId($ref_id,false))
		{
			return $this->__raiseError('No valid reference id given.',
									   'Client');
		}

		include_once 'classes/class.ilGroupXMLWriter.php';

		$xml_writer = new ilGroupXMLWriter($grp_obj);
		$xml_writer->start();
		
		$xml = $xml_writer->getXML();
		
		return strlen($xml) ? $xml : '';
	}
		
		
	// PRIVATE

}
?>