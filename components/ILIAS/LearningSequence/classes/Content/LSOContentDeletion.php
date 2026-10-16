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

declare(strict_types=1);

namespace ILIAS\LearningSequence\Content;

use ILIAS\UI\Component\Modal\Interruptive;

/**
 * Shared deletion behaviour for the mode specific content controllers.
 *
 * Deleting a content item is - from the object point of view - always the same
 * operation ({@see \ilRepUtil::deleteObjects()}). What differs between the
 * adaptive and the sequential mode is only *how* the confirmation is presented
 * to the user (an inline modal on the presentation table vs. an async modal on
 * the ordering table). Therefore the reusable parts live in this trait while the
 * mode specific presentation stays in the controllers / tables.
 *
 * The trait expects the using class to provide the following members:
 * @property \ilObjLearningSequenceContentGUI $parent_gui
 * @property \ILIAS\UI\Factory $ui_factory
 * @property \ilLanguage $lng
 * @property \ilCtrl $ctrl
 * @property \Psr\Http\Message\ServerRequestInterface $request
 * @property \ilGlobalTemplateInterface $tpl
 * @property int $ref_id ref id of the learning sequence container
 */
trait LSOContentDeletion
{
    /**
     * Field name posted by the interruptive modal for every affected item.
     */
    private const string MODAL_ITEMS_FIELD = 'interruptive_items';

    /**
     * Query parameter carrying the selected row ids of the ordering table.
     */
    private const string ORDERING_IDS_PARAM = 'lso_content_seq_item_ref_id';

    /**
     * Deletes the currently selected content item(s) and returns to the
     * content management view.
     */
    public function delete(): void
    {
        $ref_ids = $this->readSelectedRefIds();

        if ($ref_ids === []) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('no_checkbox_selected'), true);
            $this->ctrl->redirect($this->parent_gui, \ilObjLearningSequenceContentGUI::CMD_MANAGE_CONTENT);
        }

        $condition_handler = new \ILIAS\LearningSequence\Content\Condition\ConditionHandler();
        foreach ($ref_ids as $ref_id) {
            $condition_handler->deleteConditionsByRefId($this->ref_id, $ref_id);
        }

        \ilRepUtil::deleteObjects($this->ref_id, $ref_ids);

        /** @var \ilObjLearningSequence $lso */
        $lso = \ilObjLearningSequence::getInstanceByRefId($this->ref_id);
        if ($lso->getLSSettings()->getMode() === \ilLearningSequenceSettings::MODE_ADAPTIVE) {
            global $DIC;
            $boundaries = new \ILIAS\LearningSequence\Content\Adaptive\LSOAdaptiveBoundaries($DIC->database());

            $boundaries_changed = false;
            foreach ($ref_ids as $ref_id) {
                if ($boundaries->removeRefIdFromBoundaries($lso->getId(), $ref_id)) {
                    $boundaries_changed = true;
                }
            }

            if ($boundaries_changed) {
                $this->enforceOfflineForMissingBoundaries($lso);
            }
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_removed'), true);
        $this->ctrl->redirect($this->parent_gui, \ilObjLearningSequenceContentGUI::CMD_MANAGE_CONTENT);
    }

    /**
     * An adaptive learning sequence may only be online if it has a start AND
     * an end object. If either one is missing, the learning sequence is taken
     * offline and the user is informed.
     *
     * @return bool true if the learning sequence was switched offline
     */
    protected function enforceOfflineForMissingBoundaries(\ilObjLearningSequence $lso): bool
    {
        if ($lso->getLSSettings()->getMode() !== \ilLearningSequenceSettings::MODE_ADAPTIVE) {
            return false;
        }

        $online = $lso->getObjectProperties()->getPropertyIsOnline();
        if (!$online->getIsOnline()) {
            return false;
        }

        global $DIC;
        $boundaries = new \ILIAS\LearningSequence\Content\Adaptive\LSOAdaptiveBoundaries($DIC->database());
        $current = $boundaries->getBoundariesFor($lso->getId());
        if ($current['start_ref_id'] > 0 && $current['end_ref_id'] > 0) {
            return false;
        }

        $lso->getObjectProperties()->storePropertyIsOnline($online->withOffline());
        $this->tpl->setOnScreenMessage(
            'info',
            $this->lng->txt('lso_adaptive_offline_due_to_missing_boundaries'),
            true
        );

        return true;
    }

    /**
     * Collects the selected item ref ids from the request. The ids may come
     * from the interruptive modal (POST) or from the ordering table's async
     * action (GET, possibly a list).
     *
     * @return int[]
     */
    protected function readSelectedRefIds(): array
    {
        $ref_ids = [];

        $post = $this->request->getParsedBody() ?? [];
        if (isset($post[self::MODAL_ITEMS_FIELD]) && is_array($post[self::MODAL_ITEMS_FIELD])) {
            $ref_ids = array_map('intval', $post[self::MODAL_ITEMS_FIELD]);
        }

        if ($ref_ids === [] && isset($post['id']) && is_array($post['id'])) {
            $ref_ids = array_map('intval', $post['id']);
        }

        if ($ref_ids === []) {
            $query = $this->request->getQueryParams();
            $value = $query[self::ORDERING_IDS_PARAM] ?? null;
            if (is_array($value)) {
                $ref_ids = array_map('intval', $value);
            } elseif ($value !== null && $value !== '') {
                $ref_ids = array_map(
                    'intval',
                    array_filter(explode(',', (string) $value), static fn(string $id): bool => $id !== '')
                );
            }
        }

        if ($ref_ids === []) {
            $single = $this->parent_gui->extractItemRefId();
            if ($single > 0) {
                $ref_ids = [$single];
            }
        }

        return array_values(array_filter($ref_ids, static fn(int $ref_id): bool => $ref_id > 0));
    }

    /**
     * Builds the modern confirmation modal for deleting the given items.
     *
     * @param int[] $ref_ids
     */
    protected function buildDeleteModal(array $ref_ids): Interruptive
    {
        $items = [];
        foreach ($ref_ids as $ref_id) {
            $title = \ilObject::_lookupTitle(\ilObject::_lookupObjId($ref_id));
            $items[] = $this->ui_factory->modal()->interruptiveItem()->keyValue(
                (string) $ref_id,
                $this->lng->txt('title'),
                $title
            );
        }

        $form_action = $this->ctrl->getFormAction(
            $this->parent_gui,
            \ilObjLearningSequenceContentGUI::CMD_DELETE
        );

        return $this->ui_factory->modal()->interruptive(
            $this->lng->txt('delete'),
            $this->lng->txt('info_delete_sure'),
            $form_action
        )
            ->withAffectedItems($items)
            ->withActionButtonLabel($this->lng->txt('delete'));
    }
}

/**
 * Builds the action menus (dropdowns / table actions) for the learning
 * sequence content tables.
 *
 * This logic used to live inside {@see \ilObjLearningSequenceContentGUI} and was
 * moved here (previously its own file LSOTableActionHandler.php) to keep the
 * content related helpers together while giving the action collection a single,
 * well defined responsibility.
 *
 * @property-read \ilObjLearningSequenceContentGUI $gui
 * @property-read \ilObjLearningSequenceGUI $container_gui
 * @property-read \ilCtrl $ctrl
 * @property-read \ilLanguage $lng
 * @property-read \ilAccessHandler $access
 * @property-read \LSItemOnlineStatus $ls_item_online_status
 */
class LSOTableActionHandler
{
    /**
     * Standard commands of the object list gui that should be offered as
     * actions in the content tables.
     *
     * @var list<string>
     */
    private const array ALLOWED_STANDARD_COMMANDS = [
        'settings',
        'download',
        'link',
        'move',
        'copy',
        'cut',
        'info',
    ];

    /**
     * Order in which the "standard" object actions are appended after the
     * learning sequence specific actions.
     *
     * @var list<string>
     */
    private const array REMAINING_ACTION_ORDER = ['link', 'cut', 'copy'];

    /**
     * Creates an action handler for learning sequence content tables.
     */
    public function __construct(
        private readonly \ilObjLearningSequenceContentGUI $gui,
        private readonly \ilObjLearningSequenceGUI $container_gui,
        private readonly \ilCtrl $ctrl,
        private readonly \ilLanguage $lng,
        private readonly \ilAccessHandler $access,
        private readonly \LSItemOnlineStatus $ls_item_online_status
    ) {
    }

    /**
     * Collects all actions that are offered for a single content item (or, when
     * $ref_id is 0, the generic action template used to build the bulk table
     * actions).
     *
     * @param array<string, \ilObjLearningSequenceActionData> $specific_actions
     * @param bool|null $is_online Current online state of the item. When given
     *                            only the opposite toggle action is offered.
     * @return array<string, \ilObjLearningSequenceActionData>
     * @throws \ilCtrlException
     */
    public function collectActions(int $ref_id, array $specific_actions = [], ?bool $is_online = null): array
    {
        $standard_actions = $this->collectStandardActions($ref_id);

        $actions = [];
        $this->appendSettingsAction($actions, $ref_id, $standard_actions);
        $this->appendOnlineOfflineActions($actions, $ref_id, $is_online);
        $this->appendSpecificActions($actions, $specific_actions);
        $this->appendRemainingActions($actions, $ref_id, $standard_actions);

        $this->ctrl->setParameter($this->gui, 'item_ref_id', '');

        return $actions;
    }

    /**
     * Builds the actions derived from the generic object list gui (settings,
     * delete, cut, copy, link, ...).
     *
     * @return array<string, \ilObjLearningSequenceActionData>
     */
    private function collectStandardActions(int $ref_id): array
    {
        if ($ref_id <= 0) {
            return [];
        }

        $obj_id = \ilObject::_lookupObjId($ref_id);
        $type = \ilObject::_lookupType($obj_id);

        $list_gui = \ilObjectListGUIFactory::_getListGUIByType($type);
        $list_gui->initItem($ref_id, $obj_id, $type);
        $list_gui->setContainerObject($this->container_gui);

        $list_gui->enableCut(true);
        $list_gui->enableDelete(true);
        $list_gui->enableLink(true);
        $list_gui->enableCopy(true);

        if (method_exists($list_gui, 'enableDownload')) {
            $list_gui->enableDownload(true);
        }

        $standard_actions = [];
        foreach ($list_gui->getCommands() as $cmd) {
            $lang_var = $cmd['lang_var'];
            $cmd_name = $cmd['cmd'];
            $key = ($lang_var !== '') ? $lang_var : $cmd_name;

            if (!in_array($key, self::ALLOWED_STANDARD_COMMANDS, true)) {
                continue;
            }

            $label = ($lang_var !== '') ? $this->lng->txt($lang_var) : $cmd_name;

            $standard_actions[$key] = new \ilObjLearningSequenceActionData(
                label: $label,
                link: $cmd['link']
            );
        }

        $this->addManualStandardActions($standard_actions, $ref_id, $type);

        return $standard_actions;
    }

    /**
     * Some commands (delete, cut, copy, link) are not always provided by the
     * list gui. In that case they are built manually if the current user is
     * allowed to perform them.
     *
     * @param array<string, \ilObjLearningSequenceActionData> $standard_actions
     */
    private function addManualStandardActions(array &$standard_actions, int $ref_id, string $type): void
    {
        $manual_cmds = [
            'cut' => ['permission' => 'delete', 'lang_var' => 'move', 'cmd' => 'cut'],
            'copy' => ['permission' => 'copy', 'lang_var' => 'copy', 'cmd' => 'copy'],
            'link' => ['permission' => 'delete', 'lang_var' => 'link', 'cmd' => 'link'],
        ];

        foreach ($manual_cmds as $cmd_name => $info) {
            if (isset($standard_actions[$cmd_name])) {
                continue;
            }

            if (!$this->access->checkAccess($info['permission'], '', $ref_id, $type)) {
                continue;
            }

            $this->ctrl->setParameter($this->gui, 'item_ref_id', $ref_id);
            $standard_actions[$cmd_name] = new \ilObjLearningSequenceActionData(
                label: $this->lng->txt($info['lang_var']),
                link: $this->ctrl->getLinkTarget($this->gui, $info['cmd'])
            );
        }
    }

    /**
     * @param array<string, \ilObjLearningSequenceActionData> $actions
     * @param array<string, \ilObjLearningSequenceActionData> $standard_actions
     */
    private function appendSettingsAction(array &$actions, int $ref_id, array $standard_actions): void
    {
        if ($ref_id !== 0 && !isset($standard_actions['settings'])) {
            return;
        }

        $actions['settings'] = $standard_actions['settings'] ?? new \ilObjLearningSequenceActionData(
            label: $this->lng->txt('settings'),
            link: ''
        );
    }

    /**
     * @param array<string, \ilObjLearningSequenceActionData> $actions
     */
    private function appendOnlineOfflineActions(array &$actions, int $ref_id, ?bool $is_online = null): void
    {
        if ($ref_id > 0 && !$this->ls_item_online_status->hasChangeableOnlineStatus($ref_id)) {
            return;
        }

        $set_online = new \ilObjLearningSequenceActionData(
            label: $this->lng->txt('set_online'),
            link: \ilObjLearningSequenceContentGUI::CMD_SET_ONLINE
        );
        $set_offline = new \ilObjLearningSequenceActionData(
            label: $this->lng->txt('set_offline'),
            link: \ilObjLearningSequenceContentGUI::CMD_SET_OFFLINE
        );

        if ($ref_id > 0 && $is_online !== null) {
            if ($is_online) {
                $actions['set_offline'] = $set_offline;
            } else {
                $actions['set_online'] = $set_online;
            }
            return;
        }

        $actions['set_online'] = $set_online;
        $actions['set_offline'] = $set_offline;
    }

    /**
     * @param array<string, \ilObjLearningSequenceActionData> $actions
     * @param array<string, \ilObjLearningSequenceActionData> $specific_actions
     */
    private function appendSpecificActions(array &$actions, array $specific_actions): void
    {
        if ($specific_actions === []) {
            return;
        }

        $actions['divider_mode_2'] = \ilObjLearningSequenceActionData::divider();
        foreach ($specific_actions as $id => $action) {
            $actions[$id] = $action;
        }
    }

    /**
     * @param array<string, \ilObjLearningSequenceActionData> $actions
     * @param array<string, \ilObjLearningSequenceActionData> $standard_actions
     */
    private function appendRemainingActions(array &$actions, int $ref_id, array $standard_actions): void
    {
        $first_remaining = true;
        foreach (self::REMAINING_ACTION_ORDER as $key) {
            if ($ref_id !== 0 && !isset($standard_actions[$key])) {
                continue;
            }

            if ($first_remaining) {
                $actions['divider_standard'] = \ilObjLearningSequenceActionData::divider();
                $first_remaining = false;
            }

            if ($ref_id > 0) {
                $this->ctrl->setParameter($this->gui, 'item_ref_id', $ref_id);
            }

            $actions[$key] = $standard_actions[$key] ?? new \ilObjLearningSequenceActionData(
                label: $this->lng->txt($key),
                link: ''
            );
        }
    }
}
