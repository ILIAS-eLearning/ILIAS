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
* 
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @package ilias
*/

class ilCronWebResourceCheck
{
	function ilCronWebResourceCheck()
	{
		global $ilLog,$ilDB;

		$this->log =& $ilLog;
		$this->db =& $ilDB;
	}

	function check()
	{
		global $ilObjDataCache,$ilUser;

		include_once'./Services/LinkChecker/classes/class.ilLinkChecker.php';


		foreach(ilUtil::_getObjectsByOperations('webr','write',$ilUser->getId(),-1) as $node)
		{
			if(!is_object($tmp_webr =& ilObjectFactory::getInstanceByRefId($node,false)))
			{
				continue;
			}

			$tmp_webr->initLinkResourceItemsObject();
			
			// Set all link to valid. After check invalid links will be set to invalid

			$link_checker =& new ilLinkChecker($this->db);
			$link_checker->setMailStatus(true);
			$link_checker->setCheckPeriod($this->__getCheckPeriod());
			$link_checker->setObjId($tmp_webr->getId());


			$tmp_webr->items_obj->updateValidByCheck($this->__getCheckPeriod());
			foreach($link_checker->checkWebResourceLinks() as $invalid)
			{
				$tmp_webr->items_obj->readItem($invalid['page_id']);
				$tmp_webr->items_obj->setActiveStatus(false);
				$tmp_webr->items_obj->setValidStatus(false);
				$tmp_webr->items_obj->setDisableCheckStatus(true);
				$tmp_webr->items_obj->setLastCheckDate(time());
				$tmp_webr->items_obj->update(false);
			}
			
			$tmp_webr->items_obj->updateLastCheck($this->__getCheckPeriod());

			foreach($link_checker->getLogMessages() as $message)
			{
				$this->log->write($message);
			}
		}
		return true;
	}


	function __getCheckPeriod()
	{
		global $ilias;

		switch($ilias->getSetting('cron_web_resource_check'))
		{
			case 1:
				$period = 24 * 60 * 60;
				break;

			case 2:
				$period = 7 * 24 * 60 * 60;
				break;

			case 3:
				$period = 30 * 7 * 24 * 60 * 60;
				break;

			case 4:
				$period = 4  * 30 * 7 * 24 * 60 * 60;
				break;

			default:
				$period = 0;
		}
		return $period;
	}
}
?>
