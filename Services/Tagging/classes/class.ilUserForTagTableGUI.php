<?php declare(strict_types=1);

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
 * Show all users for a tag
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilUserForTagTableGUI extends ilTable2GUI
{
    protected ilAccessHandler $access;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        string $a_tag
    ) {
        global $DIC;

        $this->access = $DIC->access();

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setData(ilTagging::getUsersForTag($a_tag));
        $this->setTitle($this->lng->txt("tagging_users_using_tag"));

        $this->addColumn($this->lng->txt("user"), "");

        $this->setEnableHeader(true);
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.user_for_tag_row.html", "Services/Tagging");
        $this->setEnableTitle(true);
    }

    /**
     * @inheritDoc
     */
    protected function fillRow(array $a_set) : void
    {
        $this->tpl->setVariable(
            "USER",
            ilUserUtil::getNamePresentation($a_set["id"], true, false, "", true)
        );
    }
}
