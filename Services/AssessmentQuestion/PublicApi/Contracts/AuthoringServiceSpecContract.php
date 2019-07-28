<?php

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Contracts;

use EventSubscriberContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\DomainObjectId;
use ILIAS\UI\Component\Link\Link;

/**
 * Interface QuestionAuthoringServiceSpec
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 *
 * @package ILIAS\Services\AssessmentQuestion\PublicApi\Contracts
 */
interface AuthoringServiceSpecContract {


	/**
	 * @param AsqAdditionalConfigSection $asq_additional_config_section
	 *
	 * Additional Form Seccitons for a question delivered by consumer. E.G. Taxonomie.
	 */
	public function addAdditionalConfigSection(AdditionalConfigSectionContract $asq_additional_config_section);


	public function subscribeToQuestionCreatedPublicEvent(EventSubscriberContract $asq_public_event_subscriber);


	public function subscribeToQuestionEditedPublicEvent(EventSubscriberContract $asq_public_event_subscriber);


	public function subscribeToQuestionDeletedPublicEvent(EventSubscriberContract $asq_public_event_subscriber);


	public function withSpecificRevision(string $revision_uuid);


	/**
	 * @return int
	 */
	public function getInitiatingUserId(): int;


	/**
	 * @return Link
	 */
	public function getContainerBacklink(): Link;


	/**
	 * @return array
	 */
	public function getAsqAdditionalConfigSections(): array;
}