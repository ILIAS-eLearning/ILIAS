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

namespace ILIAS\Help\GuidedTour;

use ILIAS\DI\Container;
use ILIAS\Repository\GlobalDICDomainServices;
use ILIAS\Help\GuidedTour\Settings\SettingsManager;
use ILIAS\Help\GuidedTour\Tour\TourManager;
use ILIAS\Help\GuidedTour\Step\StepManager;
use ILIAS\Help\GuidedTour\StepRetrieval;
use ILIAS\Help\GuidedTour\Page\PageManager;
use ILIAS\Help\GuidedTour\Admin\AdminManager;
use ILIAS\Help\GuidedTour\Elements\IdPresentation;
use ILIAS\Help\GuidedTour\UserFinished\UserFinishedManager;

class InternalDomainService
{
    use GlobalDICDomainServices;

    protected static array $instance = [];

    public function __construct(
        Container $DIC,
        protected InternalRepoService $repo,
        protected InternalDataService $data
    ) {
        $this->initDomainServices($DIC);
    }

    public function tourSettings(): SettingsManager
    {
        return self::$instance["settings"] ??= new SettingsManager(
            $this->data,
            $this->repo,
            $this
        );
    }

    public function tour(): TourManager
    {
        return self::$instance["tour"] ??= new TourManager(
            $this->data,
            $this
        );
    }

    public function step(): StepManager
    {
        return self::$instance["step"] ??= new StepManager(
            $this->data,
            $this->repo,
            $this
        );
    }

    public function stepRetrieval(int $tour_id): StepRetrieval
    {
        return self::$instance["step_retrieval"][$tour_id] ??= new StepRetrieval(
            $this,
            $tour_id
        );
    }

    public function page(): PageManager
    {
        return self::$instance["page"] ??= new PageManager(
            $this
        );
    }

    public function admin(): AdminManager
    {
        return self::$instance["admin"] ??= new AdminManager(
            $this
        );
    }

    public function idPresentation(): IdPresentation
    {
        return self::$instance["id_pres"] ??= new IdPresentation(
            $this
        );
    }

    public function userFinished(): UserFinishedManager
    {
        return self::$instance["user_finished"] ??= new UserFinishedManager(
            $this->data,
            $this->repo,
            $this
        );
    }

}
