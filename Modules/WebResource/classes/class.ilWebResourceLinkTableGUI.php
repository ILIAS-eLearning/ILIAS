<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Table/classes/class.ilTable2GUI.php';
include_once './Modules/WebResource/classes/class.ilLinkResourceItems.php';
include_once("./Services/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");


/**
* TableGUI class for search results
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ModulesWebResource
*/

class ilWebResourceLinkTableGUI extends ilTable2GUI
{
	protected $editable = false;
	protected $web_res = null;

	/**
	 * Constructor	
	 */
	public function __construct($a_parent_obj,$a_parent_cmd)
	{
		global $lng,$ilAccess,$ilCtrl;
		
		parent::__construct($a_parent_obj,$a_parent_cmd);
		
		// Initialize
		if($ilAccess->checkAccess('write','',$this->getParentObject()->object->getRefId()))
		{
			$this->editable = true;
		}
		$this->web_res = new ilLinkResourceItems($this->getParentObject()->object->getId());
		
		
		$this->setTitle($lng->txt('web_resources'));
		
		if($this->isEditable())
		{
			$this->addColumn($lng->txt('title'),'title','90%');
			$this->addColumn('','','10%');
		}
		else
		{
			$this->addColumn($lng->txt('title'),'title','100%');
		}
		
		$this->setDefaultOrderField('title');
		
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($this->getParentObject()));
		$this->setRowTemplate("tpl.webr_link_row.html", 'Modules/WebResource');
		$this->setEnableTitle(true);
		$this->setEnableNumInfo(false);
	}
	
	/**
	 * Parse Links
	 * @return 
	 */
	public function parse()
	{
		$rows = array();
		foreach($this->getWebResourceItems()->getActivatedItems() as $link)
		{
			if(ilParameterAppender::_isEnabled())
			{
				$link = ilParameterAppender::_append($link);
			}
			
			$tmp['title'] = $link['title'];
			$tmp['description'] = $link['description'];
			$tmp['target'] = $link['target'];
			$tmp['link_id'] = $link['link_id'];
			
			$rows[] = $tmp;
		}
		$this->setData($rows);
	}
	
	/**
	 * @see ilTable2GUI::fillRow()
	 */
	protected function fillRow($a_set)
	{
		global $ilCtrl,$lng;
		
		$this->tpl->setVariable('TITLE',$a_set['title']);
		if(strlen($a_set['description']))
		{
			$this->tpl->setVariable('DESCRIPTION',$a_set['description']);
		}
		$this->tpl->setVariable('TARGET',$a_set['target']);
		
		if(!$this->isEditable())
		{
			return;
		}
		
		$actions = new ilAdvancedSelectionListGUI();
		$actions->setSelectionHeaderClass("small");
		$actions->setItemLinkClass("xsmall");
		
		$actions->setListTitle($lng->txt('actions'));
		$actions->setId($a_set['link_id']);
		
		$ilCtrl->setParameterByClass(get_class($this->getParentObject()), 'link_id', $a_set['link_id']);
		$actions->addItem(
			$lng->txt('edit'),
			'',
			$ilCtrl->getLinkTargetByClass(get_class($this->getParentObject()),'editLink')
		);
		$actions->addItem(
			$lng->txt('webr_deactivate'),
			'',
			$ilCtrl->getLinkTargetByClass(get_class($this->getParentObject()),'deactivateLink')
		);
		$actions->addItem(
			$lng->txt('delete'),
			'',
			$ilCtrl->getLinkTargetByClass(get_class($this->getParentObject()),'confirmDeleteLink')
		);
		$this->tpl->setVariable('ACTION_HTML',$actions->getHTML());
	}
	
	
	
	
	/**
	 * Get Web resource items object
	 * @return object	ilLinkResourceItems
	 */
	protected function getWebResourceItems()
	{
		return $this->web_res;
	}
	
	
	/**
	 * Check if links are editable
	 * @return 
	 */
	protected function isEditable()
	{
		return (bool) $this->editable;
	}
}
?>