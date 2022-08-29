<?php

declare(strict_types=1);

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

use ILIAS\Administration\SettingsTemplateGUIRequest;

/**
 * Settings templates table
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilSettingsTemplateTableGUI extends ilTable2GUI
{
    protected ilAccessHandler $access;
    protected \ILIAS\DI\Container $dic;
    protected ilRbacSystem $rbacsystem;
    protected SettingsTemplateGUIRequest $request;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        string $a_type
    ) {
        global $DIC;

        $this->dic = $DIC;
        $this->ctrl = $this->dic->ctrl();
        $this->lng = $this->dic->language();
        $this->access = $this->dic->access();
        $this->rbacsystem = $this->dic->rbac()->system();
        $ilCtrl = $this->dic->ctrl();
        $lng = $this->dic->language();
        $this->request = new SettingsTemplateGUIRequest(
            $DIC->http(),
            $DIC->refinery()
        );

        $this->setId("admsettemp" . $a_type);

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setData(ilSettingsTemplate::getAllSettingsTemplates($a_type, true));
        $this->setTitle(
            $lng->txt("adm_settings_templates") . " - " .
                $lng->txt("obj_" . $a_type)
        );

        $this->addColumn("", "", "1", true);
        $this->addColumn($this->lng->txt("title"), "title");
        $this->addColumn($this->lng->txt("description"));
        $this->addColumn($this->lng->txt("actions"));

        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate(
            "tpl.settings_template_row.html",
            "Services/Administration"
        );

        if ($this->rbacsystem->checkAccess('write', $this->request->getRefId())) {
            $this->addMultiCommand("confirmSettingsTemplateDeletion", $lng->txt("delete"));
            //$this->addCommandButton("", $lng->txt(""));
        }
    }

    protected function fillRow(array $a_set): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $ilCtrl->setParameter($this->parent_obj, "templ_id", $a_set["id"]);
        $this->tpl->setVariable("VAL_ID", $a_set["id"]);
        $this->tpl->setVariable("VAL_TITLE", ilSettingsTemplate::translate($a_set["title"] ?? ''));
        $this->tpl->setVariable("VAL_DESCRIPTION", ilSettingsTemplate::translate($a_set["description"] ?? ''));
        if ($this->rbacsystem->checkAccess('write', $this->request->getRefId())) {
            $this->tpl->setVariable("TXT_EDIT", $lng->txt("edit"));
            $this->tpl->setVariable(
                "HREF_EDIT",
                $ilCtrl->getLinkTarget($this->parent_obj, "editSettingsTemplate")
            );
        }
        $ilCtrl->setParameter($this->parent_obj, "templ_id", "");
    }
}
