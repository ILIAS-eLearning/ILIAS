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

namespace ILIAS\COPage\PC;

use ILIAS\COPage\InternalDataService;

/**
 * Page component repos
 * @author Alexander Killing <killing@leifos.de>
 */
class RepoService
{
    protected \ilDBInterface $db;
    protected InternalDataService $data;

    public function __construct(
        InternalDataService $data,
        \ilDBInterface $db
    ) {
        $this->db = $db;
        $this->data = $data;
    }

    public function mediaMap(): MapEditorSessionRepository
    {
        return new MapEditorSessionRepository();
    }
}
