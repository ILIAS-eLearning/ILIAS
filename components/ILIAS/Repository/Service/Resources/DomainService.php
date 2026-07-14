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

namespace ILIAS\Repository\Resources;

use ILIAS\Filesystem\Util\Archive\Archives;
use ILIAS\Filesystem\Util\Archive\LegacyArchives;

class DomainService
{
    protected LegacyArchives $legacy_archives;
    protected Archives $archives;
    protected \ilFileServicesSettings $file_service_settings;

    public function __construct(
        Archives $archives,
        LegacyArchives $legacy_archives,
        \ilFileServicesSettings $file_service_settings
    ) {
        $this->archives = $archives;
        $this->legacy_archives = $legacy_archives;
        $this->file_service_settings = $file_service_settings;
    }

    public function zip(): ZipAdapter
    {
        return new ZipAdapter(
            $this->archives,
            $this->legacy_archives,
            $this->file_service_settings
        );
    }
}
