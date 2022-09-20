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
