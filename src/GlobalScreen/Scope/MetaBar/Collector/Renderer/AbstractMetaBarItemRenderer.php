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

namespace ILIAS\GlobalScreen\Scope\MetaBar\Collector\Renderer;

use ILIAS\Data\URI;
use ILIAS\DI\UIServices;
use ILIAS\GlobalScreen\Collector\Renderer\ComponentDecoratorApplierTrait;
use ILIAS\GlobalScreen\Collector\Renderer\isSupportedTrait;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\hasSymbol;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\hasTitle;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\isItem;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Symbol\Symbol;

/**
 * Class AbstractMetaBarItemRenderer
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractMetaBarItemRenderer implements MetaBarItemRenderer
{
    use ComponentDecoratorApplierTrait;
    use isSupportedTrait;

    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    /**
     * BaseMetaBarItemRenderer constructor.
     */
    public function __construct()
    {
        global $DIC;
        $this->ui = $DIC->ui();
    }

    /**
     * @param string $uri_string
     * @return URI
     */
    protected function getURI(string $uri_string) : URI
    {
        if (strpos($uri_string, 'http') === 0) {
            return new URI($uri_string);
        }

        return new URI(rtrim(ILIAS_HTTP_PATH, "/") . "/" . ltrim($uri_string, "./"));
    }

    /**
     * @inheritDoc
     */
    public function getComponentForItem(isItem $item) : Component
    {
        $component = $this->getSpecificComponentForItem($item);

        return $this->applyDecorator($component, $item);
    }

    abstract protected function getSpecificComponentForItem(isItem $item) : Component;

    /**
     * @param isItem $item
     * @return Symbol
     */
    protected function buildIcon(isItem $item) : Symbol
    {
        if ($item instanceof hasSymbol && $item->hasSymbol()) {
            return $this->applySymbolDecorator($item->getSymbol(), $item);
        }
        if ($item instanceof hasTitle) {
            $abbr = strtoupper(substr($item->getTitle(), 0, 1));
        } else {
            $abbr = strtoupper(substr(uniqid('', true), -1));
        }

        return $this->ui->factory()->symbol()->icon()->standard($abbr, $abbr, 'small', true)->withAbbreviation($abbr);
    }

    /**
     * @deprecated use buildIcon() instead
     */
    protected function getStandardSymbol(isItem $item) : Symbol
    {
        return $this->buildIcon($item);
    }
}
