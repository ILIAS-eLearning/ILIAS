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

require_once("./Modules/LearningModule/classes/class.ilLMTOCExplorer.php");
require_once("./Modules/LearningModule/classes/class.ilLMObject.php");

/**
 * Public Section Explorer
 *
 * @author Michael Jansen <mjasen@databay.de>
 *
 * @ingroup ModulesIliasLearningModule
 */
class ilShopPublicSectionSelector extends ilLMTOCExplorer
{
	/**
	 * id of root folder
	 * @var int root folder id
	 * @access private
	 */
	var $root_id;
	var $output;
	var $ctrl;

	var $selectable_type;
	var $ref_id;

	/**
	* Constructor
	* @access	public
	* @param	string	scriptname
	* @param    object  lm object
	* @param	string	gui class name
	*/
	public function __construct($a_target, $a_lm_obj, $a_gui_class)
	{
		global $ilCtrl;

		$this->ctrl = $ilCtrl;
		$this->gui_class = $a_gui_class;
		parent::ilLMTOCExplorer($a_target, $a_lm_obj);
		$this->setSessionExpandVariable('lmshoppublicselectorexpand');
		$this->setExpandTarget($a_target);		
		$this->setExpand((int)$_GET['lmshoppublicselectorexpand'] ? (int)$_GET['lmshoppublicselectorexpand'] : $this->tree->readRootId());	
	}
	
	public function buildFrameTarget($a_type, $a_child = 0, $a_obj_id = 0)
	{
		return '_top';
	}
	
	public function buildLinkTarget($a_node_id, $a_type)
	{
		if(!$this->offlineMode())
		{
			return parent::buildLinkTarget($a_node_id, $a_type);
		}
		else
		{
			if ($a_node_id < 1)
			{
				$a_node_id = $this->tree->getRootId();
			}
			if ($a_type != 'pg')
			{
				$a_node = $this->tree->fetchSuccessorNode($a_node_id, 'pg');
				$a_node_id = $a_node['child'];
			}
			if (!$this->lm_obj->cleanFrames())
			{
				return 'frame_'.$a_node_id.'_maincontent.html';
			}
			else
			{
				return 'lm_pg_'.$a_node_id.'.html';
			}
		}
	}
	
	public function isClickable($a_type, $a_node_id)
	{
		global $ilUser;
		
		if($a_type == 'st')
		{
			$a_node = $this->tree->fetchSuccessorNode($a_node_id, 'pg');
			$a_node_id = $a_node['child'];
			if ($a_node_id == 0)
			{
				return false;
			}
		}
		
		if($a_type == 'pg')
		{
			// check public area mode
			include_once('./Modules/LearningModule/classes/class.ilLMObject.php');
			if ($this->lm_obj->getPublicAccessMode() == 'selected'
				&& !ilLMObject::_isPagePublic($a_node_id))
			{
				return false;
			}
		}

		return true;
	}
	
	public function getOutput()
	{
		global $ilBench, $tpl;

		$ilBench->start('Explorer', 'getOutput');

		$this->format_options[0]['tab'] = array();

		$depth = $this->tree->getMaximumDepth();

		for ($i=0;$i<$depth;++$i)
		{
			$this->createLines($i);
		}
		
		// set global body class
		$tpl->setBodyClass("il_Explorer");
		
		$tpl_tree = new ilTemplate('tpl.tree_form.html', true, true, 'Modules/LearningModule');
		
		$cur_depth = -1;
		foreach ($this->format_options as $key => $options)
		{
//echo '-'.$options['depth'].'-';
			if (!$options['visible'])
			{
				continue;
			}
			
			if ($key == 0)
			{
				continue;
			}
			
			// end tags
			$this->handleListEndTags($tpl_tree, $cur_depth, $options['depth']);
			
			// start tags
			$this->handleListStartTags($tpl_tree, $cur_depth, $options['depth']);
			
			$cur_depth = $options['depth'];
			
			if ($options['visible'] and $key != 0)
			{
				$this->formatObject($tpl_tree, $options['child'], $options, $options['obj_id']);
			}									
		}
		
		$this->handleListEndTags($tpl_tree, $cur_depth, -1);
		
		$ilBench->stop('Explorer', 'getOutput');
		
		return $tpl_tree->get();
	}
}
?>
