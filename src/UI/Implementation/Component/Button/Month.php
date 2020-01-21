<?php

/* Copyright (c) 2017 Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Button;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;

class Month implements C\Button\Month
{
    use ComponentHelper;
    use JavaScriptBindable;

    /**
     * @var string
     */
    protected $default;

    /**
     * @param string $default Label of the month directly shown as default.
     */
    public function __construct($default)
    {
        $this->default = $default;
    }

    /**
     * @inheritdoc
     */
    public function getDefault()
    {
        return $this->default;
    }
}
