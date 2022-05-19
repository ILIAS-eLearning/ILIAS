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

use ILIAS\Portfolio\Administration\PortfolioRoleAssignmentManager;
use ILIAS\Portfolio\StandardGUIRequest;

/**
 * @ilCtrl_Calls ilPortfolioRoleAssignmentGUI: ilPropertyFormGUI
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPortfolioRoleAssignmentGUI
{
    protected StandardGUIRequest $port_request;
    protected ilCtrl $ctrl;
    protected ilToolbarGUI $toolbar;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $main_tpl;
    protected PortfolioRoleAssignmentManager $manager;

    public function __construct()
    {
        global $DIC;

        $this->toolbar = $DIC->toolbar();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->manager = new PortfolioRoleAssignmentManager();
        $this->port_request = $DIC->portfolio()
            ->internal()
            ->gui()
            ->standardRequest();
    }

    public function executeCommand() : void
    {
        $ctrl = $this->ctrl;

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("listAssignments");

        switch ($next_class) {
            case "ilpropertyformgui":
                $form = $this->initAssignmentForm();
                $ctrl->forwardCommand($form);
                break;

            default:
                if (in_array($cmd, [
                    "listAssignments",
                    "addAssignment",
                    "saveAssignment",
                    "confirmAssignmentDeletion",
                    "deleteAssignments"
                ])) {
                    $this->$cmd();
                }
        }
    }

    protected function listAssignments() : void
    {
        $lng = $this->lng;
        $this->toolbar->addButton(
            $lng->txt("prtf_add_assignment"),
            $this->ctrl->getLinkTarget($this, "addAssignment")
        );

        $table = new ilPortfolioRoleAssignmentTableGUI(
            $this,
            "listAssignments",
            $this->manager
        );
        $this->main_tpl->setContent($table->getHTML());
    }

    protected function addAssignment() : void
    {
        $main_tpl = $this->main_tpl;
        $form = $this->initAssignmentForm();
        $main_tpl->setContent($form->getHTML());
    }

    public function initAssignmentForm() : ilPropertyFormGUI
    {
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        $form = new ilPropertyFormGUI();

        $options = $this->manager->getAvailableRoles();
        $si_roles = new ilSelectInputGUI($this->lng->txt("prtf_role_title"), 'role_id');
        $si_roles->setRequired(true);
        $si_roles->setOptions($options);
        $form->addItem($si_roles);

        $repo = new ilRepositorySelector2InputGUI($lng->txt("prtf_template_title"), "template_ref_id");
        $repo->setRequired(true);
        $repo->getExplorerGUI()->setSelectableTypes(array("prtt"));
        $repo->getExplorerGUI()->setTypeWhiteList(array("root", "prtt", "cat", "crs", "grp", "fold"));
        $form->addItem($repo);

        // save and cancel commands
        $form->addCommandButton("saveAssignment", $lng->txt("save"));
        $form->addCommandButton("listAssignments", $lng->txt("cancel"));

        $form->setTitle($lng->txt("prtf_add_assignment"));
        $form->setFormAction($ctrl->getFormAction($this));

        return $form;
    }

    public function saveAssignment() : void
    {
        $ctrl = $this->ctrl;
        $lng = $this->lng;
        $main_tpl = $this->main_tpl;

        $form = $this->initAssignmentForm();
        if ($form->checkInput()) {
            $this->manager->add(
                (int) $form->getInput("template_ref_id"),
                (int) $form->getInput("role_id")
            );
            $this->main_tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
            $ctrl->redirect($this, "");
        } else {
            $form->setValuesByPost();
            $main_tpl->setContent($form->getHTML());
        }
    }

    protected function confirmAssignmentDeletion() : void
    {
        $ctrl = $this->ctrl;
        $lng = $this->lng;
        $main_tpl = $this->main_tpl;

        $template_ids = $this->port_request->getRoleTemplateIds();
        if (count($template_ids) === 0) {
            $this->main_tpl->setOnScreenMessage('info', $lng->txt("no_checkbox"), true);
            $ctrl->redirect($this, "listAssignments");
        } else {
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ctrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("prtf_delete_assignment_sure"));
            $cgui->setCancel($lng->txt("cancel"), "listAssignments");
            $cgui->setConfirm($lng->txt("delete"), "deleteAssignments");
            foreach ($template_ids as $i) {
                $id_arr = explode("_", $i);
                $role_title = ilObject::_lookupTitle($id_arr[0]);
                $template_title = ilObject::_lookupTitle(
                    ilObject::_lookupObjId($id_arr[1])
                );
                $cgui->addItem("role_template_ids[]", $i, $role_title .
                    " - " . $template_title);
            }

            $main_tpl->setContent($cgui->getHTML());
        }
    }

    protected function deleteAssignments() : void
    {
        $ctrl = $this->ctrl;
        $lng = $this->lng;
        $template_ids = $this->port_request->getRoleTemplateIds();
        foreach ($template_ids as $i) {
            $id_arr = explode("_", $i);
            $this->manager->delete((int) $id_arr[1], (int) $id_arr[0]);
        }
        $this->main_tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        $ctrl->redirect($this, "listAssignments");
    }
}
