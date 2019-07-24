<?php

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Contracts;

use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AsqApiContainerIdContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AsqApiQuestionIdContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\DomainObjectId;
use ILIAS\UI\Component\Link\Link;

/**
 * Interface AsqApiPlayServiceSpecContract
 *
 * @package ILIAS\Services\AssessmentQuestion\PublicApi
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
interface AsqApiAuthoringQuestionServiceSpecContract {

	/**
	 * AsqApiAuthoringQuestionServiceSpecInterface constructor.
	 *
	 * @param AsqApiContainerIdContract $container_id
	 * @param AsqApiQuestionIdContract  $question_uuid
	 * @param int                       $actor_user_id
	 * @param Link                      $container_backlink
	 */
	public function __construct(AsqApiContainerIdContract $container_id, AsqApiQuestionIdContract $question_uuid, int $actor_user_id, Link $container_backlink);


	/**
	 * @param AsqAdditionalConfigSection $asq_additional_config_section
	 *
	 * Additional Form Seccitons for a question delivered by consumer. E.G. Taxonomie.
	 */
	public function addAdditionalConfigSection(AsqAdditionalConfigSection $asq_additional_config_section);


	public function subscribeToQuestionCreatedPublicEvent(AsqApiEventSubscriber $asq_public_event_subscriber);


	public function subscribeToQuestionEditedPublicEvent(AsqApiEventSubscriber $asq_public_event_subscriber);


	public function subscribeToQuestionDeletedPublicEvent(AsqApiEventSubscriber $asq_public_event_subscriber);


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