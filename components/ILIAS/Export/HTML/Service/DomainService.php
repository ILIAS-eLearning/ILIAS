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

use ILIAS\Repository\GlobalDICDomainServices;
use ILIAS\Export\HTML\RepoService;
use ILIAS\Export\HTML\DataService;
use ILIAS\Export\InternalDomainService;
use ILIAS\components\Export\HTML\ExportCollector;

class DomainService
{
    protected static array $instance = [];

    public function __construct(
        protected DataService $data,
        protected RepoService $repo,
        protected InternalDomainService $domain
    ) {
    }

    public function collector(
        int $obj_id,
        string $type = ""
    ): ExportCollector {
        return new ExportCollector(
            $this->data,
            $this->repo->exportFile(),
            $obj_id,
            $type
        );
    }
}
