<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Listing\Workflow;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Class Step
 * @package ILIAS\UI\Implementation\Component\Listing\Workflow
 */
class Step implements C\Listing\Workflow\Step
{
    use ComponentHelper;

    /**
     * @var	string
     */
    private $label;

    /**
     * @var	string
     */
    private $description;

    /**
     * @var	mixed
     */
    private $action;

    /**
     * @var	mixed
     */
    private $availability;

    /**
     * @var	mixed
     */
    private $status;

    /**
     * @param string 	$label
     * @param string 	$description
     */
    public function __construct($label, $description='', $action=null)
    {
        $this->checkStringArg("string", $label);
        $this->checkStringArg("string", $description);
        $this->checkArg(
            "action",
            is_null($action) || is_string($action) || $action instanceof Signal,
            $this->wrongTypeMessage("string or Signal", gettype($action))
        );

        $this->label = $label;
        $this->description = $description;
        $this->action = $action;
        $this->availability = static::AVAILABLE;
        $this->status = static::NOT_STARTED;
    }

    /**
     * @inheritdoc
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @inheritdoc
     */
    public function getAvailability()
    {
        return $this->availability;
    }

    /**
     * @inheritdoc
     */
    public function withAvailability($status)
    {
        $valid = [
            static::AVAILABLE,
            static::NOT_AVAILABLE,
            static::NOT_ANYMORE
        ];
        $this->checkArgIsElement('status', $status, $valid, 'valid status for availability');

        $clone = clone $this;
        $clone->availability = $status;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @inheritdoc
     */
    public function withStatus($status)
    {
        $valid = [
            static::NOT_STARTED,
            static::IN_PROGRESS,
            static::SUCCESSFULLY,
            static::UNSUCCESSFULLY
        ];
        $this->checkArgIsElement('status', $status, $valid, 'valid status');

        $clone = clone $this;
        $clone->status = $status;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getAction()
    {
        return $this->action;
    }
}
