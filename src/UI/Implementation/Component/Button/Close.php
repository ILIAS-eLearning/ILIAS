<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Button;

use ILIAS\Data\URI;
use ILIAS\UI\Component as C;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Triggerer;

class Close implements C\Button\Close
{

    use JavaScriptBindable;
    use ComponentHelper;
    use Triggerer;
    /**
     * @var URI
     */
    protected $close_action;


    /**
     * @inheritdoc
     */
    public function withOnClick(Signal $signal)
    {
        $this->action = null;

        return $this->withTriggeredSignal($signal, 'click');
    }


    /**
     * @inheritdoc
     */
    public function appendOnClick(Signal $signal)
    {
        return $this->appendTriggeredSignal($signal, 'click');
    }


    /**
     * @inheritDoc
     */
    public function withAction(URI $uri) : \ILIAS\UI\Component\Button\Close
    {
        /**
         * @var $clone C\Button\Close
         */
        $clone = clone $this;
        $clone->close_action = $uri;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function getAction() : ?URI
    {
        return $this->close_action;
    }
}
