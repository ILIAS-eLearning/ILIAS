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

declare(strict_types=0);

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Course\Grouping\Table\GroupingHandler as GroupingTableHandler;
use ILIAS\Course\Grouping\Table\AssignmentHandler as AssignmentTableHandler;

/**
 * Class ilObjCourseGroupingGUI
 * @author your name <your email>
 */
class ilObjCourseGroupingGUI
{
    private ilObjCourseGrouping $grp_obj;
    private int $id;
    private ilObject $content_obj;
    private string $content_type = '';

    protected ilGlobalTemplateInterface $tpl;
    protected ilCtrlInterface $ctrl;
    protected ilLanguage $lng;
    protected ilErrorHandling $error;
    protected ilAccessHandler $access;
    protected ilTabsGUI $tabs;
    protected ilToolbarGUI $toolbar;
    protected GlobalHttpState $http;
    protected RefineryFactory $refinery;
    protected UIRenderer $ui_renderer;

    protected GroupingTableHandler $grouping_table_handler;
    protected AssignmentTableHandler $assignment_table_handler;

    public function __construct(ilObject $content_obj, int $a_obj_id = 0)
    {
        global $DIC;

        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->error = $DIC['ilErr'];
        $this->tabs = $DIC->tabs();
        $this->toolbar = $DIC->toolbar();
        $this->http = $DIC->http();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->refinery = $DIC->refinery();

        $this->content_obj = $content_obj;
        $this->content_type = ilObject::_lookupType($this->content_obj->getId());

        $this->id = $a_obj_id;
        $this->ctrl->saveParameter($this, 'obj_id');
        $this->__initGroupingObject();

        $data_factory = new DataFactory();
        $this->grouping_table_handler = new GroupingTableHandler(
            $this->ctrl->getLinkTarget($this, 'handleGroupingTableAction'),
            $this->content_obj->getId(),
            $this->lng,
            $DIC->ui()->factory(),
            $data_factory,
            $this->http,
            $this->refinery,
            $DIC['static_url']
        );
        $this->assignment_table_handler = new AssignmentTableHandler(
            $this->ctrl->getLinkTarget($this, 'handleAssignmentTableAction'),
            $this->content_obj->getId(),
            $this->grp_obj,
            $this->lng,
            $DIC->ui()->factory(),
            $data_factory,
            $this->http,
            $this->refinery,
            $DIC->user(),
            $DIC->repositoryTree()
        );
    }

    public function executeCommand(): void
    {
        $this->tabs->setTabActive('crs_groupings');
        $cmd = $this->ctrl->getCmd();
        if (!$cmd = $this->ctrl->getCmd()) {
            $cmd = "edit";
        }
        $this->$cmd();
    }

    public function __initGroupingObject(): void
    {
        $this->grp_obj = new ilObjCourseGrouping($this->id);
    }

    public function getContentType(): string
    {
        return $this->content_type;
    }

    public function listGroupings(): void
    {
        if (!$this->access->checkAccess('write', '', $this->content_obj->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $this->toolbar->addButton(
            $this->lng->txt('crs_add_grouping'),
            $this->ctrl->getLinkTarget($this, 'create')
        );

        $table = $this->grouping_table_handler->getTable();
        $this->tpl->setContent($this->ui_renderer->render($table));
    }

    public function handleGroupingTableAction(): void
    {
        switch ($this->grouping_table_handler->getSelectedTableAction()) {
            case GroupingTableHandler::ACTION_EDIT:
                $selected = $this->grouping_table_handler->getSelectedGroupingIDs()[0] ?? 0;
                $this->ctrl->setParameter($this, 'obj_id', $selected);
                $this->ctrl->redirect($this, 'edit');
                break;

            case GroupingTableHandler::ACTION_DELETE:
                $this->askDeleteGrouping(...$this->grouping_table_handler->getSelectedGroupingIDs());
                break;
        }
    }

    public function askDeleteGrouping(int ...$grouping_ids): void
    {
        if (!$this->access->checkAccess('write', '', $this->content_obj->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        if (!count($grouping_ids)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('crs_grouping_select_one'));
            $this->listGroupings();
            return;
        }

        // display confirmation message
        $cgui = new ilConfirmationGUI();
        $cgui->setFormAction($this->ctrl->getFormAction($this));
        $cgui->setHeaderText($this->lng->txt("crs_grouping_delete_sure"));
        $cgui->setCancel($this->lng->txt("cancel"), "listGroupings");
        $cgui->setConfirm($this->lng->txt("delete"), "deleteGrouping");

        // list objects that should be deleted
        foreach ($grouping_ids as $grouping_id) {
            $tmp_obj = new ilObjCourseGrouping($grouping_id);
            $cgui->addItem("grouping[]", $grouping_id, $tmp_obj->getTitle());
        }
        $this->tpl->setContent($cgui->getHTML());
    }

    public function deleteGrouping(): void
    {
        if (!$this->access->checkAccess('write', '', $this->content_obj->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }
        $grouping = [];
        if ($this->http->wrapper()->post()->has('grouping')) {
            $grouping = $this->http->wrapper()->post()->retrieve(
                'grouping',
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
        }

        foreach ($grouping as $grouping_id) {
            $tmp_obj = new ilObjCourseGrouping((int) $grouping_id);
            $tmp_obj->delete();
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('crs_grouping_deleted'), true);
        $this->ctrl->redirect($this, 'listGroupings');
    }

    public function create(?ilPropertyFormGUI $a_form = null): void
    {
        if (!$this->access->checkAccess('write', '', $this->content_obj->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        if (!$a_form) {
            $a_form = $this->initForm(true);
        }

        $this->tpl->setContent($a_form->getHTML());
    }

    public function initForm(bool $a_create): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));

        $title = new ilTextInputGUI($this->lng->txt('title'), 'title');
        $title->setRequired(true);
        $form->addItem($title);

        $desc = new ilTextAreaInputGUI($this->lng->txt('description'), 'description');
        $form->addItem($desc);

        $options = array('login' => 'login',
                         'email' => 'email',
                         'matriculation' => 'matriculation'
        );

        foreach ($options as $value => $caption) {
            $options[$value] = $this->lng->txt($caption);
        }
        $uniq = new ilSelectInputGUI($this->lng->txt('unambiguousness'), 'unique');
        $uniq->setRequired(true);
        $uniq->setOptions($options);
        $form->addItem($uniq);

        if ($a_create) {
            $form->setTitle($this->lng->txt('crs_add_grouping'));
            $form->addCommandButton('add', $this->lng->txt('btn_add'));
        } else {
            $grouping = new ilObjCourseGrouping($this->id);
            $title->setValue($grouping->getTitle());
            $desc->setValue($grouping->getDescription());
            $uniq->setValue($grouping->getUniqueField());

            $ass = new ilCustomInputGUI($this->lng->txt('groupings_assigned_obj_' . $this->getContentType()));
            $form->addItem($ass);

            // assignments
            $items = array();
            foreach ($grouping->getAssignedItems() as $cond_data) {
                $items[] = ilObject::_lookupTitle($cond_data['target_obj_id']);
            }
            if ($items !== []) {
                $ass->setHtml(implode("<br />", $items));
            } else {
                $ass->setHtml($this->lng->txt('crs_grp_no_courses_assigned'));
            }

            $form->setTitle($this->lng->txt('edit_grouping'));
            $form->addCommandButton('update', $this->lng->txt('save'));
            $form->addCommandButton('selectCourse', $this->lng->txt('grouping_change_assignment'));
        }
        $form->addCommandButton('listGroupings', $this->lng->txt('cancel'));
        return $form;
    }

    public function add(): void
    {
        $form = $this->initForm(true);
        if ($form->checkInput()) {
            $this->grp_obj->setTitle($form->getInput('title'));
            $this->grp_obj->setDescription($form->getInput('description'));
            $this->grp_obj->setUniqueField($form->getInput('unique'));

            $this->grp_obj->create($this->content_obj->getRefId(), $this->content_obj->getId());
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('crs_grp_added_grouping'), true);
            $this->ctrl->redirect($this, 'listGroupings');
        }
        $form->setValuesByPost();
        $this->create($form);
    }

    public function edit(?ilPropertyFormGUI $a_form = null): void
    {
        if (!$this->access->checkAccess('write', '', $this->content_obj->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }
        if (!$a_form) {
            $a_form = $this->initForm(false);
        }
        $this->tpl->setContent($a_form->getHTML());
    }

    public function update(): void
    {
        if (!$this->access->checkAccess('write', '', $this->content_obj->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $obj_id = 0;
        if ($this->http->wrapper()->query()->has('obj_id')) {
            $obj_id = $this->http->wrapper()->query()->retrieve(
                'obj_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        $form = $this->initForm(false);
        if ($form->checkInput()) {
            $tmp_grouping = new ilObjCourseGrouping($obj_id);
            $tmp_grouping->setTitle($form->getInput('title'));
            $tmp_grouping->setDescription($form->getInput('description'));
            $tmp_grouping->setUniqueField($form->getInput('unique'));
            $tmp_grouping->update();

            $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, 'listGroupings');
        }

        $form->setValuesByPost();
        $this->edit($form);
    }

    public function selectCourse(): void
    {
        if (!$this->access->checkAccess('write', '', $this->content_obj->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        if (!$this->id) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('crs_grp_no_grouping_id_given'));
            $this->listGroupings();
            return;
        }

        $this->tabs->clearTargets();
        $this->tabs->setBackTarget(
            $this->lng->txt('back'),
            $this->ctrl->getLinkTarget($this, 'edit')
        );

        $table = $this->assignment_table_handler->getTable();
        $this->tpl->setContent($this->ui_renderer->render($table));
    }

    public function handleAssignmentTableAction(): void
    {
        switch ($this->assignment_table_handler->getSelectedTableAction()) {
            case AssignmentTableHandler::ACTION_TOGGLE_ASSIGNMENT:
                $this->assignCourse(...$this->assignment_table_handler->getSelectedRefIDs());
                break;
        }
    }

    public function assignCourse(int ...$ref_ids): void
    {
        if (!$this->access->checkAccess('write', '', $this->content_obj->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        if (!$this->id) {
            $this->listGroupings();
            return;
        }

        // add assignments of not selected items that were assigned
        // add assignments for selected items that were not assigned
        $grouping = new ilObjCourseGrouping($this->id);
        $assigned = $grouping->getAssignedItems();
        $old_assigned_ref_ids = [];
        foreach ($assigned as $item) {
            $old_assigned_ref_ids[] = $item['target_ref_id'];
        }
        $new_assigned_ref_ids = array_merge(
            array_diff($old_assigned_ref_ids, $ref_ids),
            array_diff($ref_ids, $old_assigned_ref_ids)
        );

        // delete all existing conditions
        $condh = new ilConditionHandler();
        $condh->deleteByObjId($this->id);

        foreach ($new_assigned_ref_ids as $ref_id) {
            $tmp_crs = ilObjectFactory::getInstanceByRefId($ref_id);
            $tmp_condh = new ilConditionHandler();
            $tmp_condh->enableAutomaticValidation(false);

            $tmp_condh->setTargetRefId($ref_id);
            $tmp_condh->setTargetObjId($tmp_crs->getId());
            $tmp_condh->setTargetType($this->getContentType());
            $tmp_condh->setTriggerRefId(0);
            $tmp_condh->setTriggerObjId($this->id);
            $tmp_condh->setTriggerType('crsg');
            $tmp_condh->setOperator('not_member');
            $tmp_condh->setValue($this->grp_obj->getUniqueField());

            if (!$tmp_condh->checkExists()) {
                $tmp_condh->storeCondition();
            }
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'edit');
    }
} // END class.ilObjCourseGrouping
