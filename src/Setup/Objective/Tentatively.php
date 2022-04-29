<?php declare(strict_types=1);

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
 
namespace ILIAS\Setup\Objective;

use ILIAS\Setup;

/**
 * A wrapper around an objective that attempts to achieve the wrapped
 * objective but won't stop the process on failure.
 */
class Tentatively implements Setup\Objective
{
    protected Setup\Objective $other;

    public function __construct(Setup\Objective $other)
    {
        $this->other = $other;
    }

    public function getHash() : string
    {
        if ($this->other instanceof Tentatively) {
            return $this->other->getHash();
        }
        return "tentatively " . $this->other->getHash();
    }

    public function getLabel() : string
    {
        if ($this->other instanceof Tentatively) {
            return $this->other->getLabel();
        }
        return "Tentatively: " . $this->other->getLabel();
    }

    public function isNotable() : bool
    {
        return $this->other->isNotable();
    }

    /*
     * @inheritdocs
     */
    public function getPreconditions(Setup\Environment $environment) : array
    {
        if ($this->other instanceof Tentatively) {
            return $this->other->getPreconditions($environment);
        }
        return array_map(
            function ($p) : \ILIAS\Setup\Objective\Tentatively {
                if ($p instanceof Tentatively) {
                    return $p;
                }
                return new Tentatively($p);
            },
            $this->other->getPreconditions($environment)
        );
    }

    /**
     * @inheritdocs
     */
    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        try {
            return $this->other->achieve($environment);
        } catch (Setup\UnachievableException $e) {
        }
        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment) : bool
    {
        return $this->other->isApplicable($environment);
    }
}
