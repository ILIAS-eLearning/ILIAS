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

namespace ILIAS\Container\Content;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ItemManager
{
    protected \ilContainer $container;
    protected ItemSessionRepository $item_repo;

    public function __construct(
        \ilContainer $container,
        ItemSessionRepository $item_repo
    ) {
        $this->item_repo = $item_repo;
        $this->container = $container;
    }

    public function setExpanded(int $id, int $val) : void
    {
        $this->item_repo->setExpanded($id, $val);
    }

    public function getExpanded(int $id) : ?int
    {
        return $this->item_repo->getExpanded($id);
    }
}
