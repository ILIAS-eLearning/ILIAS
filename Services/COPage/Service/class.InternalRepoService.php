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

namespace ILIAS\COPage;

use ILIAS\COPage\History\HistoryDBRepository;
use ILIAS\COPage\Usage\UsageDBRepository;

/**
 * Repository internal repo service
 * @author Alexander Killing <killing@leifos.de>
 */
class InternalRepoService
{
    protected InternalDataService $data;
    protected \ilDBInterface $db;
    protected Editor\RepoService $edit_repo;

    public function __construct(InternalDataService $data, \ilDBInterface $db)
    {
        $this->data = $data;
        $this->db = $db;
        $this->edit_repo = new Editor\RepoService(
            $this->data,
            $this->db
        );
    }

    public function edit(): Editor\EditSessionRepository
    {
        return $this->edit_repo->edit();
    }

    public function pc(): PC\RepoService
    {
        return new PC\RepoService(
            $this->data,
            $this->db
        );
    }

    public function history(): HistoryDBRepository
    {
        return new HistoryDBRepository($this->db);
    }

    public function usage(): UsageDBRepository
    {
        return new UsageDBRepository($this->db);
    }
}
