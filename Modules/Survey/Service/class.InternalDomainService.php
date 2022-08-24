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

namespace ILIAS\Survey;

use ILIAS\Survey\Mode\FeatureConfig;
use ILIAS\Survey\Mode\ModeFactory;
use ILIAS\Survey\Code\CodeManager;
use ILIAS\Repository\GlobalDICDomainServices;
use ILIAS\Survey\Editing\EditManager;

/**
 * Survey internal domain service
 * @author Alexander Killing <killing@leifos.de>
 */
class InternalDomainService
{
    use GlobalDICDomainServices;

    protected ModeFactory $mode_factory;
    protected InternalRepoService $repo_service;
    protected InternalDataService $data_service;

    public function __construct(
        ModeFactory $mode_factory,
        InternalRepoService $repo_service,
        InternalDataService $data_service
    ) {
        global $DIC;

        $this->initDomainServices($DIC);
        $this->repo_tree = $DIC->repositoryTree();
        $this->access = $DIC->access();
        $this->lng = $DIC->language();

        $this->repo_service = $repo_service;
        $this->data_service = $data_service;

        $this->mode_factory = $mode_factory;
    }

    public function modeFeatureConfig(int $mode): FeatureConfig
    {
        $mode_provider = $this->mode_factory->getModeById($mode);
        return $mode_provider->getFeatureConfig();
    }

    public function participants(): Participants\DomainService
    {
        return new Participants\DomainService(
            $this,
            $this->repo_service
        );
    }

    public function execution(): Execution\DomainService
    {
        return new Execution\DomainService(
            $this->repo_service,
            $this
        );
    }

    public function access(int $ref_id, int $user_id): Access\AccessManager
    {
        return new Access\AccessManager(
            $this,
            $this->access,
            $ref_id,
            $user_id
        );
    }

    public function code(\ilObjSurvey $survey, int $user_id): CodeManager
    {
        return new CodeManager(
            $this->repo_service->code(),
            $this->data_service,
            $survey,
            $this,
            $user_id
        );
    }

    public function evaluation(
        \ilObjSurvey $survey,
        int $user_id,
        int $requested_appr_id = 0,
        string $requested_rater_id = ""
    ): Evaluation\EvaluationManager {
        return new Evaluation\EvaluationManager(
            $this,
            $this->repo_service,
            $survey,
            $user_id,
            $requested_appr_id,
            $requested_rater_id
        );
    }

    public function edit(): EditManager
    {
        return new EditManager(
            $this->repo_service,
            $this
        );
    }
}
