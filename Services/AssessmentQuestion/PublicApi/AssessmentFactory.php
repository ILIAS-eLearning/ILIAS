<?php
declare(strict_types=1);

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Factory;

use ILIAS\Services\AssessmentQuestion\PublicApi\Authoring\AuthoringService;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\AssessmentEntityId;
use ILIAS\Services\AssessmentQuestion\PublicApi\Processing\ProcessingService;

/**
 * Class AssessmentServices
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 *
 * @package ILIAS\Services\AssessmentQuestion\PublicApi\Factory
 */
class AssessmentFactory
{

    /**
     * This factory provides the following services
     * * Authoring Question Service
     * * Authoring Question List Service
     * * Authoring Question Import Service
     *
     * @param int $container_obj_id
     * @param int $actor_user_id
     *
     * @return AuthoringService
     */
    public function questionAuthoring(int $container_obj_id, int $actor_user_id) : AuthoringService
    {
        return new AuthoringService($container_obj_id, $actor_user_id);
    }


    /**
     * Use the services of this factory for
     * * presenting
     * * save user answers
     * * scoring
     *
     * @param int $actor_user_id
     *
     * @return ProcessingService
     */
    public function questionProcessing(int $actor_user_id)
    {
        return new ProcessingService($actor_user_id);
    }


    /**
     * As consumer you are responsible for creating the uuids
     * This factory helps you!
     *
     * @return AssessmentEntityId
     */
    public function entityIdBuilder() : AssessmentEntityId
    {
        //TODO
    }
}
