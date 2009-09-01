<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Table/classes/class.ilTable2GUI.php';
include_once './Modules/WebResource/classes/class.ilLinkResourceItems.php';


/**
* TableGUI class for search results
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ModulesWebResource
*/
class ilWebResourceEditableLinkTableGUI extends ilTable2GUI
{
	protected $web_res = null;
	protected $invalid = array();

	/**
	 * Constructor	
	 */
	public function __construct($a_parent_obj,$a_parent_cmd)
	{
		global $lng,$ilAccess,$ilCtrl;
		
		parent::__construct($a_parent_obj,$a_parent_cmd);
		
		// Initialize
		$this->web_res = new ilLinkResourceItems($this->getParentObject()->object->getId());
		
		
		$this->setTitle($lng->txt('webr_edit_links'));
		
		$this->addColumn('','','1px');
		$this->addColumn($this->lng->txt('title'),'title','25%');
		$this->addColumn($this->lng->txt('target'),'target','25%');
		$this->addColumn($this->lng->txt('valid'),'valid','10px');
		$this->addColumn($this->lng->txt('webr_active'),'active','10px');
		$this->addColumn($this->lng->txt('webr_disable_check'),'disable_check','10px');
		#$this->addColumn('','','10px');
		
		// TODO: Dynamic fields
		
		// TODO: sorting
		$this->setDefaultOrderField('title');
		
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($this->getParentObject()));
		$this->setRowTemplate("tpl.webr_editable_link_row.html", 'Modules/WebResource');
		$this->setEnableTitle(true);
		$this->setEnableNumInfo(true);
		$this->setSelectAllCheckbox('link_ids');
		
		$this->addMultiCommand('confirmDeleteLink', $this->lng->txt('delete'));
		$this->addCommandButton('updateLinks', $this->lng->txt('save'));
	}
	
	/**
	 * Invalid links
	 * @param object $a_links
	 * @return 
	 */
	public function setInvalidLinks($a_links)
	{
		$this->invalid = $a_links;
	}
	
	/**
	 * Get invalid links
	 * @return 
	 */
	public function getInvalidLinks()
	{
		return $this->invalid ? $this->invalid : array();
	}
	
	/**
	 * Parse selected items
	 * @param array $a_link_ids
	 * @return 
	 */
	public function parseSelectedLinks($a_link_ids)
	{
		$rows = array();
		foreach($a_link_ids as $link_id)
		{
			$link = $this->getWebResourceItems()->getItem($link_id);

			$tmp['id'] = $link['link_id'];
			$tmp['title'] = $link['title'];
			$tmp['description'] = $link['description'];
			$tmp['target'] = $link['target'];
			$tmp['link_id'] = $link['link_id'];
			$tmp['active'] = $link['active'];
			$tmp['disable_check'] = $link['disable_check'];
			$tmp['valid'] = $link['valid'];
			$tmp['last_check'] = $link['last_check'];
			
			$rows[] = $tmp;
		}
		$this->setData($rows);
	}
	
	public function updateFromPost()
	{
		$rows = array();
		foreach($this->getData() as $link)
		{
			$link_id = $link['id'];
			
			$tmp = $link;
			$tmp['title'] = $_POST['links'][$link_id]['tit'];
			$tmp['description'] = $_POST['links'][$link_id]['des'];
			$tmp['target'] = $_POST['links'][$link_id]['tar'];
			$tmp['valid'] = $_POST['links'][$link_id]['vali'];
			$tmp['disable_check'] = $_POST['links'][$link_id]['che'];
			$tmp['active'] = $_POST['links'][$link_id]['act'];
			
			$rows[] = $tmp;
		}
		$this->setData($rows);
	}
	
	
	/**
	 * Parse Links
	 * @return 
	 */
	public function parse()
	{
		$rows = array();
		foreach($this->getWebResourceItems()->getAllItems() as $link)
		{
			$tmp['id'] = $link['link_id'];
			$tmp['title'] = $link['title'];
			$tmp['description'] = $link['description'];
			$tmp['target'] = $link['target'];
			$tmp['link_id'] = $link['link_id'];
			$tmp['active'] = $link['active'];
			$tmp['disable_check'] = $link['disable_check'];
			$tmp['valid'] = $link['valid'];
			$tmp['last_check'] = $link['last_check'];
			
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
		
		if(in_array($a_set['id'], $this->getInvalidLinks()))
		{
			$this->tpl->setVariable('CSS_ROW','warn');
		}
		
		// Check
		$this->tpl->setVariable('VAL_ID',$a_set['id']);
		$this->tpl->setVariable('VAL_CHECKBOX',
			ilUtil::formCheckbox(false, 'link_ids[]',$a_set['id'])
		);
		
		// Column title
		$this->tpl->setVariable('TXT_TITLE',$this->lng->txt('title'));
		$this->tpl->setVariable('VAL_TITLE',$a_set['title']);
		$this->tpl->setVariable('TXT_DESC',$this->lng->txt('description'));
		$this->tpl->setVariable('VAL_DESC',$a_set['description']);
		
		// Column Target
		$this->tpl->setVariable('TXT_TARGET',$this->lng->txt('target'));
		$this->tpl->setVariable('VAL_TARGET',$a_set['target']);
		
		$this->tpl->setVariable('TXT_LAST_CHECK',$this->lng->txt('webr_last_check_table'));
		$this->tpl->setVariable('LAST_CHECK',
			$a_set['last_check'] ?
			ilDatePresentation::formatDate(new ilDateTime($a_set['last_check'],IL_CAL_UNIX)) :
			$this->lng->txt('no_date')
		);		
		
		// Valid
		$this->tpl->setVariable('VAL_VALID',
			ilUtil::formCheckbox($a_set['valid'], 'links['.$a_set['id'].'][vali]', 1)
		);
		
		// Active
		$this->tpl->setVariable('VAL_ACTIVE',
			ilUtil::formCheckbox($a_set['active'], 'links['.$a_set['id'].'][act]', 1)
		);

		// Valid
		$this->tpl->setVariable('VAL_CHECK',
			ilUtil::formCheckbox($a_set['disable_check'], 'links['.$a_set['id'].'][che]', 1)
		);
		
		// Edit
		/*
		$ilCtrl->setParameterByClass(get_class($this->getParentObject()), 'link_id', $a_set['id']);
		$this->tpl->setVariable('EDIT_LINK',
			$ilCtrl->getLinkTargetByClass(get_class($this->getParentObject()),'editLink')
		);
		$ilCtrl->clearParametersByClass(get_class($this->getParentObject()));
		$this->tpl->setVariable('EDIT_ALT',$this->lng->txt('edit'));
		*/
		
	}
	
	
	
	
	/**
	 * Get Web resource items object
	 * @return object	ilLinkResourceItems
	 */
	protected function getWebResourceItems()
	{
		return $this->web_res;
	}
}
?>