<?php
declare(strict_types=1);

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Common;

use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerOptions;
use ILIAS\AssessmentQuestion\DomainModel\QuestionData;
use ILIAS\AssessmentQuestion\DomainModel\QuestionLegacyData;
use ILIAS\AssessmentQuestion\DomainModel\QuestionPlayConfiguration;

interface QuestionDto
{
    /**
     * @return string
     */
    public function getId(): string;

    /**
     * @return int
     */
    public function getQuestionIntId(): int;

    /**
     * @return string
     */
    public function getRevisionId(): string;


    /**
     * @return string
     */
    public function getRevisionName(): string;

    /**
     * @return QuestionData
     */
    public function getData(): ?QuestionData;


    /**
     * @return QuestionPlayConfiguration
     */
    public function getPlayConfiguration(): ?QuestionPlayConfiguration;


    /**
     * @return AnswerOptions
     */
    public function getAnswerOptions(): AnswerOptions;
}