<?php declare(strict_types=1);

/* Copyright (c) 2017 Alexander Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Link;

use ILIAS\UI\Component as C;

class Standard extends Link implements C\Link\Standard
{
    protected string $label;

    public function __construct(string $label, string $action)
    {
        parent::__construct($action);
        $this->label = $label;
    }

    /**
     * @inheritdoc
     */
    public function getLabel() : string
    {
        return $this->label;
    }
}
