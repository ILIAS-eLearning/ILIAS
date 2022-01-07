<?php namespace ILIAS\GlobalScreen\Scope\MetaBar\Collector\Renderer;

use ILIAS\Data\URI;
use ILIAS\DI\UIServices;
use ILIAS\GlobalScreen\Collector\Renderer\ComponentDecoratorApplierTrait;
use ILIAS\GlobalScreen\Collector\Renderer\isSupportedTrait;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\hasSymbol;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\hasTitle;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\isItem;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Symbol\Symbol;

/******************************************************************************
 * This file is part of ILIAS, a powerful learning management system.
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *****************************************************************************/

/**
 * Class AbstractMetaBarItemRenderer
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractMetaBarItemRenderer implements MetaBarItemRenderer
{
    use ComponentDecoratorApplierTrait;
    use isSupportedTrait;
    
    protected UIServices $ui;
    
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
        $component = $this->applyDecorator($component, $item);
        
        return $component;
    }
    
    abstract protected function getSpecificComponentForItem(isItem $item) : Component;
    
    /**
     * @param isItem $item
     * @return Symbol
     */
    protected function getStandardSymbol(isItem $item) : Symbol
    {
        if ($item instanceof hasSymbol && $item->hasSymbol()) {
            return $item->getSymbol();
        }
        if ($item instanceof hasTitle) {
            $abbr = strtoupper(substr($item->getTitle(), 0, 1));
        } else {
            $abbr = strtoupper(substr(uniqid('', true), -1));
        }
        
        return $this->ui->factory()->symbol()->icon()->standard($abbr, $abbr, 'small', true)->withAbbreviation($abbr);
    }
}
