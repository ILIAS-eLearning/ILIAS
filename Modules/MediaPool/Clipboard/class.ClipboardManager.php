<?php

declare(strict_types=1);

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

namespace ILIAS\MediaPool\Clipboard;

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

    public function setFolder(int $fold_id): void
    {
        $this->repo->setFolder($fold_id);
    }

    public function getFolder(): int
    {
        return $this->repo->getFolder();
    }

    public function setIds(array $ids): void
    {
        $this->repo->setIds($ids);
    }

    public function getIds(): array
    {
        return $this->repo->getIds();
    }
}
