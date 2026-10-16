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

use ILIAS\Data\Factory;
use ILIAS\HTTP\Wrapper\RequestWrapper;
use ILIAS\LearningSequence\Content\Condition\AbstractCondition;
use ILIAS\LearningSequence\Content\Condition\ConditionFactory;
use ILIAS\LearningSequence\Content\Condition\ilObjLearningSequenceConditionDiscover;
use ILIAS\UI\Component\Menu\Drilldown;
use ILIAS\UI\Component\Modal\Roundtrip;
use ILIAS\UI\Component\Panel\Panel;
use ILIAS\UI\URLBuilder;
use JetBrains\PhpStorm\NoReturn;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @ilCtrl_isCalledBy ilObjLearningSequenceConditionsGUI: ilObjLearningSequenceContentGUI
 */
class ilObjLearningSequenceConditionsGUI implements ilCtrlSecurityInterface
{
    public const string CMD_MANAGE_CONDITIONS = "manageConditions";
    public const string CMD_CONFIRM_DELETE_CONDITION = "confirmDeleteCondition";
    public const string CMD_CREATE_CONDITION = "createCondition";
    public const string CMD_DELETE_CONDITION = "deleteCondition";

    protected int $lso_ref_id;
    /** @var int LSO content object */
    protected int $item_ref_id;
    private ilObjLearningSequenceConditionDiscover $discoverer;
    private ConditionFactory $condition_factory;

    public function __construct(
        protected ilObjLearningSequenceGUI|ilObjLearningSequenceContentGUI $parent_gui,
        protected ilCtrl $ctrl,
        protected ilGlobalTemplateInterface $tpl,
        protected ilLanguage $lng,
        protected ilAccess $access,
        protected RequestWrapper $query_wrapper,
        protected RequestWrapper $post_wrapper,
        protected ILIAS\UI\Factory $ui_factory,
        protected ILIAS\UI\Renderer $ui_renderer,
        protected ServerRequestInterface $request,
        protected \ILIAS\Refinery\Factory $refinery,
    ) {
        global $DIC;
        $this->lso_ref_id = $this->query_wrapper->retrieve('ref_id', $DIC->refinery()->kindlyTo()->int());
        $this->item_ref_id = $this->query_wrapper->retrieve('item_ref_id', $DIC->refinery()->kindlyTo()->int());
        $this->discoverer = new ilObjLearningSequenceConditionDiscover();
        $this->condition_factory = new ConditionFactory($this->discoverer, $DIC->database());
    }

    /**
     * @throws ilException
     * @throws ilCtrlException
     */
    public function executeCommand(): void
    {
        $this->assertWriteAccess();

        // ilCtrl returns no command if the CSRF validation fails, fall back
        // to the conditions overview in this case.
        $cmd = $this->ctrl->getCmd(self::CMD_MANAGE_CONDITIONS);
        $next_class = $this->ctrl->getNextClass();

        switch ($next_class) {
            case strtolower(ilObjLearningSequenceConditionConfigurationGUI::class):
                $this->ctrl->setReturn($this, self::CMD_MANAGE_CONDITIONS);
                $this->ctrl->forwardCommand(new $next_class(
                    $this->ctrl,
                    $this->tpl,
                    $this->lng,
                    $this->access,
                    $this->post_wrapper,
                    $this->ui_factory,
                    $this->ui_renderer,
                    $this->request,
                ));
                break;
            default:
                switch ($cmd) {
                    case self::CMD_MANAGE_CONDITIONS:
                    case self::CMD_CONFIRM_DELETE_CONDITION:
                    case self::CMD_CREATE_CONDITION:
                    case self::CMD_DELETE_CONDITION:
                        $this->$cmd();
                        break;
                    default:
                        throw new ilException("ilObjLearningSequenceConditionsGUI: Command not supported: $cmd");
                }
                break;
        }
    }

    /**
     * Creating and deleting conditions is triggered by links (drilldown, interruptive
     * modal action) and therefore needs to be protected by an ilCtrl CSRF token.
     */
    public function getUnsafeGetCommands(): array
    {
        return [
            self::CMD_CREATE_CONDITION,
            self::CMD_DELETE_CONDITION,
        ];
    }

    public function getSafePostCommands(): array
    {
        return [];
    }

    private function assertWriteAccess(): void
    {
        if (!$this->access->checkAccess('write', '', $this->lso_ref_id)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('msg_no_perm_write'), true);
            $this->ctrl->setParameterByClass(ilObjLearningSequenceGUI::class, 'ref_id', $this->lso_ref_id);
            $this->ctrl->redirectByClass(
                [ilRepositoryGUI::class, ilObjLearningSequenceGUI::class],
                ilObjLearningSequenceGUI::CMD_VIEW
            );
        }
    }

    /**
     * @return void
     * @throws ilCtrlException|ilException
     */
    protected function manageConditions(): void
    {
        global $DIC;
        /** @var \ILIAS\DI\Container $DIC */
        $DIC->tabs()->setBack2Target($this->lng->txt('back'), $this->ctrl->getLinkTarget($this->parent_gui));

        $modal = $this->buildAddConditionModal();
        $button = $this->ui_factory->button()->standard(
            $this->lng->txt('add_condition'),
            '#'
        )->withOnClick($modal->getShowSignal());
        $DIC->toolbar()->addComponent($button);

        $this->tpl->setContent(
            $this->ui_renderer->render([
                $this->buildConditionsTable(),
                $modal,
            ])
        );
    }

    /**
     * @return Drilldown
     */
    protected function buildDrilldown(): Drilldown
    {
        $input_conditions_steps = array_map(
            function (string $class): array {
                $condition = new $class();
                $condition->setObjRefId($this->item_ref_id);
                $condition->setLsoRefId($this->lso_ref_id);
                return $condition->setupSteps();
            },
            $this->discoverer->getAllInputConditions()
        );

        $output_conditions_steps = array_map(
            function (string $class): array {
                $condition = new $class();
                $condition->setObjRefId($this->item_ref_id);
                $condition->setLsoRefId($this->lso_ref_id);
                return $condition->setupSteps();
            },
            $this->discoverer->getAllOutputConditions()
        );

        return $this->ui_factory->menu()->drilldown(
            $this->lng->txt('conditions'),
            [
                $this->ui_factory->menu()->sub(
                    $this->lng->txt('input_conditions'),
                    array_merge(...$input_conditions_steps)
                ),
                $this->ui_factory->menu()->sub(
                    $this->lng->txt('output_conditions'),
                    array_merge(...$output_conditions_steps)
                )
            ]
        );
    }

    /**
     * @return Roundtrip
     */
    protected function buildAddConditionModal(): Roundtrip
    {
        return $this->ui_factory->modal()->roundtrip(
            $this->lng->txt('add_condition'),
            [
                'drilldown' => $this->buildDrilldown()
            ]
        );
    }

    /**
     * @throws ilCtrlException
     * @throws ilException
     */
    private function buildConditionsTable(): Panel
    {
        $df = new Factory();
        $af = new \ILIAS\UI\Implementation\Component\Table\Action\Factory();

        // single action - edit
        $this->ctrl->setParameterByClass(
            ilObjLearningSequenceConditionConfigurationGUI::class,
            'ref_id',
            $this->lso_ref_id
        );
        $this->ctrl->setParameterByClass(
            ilObjLearningSequenceConditionConfigurationGUI::class,
            'item_ref_id',
            $this->item_ref_id
        );
        $url = $this->ctrl->getLinkTargetByClass(
            ilObjLearningSequenceConditionConfigurationGUI::class,
            ilObjLearningSequenceConditionConfigurationGUI::CMD_CONFIGURE_COMMAND
        );
        $url_builder = new URLBuilder($df->uri(ILIAS_HTTP_PATH . '/' . $url));
        [$url_builder, $action_parameter_token, $row_id_token] = $url_builder->acquireParameters(
            ['condition'],
            'cmd',
            'id'
        );
        $actions['edit'] = $af->single(
            $this->lng->txt('edit'),
            $url_builder,
            $row_id_token
        );

        // standard action: delete condition
        $this->ctrl->setParameter($this, 'item_ref_id', $this->item_ref_id);
        $url = $this->ctrl->getLinkTarget($this, self::CMD_CONFIRM_DELETE_CONDITION);
        $url_builder = new URLBuilder($df->uri(ILIAS_HTTP_PATH . '/' . $url));
        [$url_builder, $action_parameter_token, $row_id_token] = $url_builder->acquireParameters(
            ['delete'],
            'delete',
            'ids'
        );
        $actions['delete'] = $this->ui_factory->table()->action()->standard(
            $this->lng->txt('delete'),
            $url_builder,
            $row_id_token,
        )->withAsync();

        $table = $this->ui_factory->table()->data(
            new ilLearningSequenceConditionsRetrieval($this->lso_ref_id, $this->item_ref_id),
            '',
            [
                'type' => $this->ui_factory->table()->column()->text(
                    $this->lng->txt('condition_type')
                )->withIsSortable(false),
                'name' => $this->ui_factory->table()->column()->text(
                    $this->lng->txt('condition_name')
                )->withIsSortable(false),
                'subtype' => $this->ui_factory->table()->column()->text(
                    $this->lng->txt('condition_subtype')
                )->withIsSortable(false),
                'details' => $this->ui_factory->table()->column()->text(
                    $this->lng->txt('details')
                )->withIsSortable(false),
            ]
        )
            ->withActions($actions)
            ->withRequest($this->request);

        return $this->ui_factory->panel()->standard(
            sprintf(
                $this->lng->txt('manage_conditions'),
                ilObject::_lookupTitle(ilObject::_lookupObjectId($this->item_ref_id))
            ),
            $table
        );
    }

    /**
     * Creates a new condition.
     *
     * @return void
     * @throws ilCtrlException|ilException|ReflectionException
     */
    private function createCondition(): void
    {
        $type_id = (int) $this->request->getQueryParams()['type_id'] ?? '';
        $subtype = $this->request->getQueryParams()['subtype'] ?? null;

        $condition = $this->condition_factory->getNewConditionInstance(
            $this->lso_ref_id,
            $this->item_ref_id,
            $type_id,
            $subtype,
        );
        $condition->clearValidationMessages();
        $condition->create();

        if ($condition->hasValidationMessages()) {
            $this->showValidationMessages($condition);
            $this->ctrl->setParameterByClass(\ilObjLearningSequenceConditionsGUI::class, 'ref_id', $this->lso_ref_id);
            $this->ctrl->setParameterByClass(\ilObjLearningSequenceConditionsGUI::class, 'item_ref_id', $this->item_ref_id);
            $this->ctrl->redirectByClass(
                [
                    ilRepositoryGUI::class,
                    ilObjLearningSequenceGUI::class,
                    ilObjLearningSequenceContentGUI::class,
                    \ilObjLearningSequenceConditionsGUI::class
                ],
                ilObjLearningSequenceConditionsGUI::CMD_MANAGE_CONDITIONS
            );
            return;
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'), true);
        $this->ctrl->setParameter($this, 'item_ref_id', $this->item_ref_id);
        $this->ctrl->redirectByClass(
            [
                ilRepositoryGUI::class,
                ilObjLearningSequenceGUI::class,
                ilObjLearningSequenceContentGUI::class,
                \ilObjLearningSequenceConditionsGUI::class
            ],
            ilObjLearningSequenceConditionsGUI::CMD_MANAGE_CONDITIONS
        );
    }

    private function showValidationMessages(AbstractCondition $condition): void
    {
        foreach ($condition->getValidationMessages() as $message) {
            $this->tpl->setOnScreenMessage('failure', $message, true);
        }
    }

    /**
     * @throws ilCtrlException
     * @throws ilException
     * @throws ReflectionException
     */
    #[NoReturn]
    private function confirmDeleteCondition(): void
    {
        $this->ctrl->setParameter($this, 'item_ref_id', $this->item_ref_id);

        if (!$this->query_wrapper->has('delete_ids')) {
            $modal = $this->ui_factory->modal()->interruptive(
                $this->lng->txt('delete_condition'),
                $this->lng->txt('delete_condition_no_selection'),
                '#'
            )->withActionButtonLabel($this->lng->txt('ok'));
            exit($this->ui_renderer->render($modal));
        }

        $string_list = $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->string());
        $condition_ids = $this->query_wrapper->retrieve('delete_ids', $string_list);

        if (in_array('ALL_OBJECTS', $condition_ids, true)) {
            $conditions_to_delete = $this->discoverer->getAllConditionIdsForItem($this->item_ref_id);
        } else {
            $conditions_to_delete = array_map('intval', $condition_ids);
        }

        $affected_items = [];
        foreach ($conditions_to_delete as $condition_id) {
            try {
                $affected_items[] = $this->ui_factory->modal()->interruptiveItem()->standard(
                    'condition_' . $condition_id,
                    $this->lng->txt($this->condition_factory->getConditionInstanceById($condition_id)->getName()),
                );
            } catch (Exception $e) {
                // condition not found
            }
        }

        $modal = $this->ui_factory->modal()->interruptive(
            $this->lng->txt('delete_condition'),
            $this->lng->txt('delete_condition_verify'),
            $this->ctrl->getLinkTargetByClass(self::class, self::CMD_DELETE_CONDITION)
        )->withAffectedItems($affected_items);

        exit($this->ui_renderer->render($modal));
    }

    /**
     * Deletes a condition
     *
     * @return void
     * @throws ilException
     * @throws ReflectionException
     */
    private function deleteCondition(): void
    {
        if (!$this->post_wrapper->has('interruptive_items')) {
            throw new ilException('No condition id provided');
        }

        $list = $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->string());
        $condition_to_delete = $this->post_wrapper->retrieve('interruptive_items', $list);

        foreach ($condition_to_delete as $condition) {
            $condition_id = (int) str_replace('condition_', '', $condition);
            $condition = $this->condition_factory->getConditionInstanceById($condition_id);
            $condition->delete();
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('conditions_deleted'), true);
        $this->ctrl->setParameter($this, 'item_ref_id', $this->item_ref_id);
        $this->ctrl->redirect($this, self::CMD_MANAGE_CONDITIONS);
    }
}
