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

use ILIAS\Refinery\Factory;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Conditions\Configuration\ConditionTriggerTableGUI;
use ILIAS\Conditions\Configuration\ConditionTriggerProvider as ConditionTriggerProvider;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\UI\Component\Input\Container\Form\Standard as StandardForm;
use ILIAS\UI\Component\Input\Field\Section as Section;
use ILIAS\Refinery\ConstraintViolationException;

/**
 * class ilConditionHandlerGUI
 * @author       Stefan Meyer <meyer@leifos.com>
 * @version      $Id$
 * This class is aggregated in folders, groups which have a parent course object
 * Since it is something like an interface, all varirables, methods have there own name space (names start with cci) to avoid collisions
 * @ilCtrl_Calls ilConditionHandlerGUI:
 */
class ilConditionHandlerGUI
{
    private const LIST_MODE_UNDEFINED = 'undefined';
    private const LIST_MODE_ALL = 'all';
    private const LIST_MODE_SUBSET = 'subset';

    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ilTree $tree;
    protected ilAccessHandler $access;
    protected ilToolbarGUI $toolbar;
    protected ilConditionUtil $conditionUtil;
    protected ilObjectDefinition $objectDefinition;
    private GlobalHttpState $http;
    private Factory $refinery;
    private UIFactory $ui_factory;
    private UIRenderer $ui_renderer;

    protected ilConditionHandler $ch_obj;
    protected ?ilObject $target_obj = null;
    protected int $target_id = 0;
    protected string $target_type = '';
    protected string $target_title = '';
    protected int $target_ref_id = 0;

    protected bool $automatic_validation = true;

    public function __construct(?int $a_ref_id = null)
    {
        global $DIC;

        $this->ch_obj = new ilConditionHandler();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('rbac');
        $this->lng->loadLanguageModule('cond');
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->tree = $DIC->repositoryTree();
        $this->access = $DIC->access();
        $this->toolbar = $DIC->toolbar();
        $this->conditionUtil = $DIC->conditions()->util();
        $this->objectDefinition = $DIC['objDefinition'];
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();

        if ($a_ref_id) {
            $target_obj = ilObjectFactory::getInstanceByRefId($a_ref_id);
            if ($target_obj !== null) {
                $this->setTargetId($target_obj->getId());
                $this->setTargetRefId($target_obj->getRefId());
                $this->setTargetType($target_obj->getType());
                $this->setTargetTitle($target_obj->getTitle());
            }
        }
    }


    protected function initConditionIdFromQuery(): int
    {
        if ($this->http->wrapper()->query()->has('condition_id')) {
            return $this->http->wrapper()->query()->retrieve(
                'condition_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        return 0;
    }

    protected function initConditionsIdsFromPost(): SplFixedArray
    {
        if ($this->http->wrapper()->post()->has('conditions')) {
            return SplFixedArray::fromArray(
                $this->http->wrapper()->post()->retrieve(
                    'conditions',
                    $this->refinery->kindlyTo()->listOf(
                        $this->refinery->kindlyTo()->int()
                    )
                )
            );
        }
        return new SplFixedArray(0);
    }

    protected function initItemIdsFromPost(): SplFixedArray
    {
        if ($this->http->wrapper()->post()->has('item_ids')) {
            return SplFixedArray::fromArray(
                $this->http->wrapper()->post()->retrieve(
                    'item_ids',
                    $this->refinery->kindlyTo()->listOf(
                        $this->refinery->kindlyTo()->int()
                    )
                )
            );
        }
        return new SplFixedArray(0);
    }

    protected function initObligatoryItemsFromPost(): SplFixedArray
    {
        if ($this->http->wrapper()->post()->has('obl')) {
            return SplFixedArray::fromArray(
                $this->http->wrapper()->post()->retrieve(
                    'obl',
                    $this->refinery->kindlyTo()->listOf(
                        $this->refinery->kindlyTo()->int()
                    )
                )
            );
        }
        return new SplFixedArray(0);
    }

    protected function initListModeFromPost(): string
    {
        return $this->http->wrapper()->post()->retrieve(
            'list_mode',
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->string(),
                $this->refinery->always(self::LIST_MODE_UNDEFINED)
            ])
        );
    }

    protected function initSourceIdFromQuery(): int
    {
        if ($this->http->wrapper()->query()->has('source_id')) {
            return $this->http->wrapper()->query()->retrieve(
                'source_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        return 0;
    }

    public static function translateOperator(int $a_obj_id, string $a_operator, string $value = ''): string
    {
        global $DIC;

        $lng = $DIC->language();
        switch ($a_operator) {
            case ilConditionHandler::OPERATOR_LP:
                $lng->loadLanguageModule('trac');

                $obj_settings = new ilLPObjSettings($a_obj_id);
                return ilLPObjSettings::_mode2Text($obj_settings->getMode());

            case ilConditionHandler::OPERATOR_RESULT_RANGE_PERCENTAGE:
                $postfix = '';
                $value_arr = unserialize($value);
                if ($value_arr !== false) {
                    $postfix = ', ';
                    if (ilObject::_lookupType($a_obj_id) === 'crs') {
                        $postfix .= ilCourseObjective::lookupObjectiveTitle((int) $value_arr['objective']) . ' ';
                    }
                    $postfix .= $value_arr['min_percentage'] . ' - ' . $value_arr['max_percentage'] . '%';
                }
                return $lng->txt('condition_' . $a_operator) . $postfix;

            default:
                $lng->loadLanguageModule('rbac');
                return $lng->txt('condition_' . $a_operator);
        }
    }

    protected function getConditionHandler(): ilConditionHandler
    {
        return $this->ch_obj;
    }

    public function setBackButtons(array $a_btn_arr): void
    {
        ilSession::set('precon_btn', $a_btn_arr);
    }

    public function getBackButtons(): array
    {
        if (ilSession::has('precon_btn')) {
            return ilSession::get('precon_btn');
        }
        return [];
    }

    public function executeCommand(): void
    {
        global $DIC;

        $ilErr = $DIC['ilErr'];

        if (!$this->access->checkAccess('write', '', $this->getTargetRefId())) {
            $ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->WARNING);
        }

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        switch ($next_class) {
            default:
                if (empty($cmd)) {
                    $cmd = "view";
                }
                $this->$cmd();
                break;
        }
    }

    public function setAutomaticValidation(bool $a_status): void
    {
        $this->automatic_validation = $a_status;
    }

    public function getAutomaticValidation(): bool
    {
        return $this->automatic_validation;
    }

    public function setTargetId(int $a_target_id): void
    {
        $this->target_id = $a_target_id;
    }

    public function getTargetId(): int
    {
        return $this->target_id;
    }

    public function setTargetRefId(int $a_target_ref_id): void
    {
        $this->target_ref_id = $a_target_ref_id;
    }

    public function getTargetRefId(): int
    {
        return $this->target_ref_id;
    }

    public function setTargetType(string $a_target_type): void
    {
        $this->target_type = $a_target_type;
    }

    public function getTargetType(): string
    {
        return $this->target_type;
    }

    public function setTargetTitle(string $a_target_title): void
    {
        $this->target_title = $a_target_title;
    }

    /**
     * Check if target has refernce id
     */
    public function isTargetReferenced(): bool
    {
        return (bool) $this->getTargetRefId();
    }

    public function getTargetTitle(): string
    {
        return $this->target_title;
    }




    protected function showObligatoryForm(
        string $list_mode = self::LIST_MODE_ALL
    ): ?ilPropertyFormGUI {
        if (!$this->objectDefinition->isRBACObject($this->getTargetType())) {
            return null;
        }

        $all = ilConditionHandler::_getPersistedConditionsOfTarget($this->getTargetRefId(), $this->getTargetId());
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, 'listConditions'));
        $form->setTitle($this->lng->txt('precondition_obligatory_settings'));
        $form->addCommandButton('saveObligatorySettings', $this->lng->txt('save'));

        $hide = new ilCheckboxInputGUI($this->lng->txt('rbac_precondition_hide'), 'hidden');
        $hide->setChecked(ilConditionHandler::lookupPersistedHiddenStatusByTarget($this->getTargetRefId()));
        $hide->setValue("1");
        $hide->setInfo($this->lng->txt('rbac_precondition_hide_info'));
        $form->addItem($hide);

        $mode = new ilRadioGroupInputGUI($this->lng->txt("rbac_precondition_mode"), "list_mode");
        $form->addItem($mode);
        $mode->setValue($list_mode);

        $mall = new ilRadioOption($this->lng->txt("rbac_precondition_mode_all"), "all");
        $mall->setInfo($this->lng->txt("rbac_precondition_mode_all_info"));
        $mode->addOption($mall);

        if (count($all) > 1) {
            $min = 1;
            $max = count($all) - 1;

            $msubset = new ilRadioOption($this->lng->txt("rbac_precondition_mode_subset"), "subset");
            $msubset->setInfo($this->lng->txt("rbac_precondition_mode_subset_info"));
            $mode->addOption($msubset);

            $obl = new ilNumberInputGUI($this->lng->txt('precondition_num_obligatory'), 'required');
            $obl->setInfo($this->lng->txt('precondition_num_optional_info'));

            $num_required = ilConditionHandler::lookupObligatoryConditionsOfTarget(
                $this->getTargetRefId(),
                $this->getTargetId()
            );
            $obl->setValue($num_required > 0 ? (string) $num_required : null);
            $obl->setRequired(true);
            $obl->setSize(1);
            $obl->setMinValue($min);
            $obl->setMaxValue($max);
            $msubset->addSubItem($obl);
        }

        $old_mode = new ilHiddenInputGUI("old_list_mode");
        $old_mode->setValue($list_mode);
        $form->addItem($old_mode);

        return $form;
    }

    public function edit(?ilPropertyFormGUI $form = null): void
    {
        $condition_id = $this->initConditionIdFromQuery();
        if (!$condition_id) {
            $this->tpl->setOnScreenMessage('failure', "Missing id: condition_id");
            $this->listConditions();
            return;
        }
        $this->ctrl->setParameter($this, 'condition_id', $condition_id);
        $condition = ilConditionHandler::_getCondition($condition_id);

        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initFormCondition($condition['trigger_ref_id'], $condition_id, 'edit');
        }
        $this->tpl->setContent($form->getHTML());
    }

    public function updateCondition(): void
    {
        $condition_id = $this->initConditionIdFromQuery();
        if (!$condition_id) {
            $this->tpl->setOnScreenMessage('failure', "Missing id: condition_id");
            $this->listConditions();
            return;
        }
        // Update condition
        $condition_handler = new ilConditionHandler();
        $condition = ilConditionHandler::_getCondition($condition_id);

        $form = $this->initFormCondition(
            $condition['trigger_ref_id'],
            $condition_id,
            'edit'
        );

        if (!$form->checkInput()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('err_check_input'));
            $this->edit($form);
            return;
        }

        $condition_handler->setOperator((string) $form->getInput('operator'));
        $condition_handler->setObligatory((bool) $form->getInput('obligatory'));
        $condition_handler->setTargetRefId($this->getTargetRefId());
        $condition_handler->setValue('');
        switch ($this->getTargetType()) {
            case 'st':
                $condition_handler->setReferenceHandlingType((int) $form->getInput('ref_handling'));
                break;

            default:
                $condition_handler->setReferenceHandlingType(ilConditionHandler::UNIQUE_CONDITIONS);
                break;
        }
        $condition_handler->updateCondition($condition['id']);

        // Update relevant sco's
        if ($condition['trigger_type'] === 'sahs') {
            $olp = ilObjectLP::getInstance($condition['trigger_obj_id']);
            $collection = $olp->getCollectionInstance();
            if ($collection) {
                $collection->delete();
            }
            $item_ids = $this->initItemIdsFromPost();
            if (count($item_ids)) { // #12901
                $collection->activateEntries($item_ids->toArray());
            }
            ilLPStatusWrapper::_refreshStatus($condition['trigger_obj_id']);
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'listConditions');
    }

    protected function adjustConditionsAfterDeletion(): void
    {
        $conditions = ilConditionHandler::_getPersistedConditionsOfTarget(
            $this->getTargetRefId(),
            $this->getTargetId()
        );
        $optional_conditions = ilConditionHandler::getPersistedOptionalConditionsOfTarget(
            $this->getTargetRefId(),
            $this->getTargetId()
        );
        if (
            count($conditions) === 1 &&
            count($optional_conditions) > 0
        ) {
            // set to obligatory
            foreach ($conditions as $condition) {
                ilConditionHandler::updateObligatory($condition['id'], true);
            }
        }
        $num_obligatory = ilConditionHandler::lookupObligatoryConditionsOfTarget(
            $this->getTargetRefId(),
            $this->getTargetId()
        );
        if (
            $num_obligatory == count($conditions)
        ) {
            // set all obligatory
            foreach ($conditions as $condition) {
                ilConditionHandler::updateObligatory($condition['id'], true);
            }
        } elseif (
            $num_obligatory > count($conditions)
        ) {
            // reduce required triggers to maximum
            ilConditionHandler::saveNumberOfRequiredTriggers(
                $this->getTargetRefId(),
                $this->getTargetId(),
                count($conditions) - 1
            );
        }
    }

    public function selector(): void
    {
        $this->tpl->setOnScreenMessage('info', $this->lng->txt("condition_select_object"));

        $exp = new ilConditionSelector($this, "selector");
        $exp->setTypeWhiteList(array_merge(
            $this->getConditionHandler()->getTriggerTypes(),
            array("root", "cat", "grp", "fold", "crs", "prg")
        ));
        //setRefId have to be after setTypeWhiteList!
        $exp->setRefId($this->getTargetRefId());
        $exp->setClickableTypes($this->getConditionHandler()->getTriggerTypes());

        if (!$exp->handleCommand()) {
            $this->tpl->setContent($exp->getHTML());
        }
    }

    public function add(?ilPropertyFormGUI $form = null): void
    {
        $source_id = $this->initSourceIdFromQuery();
        if (!$source_id) {
            $this->tpl->setOnScreenMessage('failure', "Missing id: condition_id");
            $this->selector();
            return;
        }
        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initFormCondition($source_id, 0, 'add');
        }
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * assign new trigger condition to target
     */
    public function assign(): void
    {
        $source_id = $this->initSourceIdFromQuery();
        if (!$source_id) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('no_condition_selected'));
            $this->selector();
            return;
        }

        $form = $this->initFormCondition($source_id, 0, 'add');
        if (!$form->checkInput()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('err_check_input'));
            $this->add($form);
            return;
        }
        $this->ch_obj->setTargetRefId($this->getTargetRefId());
        $this->ch_obj->setTargetObjId($this->getTargetId());
        $this->ch_obj->setTargetType($this->getTargetType());

        switch ($this->getTargetType()) {
            case 'st':
                $this->ch_obj->setReferenceHandlingType((int) $form->getInput('ref_handling'));
                break;

            default:
                $this->ch_obj->setReferenceHandlingType(ilConditionHandler::UNIQUE_CONDITIONS);
                break;
        }
        // this has to be changed, if non referenced trigger are implemted
        $trigger_obj = ilObjectFactory::getInstanceByRefId($source_id);
        if ($trigger_obj !== null) {
            $this->ch_obj->setTriggerRefId($trigger_obj->getRefId());
            $this->ch_obj->setTriggerObjId($trigger_obj->getId());
            $this->ch_obj->setTriggerType($trigger_obj->getType());
        }
        $this->ch_obj->setOperator($form->getInput('operator'));
        $this->ch_obj->setObligatory((bool) $form->getInput('obligatory'));
        $this->ch_obj->setHiddenStatus(ilConditionHandler::lookupPersistedHiddenStatusByTarget($this->getTargetRefId()));
        $this->ch_obj->setValue('');

        // Save assigned sco's
        if ($this->ch_obj->getTriggerType() === 'sahs') {
            $olp = ilObjectLP::getInstance($this->ch_obj->getTriggerObjId());
            $collection = $olp->getCollectionInstance();
            if ($collection) {
                $collection->delete();
            }

            $items_ids = $this->initItemIdsFromPost();
            if (count($items_ids)) { // #12901
                $collection->activateEntries($items_ids->toArray());
            }
        }
        $this->ch_obj->enableAutomaticValidation($this->getAutomaticValidation());
        if (!$this->ch_obj->storeCondition()) {
            $this->tpl->setOnScreenMessage('failure', $this->ch_obj->getErrorMessage(), true);
        } else {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('added_new_condition'), true);
        }
        $this->ctrl->redirect($this, 'listConditions');
    }

    public function getConditionsOfTarget(): array
    {
        $cond = [];
        foreach (ilConditionHandler::_getPersistedConditionsOfTarget(
            $this->getTargetRefId(),
            $this->getTargetId(),
            $this->getTargetType()
        ) as $condition) {
            if ($condition['operator'] === 'not_member') {
                continue;
            }

            $cond[] = $condition;
        }
        return $cond;
    }

    private function initFormCondition(
        int $a_source_id,
        int $a_condition_id = 0,
        string $a_mode = 'add'
    ): ilPropertyFormGUI {
        $trigger_obj_id = ilObject::_lookupObjId($a_source_id);
        $trigger_type = ilObject::_lookupType($trigger_obj_id);

        $condition = ilConditionHandler::_getCondition($a_condition_id);
        $form = new ilPropertyFormGUI();
        $this->ctrl->setParameter($this, 'source_id', $a_source_id);
        $form->setFormAction($this->ctrl->getFormAction($this));

        $info_source = new ilNonEditableValueGUI($this->lng->txt("rbac_precondition_source"));
        $info_source->setValue(ilObject::_lookupTitle(ilObject::_lookupObjId($a_source_id)));
        $form->addItem($info_source);

        $info_target = new ilNonEditableValueGUI($this->lng->txt("rbac_precondition_target"));
        $info_target->setValue($this->getTargetTitle());
        $form->addItem($info_target);

        $obl = new ilHiddenInputGUI('obligatory');
        if ($a_condition_id) {
            $obl->setValue((string) (bool) $condition['obligatory']);
        } else {
            $obl->setValue("1");
        }
        $form->addItem($obl);

        $sel = new ilSelectInputGUI($this->lng->txt('condition'), 'operator');
        $ch_obj = new ilConditionHandler();
        $operators = [];
        if ($a_mode === 'add') {
            $operators[''] = $this->lng->txt('select_one');
        }
        foreach ($ch_obj->getOperatorsByTriggerType($trigger_type) as $operator) {
            $operators[$operator] = $this->lng->txt('condition_' . $operator);
        }
        $sel->setValue($condition['operator'] ?? '');
        $sel->setOptions($operators);
        $sel->setRequired(true);
        $form->addItem($sel);

        if (ilConditionHandler::_isReferenceHandlingOptional($this->getTargetType())) {
            $rad_opt = new ilRadioGroupInputGUI($this->lng->txt('cond_ref_handling'), 'ref_handling');
            $rad_opt->setValue((string) ($condition['ref_handling'] ?? ilConditionHandler::SHARED_CONDITIONS));

            $opt2 = new ilRadioOption(
                $this->lng->txt('cond_ref_shared'),
                (string) ilConditionHandler::SHARED_CONDITIONS
            );
            $rad_opt->addOption($opt2);

            $opt1 = new ilRadioOption(
                $this->lng->txt('cond_ref_unique'),
                (string) ilConditionHandler::UNIQUE_CONDITIONS
            );
            $rad_opt->addOption($opt1);

            $form->addItem($rad_opt);
        }

        // Additional settings for SCO's
        if ($trigger_type === 'sahs') {
            $this->lng->loadLanguageModule('trac');

            $cus = new ilCheckboxGroupInputGUI($this->lng->txt('trac_sahs_relevant_items'), 'item_ids');
            $cus->setInfo($this->lng->txt('trac_lp_determination_info_sco'));
            $cus->setRequired(true);

            $olp = ilObjectLP::getInstance($trigger_obj_id);
            $collection = $olp->getCollectionInstance();
            $checked = [];
            if ($collection) {
                foreach ($collection->getPossibleItems() as $item_id => $sahs_item) {
                    $sco = new ilCheckboxOption($sahs_item['title'], (string) $item_id);
                    if ($collection->isAssignedEntry($item_id)) {
                        $checked[] = $item_id;
                    }
                    $cus->addOption($sco);
                }
            }
            $cus->setValue($checked);
            $form->addItem($cus);
        }
        switch ($a_mode) {
            case 'edit':
                $form->setTitleIcon(ilUtil::getImagePath('standard/icon_' . $this->getTargetType() . '.svg'));
                $form->setTitle($this->lng->txt('rbac_edit_condition'));
                $form->addCommandButton('updateCondition', $this->lng->txt('save'));
                $form->addCommandButton('listConditions', $this->lng->txt('cancel'));
                break;

            case 'add':
                $form->setTitleIcon(ilUtil::getImagePath('standard/icon_' . $this->getTargetType() . '.svg'));
                $form->setTitle($this->lng->txt('add_condition'));
                $form->addCommandButton('assign', $this->lng->txt('save'));
                $form->addCommandButton('selector', $this->lng->txt('back'));
                break;
        }
        return $form;
    }

    protected function listConditions(bool $load_form_with_request = false): void
    {
        $optional_conditions = ilConditionHandler::getPersistedOptionalConditionsOfTarget(
            $this->getTargetRefId(),
            $this->getTargetId(),
            $this->getTargetType()
        );
        $allow_optional_preconditions = (bool) count($optional_conditions);
        $table = new ConditionTriggerTableGUI(
            new ConditionTriggerProvider(
                $this->getTargetRefId(),
                $this->getTargetId(),
                $this->getTargetType()
            ),
            $allow_optional_preconditions
        );

        // add condition button
        $add_condition_trigger_button = $this->ui_factory->button()->standard(
            $this->lng->txt('add_condition'),
            $this->ctrl->getLinkTarget($this, 'selector')
        );
        $add_condition_trigger_button = $this->ui_renderer->render($add_condition_trigger_button);

        $form = $this->initCompulsoryForm($load_form_with_request);
        $form_content = '';
        if ($form instanceof StandardForm) {
            $form_content = $this->ui_renderer->render([$form]);
        }
        $this->tpl->setContent(
            $add_condition_trigger_button .
            $form_content .
            $table->render()
        );
    }

    protected function handleConditionTriggerTableActions(): void
    {
        $action = $this->http->wrapper()->query()->retrieve(
            ConditionTriggerTableGUI::ACTION_TOKEN_NS,
            $this->refinery->byTrying(
                [
                    $this->refinery->kindlyTo()->string(),
                    $this->refinery->always('')
                ]
            )
        );
        match ($action) {
            'editConditionTrigger' => $this->editConditionTrigger(),
            'saveCompulsory' => $this->saveCompulsoryStatus(),
            'confirmDeleteConditionTrigger' => $this->confirmDeleteConditionTrigger(),
            default => $this->ctrl->redirect($this, 'listConditions')
        };
    }

    protected function saveCompulsoryStatus(): void
    {
        $all_conditions = ilConditionHandler::_getPersistedConditionsOfTarget(
            $this->getTargetRefId(),
            $this->getTargetId(),
            $this->getTargetType()
        );

        $compulsory_ids = $this->http->wrapper()->query()->retrieve(
            ConditionTriggerTableGUI::ID_TOKEN_NS,
            $this->refinery->byTrying(
                [
                    $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int()),
                    $this->refinery->always([])
                ]
            )
        );

        if (count($compulsory_ids) > (count($all_conditions) - 2)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("rbac_precondition_minimum_optional"), true);
            $this->ctrl->redirect($this, 'listConditions');
        }

        foreach ($all_conditions as $item) {
            $status = false;
            if (in_array($item['condition_id'], $compulsory_ids)) {
                $status = true;
            }
            ilConditionHandler::updateObligatory($item['condition_id'], $status);
        }

        // re-calculate
        ilConditionHandler::calculatePersistedRequiredTriggers(
            $this->getTargetRefId(),
            $this->getTargetId(),
            $this->getTargetType(),
            true
        );

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'listConditions');
    }

    protected function confirmDeleteConditionTrigger(): void
    {
        $condition_trigger_ids = $this->http->wrapper()->query()->retrieve(
            ConditionTriggerTableGUI::ID_TOKEN_NS,
            $this->refinery->byTrying(
                [
                    $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int()),
                    // Actions for entire table sends a fixed token in an array, instead of all row ids
                    $this->refinery->custom()->transformation(function ($var) {
                        if (
                            is_array($var) &&
                            count($var) === 1 &&
                            (string) $var[0] === 'ALL_OBJECTS'
                        ) {
                            return $var;
                        }
                        throw new UnexpectedValueException('Unexpected string in row_id array.');
                    }),
                    $this->refinery->always([])
                ]
            )
        );

        if ($condition_trigger_ids === ['ALL_OBJECTS']) {
            $condition_trigger_ids = [];
            foreach (ilConditionHandler::_getPersistedConditionsOfTarget(
                $this->getTargetRefId(),
                $this->getTargetId(),
                $this->getTargetType()
            ) as $condition) {
                $condition_trigger_ids[] = $condition['id'];
            }
        }

        $items = [];
        foreach ($condition_trigger_ids as $condition_trigger_id) {
            $condition = ilConditionHandler::_getCondition($condition_trigger_id);
            $items[] = $this->ui_factory->modal()->interruptiveItem()->standard(
                (string) $condition_trigger_id,
                ilObject::_lookupTitle($condition['trigger_obj_id']) .
                ' (' . $this->lng->txt('condition') . ':' .
                $this->lng->txt('condition_' . $condition['operator']) . ')'
            );
        }

        $output = $this->ui_renderer->renderAsync(
            [
                $this->ui_factory->modal()->interruptive(
                    $this->lng->txt('confirm'),
                    $this->lng->txt('rbac_condition_delete_sure'),
                    $this->ctrl->getFormAction($this, 'deleteConditionTrigger')
                )->withAffectedItems($items)
        ]
        );

        $this->http->saveResponse($this->http->response()->withBody(
            Streams::ofString($output)
        ));
        $this->http->sendResponse();
        $this->http->close();
    }

    protected function deleteConditionTrigger(): void
    {
        $condition_trigger_ids = $this->http->wrapper()->post()->retrieve(
            'interruptive_items',
            $this->refinery->byTrying(
                [
                    $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int()),
                    $this->refinery->always([])
                ]
            )
        );
        if (!count($condition_trigger_ids)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('no_condition_selected'));
            $this->listConditions();
            return;
        }

        foreach ($condition_trigger_ids as $condition_id) {
            $this->ch_obj->deleteCondition($condition_id);
        }
        $this->adjustConditionsAfterDeletion();
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('condition_deleted'), true);
        $this->ctrl->redirect($this, 'listConditions');
    }

    protected function initCompulsoryForm(bool $with_request = false): ?StandardForm
    {
        if (!$this->objectDefinition->isRBACObject($this->getTargetType())) {
            return null;
        }

        $all_conditions = ilConditionHandler::_getPersistedConditionsOfTarget($this->getTargetRefId(), $this->getTargetId());
        $optional_conditions = ilConditionHandler::getPersistedOptionalConditionsOfTarget(
            $this->getTargetRefId(),
            $this->getTargetId(),
            $this->getTargetType()
        );

        $old_status = $this->ui_factory->input()->field()->hidden()->withValue(
            (string) (count($optional_conditions) ? self::LIST_MODE_SUBSET : self::LIST_MODE_ALL)
        );

        $hidden = $this->ui_factory->input()->field()->checkbox(
            $this->lng->txt('rbac_precondition_hide'),
            $this->lng->txt('rbac_precondition_hide_info')
        )->withValue(ilConditionHandler::lookupPersistedHiddenStatusByTarget($this->getTargetRefId()));

        $condition_mode_all = $this->ui_factory->input()->field()->group(
            [],
            $this->lng->txt('rbac_precondition_mode_all'),
            $this->lng->txt('rbac_precondition_mode_all_info')
        );
        $list_mode_items[self::LIST_MODE_ALL] = $condition_mode_all;

        $subset_limit = [];
        if (count($all_conditions) > 1) {

            $num_required = ilConditionHandler::lookupObligatoryConditionsOfTarget(
                $this->getTargetRefId(),
                $this->getTargetId()
            );
            $subset_limit['num_compulsory'] =
                $this->ui_factory->input()
                                 ->field()
                                 ->numeric(
                                     $this->lng->txt('precondition_num_obligatory'),
                                     $this->lng->txt('precondition_num_optional_info')
                                 )
                                 ->withValue($num_required > 0 ? $num_required : null)
                                 ->withAdditionalTransformation(
                                     $this->refinery->logical()->parallel(
                                         [
                                             $this->refinery->int()->isGreaterThan(0),
                                             $this->refinery->int()->isLessThan(count($all_conditions))
                                         ]
                                     )
                                 );
        }
        if (count($all_conditions) > 1) {
            $condition_mode_subset = $this->ui_factory->input()->field()->group(
                $subset_limit,
                $this->lng->txt('rbac_precondition_mode_subset'),
                $this->lng->txt('rbac_precondition_mode_subset_info')
            );
            $list_mode_items[self::LIST_MODE_SUBSET] = $condition_mode_subset;
        }

        $list_mode = $this->ui_factory->input()->field()->switchableGroup(
            $list_mode_items,
            $this->lng->txt('rbac_precondition_mode')
        )->withValue(
            count(ilConditionHandler::getPersistedOptionalConditionsOfTarget(
                $this->getTargetRefId(),
                $this->getTargetId(),
                $this->getTargetType()
            )) ? self::LIST_MODE_SUBSET : self::LIST_MODE_ALL
        );

        $main_section = $this->ui_factory->input()->field()->section(
            [
                'old_status' => $old_status,
                'hidden_status' => $hidden,
                'list_mode' => $list_mode
            ],
            $this->lng->txt('precondition_obligatory_settings')
        );
        $form = $this->ui_factory->input()->container()->form()->standard(
            $this->ctrl->getFormAction($this, 'saveCompulsoryForm'),
            [
                'compulsory_configuration' => $main_section
            ]
        );
        if ($with_request) {
            $form = $form->withRequest($this->http->request());
        }
        return $form;
    }

    protected function saveCompulsoryForm(): void
    {
        $form = $this->initCompulsoryForm(true);
        $data = $form->getData();
        if (!$data) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('err_check_input'));
            $this->listConditions(true);
            return;
        }
        $cond = new ilConditionHandler();
        $cond->setTargetRefId($this->getTargetRefId());
        $cond->updateHiddenStatus($data['compulsory_configuration']['hidden_status']);

        $old_status = $data['compulsory_configuration']['old_status'];
        switch ($data['compulsory_configuration']['list_mode'][0]) {
            case self::LIST_MODE_ALL:
                if ($old_status != self::LIST_MODE_ALL) {
                    $optional_conditions = ilConditionHandler::getPersistedOptionalConditionsOfTarget(
                        $this->getTargetRefId(),
                        $this->getTargetId(),
                        $this->getTargetType()
                    );
                    // Set all optional conditions to obligatory
                    foreach ($optional_conditions as $item) {
                        ilConditionHandler::updateObligatory($item["condition_id"], true);
                    }
                }
                break;
            case self::LIST_MODE_SUBSET:
                $num_required = $data['compulsory_configuration']['list_mode'][1]['num_compulsory'];
                if ($old_status != self::LIST_MODE_SUBSET) {
                    $all_conditions = ilConditionHandler::_getPersistedConditionsOfTarget(
                        $this->getTargetRefId(),
                        $this->getTargetId(),
                        $this->getTargetType()
                    );
                    foreach ($all_conditions as $item) {
                        ilConditionHandler::updateObligatory($item["condition_id"], false);
                    }
                }
                ilConditionHandler::saveNumberOfRequiredTriggers(
                    $this->getTargetRefId(),
                    $this->getTargetId(),
                    (int) $num_required
                );
                break;
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'listConditions');
    }

    protected function addConditionTrigger(bool $with_request = false): void
    {
        $source_id = $this->initSourceIdFromQuery();
        if (!$source_id) {
            $this->tpl->setOnScreenMessage('failure', "Missing id: condition_id");
            $this->selector();
            return;
        }
        $this->ctrl->setParameter($this, 'source_id', $source_id);
        $form = $this->initConditionTriggerForm($with_request, $source_id, 0);
        $this->tpl->setContent($this->ui_renderer->render([$form]));
    }

    protected function editConditionTrigger(bool $with_request = false): void
    {
        if ($this->http->wrapper()->query()->has(ConditionTriggerTableGUI::ID_TOKEN_NS)) {
            $condition_id = $this->http->wrapper()->query()->retrieve(
                ConditionTriggerTableGUI::ID_TOKEN_NS,
                $this->refinery->byTrying(
                    [
                        $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int()),
                        $this->refinery->always([])
                    ]
                )
            );
            $condition_id = end($condition_id);
        } else {
            $condition_id = $this->initConditionIdFromQuery();
        }
        if (!$condition_id) {
            $this->tpl->setOnScreenMessage('failure', "Missing id: condition_id");
            $this->listConditions();
            return;
        }
        $condition = ilConditionHandler::_getCondition($condition_id);
        $this->ctrl->setParameter($this, 'condition_id', $condition_id);
        $form = $this->initConditionTriggerForm(
            $with_request,
            $condition['trigger_ref_id'],
            $condition_id,
            'edit'
        );
        $this->tpl->setContent($this->ui_renderer->render([$form]));
    }

    private function initConditionTriggerForm(bool $with_request, int $trigger_id, int $condition_id, string $mode = 'add'): StandardForm
    {
        $trigger_type = ilObject::_lookupType($trigger_id, true);
        $trigger_obj_id = ilObject::_lookupObjId($trigger_id);
        $condition_handler = new ilConditionHandler();
        $condition = ilConditionHandler::_getCondition($condition_id);

        if ($mode == 'edit') {
            $main_section_items['obligatory'] = $this->ui_factory->input()->field()->hidden()
                ->withValue($condition['obligatory']);
        }

        $group_items = [];
        foreach ($condition_handler->getOperatorsByTriggerType($trigger_type) as $operator) {
            switch ($operator) {
                case ilConditionHandler::OPERATOR_RESULT_RANGE_PERCENTAGE:
                    $group_items[$operator] = $this->ui_factory->input()->field()->group(
                        [
                            'result_range_percentage' => $this->initRangeConditionInputItem(
                                $trigger_id,
                                $trigger_obj_id,
                                $condition
                            )
                        ],
                        $this->lng->txt('condition_' . $operator)
                    );
                    break;
                default:
                    $group_items[$operator] = $this->ui_factory->input()->field()->group(
                        [],
                        $this->lng->txt('condition_' . $operator)
                    );

            }
        }
        $main_section_items['operator'] = $this->ui_factory->input()->field()->switchableGroup(
            $group_items,
            $this->lng->txt('condition')
        )->withRequired(true);
        if ($mode == 'edit') {
            $main_section_items['operator'] = $main_section_items['operator']->withValue((string) ($condition['operator']));
        }

        // main section
        $main_section = $this->ui_factory->input()->field()->section(
            $main_section_items,
            $this->lng->txt('add_condition') . ' (' . ilObject::_lookupTitle($trigger_obj_id) . ')'
        );
        // form
        $form = $this->ui_factory->input()->container()->form()->standard(
            $this->ctrl->getLinkTarget($this, $mode === 'add' ? 'saveConditionTrigger' : 'updateConditionTrigger'),
            [
                'condition_configuration' => $main_section
            ]
        );
        if ($with_request) {
            $form = $form->withRequest($this->http->request());
        }
        return $form;
    }

    protected function initRangeConditionInputItem(int $trigger_ref_id, int $trigger_obj_id, array $condition): Section
    {
        list($stored_objective_id, $stored_min, $stored_max) = $this->extractValueOptionsFromCondition($condition);
        if (ilObject::_lookupType($trigger_obj_id) === 'crs') {
            $course = ilObjectFactory::getInstanceByRefId($trigger_ref_id);
            if (($course instanceof ilObjCourse) && $course->getViewMode() == ilCourseConstants::IL_CRS_VIEW_OBJECTIVE) {
                $select_options = [];
                foreach (ilCourseObjective::_getObjectiveIds($trigger_obj_id) as $objective_id) {
                    $objective = new ilCourseObjective($course, $objective_id);
                    $select_options[$objective_id] = $objective->getTitle();
                }
                $this->lng->loadLanguageModule('crs');
                $sections['objective'] = $this->ui_factory->input()->field()->select(
                    $this->lng->txt('crs_objectives'),
                    $select_options,
                )->withRequired(true);
                if ($stored_objective_id > 0) {
                    $sections['objective'] = $sections['objective']->withValue($stored_objective_id);
                }
            }
        }
        $sections['min'] = $this->ui_factory->input()->field()->numeric(
            $this->lng->txt('precondition_operator_range_min'),
        )->withAdditionalTransformation(
            $this->refinery->logical()->parallel(
                [
                    $this->refinery->int()->isGreaterThanOrEqual(0),
                    $this->refinery->int()->isLessThanOrEqual(100)
                ]
            )
        )->withRequired(true)
         ->withValue($stored_min);
        $sections['max'] = $this->ui_factory->input()->field()->numeric(
            $this->lng->txt('precondition_operator_range_max'),
        )->withAdditionalTransformation(
            $this->refinery->logical()->parallel(
                [
                    $this->refinery->int()->isGreaterThan(0),
                    $this->refinery->int()->isLessThanOrEqual(100)
                ]
            )
        )->withRequired(true)
         ->withValue($stored_max);
        return $this->ui_factory->input()->field()->section(
            $sections,
            ''
        )->withAdditionalTransformation(
            $this->refinery->custom()->constraint(
                function ($min_max) {
                    return $min_max['min'] < $min_max['max'];
                },
                $this->lng->txt('precondition_operator_range_err_min_max')
            )
        );
    }

    protected function extractValueOptionsFromInput(array $data): string
    {
        if ($data['condition_configuration']['operator'][0] !== ilConditionHandler::OPERATOR_RESULT_RANGE_PERCENTAGE) {
            return '';
        }
        return serialize(
            [
                'objective' => $data['condition_configuration']['operator'][1]['result_range_percentage']['objective'] ?? null,
                'min_percentage' => $data['condition_configuration']['operator'][1]['result_range_percentage']['min'],
                'max_percentage' => $data['condition_configuration']['operator'][1]['result_range_percentage']['max']

            ]
        );
    }

    protected function extractValueOptionsFromCondition(array $condition): array
    {
        if (($condition['value'] ?? '') === '') {
            return [0,0,0];
        }
        $value_arr = unserialize($condition['value']);
        if ($value_arr === false) {
            return [0,0,0];
        }
        return [
            $value_arr['objective'] ?? 0,
            $value_arr['min_percentage'] ?? 0,
            $value_arr['max_percentage'] ?? 0
        ];
    }

    protected function updateConditionTrigger(): void
    {
        $condition_id = $this->initConditionIdFromQuery();
        if (!$condition_id) {
            $this->tpl->setOnScreenMessage('failure', "Missing id: condition_id");
            $this->listConditions();
            return;
        }
        $condition = ilConditionHandler::_getCondition($condition_id);
        $form = $this->initConditionTriggerForm(true, $condition['trigger_ref_id'], $condition_id, 'edit');
        $data = $form->getData();
        if (!$data) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('err_check_input'));
            $this->editConditionTrigger(true);
            return;
        }

        // Update condition
        $condition_handler = new ilConditionHandler();
        $condition_handler->setOperator($data['condition_configuration']['operator'][0]);
        $condition_handler->setObligatory((bool) $data['condition_configuration']['obligatory']);
        $condition_handler->setTargetRefId($this->getTargetRefId());
        $condition_handler->setValue($this->extractValueOptionsFromInput($data));
        $condition_handler->updateCondition($condition_id);
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'listConditions');

        /**
        switch ($this->getTargetType()) {
            case 'st':
                $condition_handler->setReferenceHandlingType((int) $form->getInput('ref_handling'));
                break;

            default:
                $condition_handler->setReferenceHandlingType(ilConditionHandler::UNIQUE_CONDITIONS);
                break;
        }
        $condition_handler->updateCondition($condition['id']);

        // Update relevant sco's
        if ($condition['trigger_type'] === 'sahs') {
            $olp = ilObjectLP::getInstance($condition['trigger_obj_id']);
            $collection = $olp->getCollectionInstance();
            if ($collection) {
                $collection->delete();
            }
            $item_ids = $this->initItemIdsFromPost();
            if (count($item_ids)) { // #12901
                $collection->activateEntries($item_ids->toArray());
            }
            ilLPStatusWrapper::_refreshStatus($condition['trigger_obj_id']);
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'listConditions');
         *
         */

    }

    protected function saveConditionTrigger(): void
    {
        $source_id = $this->initSourceIdFromQuery();
        if (!$source_id) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('no_condition_selected'), true);
            $this->ctrl->redirect($this, 'listConditions');
            return;
        }

        $form = $this->initConditionTriggerForm(true, $source_id, 0);
        $data = $form->getData();
        if (!$data) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('err_check_input'));
            $this->addConditionTrigger(true);
            return;
        }

        $condition = new ilConditionHandler();
        $condition->setTargetRefId($this->getTargetRefId());
        $condition->setTargetObjId($this->getTargetId());
        $condition->setTargetType($this->getTargetType());
        $trigger = ilObjectFactory::getInstanceByRefId($source_id, false);
        if ($trigger instanceof ilObject) {
            $condition->setTriggerRefId($trigger->getRefId());
            $condition->setTriggerObjId($trigger->getId());
            $condition->setTriggerType($trigger->getType());
        }
        $condition->setOperator($data['condition_configuration']['operator'][0]);
        $condition->setObligatory((bool) ($data['condition_configuration']['obligatory'] ?? true));
        $condition->setHiddenStatus(ilConditionHandler::lookupPersistedHiddenStatusByTarget($this->getTargetRefId()));
        $condition->setValue($this->extractValueOptionsFromInput($data));
        $condition->enableAutomaticValidation($this->getAutomaticValidation());
        if (!$condition->storeCondition()) {
            $this->tpl->setOnScreenMessage('failure', $condition->getErrorMessage(), true);
        } else {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('added_new_condition'), true);
        }
        $this->ctrl->redirect($this, 'listConditions');

        /**
        switch ($this->getTargetType()) {
            case 'st':
                $this->ch_obj->setReferenceHandlingType((int) $form->getInput('ref_handling'));
                break;

            default:
                $this->ch_obj->setReferenceHandlingType(ilConditionHandler::UNIQUE_CONDITIONS);
                break;
        }
        // this has to be changed, if non referenced trigger are implemted
        $this->ch_obj->setOperator($form->getInput('operator'));
        $this->ch_obj->setObligatory((bool) $form->getInput('obligatory'));
        $this->ch_obj->setHiddenStatus(ilConditionHandler::lookupPersistedHiddenStatusByTarget($this->getTargetRefId()));
        $this->ch_obj->setValue('');

        // Save assigned sco's
        if ($this->ch_obj->getTriggerType() === 'sahs') {
            $olp = ilObjectLP::getInstance($this->ch_obj->getTriggerObjId());
            $collection = $olp->getCollectionInstance();
            if ($collection) {
                $collection->delete();
            }

            $items_ids = $this->initItemIdsFromPost();
            if (count($items_ids)) { // #12901
                $collection->activateEntries($items_ids->toArray());
            }
        }
         */


    }


}
