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
    public function __construct($a_ref_id)
    {
        global $DIC;

        $lng = $DIC['lng'];

        $this->ref_id = $a_ref_id;
        $lng->loadLanguageModule("user");
    }
    
    public function executeCommand()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        
        $next_class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd();
        
        switch ($next_class) {
            default:
                if (!$cmd) {
                    $cmd = "listCodes";
                }
                $this->$cmd();
                break;
        }
        
        return true;
    }
    
    public function listCodes()
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $ilErr = $DIC['ilErr'];
        $ilCtrl = $DIC['ilCtrl'];
        $ilToolbar = $DIC['ilToolbar'];
        $lng = $DIC['lng'];
        $tpl = $DIC['tpl'];

        if (!$ilAccess->checkAccess('read', '', $this->ref_id)) {
            $ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->MESSAGE);
        }

        include_once "Services/UIComponent/Button/classes/class.ilLinkButton.php";
        $button = ilLinkButton::getInstance();
        $button->setCaption("user_account_codes_add");
        $button->setUrl($ilCtrl->getLinkTarget($this, "addCodes"));
        $ilToolbar->addButtonInstance($button);
        
        include_once("./Services/User/classes/class.ilAccountCodesTableGUI.php");
        $ctab = new ilAccountCodesTableGUI($this, "listCodes");
        $tpl->setContent($ctab->getHTML());
    }
    
    public function initAddCodesForm()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        
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
        
        $valid = new ilRadioGroupInputGUI($lng->txt('user_account_code_valid_until'), 'valid_type');
        $valid->setRequired(true);
        
        $unl = new ilRadioOption($lng->txt('user_account_code_valid_until_unlimited'), 'valid_unlimited');
        $valid->addOption($unl);
        
        $st = new ilRadioOption($lng->txt('user_account_code_valid_until_static'), 'valid_static');
        $valid->addOption($st);
        
        $dt = new ilDateTimeInputGUI($lng->txt('date'), 'valid_date');
        $dt->setRequired(true);
        $st->addSubItem($dt);
        
        $dyn = new ilRadioOption($lng->txt('user_account_code_valid_until_dynamic'), 'valid_dynamic');
        $valid->addOption($dyn);
        
        $ds = new ilNumberInputGUI($lng->txt('days'), 'valid_days');
        $ds->setSize(5);
        $ds->setRequired(true);
        $dyn->addSubItem($ds);
                    
        $this->form_gui->addItem($valid);

        $this->form_gui->addCommandButton('createCodes', $lng->txt('create'));
        $this->form_gui->addCommandButton('listCodes', $lng->txt('cancel'));
    }
    
    public function addCodes()
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $ilErr = $DIC['ilErr'];
        $tpl = $DIC['tpl'];
        $lng = $DIC['lng'];

        if (!$ilAccess->checkAccess('write', '', $this->ref_id)) {
            $ilErr->raiseError($lng->txt("msg_no_perm_write"), $ilErr->MESSAGE);
        }
    
        $this->initAddCodesForm();
        $tpl->setContent($this->form_gui->getHTML());
    }
    
    public function createCodes()
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $ilErr = $DIC['ilErr'];
        $lng = $DIC['lng'];
        $tpl = $DIC['tpl'];
        $ilCtrl = $DIC['ilCtrl'];

        if (!$ilAccess->checkAccess('write', '', $this->ref_id)) {
            $ilErr->raiseError($lng->txt("msg_no_perm_write"), $ilErr->MESSAGE);
        }
        
        $this->initAddCodesForm();
        if ($this->form_gui->checkInput()) {
            $number = $this->form_gui->getInput('acc_codes_number');
            switch ($this->form_gui->getInput('valid_type')) {
                case 'valid_unlimited':
                    $valid = 0;
                    break;
                
                case 'valid_static':
                    $valid = $this->form_gui->getItemByPostVar('valid_date')->getDate()->get(IL_CAL_DATE);
                    break;
                
                case 'valid_dynamic':
                    $valid = $this->form_gui->getInput('valid_days');
                    break;
            }
            
            include_once './Services/User/classes/class.ilAccountCode.php';
            
            $stamp = time();
            for ($loop = 1; $loop <= $number; $loop++) {
                ilAccountCode::create($valid, $stamp);
            }
            
            ilUtil::sendSuccess($lng->txt('saved_successfully'), true);
            $ilCtrl->redirect($this, "listCodes");
        } else {
            $this->form_gui->setValuesByPost();
            $tpl->setContent($this->form_gui->getHtml());
        }
    }
    
    public function deleteCodes()
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        
        include_once './Services/User/classes/class.ilAccountCode.php';
        ilAccountCode::deleteCodes($_POST["id"]);
        
        ilUtil::sendSuccess($lng->txt('info_deleted'), true);
        $ilCtrl->redirect($this, "listCodes");
    }

    public function deleteConfirmation()
    {
        global $DIC;

        $ilErr = $DIC['ilErr'];
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        $tpl = $DIC['tpl'];

        if (!isset($_POST["id"])) {
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
        foreach ($data as $code) {
            $gui->addItem("id[]", $code["code_id"], $code["code"]);
        }

        $tpl->setContent($gui->getHTML());
    }
    
    public function resetCodesFilter()
    {
        include_once("./Services/User/classes/class.ilAccountCodesTableGUI.php");
        $utab = new ilAccountCodesTableGUI($this, "listCodes");
        $utab->resetOffset();
        $utab->resetFilter();
        
        $this->listCodes();
    }
    
    public function applyCodesFilter()
    {
        include_once("./Services/User/classes/class.ilAccountCodesTableGUI.php");
        $utab = new ilAccountCodesTableGUI($this, "listCodes");
        $utab->resetOffset();
        $utab->writeFilterToSession();
        
        $this->listCodes();
    }
    
    public function exportCodes()
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $ilErr = $DIC['ilErr'];
        $lng = $DIC['lng'];

        if (!$ilAccess->checkAccess('read', '', $this->ref_id)) {
            $ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->MESSAGE);
        }
        
        include_once("./Services/User/classes/class.ilAccountCodesTableGUI.php");
        $utab = new ilAccountCodesTableGUI($this, "listCodes");
        
        include_once './Services/User/classes/class.ilAccountCode.php';
        $codes = ilAccountCode::getCodesForExport($utab->filter["code"], $utab->filter["valid_until"], $utab->filter["generated"]);

        if (sizeof($codes)) {
            // #13497
            ilUtil::deliverData(implode("\r\n", $codes), "ilias_account_codes_" . date("d-m-Y") . ".txt", "text/plain");
        } else {
            ilUtil::sendFailure($lng->txt("account_export_codes_no_data"));
            $this->listCodes();
        }
    }
}
