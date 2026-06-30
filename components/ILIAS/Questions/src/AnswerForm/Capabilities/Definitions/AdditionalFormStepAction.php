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

namespace ILIAS\Questions\AnswerForm\Capabilities\Definitions;

use ILIAS\Questions\AnswerForm\Capabilities\Capability;
use ILIAS\Questions\AnswerForm\Properties;
use ILIAS\Questions\AnswerForm\Views\Edit as AnswerFormEditView;
use ILIAS\Questions\Presentation\Definitions\DefaultEnvironment;
use ILIAS\Questions\Presentation\Layout\Async;
use ILIAS\Questions\Presentation\Layout\EditForm;
use ILIAS\Questions\Presentation\Layout\Tools\InputsBuilderSession;
use ILIAS\UI\Component\Table\Action\Action as TableAction;

class AdditionalFormStepAction
{
    private const string PREFIX = 'c-fsa';

    private const string SUB_ACTION_BACK = 'b';
    private const string SUB_ACTION_SAVE = 's';

    private Capability|AnswerFormEditView $previous;
    private ?Capability $next = null;

    /**
     * @param \Closure $retrieve_inputs_builder This Closure will be called when
     * the action is triggered. It will receive a
     * `ILIAS\Questions\Presentation\Definitions\Environment` as only parameter.
     * And MUST return a `ILIAS\Questions\Presentation\Layout\Tools\InputsBuilderSession`.
     */
    public function __construct(
        private readonly Capability $capability,
        private readonly string $lang_var,
        private readonly \Closure $retrieve_inputs_builder
    ) {
    }

    public function do(
        DefaultEnvironment $environment
    ): Async|EditForm|Properties {
        $sub_action = $environment->getSubAction();
        $environment_with_preserved_parameters = $environment
            ->withPreservedTableRowIdsParameter()
            ->withPreservedFormStartSubActionParameter();
        return match ($sub_action) {
            self::SUB_ACTION_SAVE => $this->forwardToNextForm(
                $environment_with_preserved_parameters
            ),
            self::SUB_ACTION_BACK => $this->backToPreviousForm(
                $environment_with_preserved_parameters
            ),
            default => $this->buildFormWithCarry(
                $environment_with_preserved_parameters
            )
        };
    }

    public function getIdentifier(): string
    {
        return self::PREFIX . '_' . md5($this->capability::class);
    }

    private function toPrevious(
        DefaultEnvironment $envrionment
    ): Async|EditForm {
        if ($this->previous instanceof AnswerFormEditView) {
            return $this->previous
                ->backToLastEditCommand($envrionment->withActionParameter(''))
                ->withIsFinalStep(false);
        }

        return $this->previous->getAnswerFormEditAdditionalStep()->do(
            $envrionment
        );
    }

    public function withPrevious(
        Capability|AnswerFormEditView $previous
    ): self {
        $clone = clone $this;
        $clone->previous = $previous;
        return $clone;
    }

    private function toNext(
        DefaultEnvironment $environment
    ): Async|EditForm|Properties {
        if ($this->next === null) {
            return $environment->getAnswerFormProperties();
        }

        return $this->next->getAnswerFormEditAdditionalStep()->do(
            $environment
        );
    }

    public function withNext(
        ?Capability $next
    ): self {
        $clone = clone $this;
        $clone->next = $next;
        return $clone;
    }

    public function getAsTableAction(
        DefaultEnvironment $environment
    ): TableAction {
        return $environment->getUIFactory()->table()->action()->standard(
            $environment->getLanguage()->txt($this->lang_var),
            $environment->withActionParameter(
                $this->getIdentifier()
            )->getUrlBuilder(),
            $environment->getTableRowIdToken()
        );
    }

    private function buildFormWithCarry(
        DefaultEnvironment $environment
    ): EditForm {
        $properties = $environment->getAnswerFormProperties();

        $inputs_builder_for_points = $this->retrieve_inputs_builder->__invoke(
            $environment
        )->withCarry(
            $properties->toCarry()
        );

        $inputs_builder_for_points->persistCarry();

        return $this->buildForm(
            $environment->withAnswerFormProperties($properties),
            $inputs_builder_for_points
        );
    }

    private function buildForm(
        DefaultEnvironment $environment,
        InputsBuilderSession $inputs_builder
    ): EditForm {
        $properties = $environment->getAnswerFormProperties();
        return $environment->getPresentationFactory()->getEditForm(
            $inputs_builder,
            $environment->withSubActionParameter(
                self::SUB_ACTION_SAVE
            )->getUrlBuilder(),
            $this->previous === null
                ? null
                : $environment->withSubActionParameter(
                    self::SUB_ACTION_BACK
                )->getUrlBuilder()
        )->withIsFinalStep(true)
        ->withContentBeforeForm(
            $properties->getClozeText()->buildPanelForEditing(
                $environment->getUIFactory(),
                $environment->getLanguage(),
                $properties->getGaps(),
                $properties->getLegacyClozeText()
            )
        );
    }

    private function forwardToNextForm(
        DefaultEnvironment $environment
    ): EditForm|Properties {
        $processed_form = $this->processForm($environment);
        if ($processed_form instanceof EditForm) {
            return $processed_form;
        }

        return $this->toNext(
            $environment->withAnswerFormProperties($processed_form)
        );
    }

    private function backToPreviousForm(
        DefaultEnvironment $environment
    ): EditForm {
        $processed_form = $this->processForm($environment);
        if ($processed_form instanceof EditForm) {
            return $processed_form;
        }

        return $this->toPrevious(
            $environment->withAnswerFormProperties($processed_form)
        );
    }

    private function processForm(
        DefaultEnvironment $environment
    ): EditForm|Properties {
        $inputs_builder = $this->retrieve_inputs_builder->__invoke($environment);
        $form = $this->buildForm(
            $environment,
            $inputs_builder
        )->withRequest($environment->getHttpServices()->request());

        $data = $form->getData();
        if ($data === null) {
            $inputs_builder->persistCarry();
            return $form;
        }

        return $data;
    }
}
