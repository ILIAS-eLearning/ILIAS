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

use ILIAS\HTTP\Wrapper\RequestWrapper;
use ILIAS\LearningSequence\Content\Adaptive\LSOAdaptiveContent;
use ILIAS\LearningSequence\Content\LSOContentController;
use ILIAS\LearningSequence\Content\Sequential\LSOSequentialContent;
use ILIAS\Refinery\Factory;
use ILIAS\UI\Renderer;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Manages the content of a learning sequence.
 *
 * @property \ilObjLearningSequenceGUI|self $parent_gui
 * @property \ilCtrl $ctrl
 * @property \ilGlobalTemplateInterface $tpl
 * @property \ilLanguage $lng
 * @property \ilAccessHandler $access
 * @property \ilConfirmationGUI $confirmation_gui
 * @property \LSItemOnlineStatus $ls_item_online_status
 * @property RequestWrapper $query_wrapper
 * @property RequestWrapper $post_wrapper
 * @property Factory $refinery
 * @property \ILIAS\UI\Factory $ui_factory
 * @property Renderer $ui_renderer
 * @property ServerRequestInterface $request
 *
 * @ilCtrl_Calls ilObjLearningSequenceContentGUI: ilObjLearningSequenceConditionsGUI
 */
class ilObjLearningSequenceContentGUI implements ilCtrlSecurityInterface
{
    public const string CMD_MANAGE_CONTENT = "manageContent";
    public const string CMD_SAVE = "save";
    public const string CMD_DELETE = "delete";
    public const string CMD_CONFIRM_DELETE = "confirmDelete";
    public const string CMD_CANCEL = "cancel";
    public const string CMD_SET_ONLINE = "setOnline";
    public const string CMD_SET_OFFLINE = "setOffline";
    public const string CMD_SET_START_OBJECT = "setStartObject";
    public const string CMD_UNSET_START_OBJECT = "unsetStartObject";
    public const string CMD_SET_END_OBJECT = "setEndObject";
    public const string CMD_UNSET_END_OBJECT = "unsetEndObject";
    public const string CMD_SET_CONDITION_ALWAYS = "setConditionAlways";
    public const string CMD_SET_CONDITION_LP = "setConditionLP";
    public const string CMD_REORDER = "reorder";
    public const string CMD_CUT = "cut";
    public const string CMD_COPY = "copy";
    public const string CMD_LINK = "link";
    public const string CMD_SETTINGS = "settings";
    public const string CMD_VIEW = "view";

    /**
     * Commands handled by this gui itself (a few internal object actions).
     * Everything else is delegated to the mode specific content controller.
     */
    private const array INTERNAL_COMMANDS = [
        self::CMD_SET_ONLINE,
        self::CMD_SET_OFFLINE,
        self::CMD_SET_CONDITION_ALWAYS,
        self::CMD_SET_CONDITION_LP,
        self::CMD_CUT,
        self::CMD_COPY,
        self::CMD_LINK,
        self::CMD_SETTINGS,
    ];

    public const string FIELD_ORDER = 'f_order';
    public const string FIELD_ONLINE = 'f_online';
    public const string FIELD_POSTCONDITION_TYPE = 'f_pct';

    /**
     * Creates the content management GUI.
     */
    public function __construct(
        protected \ilObjLearningSequenceGUI|ilObjLearningSequenceContentGUI $parent_gui,
        protected \ilCtrl $ctrl,
        protected \ilGlobalTemplateInterface $tpl,
        protected \ilLanguage $lng,
        protected \ilAccessHandler $access,
        protected \ilConfirmationGUI $confirmation_gui,
        protected \LSItemOnlineStatus $ls_item_online_status,
        protected RequestWrapper $query_wrapper,
        protected RequestWrapper $post_wrapper,
        protected Factory $refinery,
        protected \ILIAS\UI\Factory $ui_factory,
        protected Renderer $ui_renderer,
        protected ServerRequestInterface $request
    ) {
    }

    /**
     * Sets the page content.
     */
    public function setContent(string $html): void
    {
        $this->tpl->setContent($html);
    }

    /**
     * Renders the "add new object" drilldown (AddNewItemGUI) into the toolbar
     * of the content management view. Delegates to the parent object gui which
     * knows the creatable sub object types.
     */
    public function showPossibleSubObjects(): void
    {
        $this->parent_gui->showPossibleSubObjects();
    }

    /**
     * Executes the requested content management command.
     */
    public function executeCommand(): void
    {
        $this->assertWriteAccess();

        if ($this->forwardToConditionsGUI()) {
            return;
        }

        $this->dispatchCommand($this->resolveCommand());
    }

    /**
     * Guards the whole gui: without write permission the user is sent back to
     * the object view.
     */
    private function assertWriteAccess(): void
    {
        if (!$this->access->checkAccess("write", '', $this->parent_gui->getRefId())) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('msg_no_perm_write'), true);
            $this->ctrl->redirect($this->parent_gui, self::CMD_VIEW);
        }
    }

    /**
     * Forwards to the conditions gui when it is the next class in the control
     * flow. Returns true when the command was handled here.
     * @throws ilCtrlException
     */
    private function forwardToConditionsGUI(): bool
    {
        if ($this->ctrl->getNextClass($this) !== strtolower(ilObjLearningSequenceConditionsGUI::class)) {
            return false;
        }

        $lso_di = $this->parent_gui->getObject()->getDI();
        $gui = new ilObjLearningSequenceConditionsGUI(
            $this,
            $this->ctrl,
            $this->tpl,
            $this->lng,
            $this->access,
            $this->query_wrapper,
            $this->post_wrapper,
            $this->ui_factory,
            $this->ui_renderer,
            $this->request,
            $this->refinery,
            $lso_di["conditions.types"],
            $lso_di["conditions.factory"],
            $lso_di["conditions.handler"],
        );
        $this->ctrl->forwardCommand($gui);

        return true;
    }

    /**
     * All commands modifying the learning sequence or its items. They are
     * reached by links (dropdown entries, table actions) and therefore need
     * to be protected by an ilCtrl CSRF token.
     *
     * @inheritDoc
     */
    public function getUnsafeGetCommands(): array
    {
        return [
            self::CMD_DELETE,
            self::CMD_REORDER,
            self::CMD_SET_ONLINE,
            self::CMD_SET_OFFLINE,
            self::CMD_SET_START_OBJECT,
            self::CMD_UNSET_START_OBJECT,
            self::CMD_SET_END_OBJECT,
            self::CMD_UNSET_END_OBJECT,
            self::CMD_SET_CONDITION_ALWAYS,
            self::CMD_SET_CONDITION_LP,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getSafePostCommands(): array
    {
        return [];
    }

    /**
     * Determines the command to run, resolving the aliases used by the
     * ordering table and the generic "cancel"/"view" commands.
     *
     * The ordering table's actions carry their command in the query parameter
     * "lso_content_seq_cmd" and target the table's form action (cmd=post with
     * fallback command "reorder"). ilCtrl only resolves "reorder" for such a
     * request if the CSRF token is valid, so the table command is only accepted
     * in that case. Otherwise the token check could be bypassed.
     */
    private function resolveCommand(): string
    {
        $query_params = $this->request->getQueryParams();
        $ctrl_cmd = $this->ctrl->getCmd();
        $table_cmd = $query_params['lso_content_seq_cmd'] ?? null;

        $cmd = $ctrl_cmd;
        if (is_string($table_cmd) && $ctrl_cmd === self::CMD_REORDER) {
            $cmd = $table_cmd;
        }

        if ($cmd === self::CMD_CANCEL) {
            $this->ctrl->redirect($this, self::CMD_MANAGE_CONTENT);
        }

        if ($cmd === self::CMD_VIEW || $cmd === '' || $cmd === null) {
            return self::CMD_MANAGE_CONTENT;
        }

        return $cmd;
    }

    /**
     * Main controller logic: a command is either delegated to the mode
     * specific content controller or executed by one of the few internal
     * methods of this gui.
     */
    private function dispatchCommand(string $cmd): void
    {
        $controller = $this->createContentController();

        if (in_array($cmd, $controller->getSupportedCommands(), true)) {
            $controller->$cmd();
            return;
        }

        if (in_array($cmd, self::INTERNAL_COMMANDS, true)) {
            $this->$cmd();
            return;
        }

        throw new \ilException("ilObjLearningSequenceContentGUI: command not supported: $cmd");
    }

    /**
     * Builds the content controller matching the current LSO mode.
     */
    private function createContentController(): LSOContentController
    {
        $ref_id = $this->parent_gui->getRefId();
        $obj_id = $this->parent_gui->getObject()->getId();
        $mode = $this->parent_gui->getObject()->getLSSettings()->getMode();

        $class = ($mode === ilLearningSequenceSettings::MODE_ADAPTIVE)
            ? LSOAdaptiveContent::class
            : LSOSequentialContent::class;

        return new $class(
            $this,
            $this->ui_factory,
            $this->ui_renderer,
            $this->lng,
            $this->ctrl,
            $this->request,
            $this->tpl,
            $ref_id,
            $obj_id
        );
    }
    /**
     * Creates a handler for table actions.
     */
    public function getTableActionHandler(): \ILIAS\LearningSequence\Content\LSOTableActionHandler
    {
        return new \ILIAS\LearningSequence\Content\LSOTableActionHandler(
            $this,
            $this->parent_gui,
            $this->ctrl,
            $this->lng,
            $this->access,
            $this->ls_item_online_status
        );
    }

    /**
     * Sets the selected item's postcondition to always allow proceeding.
     */
    public function setConditionAlways(): void
    {
        $this->setPostConditionOperator(\ilLSPostCondition::OPERATOR_ALWAYS);
    }

    /**
     * Sets the selected item's postcondition to require learning progress.
     */
    public function setConditionLP(): void
    {
        $this->setPostConditionOperator(\ilLSPostCondition::OPERATOR_LP);
    }

    /**
     * Sets the postcondition operator of the selected item.
     */
    private function setPostConditionOperator(string $operator): void
    {
        $ref_id = $this->extractItemRefId();
        if ($ref_id > 0) {
            $lso = \ilObjLearningSequence::getInstanceByRefId($this->parent_gui->getRefId());
            $items = $lso->getLSItems();
            $updated = [];
            foreach ($items as $item) {
                if ($item->getRefId() === $ref_id) {
                    $item = $item->withPostCondition(
                        $item->getPostCondition()->withConditionOperator($operator)
                    );
                }
                $updated[] = $item;
            }
            $lso->storeLSItems($updated);
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_obj_modified'), true);
        }
        $this->ctrl->redirect($this, self::CMD_MANAGE_CONTENT);
    }

    /**
     * Forwards the cut command to the parent GUI.
     */
    public function cut(): void
    {
        $this->forwardClipboardCommand(\ilObjLearningSequenceGUI::CMD_CUT);
    }

    /**
     * Forwards the copy command to the parent GUI.
     */
    public function copy(): void
    {
        $this->forwardClipboardCommand('copy');
    }

    /**
     * Forwards the link command to the parent GUI.
     */
    public function link(): void
    {
        $this->forwardClipboardCommand(\ilObjLearningSequenceGUI::CMD_LINK);
    }

    /**
     * Forwards a clipboard command for the selected item to the parent GUI.
     */
    private function forwardClipboardCommand(string $cmd): void
    {
        $ref_id = $this->extractItemRefId();
        if ($ref_id <= 0) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('no_checkbox_selected'), true);
            $this->ctrl->redirect($this, self::CMD_MANAGE_CONTENT);
        }

        $this->ctrl->setParameterByClass(\ilObjLearningSequenceGUI::class, 'item_ref_id', $ref_id);
        $this->ctrl->redirectByClass(\ilObjLearningSequenceGUI::class, $cmd);
    }

    /**
     * Opens the settings page of the selected item.
     */
    public function settings(): void
    {
        $ref_id = $this->extractItemRefId();
        if ($ref_id <= 0) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('no_checkbox_selected'), true);
            $this->ctrl->redirect($this, self::CMD_MANAGE_CONTENT);
        }

        $link = $this->getObjectSettingsLink($ref_id);
        if ($link === '') {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('msg_no_perm_read'), true);
            $this->ctrl->redirect($this, self::CMD_MANAGE_CONTENT);
        }

        $this->ctrl->redirectToURL($link);
    }

    /**
     * Determines the "settings" link of the given item by asking its object
     * list gui, so the action points to the object's own settings and not to
     * the content gui.
     */
    private function getObjectSettingsLink(int $ref_id): string
    {
        $obj_id = \ilObject::_lookupObjId($ref_id);
        $type = \ilObject::_lookupType($obj_id);

        $list_gui = \ilObjectListGUIFactory::_getListGUIByType($type);
        $list_gui->initItem($ref_id, $obj_id, $type);
        $list_gui->setContainerObject($this->parent_gui);

        foreach ($list_gui->getCommands() as $cmd) {
            $key = ($cmd['lang_var'] !== '') ? $cmd['lang_var'] : $cmd['cmd'];
            if ($key === 'settings') {
                return (string) $cmd['link'];
            }
        }

        return '';
    }

    /**
     * Sets the selected item online.
     */
    public function setOnline(): void
    {
        $ref_id = $this->extractItemRefId();
        if ($ref_id > 0) {
            $lso = \ilObjLearningSequence::getInstanceByRefId($this->parent_gui->getRefId());
            $items = $lso->getLSItems();
            $updated = [];
            foreach ($items as $item) {
                if ($item->getRefId() === $ref_id) {
                    $item = $item->withOnline(true);
                }
                $updated[] = $item;
            }
            $lso->storeLSItems($updated);
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_obj_modified'), true);
        }
        $this->ctrl->redirect($this, self::CMD_MANAGE_CONTENT);
    }

    /**
     * Sets the selected item offline.
     */
    public function setOffline(): void
    {
        $ref_id = $this->extractItemRefId();
        if ($ref_id > 0) {
            $lso = \ilObjLearningSequence::getInstanceByRefId($this->parent_gui->getRefId());
            $items = $lso->getLSItems();
            $updated = [];
            foreach ($items as $item) {
                if ($item->getRefId() === $ref_id) {
                    $item = $item->withOnline(false);
                }
                $updated[] = $item;
            }
            $lso->storeLSItems($updated);
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_obj_modified'), true);
        }
        $this->ctrl->redirect($this, self::CMD_MANAGE_CONTENT);
    }

    /**
     * Extracts the selected item reference ID from the request.
     */
    public function extractItemRefId(): int
    {
        $query_params = $this->request->getQueryParams();
        foreach (['lso_content_seq_item_ref_id', 'item_ref_id'] as $key) {
            $value = $query_params[$key] ?? 0;
            $ref_id = (int) (is_array($value) ? ($value[0] ?? 0) : $value);
            if ($ref_id !== 0) {
                return $ref_id;
            }
        }
        return 0;
    }
}
