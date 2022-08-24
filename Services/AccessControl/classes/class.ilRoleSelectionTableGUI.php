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
 * @author  Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * @ingroup ServicesAccessControl
 */
class ilRoleSelectionTableGUI extends ilTable2GUI
{
    protected ilRbacReview $review;

    public function __construct(object $a_parent_obj, string $a_parent_cmd)
    {
        global $DIC;

        $this->review = $DIC->rbac()->review();

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->addColumn('', 'f', (string) 1);
        $this->addColumn($this->lng->txt('title'), 'title', "70%");
        $this->addColumn($this->lng->txt('context'), 'context', "30%");

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.show_role_selection_row.html", "Services/AccessControl");
        $this->setDefaultOrderField('type');
        $this->setDefaultOrderDirection("desc");
    }

    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable('VAL_ID', $a_set['id']);
        $this->tpl->setVariable('VAL_TITLE', $a_set['title']);
        if (strlen($a_set['description'])) {
            $this->tpl->setVariable('VAL_DESC', $a_set['description']);
        }
        $this->tpl->setVariable('VAL_CONTEXT', $a_set['context']);
    }

    public function parse(array $entries): void
    {
        $records_arr = [];
        foreach ($entries as $entry) {
            $tmp_arr['id'] = $entry['obj_id'];
            $tmp_arr['title'] = ilObjRole::_getTranslation(ilObject::_lookupTitle($entry['obj_id']));
            $tmp_arr['description'] = ilObject::_lookupDescription($entry['obj_id']);
            $tmp_arr['context'] = ilObject::_lookupTitle($this->review->getObjectOfRole((int) $entry['obj_id']));

            $records_arr[] = $tmp_arr;
        }
        $this->setData($records_arr);
    }
}
