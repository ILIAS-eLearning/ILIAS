<?php
/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Listing\Workflow;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Class Workflow
 * @package ILIAS\UI\Implementation\Component\Listing\Workflow
 */
abstract class Workflow implements C\Listing\Workflow\Workflow
{
    use ComponentHelper;

    /**
     * @var	string
     */
    private $title;

    /**
     * @var	array
     */
    private $steps;

    /**
     * @var	int
     */
    private $active;

    /**
     * Workflow constructor.
     * @param 	string 	$title
     * @param 	Step[] 	$steps
     */
    public function __construct($title, array $steps)
    {
        $this->checkStringArg("string", $title);
        $types = array('string',Step::class);
        $this->checkArgListElements("steps", $steps, $types);
        $this->title = $title;
        $this->steps = $steps;
        $this->active = 0;
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @inheritdoc
     */
    public function withActive($active)
    {
        $this->checkIntArg("int", $active);
        if ($active < 0 || $active > $this->getAmountOfSteps() - 1) {
            throw new \InvalidArgumentException("active must be be within the amount of steps", 1);
        }
        $clone = clone $this;
        $clone->active = $active;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Return the amount of steps of this workflow.
     * @return int
     */
    public function getAmountOfSteps()
    {
        return count($this->steps);
    }

    /**
     * @inheritdoc
     */
    public function getSteps()
    {
        return $this->steps;
    }
}
