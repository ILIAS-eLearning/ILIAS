<?php declare(strict_types=1);

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

/**
 * This describes a Workflow.
 */
interface Workflow extends Component
{
    /**
     * Get the title of this workflow.
     */
    public function getTitle() : string;

    /**
     * The step at this position is set to active.
     *
     * @throws \InvalidArgumentException 	if $active exceeds the amount of steps
     */
    public function withActive(int $active) : Workflow;

    /**
     * This is the index of the active step.
     */
    public function getActive() : int;

    /**
     * Get the steps of this workflow.
     *
     * @return Step[]
     */
    public function getSteps() : array;
}
