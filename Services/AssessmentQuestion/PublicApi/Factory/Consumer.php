<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Factory;

use ilDateTime;
use ILIAS\Services\AssessmentQuestion\PublicApi\AdditionalConfigSection;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AdditionalConfigSectionContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\QuestionIdContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\RevisionIdContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\UserAnswerIdContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\UserAnswerSubmitContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\RevisionId;
use ILIAS\Services\AssessmentQuestion\PublicApi\UserAnswerSubmit;
use ILIAS\Services\AssessmentQuestion\PublicApi\UserAnswerId;
use ILIAS\Services\AssessmentQuestion\PublicApi\QuestionId;
use ILIAS\Services\AssessmentQuestion\PublicApi\QuestionResourcesCollector;
use ilFormSectionHeaderGUI;
use ilFormPropertyGUI;
use JsonSerializable;
use Ramsey\Uuid\Uuid;

/**
 * Class Consumer
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 *
 * @package ILIAS\Services\AssessmentQuestion\PublicApi\Factory
 */
class Consumer {

	/**
	 * @param string $questionUuid
	 *
	 * @return QuestionIdContract
	 * @throws UuidIsInvalidException
	 */
	public function questionUuid($question_uuid): QuestionIdContract {
		$question = QuestionRepository::getInstance()->getQuestionById(new DomainObjectId($question_uuid));

		return new QuestionId($question_uuid, $question->getCreatedOnIliasNicId(), $question->getCreatedOn());
	}


	/**
	 *
	 * @return QuestionIdContract
	 * @throws \ilDateTimeException
	 */
	public function newQuestionUuid(): QuestionIdContract {
		global $DIC;

		return new QuestionId(UUID::uuid4(), $DIC->settings()->get('inst_id'), new ilDateTime());
	}


	/**
	 * @param string $questionUuid
	 * @param string $revisionUuid
	 *
	 * @return RevisionIdContract
	 * @throws UuidIsInvalidException
	 */
	public function revisionUuid($question_uuid, $revision_uuid): RevisionIdContract {
		$question = QuestionRepository::getInstance()
			->getQuestionRevisionById(new DomainObjectId($question_uuid), new DomainObjectId($question_uuid), new RevisionId($revision_uuid));

		return new RevisionId($question_uuid, $revision_uuid, $question->getRevision()->getCreatedOnIliasNicId(), $question->getRevision()
			->getCreatedOn());
	}


	/**
	 * @param $questionUuid
	 *
	 * @return RevisionIdContract
	 * @throws \ilDateTimeException
	 */
	public function newRevisionUuid($question_uuid): RevisionIdContract {
		global $DIC;

		return new RevisionId($question_uuid, UUID::create(), $DIC->settings()->get('inst_id'), new ilDateTime());
	}


	/**
	 * @param string $userAnswerUuid
	 *
	 * @return UserAnswerIdContract
	 */
	public function userAnswerUuid($user_answer_uuid): UserAnswerIdContract {
		//TODO
	}

	/**
	 * @param string $userAnswerUuid
	 *
	 * @return UserAnswerIdContract
	 */
	public function newUserAnswerUuid(): UserAnswerIdContract {
		return new UserAnswerId(UUID::create(),new ilDateTime());
	}


	/**
	 * @return QuestionResourcesCollector
	 */
	public function questionRessourcesCollector(): QuestionResourcesCollector {
		return new QuestionResourcesCollector();
	}


	/**
	 * @param ilFormSectionHeaderGUI $sectionHeader
	 * @param ilFormPropertyGUI[]    $sectionInputs
	 *
	 * @return AdditionalConfigSectionContract
	 */
	public function questionConfigSection(ilFormSectionHeaderGUI $sectionHeader, array $section_inputs): AdditionalConfigSectionContract {
		return new AdditionalConfigSection($sectionHeader, $section_inputs);
	}
}
