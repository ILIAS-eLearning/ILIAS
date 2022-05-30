<?php declare(strict_types = 1);

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

namespace ILIAS\Survey;

use ILIAS\Survey\Execution;
use ILIAS\Survey\Editing;
use ILIAS\Survey\Evaluation;

/**
 * Survey internal data service
 * @author Alexander Killing <killing@leifos.de>
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

    public function execution() : Execution\RepoService
    {
        return new Execution\RepoService(
            $this->data,
            $this->db
        );
    }

    public function participants() : Participants\RepoService
    {
        return new Participants\RepoService(
            $this->data,
            $this->db
        );
    }

    public function code() : Code\CodeDBRepo
    {
        return new Code\CodeDBRepo(
            $this->data,
            $this->db
        );
    }

    public function settings() : Settings\SettingsDBRepository
    {
        return new Settings\SettingsDBRepository(
            $this->data,
            $this->db
        );
    }

    public function edit() : Editing\EditSessionRepo
    {
        return new Editing\EditSessionRepo();
    }

    public function evaluation() : Evaluation\EvaluationSessionRepo
    {
        return new Evaluation\EvaluationSessionRepo();
    }
}
