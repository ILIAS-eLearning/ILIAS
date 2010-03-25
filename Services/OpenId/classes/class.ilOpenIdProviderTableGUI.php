<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('Services/Table/classes/class.ilTable2GUI.php');
include_once './Services/OpenId/classes/class.ilOpenIdProviders.php';

/**
 * @classDescription Open ID provider table
 * 
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * 
 */
class ilOpenIdProviderTableGUI extends ilTable2GUI
{
	private $ctrl = null;

	/**
	 * Constructor
	 * @return 
	 */
	public function __construct($a_parent_class,$a_parent_cmd)
	{
	 	global $lng,$ilCtrl;
	 	
	 	$this->ctrl = $ilCtrl;

		parent::__construct($a_parent_class,$a_parent_cmd);
		
		$this->setTitle($this->lng->txt('auth_openid_provider_tbl'));
		
	 	$this->addColumn('','f',1);
	 	$this->addColumn($this->lng->txt('title'),'title',"50%");
	 	$this->addColumn($this->lng->txt('url'),'url',"40%");
	 	$this->addColumn('','',"10%");
	 	
		$this->setFormAction($this->ctrl->getFormAction($a_parent_class));
		$this->setRowTemplate("tpl.openid_provider_row.html","Services/OpenId");
		$this->setDefaultOrderField('title');
		$this->setDefaultOrderDirection("asc");
		
		$this->addMultiCommand('deleteProvider',$this->lng->txt('delete'));
		$this->addCommandButton('addProvider', $this->lng->txt('auth_openid_provider_add'));
		
		$this->setSelectAllCheckbox('provider_ids');
	}
	
	/**
	 * 
	 * @return 
	 */
	public function fillRow($set)
	{
		$this->tpl->setVariable('VAL_ID',$set['provider_id']);
		$this->tpl->setVariable('VAL_TITLE',$set['title']);
		$this->tpl->setVariable('VAL_URL',$set['url']);
		$this->tpl->setVariable('TXT_EDIT',$this->lng->txt('edit'));
		$this->ctrl->setParameter($this->getParentObject(),'provider_id',$set['provider_id']);
		$this->tpl->setVariable('EDIT_LINK',$this->ctrl->getLinkTarget($this->getParentObject(),'editProvider'));
		
	}
	
	/**
	 * Parse provider
	 * @return 
	 */
	public function parse()
	{
		$providers = ilOpenIdProviders::getInstance()->getProvider();
		
		$data = array();
		for($i = 0; $i < count($providers); $i++)
		{
			$data[$i]['provider_id'] = $providers[$i]->getId();
			$data[$i]['title'] = $providers[$i]->getName();
			$data[$i]['url'] = $providers[$i]->getURL();
		}
		$this->setData($data);
	}
}
?>
