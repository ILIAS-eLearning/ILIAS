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
* Class ilLPListOfProgress
*
* @author Stefan Meyer <smeyer@databay.de>
*
* @version $Id$
*
* @package ilias-tracking
*
*/

include_once './Services/Tracking/classes/class.ilLearningProgressBaseGUI.php';

class ilLPListOfProgressGUI extends ilLearningProgressBaseGUI
{
	function ilLPListOfProgressGUI($a_mode,$a_ref_id)
	{
		parent::ilLearningProgressBaseGUI($a_mode,$a_ref_id);
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		$this->ctrl->setReturn($this, "");

		switch($this->ctrl->getNextClass())
		{
			default:
				$cmd = $this->__getDefaultCommand();
				$this->$cmd();

		}
		return true;
	}

	function show()
	{
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.lp_list_progress.html','Services/Tracking');
		$this->__setSubTabs(LP_ACTIVE_PROGRESS);
	}
}
?>