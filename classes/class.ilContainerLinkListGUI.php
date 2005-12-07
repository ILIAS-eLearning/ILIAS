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
* Class ilContainerLinkListGUI
*
* @author Stefan Meyer <alex.killing@gmx.de>

* @version $Id$
*
* @ilCtrl_Calls ilContainerLinkListGUI:
* 
* @package ilias-core
*/

class ilContainerLinkListGUI
{
	var $ctrl;

	/**
	* Constructor
	* @access public
	*/
	function ilContainerLinkListGUI()
	{
		global $ilCtrl;
		
		$this->ctrl =& $ilCtrl;
	}

	function &executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		//$this->prepareOutput();

		switch($next_class)
		{			
			default:
				$this->$cmd();

				break;
		}
		return true;
	}
	
	function show()
	{
		global $lng;
		
		$tpl = new ilTemplate("tpl.container_link_help.html", true, true);
		
		$type_ordering = array(
			"cat", "fold", "crs", "icrs", "icla", "grp", "chat", "frm", "lres",
			"glo", "webr", "file", "exc",
			"tst", "svy", "mep", "qpl", "spl");
			
		$tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
		$tpl->setVariable("TXT_HELP_HEADER", $lng->txt("help"));
		foreach($type_ordering as $type)
		{
			$tpl->setCurrentBlock("row");
			$tpl->setVariable("ROWCOL", "tblrow".((($i++)%2)+1));
			if ($type != "lres")
			{
				$tpl->setVariable("TYPE", $lng->txt("objs_".$type));
			}
			else
			{
				$tpl->setVariable("TYPE", $lng->txt("learning_resources"));
			}
			$tpl->setVariable("TXT_LINK", "++".$type."++");
			$tpl->parseCurrentBlock();
		}
		$tpl->show();
		exit;

	}

} // END class.ilContainerLinkListGUI
?>
