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

namespace ILIAS\Export\HTML;

use ILIAS\components\Export\HTML\ExportException;

class DataService
{
    public function __construct()
    {
    }

    public function exportFile(
        int $object_id,
        string $rid,
        string $timestamp,
        string $type
    ): ExportFile {
        return new ExportFile($object_id, $rid, $timestamp, $type);
    }

    public function exportException(
        string $message
    ): ExportException {
        return new ExportException($message);
    }
}
