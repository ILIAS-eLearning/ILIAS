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

namespace ILIAS\GlobalScreen\Scope\Layout\MetaContent\Media;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class JsCollection extends AbstractCollection
{
    protected array $path_storage = [];


    public function addItem(Js $item): void
    {
        $basename = $this->stripPath($item->getContent());
        if (!array_key_exists($basename, $this->items)) {
            $this->storeItem($item);
        } else {
            $existing = $this->items[$basename];
            if (($existing instanceof Js) && $existing->getBatch() > $item->getBatch()) {
                $this->storeItem($item);
            }
        }
    }

    private function storeItem(
        js $item
    ): void {
        $strip_path = $this->stripPath($item->getContent());
        $this->items[$strip_path] = $item;
        $this->path_storage[$strip_path] = $item->getBatch();
    }

    #[\Override]
    public function getItemsInOrderOfDelivery(): array
    {
        $ordered = [];
        foreach ($this->getItems() as $js) {
            $ordered['pos_' . (string) $js->getBatch()][] = $js;
        }
        ksort($ordered);
        $ordered_all = [];
        foreach ($ordered as $item) {
            foreach ($item as $js) {
                $ordered_all[] = $js;
            }
        }

        return $ordered_all;
    }
}
