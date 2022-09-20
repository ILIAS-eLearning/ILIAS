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
 ********************************************************************
 */

/**
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*/
class ilFSStorageSession extends ilFileSystemAbstractionStorage
{
    public function __construct(int $a_event_id = 0)
    {
        parent::__construct(ilFileSystemAbstractionStorage::STORAGE_DATA, true, $a_event_id);
    }

    public function createDirectory(): bool
    {
        return ilFileUtils::makeDirParents($this->getAbsolutePath());
    }

    protected function getPathPostfix(): string
    {
        return 'sess';
    }

    protected function getPathPrefix(): string
    {
        return 'ilSession';
    }
}
