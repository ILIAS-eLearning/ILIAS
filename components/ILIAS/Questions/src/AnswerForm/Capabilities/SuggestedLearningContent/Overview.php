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

namespace ILIAS\Questions\AnswerForm\Capabilities\SuggestedLearningContent;

use ILIAS\Questions\Presentation\Definitions\Editability;
use ILIAS\Questions\Presentation\Definitions\Environment;
use ILIAS\Questions\Presentation\Layout\Async;
use ILIAS\Questions\Presentation\Layout\Renderable;
use ILIAS\Questions\Presentation\Layout\Tools\InputsBuilderSession;
use ILIAS\StaticURL\Services as StaticURLServices;
use ILIAS\UI\Component\Input\Container\Form\Standard as StandardForm;
use ILIAS\UI\Component\Input\Field\Select;
use ILIAS\UI\Component\Input\Field\Section;
use ILIAS\UI\Component\Panel\Standard as StandardPanel;
use ILIAS\UI\Component\Prompt\Prompt;
use ILIAS\UI\Renderer as UIRenderer;

class Overview implements Renderable
{
    private const string SUB_ACTION_SELECT_TYPE = 'st';
    private const string SUB_ACTION_SELECT_CONTENT = 'sc';
    private const string SUB_ACTION_SAVE_CONTENT = 'sac';
    private const string SUB_ACTION_SAVE_SUB_CONTENT = 'ssc';

    public function __construct(
        private readonly \ilCtrl $ctrl,
        private readonly \ilRbacSystem $rbac_system,
        private readonly \ilTree $tree,
        private readonly \ilObjUser $current_user,
        private readonly StaticURLServices $static_url,
        private readonly Environment $environment,
        private readonly Repository $repository
    ) {
    }

    #[\Override]
    public function render(
        UIRenderer $ui_renderer
    ): string {
        return $ui_renderer->render([
            $this->buildPanel()
        ]);
    }

    public function doAction(
        string $action
    ): Async {
        return match($action) {
            self::SUB_ACTION_SELECT_TYPE => $this->buildPromptShowAsync(
                $this->buildSelectTypeForm()
            ),
            self::SUB_ACTION_SELECT_CONTENT => $this->processSelectTypeForm(),
            self::SUB_ACTION_SAVE_CONTENT => $this->processSelectContentForm(),
            self::SUB_ACTION_SAVE_SUB_CONTENT => $this->processSelectSubContentForm(),
            default => $this->forwardActionToActors($action)
        };
    }

    private function buildPrompt(): Prompt
    {
        return $this->environment->getUIFactory()->prompt()->standard(
            $this->environment->withSubActionParameter(
                self::SUB_ACTION_SELECT_TYPE
            )->getUrlBuilder()->buildURI()
        );
    }

    private function buildPanel(): StandardPanel
    {
        $content = [
            $this->environment->getUIFactory()->listing()->descriptive(
                $this->repository->getFor(
                    $this->environment->getAnswerFormId()
                )->getListing(
                    $this->ctrl,
                    $this->static_url,
                    $this->environment
                )
            )
        ];

        if ($this->environment->getEditability() === Editability::Full) {
            $prompt = $this->buildPrompt();
            $content[] = $prompt;
            $content[] = $this->environment->getUIFactory()->button()->standard(
                $this->environment->getLanguage()->txt('edit'),
                $prompt->getShowSignal()
            );
        }

        return $this->environment->getUIFactory()->panel()->standard(
            $this->environment->getLanguage()->txt('suggested_learning_content'),
            $content
        );
    }

    private function forwardActionToActors(
        string $action
    ): Async {
        $node_retrieval = new NodeRetrieval(
            $this->rbac_system,
            $this->tree,
            $this->environment,
            $this->retrieveReferencedObjectTypeFromCarry()
        );

        if ($node_retrieval->can($action)) {
            return $node_retrieval->do($action);
        }

        $upload_handler = $this->environment->getPresentationFactory()->getUploadHandler(
            $this->environment,
            new Stakeholder(
                $this->current_user->getId()
            )
        );

        if ($upload_handler->can($action)) {
            return $upload_handler->do($action);
        }

        throw new \InvalidArgumentException(
            "No actor found that can '{$action}'"
        );
    }

    private function buildSelectTypeForm(): StandardForm
    {
        $uf = $this->environment->getUIFactory();

        return $uf->input()->container()->form()->standard(
            $this->environment->withSubActionParameter(
                self::SUB_ACTION_SELECT_CONTENT
            )->getUrlBuilder()->buildURI()->__toString(),
            [
                'type' => $this->buildTypeSelect()
            ]
        )->withSubmitLabel(
            $this->environment->getLanguage()->txt('next')
        );
    }

    private function processSelectTypeForm(): Async
    {
        $form = $this->buildSelectTypeForm()->withRequest(
            $this->environment->getHttpServices()->request()
        );
        $data = $form->getData();
        if ($data === null) {
            return $this->buildPromptShowAsync($form);
            ;
        }

        if ($data['type'] === Types::None) {
            $this->repository->delete(
                $this->environment->getAnswerFormId()
            );
            return $this->buildRedirectToOverviewAsync();
        }

        $inputs_builder = $this->buildInputsBuilderSelectContentForm()
            ->withCarry($data['type']->value);
        $inputs_builder->persistCarry();

        return $this->buildPromptShowAsync(
            $this->buildSelectContentForm($inputs_builder)
        );
    }

    private function buildSelectContentForm(
        InputsBuilderSession $inputs_builder
    ): StandardForm {
        $uf = $this->environment->getUIFactory();

        $form = $uf->input()->container()->form()->standard(
            $this->environment->withSubActionParameter(
                self::SUB_ACTION_SAVE_CONTENT
            )->getUrlBuilder()->buildURI()->__toString(),
            [
                'content' => $inputs_builder->getInputs()
            ]
        );

        $type = $inputs_builder->retrieveCarry(
            $this->environment->getRefinery()->custom()->transformation(
                fn(string $v): Types => Types::tryFrom($v)
            )
        );
        if ($type->hasSelectContentSubForm()) {
            return $form->withSubmitLabel(
                $this->environment->getLanguage()->txt('next')
            );
        }
        return $form;
    }

    private function processSelectContentForm(): Async
    {
        $select_content_inputs_builder = $this->buildInputsBuilderSelectContentForm();
        $form = $this->buildSelectContentForm(
            $select_content_inputs_builder
        )->withRequest(
            $this->environment->getHttpServices()->request()
        );
        $data = $form->getData();
        if ($data === null) {
            $select_content_inputs_builder->persistCarry();
            return $this->buildPromptShowAsync($form);
        }

        if (!$data['content']->getType()->hasSelectContentSubForm()) {
            $this->repository->store($data['content']);
            return $this->buildRedirectToOverviewAsync();
        }

        $select_sub_content_inputs_builder = $this
            ->buildInputsBuilderSelectSubContentForm()
            ->withCarry(
                json_encode([
                    'type' => $data['content']->getType()->value,
                    'target_ref_id' => $data['content']->getTargetRefId()
                ])
            );
        $select_sub_content_inputs_builder->persistCarry();

        return $this->buildPromptShowAsync(
            $this->buildSelectSubContentForm($select_sub_content_inputs_builder)
        );
    }

    private function buildSelectSubContentForm(
        InputsBuilderSession $inputs_builder
    ): StandardForm {
        $uf = $this->environment->getUIFactory();

        $form = $uf->input()->container()->form()->standard(
            $this->environment->withSubActionParameter(
                self::SUB_ACTION_SAVE_SUB_CONTENT
            )->getUrlBuilder()->buildURI()->__toString(),
            [
                'content' => $inputs_builder->getInputs()
            ]
        );
        return $form;
    }

    private function processSelectSubContentForm(): Async
    {
        $inputs_builder = $this->buildInputsBuilderSelectSubContentForm();
        $form = $this->buildSelectSubContentForm(
            $inputs_builder
        )->withRequest(
            $this->environment->getHttpServices()->request()
        );
        $data = $form->getData();
        if ($data === null) {
            $inputs_builder->persistCarry();
            return $this->buildPromptShowAsync($form);
        }

        $this->repository->store($data['content']);
        return $this->buildRedirectToOverviewAsync();
    }

    private function buildTypeSelect(): Select
    {
        return $this->environment->getUIFactory()->input()->field()->select(
            $this->environment->getLanguage()->txt('type'),
            array_reduce(
                Types::cases(),
                function (array $c, Types $v): array {
                    $c[$v->value] = $v->getTranslatedOptionName($this->environment->getLanguage());
                    return $c;
                },
                []
            )
        )->withAdditionalTransformation(
            $this->environment->getRefinery()->custom()->transformation(
                fn(string $v): Types => Types::tryFrom($v) ?? Types::None
            )
        )->withRequired(true);
    }

    private function buildInputsBuilderSelectContentForm(): InputsBuilderSession
    {
        return $this->environment->getPresentationFactory()
            ->getSessionBasedInputsBuilder(
                $this->environment->getRefinery()->custom()->transformation(
                    fn(string $v): Section
                        => Types::tryFrom($v)->buildContentInput(
                            $this->repository,
                            $this->rbac_system,
                            $this->tree,
                            $this->environment,
                            $this->current_user->getId()
                        )
                )
            );
    }

    private function buildInputsBuilderSelectSubContentForm(): InputsBuilderSession
    {
        return $this->environment->getPresentationFactory()
            ->getSessionBasedInputsBuilder(
                $this->environment->getRefinery()->custom()->transformation(
                    function (string $v): Section {
                        $v_array = json_decode($v, true);
                        return Types::tryFrom($v_array['type'])->buildSubContentInput(
                            $this->repository,
                            $this->rbac_system,
                            $this->environment,
                            $v_array['target_ref_id']
                        );
                    }
                )
            );
    }

    private function buildPromptShowAsync(
        StandardForm $form
    ): Async {
        $lng = $this->environment->getLanguage();

        return $this->environment->getPresentationFactory()->getAsync(
            $this->environment->getUIFactory()->prompt()->state()->show(
                $form
            )->withTitle(
                $lng->txt('edit_suggested_solution')
            )
        );
    }

    private function buildRedirectToOverviewAsync(): Async
    {
        return $this->environment->getPresentationFactory()->getAsync(
            $this->environment->getUIFactory()->prompt()->state()->redirect(
                $this->environment
                    ->withDefaultSubAction()
                    ->getUrlBuilder()
                    ->buildURI()
            )
        );
    }

    private function retrieveReferencedObjectTypeFromCarry(): string
    {
        $inputs_builder = $this->buildInputsBuilderSelectContentForm();
        $inputs_builder->persistCarry();

        return $inputs_builder->retrieveCarry(
            $this->environment->getRefinery()->custom()->transformation(
                fn(string $v): string => Types::tryFrom($v)
                    ->getReferencedObjectType()
            )
        );
    }
}
