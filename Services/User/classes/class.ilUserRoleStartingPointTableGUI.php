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
 * TableGUI class for LTI consumer listing
 * @author Jesús López <lopez@leifos.com>
 */
class ilUserRoleStartingPointTableGUI extends ilTable2GUI
{
    public const TABLE_POSITION_USER_CHOOSES = -1;
    public const TABLE_POSITION_DEFAULT = 9999;

    private ilLogger $log;
    private ilRbacReview $rbacreview;

    public function __construct(object $a_parent_obj)
    {
        global $DIC;

        $this->rbacreview = $DIC->rbac()->review();

        $this->log = ilLoggerFactory::getLogger('user');

        $this->parent_obj = $a_parent_obj;

        $this->setId('usrrolesp');

        parent::__construct($a_parent_obj);

        $this->getItems();

        $this->setLimit(9999);
        $this->setTitle($this->lng->txt('user_role_starting_point'));

        $this->addColumn($this->lng->txt('user_order'));
        $this->addColumn($this->lng->txt('criteria'));
        $this->addColumn($this->lng->txt('starting_page'));
        $this->addColumn($this->lng->txt('actions'));
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate('tpl.user_role_starting_point_row.html', 'Services/User');
        $this->addCommandButton('saveOrder', $this->lng->txt('save_order'));

        $this->setExternalSorting(true);
    }

    /**
     * Get data
     */
    public function getItems(): void
    {
        $dc = new ilObjectDataCache();

        $valid_points = ilUserUtil::getPossibleStartingPoints();

        $status = (ilUserUtil::hasPersonalStartingPoint() ? $this->lng->txt('yes') : $this->lng->txt('no'));

        $starting_points = [];
        $starting_points[] = [
            'id' => 'user',
            'criteria' => $this->lng->txt('user_chooses_starting_page'),
            'starting_page' => $status,
            'starting_position' => self::TABLE_POSITION_USER_CHOOSES
        ];

        $available_starting_points = ilStartingPoint::getStartingPoints();

        foreach ($available_starting_points as $available_starting_point) {
            $starting_point = $available_starting_point['starting_point'];
            $position = $available_starting_point['position'];
            $sp_text = $valid_points[$starting_point] ?? '';

            if ($starting_point == ilUserUtil::START_REPOSITORY_OBJ && $available_starting_point['starting_object']) {
                $object_id = ilObject::_lookupObjId($available_starting_point['starting_object']);
                $type = $dc->lookupType($object_id);
                $title = $dc->lookupTitle($object_id);
                $sp_text = $this->lng->txt('obj_' . $type)
                    . ' <i>"' . $title . '"</i> '
                    . '[' . $available_starting_point['starting_object'] . ']';
            }

            if ($available_starting_point['rule_type'] == ilStartingPoint::ROLE_BASED) {
                $options = unserialize($available_starting_point['rule_options'], ['allowed_classes' => false]);

                $role_obj = ilObjectFactory::getInstanceByObjId($options['role_id'], false);
                if (!($role_obj instanceof \ilObjRole)) {
                    continue;
                }

                $starting_points[] = [
                    'id' => $available_starting_point['id'],
                    'criteria' => $role_obj->getTitle(),
                    'starting_page' => $sp_text,
                    'starting_position' => (int) $position,
                    'role_id' => $role_obj->getId()
                ];
            }
        }

        $default_sp = ilUserUtil::getStartingPoint();
        $starting_point = $valid_points[$default_sp];
        if ($default_sp == ilUserUtil::START_REPOSITORY_OBJ) {
            $reference_id = ilUserUtil::getStartingObject();

            $object_id = ilObject::_lookupObjId($reference_id);
            $type = $dc->lookupType($object_id);
            $title = $dc->lookupTitle($object_id);
            $starting_point = $this->lng->txt('obj_' . $type) . ' ' . '<i>"' . $title . '\' ($reference_id)</i>';
        }

        $starting_points[] = [
            'id' => 'default',
            'criteria' => $this->lng->txt('default'),
            'starting_page' => $starting_point,
            'starting_position' => self::TABLE_POSITION_DEFAULT
        ];

        $sorted_starting_points = ilStartingPoint::reArrangePositions(
            ilArrayUtil::sortArray($result, 'starting_position', 'asc', true)
        );

        $this->setData($sorted_starting_points);
    }

    protected function fillRow(array $row_data): void // Missing array type.
    {
        $list = new ilAdvancedSelectionListGUI();
        $list->setListTitle($this->lng->txt('actions'));

        $this->ctrl->setParameter($this->getParentObject(), 'spid', $row_data['id']);


        if ($row_data['id'] > 0
            && $row_data['id'] !== 'default'
            && $row_data['id'] !== 'user') {
            if (ilStartingPoint::ROLE_BASED) {
                $this->ctrl->setParameter($this->getParentObject(), 'rolid', $row_data['role_id']);
            }

            $list->setId($row_data['id']);

            $edit_url = $this->ctrl->getLinkTarget($this->getParentObject(), 'initRoleStartingPointForm');
            $list->addItem($lng->txt('edit'), '', $edit_url);
            $delete_url = $this->ctrl->getLinkTarget($this->getParentObject(), 'confirmDeleteStartingPoint');
            $list->addItem($lng->txt('delete'), '', $delete_url);
            $this->tpl->setVariable('VAL_ID', 'position[' . $row_data['id'] . ']');
            $this->tpl->setVariable('VAL_POS', $row_data['starting_position']);

            $parent_title = '';
            if (ilObject::_lookupType($row_data['role_id']) == 'role') {
                $ref_id = $this->rbacreview->getObjectReferenceOfRole($row_data['role_id']);
                if ($ref_id != ROLE_FOLDER_ID) {
                    $parent_title = ' (' . ilObject::_lookupTitle(ilObject::_lookupObjId($ref_id)) . ')';
                }
            }
            $this->tpl->setVariable('TXT_TITLE', $this->lng->txt('has_role') . ': ' .
                ilObjRole::_getTranslation($row_data['criteria']) . $parent_title);
        } else {
            if ($row_data['id'] == 'default') {
                $this->ctrl->setParameter($this->getParentObject(), 'rolid', 'default');
                $edit_url = $this->ctrl->getLinkTarget($this->getParentObject(), 'initRoleStartingPointForm');
            } else {
                $this->ctrl->setParameter($this->getParentObject(), 'rolid', 'user');
                $edit_url = $this->ctrl->getLinkTarget($this->getParentObject(), 'initUserStartingPointForm');
            }

            $list->addItem($lng->txt('edit'), '', $edit_url);

            $this->tpl->setVariable('HIDDEN', 'hidden');
            $this->tpl->setVariable('TXT_TITLE', $row_data['criteria']);
        }

        $this->tpl->setVariable('TXT_PAGE', $row_data['starting_page']);

        $this->tpl->setVariable('ACTION', $list->getHTML());
    }
}
