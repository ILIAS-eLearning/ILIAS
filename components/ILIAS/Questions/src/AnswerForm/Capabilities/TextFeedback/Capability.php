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

namespace ILIAS\Questions\AnswerForm\Capabilities\TextFeedback;

use ILIAS\Questions\AnswerForm\Capabilities\Capability as CapabilityInterface;
use ILIAS\Questions\AnswerForm\Capabilities\Definitions\ActionWithTab;
use ILIAS\Questions\AnswerForm\Capabilities\Definitions\AdditionalTabProvider;
use ILIAS\Questions\AnswerForm\Capabilities\Definitions\Feedback;
use ILIAS\Questions\AnswerForm\Capabilities\Definitions\FeedbackProvider;
use ILIAS\Questions\AnswerForm\Capabilities\Definitions\PageMigrationProvider;
use ILIAS\Questions\AnswerForm\Properties;
use ILIAS\Questions\Presentation\Definitions\Environment;
use ILIAS\Questions\Presentation\Layout\Async;
use ILIAS\Questions\Presentation\Layout\Viewable;
use ILIAS\Data\Text\Factory as TextFactory;
use ILIAS\Data\UUID\Factory as UuidFactory;
use ILIAS\Data\UUID\Uuid;

class Capability implements CapabilityInterface, AdditionalTabProvider, FeedbackProvider, PageMigrationProvider
{
    private const string SUB_ACTION_SAVE = 's';
    private const string SUB_ACTION_INSERT_LEGACY_TEXTS = 'ilt';

    public function __construct(
        private readonly TextFactory $text_factory,
        private readonly Repository $repository
    ) {
    }

    #[\Override]
    public static function getIdentifier(): string
    {
        return 'text_feedback';
    }


    #[\Override]
    public function isAvailableFor(
        Properties $answer_form_properties
    ): bool {
        return $answer_form_properties
            ->getTypeGenericProperties()
            ->getDefinition()
            ->hasCapability(
                self::getIdentifier()
            );
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
    public function getFeedback(
        Properties $answer_form_properties
    ): ?Feedback {
        return $this->repository->getFor(
            $answer_form_properties->getAnswerFormId(),
            $answer_form_properties
                ->getTypeGenericProperties()
                ->getDefinition()
                ->getCapability(
                    self::getIdentifier()
                )
        );
    }

    #[\Override]
    public function runPageMigrations(): void
    {
        $this->repository->migrateFeedbackPages();
    }

    #[\Override]
    public function onAnswerFormClone(
        UuidFactory $uuid_factory,
        Properties $old_answer_form_properties,
        Properties $new_answer_form_properties
    ): void {
        $this->repository->store(
            $new_answer_form_properties->getAnswerFormId(),
            $this->repository->getFor(
                $old_answer_form_properties->getAnswerFormId(),
                $old_answer_form_properties
                    ->getTypeGenericProperties()
                    ->getDefinition()
                    ->getCapability(self::getIdentifier())
            )->onAnswerFormClone(
                $uuid_factory,
                $old_answer_form_properties,
                $new_answer_form_properties
            )
        );
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
                    ->getCapability(self::getIdentifier())
            )->onAnswerFormUpdate(
                $answer_form_properties
            )
        );
    }

    public function onAnswerFormDelete(
        Properties $answer_form_properties
    ): void {
        $this->repository->delete(
            $answer_form_properties->getAnswerFormId()
        );
    }

    private function buildDoEditActionClosure(): \Closure
    {
        return function (
            Environment $environment
        ): Async|Viewable {
            $sub_action = $environment->getSubAction();
            return match ($sub_action) {
                '' => $this->buildOverview($environment),
                self::SUB_ACTION_INSERT_LEGACY_TEXTS => $this->buildOverviewWithLegacyTexts(
                    $environment
                ),
                self::SUB_ACTION_SAVE => $this->save($environment),
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
                    ->getCapability(self::getIdentifier())
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
