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

namespace ILIAS\Modules\EmployeeTalk\Talk\Repository;

use ilObjTalkTemplate;
use ilTree;
use ilObjTalkTemplateAdministration;

final class IliasDBTalkTemplateRepository
{
    private ilTree $tree;

    /**
     * IliasDBTalkTemplateRepository constructor.
     * @param ilTree $tree
     */
    public function __construct(ilTree $tree)
    {
        $this->tree = $tree;
    }

    /**
     * @return ilObjTalkTemplate[]
     */
    public function findAll(): array
    {
        $rawTemplates = $this->tree->getChildsByType(ilObjTalkTemplateAdministration::getRootRefId(), ilObjTalkTemplate::TYPE);
        $templates = array_map(function (array $template) {
            return new ilObjTalkTemplate($template['ref_id']);
        }, $rawTemplates);
        return $templates;
    }
}
