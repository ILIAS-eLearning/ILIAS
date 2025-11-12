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

namespace ILIAS\LearningModule\Media;

use Generator;
use ilCtrl;
use ilLMPageObject;
use ilLMPageObjectGUI;
use ILIAS\MediaObjects\OverviewGUI\SubObjectRetrieval;

class PageRetrieval implements SubObjectRetrieval
{
    public function __construct(
        protected int $obj_id,
        protected ilCtrl $ctrl
    ) {
    }

    /**
     * @return string[]
     */
    public function getPossibleTypes(): Generator
    {
        yield 'lm:pg';
    }

    /**
     * @return int[]
     */
    public function getAllIDsForType(string $type): Generator
    {
        if ($type !== 'lm:pg') {
            return;
        }
        foreach (ilLMPageObject::getPageList($this->obj_id) as $page) {
            yield (int) $page['obj_id'];
        }
    }

    public function getLinkToSubObject(string $type, int $id): string
    {
        if ($type !== 'lm:pg') {
            return '';
        }
        $this->ctrl->setParameterByClass(ilLMPageObjectGUI::class, 'obj_id', $id);
        $link = $this->ctrl->getLinkTargetByClass(ilLMPageObjectGUI::class, 'edit');
        $this->ctrl->clearParameterByClass(ilLMPageObjectGUI::class, 'obj_id');
        return $link;
    }

    public function getTitleOfSubObject(string $type, int $id): string
    {
        if ($type !== 'lm:pg') {
            return '';
        }
        return ilLMPageObject::_lookupTitle($id);
    }
}
