<?php declare(strict_types = 1);

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

namespace ILIAS\Block;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class BlockManager
{
    protected BlockSessionRepository $repo;

    public function __construct(BlockSessionRepository $repo)
    {
        $this->repo = $repo;
    }

    public function setNavPar(
        string $par,
        string $val
    ) : void {
        $this->repo->setNavPar($par, $val);
    }

    public function getNavPar(
        string $par
    ) : string {
        return $this->repo->getNavPar($par);
    }
}
