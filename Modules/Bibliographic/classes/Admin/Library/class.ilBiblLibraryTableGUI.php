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
 * Bibliographic ilBiblLibraryTableGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBiblLibraryTableGUI extends ilTable2GUI
{
    use \ILIAS\Modules\OrgUnit\ARHelper\DIC;


    /**
     * ilObjBibliographicAdminTableGUI constructor.
     */
    public function __construct(ilBiblLibraryGUI $parent_gui)
    {
        parent::__construct($parent_gui);
        $this->setTitle($this->lng()->txt('bibl_settings_libraries'));
        $this->setId('bibl_libraries_tbl');
        $this->initColumns();
        $this->setEnableNumInfo(false);
        $this->setFormAction($this->ctrl()->getFormAction($parent_gui));
        $this->setRowTemplate('tpl.bibl_settings_lib_list_row.html', 'Modules/Bibliographic');
    }


    public function fillRow(array $a_set) : void
    {
        $this->tpl->setVariable('VAL_LIBRARY_NAME', $a_set['name']);
        $this->tpl->setVariable('VAL_LIBRARY_URL', $a_set['url']);
        $this->tpl->setVariable('VAL_LIBRARY_IMG', $a_set['img']);

        if ($this->checkPermissionBoolAndReturn('write')) {
            $this->ctrl()->setParameter($this->parent_obj, ilBiblLibraryGUI::F_LIB_ID, $a_set['id']);
            $current_selection_list = new ilAdvancedSelectionListGUI();
            $current_selection_list->setListTitle($this->lng->txt("actions"));
            $current_selection_list->setId($a_set['id']);
            $current_selection_list->addItem(
                $this->lng->txt(ilBiblLibraryGUI::CMD_EDIT),
                "",
                $this->ctrl()->getLinkTarget($this->parent_obj, ilBiblLibraryGUI::CMD_EDIT)
            );
            $current_selection_list->addItem(
                $this->lng->txt(ilBiblLibraryGUI::CMD_DELETE),
                "",
                $this->ctrl()->getLinkTarget($this->parent_obj, ilBiblLibraryGUI::CMD_DELETE)
            );
            $this->tpl->setVariable('VAL_ACTIONS', $current_selection_list->getHTML());
        } else {
            $this->tpl->setVariable('VAL_ACTIONS', "&nbsp;");
        }
    }


    protected function initColumns() : void
    {
        $this->addColumn($this->lng()->txt('bibl_library_name'), '', '30%');
        $this->addColumn($this->lng()->txt('bibl_library_url'), '30%');
        $this->addColumn($this->lng()->txt('bibl_library_img'), '', '30%');
        $this->addColumn($this->lng()->txt('actions'), '', '8%');
    }
}
