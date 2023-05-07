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

namespace ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer;

use ILIAS\Data\URI;
use ILIAS\GlobalScreen\Collector\Renderer\ComponentDecoratorApplierTrait;
use ILIAS\GlobalScreen\Collector\Renderer\isSupportedTrait;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasSymbol;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasTitle;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\supportsAsynchronousLoading;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Symbol\Symbol;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use Throwable;

/**
 * Class BaseTypeRenderer
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class BaseTypeRenderer implements TypeRenderer
{
    use MakeSlateAsync, SlateSessionStateCode {
        MakeSlateAsync::hash insteadof SlateSessionStateCode;
        MakeSlateAsync::unhash insteadof SlateSessionStateCode;
    }
    use isSupportedTrait;

    use ComponentDecoratorApplierTrait;

    /**
     * @var \ILIAS\UI\Factory
     */
    protected $ui_factory;

    /**
     * @var \ILIAS\UI\Renderer
     */
    protected $ui_renderer;

    /**
     * BaseTypeRenderer constructor.
     */
    public function __construct()
    {
        global $DIC;
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
    }

    /**
     * @inheritDoc
     */
    public function getComponentForItem(isItem $item, bool $with_content = true) : Component
    {
        return $this->applyDecorator($with_content ? $this->getComponentWithContent($item) : $this->getComponentWithoutContent($item), $item);
    }

    /**
     * @inheritDoc
     */
    public function getComponentWithContent(isItem $item) : Component
    {
        return $this->ui_factory->legacy($item->getProviderIdentification()->serialize());
    }

    /**
     * @inheritDoc
     */
    public function getComponentWithoutContent(isItem $item) : Component
    {
        if (!$this->supportsAsyncContent($item)) {
            return $this->getComponentWithContent($item);
        }
        /** @var $item supportsAsynchronousLoading $content */
        $content = $this->ui_factory->legacy('...');
        $name = $item instanceof hasTitle ? $item->getTitle() : "-";
        $slate = $this->ui_factory->mainControls()->slate()->legacy($name, $this->getStandardSymbol($item), $content);
        $slate = $this->addAsyncLoadingCode($slate, $item);

        return $this->addOnloadCode($slate, $item);
    }

    private function supportsAsyncContent(isItem $item) : bool
    {
        return $item instanceof supportsAsynchronousLoading && $item->supportsAsynchronousLoading();
    }

    /**
     * @param isItem $item
     * @return Symbol
     */
    protected function getStandardSymbol(isItem $item) : Symbol
    {
        if ($item instanceof hasSymbol && $item->hasSymbol()) {
            $c = $item->getSymbolDecorator();
            if ($c !== null) {
                return $this->applySymbolDecorator($item->getSymbol(), $item);
            }

            return $item->getSymbol();
        }
        if ($item instanceof hasTitle) {
            if (function_exists('mb_substr')) {
                $abbr = strtoupper(mb_substr($item->getTitle(), 0, 1));
            } else {
                $abbr = strtoupper(substr($item->getTitle(), 0, 1));
            }
        } else {
            $abbr = strtoupper(substr(uniqid('', true), -1));
        }

        return $this->ui_factory->symbol()->icon()->standard($abbr, $abbr, 'small', true)->withAbbreviation($abbr);
    }

    /**
     * @param string $uri_string
     * @return URI
     */
    protected function getURI(string $uri_string) : URI
    {
        $uri_string = trim($uri_string, " ");

        if (strpos($uri_string, 'http') === 0) {
            $checker = self::getURIChecker();
            if ($checker($uri_string)) {
                return new URI($uri_string);
            }
            return new URI(rtrim(ILIAS_HTTP_PATH, "/") . "/" . ltrim($_SERVER['REQUEST_URI'] ?? '', "./"));
        }

        return new URI(rtrim(ILIAS_HTTP_PATH, "/") . "/" . ltrim($uri_string, "./"));
    }

    public static function getURIChecker() : callable
    {
        return static function (string $v) : bool {
            $v = self::getURIConverter()($v);
            try {
                new URI($v);
            } catch (Throwable $e) {
                return false;
            }
            return true;
        };
    }

    public static function getURIConverter() : callable
    {
        return static function (string $v) : string {
            if (strpos($v, './') === 0) {
                $v = ltrim($v, './');
                return ILIAS_HTTP_PATH . '/' . $v;
            }

            return $v;
        };
    }
}
