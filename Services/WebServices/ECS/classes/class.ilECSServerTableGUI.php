<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Table/classes/class.ilTable2GUI.php';

/**
 * Description of ilECSServerTableGUI
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesWebServicesECS
 */
class ilECSServerTableGUI extends ilTable2GUI
{
	/**
	 * Constructor
	 * @param object $a_parent_obj
	 * @param string $a_parent_cmd 
	 */
	public function  __construct($a_parent_obj, $a_parent_cmd = "")
	{
		$this->setId('ecs_server_list');
		parent::__construct($a_parent_obj, $a_parent_cmd);
	}

	/**
	 * Init Table
	 */
	public function initTable()
	{
		$this->setTitle($this->lng->txt('ecs_available_ecs'));
		$this->setRowTemplate('tpl.ecs_server_row.html','Services/WebServices/ECS');

		$this->addColumn($this->lng->txt('ecs_tbl_active'), '','1%');
		$this->addColumn($this->lng->txt('title'), '','80%');
		$this->addColumn($this->lng->txt('actions'), '', '19%');
	}

	/**
	 * Fill row
	 * @staticvar int $counter
	 * @param array $set 
	 */
	public function  fillRow($set)
	{
		global $ilCtrl;

		$ilCtrl->setParameter($this->getParentObject(),'server_id',$set['server_id']);
		$ilCtrl->setParameterByClass('ilecsmappingsettingsgui','server_id',$set['server_id']);

		if($set['active'])
		{
			$this->tpl->setVariable('IMAGE_OK',  ilUtil::getImagePath('icon_ok.svg'));
			$this->tpl->setVariable('TXT_OK', $this->lng->txt('ecs_activated'));
		}
		else
		{
			$this->tpl->setVariable('IMAGE_OK',  ilUtil::getImagePath('icon_not_ok.svg'));
			$this->tpl->setVariable('TXT_OK', $this->lng->txt('ecs_inactivated'));
		}
		
		$this->tpl->setVariable('VAL_TITLE', ilECSSetting::getInstanceByServerId($set['server_id'])->getTitle());
		$this->tpl->setVariable('LINK_EDIT', $ilCtrl->getLinkTarget($this->getParentObject(),'edit'));
		$this->tpl->setVariable('TXT_SRV_ADDR', $this->lng->txt('ecs_server_addr'));

		if(ilECSSetting::getInstanceByServerId($set['server_id'])->getServer())
		{
			$this->tpl->setVariable('VAL_DESC', ilECSSetting::getInstanceByServerId($set['server_id'])->getServer());
		}
		else
		{
			$this->tpl->setVariable('VAL_DESC', $this->lng->txt('ecs_not_configured'));
		}

		$dt = ilECSSetting::getInstanceByServerId($set['server_id'])->fetchCertificateExpiration();
		if($dt != NULL)
		{
			$this->tpl->setVariable('TXT_CERT_VALID', $this->lng->txt('ecs_cert_valid_until'));
			
			$now = new ilDateTime(time(),IL_CAL_UNIX);
			$now->increment(IL_CAL_MONTH, 2);
			
			if(ilDateTime::_before($dt, $now))
			{
				$this->tpl->setCurrentBlock('invalid');
				$this->tpl->setVariable('VAL_ICERT',  ilDatePresentation::formatDate($dt));
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				$this->tpl->setCurrentBlock('valid');
				$this->tpl->setVariable('VAL_VCERT',  ilDatePresentation::formatDate($dt));
				$this->tpl->parseCurrentBlock();
			}
		}

		// Actions
		include_once './Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php';
		$list = new ilAdvancedSelectionListGUI();
		$list->setSelectionHeaderClass('small');
		$list->setItemLinkClass('small');
		$list->setId('actl_'.$set['server_id']);
		$list->setListTitle($this->lng->txt('actions'));

		if(ilECSSetting::getInstanceByServerId($set['server_id'])->isEnabled())
		{
			$list->addItem($this->lng->txt('ecs_deactivate'), '', $ilCtrl->getLinkTarget($this->getParentObject(),'deactivate'));
		}
		else
		{
			$list->addItem($this->lng->txt('ecs_activate'), '', $ilCtrl->getLinkTarget($this->getParentObject(),'activate'));
		}

		$list->addItem($this->lng->txt('edit'), '', $ilCtrl->getLinkTarget($this->getParentObject(),'edit'));
		$list->addItem($this->lng->txt('copy'), '', $ilCtrl->getLinkTarget($this->getParentObject(),'cp'));
		$list->addItem($this->lng->txt('delete'), '', $ilCtrl->getLinkTarget($this->getParentObject(),'delete'));
		$this->tpl->setVariable('ACTIONS',$list->getHTML());
		
		$ilCtrl->clearParameters($this->getParentObject());
	}

	/**
	 * Parse available servers
	 * @param ilECSServerSettings $servers
	 */
	public function parse(ilECSServerSettings $servers)
	{
		$rows = array();
		foreach($servers->getServers() as $server_id => $server)
		{
			$tmp['server_id'] = $server->getServerId();
			$tmp['active'] = $server->isEnabled();

			$rows[] = $tmp;
		}
		$this->setData($rows);
	}

}
?>
