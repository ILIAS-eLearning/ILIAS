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

namespace ILIAS\Wiki;

use ILIAS\Wiki\Page\PageDBRepository;
use ILIAS\Wiki\Navigation\ImportantPageDBRepository;
use ILIAS\Wiki\Links\MissingPageDBRepository;

/**
 * Wiki repo service
 */
class InternalRepoService
{
    protected InternalDataService $data;
    protected \ilDBInterface $db;

    public function __construct(InternalDataService $data, \ilDBInterface $db)
    {
        $this->data = $data;
        $this->db = $db;
    }

    public function page(): PageDBRepository
    {
        return new PageDBRepository(
            $this->data,
            $this->db
        );
    }

    public function importantPage(): ImportantPageDBRepository
    {
        return new ImportantPageDBRepository(
            $this->data,
            $this->db
        );
    }

    public function missingPage(): MissingPageDBRepository
    {
        return new MissingPageDBRepository(
            $this->data,
            $this->db
        );
    }
}
