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
use ILIAS\UI\Implementation\Component\Table\OrderingRow;
use ILIAS\UI\URLBuilder;
use ILIAS\Language\Language;

class Properties implements Property
{
    private PropertySequence $sequence;
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
    }

    public function getQuestionId(): int
    {
        return $this->question_id;
    }

    public function getGeneralQuestionProperties(): GeneralQuestionProperties
    {
        return $this->question_properties;
    }

    public function getSequenceInformation(): PropertySequence
    {
        return $this->sequence;
    }

    public function withSequenceInformation(PropertySequence $sequence): self
    {
        if ($this->question_properties->getParentObjectId() !== $sequence->getTestId()) {
            throw new \Exception(
                sprintf(
                    'The test ids do not match.  Id question: %s. Id sequence: %s.',
                    $this->question_properties->getParentObjectId(),
                    $sequence->getTestId()
                )
            );
        }

        $clone = clone $this;
        $clone->sequence = $sequence;
        return $clone;
    }

    public function getAggregatedResults(): PropertyAggregatedResults
    {
        return $this->getAggregatedResults();
    }

    public function withAggregatedResults(PropertyAggregatedResults $aggregated_results): self
    {
        $clone = clone $this;
        $clone->aggregated_results = $aggregated_results;
        return $clone;
    }

    public function getAsQuestionsTableRow(
        UIFactory $ui_factory,
        Language $lng,
        URLBuilder $url_builder,
        OrderingRowBuilder $row_builder,
        TitleColumnsBuilder $title_builder,
        string $row_id_token
    ): OrderingRow {
        return $row_builder->buildOrderingRow(
            $this->question_id,
            [
                'title' => $ui_factory->link()->standard(
                    $this->question_properties->getTitle(),
                    $url_builder
                            ->withParameter($row_id_token, (string) $this->question_id)
                            ->buildURI()
                            ->__toString()
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
