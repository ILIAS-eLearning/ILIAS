<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Table/classes/class.ilTable2GUI.php';
require_once 'Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php';
require_once 'Services/Saml/classes/class.ilSamlIdp.php';

/**
 * Class ilSamlIdpTableGUI
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilSamlIdpTableGUI extends ilTable2GUI
{
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * ilSamlIdpTableGUI constructor.
	 * @param        $a_parent_obj
	 * @param string $a_parent_cmd
	 * @param string $a_template_context
	 */
	public function __construct($a_parent_obj, $a_parent_cmd = "", $a_template_context = "")
	{
		/** @var $ilCtrl ilCtrl */
		global $ilCtrl;

		$this->setId('saml_idp_list');
		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);

		$this->ctrl = $ilCtrl;

		$this->setTitle($this->lng->txt('auth_saml_idps'));
		$this->setRowTemplate('tpl.saml_idp_row.html','Services/Saml');

		$this->addColumn($this->lng->txt('active'), '', '1%');
		$this->addColumn($this->lng->txt('title'), '','80%');
		$this->addColumn($this->lng->txt('actions'), '', '19%');

		$this->getItems();
	}

	/**
	 * 
	 */
	protected function getItems()
	{
		$idp_data = array();

		foreach(ilSamlIdp::getAllIdps() as $idp)
		{
			$idp_data[] = $idp->toArray();
		}

		$this->setData($idp_data);
	}

	/**
	 * @param array $a_set
	 */
	protected function fillRow($a_set)
	{
		if($a_set['is_active'])
		{
			$this->tpl->setVariable('IMAGE_OK',  ilUtil::getImagePath('icon_ok.svg'));
			$this->tpl->setVariable('TXT_OK', $this->lng->txt('active'));
		}
		else
		{
			$this->tpl->setVariable('IMAGE_OK',  ilUtil::getImagePath('icon_not_ok.svg'));
			$this->tpl->setVariable('TXT_OK', $this->lng->txt('inactive'));
		}

		$this->tpl->setVariable('NAME', $a_set['name']);

		$list = new ilAdvancedSelectionListGUI();
		$list->setSelectionHeaderClass('small');
		$list->setItemLinkClass('small');
		$list->setId('actl_' . $a_set['idp_id']);
		$list->setListTitle($this->lng->txt('actions'));

		$this->ctrl->setParameter($this->getParentObject(),'saml_idp_id', $a_set['idp_id']);
		$list->addItem($this->lng->txt('edit'), '', $this->ctrl->getLinkTarget($this->getParentObject(), 'showIdpSettings'));
		if($a_set['is_active'])
		{
			$list->addItem($this->lng->txt('deactivate'), '', $this->ctrl->getLinkTarget($this->getParentObject(), 'deactivateIdp'));
		}
		else
		{
			$list->addItem($this->lng->txt('activate'), '', $this->ctrl->getLinkTarget($this->getParentObject(), 'activateIdp'));
		}
		$this->ctrl->setParameter($this->getParentObject(),'saml_idp_id', '');

		$this->tpl->setVariable('ACTIONS',$list->getHTML());
	}
}