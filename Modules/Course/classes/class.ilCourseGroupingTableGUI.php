<?php

declare(strict_types=0);
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
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ModulesCourse
 */
class ilCourseGroupingTableGUI extends ilTable2GUI
{
    public function __construct(object $a_parent_obj, string $a_parent_cmd, ilObject $a_content_obj)
    {
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $type = ilObject::_lookupType($a_content_obj->getId());
        $this->lng->loadLanguageModule($type);

        $this->addColumn('', '', '1');
        $this->addColumn($this->lng->txt('title'), 'title');
        $this->addColumn($this->lng->txt('description'), 'description');
        $this->addColumn($this->lng->txt('unambiguousness'), 'unique');
        $this->addColumn($this->lng->txt('groupings_assigned_obj_' . $type), 'assigned_sortable');
        $this->addColumn('', '');

        $this->setTitle($this->lng->txt('groupings'));

        $this->addMultiCommand('askDeleteGrouping', $this->lng->txt('delete'));
        $this->setSelectAllCheckbox('grouping');

        $this->setRowTemplate("tpl.groupings.html", "Modules/Course");
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));

        $this->setDefaultOrderField('title');
        $this->setDefaultOrderDirection('asc');

        $this->getItems($a_content_obj);
    }

    protected function getItems(ilObject $a_content_obj): void
    {
        $items = ilObjCourseGrouping::_getVisibleGroupings($a_content_obj->getId());

        $data = array();
        foreach ($items as $grouping_id) {
            $tmp_obj = new ilObjCourseGrouping($grouping_id);

            $data[$grouping_id]['id'] = $grouping_id;
            $data[$grouping_id]['source_ref_id'] = $tmp_obj->getContainerRefId();
            $data[$grouping_id]['assigned'] = [];
            $data[$grouping_id]['title'] = $tmp_obj->getTitle();
            $data[$grouping_id]['unique'] = $this->lng->txt($tmp_obj->getUniqueField());
            $data[$grouping_id]['description'] = $tmp_obj->getDescription();

            // Assigned items
            $assigned_items = $tmp_obj->getAssignedItems();
            $data[$grouping_id]['assigned'] = [];
            foreach ($assigned_items as $condition) {
                $data[$grouping_id]['assigned'][] = ilObject::_lookupTitle($condition['target_obj_id']);
            }
            $data[$grouping_id]['assigned_sortable'] = implode(' ', $data[$grouping_id]['assigned']);
        }

        $this->setData($data);
    }

    protected function fillRow(array $a_set): void
    {
        if (is_array($a_set["assigned"]) && count($a_set["assigned"]) > 0) {
            foreach ($a_set["assigned"] as $item) {
                $this->tpl->setCurrentBlock("assigned");
                $this->tpl->setVariable("ITEM_TITLE", $item);
                $this->tpl->parseCurrentBlock();
            }
        } else {
            $this->tpl->setCurrentBlock("assigned");
            $this->tpl->setVariable("ITEM_TITLE", $this->lng->txt('crs_grp_no_courses_assigned'));
            $this->tpl->parseCurrentBlock();
        }

        if (array_key_exists('source_ref_id', $a_set) && $a_set['source_ref_id']) {
            $path = new \ilPathGUI();
            $path->enableHideLeaf(false);
            $path->enableTextOnly(false);
            $path->setUseImages(true);

            $this->tpl->setCurrentBlock('path');
            $this->tpl->setVariable('OBJ_PATH', $path->getPath(ROOT_FOLDER_ID, (int) $a_set['source_ref_id']));
            $this->tpl->parseCurrentBlock();
        }

        $this->tpl->setVariable("ID", $a_set["id"]);
        $this->tpl->setVariable("TXT_TITLE", $a_set["title"]);
        $this->tpl->setVariable("TXT_DESCRIPTION", $a_set["description"]);
        $this->tpl->setVariable("TXT_UNIQUE", $a_set["unique"]);

        $this->ctrl->setParameter($this->parent_obj, 'obj_id', $a_set["id"]);
        $this->tpl->setVariable(
            "EDIT_LINK",
            $this->ctrl->getLinkTarget($this->parent_obj, 'edit')
        );
        $this->tpl->setVariable('TXT_EDIT', $this->lng->txt('edit'));
    }
}
