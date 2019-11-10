<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * TableGUI class for survey question source materials
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class SurveyMaterialsSourceTableGUI extends ilTable2GUI
{	
	public function __construct($a_parent_obj, $a_parent_cmd, $a_cancel_cmd)
	{
		global $DIC;

		$this->ctrl = $DIC->ctrl();
		$this->lng = $DIC->language();
		$ilCtrl = $DIC->ctrl();
		$lng = $DIC->language();
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
				
		$this->addColumn($lng->txt("title"), "title");
		$this->addColumn($lng->txt("action"), "");
		$this->setTitle($this->lng->txt('select_object_to_link'));
		
		$this->setLimit(9999);
		$this->disable("numinfo");
				
		$this->setRowTemplate("tpl.il_svy_qpl_material_source_row.html", "Modules/SurveyQuestionPool");		

		$this->setFormAction($ilCtrl->getFormAction($this->getParentObject(), $this->getParentCmd()));
		$this->addCommandButton($a_cancel_cmd, $this->lng->txt('cancel'));
		
		$this->setDefaultOrderField("title");
		$this->setDefaultOrderDirection("asc");
	}
	
	/**
	* Fill data row
	*/
	protected function fillRow($data)
	{
		$ilCtrl = $this->ctrl;
		$lng = $this->lng;
		
		$url_cmd = "add".strtoupper($data["item_type"]);
		$url_type = strtolower($data["item_type"]);
	
		$ilCtrl->setParameter($this->getParentObject(), $url_type, $data["item_id"]);
		$url = $ilCtrl->getLinkTarget($this->getParentObject(), $url_cmd).
		$ilCtrl->setParameter($this->getParentObject(), $url_type, "");
	
		$this->tpl->setVariable("TITLE", $data['title']);
		$this->tpl->setVariable("URL_ADD", $url);
		$this->tpl->setVariable("TXT_ADD", $lng->txt("add"));
	}
}
?>
