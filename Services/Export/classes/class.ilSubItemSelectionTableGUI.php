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
 * Select subitems for export
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilSubItemSelectionTableGUI extends ilTable2GUI
{
    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        int $a_ref_id,
        string $a_cmd = "createExportFile",
        string $a_cmd_txt = ""
    ) {
        global $DIC;

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->lng->loadLanguageModule("export");

        if ($a_cmd_txt == "") {
            $a_cmd_txt = $this->lng->txt("export_create_export_file");
        }

        $this->addColumn($this->lng->txt("export_resource"));
        $this->addColumn($this->lng->txt("export_last_export"));
        $this->addColumn($this->lng->txt("export_last_export_file"), "", "20%");
        $this->addColumn($this->lng->txt("export_create_new_file"), "", "20%");
        $this->addColumn($this->lng->txt("export_omit_resource"), "", "20%");
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->addCommandButton($a_cmd, $a_cmd_txt);
        $this->setRowTemplate(
            "tpl.sub_item_selection_row.html",
            "Services/Export"
        );
        $this->setTitle($this->lng->txt("export_select_resources"));
        $this->setData(ilExport::_getValidExportSubItems($a_ref_id));
        $this->setLimit(99999);
    }

    /**
     * @inheritDoc
     */
    protected function fillRow(array $a_set) : void
    {
        $now = new ilDateTime(time(), IL_CAL_UNIX);

        $this->tpl->setVariable("IMG_ALT", $this->lng->txt("obj_" . $a_set["type"]));
        $this->tpl->setVariable("IMG_SRC", ilObject::_getIcon(
            (int) $a_set["obj_id"],
            "small",
            $a_set["type"]
        ));
        $this->tpl->setVariable("VAL_TITLE", $a_set["title"]);
        $this->tpl->setVariable("ID", $a_set["ref_id"]);
        $this->tpl->setVariable("TXT_LAST_EXPORT_FILE", $this->lng->txt("export_last_file"));
        $this->tpl->setVariable("TXT_OMIT", $this->lng->txt("export_omit"));
        $this->tpl->setVariable("TXT_CREATE_NEW_EXPORT_FILE", $this->lng->txt("export_create"));
        $preset = "CREATE";
        if ($a_set["timestamp"] > 0) {
            $last_export = new ilDateTime($a_set["timestamp"], IL_CAL_UNIX);
            $this->tpl->setVariable(
                "VAL_LAST_EXPORT",
                ilDatePresentation::formatDate($last_export)
            );
            if (ilDateTime::_equals($last_export, $now, IL_CAL_DAY)) {
                $preset = "LAST_FILE";
            }
        }
        $this->tpl->setVariable("SEL_" . $preset, ' checked="checked" ');
    }
}
