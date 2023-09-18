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

namespace ILIAS\ResourceStorage\Flavour\Engine;

use ILIAS\ResourceStorage\Flavour\Machine\FlavourMachine;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Factory
{
    private array $engines_strings = [];
    private array $engines = [];

    public function __construct(array $additional_engines = [])
    {
        $this->engines_strings = array_merge($additional_engines, [
            ImagickEngine::class,
            GDEngine::class,
            NoEngine::class
        ]);
    }

    public function get(FlavourMachine $machine): ?Engine
    {
        $depends_on = $machine->dependsOnEngine();
        if (!in_array($depends_on, $this->engines_strings)) {
            return null;
        }
        if (isset($this->engines[$depends_on])) {
            return $this->engines[$depends_on];
        }

        try {
            $engine = $this->engines[$depends_on] = new $depends_on();
        } catch (\Throwable $t) {
            return null;
        }
        return $engine;
    }
}
