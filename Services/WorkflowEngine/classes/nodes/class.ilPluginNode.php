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
 * Plugin node of the petri net based workflow engine.
 *
 * The plugin node is a deciding node. It features a multiple set of emitters
 * and activities.
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @ingroup Services/WorkflowEngine
 */
class ilPluginNode extends ilBaseNode
{
    /**
     * This holds a list of emitters attached to the node.
     * In this node type, these are the 'else' emitters.
     * @var ilEmitter[] Array of ilEmitter
     */
    private array $else_emitters = [];

    /**
     * This holds a list of activities attached to the node.
     * In this node type, these are the 'else' activities.
     * @var ilActivity[] Array of ilActivity
     */
    private array $else_activities = [];

    /**
     * This holds the piece of code used to determine if the 'then' or the 'else'
     * sets of activities and emitters are to be used.
     *
     * @var string PHP code to be executed to determine the 'decision' of the node.
     */
    private string $evaluation_expression = "return null;";

    /**
     * Default constructor.
     *
     * @param ilWorkflow $context Reference to the parent workflow.
     */
    public function __construct(ilWorkflow $context)
    {
        $this->context = $context;
        $this->detectors = [];
        $this->emitters = [];
        $this->else_emitters = [];
        $this->activities = [];
        $this->else_activities = [];
    }

    /**
     * Activates the node.
     */
    public function activate(): void
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
    public function deactivate(): void
    {
        $this->active = false;
        foreach ($this->detectors as $detector) {
            $detector->onDeactivate();
        }
        $this->onDeactivate();
    }

    /**
     * Passes a trigger to attached detectors.
     */
    public function trigger(string $a_type, ?array $a_params = null): void
    {
        if ($this->active === true && count($this->detectors) !== 0) {
            foreach ($this->detectors as $detector) {
                if (get_class($detector) === $a_type) {
                    $detector->trigger($a_params);
                }
            }
        }
        $this->attemptTransition();
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
        // TODO Call Plugin here.
        $eval_function = function ($detectors) {
            return eval($this->evaluation_expression);
        };

        if ($eval_function($this->detectors) === null) {
            return false;
        }

        return true;
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
        // TODO Call Plugin here.
        $eval_function = function ($detectors) {
            return eval($this->evaluation_expression);
        };

        if ($eval_function($this->detectors) === null) {
            return false;
        }

        if ($eval_function($this->detectors) === true) {
            $this->executeTransition();
            return true;
        } elseif (method_exists($this, 'executeElseTransition')) {
            $this->executeElseTransition();
            return true;
        }
        return false;
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
     * Executes the 'then'-transition of the node.
     */
    public function executeTransition()
    {
        $this->deactivate();
        $this->executeActivities();
        $this->executeEmitters();
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
    public function setEvaluationExpression(string $a_expression): void
    {
        // TODO Rework to use a Plugin here.
        $this->evaluation_expression = $a_expression;
    }

    /**
     * This method is called by detectors, that just switched to being satisfied.
     *
     * @param ilDetector $detector ilDetector which is now satisfied.
     *
     * @return
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
