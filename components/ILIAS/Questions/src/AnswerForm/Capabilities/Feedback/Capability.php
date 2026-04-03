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

namespace ILIAS\Questions\AnswerForm\Capabilities\Feedback;

use ILIAS\Questions\AnswerForm\Capabilities\ActionWithTab;
use ILIAS\Questions\AnswerForm\Capabilities\Capability as CapabilityInterface;
use ILIAS\Questions\AnswerForm\Properties;
use ILIAS\Questions\Presentation\Definitions\Environment;
use ILIAS\Questions\Presentation\Layout\Async;
use ILIAS\Questions\Presentation\Layout\Renderable;
use ILIAS\Data\Text\Factory as TextFactory;

class Capability implements CapabilityInterface
{
    private const string SUB_ACTION_SAVE = 's';
    private const string SUB_ACTION_INSERT_LEGACY_TEXTS = 'ilt';

    public function __construct(
        private readonly TextFactory $text_factory,
        private readonly Repository $repository
    ) {
    }

    #[\Override]
    public function isAvailableFor(
        Properties $answer_form_properties
    ): bool {
        return $answer_form_properties
            ->getTypeGenericProperties()
            ->getDefinition()
            ->hasCapability(
                Feedback::class
            );
    }

    #[\Override]
    public function providesAnswerFormEditAdditionalTab(): bool
    {
        return true;
    }

    #[\Override]
    public function getAnswerFormEditAdditionalTab(): ActionWithTab
    {
        return new ActionWithTab(
            $this,
            'feedback',
            $this->buildDoEditActionClosure()
        );
    }

    #[\Override]
    public function providesAnswerFormEditAdditionalStep(): bool
    {
        return false;
    }

    #[\Override]
    public function getAnswerFormEditAdditionalStep(): null
    {
        return null;
    }

    #[\Override]
    public function onAnswerFormUpdate(
        Properties $answer_form_properties
    ): void {
        $this->repository->store(
            $answer_form_properties->getAnswerFormId(),
            $this->repository->getFor(
                $answer_form_properties->getAnswerFormId(),
                $answer_form_properties
                    ->getTypeGenericProperties()
                    ->getDefinition()
                    ->getCapability(Feedback::class)
            )->onAnswerFormUpdate(
                $answer_form_properties
            )
        );
    }

    private function buildDoEditActionClosure(): \Closure
    {
        return function (
            Environment $environment
        ): Async|Renderable {
            $sub_action = $environment->getSubAction();
            return match ($sub_action) {
                '' => $this->buildOverview($environment),
                self::STEP_INSERT_LEGACY_TEXTS => $this->buildOverviewWithLegacyTexts(
                    $environment
                ),
                self::STEP_SAVE => $this->save($environment),
                default => $this->buildOverview($environment)->doAction(
                    $this->repository,
                    $sub_action
                )
            };
        };
    }

    private function buildOverview(
        Environment $environment
    ): Overview {
        return new Overview(
            $environment,
            $this->text_factory,
            $this->repository->getFor(
                $environment->getAnswerFormId(),
                $environment
                    ->getAnswerFormProperties()
                    ->getTypeGenericProperties()
                    ->getDefinition()
                    ->getCapability(Feedback::class)
            ),
            $environment->withSubActionParameter(
                self::SUB_ACTION_SAVE
            )->getUrlBuilder(),
            $environment->withSubActionParameter(
                self::SUB_ACTION_INSERT_LEGACY_TEXTS
            )->getUrlBuilder()
        );
    }

    private function buildOverviewWithLegacyTexts(
        Environment $environment
    ): Overview {
        return $this->buildOverview($environment)
            ->withLegacyTextsAsValues(true);
    }

    private function save(
        Environment $environment
    ): Overview {
        $feedback = $this->buildOverview($environment)->processForm();
        if ($feedback instanceof Overview) {
            return $feedback;
        }

        $this->repository->store(
            $environment->getAnswerFormId(),
            $feedback
        );

        return $environment->redirectTo(
            $environment->withDefaultSubAction()->getUrlBuilder()
        );
    }
}
