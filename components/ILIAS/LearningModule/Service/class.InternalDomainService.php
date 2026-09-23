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

namespace ILIAS\LearningModule;

use ILIAS\DI\Container;
use ILIAS\Repository\GlobalDICDomainServices;
use ILIAS\ILIASObject\Properties\Translations\CachedRepository;
use ILIAS\ILIASObject\Properties\Translations\Translations;

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

    public function lmTree(int $lm_id): \ilLMTree
    {
        return self::$instance["tree"][$lm_id] ??= new \ilLMTree($lm_id);
    }

    public function subObjectRetrieval(
        int $lm_id,
        string $type,
        int $current_node,
        string $lang
    ): Editing\SubObjectRetrieval {
        return self::$instance["sub_obj_retrieval"][$lm_id][$type][$current_node] ??=
            new Editing\SubObjectRetrieval(
                $this->lmTree($lm_id),
                $type,
                $current_node,
                $lang
            );
    }

    public function glossariesRetrieval(
        \ilObjLearningModule $lm
    ): Editing\GlossariesRetrieval {
        return new Editing\GlossariesRetrieval($lm);
    }

    public function pagesRetrieval(
        int $lm_id,
        string $lm_type,
        bool $layout_per_page
    ): Editing\PagesRetrieval {
        return new Editing\PagesRetrieval($lm_id, $lm_type, $layout_per_page);
    }

    public function blockedUsersRetrieval(int $ref_id): Question\BlockedUsers\Retrieval
    {
        return new Question\BlockedUsers\Retrieval($ref_id);
    }

    public function questionStatisticsRetrieval(int $lm_id): Question\Statistics\Retrieval
    {
        return new Question\Statistics\Retrieval($lm_id, $this->DIC->testQuestion());
    }

    public function translation(int $lm_id): Translations
    {
        return (new CachedRepository($this->database()))->getFor($lm_id);
    }

    public function exportIdsRetrieval(int $lm_id): Editing\ExportIds\Retrieval
    {
        return new Editing\ExportIds\Retrieval($lm_id);
    }

    public function shortTitlesRetrieval(
        int $lm_id,
        string $lang
    ): Editing\ShortTitles\Retrieval {
        return new Editing\ShortTitles\Retrieval($lm_id, $lang);
    }

    public function linksRetrieval(
        int $lm_id,
        string $lm_type
    ): Links\Retrieval {
        return new Links\Retrieval(
            $lm_id,
            $lm_type,
            $this->DIC->ctrl(),
            $this->lng()
        );
    }

    public function helpTooltipRetrieval(
        string $component
    ): HelpTooltip\Retrieval {
        return new HelpTooltip\Retrieval(
            $this->DIC->help()->internal()->domain()->tooltips(),
            $component
        );
    }
}
