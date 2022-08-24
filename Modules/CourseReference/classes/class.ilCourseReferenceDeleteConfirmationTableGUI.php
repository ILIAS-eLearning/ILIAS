<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilCourseReferencePathInfo
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilCourseReferenceDeleteConfirmationTableGUI extends ilTable2GUI
{
    /**
     * @var \ilTree|null
     */
    private $tree = null;


    /**
     * @var object|null
     */
    private $member_obj = null;

    /**
     * @var int[]
     */
    private $participants = [];

    /**
     * ilCourseReferenceDeleteConfirmationTableGUI constructor.
     * @param object $gui
     * @param object $member_obj
     * @param string $cmd
     */
    public function __construct($gui, $member_obj, string $cmd)
    {
        global $DIC;

        $this->tree = $DIC->repositoryTree();

        $this->setId('crsr_mem_confirmation');
        parent::__construct($gui, $cmd);

        $this->member_obj = $member_obj;
    }

    /**
     * @param int[] $participants
     */
    public function setParticipants(array $participants)
    {
        $this->participants = $participants;
    }

    /**
     * Init table
     */
    public function init()
    {
        $this->setRowTemplate('tpl.crsr_mem_deletion_confirmation_row.html', 'Modules/CourseReference');
        $this->addCommandButton('deleteParticipantsWithLinkedCourses', $this->lng->txt('confirm'));
        $this->addCommandButton('participants', $this->lng->txt('cancel'));
        $this->setFormAction($this->ctrl->getFormAction($this->getParentObject()));

        $this->disable('sort');
        $this->setShowRowsSelector(false);
        $this->setSelectAllCheckbox('refs');

        $this->addColumn($this->lng->txt('type'), 'type', '50px');
        $this->addColumn($this->lng->txt('title'), 'title');
    }

    /**
     * Parse table content
     */
    public function parse()
    {
        $rows = [];
        foreach ($this->participants as $part_id) {
            $row = [];
            $row['type'] = 'usr';
            $row['id'] = $part_id;

            $name = ilObjUser::_lookupName($part_id);

            $row['title'] = ($name['lastname'] . ', ' . $name['firstname'] . ' [' . $name['login'] . ']');
            $rows[] = $row;
        }

        $this->setData($rows);
    }

    /**
     * @inheritdoc
     */
    public function fillRow(array $a_set): void
    {
        $this->tpl->setVariable('HIDDEN_NAME', $a_set['id']);
        $this->tpl->setVariable('TYPE_ICON', ilUtil::getImagePath('icon_usr.svg'));
        $this->tpl->setVariable('ALT_USR', $this->lng->txt('obj_usr'));
        $this->tpl->setVariable('VAL_LOGIN', $a_set['title']);

        $linked_course_assignments = $this->readLinkedCourseAssignments($a_set['id']);
        foreach ($linked_course_assignments as $course_ref_id) {
            $path = new ilPathGUI();
            $path->enableHideLeaf(false);
            $path->enableTextOnly(false);
            $this->tpl->setCurrentBlock('reference_path');
            $this->tpl->setVariable('CHECK_USER_NAME', 'refs[' . $a_set['id'] . '][' . $course_ref_id . ']');
            $this->tpl->setVariable('CHECK_USER_VAL', 1);
            $this->tpl->setVariable('REF_PATH', $path->getPath(ROOT_FOLDER_ID, (int) $course_ref_id));
            $this->tpl->parseCurrentBlock();
        }


        if (count($linked_course_assignments)) {
            $this->tpl->setCurrentBlock('reference_select');
            $this->tpl->setVariable('RUID', $a_set['id']);
            $this->tpl->setVariable('RFORM_ID', $this->formname);
            $this->tpl->setVariable('SEL_ALL', $this->lng->txt('select_all'));
            $this->tpl->parseCurrentBlock();
        }
    }

    /**
     * @param $part_id
     */
    protected function readLinkedCourseAssignments($part_id): array
    {
        $childs = $this->tree->getChildsByType($this->member_obj->getRefId(), 'crsr');
        $assigned_references = [];
        foreach ($childs as $tree_node) {
            $path_info = ilCourseReferencePathInfo::getInstanceByRefId($tree_node['child']);
            if (!$path_info->hasParentCourse()) {
                continue;
            }
            if (!$path_info->isMemberUpdateEnabled()) {
                continue;
            }
            if (!$path_info->checkManagmentAccess()) {
                continue;
            }
            $course_ref_id = $path_info->getTargetId();
            $part = ilCourseParticipants::getInstance($course_ref_id);
            if ($part->isMember($part_id)) {
                $assigned_references[] = $course_ref_id;
            }
        }
        return $assigned_references;
    }
}
