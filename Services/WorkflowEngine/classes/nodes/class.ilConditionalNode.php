<?php

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

/**
 * Conditional node of the petri net based workflow engine.
 *
 * The conditional node is a deciding node. It features a doubled set of emitters
 * and activities. The new set is prefixed with 'else_'. In the core of it, a
 * given piece of php code is executed that returns either true, false or null, telling
 * the node which set is to be executed - if any. This takes advantage of create_function,
 * which is just as evil as eval. Because of that, the configuration of the
 * conditional node _must not_ be made available to users/admins and the content
 * which makes up the evaluation method has no business in the database as this
 * may pose a severe risk. Put these code pieces into the workflow object and
 * set it during workflow creation. Remember: I told you before.
 * Alternatively, you can extend this class and pre-set a code, that takes
 * parameters from ilSetting, ini-Files, water diviner... up to you.
 * Keep in mind to check these parameters for type and where possible, avoid
 * strings and/or types that allow larger code segments.
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @ingroup Services/WorkflowEngine
 */
class ilConditionalNode extends ilBaseNode
{
    /**
     * This holds a list of emitters attached to the node.
     * In this node type, these are the 'else' emitters.
     * @var null|ilEmitter[] Array of ilEmitter
     */
    private ?array $else_emitters = null;

    /**
     * This holds a list of activities attached to the node.
     * In this node type, these are the 'else' activities.
     * @var null|ilActivity[] Array of ilActivity
     */
    private ?array $else_activities = null;

    /**
     * This holds the piece of code used to determine if the 'then' or the 'else'
     * sets of activities and emitters are to be used.
     *
     * @var string PHP code to be executed to determine the 'decision' of the node.
     */
    private string $evaluation_expression = "return true;";

    /**
     * Default constructor.
     *
     * @param ilWorkflow Reference to the parent workflow.
     */
    public function __construct(ilWorkflow $a_context)
    {
        $this->context = $a_context;
        $this->detectors = [];
        $this->emitters = [];
        $this->else_emitters = [];
        $this->activities = [];
        $this->else_activities = [];
    }

    /**
     * Activates the node.
     */
    public function activate()
    {
        $this->active = true;
        foreach ($this->detectors as $detector) {
            $detector->onActivate();
        }
        $this->onActivate();
        $this->attemptTransition();
    }

    /**
     * Deactivates the node.
     */
    public function deactivate()
    {
        $this->active = false;
        foreach ($this->detectors as $detector) {
            $detector->onDeactivate();
        }
        $this->onDeactivate();
    }

    /**
     * Checks, if the preconditions of the node to transit are met.
     *
     * Please note, that in a conditional node, this means the node can transit
     * to one or another outcome. This method only returns false, if the return
     * value of the method is neither true nor false.
     *
     * @return bool True, if node is ready to transit.
     */
    public function checkTransitionPreconditions(): bool
    {
        $eval_function = function ($detectors) {
            return eval($this->evaluation_expression);
        };

        return $eval_function($this->detectors) !== null;
    }

    /**
     * Attempts to transit the node.
     *
     * Basically, this checks for preconditions and transits, returning true or
     * false if preconditions are not met, aka detectors are not fully satisfied.
     *
     * @return bool True, if transition succeeded.
     */
    public function attemptTransition(): bool
    {
        $eval_function = function ($detectors) {
            return eval($this->evaluation_expression);
        };

        if ($eval_function($this->detectors) === null) {
            return false;
        }

        if ($eval_function($this->detectors) === true) {
            $this->executeTransition();
            return true;
        }

        $this->executeElseTransition();

        return true;
    }

    /**
     * Executes all 'then'-activities attached to the node.
     */
    private function executeActivities(): void
    {
        if (count($this->activities) !== 0) {
            foreach ($this->activities as $activity) {
                $activity->execute();
            }
        }
    }

    /**
     * Exectes all 'else'-activities attached to the node.
     */
    private function executeElseActivities(): void
    {
        if (count($this->else_activities) !== 0) {
            foreach ($this->else_activities as $activity) {
                $activity->execute();
            }
        }
    }

    /**
     * Executes all 'then'-emitters attached to the node.
     */
    private function executeEmitters(): void
    {
        if (count($this->emitters) !== 0) {
            foreach ($this->emitters as $emitter) {
                $emitter->emit();
            }
        }
    }

    /**
     * Executes all 'else'-emitters attached to the node.
     */
    private function executeElseEmitters(): void
    {
        if (count($this->else_emitters) !== 0) {
            foreach ($this->else_emitters as $emitter) {
                $emitter->emit();
            }
        }
    }

    /**
     * Executes the 'then'-transition of the node.
     */
    public function executeTransition()
    {
        $this->deactivate();
        $this->executeActivities();
        $this->executeEmitters();
    }

    /**
     * Executes the 'else'-transition of the node.
     */
    public function executeElseTransition(): void
    {
        $this->deactivate();
        $this->executeElseActivities();
        $this->executeElseEmitters();
    }

    /**
     * Adds an emitter to one of the lists attached to the node.
     * @param ilEmitter $emitter
     * @param bool   $else_emitter True, if the emitter should be an 'else'-emitter.
     */
    public function addEmitter(ilEmitter $emitter, bool $else = false): void
    {
        if (!$else) {
            $this->emitters[] = $emitter;
        } else {
            $this->else_emitters[] = $emitter;
        }
    }

    /**
     * Adds an activity to one of the lists attached to the node.
     * @param ilActivity $activity
     * @param bool    $else_activity True, if the activity should be an 'else'-activity.
     */
    public function addActivity(ilActivity $activity, bool $else = false): void
    {
        if (!$else) {
            $this->activities[] = $activity;
        } else {
            $this->else_activities[] = $activity;
        }
    }

    /**
     * This sets the evaluation expression for the node.
     *
     * Example:
     * 		$evaluation_expression=
     *	'
     *		# detector 0 is lp-start,
     *		# detector 1 is lp-timeout
     *		$retval = null;
     *
     *		if ($detectors[0]->getDetectorState() == false
     *			&& $detectors[1]->getDetectorState() == true)
     *		{
     *			#lp-timeout before lp-start -> else-execution
     *			$retval = false;
     *		}
     *
     *		if ($detectors[0]->getDetectorState() == true
     *			&& $detectors[1]->getDetectorState() == false)
     *		{
     *			#lp-start before lp-timeout -> then-execution
     *			$retval = true;
     *		}
     *		return $retval;
     *	';
     * The method gets an array containing the nodes detectors as parameters, named
     * as $detectors.
     *
     * Please note that the method here returns 'tri-state' - true, false, null.
     *
     * Null is used to tell, that no decision is possible due to the state of the
     * detectors. In the above example, if none of both detectors were satisfied,
     * the node should neither transit the 'then'-set nor the 'else'-set.
     *
     * @var string PHP code to be executed to determine the 'decision' of the node.
     */
    public function setEvaluationExpression($a_expression): void
    {
        $this->evaluation_expression = $a_expression;
    }

    /**
     * This method is called by detectors, that just switched to being satisfied.
     *
     * @param ilDetector $detector ilDetector which is now satisfied.
     *
     * @return mixed|void
     */
    public function notifyDetectorSatisfaction(ilDetector $detector): void
    {
        if ($this->isActive()) {
            $this->attemptTransition();
        }
    }

    /**
     * Returns all currently set activites
     * @param bool $else True, if else activities should be returned.
     * @return Array Array with objects of ilActivity
     */
    public function getActivities(bool $else = false): array
    {
        if ($else) {
            return $this->else_activities;
        }

        return $this->activities;
    }

    /**
     * Returns all currently set emitters
     * @param bool $else True, if else emitters should be returned.
     * @return Array Array with objects of ilEmitter
     */
    public function getEmitters(bool $else = false): array
    {
        if ($else) {
            return $this->else_emitters;
        }

        return $this->emitters;
    }
}
