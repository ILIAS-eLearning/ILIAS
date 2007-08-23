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
* Class ilLPItemListGUI
*
* @author Stefan Meyer <smeyer@databay.de>
*
* @version $Id$
*
* @extends ilObjectGUI
* @package ilias-core
*
*/

include_once 'Services/Tracking/classes/ItemList/class.ilLPObjectItemListGUI.php';

class ilLPUserItemListGUI extends ilLPObjectItemListGUI
{
	var $child_id = null;

	function ilLPUserItemListGUI($a_obj_id)
	{
		parent::ilLPObjectItemListGUI($a_obj_id,'usr');
	}

	function readUserInfo()
	{
		parent::readUserInfo();
		$this->__readTitle();
		$this->__readDescription();
	}


	function __readTitle()
	{
		global $ilObjDataCache;

		include_once './Services/User/classes/class.ilObjUser.php';

		$login = '['.ilObjUser::_lookupLogin($this->getCurrentUser()).']';
		$fullname = $ilObjDataCache->lookupTitle($this->getCurrentUser());

		return $this->title = $login .' '. $fullname;
	}
	function __readDescription()
	{
		return $this->description = '';
	}

	function renderTypeImage()
	{
		$this->tpl->setCurrentBlock("row_type_image");
		$this->tpl->setVariable("TYPE_IMG",ilObjUser::_getPersonalPicturePath($this->getCurrentUser(),'xxsmall'));
		$this->tpl->setVariable("TYPE_ALT",$this->getTitle());
		$this->tpl->parseCurrentBlock();
	}
}
?>