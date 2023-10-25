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

namespace ILIAS\Help;

use ILIAS\Help\Map\MapDBRepository;
use ILIAS\Help\Tooltips\TooltipsDBRepository;
use ILIAS\Help\Module\ModuleDBRepository;

class InternalRepoService
{
    protected InternalDataService $data;
    protected \ilDBInterface $db;

    public function __construct(InternalDataService $data, \ilDBInterface $db)
    {
        $this->data = $data;
        $this->db = $db;
    }

    public function map(): MapDBRepository
    {
        return new MapDBRepository($this->db);
    }

    public function tooltips(): TooltipsDBRepository
    {
        return new TooltipsDBRepository($this->db);
    }

    public function module(): ModuleDBRepository
    {
        return new ModuleDBRepository($this->db);
    }

}
