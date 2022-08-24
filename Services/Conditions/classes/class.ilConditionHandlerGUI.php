<?php

declare(strict_types=1);

/******************************************************************************
 *
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
 *     https://www.ilias.de
 *     https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

use ILIAS\Refinery\Factory;
use ILIAS\HTTP\GlobalHttpState;

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

    protected ilConditionHandler $ch_obj;
    protected ?ilObject $target_obj = null;
    protected int $target_id = 0;
    protected string $target_type = '';
    protected string $target_title = '';
    protected int $target_ref_id = 0;

    protected bool $automatic_validation = true;

    public function __construct(int $a_ref_id = null)
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
        if ($this->http->wrapper()->post()->has('list_mode')) {
            return $this->http->wrapper()->post()->retrieve(
                'list_mode',
                $this->refinery->kindlyTo()->string()
            );
        }
        return "";
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

    public static function translateOperator(int $a_obj_id, string $a_operator): string
    {
        global $DIC;

        $lng = $DIC->language();
        switch ($a_operator) {
            case ilConditionHandler::OPERATOR_LP:
                $lng->loadLanguageModule('trac');

                $obj_settings = new ilLPObjSettings($a_obj_id);
                return ilLPObjSettings::_mode2Text($obj_settings->getMode());

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

    protected function listConditions(): void
    {
        // check if parent deals with conditions
        if (
            $this->getTargetRefId() > 0 &&
            $this->conditionUtil->isUnderParentControl($this->getTargetRefId())
        ) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("cond_under_parent_control"));
            return;
        }

        $this->toolbar->addButton($this->lng->txt('add_condition'), $this->ctrl->getLinkTarget($this, 'selector'));
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.list_conditions.html', 'Services/AccessControl');

        $optional_conditions = ilConditionHandler::getPersistedOptionalConditionsOfTarget(
            $this->getTargetRefId(),
            $this->getTargetId(),
            $this->getTargetType()
        );
        $list_mode = $this->initListModeFromPost();
        if (count($optional_conditions)) {
            if ($list_mode === self::LIST_MODE_UNDEFINED) {
                $list_mode = self::LIST_MODE_SUBSET;
            }
        } elseif ($list_mode === self::LIST_MODE_UNDEFINED) {
            $list_mode = self::LIST_MODE_ALL;
        }

        // Show form only if conditions are availabe
        if (count(ilConditionHandler::_getPersistedConditionsOfTarget(
            $this->getTargetRefId(),
            $this->getTargetId(),
            $this->getTargetType()
        ))
        ) {
            $form = $this->showObligatoryForm($list_mode);
            if ($form instanceof ilPropertyFormGUI) {
                $this->tpl->setVariable('TABLE_SETTINGS', $form->getHTML());
            }
        }

        $table = new ilConditionHandlerTableGUI($this, 'listConditions', $list_mode === self::LIST_MODE_ALL);
        $table->setConditions(
            ilConditionHandler::_getPersistedConditionsOfTarget(
                $this->getTargetRefId(),
                $this->getTargetId(),
                $this->getTargetType()
            )
        );

        $h = $table->getHTML();
        $this->tpl->setVariable('TABLE_CONDITIONS', $h);
    }

    protected function saveObligatorySettings(): void
    {
        $form = $this->showObligatoryForm();
        if ($form !== null && $form->checkInput()) {
            $old_mode = $form->getInput("old_list_mode");
            switch ($form->getInput("list_mode")) {
                case "all":
                    if ($old_mode !== "all") {
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

                case "subset":
                    $num_req = $form->getInput('required');
                    if ($old_mode !== "subset") {
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
                        $num_req
                    );
                    break;
            }

            $cond = new ilConditionHandler();
            $cond->setTargetRefId($this->getTargetRefId());
            $cond->updateHiddenStatus((bool) $form->getInput('hidden'));

            $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, 'listConditions');
        }

        $form->setValuesByPost();
        $this->tpl->setOnScreenMessage('failure', $this->lng->txt('err_check_input'));
        $this->tpl->setContent($form->getHTML());
    }

    protected function saveObligatoryList(): void
    {
        $all_conditions = ilConditionHandler::_getPersistedConditionsOfTarget(
            $this->getTargetRefId(),
            $this->getTargetId(),
            $this->getTargetType()
        );

        $obligatory_ids = $this->initObligatoryItemsFromPost();
        if (count($obligatory_ids) > count($all_conditions) - 2) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("rbac_precondition_minimum_optional"), true);
            $this->ctrl->redirect($this, 'listConditions');
        }

        foreach ($all_conditions as $item) {
            $status = false;
            if (in_array($item['condition_id'], $obligatory_ids->toArray(), true)) {
                $status = true;
            }
            ilConditionHandler::updateObligatory($item["condition_id"], $status);
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
            $obl->setValue($num_required > 0 ? $num_required : null);
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

    public function askDelete(): void
    {
        $condition_ids = $this->initConditionsIdsFromPost();
        if (!count($condition_ids)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('no_condition_selected'));
            $this->listConditions();
            return;
        }

        // display confirmation message
        $cgui = new ilConfirmationGUI();
        $cgui->setFormAction($this->ctrl->getFormAction($this, "listConditions"));
        $cgui->setHeaderText($this->lng->txt("rbac_condition_delete_sure"));
        $cgui->setCancel($this->lng->txt("cancel"), "listConditions");
        $cgui->setConfirm($this->lng->txt("delete"), "delete");

        // list conditions that should be deleted
        foreach ($condition_ids as $condition_id) {
            $condition = ilConditionHandler::_getCondition($condition_id);

            $title = ilObject::_lookupTitle($condition['trigger_obj_id']) .
                " (" . $this->lng->txt("condition") . ": " .
                $this->lng->txt('condition_' . $condition['operator']) . ")";
            $icon = ilUtil::getImagePath('icon_' . $condition['trigger_type'] . '.svg');
            $alt = $this->lng->txt('obj_' . $condition['trigger_type']);

            $cgui->addItem("conditions[]", (string) $condition_id, $title, $icon, $alt);
        }

        $this->tpl->setContent($cgui->getHTML());
    }

    public function delete(): void
    {
        $condition_ids = $this->initConditionsIdsFromPost();
        if (!count($condition_ids)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('no_condition_selected'));
            $this->listConditions();
            return;
        }

        foreach ($condition_ids as $condition_id) {
            $this->ch_obj->deleteCondition($condition_id);
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('condition_deleted'), true);
        $this->ctrl->redirect($this, 'listConditions');
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
        if ($a_mode === 'add') {
            $operators[0] = $this->lng->txt('select_one');
        }
        $operators = [];
        foreach ($ch_obj->getOperatorsByTriggerType($trigger_type) as $operator) {
            $operators[$operator] = $this->lng->txt('condition_' . $operator);
        }
        $sel->setValue($condition['operator'] ?? 0);
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

            $cus = new ilCustomInputGUI($this->lng->txt('trac_sahs_relevant_items'), 'item_ids[]');
            $cus->setRequired(true);

            $tpl = new ilTemplate(
                'tpl.condition_handler_sco_row.html',
                true,
                true,
                "Services/AccessControl"
            );

            $olp = ilObjectLP::getInstance($trigger_obj_id);
            $collection = $olp->getCollectionInstance();
            if ($collection) {
                foreach ($collection->getPossibleItems() as $item_id => $sahs_item) {
                    $tpl->setCurrentBlock("sco_row");
                    $tpl->setVariable('SCO_ID', $item_id);
                    $tpl->setVariable('SCO_TITLE', $sahs_item['title']);
                    $tpl->setVariable('CHECKED', $collection->isAssignedEntry($item_id) ? 'checked="checked"' : '');
                    $tpl->parseCurrentBlock();
                }
            }
            $tpl->setVariable('INFO_SEL', $this->lng->txt('trac_lp_determination_info_sco'));
            $cus->setHtml($tpl->get());
            $form->addItem($cus);
        }
        switch ($a_mode) {
            case 'edit':
                $form->setTitleIcon(ilUtil::getImagePath('icon_' . $this->getTargetType() . '.svg'));
                $form->setTitle($this->lng->txt('rbac_edit_condition'));
                $form->addCommandButton('updateCondition', $this->lng->txt('save'));
                $form->addCommandButton('listConditions', $this->lng->txt('cancel'));
                break;

            case 'add':
                $form->setTitleIcon(ilUtil::getImagePath('icon_' . $this->getTargetType() . '.svg'));
                $form->setTitle($this->lng->txt('add_condition'));
                $form->addCommandButton('assign', $this->lng->txt('save'));
                $form->addCommandButton('selector', $this->lng->txt('back'));
                break;
        }
        return $form;
    }
}
