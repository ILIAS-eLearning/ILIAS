<?php

declare(strict_types=1);

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Link;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;

class Bulky extends Link implements C\Link\Bulky
{

    use JavaScriptBindable;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var Symbol
     */
    protected $symbol;

    public function __construct(C\Symbol\Symbol $symbol, string $label, \ILIAS\Data\URI $target)
    {
        $action = $target->getBaseURI();
        if ($target->getQuery()) {
            $action .= '?' . $target->getQuery();
        }
        parent::__construct($action);
        $this->label = $label;
        $this->symbol = $symbol;
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
    public function getSymbol() : C\Symbol\Symbol
    {
        return $this->symbol;
    }
}
