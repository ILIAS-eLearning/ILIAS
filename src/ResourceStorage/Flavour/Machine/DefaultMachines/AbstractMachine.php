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

namespace ILIAS\ResourceStorage\Flavour\Machine\DefaultMachines;

use ILIAS\ResourceStorage\Flavour\Engine\Engine;
use ILIAS\ResourceStorage\Flavour\Machine\FlavourMachine;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
abstract class AbstractMachine implements FlavourMachine
{
    protected ?Engine $engine = null;

    public function __construct()
    {
    }

    public function withEngine(Engine $engine): FlavourMachine
    {
        $clone = clone $this;
        $clone->engine = $engine;
        return $clone;
    }

    public function getEngine(): Engine
    {
        if ($this->engine === null) {
            throw new \OutOfBoundsException("Engine not available");
        }
        return $this->engine;
    }
}
