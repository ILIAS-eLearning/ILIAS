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

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPortfolioRoleAssignmentTableGUI extends ilTable2GUI
{
    protected PortfolioRoleAssignmentManager $manager;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        PortfolioRoleAssignmentManager $manager
    ) {
        global $DIC;

        $this->id = "";
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->manager = $manager;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setData($this->getItems());
        $this->setTitle($this->lng->txt(""));

        $this->addColumn("", "", "1", true);
        $this->addColumn($this->lng->txt("prtf_role_title"), "role_title");
        $this->addColumn($this->lng->txt("prtf_template_title"), "template_title");

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.prtf_role_assignment_row.html", "Modules/Portfolio/Administration");

        $this->addMultiCommand("confirmAssignmentDeletion", $this->lng->txt("prtf_delete_assignment"));
    }

    protected function getItems(): array
    {
        return $this->manager->getAllAssignmentData();
    }

    protected function fillRow(array $a_set): void
    {
        $tpl = $this->tpl;
        $tpl->setVariable("ROLE_ID", $a_set["role_id"]);
        $tpl->setVariable("TEMPLATE_ID", $a_set["template_ref_id"]);
        $tpl->setVariable("TEMPLATE_TITLE", $a_set["template_title"]);
        $tpl->setVariable("ROLE_TITLE", $a_set["role_title"]);
    }
}
