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

require_once ("./Modules/ScormAicc/classes/AICC/class.ilAICCObjectGUI.php");
require_once ("./Modules/ScormAicc/classes/AICC/class.ilAICCBlock.php");

/**
* GUI class for AICC Unit element
*
* @version $Id$
*
* @ingroup ModulesScormAicc
*/
class ilAICCBlockGUI extends ilAICCObjectGUI
{

	function ilAICCBlockGUI($a_id)
	{
		parent::ilAICCObjectGUI();
		$this->ac_object =& new ilAICCBlock($a_id);
	}

	function view()
	{

		$this->tpl = new ilTemplate("tpl.main.html", true, true);
		
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.aicc_block.html", "Modules/ScormAicc");
		$this->tpl->setVariable("BLOCK_TITLE", $this->ac_object->getTitle() );
		$this->tpl->setVariable("BLOCK_DESCRIPTION", $this->ac_object->getDescription() );
		$this->tpl->parseCurrentBlock();
		
	}
}
?>