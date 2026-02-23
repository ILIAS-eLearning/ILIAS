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

use ILIAS\Export\ExportHandler\Consumer\ExportConfig\BasicHandler as ExportConfig;

class ilUserExportConfig extends ExportConfig
{
    protected string $export_type;

    public function __construct()
    {
        $this->export_type = '';
    }

    public function setExportType(string $export_type)
    {
        $this->export_type = $export_type;
    }

    public function getExportType(): string
    {
        return $this->export_type;
    }
}
