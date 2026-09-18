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
use ILIAS\Portfolio\InternalGUIService;
use ILIAS\Portfolio\StandardGUIRequest;
use ILIAS\Repository\Table\TableAdapterGUI;

/**
 * @ilCtrl_Calls ilPortfolioRoleAssignmentGUI: ilPropertyFormGUI
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPortfolioRoleAssignmentGUI
{
    protected ilAccessHandler $access;
    protected int $ref_id;
    protected StandardGUIRequest $port_request;
    protected ilCtrl $ctrl;
    protected ilToolbarGUI $toolbar;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $main_tpl;
    protected PortfolioRoleAssignmentManager $manager;
    protected InternalGUIService $portfolio_gui;

    public function __construct()
    {
        global $DIC;

        $this->toolbar = $DIC->toolbar();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->manager = new PortfolioRoleAssignmentManager();
        $this->portfolio_gui = $DIC->portfolio()->internal()->gui();
        $this->port_request = $DIC->portfolio()
            ->internal()
            ->gui()
            ->standardRequest();
        $this->ref_id = $this->port_request->getRefId();
        $this->access = $DIC->access();
    }

    public function executeCommand(): void
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
                    "deleteAssignment"
                ])) {
                    $this->$cmd();
                }
        }
    }

    protected function listAssignments(): void
    {
        $lng = $this->lng;
        if ($this->checkWrite()) {
            $this->toolbar->addButton(
                $lng->txt("prtf_add_assignment"),
                $this->ctrl->getLinkTarget($this, "addAssignment")
            );
        }

        $table = $this->getRoleAssignmentTable();
        if ($table->handleCommand()) {
            return;
        }
        $this->main_tpl->setContent($table->render());
    }

    protected function getRoleAssignmentTable(): TableAdapterGUI
    {
        return $this->portfolio_gui
            ->portfolioRoleAssignmentTableBuilder(
                $this->checkWrite(),
                $this,
                "listAssignments"
            )
            ->getTable();
    }

    protected function addAssignment(): void
    {
        $main_tpl = $this->main_tpl;
        $form = $this->initAssignmentForm();
        $main_tpl->setContent($form->getHTML());
    }

    public function initAssignmentForm(): ilPropertyFormGUI
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

    protected function checkWrite(bool $return = false): bool
    {
        $lng = $this->lng;
        $ctrl = $this->ctrl;
        $has_perm = $this->access->checkAccess(
            "write",
            "",
            $this->ref_id
        );
        if ($return && !$has_perm) {
            $this->main_tpl->setOnScreenMessage('failure', $lng->txt("no_permission"), true);
            $ctrl->redirect($this, "");

        }

        return $has_perm;
    }

    public function saveAssignment(): void
    {
        $ctrl = $this->ctrl;
        $lng = $this->lng;
        $main_tpl = $this->main_tpl;
        $this->checkWrite(true);
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

    public function confirmAssignmentDeletion(string $assignment_id): void
    {
        $this->checkWrite(true);
        $assignment = $this->getAssignment($assignment_id);
        if ($assignment === null) {
            $this->main_tpl->setOnScreenMessage('info', $this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "listAssignments");
        }

        $this->getRoleAssignmentTable()->renderDeletionConfirmation(
            $this->lng->txt("prtf_delete_assignment_sure"),
            $this->lng->txt("prtf_delete_assignment_sure"),
            "deleteAssignment",
            [
                $assignment_id => $assignment["role_title"] . " - " . $assignment["template_title"]
            ]
        );
    }

    protected function deleteAssignment(): void
    {
        $this->checkWrite(true);
        $template_ids = $this->getRoleAssignmentTable()->getItemIds();
        $assignment = $this->getAssignment($template_ids[0] ?? "");
        if ($assignment === null) {
            $this->main_tpl->setOnScreenMessage('info', $this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "listAssignments");
        }

        $this->manager->delete(
            $assignment["template_ref_id"],
            $assignment["role_id"]
        );
        $this->main_tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "listAssignments");
    }

    protected function getAssignment(string $assignment_id): ?array
    {
        $parts = explode("_", $assignment_id);
        if (count($parts) !== 2 || !ctype_digit($parts[0]) || !ctype_digit($parts[1])) {
            return null;
        }

        $role_id = (int) $parts[0];
        $template_ref_id = (int) $parts[1];
        foreach ($this->manager->getAllAssignmentData() as $assignment) {
            if ((int) $assignment["role_id"] === $role_id
                && (int) $assignment["template_ref_id"] === $template_ref_id) {
                return [
                    "role_id" => $role_id,
                    "template_ref_id" => $template_ref_id,
                    "role_title" => $assignment["role_title"],
                    "template_title" => $assignment["template_title"]
                ];
            }
        }

        return null;
    }
}
