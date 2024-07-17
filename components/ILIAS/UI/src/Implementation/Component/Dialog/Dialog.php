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

declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Dialog;

use ILIAS\UI\Component\Dialog as I;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\SignalGenerator;

use ILIAS\Data\URI;

/**
 *
 */
abstract class Dialog implements I\Dialog
{
    use ComponentHelper;
    use JavaScriptBindable;

    protected Signal $show_signal;
    protected Signal $close_signal;

    public function __construct(
        SignalGenerator $signal_generator,
        protected ?URI $async_url
    ) {
        $this->show_signal = $signal_generator->create();
        $this->close_signal = $signal_generator->create();
    }

    public function getAsyncUrl(): ?URI
    {
        return $this->async_url;
    }

    public function getShowSignal(?URI $uri = null): Signal
    {
        $target = $uri ?? $this->async_url;
        $signal = clone $this->show_signal;
        $signal->addOption('url', $target ? $target->__toString() : '#');
        return $signal;
    }

    public function getCloseSignal(): Signal
    {
        return $this->close_signal;
    }
}
