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
 * TableGUI class for file list
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCFileListTableGUI extends ilTable2GUI
{
    protected int $pos = 0;
    protected ilPCFileList $file_list;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        ilPCFileList $a_file_list
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->addColumn("", "", "1");
        $this->addColumn($lng->txt("cont_position"), "", "1");
        if ($this->getParentObject()->checkStyleSelection()) {
            $this->addColumn($lng->txt("cont_file"), "", "50%");
            $this->addColumn($lng->txt("cont_characteristic"), "", "50%");
        } else {
            $this->addColumn($lng->txt("cont_file"), "", "100%");
        }
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate(
            "tpl.file_list_row.html",
            "Services/COPage"
        );

        $this->file_list = $a_file_list;
        $this->setData($this->file_list->getFileList());
        $this->setLimit(0);

        $this->addMultiCommand("deleteFileItem", $lng->txt("delete"));
        if (count($this->getData()) > 0) {
            if ($this->getParentObject()->checkStyleSelection()) {
                $this->addCommandButton("savePositionsAndClasses", $lng->txt("cont_save_positions_and_classes"));
            } else {
                $this->addCommandButton("savePositions", $lng->txt("cont_save_positions"));
            }
        }

        $this->setTitle($lng->txt("cont_files"));
    }

    protected function fillRow(array $a_set): void
    {
        if ($this->getParentObject()->checkStyleSelection()) {
            $this->tpl->setCurrentBlock("class_sel");
            $sel = ($a_set["class"] == "")
                ? "FileListItem"
                : $a_set["class"];
            $this->tpl->setVariable(
                "CLASS_SEL",
                ilLegacyFormElementsUtil::formSelect(
                    $sel,
                    "class[" . $a_set["hier_id"] . ":" . $a_set["pc_id"] . "]",
                    $this->getParentObject()->getCharacteristics(),
                    false,
                    true
                )
            );
            $this->tpl->parseCurrentBlock();
        }

        $this->pos += 10;
        $this->tpl->setVariable("POS", $this->pos);
        $this->tpl->setVariable("FID", $a_set["hier_id"] . ":" . $a_set["pc_id"]);
        $this->tpl->setVariable("TXT_FILE", ilObject::_lookupTitle($a_set["id"]));
    }
}
