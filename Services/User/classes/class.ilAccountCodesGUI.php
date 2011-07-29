<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* GUI class for account codes
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id$
*
* @ilCtrl_Calls ilAccountCodesGUI:
* @ingroup ServicesUser
*/
class ilAccountCodesGUI 
{	
	protected $ref_id; // [int]
	
	/**
	 * Constructor
	 * 
	 * @param int $a_ref_id
	 */
	function __construct($a_ref_id)
	{
		global $lng;

		$this->ref_id = $a_ref_id;
		$lng->loadLanguageModule("user");
	}
	
	function executeCommand()
	{
		global $ilCtrl;
		
		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd();
		
		switch($next_class)
		{
			default:
				if(!$cmd)
				{
					$cmd = "listCodes";
				}
				$this->$cmd();
				break;
		}
		
		return true;
	}
	
	function listCodes()
	{
		global $ilAccess, $ilErr, $ilCtrl, $ilToolbar, $lng, $tpl;

		if(!$ilAccess->checkAccess('read','',$this->ref_id))
		{
			$ilErr->raiseError($lng->txt("msg_no_perm_read"),$ilErr->MESSAGE);
		}

		$ilToolbar->addButton($lng->txt("user_account_codes_add"),
			$ilCtrl->getLinkTarget($this, "addCodes"));

		include_once("./Services/User/classes/class.ilAccountCodesTableGUI.php");
		$ctab = new ilAccountCodesTableGUI($this, "listCodes");
		$tpl->setContent($ctab->getHTML());
	}
	
	function initAddCodesForm()
	{
		global $ilCtrl, $lng;
		
		include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';

		$this->form_gui = new ilPropertyFormGUI();
		$this->form_gui->setFormAction($ilCtrl->getFormAction($this, 'createCodes'));
		$this->form_gui->setTitle($lng->txt('user_account_codes_edit_header'));
		
		$count = new ilNumberInputGUI($lng->txt('user_account_codes_number'), 'acc_codes_number');
		$count->setSize(4);
		$count->setMaxLength(4);
		$count->setMinValue(1);
		$count->setMaxValue(1000);
		$count->setRequired(true);
		$this->form_gui->addItem($count);

		$this->form_gui->addCommandButton('createCodes', $lng->txt('create'));
		$this->form_gui->addCommandButton('listCodes', $lng->txt('cancel'));
	}
	
	function addCodes()
	{
		global $ilAccess, $ilErr, $tpl, $lng;

		if(!$ilAccess->checkAccess('write', '', $this->ref_id))
		{
			$ilErr->raiseError($lng->txt("msg_no_perm_write"), $ilErr->MESSAGE);
		}
	
		$this->initAddCodesForm();
		$tpl->setContent($this->form_gui->getHTML());
	}
	
	function createCodes()
	{
		global $ilAccess, $ilErr, $lng, $tpl;

		if(!$ilAccess->checkAccess('write', '', $this->ref_id))
		{
			$ilErr->raiseError($lng->txt("msg_no_perm_write"), $ilErr->MESSAGE);
		}
		
		$this->initAddCodesForm();
		if($this->form_gui->checkInput())
		{
			$number = $this->form_gui->getInput('acc_codes_number');
			
			include_once './Services/User/classes/class.ilAccountCode.php';
			
			$stamp = time();
			for($loop = 1; $loop <= $number; $loop++)
			{
				ilAccountCode::create($role, $stamp);
			}
			
			ilUtil::sendSuccess($lng->txt('saved_successfully'), true);
			$this->ctrl->redirect($this, "listCodes");
		}
		else
		{
			$this->form_gui->setValuesByPost();
			$tpl->setContent($this->form_gui->getHtml());
		}
	}
	
	function deleteCodes()
	{
		global $lng, $ilCtrl;
		
		include_once './Services/User/classes/class.ilAccountCode.php';
		ilAccountCode::deleteCodes($_POST["id"]);
		
		ilUtil::sendSuccess($lng->txt('info_deleted'), true);
		$ilCtrl->redirect($this, "listCodes");
	}

	function deleteConfirmation()
	{
		global $ilErr, $lng, $ilCtrl, $tpl;

		if(!isset($_POST["id"]))
		{
			$ilErr->raiseError($lng->txt("no_checkbox"), $ilErr->MESSAGE);
		}
	
		include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
		$gui = new ilConfirmationGUI();
		$gui->setHeaderText($lng->txt("info_delete_sure"));
		$gui->setCancel($lng->txt("cancel"), "listCodes");
		$gui->setConfirm($lng->txt("confirm"), "deleteCodes");
		$gui->setFormAction($ilCtrl->getFormAction($this, "deleteCodes"));
		
		include_once './Services/User/classes/class.ilAccountCode.php';
		$data = ilAccountCode::loadCodesByIds($_POST["id"]);
		foreach($data as $code)
		{
			$gui->addItem("id[]", $code["code_id"], $code["code"]);
		}

		$tpl->setContent($gui->getHTML());
	}
	
	function resetCodesFilter()
	{
		include_once("./Services/User/classes/class.ilAccountCodesTableGUI.php");
		$utab = new ilAccountCodesTableGUI($this, "listCodes");
		$utab->resetOffset();
		$utab->resetFilter();
		
		$this->listCodes();
	}
	
	function applyCodesFilter()
	{
		include_once("./Services/User/classes/class.ilAccountCodesTableGUI.php");
		$utab = new ilAccountCodesTableGUI($this, "listCodes");
		$utab->resetOffset();
		$utab->writeFilterToSession();
		
		$this->listCodes();
	}
	
	function exportCodes()
	{
		global $ilAccess, $ilErr, $lng;

		if(!$ilAccess->checkAccess('read', '', $this->ref_id))
		{
			$ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->MESSAGE);
		}
		
		include_once("./Services/User/classes/class.ilAccountCodesTableGUI.php");
		$utab = new ilAccountCodesTableGUI($this, "listCodes");
		
		include_once './Services/User/classes/class.ilAccountCode.php';
		$codes = ilAccountCode::getCodesForExport($utab->filter["code"], $utab->filter["valid_until"], $utab->filter["generated"]);

		if(sizeof($codes))
		{
			// :TODO: add url/link to login?!
			ilUtil::deliverData(implode("\n", $codes), "ilias_account_codes_".date("d-m-Y").".txt","text/plain");
		}
		else
		{
			ilUtil::sendFailure($this->lng->txt("account_export_codes_no_data"));
			$this->listCodes();
		}
	}
}

?>