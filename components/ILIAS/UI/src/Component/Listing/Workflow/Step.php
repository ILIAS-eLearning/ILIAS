<?php

declare(strict_types=1);

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

namespace ILIAS\UI\Component\Listing\Workflow;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Signal;

/**
 * This describes a Workflow Step
 */
interface Step extends Component
{
    public const AVAILABLE = 1;
    public const NOT_AVAILABLE = 2;
    public const NOT_ANYMORE = 3;
    public const ACTIVE = 4;

    public const NOT_STARTED = 1;
    public const IN_PROGRESS = 2;
    public const SUCCESSFULLY = 3;
    public const UNSUCCESSFULLY = 4;

    /**
     * Get the label of this step.
     */
    public function getLabel(): string;

    /**
     * Get the description of this step.
     */
    public function getDescription(): string;


    /**
     * Get the availability status of this step.
     *
     * @return 	mixed
     */
    public function getAvailability();

    /**
     * Get a step like this with completion status according to parameter.
     *
     * @param 	mixed 	$status
     */
    public function withAvailability($status): Step;

    /**
     * Get the status of this step.
     *
     * @return 	mixed
     */
    public function getStatus();

    /**
     * Get a step like this with completion status according to parameter.
     *
     * @param 	mixed 	$status
     */
    public function withStatus($status): Step;

    /**
     * Get the action of this Step.
     *
     * @return	null|Signal|string
     */
    public function getAction();
}
