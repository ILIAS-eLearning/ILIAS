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

use ILIAS\UI\Component\Signal;

/**
 * This is the interface for a workflow factory.
 */
interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *      A workflow step represents a single step in a sequence of steps.
     *      The status of a step consists of two parts: its availability and its
     *      outcome or result.
     *      Possible variants of availability are "available", "not available"
     *      and "not available anymore". The status "active" will be set by the workflow.
     *      The status of a step is defined as "not started", "in progress",
     *      "completed successfully" and "unsuccessfully completed".
     *   composition: >
     *     A workflow step consists of a label, a description and a marker
     *     that indicates its availability and result.
     *     If a step is available and carries an action, the label is rendered as shy-button.
     *   effect: >
     *     A Step MAY have an action; when clicked, the action is triggered.
     * context:
     *     - A Step MUST be used within a Workflow.
     * ----
     * @param string 	$label
     * @param string 	$description
     * @param null|string|\ILIAS\UI\Component\Signal 	$action
     * @return  \ILIAS\UI\Component\Listing\Workflow\Step
     */
    public function step(string $label, string $description = '', $action = null) : Step;

    /**
     * ---
     * description:
     *   purpose: >
     *      A linear workflow is the basic form of a workflow: the user
     *      should tackle every step, one after the other.
     *   composition: >
     *     A linear workflow has a title and lists a sequence of steps.
     *     If the user is currently working on a step, the step is marked as active.
     *   effect: >
     *     A Step MAY have an action; when clicked, the action is triggered.
     *
     * rules:
     *   usage:
     *       1: >
     *         Use a Linear Workflow for a set of tasks that should be performed one
     *         after the other and where there are no inter-dependencies other
     *         than completeness of the prior task.
     *       2: >
     *         You SHOULD NOT use Linear Workflow for workflows with forked paths
     *         due to user-decisions or calculations.
     *       3: >
     *         You SHOULD NOT use Linear Workflow for continuous workflows;
     *         a linear workflow MUST have a start- and end-point.
     *
     * ----
     * @param string 	$title
     * @param Step[] 	$steps
     * @return  \ILIAS\UI\Component\Listing\Workflow\Linear
     */
    public function linear(string $title, array $steps) : Linear;
}
