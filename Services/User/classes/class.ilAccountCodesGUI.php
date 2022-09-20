<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * GUI class for account codes
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilAccountCodesGUI
{
    protected ilPropertyFormGUI $form_gui;
    protected \ILIAS\User\StandardGUIRequest $request;
    protected int $ref_id;
    protected array $filter; // obsolete?
    private \ilGlobalTemplateInterface $main_tpl;

    public function __construct(int $a_ref_id)
    {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();

        $lng = $DIC['lng'];

        $this->ref_id = $a_ref_id;
        $lng->loadLanguageModule("user");
        $this->request = new \ILIAS\User\StandardGUIRequest(
            $DIC->http(),
            $DIC->refinery()
        );
    }

    public function executeCommand(): void
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
    }

    public function listCodes(): void
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

        $button = ilLinkButton::getInstance();
        $button->setCaption("user_account_codes_add");
        $button->setUrl($ilCtrl->getLinkTarget($this, "addCodes"));
        $ilToolbar->addButtonInstance($button);

        $ctab = new ilAccountCodesTableGUI($this, "listCodes");
        $tpl->setContent($ctab->getHTML());
    }

    public function initAddCodesForm(): void
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];

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

    public function addCodes(): void
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

    public function createCodes(): void
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $ilErr = $DIC['ilErr'];
        $lng = $DIC['lng'];
        $tpl = $DIC['tpl'];
        $ilCtrl = $DIC['ilCtrl'];

        $valid = "";

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

            $stamp = time();
            for ($loop = 1; $loop <= $number; $loop++) {
                ilAccountCode::create($valid, $stamp);
            }

            $this->main_tpl->setOnScreenMessage('success', $lng->txt('saved_successfully'), true);
            $ilCtrl->redirect($this, "listCodes");
        } else {
            $this->form_gui->setValuesByPost();
            $tpl->setContent($this->form_gui->getHTML());
        }
    }

    public function deleteCodes(): void
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];

        $ids = $this->request->getIds();
        ilAccountCode::deleteCodes($ids);

        $this->main_tpl->setOnScreenMessage('success', $lng->txt('info_deleted'), true);
        $ilCtrl->redirect($this, "listCodes");
    }

    public function deleteConfirmation(): void
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        $tpl = $DIC['tpl'];

        $ids = $this->request->getIds();
        if (count($ids) == 0) {
            $this->main_tpl->setOnScreenMessage('failure', $lng->txt("no_checkbox"), true);
            $this->listCodes();
        }

        $gui = new ilConfirmationGUI();
        $gui->setHeaderText($lng->txt("info_delete_sure"));
        $gui->setCancel($lng->txt("cancel"), "listCodes");
        $gui->setConfirm($lng->txt("confirm"), "deleteCodes");
        $gui->setFormAction($ilCtrl->getFormAction($this, "deleteCodes"));

        $data = ilAccountCode::loadCodesByIds($ids);
        foreach ($data as $code) {
            $gui->addItem("id[]", $code["code_id"], $code["code"]);
        }

        $tpl->setContent($gui->getHTML());
    }

    public function resetCodesFilter(): void
    {
        $utab = new ilAccountCodesTableGUI($this, "listCodes");
        $utab->resetOffset();
        $utab->resetFilter();

        $this->listCodes();
    }

    public function applyCodesFilter(): void
    {
        $utab = new ilAccountCodesTableGUI($this, "listCodes");
        $utab->resetOffset();
        $utab->writeFilterToSession();

        $this->listCodes();
    }

    public function exportCodes(): void
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $ilErr = $DIC['ilErr'];
        $lng = $DIC['lng'];

        if (!$ilAccess->checkAccess('read', '', $this->ref_id)) {
            $ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->MESSAGE);
        }

        $utab = new ilAccountCodesTableGUI($this, "listCodes");
        $codes = ilAccountCode::getCodesForExport($utab->filter["code"], $utab->filter["valid_until"], $utab->filter["generated"]);

        if (count($codes)) {
            // #13497
            ilUtil::deliverData(implode("\r\n", $codes), "ilias_account_codes_" . date("d-m-Y") . ".txt", "text/plain");
        } else {
            $this->main_tpl->setOnScreenMessage('failure', $lng->txt("account_export_codes_no_data"));
            $this->listCodes();
        }
    }
}
