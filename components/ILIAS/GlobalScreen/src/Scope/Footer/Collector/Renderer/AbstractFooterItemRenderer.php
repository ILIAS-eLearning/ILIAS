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

namespace ILIAS\GlobalScreen\Scope\Footer\Collector\Renderer;

use ILIAS\Data\URI;
use ILIAS\DI\UIServices;
use ILIAS\GlobalScreen\Collector\Renderer\DecoratorApplierTrait;
use ILIAS\GlobalScreen\Collector\Renderer\isSupportedTrait;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\HasHelpTopics;
use ILIAS\GlobalScreen\Scope\Footer\Factory\isItem;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
abstract class AbstractFooterItemRenderer implements FooterItemRenderer
{
    use DecoratorApplierTrait;
    use isSupportedTrait;

    public function __construct(protected UIServices $ui)
    {
    }

    protected function getURI(string $uri_string): URI
    {
        if (str_starts_with($uri_string, 'http')) {
            return new URI($uri_string);
        }

        return new URI(rtrim(ILIAS_HTTP_PATH, "/") . "/" . ltrim($uri_string, "./"));
    }

    public function getComponentForItem(isItem $item): Component|array
    {
        $component = $this->getSpecificComponentForItem($item);
        if ($component instanceof Component) {
            $component = $this->applyComponentDecorator($component, $item);
            if ($component instanceof HasHelpTopics) {
                return $component->withHelpTopics(...$item->getTopics());
            }
        }

        // decoratory are applied to array elemts in specific rendereres

        return $component;
    }

    abstract protected function getSpecificComponentForItem(isItem $item): Component|array;
}
