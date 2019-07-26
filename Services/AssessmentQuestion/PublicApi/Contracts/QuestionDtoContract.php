<?php

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Contracts;

use ILIAS\UI\Component\Link\Link;

/**
 * Interface QuestionDtoContract
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 *
 * @package ILIAS\Services\AssessmentQuestion\PublicApi\Contracts
 */
interface QuestionDtoContract {

	public function getQuestionUuid(): string;


	public function getRevisionUuid(): string;


	public function getTitle(): string;


	public function getQuestionText(): string;


	public function getAuthor(): string;


	public function getCreationLink(): Link;


	public function getEditLink(): Link;


	public function getPreviewLink(): Link;


	public function getEditPageLink(): Link;


	public function getEditFeedbacksLink(): Link;


	public function getEditHintsLink(): Link;


	public function getStatisticLink(): Link;
}
