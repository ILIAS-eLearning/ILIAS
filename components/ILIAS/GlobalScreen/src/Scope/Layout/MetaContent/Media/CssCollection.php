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
class CssCollection extends AbstractCollection
{
    public function addItem(Css $item): void
    {
        $content = $item->getContent();

        // Add external URLs to the collection if allowed
        if ($this->allow_external && $this->isExternalURI($content)) {
            $this->items[] = $item;
            return;
        }

        if ($this->isURI($content)) {
            $this->items[] = $item;
            return;
        }

        // add item only if it is not already in the collection
        $real_path = realpath(parse_url($content, PHP_URL_PATH));
        if (!$this->allow_non_existing && $real_path === false) {
            return;
        }
        foreach ($this->getItems() as $css) {
            if (!$this->allow_non_existing && realpath(parse_url((string) $css->getContent(), PHP_URL_PATH)) === $real_path) {
                return;
            }
        }
        $this->items[] = $item;
    }

}
