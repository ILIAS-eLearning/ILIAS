<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer;

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

/**
 * Class BaseTypeRenderer
 *
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
     * @var Factory
     */
    protected $ui_factory;


    /**
     * BaseTypeRenderer constructor.
     */
    public function __construct()
    {
        global $DIC;
        $this->ui_factory = $DIC->ui()->factory();
    }


    /**
     * @inheritDoc
     */
    public function getComponentForItem(isItem $item, bool $with_content = true) : Component
    {
        return $with_content ? $this->getComponentWithContent($item) : $this->getComponentWithoutContent($item);
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
        $content = $this->ui_factory->legacy("...");
        $name = $item instanceof hasTitle ? $item->getTitle() : "-";
        $slate = $this->ui_factory->mainControls()->slate()->legacy($name, $this->getStandardSymbol($item), $content);
        $slate = $this->addAsyncLoadingCode($slate, $item);
        $slate = $this->addOnloadCode($slate, $item);

        $slate = $this->applyDecorator($slate, $item);

        return $slate;
    }


    private function supportsAsyncContent(isItem $item) : bool
    {
        return $item instanceof supportsAsynchronousLoading && $item->supportsAsynchronousLoading();
    }


    /**
     * @param isItem $item
     *
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

        return $this->ui_factory->symbol()->icon()->standard($abbr, $abbr, 'small', true)->withAbbreviation($abbr);
    }


    /**
     * @param string $uri_string
     *
     * @return URI
     */
    protected function getURI(string $uri_string) : URI
    {
        if (strpos($uri_string, 'http') === 0) {
            return new URI($uri_string);
        }

        return new URI(rtrim(ILIAS_HTTP_PATH, "/") . "/" . ltrim($uri_string, "./"));
    }
}
