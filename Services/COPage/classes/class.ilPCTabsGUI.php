<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/COPage/classes/class.ilPCTabs.php");
require_once("./Services/COPage/classes/class.ilPageContentGUI.php");

/**
* Class ilPCTabsGUI
*
* User Interface for Tabbed Content
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPCTabsGUI extends ilPageContentGUI
{
	/**
	* Constructor
	* @access	public
	*/
	function ilPCTabsGUI(&$a_pg_obj, &$a_content_obj, $a_hier_id, $a_pc_id = "")
	{
		parent::ilPageContentGUI($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);
	}
	
	/**
	* execute command
	*/
	function &executeCommand()
	{
		// get next class that processes or forwards current command
		$next_class = $this->ctrl->getNextClass($this);

		// get current command
		$cmd = $this->ctrl->getCmd();

		switch($next_class)
		{
			default:
				$ret =& $this->$cmd();
				break;
		}

		return $ret;
	}

	/**
	* Insert new tabs
	*/
	function insert($a_omit_form_init = false)
	{
		global $tpl;
		
		$this->displayValidationError();

		if (!$a_omit_form_init)
		{
			$this->initForm("create");
		}
		$html = $this->form->getHTML();
		$tpl->setContent($html);
	}

	/**
	* Edit tabs
	*/
	function editProperties()
	{
		global $ilCtrl, $lng, $tpl;
		
		$this->displayValidationError();
		$this->setTabs();
		
		$this->initForm();
		$this->getFormValues();
		$html = $this->form->getHTML();
		$tpl->setContent($html);
	}

	/**
	* Insert tabs form.
	*/
	function initForm($a_mode = "edit")
	{
		global $ilCtrl, $tpl, $lng;

		include_once("./Services/Accordion/classes/class.ilAccordionGUI.php");
		ilAccordionGUI::addCss();

		// edit form
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
		$this->form->setFormAction($ilCtrl->getFormAction($this));
		if ($a_mode != "edit")
		{
			$this->form->setTitle($lng->txt("cont_ed_insert_tabs"));
		}
		else
		{
			$this->form->setTitle($lng->txt("cont_edit_tabs"));
		}
		
		// tabs type
		/*$type_prop = new ilSelectInputGUI($lng->txt("cont_type"),
			"type");
		$types = array(ilPCTabs::ACCORDION_VER => $lng->txt("cont_tabs_acc_ver"),
			ilPCTabs::ACCORDION_HOR => $lng->txt("cont_tabs_acc_hor"));
		$type_prop->setOptions($types);
		$this->form->addItem($type_prop);*/
		
		$templ = $this->getTemplateOptions("vaccordion");

		require_once("./Services/Form/classes/class.ilAdvSelectInputGUI.php");
		$vchar_prop = new ilAdvSelectInputGUI($this->lng->txt("cont_characteristic"),
			"vaccord_templ");

		$vchars = array();
		foreach($templ as $k => $te)
		{
			$t = explode(":", $k);
			$html = $this->style->lookupTemplatePreview($t[1]).'<div style="clear:both" class="small">'.$te."</div>";
			$vchar_prop->addOption($k, $te, $html);
			if ($t[2] == "VerticalAccordion")
			{
				$vchar_prop->setValue($k);
			}
		}

		$templ = $this->getTemplateOptions("haccordion");
		$hchar_prop = new ilAdvSelectInputGUI($this->lng->txt("cont_characteristic"),
			"haccord_templ");
		$hchars = array();
		foreach($templ as $k => $te)
		{
			$t = explode(":", $k);
			$html = $this->style->lookupTemplatePreview($t[1]).'<div style="clear:both" class="small">'.$te."</div>";
			$hchar_prop->addOption($k, $te, $html);
			if ($t[2] == "HorizontalAccordion")
			{
				$hchar_prop->setValue($k);
			}
		}
		
		$radg = new ilRadioGroupInputGUI($lng->txt("cont_type"), "type");
		$radg->setValue(ilPCTabs::ACCORDION_VER);
		$op1 = new ilRadioOption($lng->txt("cont_tabs_acc_ver"), ilPCTabs::ACCORDION_VER);
		$op1->addSubItem($vchar_prop);
		$radg->addOption($op1);
		$op2 = new ilRadioOption($lng->txt("cont_tabs_acc_hor"), ilPCTabs::ACCORDION_HOR);
		$op2->addSubItem($hchar_prop);
		$radg->addOption($op2);
		$this->form->addItem($radg);
		
		
		// number of initial tabs
		if ($a_mode == "create")
		{
			$nr_prop = new ilSelectInputGUI($lng->txt("cont_number_of_tabs"),
				"nr");
			$nrs = array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 
				7 => 7, 8 => 8, 9 => 9, 10 => 10);
			$nr_prop->setOptions($nrs);
			$this->form->addItem($nr_prop);
		}
		
		$ni = new ilNumberInputGUI($this->lng->txt("cont_tab_cont_width"), "content_width");
		$ni->setMaxLength(4);
		$ni->setSize(4);
		$this->form->addItem($ni);
		
		$ni = new ilNumberInputGUI($this->lng->txt("cont_tab_cont_height"), "content_height");
		$ni->setMaxLength(4);
		$ni->setSize(4);
		$this->form->addItem($ni);

		// behaviour 
		$options = array(
			"AllClosed" => $lng->txt("cont_all_closed"),
			"FirstOpen" => $lng->txt("cont_first_open"),
			"ForceAllOpen" => $lng->txt("cont_force_all_open"),
			);
		$si = new ilSelectInputGUI($this->lng->txt("cont_behavior"), "behavior");
		$si->setOptions($options);
		$this->form->addItem($si);
		
		
		// alignment
		$align_opts = array("Left" => $lng->txt("cont_left"),
			"Right" => $lng->txt("cont_right"), "Center" => $lng->txt("cont_center"),
			"LeftFloat" => $lng->txt("cont_left_float"),
			"RightFloat" => $lng->txt("cont_right_float"));
		$align = new ilSelectInputGUI($this->lng->txt("cont_align"), "align");
		$align->setOptions($align_opts);
		$align->setValue("Center");
		$align->setInfo($lng->txt("cont_tabs_hor_align_info"));
		$this->form->addItem($align);

		// save/cancel buttons
		if ($a_mode == "create")
		{
			$this->form->addCommandButton("create_section", $lng->txt("save"));
			$this->form->addCommandButton("cancelCreate", $lng->txt("cancel"));
		}
		else
		{
			$this->form->addCommandButton("update", $lng->txt("save"));
			$this->form->addCommandButton("cancelUpdate", $lng->txt("cancel"));
		}
	}

	/**
	* Get form values
	*/
	function getFormValues()
	{
		$values["type"] = $this->content_obj->getTabType();
		$values["content_width"] = $this->content_obj->getContentWidth();
		$values["content_height"] = $this->content_obj->getContentHeight();
		$values["align"] = $this->content_obj->getHorizontalAlign();
		$values["behavior"] = $this->content_obj->getBehavior();
		$this->form->setValuesByArray($values);
		
		if ($values["type"] == ilPCTabs::ACCORDION_VER)
		{
			$va = $this->form->getItemByPostVar("vaccord_templ");
			$v = "t:".
				ilObjStyleSheet::_lookupTemplateIdByName($this->getStyleId(), $this->content_obj->getTemplate()).":".
				$this->content_obj->getTemplate();
			$va->setValue($v);
		}
		if ($values["type"] == ilPCTabs::ACCORDION_HOR)
		{
			$ha = $this->form->getItemByPostVar("haccord_templ");
			$v = "t:".
				ilObjStyleSheet::_lookupTemplateIdByName($this->getStyleId(), $this->content_obj->getTemplate()).":".
				$this->content_obj->getTemplate();
			$ha->setValue($v);
		}
	}

	/**
	* Create new tabs in dom and update page in db
	*/
	function create()
	{
		global $ilDB, $lng;
		
		$this->initForm("create");
		if ($this->form->checkInput())
		{
			$this->content_obj = new ilPCTabs($this->getPage());
			$this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);
			$this->content_obj->setTabType($_POST["type"]);
			$this->content_obj->setContentWidth($_POST["content_width"]);
			$this->content_obj->setContentHeight($_POST["content_height"]);
			$this->content_obj->setHorizontalAlign($_POST["align"]);
			$this->content_obj->setBehavior($_POST["behavior"]);
			for ($i = 0; $i < (int) $_POST["nr"]; $i++)
			{
				$this->content_obj->addTab($lng->txt("cont_new_tab"));
			}
			if ($_POST["type"] == ilPCTabs::ACCORDION_VER)
			{
				$t = explode(":", $_POST["vaccord_templ"]);
				$this->content_obj->setTemplate($t[2]);
			}
			if ($_POST["type"] == ilPCTabs::ACCORDION_HOR)
			{
				$t = explode(":", $_POST["haccord_templ"]);
				$this->content_obj->setTemplate($t[2]);
			}
			$this->updated = $this->pg_obj->update();

			if ($this->updated === true)
			{
				$this->afterCreation();
				//$this->ctrl->returnToParent($this, "jump".$this->hier_id);
			}
			else
			{
				$this->insert();
			}
		}
		else
		{
			$this->form->setValuesByPost();
			$this->insert(true);
//			return $this->form->getHtml();
		}
	}
	
	/**
	 * After creation processing
	 */
	function afterCreation()
	{
		global $ilCtrl;

		$this->pg_obj->stripHierIDs();
		$this->pg_obj->addHierIDs();
		$ilCtrl->setParameter($this, "hier_id", $this->content_obj->readHierId());
		$ilCtrl->setParameter($this, "pc_id", $this->content_obj->readPCId());
		$this->content_obj->setHierId($this->content_obj->readHierId());
		$this->setHierId($this->content_obj->readHierId());
		$this->content_obj->setPCId($this->content_obj->readPCId());
		$this->edit();
	}


	/**
	* Save tabs properties in db and return to page edit screen
	*/
	function update()
	{
		$this->initForm();
		if ($this->form->checkInput())
		{
			$this->content_obj->setTabType(ilUtil::stripSlashes($_POST["type"]));
			$this->content_obj->setContentWidth($_POST["content_width"]);
			$this->content_obj->setContentHeight($_POST["content_height"]);
			$this->content_obj->setHorizontalAlign($_POST["align"]);
			$this->content_obj->setTemplate("");
			$this->content_obj->setBehavior($_POST["behavior"]);
			if ($_POST["type"] == ilPCTabs::ACCORDION_VER)
			{
				$t = explode(":", $_POST["vaccord_templ"]);
				$this->content_obj->setTemplate($t[2]);
			}
			if ($_POST["type"] == ilPCTabs::ACCORDION_HOR)
			{
				$t = explode(":", $_POST["haccord_templ"]);
				$this->content_obj->setTemplate($t[2]);
			}
		}
		$this->updated = $this->pg_obj->update();
		if ($this->updated === true)
		{
			$this->ctrl->returnToParent($this, "jump".$this->hier_id);
		}
		else
		{
			$this->pg_obj->addHierIDs();
			$this->editProperties();
		}
	}
	
	//
	// Edit Tabs
	//
	
	
	/**
	* List all tabs
	*/
	function edit()
	{
		global $tpl, $ilTabs, $ilCtrl, $ilToolbar, $lng;

		$ilToolbar->addButton($lng->txt("cont_add_tab"),
			$ilCtrl->getLinkTarget($this, "addTab"));

		$this->setTabs();
		$ilTabs->activateTab("cont_tabs");
		include_once("./Services/COPage/classes/class.ilPCTabsTableGUI.php");
		$table_gui = new ilPCTabsTableGUI($this, "edit", $this->content_obj);
		$tpl->setContent($table_gui->getHTML());
	}
	
	/**
	* Save tabs properties in db and return to page edit screen
	*/
	function saveTabs()
	{
		global $ilCtrl, $lng;

		if (is_array($_POST["caption"]))
		{
			$captions = ilUtil::stripSlashesArray($_POST["caption"]);
			$this->content_obj->saveCaptions($captions);
		}
		if (is_array($_POST["position"]))
		{
			$positions = ilUtil::stripSlashesArray($_POST["position"]);
			$this->content_obj->savePositions($positions);
		}
		$this->updated = $this->pg_obj->update();
		ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
		$ilCtrl->redirect($this, "edit");
	}

	/**
	* Save tabs properties in db and return to page edit screen
	*/
	function addTab()
	{
		global $lng, $ilCtrl;
		
		$this->content_obj->addTab($lng->txt("cont_new_tab"));
		$this->updated = $this->pg_obj->update();

		ilUtil::sendSuccess($lng->txt("cont_added_tab"), true);
		$ilCtrl->redirect($this, "edit");
	}
	
	/**
	* Confirm tabs deletion
	*/
	function confirmTabsDeletion()
	{
		global $ilCtrl, $tpl, $lng;

		$this->setTabs();

		if (!is_array($_POST["tid"]) || count($_POST["tid"]) == 0)
		{
			ilUtil::sendInfo($lng->txt("no_checkbox"), true);
			$ilCtrl->redirect($this, "edit");
		}
		else
		{
			include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
			$cgui = new ilConfirmationGUI();
			$cgui->setFormAction($ilCtrl->getFormAction($this));
			$cgui->setHeaderText($lng->txt("cont_tabs_confirm_deletion"));
			$cgui->setCancel($lng->txt("cancel"), "cancelTabDeletion");
			$cgui->setConfirm($lng->txt("delete"), "deleteTabs");
			
			foreach ($_POST["tid"] as $k => $i)
			{
				$id = explode(":", $k);
				$cgui->addItem("tid[]", $k,
					$this->content_obj->getCaption($id[0], $id[1]));
			}
			
			$tpl->setContent($cgui->getHTML());
		}
	}
	
	/**
	* Cancel tab deletion
	*/
	function cancelTabDeletion()
	{
		global $ilCtrl;
		$ilCtrl->redirect($this, "edit");
	}
	
	/**
	* Delete Tabs
	*/
	function deleteTabs()
	{
		global $ilCtrl;
		
		if (is_array($_POST["tid"]))
		{
			foreach($_POST["tid"] as $tid)
			{
				$ids = explode(":", $tid);
				$this->content_obj->deleteTab($ids[0], $ids[1]);
			}
		}
		$this->updated = $this->pg_obj->update();
		
		$ilCtrl->redirect($this, "edit");
	}
	
	
	/**
	* Set tabs
	*/
	function setTabs()
	{
		global $ilTabs, $ilCtrl, $lng;

		$ilTabs->setBackTarget($lng->txt("pg"),
			$this->ctrl->getParentReturn($this));

		$ilTabs->addTarget("cont_tabs",
			$ilCtrl->getLinkTarget($this, "edit"), "edit",
			get_class($this));

		$ilTabs->addTarget("cont_edit_tabs",
			$ilCtrl->getLinkTarget($this, "editProperties"), "editProperties",
			get_class($this));

	}
}
?>
