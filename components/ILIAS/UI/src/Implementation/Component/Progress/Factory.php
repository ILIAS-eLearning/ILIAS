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
use ILIAS\UI\Component\Progress as C;
use ILIAS\Data\URI;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class Factory implements C\Factory
{
    public function __construct(
        protected C\AsyncRefreshInterval $async_refresh_interval,
        protected SignalGeneratorInterface $signal_generator,
        protected State\Factory $state_factory,
    ) {
    }

    public function bar(string $label, ?URI $async_url = null): Bar
    {
        return new Bar($this->async_refresh_interval, $this->signal_generator, $label, $async_url);
    }

    public function state(): State\Factory
    {
        return $this->state_factory;
    }
}
