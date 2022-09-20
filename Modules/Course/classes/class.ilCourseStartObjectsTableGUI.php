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
class ilCourseStartObjectsTableGUI extends ilTable2GUI
{
    public function __construct(object $a_parent_obj, string $a_parent_cmd, ilObject $a_obj_course)
    {
        global $DIC;

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->lng->loadLanguageModule('crs');

        $this->addColumn('', '', '1');
        $this->addColumn($this->lng->txt('type'), 'type', '1');
        $this->addColumn($this->lng->txt('title'), 'title');
        $this->addColumn($this->lng->txt('description'), 'description');

        // add
        if ($a_parent_cmd != 'listStructure') {
            $this->setTitle($this->lng->txt('crs_select_starter'));

            $this->addMultiCommand('addStarter', $this->lng->txt('crs_add_starter'));
        } // list
        else {
            $this->setTitle($this->lng->txt('crs_start_objects'));

            $this->addMultiCommand('askDeleteStarter', $this->lng->txt('delete'));
        }

        $this->setRowTemplate("tpl.crs_add_starter.html", "Modules/Course");
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setSelectAllCheckbox('starter');

        $this->setDefaultOrderField('title');
        $this->setDefaultOrderDirection('asc');

        $data = array();

        $crs_start = new ilCourseStart($a_obj_course->getRefId(), $a_obj_course->getId());

        // add
        if ($a_parent_cmd != 'listStructure') {
            $data = $this->getPossibleObjects($a_obj_course, $crs_start);
        } // list
        else {
            $data = $this->getStartObjects($a_obj_course, $crs_start);
        }

        $this->setData($data);
    }

    protected function getPossibleObjects(ilObject $a_obj_course, ilCourseStart $crs_start): array
    {
        $data = array();
        foreach ($crs_start->getPossibleStarters() as $item_ref_id) {
            $tmp_obj = ilObjectFactory::getInstanceByRefId($item_ref_id);

            $data[$item_ref_id]['id'] = $item_ref_id;
            $data[$item_ref_id]['title'] = $tmp_obj->getTitle();
            $data[$item_ref_id]['type'] = $this->lng->txt('obj_' . $tmp_obj->getType());
            $data[$item_ref_id]['icon'] = ilObject::_getIcon($tmp_obj->getId(), 'tiny');

            if (strlen($tmp_obj->getDescription())) {
                $data[$item_ref_id]['description'] = $tmp_obj->getDescription();
            }
        }
        return $data;
    }

    protected function getStartObjects(ilObject $a_obj_course, ilCourseStart $crs_start): array
    {
        $starters = $crs_start->getStartObjects();
        $data = array();
        foreach ($starters as $start_id => $item) {
            $tmp_obj = ilObjectFactory::getInstanceByRefId($item['item_ref_id']);

            $data[$item['item_ref_id']]['id'] = $start_id;
            $data[$item['item_ref_id']]['title'] = $tmp_obj->getTitle();
            $data[$item['item_ref_id']]['type'] = $this->lng->txt('obj_' . $tmp_obj->getType());
            $data[$item['item_ref_id']]['icon'] = ilObject::_getIcon($tmp_obj->getId(), 'tiny');

            if (strlen($tmp_obj->getDescription())) {
                $data[$item['item_ref_id']]['description'] = $tmp_obj->getDescription();
            }
        }

        return $data;
    }

    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable("ID", (string) $a_set["id"]);
        $this->tpl->setVariable("TXT_TITLE", (string) $a_set["title"]);
        $this->tpl->setVariable("TXT_DESCRIPTION", (string) $a_set["description"]);
        $this->tpl->setVariable("ICON_SRC", (string) $a_set["icon"]);
        $this->tpl->setVariable("ICON_ALT", (string) $a_set["type"]);
    }
}
