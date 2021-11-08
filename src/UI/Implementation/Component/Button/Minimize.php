<?php

namespace ILIAS\UI\Implementation\Component\Button;

use ILIAS\UI\Component as C;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Triggerer;

class Minimize implements C\Button\Minimize
{
    use JavaScriptBindable;
    use ComponentHelper;
    use Triggerer;

    /**
     * @inheritdoc
     */
    public function withOnClick(Signal $signal)
    {
        return $this->withTriggeredSignal($signal, 'click');
    }

    /**
     * @inheritdoc
     */
    public function appendOnClick(Signal $signal)
    {
        return $this->appendTriggeredSignal($signal, 'click');
    }
}
