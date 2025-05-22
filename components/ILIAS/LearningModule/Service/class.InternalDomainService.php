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
use ilLMTree;
use ILIAS\LearningModule\Table\SubObjectRetrieval;

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
    ): SubObjectRetrieval {
        return self::$instance["sub_obj_retrieval"][$lm_id][$type][$current_node] ??=
            new SubObjectRetrieval(
                $this->lmTree($lm_id),
                $type,
                $current_node,
                $lang
            );
    }

}
