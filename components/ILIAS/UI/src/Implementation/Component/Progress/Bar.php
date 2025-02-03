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

namespace ILIAS\UI\Implementation\Component\Progress;

use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\Triggerer;
use ILIAS\UI\Component\Progress;
use ILIAS\UI\Component\Signal;
use ILIAS\Data\URI;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class Bar implements Progress\Bar
{
    use JavaScriptBindable;
    use ComponentHelper;
    use Triggerer;

    public const MAX_VALUE = 100;

    protected Signal $update_signal;
    protected Signal $reset_signal;

    public function __construct(
        protected Progress\AsyncRefreshInterval $async_refresh_interval,
        protected SignalGeneratorInterface $signal_generator,
        protected string $label,
        protected ?URI $async_url = null,
    ) {
        $this->update_signal = $this->signal_generator->create();
        $this->reset_signal = $this->signal_generator->create();
    }

    public function withResetSignals()
    {
        $clone = clone $this;
        $clone->update_signal = $this->signal_generator->create();
        $clone->reset_signal = $this->signal_generator->create();
        return $clone;
    }

    public function getUpdateSignal(): Signal
    {
        return $this->update_signal;
    }

    public function getResetSignal(): Signal
    {
        return $this->reset_signal;
    }

    public function getAsyncRefreshInterval(): Progress\AsyncRefreshInterval
    {
        return $this->async_refresh_interval;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getAsyncUrl(): ?URI
    {
        return $this->async_url;
    }
}
