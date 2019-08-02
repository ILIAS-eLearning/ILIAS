<?php

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Contracts;

use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\DomainObjectId;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AdditionalConfigSectionContract;
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
	 * AsqApiAuthoringQuestionServiceSpecInterface constructor.
	 *
	 * @param int $container_obj_id
	 * @param QuestionIdContract $question_uuid
	 * @param int $actor_user_id
	 * @param Link $container_backlink
	 */
	public function __construct(int $container_obj_id, QuestionIdContract $question_uuid, int $actor_user_id, Link $container_backlink);


	/**
	 * @param AdditionalConfigSectionContract $asq_additional_config_section
	 *
	 * Additional Form Seccitons for a question delivered by consumer. E.G. Taxonomie.
	 */
	public function addAdditionalConfigSection(AdditionalConfigSectionContract $asq_additional_config_section);


	public function subscribeToQuestionCreatedPublicEvent(EventSubscriber $asq_public_event_subscriber);


	public function subscribeToQuestionEditedPublicEvent(EventSubscriber $asq_public_event_subscriber);


	public function subscribeToQuestionDeletedPublicEvent(EventSubscriber $asq_public_event_subscriber);


	public function withSpecificRevision(string $revision_uuid);


	/**
	 * @return int
	 */
	public function getActorUserId(): int;


	/**
	 * @return Link
	 */
	public function getContainerBacklink(): Link;


	/**
	 * @return array
	 */
	public function getAsqAdditionalConfigSections(): array;
}