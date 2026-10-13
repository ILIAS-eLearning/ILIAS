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

use ILIAS\DI\Container;
use ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper;
use ILIAS\LearningSequence\Content\Condition\AbstractCondition;
use ILIAS\LearningSequence\Content\Condition\ConditionFactory;
use ILIAS\LearningSequence\Content\Condition\ilObjLearningSequenceConditionDiscover;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @ilCtrl_isCalledBy ilObjLearningSequenceConditionConfigurationGUI: ilObjLearningSequenceConditionsGUI
 */
class ilObjLearningSequenceConditionConfigurationGUI implements ilCtrlSecurityInterface
{
    protected int $lso_ref_id;
    protected int $item_ref_id;
    protected ?int $condition_id = null;
    protected ?int $type_id = null;
    protected ?string $subtype = null;
    protected bool $create = true;
    private ArrayBasedRequestWrapper $query;
    private Container $dic;
    private ilObjLearningSequenceConditionDiscover $discoverer;
    private ConditionFactory $condition_factory;

    public function __construct(
        protected ilCtrl $ctrl,
        protected ilGlobalTemplateInterface $tpl,
        protected ilLanguage $lng,
        protected ilAccess $access,
        protected ArrayBasedRequestWrapper $post_wrapper,
        protected ILIAS\UI\Factory $ui_factory,
        protected ILIAS\UI\Renderer $ui_renderer,
        protected ServerRequestInterface $request,
    ) {
        global $DIC;
        $this->dic = $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->query = $DIC->http()->wrapper()->query();
        $this->discoverer = new ilObjLearningSequenceConditionDiscover();
        $this->condition_factory = new ConditionFactory($this->discoverer, $DIC->database());
    }

    public const string CMD_CREATE_CONDITION = "createCondition";
    public const string CMD_CONFIGURE_COMMAND = "configure";

    /**
     * @return void
     * @throws ilCtrlException
     * @throws ilException
     */
    public function executeCommand(): void
    {
        $this->initCondition();
        $this->assertWriteAccess();
        $this->initBackTab();

        // ilCtrl returns no command if the CSRF validation fails, fall back
        // to the configuration form in this case so it can be submitted again.
        $cmd = $this->ctrl->getCmd(self::CMD_CONFIGURE_COMMAND);

        switch ($cmd) {
            case self::CMD_CONFIGURE_COMMAND:
            case self::CMD_CREATE_CONDITION:
                $this->$cmd();
                break;
            default:
                throw new ilException("ilObjLearningSequenceConditionGUI: Command not supported: $cmd");
        }
    }

    /**
     * The configuration forms are submitted to a regular link target with
     * cmd=createCondition, which ilCtrl treats as GET command.
     */
    public function getUnsafeGetCommands(): array
    {
        return [
            self::CMD_CREATE_CONDITION,
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
     * @throws ilCtrlException
     */
    private function initBackTab(): void
    {
        global $DIC;
        $this->ctrl->setParameterByClass(ilObjLearningSequenceConditionsGUI::class, 'item_ref_id', $this->item_ref_id);
        $DIC->tabs()->setBack2Target(
            $this->lng->txt('back'),
            $this->ctrl->getLinkTargetByClass(
                ilObjLearningSequenceConditionsGUI::class,
                ilObjLearningSequenceConditionsGUI::CMD_MANAGE_CONDITIONS
            )
        );
    }

    /**
     * @return void
     * @throws ilCtrlException
     */
    private function initCondition(): void
    {
        $int_list = $this->dic->refinery()->kindlyTo()->listOf($this->dic->refinery()->kindlyTo()->int());
        $int = $this->dic->refinery()->kindlyTo()->int();

        if ($this->query->has('condition_id')) {
            $this->create = false;

            $condition_id = $this->query->retrieve('condition_id', $int_list);
            if (is_array($condition_id)) {
                $this->condition_id = current($condition_id);
            } else {
                $this->condition_id = $condition_id;
            }

            $query = $this->dic->database()->queryF(
                'SELECT * FROM lso_conditions WHERE condition_id = %s',
                ['integer'],
                [$this->condition_id],
            );
            if ($record = $this->dic->database()->fetchAssoc($query)) {
                $this->type_id = $record['type_id'];
            }
        }

        if ($this->query->has('ref_id')) {
            $this->lso_ref_id = $this->query->retrieve('ref_id', $int);
        }
        if ($this->query->has('item_ref_id')) {
            $this->item_ref_id = $this->query->retrieve('item_ref_id', $int);
        }
        if (is_null($this->type_id) && $this->query->has('type_id')) {
            $this->type_id = $this->query->retrieve('type_id', $int);
        }
        if ($this->query->has('subtype')) {
            $this->subtype = $this->query->retrieve('subtype', $this->dic->refinery()->kindlyTo()->string());
            $this->ctrl->setParameter($this, 'subtype', $this->subtype);
        }
    }

    /**
     * @return void
     * @throws ilCtrlException
     * @throws ilException
     * @throws ReflectionException
     */
    protected function configure(): void
    {
        try {
            $condition = $this->buildCondition();
        } catch (\Exception $e) {
            // condition not found - redirect
            $this->tpl->setOnScreenMessage(
                'failure',
                $this->lng->txt('lso_exception_condition_not_exists'),
                true
            );

            $this->ctrl->setParameterByClass(ilObjLearningSequenceConditionsGUI::class, 'item_ref_id', $this->item_ref_id);
            $this->ctrl->redirectByClass(
                ilObjLearningSequenceConditionsGUI::class,
                ilObjLearningSequenceConditionsGUI::CMD_MANAGE_CONDITIONS
            );
            return;
        }

        $this->checkIfConditionAlreadyExistsInItem($condition);

        $this->tpl->setContent(
            $this->ui_renderer->render(
                $condition->getAdditionalForm()
            )
        );
    }

    /**
     * @param AbstractCondition $condition
     * @return void
     * @throws ReflectionException
     * @throws ilCtrlException
     * @throws ilException
     */
    private function checkIfConditionAlreadyExistsInItem(AbstractCondition $condition): void
    {
        if (!$this->create) {
            return;
        }

        $condition_ids = $this->discoverer->getAllConditionIdsForItem($this->item_ref_id);
        foreach ($condition_ids as $condition_id) {
            $any_condition_of_object = $this->condition_factory->getConditionInstanceById($condition_id);
            if (
                $condition->getTypeId() === $any_condition_of_object->getTypeId()
                && !$condition->allowMultipleConditionsOfSameType()
            ) {
                $this->tpl->setOnScreenMessage(
                    'failure',
                    $this->lng->txt('condition_already_exists'),
                    true
                );

                $this->ctrl->setParameterByClass(
                    ilObjLearningSequenceConditionsGUI::class,
                    'item_ref_id',
                    $this->item_ref_id
                );
                $this->ctrl->redirectByClass(
                    ilObjLearningSequenceConditionsGUI::class,
                    ilObjLearningSequenceConditionsGUI::CMD_MANAGE_CONDITIONS
                );
                return;
            }
        }
    }

    /**
     * @throws ilCtrlException
     * @throws ilException
     * @throws ReflectionException
     */
    protected function createCondition(): void
    {
        $condition = $this->buildCondition();
        $condition->clearValidationMessages();

        $form = $condition->getAdditionalForm();

        if ($form === null) {
            throw new ilException('Condition does not provide a configuration form.');
        }

        $form = $form->withRequest($this->request);
        $data = $form->getData();

        if (!$data) {
            $this->tpl->setContent($this->ui_renderer->render($form));
            return;
        }

        $condition->applyAdditionalFormData($data);
        if ($condition->hasValidationMessages()) {
            $this->showValidationMessages($condition);
            $this->tpl->setContent($this->ui_renderer->render($form));
            return;
        }

        if ($this->create) {
            $condition->create();
        } else {
            $condition->edit();
        }
        if ($condition->hasValidationMessages()) {
            $this->showValidationMessages($condition);
            $this->tpl->setContent($this->ui_renderer->render($form));
            return;
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'), true);
        foreach ($this->getStaticInputConfigurationMessages($condition) as $message) {
            $this->tpl->setOnScreenMessage('info', $message, true);
        }
        $this->ctrl->setParameterByClass(ilObjLearningSequenceConditionsGUI::class, 'ref_id', $this->lso_ref_id);
        $this->ctrl->setParameterByClass(ilObjLearningSequenceConditionsGUI::class, 'item_ref_id', $this->item_ref_id);
        $this->ctrl->redirectByClass(
            ilObjLearningSequenceConditionsGUI::class,
            ilObjLearningSequenceConditionsGUI::CMD_MANAGE_CONDITIONS
        );
    }

    /**
     * @throws ilException
     * @throws ReflectionException
     */
    private function buildCondition(): AbstractCondition
    {
        if (!$this->create) {
            if ($this->condition_id === null) {
                throw new ilException('Condition id is missing for edit.');
            }

            return $this->condition_factory->getConditionInstanceById($this->condition_id);
        }

        return $this->condition_factory->getNewConditionInstance(
            $this->lso_ref_id,
            $this->item_ref_id,
            $this->type_id,
            $this->subtype,
        );
    }

    private function showValidationMessages(AbstractCondition $condition): void
    {
        foreach ($condition->getValidationMessages() as $message) {
            $this->tpl->setOnScreenMessage('failure', $message, false);
        }
    }

    /**
     * @return string[]
     */
    private function getStaticInputConfigurationMessages(AbstractCondition $condition): array
    {
        $grouped_titles = [];

        foreach ($condition->getStaticInputConfigurationIssues() as $issue) {
            if ($issue->summary_message_language_var === null) {
                continue;
            }

            foreach ($issue->affected_ref_ids as $ref_id) {
                $title = ilObject::_lookupTitle(ilObject::_lookupObjId($ref_id));
                $grouped_titles[$issue->summary_message_language_var][$title] = $title;
            }
        }

        if ($grouped_titles === []) {
            return [];
        }

        ksort($grouped_titles);

        $messages = [];
        foreach ($grouped_titles as $lang_var => $titles) {
            sort($titles);
            $messages[] = sprintf($this->lng->txt($lang_var), implode(', ', $titles));
        }

        return $messages;
    }
}
