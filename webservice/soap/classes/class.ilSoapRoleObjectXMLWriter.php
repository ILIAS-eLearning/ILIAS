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
* Class to simplify manual writing of xml documents.
* It only supports writing xml sequentially, because the xml document
* is saved in a string with no additional structure information.
* The author is responsible for well-formedness and validity
* of the xml document.
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id: class.ilObjectXMLWriter.php,v 1.3 2005/11/04 12:50:24 smeyer Exp $
*/

include_once "./classes/class.ilXmlWriter.php";

class ilSoapRoleObjectXMLWriter extends ilXmlWriter
{
	var $ilias;
	var $xml;
	var $roles;
	var $role_type;
	var $user_id = 0;

	/**
	* constructor
	* @param	string	xml version
	* @param	string	output encoding
	* @param	string	input encoding
	* @access	public
	*/
	function ilSoapRoleObjectXMLWriter()
	{
		global $ilias,$ilUser;

		parent::ilXmlWriter();

		$this->ilias =& $ilias;
		$this->user_id = $ilUser->getId();
	}


	function setObjects(&  $roles)
	{
		$this->roles = & $roles;
	}

	function setType ($type)
	{
		$this->role_type = $type;
	}


	function start()
	{
		if (!is_array($this->roles))
			return false;

		$this->__buildHeader();

		include_once './classes/class.ilObjRole.php';

		foreach ($this->roles as $role)
		{
			// if role type is not empty and does not match, then continue;
			if (!empty($this->role_type) && strcasecmp ($this->role_type, $role["role_type"]) != 0 )
			{
				continue;
			}

			$attrs = array(	'role_type' => ucwords($role["role_type"]), 'obj_id' => $role["obj_id"], 'id' => $role["title"]);

			// open tag
			$this->xmlStartTag("Role", $attrs);


			$this->xmlElement('Title',null, $role["title"]);
			$this->xmlElement('Description',null, $role["description"]);
			$this->xmlElement('Translation',null,ilObjRole::_getTranslation($role["title"]));

			if ($ref_id = ilSoapRoleObjectXMLWriter::__extractRefId($role["title"]))
			{

				$ownerObj = IlObjectFactory::getInstanceByRefId($ref_id, false);

				if (is_object($ownerObj))
				{
					$attrs = array ("obj_id" => $ownerObj->getId(), "ref_id" => $ownerObj->getRefId(), "type" => $ownerObj->getType());
					$this->xmlStartTag('Owner', $attrs);
					$this->xmlElement ('Title', null, $ownerObj->getTitle());
					$this->xmlElement ('Description', null, $ownerObj->getDescription());
					$this->xmlEndTag ('Owner', $attrs);
				}
			}

			$this->xmlEndTag ("Role");

		}

		$this->__buildFooter();

		return true;
	}

	function getXML()
	{
		return $this->xmlDumpMem(FALSE);
	}


	function __buildHeader()
	{
		$this->xmlSetDtdDef("<!DOCTYPE Roles PUBLIC \"-//ILIAS//DTD ILIAS Roles//EN\" \"http://www.ilias.uni-koeln.de/download/dtd/ilias_role_object_3_7.dtd\">");
		$this->xmlSetGenCmt("Roles information of ilias system");
		$this->xmlHeader();

		$this->xmlStartTag('Roles');

		return true;
	}

	function __buildFooter()
	{
		$this->xmlEndTag('Roles');
	}

	/**
	*	@param role_title with format like il_crs_member_893
	*	@return	ref id or false
	*/

	function __extractRefId($role_title)
	{

		$test_str = explode('_',$role_title);

		if ($test_str[0] == 'il')
		{
			$test2 = (int) $test_str[3];
			return is_numeric ($test2) ? (int) $test2 : false;
		}
		return false;
	}

}


?>
