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

include_once('Services/CopyWizard/classes/class.ilCopyWizardOptions.php');

/** 
* @defgroup ServicesCopyWizard Services/CopyWizard
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ilCtrl_Calls 
* @ingroup 
*/

class ilCopyWizardPage
{
	private $type;
	private $source_id;
	private $obj_id;
	private $item_type;
	
	private $tree;
	private $lng;
	private $objDefinition;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function __construct($a_source_id,$a_item_type = '')
	{
		global $ilObjDataCache,$tree,$lng,$objDefinition;
		
		$this->source_id = $a_source_id;
		$this->item_type = $a_item_type;
		$this->obj_id = $ilObjDataCache->lookupObjId($this->source_id);
	 	$this->type = $ilObjDataCache->lookupType($this->obj_id);
	 	$this->tree = $tree;
	 	$this->lng = $lng;
	 	$this->objDefinition = $objDefinition;
	}
	
	/**
	 * Get wizard page tree presentation
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function buildTreePresentation()
	{
		include_once ("Services/CopyWizard/classes/class.ilCopyWizardExplorer.php");
		$exp = new ilCopyWizardExplorer("repository.php?cmd=goto");
		$exp->setExpandTarget("repository.php?cmd=showTree");
		$exp->setTargetGet("ref_id");
		$exp->setFilterMode(IL_FM_POSITIVE);
		$exp->forceExpandAll(true, false);
		$exp->addFilter("root");
		$exp->addFilter("cat");

		if ($_GET["expand"] == "")
		{
			$expanded = $this->tree->readRootId();
		}
		else
		{
			$expanded = $_GET["expand"];
		}

		$exp->setExpand($expanded);

		// build html-output
		$exp->setOutput(0);
		$output = $exp->getOutput();

		return $output;
	}
	
	
	/**
	 * Get wizard page block html
	 *
	 * @access public
	 * 
	 */
	public function getWizardPageBlockHTML()
	{
		$this->readItems();
		
		if(!count($this->items))
		{
			return '';
		}
		
	 	$this->initTemplate();
	 	$this->fillItemBlock();
	 	$this->fillMainBlock();
	 	$this->fillAdditionalOptions();
	 	
	 	return $this->tpl->get();
	}
	
	/**
	 * init template
	 *
	 * @access protected
	 */
	protected function initTemplate()
	{
		$this->tpl = new ilTemplate('tpl.copy_wizard_block.html',true,true,'Services/CopyWizard');
	}
	
	/**
	 * 
	 *
	 * @access protected
	 */
	protected function fillMainBlock()
	{
		
		if(count($this->items) > 1)
		{	
			$this->tpl->setCurrentBlock('obj_options');
			$this->tpl->setVariable('NAME_OPTIONS',$this->lng->txt('omit_all'));
			$this->tpl->setVariable('JS_FIELD',$this->item_type.'_'.ilCopyWizardOptions::COPY_WIZARD_OMIT);
			$this->tpl->setVariable('JS_TYPE',$this->item_type.'_omit');
			$this->tpl->parseCurrentBlock();
			
			$this->tpl->setCurrentBlock('obj_options');
			$this->tpl->setVariable('NAME_OPTIONS',$this->lng->txt('copy_all'));
			$this->tpl->setVariable('OBJ_CHECKED','checked="checked"');
			$this->tpl->setVariable('JS_FIELD',$this->item_type.'_'.ilCopyWizardOptions::COPY_WIZARD_COPY);
			$this->tpl->setVariable('JS_TYPE',$this->item_type.'_copy');
			$this->tpl->parseCurrentBlock();
	
			if($this->objDefinition->allowLink($this->item_type))
			{
				$this->tpl->setCurrentBlock('obj_options');
				$this->tpl->setVariable('NAME_OPTIONS',$this->lng->txt('link_all'));
				$this->tpl->setVariable('JS_FIELD',$this->item_type.'_'.ilCopyWizardOptions::COPY_WIZARD_LINK);
				$this->tpl->setVariable('JS_TYPE',$this->item_type.'_link');
				$this->tpl->parseCurrentBlock();
				
			}
			$this->tpl->setVariable('OPTION_CLASS','option_value');
		}
		else
		{
			$this->tpl->setVariable('OPTION_CLASS','option');
		}		
		$this->tpl->setVariable('OBJ_IMG',ilUtil::getImagePath('icon_'.$this->item_type.'.gif'));
		$this->tpl->setVariable('OBJ_ALT',$this->lng->txt('objs_'.$this->item_type));
		$this->tpl->setVariable('ROWSPAN',count($this->items) + 1);
	}
	
	/**
	 * Fill item block
	 *
	 * @access protected
	 */
	protected function fillItemBlock()
	{
		foreach($this->items as $node)
		{
			$this->tpl->setCurrentBlock('item_options');
			$this->tpl->setVariable('ITEM_CHECK_NAME','cp_options['.$node['child'].'][type]');
			$this->tpl->setVariable('ITEM_VALUE',ilCopyWizardOptions::COPY_WIZARD_OMIT);
			$this->tpl->setVariable('ITEM_NAME_OPTION',$this->lng->txt('omit'));
			$this->tpl->setVariable('ITEM_ID',$this->item_type.'_'.ilCopyWizardOptions::COPY_WIZARD_OMIT);
			$this->tpl->parseCurrentBlock();
			
			$this->tpl->setCurrentBlock('item_options');
			$this->tpl->setVariable('ITEM_CHECK_NAME','cp_options['.$node['child'].'][type]');
			$this->tpl->setVariable('ITEM_VALUE',ilCopyWizardOptions::COPY_WIZARD_COPY);
			$this->tpl->setVariable('ITEM_NAME_OPTION',$this->lng->txt('copy'));
			$this->tpl->setVariable('ITEM_CHECKED','checked="checked"');
			$this->tpl->setVariable('ITEM_ID',$this->item_type.'_'.ilCopyWizardOptions::COPY_WIZARD_COPY);
			$this->tpl->parseCurrentBlock();
			
			if($this->objDefinition->allowLink($this->item_type))
			{
				$this->tpl->setCurrentBlock('item_options');
				$this->tpl->setVariable('ITEM_CHECK_NAME','cp_options['.$node['child'].'][type]');
				$this->tpl->setVariable('ITEM_VALUE',ilCopyWizardOptions::COPY_WIZARD_LINK);
				$this->tpl->setVariable('ITEM_NAME_OPTION',$this->lng->txt('link'));
				$this->tpl->setVariable('ITEM_ID',$this->item_type.'_'.ilCopyWizardOptions::COPY_WIZARD_LINK);
				$this->tpl->parseCurrentBlock();
			}
			
			
			$this->tpl->setCurrentBlock('item_row');
			$this->tpl->setVariable('ITEM_TITLE',$node['title']);
			$this->tpl->setVariable('DESCRIPTION',$node['description']);
			$this->tpl->parseCurrentBlock();
		}
	}
	
	/**
	 * Fill additional options
	 *
	 * @access protected
	 */
	protected function fillAdditionalOptions()
	{
	
	}
	
	/**
	 * Read items
	 *
	 * @access protected
	 */
	protected function readItems()
	{
		$nodes = $this->tree->getSubTree($this->tree->getNodeData($this->source_id),true,$this->item_type);
		
		$this->items = array();
		switch($nodes[0]['type'])
		{
			case 'fold':
			case 'grp':
			case 'crs':
			case 'cat':
				foreach($nodes as $node)
				{
					if($node['child'] != $this->source_id)
					{
						$this->items[] = $node;
					}
				}
				break;
			default:
				$this->items = $nodes;
				break;
		}
	}
}


?>