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

namespace ILIAS\Export;

use ILIAS\Repository\RepoServiceBase;

class InternalRepoService
{
    use RepoServiceBase;

    protected static array $instance = [];

    public function __construct(
        protected InternalDataService $data,
        protected \ilDBInterface $db
    ) {
    }

    public function html(): HTML\RepoService
    {
        return self::$instance['html'] ??= new HTML\RepoService(
            $this->data->html(),
            $this->db,
            $this->irss()
        );
    }
}
