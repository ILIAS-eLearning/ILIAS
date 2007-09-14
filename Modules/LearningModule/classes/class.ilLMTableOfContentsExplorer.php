<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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

require_once("./Modules/LearningModule/classes/class.ilLMExplorer.php");
require_once("./Modules/LearningModule/classes/class.ilStructureObject.php");

/*
* Table of Contents Explorer
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesIliasLearningModule
*/
class ilTableOfContentsExplorer extends ilLMExplorer
{

	/**
	 * id of root folder
	 * @var int root folder id
	 * @access private
	 */
	var $root_id;
	var $output;
	var $offline;

	/**
	* Constructor
	* @access	public
	* @param	string	scriptname
	* @param    int user_id
	*/
	function ilTableOfContentsExplorer($a_target,&$a_lm_obj, $a_export_format = "")
	{
		parent::ilLMExplorer($a_target, $a_lm_obj);
		$this->setSessionExpandVariable("lmtocexpand");
		$this->export_format = $a_export_format;
		$this->lm_obj =& $a_lm_obj;

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
		//$showViewInFrameset = $this->ilias->ini->readVariable("layout","view_target") == "frame";
		$showViewInFrameset = true;

		if ($showViewInFrameset)
		{
			$this->setFrameTarget(ilFrameTargetInfo::_getFrame("MainContent"));
		}
		else
		{
			$this->setFrameTarget("_top");
		}

	}

	/**
	* set offline mode
	*/
	function setOfflineMode($a_offline = true)
	{
		$this->offline = $a_offline;

		if ($a_offline)
		{
			if ($this->export_format == "scorm")
			{
				$this->setFrameTarget("");
			}
		}

	}

	/**
	* get offline mode
	*/
	function offlineMode()
	{
		return $this->offline;
	}

	/**
	* standard implementation for title, maybe overwritten by derived classes
	*/
	function buildTitle($a_title, $a_id, $a_type)
	{
		global $lng;

		include_once("./Modules/LearningModule/classes/class.ilObjContentObject.php");
		$access_str = "";
		if (!ilObjContentObject::_checkPreconditionsOfPage(
			$_GET['ref_id'],ilObject::_lookupObjId($_GET["ref_id"]), $a_id))
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

	/**
	* get target frame
	*/
	function buildFrameTarget($a_type, $a_child = 0, $a_obj_id = 0)
	{
		// Determine whether the view of a learning resource should
		// be shown in the frameset of ilias, or in a separate window.
		//$showViewInFrameset = $this->ilias->ini->readVariable("layout","view_target") == "frame";
		$showViewInFrameset = true;

		if ($this->offlineMode() &&
			$this->export_format == "scorm")
		{
			return "";
		}

		if ($showViewInFrameset && !$this->offlineMode())
		{
			return ilFrameTargetInfo::_getFrame("MainContent");
		}
		else
		{
			return "_top";
		}
	}

	/**
	* build link target
	*/
	function buildLinkTarget($a_node_id, $a_type)
	{
		if (!$this->offlineMode())
		{
			return parent::buildLinkTarget($a_node_id, $a_type);
		}
		else
		{
			if ($a_node_id < 1)
			{
				$a_node_id = $this->tree->getRootId();
			}
			if ($a_type != "pg")
			{
				$a_node = $this->tree->fetchSuccessorNode($a_node_id, "pg");
				$a_node_id = $a_node["child"];
			}

			if ($nid = ilLMObject::_lookupNID($this->lm_obj->getId(), $a_node_id, "pg"))
			{
				return "lm_pg_".$nid.".html";
			}
			else
			{
				return "lm_pg_".$a_node_id.".html";
			}
		}
	}

	/*function isClickable($a_type, $a_obj_id)
	{
		return true;
	}*/

	/**
	* get image path (may be overwritten by derived classes)
	*/
	function getImage($a_name)
	{
		return ilUtil::getImagePath($a_name, false, "output", $this->offlineMode());
	}
	
	/**
	* force expansion of node
	*/
	function forceExpanded($a_obj_id)
	{
		if ($this->offlineMode())
		{
			return true;
		}
		else
		{
			return true;
		}
	}

	function isVisible($a_id, $a_type)
	{
		if(!ilLMObject::_lookupActive($a_id))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

} // END class ilTableOfContentsExplorer
?>
