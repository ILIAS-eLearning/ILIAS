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

/*
* Table of Contents Explorer
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/

require_once("content/classes/class.ilLMExplorer.php");
require_once("content/classes/class.ilStructureObject.php");

class ilTableOfContentsExplorer extends ilLMExplorer
{

	/**
	 * id of root folder
	 * @var int root folder id
	 * @access private
	 */
	var $root_id;
	var $output;

	/**
	* Constructor
	* @access	public
	* @param	string	scriptname
	* @param    int user_id
	*/
	function ilTableOfContentsExplorer($a_target,&$a_lm_obj)
	{
		parent::ilLMExplorer($a_target, $a_lm_obj);
		$this->setExpandTarget("lm_presentation.php?cmd=".$_GET["cmd"]."&ref_id=".$this->lm_obj->getRefId());
		$this->setSessionExpandVariable("lmtocexpand");

		$this->addFilter("du");
		$this->addFilter("st");
		if ($a_lm_obj->getTOCMode() == "pages")
		{
			$this->addFilter("pg");
		}
		$this->setFiltered(true);
		$this->setFilterMode(IL_FM_POSITIVE);


                // Determine whether the view of a learning resource should
                // be shown in the frameset of ilias, or in a separate window.
                $showViewInFrameset = $this->ilias->ini->readVariable("layout","view_target") == "frame";

                if ($showViewInFrameset) 
                {
                    $this->setFrameTarget("bottom");
                }
                else
                {
                    $this->setFrameTarget("_top");
                }

	}

	/**
	* standard implementation for title, maybe overwritten by derived classes
	*/
	function buildTitle($a_title, $a_id, $a_type)
	{
		global $lng;
		
		include_once("content/classes/class.ilObjContentObject.php");
		$access_str = "";
		if (!ilObjContentObject::_checkPreconditionsOfPage(
			ilObject::_lookupObjId($_GET["ref_id"]), $a_id))
		{
			$access_str = " (".$lng->txt("cont_no_access").")";
		}
		
		if ($a_type == "st")
		{
			return ilStructureObject::_getPresentationTitle($a_id,
				$this->lm_obj->isActiveNumbering()).$access_str;
		}

		if ($this->lm_obj->getTOCMode() == "chapters" || $a_type != "pg")
		{
			return $a_title.$access_str;
		}
		else
		{
			if ($a_type == "pg")
			{
				return ilLMPageObject::_getPresentationTitle($a_id,
					$this->lm_obj->getPageHeader(), $this->lm_obj->isActiveNumbering()).$access_str;
			}
		}
		
	}

	function buildFrameTarget($a_type, $a_child = 0, $a_obj_id = 0)
	{
                // Determine whether the view of a learning resource should
                // be shown in the frameset of ilias, or in a separate window.
                $showViewInFrameset = $this->ilias->ini->readVariable("layout","view_target") == "frame";

                if ($showViewInFrameset) 
                {
                    return "bottom";
                }
                else
                {
                    return "_top";
                }
	}

	/*function isClickable($a_type, $a_obj_id)
	{
		return true;
	}*/


} // END class ilTableOfContentsExplorer
?>
