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
use ILIAS\UI\Component\Input\Field\Node\NodeRetrieval;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Input\Field\Node\Factory as NodeFactory;
use ILIAS\UI\Component\Symbol\Icon\Factory as IconFactory;

/**
 * Picker class for LSO objects.
 * This class is NOT a GUI class and does NOT participate in ilCtrl flow.
 */
class LSOObjectPicker
{
    protected UIFactory $ui_factory;

    public function __construct(
        protected int $lso_ref_id,
        protected int $current_item_ref_id,
    ) {
        global $DIC;
        $this->ui_factory = $DIC->ui()->factory();
    }

    public function getPicker(string $label, bool $multi): FormInput
    {
        $lso = \ilObjLearningSequence::getInstanceByRefId($this->lso_ref_id);
        $lso_items = $lso->getLSItems();
        $filtered_lso_items = array_filter(
            $lso_items,
            fn($item) => $item->getRefId() !== $this->current_item_ref_id
        );

        $retrieval = new class ($filtered_lso_items) implements NodeRetrieval {
            protected array $items;

            public function __construct(array $items)
            {
                $this->items = $items;
            }

            public function getNodes(
                NodeFactory $node_factory,
                IconFactory $icon_factory,
                array $sync_node_id_whitelist = [],
                ?string $parent_id = null,
            ): \Generator {
                if ($parent_id !== null) {
                    return;
                }

                foreach ($this->items as $item) {
                    $ref_id = (string) $item->getRefId();
                    $obj_id = \ilObject::_lookupObjId((int) $ref_id);
                    $title = \ilObject::_lookupTitle($obj_id);
                    $type = \ilObject::_lookupType($obj_id);
                    $icon = $icon_factory->standard($type, $title);
                    yield $node_factory->leaf(
                        [$ref_id],
                        $title,
                        $icon
                    );
                }
            }

            public function getNodesAsLeaf(
                NodeFactory $node_factory,
                IconFactory $icon_factory,
                array $node_ids,
            ): \Generator {
                foreach ($node_ids as $node_id) {
                    $ref_id = (string) $node_id;
                    $obj_id = \ilObject::_lookupObjId((int) $ref_id);
                    $title = ($obj_id > 0)
                        ? \ilObject::_lookupTitle($obj_id)
                        : sprintf('Ref-ID %s', $ref_id);
                    $type = ($obj_id > 0)
                        ? \ilObject::_lookupType($obj_id)
                        : '';

                    if ($type !== '') {
                        $icon = $icon_factory->standard($type, $title);
                        yield $node_factory->leaf([$ref_id], $title, $icon);
                        continue;
                    }

                    yield $node_factory->leaf([$ref_id], $title);
                }
            }
        };

        if ($multi) {
            return $this->ui_factory->input()->field()->treeMultiSelect($retrieval, $label)
                ->withSelectChildNodes(false)->withRequired(true);
        }

        return $this->ui_factory->input()->field()->treeSelect($retrieval, $label)->withRequired(true);
    }
}
