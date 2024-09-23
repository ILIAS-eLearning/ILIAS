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

namespace ILIAS\Test\Questions\Properties;

use ILIAS\Test\Utilities\TitleColumnsBuilder;
use ILIAS\TestQuestionPool\Questions\GeneralQuestionProperties;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Table\OrderingRowBuilder;
use ILIAS\UI\Component\Table\OrderingRow;
use ILIAS\Language\Language;

class Properties implements Property
{
    private ?PropertySequence $sequence = null;
    private PropertyAggregatedResults $aggregated_results;

    public function __construct(
        private readonly int $question_id,
        private readonly GeneralQuestionProperties $question_properties,
    ) {
        if ($question_id !== $question_properties->getQuestionId()) {
            throw new \Exception(
                sprintf(
                    'The question ids do not match.  Id local: %s. Id properties: %s.',
                    $question_id,
                    $question_properties->getQuestionId()
                )
            );
        }

        $this->aggregated_results = new PropertyAggregatedResults($question_id);
    }

    public function getQuestionId(): int
    {
        return $this->question_id;
    }

    public function getGeneralQuestionProperties(): GeneralQuestionProperties
    {
        return $this->question_properties;
    }

    public function getSequenceInformation(): ?PropertySequence
    {
        return $this->sequence;
    }

    public function withSequenceInformation(PropertySequence $sequence): self
    {
        $clone = clone $this;
        $clone->sequence = $sequence;
        return $clone;
    }

    public function getAggregatedResults(): ?PropertyAggregatedResults
    {
        return $this->aggregated_results;
    }

    public function withAggregatedResults(PropertyAggregatedResults $aggregated_results): self
    {
        $clone = clone $this;
        $clone->aggregated_results = $aggregated_results;
        return $clone;
    }

    public function getAsQuestionsTableRow(
        Language $lng,
        UIFactory $ui_factory,
        \Closure $question_target_link_builder,
        OrderingRowBuilder $row_builder,
        TitleColumnsBuilder $title_builder
    ): OrderingRow {
        return $row_builder->buildOrderingRow(
            (string) $this->question_id,
            [
                'question_id' => $this->question_id,
                'title' => $ui_factory->link()->standard(
                    $this->question_properties->getTitle(),
                    $question_target_link_builder($this->question_id)
                ),
                'description' => $this->question_properties->getDescription(),
                'type_tag' => $this->question_properties->getTypeName($lng),
                'points' => $this->question_properties->getAvailablePoints(),
                'author' => $this->question_properties->getAuthor(),
                'complete' => $this->question_properties->isRequiredInformationComplete(),
                'lifecycle' => \ilAssQuestionLifecycle::getInstance($this->question_properties->getLifecycle())->getTranslation($lng) ?? '',
                'qpl' => $title_builder->buildAccessCheckedQuestionpoolTitleAsLink($this->question_properties->getOriginObjectId()),
                'nr_of_answers' => $this->getAggregatedResults()->getNumberOfAnswers(),
                'average_points' => $this->getAggregatedResults()->getAveragePoints(),
                'percentage_points_achieved' => $this->getAggregatedResults()->getPercentageOfPointsAchieved()
            ]
        );
    }
}
