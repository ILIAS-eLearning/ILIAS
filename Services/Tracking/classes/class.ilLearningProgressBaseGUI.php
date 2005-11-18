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
* Class ilObjUserTrackingGUI
*
* @author Stefan Meyer <smeyer@databay.de>
*
* @version $Id$
*
* @package ilias-tracking
*
*/

define("LP_MODE_PERSONAL_DESKTOP",1);
define("LP_MODE_ADMINISTRATION",2);
define("LP_MODE_REPOSITORY",3);

define("LP_ACTIVE_SETTINGS",1);
define("LP_ACTIVE_OBJECTS",2);
define("LP_ACTIVE_PROGRESS",3);

include_once 'Services/Tracking/classes/class.ilObjUserTracking.php';

/* Base class for all Learning progress gui classes.
 * Defines modes for presentation according to the context in which it was called
 * E.g: mode LP_MODE_PERSONAL_DESKTOP displays only listOfObjects.
 */

class ilLearningProgressBaseGUI 
{
	var $tpl = null;
	var $ctrl = null;
	var $lng = null;

	var $ref_id = 0;

	var $mode = 0;

	function ilLearningProgressBaseGUI($a_mode,$a_ref_id = 0)
	{
		global $tpl,$ilCtrl,$lng,$ilObjDataCache;

		$this->tpl =& $tpl;
		$this->ctrl =& $ilCtrl;
		$this->lng =& $lng;
		$this->lng->loadLanguageModule('trac');

		$this->mode = $a_mode;
		$this->ref_id = $a_ref_id;
		$this->obj_id = $ilObjDataCache->lookupObjId($this->ref_id);
	}
	
	function getMode()
	{
		return $this->mode;
	}

	function getRefId()
	{
		return $this->ref_id;
	}

	function getObjId()
	{
		return $this->obj_id;
	}

	// Protected
	function __getDefaultCommand()
	{
		if(strlen($cmd = $this->ctrl->getCmd()))
		{
			return $cmd;
		}
		return 'show';
	}

	function __setSubTabs($a_active)
	{
		include_once 'classes/class.ilTabsGUI.php';

		$tabs_gui = new ilTabsGUI();
		$tabs_gui->setSubTabs();

		switch($this->getMode())
		{
			case LP_MODE_PERSONAL_DESKTOP:

				$tabs_gui->addTarget('trac_progress',
									 $this->ctrl->getLinkTargetByClass('illplistofprogressgui',''),
									 "","","",$a_active == LP_ACTIVE_PROGRESS);
				$tabs_gui->addTarget('trac_objects',
									 $this->ctrl->getLinkTargetByClass("illplistofobjectsgui",''),
									 "","","",$a_active == LP_ACTIVE_OBJECTS);
				break;


			case LP_MODE_REPOSITORY:

				$tabs_gui->addTarget('trac_objects',
									 $this->ctrl->getLinkTargetByClass("illplistofobjectsgui",''),
									 "","","",$a_active == LP_ACTIVE_OBJECTS);
				$tabs_gui->addTarget('trac_settings',
									 $this->ctrl->getLinkTargetByClass('illplistofsettingsgui',''),
									 "","","",$a_active == LP_ACTIVE_SETTINGS);
				break;

			case LP_MODE_ADMINISTRATION:
				
				$tabs_gui->addTarget('trac_objects',
									 $this->ctrl->getLinkTargetByClass("illplistofobjectsgui",''),
									 "","","",$a_active == LP_ACTIVE_OBJECTS);
				$tabs_gui->addTarget('trac_progress',
									 $this->ctrl->getLinkTargetByClass('illplistofprogressgui',''),
									 "","","",$a_active == LP_ACTIVE_PROGRESS);
				break;

			default:
				die ('No valid mode given');
				break;
		}
		$this->tpl->setVariable("SUB_TABS", $tabs_gui->getHTML());

		return true;
	}

	function __buildFooter()
	{
		switch($this->getMode())
		{
			case LP_MODE_PERSONAL_DESKTOP:

				$this->tpl->show(true);
		}
	}

	function __buildHeader()
	{
		if($this->getMode() == LP_MODE_PERSONAL_DESKTOP)
		{
			$this->tpl->setCurrentBlock("header_image");
			$this->tpl->setVariable("IMG_HEADER", ilUtil::getImagePath("icon_pd_b.gif"));
			$this->tpl->parseCurrentBlock();
			$this->tpl->setVariable("HEADER",$this->lng->txt("personal_desktop"));
			
			// set locator
			$this->tpl->setVariable("TXT_LOCATOR", $this->lng->txt("locator"));
			$this->tpl->touchBlock("locator_separator");
			$this->tpl->setCurrentBlock("locator_item");
			$this->tpl->setVariable("ITEM", $this->lng->txt("personal_desktop"));
			$this->tpl->setVariable("LINK_ITEM",
									$this->ctrl->getLinkTargetByClass("ilpersonaldesktopgui"));
			$this->tpl->parseCurrentBlock();
			
			$this->tpl->setCurrentBlock("locator_item");
			$this->tpl->setVariable("ITEM", $this->lng->txt("learning_progress"));
			$this->tpl->setVariable("LINK_ITEM",
									$this->ctrl->getLinkTarget($this));
			$this->tpl->parseCurrentBlock();
		
			// catch feedback message
			sendInfo();
			// display infopanel if something happened
			infoPanel();
		}

	}
}
?>