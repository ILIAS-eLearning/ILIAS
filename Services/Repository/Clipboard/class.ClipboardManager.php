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

namespace ILIAS\Repository\Clipboard;

/**
 * Manages items in repository clipboard
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ClipboardManager
{
    protected ClipboardSessionRepository $repo;

    public function __construct(ClipboardSessionRepository $repo)
    {
        $this->repo = $repo;
    }

    public function setCmd(string $cmd) : void
    {
        $this->repo->setCmd($cmd);
    }

    public function getCmd() : string
    {
        return $this->repo->getCmd();
    }

    public function setParent(int $parent) : void
    {
        $this->repo->setParent($parent);
    }

    public function getParent() : int
    {
        return $this->repo->getParent();
    }

    public function setRefIds(array $ref_ids) : void
    {
        $this->repo->setRefIds($ref_ids);
    }

    public function getRefIds() : array
    {
        return $this->repo->getRefIds();
    }

    public function hasEntries() : bool
    {
        return $this->repo->hasEntries();
    }

    public function clear() : void
    {
        $this->repo->clear();
    }
}
