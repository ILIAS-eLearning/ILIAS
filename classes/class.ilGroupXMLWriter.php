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
* XML writer class
*
* Class for writing xml export versions of courses
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*/

include_once "./classes/class.ilXmlWriter.php";

class ilGroupXMLWriter extends ilXmlWriter
{
	private $ilias;

	private $xml;
	private $group_obj;
	private $attach_users = true;

	/**
	* constructor
	* @param	string	xml version
	* @param	string	output encoding
	* @param	string	input encoding
	* @access	public
	*/
	function ilGroupXMLWriter(&$group_obj)
	{
		global $ilias;

		parent::ilXmlWriter();

		$this->EXPORT_VERSION = "2";

		$this->ilias =& $ilias;
		$this->group_obj =& $group_obj;
	}

	function start()
	{
		$this->__buildHeader();
		$this->__buildTitleDescription();
		$this->__buildRegistration();
		if ($this->attach_users) 
		{
			$this->__buildAdmin();
			$this->__buildMember();
		}
		$this->__buildFooter();
		
	}

	function getXML()
	{
		return $this->xmlDumpMem(FALSE);
	}

	// PRIVATE
	function __buildHeader()
	{
		$this->xmlSetDtdDef("<!DOCTYPE group PUBLIC \"-//ILIAS//DTD Group//EN\" \"".ILIAS_HTTP_PATH."/xml/ilias_group_3_8.dtd\">");  
		$this->xmlSetGenCmt("Export of ILIAS group ". $this->group_obj->getId()." of installation ".$this->ilias->getSetting('inst_id').".");
		$this->xmlHeader();

		$attrs["exportVersion"] = $this->EXPORT_VERSION;
		$attrs["id"] = "il_".$this->ilias->getSetting('inst_id').'_grp_'.$this->group_obj->getId();
		$attrs['type'] = $this->group_obj->readGroupStatus() ? 'open' : 'closed';
		$this->xmlStartTag("group", $attrs);

		return true;
	}

	function __buildTitleDescription()
	{
		$this->xmlElement('title',null,$this->group_obj->getTitle());
		
		if($desc = $this->group_obj->getDescription())
		{
			$this->xmlElement('description',null,$desc);
		}

		$attr['id'] = 'il_'.$this->ilias->getSetting('inst_id').'_usr_'.$this->group_obj->getOwner();
		$this->xmlElement('owner',$attr);
	}

	function __buildRegistration()
	{
		switch($this->group_obj->getRegistrationFlag())
		{
			case '0':
				$attr['type'] = 'disabled';
				break;
			case '1':
				$attr['type'] = 'enabled';
				break;
			case '2':
				$attr['type'] = 'password';
				break;
		}
		$this->xmlStartTag('registration',$attr);

		if(strlen($pwd = $this->group_obj->getPassword()))
		{
			$this->xmlElement('password',null,$pwd);
		}
		if($timest = $this->group_obj->getExpirationTimestamp())
		{
			$this->xmlElement('expiration',null,$timest);
		}
		$this->xmlEndTag('registration');
	}
		
	function __buildAdmin()
	{
		foreach($this->group_obj->getGroupAdminIds() as $id)
		{
			$attr['id'] = 'il_'.$this->ilias->getSetting('inst_id').'_usr_'.$id;

			$this->xmlElement('admin',$attr);
		}
		return true;
	}

	function __buildMember()
	{
		foreach($this->group_obj->getGroupMemberIds() as $id)
		{
			if(!$this->group_obj->isAdmin($id))
			{
				$attr['id'] = 'il_'.$this->ilias->getSetting('inst_id').'_usr_'.$id;
				
				$this->xmlElement('member',$attr);
			}
		}
		return true;
	}

	function __buildFooter()
	{
		$this->xmlEndTag('group');
	}

	function setAttachUsers ($value) {
		$this->attach_users = $value ? true : false;
	}

}


?>
