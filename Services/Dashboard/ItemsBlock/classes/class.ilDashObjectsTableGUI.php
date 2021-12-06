<?php // CR: declare(strict_types=1) is missing

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Classic table for rep object lists, including checkbox
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilDashObjectsTableGUI extends ilTable2GUI
{
    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        int $sub_id
    ) {
        global $DIC;

        $this->id = "dash_obj_" . $sub_id;
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();

        parent::__construct($a_parent_obj, $a_parent_cmd);

        // CR: Delete comment line 37
        //$this->setData($this->getItems());
        $this->setTitle($this->lng->txt(""));

        $this->addColumn("", "", "", true);

        $this->setEnableNumInfo(false);
        $this->setEnableHeader(false);

        // CR: Delete comment line 46
        //$this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.dash_obj_row.html", "Services/Dashboard");

        // CR: Delete comment line 50-51
        //$this->addMultiCommand("", $this->lng->txt(""));
        //$this->addCommandButton("", $this->lng->txt(""));
        $this->setLimit(9999);
    }

    /**
     * Get items
     *
     * @return array[]
     */
    /*
    protected function getItems()
    {
        $items = [];

        return $items;
    }*/

    /**
     * Fill table row
     */
    protected function fillRow($a_set)
    {
        $tpl = $this->tpl;
        $tpl->setVariable("ID", $a_set["ref_id"]);
        $tpl->setVariable("ICON", ilObject::_getIcon($a_set["obj_id"]));
        $tpl->setVariable("TITLE", $a_set["title"]);
    }
}
