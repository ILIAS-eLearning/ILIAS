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

namespace ILIAS\components\Export\HTML;

use ILIAS\Export\HTML\ExportFileDBRepository;
use ILIAS\Export\HTML\DataService;
use ILIAS\Export\HTML\ExportFile;

class ExportFileManager
{

    public function __construct(
        protected DataService $data,
        protected ExportFileDBRepository $repo
    )
    {
    }

    public function getLatestOfObjectIdAndType(int $object_id, string $type = ""): ?ExportFile
    {
        return $this->repo->getLatestOfObjectIdAndType($object_id, $type);
    }

    public function deliver(ExportFile $file) : void
    {
        $this->repo->deliverFile($file->getRid());
    }
}
