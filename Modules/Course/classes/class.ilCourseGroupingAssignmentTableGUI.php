<?php declare(strict_types=0);
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
 * @version $Id$
 * @ingroup ModulesCourse
 */
class ilCourseGroupingAssignmentTableGUI extends ilTable2GUI
{
    private string $type = '';

    protected ilObjUser $user;
    protected ilTree $tree;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        ilObject $a_content_obj,
        ilObjCourseGrouping $a_group_obj
    ) {
        global $DIC;

        $this->user = $DIC->user();
        $this->tree = $DIC->repositoryTree();

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->type = ilObject::_lookupType($a_content_obj->getId());
        $this->lng->loadLanguageModule($this->type);

        // #9017
        $this->setLimit(9999);

        $this->addColumn('', '', '1');
        $this->addColumn($this->lng->txt('title'), 'title');
        $this->addColumn($this->lng->txt('path'), 'path');

        $this->setDefaultOrderField('title');
        $this->setDefaultOrderDirection('asc');

        $this->setTitle($this->lng->txt('crs_grp_assign_crs') . ' (' . $a_group_obj->getTitle() . ')');

        $this->setRowTemplate("tpl.crs_grp_select_crs.html", "Modules/Course");
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));

        $this->addMultiCommand('assignCourse', $this->lng->txt('grouping_change_assignment'));
        $this->addCommandButton('edit', $this->lng->txt('cancel'));

        $this->getItems($a_group_obj);
    }

    protected function getItems(ilObjCourseGrouping $a_group_obj) : void
    {
        $items = ilUtil::_getObjectsByOperations(
            $this->type,
            'write',
            $this->user->getId(),
            -1
        );
        $items_obj_id = array();
        $items_ids = array();
        foreach ($items as $ref_id) {
            $obj_id = ilObject::_lookupObjId($ref_id);
            $items_ids[$obj_id] = $ref_id;
            $items_obj_id[] = $obj_id;
        }
        $items_obj_id = ilUtil::_sortIds($items_obj_id, 'object_data', 'title', 'obj_id');

        $assigned_ids = array();
        $assigned = $a_group_obj->getAssignedItems();
        if ($assigned !== []) {
            foreach ($assigned as $item) {
                $assigned_ids[] = $item['target_ref_id'];
            }
        }

        $data = array();
        foreach ($items_obj_id as $obj_id) {
            $item_id = $items_ids[$obj_id];
            if ($this->tree->checkForParentType($item_id, 'adm')) {
                continue;
            }

            $obj_id = ilObject::_lookupObjId($item_id);

            $data[] = array('id' => $item_id,
                            'title' => ilObject::_lookupTitle($obj_id),
                            'description' => ilObject::_lookupDescription($obj_id),
                            'path' => $this->__formatPath($this->tree->getPathFull($item_id)),
                            'assigned' => in_array($item_id, $assigned_ids)
            );
        }
        $this->setData($data);
    }

    /**
     * @todo use ilPathGUI
     */
    public function __formatPath(array $a_path_arr) : string
    {
        $counter = 0;
        $path = '';
        foreach ($a_path_arr as $data) {
            if (!$counter++) {
                continue;
            }
            if ($counter++ > 2) {
                $path .= " -> ";
            }
            $path .= $data['title'];
        }
        return $path;
    }

    protected function fillRow(array $a_set) : void
    {
        $this->tpl->setVariable("ID", $a_set["id"]);
        $this->tpl->setVariable("TXT_TITLE", $a_set["title"]);
        $this->tpl->setVariable("TXT_PATH", $a_set["path"]);

        if ($a_set["assigned"]) {
            $this->tpl->setVariable("STATUS_CHECKED", " checked=\"checked\"");
        }
    }
}
