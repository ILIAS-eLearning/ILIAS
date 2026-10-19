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

namespace ILIAS\LearningSequence\Content\Condition;

use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\UI\Factory as UIFactory;

/**
 * Picker class for LSO objects.
 * This class is NOT a GUI class and does NOT participate in ilCtrl flow.
 */
class LSOObjectPicker
{
    public function __construct(
        protected UIFactory $ui_factory,
        protected int $lso_ref_id,
        protected int $current_item_ref_id,
    ) {
    }

    public function getPicker(string $label, bool $multi): FormInput
    {
        $lso = \ilObjLearningSequence::getInstanceByRefId($this->lso_ref_id);
        $lso_items = $lso->getLSItems();
        $filtered_lso_items = array_filter(
            $lso_items,
            fn($item) => $item->getRefId() !== $this->current_item_ref_id
        );

        $retrieval = new LSOItemNodeRetrieval($filtered_lso_items);

        if ($multi) {
            return $this->ui_factory->input()->field()->treeMultiSelect($retrieval, $label)
                ->withSelectChildNodes(false)->withRequired(true);
        }

        return $this->ui_factory->input()->field()->treeSelect($retrieval, $label)->withRequired(true);
    }
}
