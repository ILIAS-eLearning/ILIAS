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

/**
 * List all contributors members of a blog
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilContributorTableGUI extends ilTable2GUI
{
    protected ilRbacReview $rbacreview;
    protected array $local_roles = [];
    protected array $contributor_ids = [];

    public function __construct(
        ilRbacReview $rbacreview,
        object $a_parent_obj,
        string $a_parent_cmd,
        array $a_roles
    ) {
        $this->local_roles = $a_roles;
        $this->rbacreview = $rbacreview;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->addColumn("", "", "1");
        $this->addColumn($this->lng->txt("name"), "name");
        $this->addColumn($this->lng->txt("obj_role"), "role");

        $this->setDefaultOrderField("name");

        $this->setRowTemplate("tpl.contributor_row.html", "components/ILIAS/Blog");
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));

        $this->setSelectAllCheckbox("id"); // #16472

        if ($this->contributor_ids) {
            $this->setTitle($this->lng->txt("blog_contributor_container_add"));
            $this->addMultiCommand("addContributorContainerAction", $this->lng->txt("add"));
        } else {
            $this->setTitle($this->lng->txt("blog_contributors"));
            $this->addMultiCommand("confirmRemoveContributor", $this->lng->txt("remove"));
        }

        $this->getItems();
    }

    protected function getItems(): void
    {
        $rbacreview = $this->rbacreview;

        $user_map = $assigned = array();
        foreach ($this->local_roles as $id => $title) {
            $local = $rbacreview->assignedUsers($id);
            $assigned = array_merge($assigned, $local);
            foreach ($local as $user_id) {
                $user_map[$user_id][] = $title;
            }
        }

        $data = array();
        foreach (array_unique($assigned) as $id) {
            $data[] = array("id" => $id,
                "name" => ilUserUtil::getNamePresentation($id, false, false, "", true),
                "role" => $user_map[$id]);
        }

        $this->setData($data);
    }

    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable("VAL_ID", $a_set["id"]);
        $this->tpl->setVariable("TXT_NAME", $a_set["name"]);
        $this->tpl->setVariable("TXT_ROLES", implode(", ", $a_set["role"]));
    }
}
