<?php declare(strict_types=1);

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Listing\Workflow;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component\Signal;

/**
 * Class Step
 * @package ILIAS\UI\Implementation\Component\Listing\Workflow
 */
class Step implements C\Listing\Workflow\Step
{
    use ComponentHelper;

    private string $label;
    private string $description;

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

    public function __construct(string $label, string $description = '', $action = null)
    {
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
    public function getLabel() : string
    {
        return $this->label;
    }

    /**
     * @inheritdoc
     */
    public function getDescription() : string
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
    public function withAvailability($status) : C\Listing\Workflow\Step
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
    public function withStatus($status) : C\Listing\Workflow\Step
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
