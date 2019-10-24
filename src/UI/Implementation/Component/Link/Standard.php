<?php

/* Copyright (c) 2017 Alexander Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Link;

use ILIAS\UI\Component as C;

class Standard extends Link implements C\Link\Standard
{

    /**
     * @var string
     */
    protected $label;

    /**
     * Standard constructor.
     * @param string $label
     * @param string $action
     */
    public function __construct($label, $action)
    {
        parent::__construct($action);
        $this->checkStringArg("label", $label);
        $this->label = $label;
    }

    /**
     * @inheritdoc
     */
    public function getLabel()
    {
        return $this->label;
    }
}
