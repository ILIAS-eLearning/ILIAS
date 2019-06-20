<?php namespace ILIAS\GlobalScreen\Scope\Layout\Provider;

use Closure;
use ILIAS\UI\Component\Breadcrumbs\Breadcrumbs;
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\Legacy\Legacy;
use ILIAS\UI\Component\MainControls\MainBar;
use ILIAS\UI\Component\MainControls\MetaBar;

/**
 * Class DecoratedPagePartProvider
 *
 * @internal
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class DecoratedPagePartProvider implements PagePartProvider
{

    /**
     * @var PagePartProvider
     */
    private $original;
    /**
     * @var Closure
     */
    private $deco;
    /**
     * @var string
     */
    private $purpose = '';


    /**
     * DecoratedPagePartProvider constructor.
     *
     * @param PagePartProvider $original
     * @param Closure          $deco
     * @param string           $purpose
     */
    public function __construct(PagePartProvider $original, Closure $deco, string $purpose)
    {
        $this->original = $original;
        $this->deco = $deco;
        $this->purpose = $purpose;
    }


    private function getDecoratedOrOriginal(string $purpose, $original)
    {
        if ($this->isDecorated($purpose)) {
            $deco = $this->deco;

            return $deco($original);
        }

        return $original;
    }


    private function isDecorated(string $purpose) : bool
    {
        return $purpose === $this->purpose;
    }


    /**
     * @inheritDoc
     */
    public function getContent() : Legacy
    {
        return $this->getDecoratedOrOriginal(Legacy::class, $this->original->getContent());
    }


    /**
     * @inheritDoc
     */
    public function getMetaBar() : MetaBar
    {
        return $this->getDecoratedOrOriginal(MetaBar::class, $this->original->getMetaBar());
    }


    /**
     * @inheritDoc
     */
    public function getMainBar() : MainBar
    {
        return $this->getDecoratedOrOriginal(MainBar::class, $this->original->getMainBar());
    }


    /**
     * @inheritDoc
     */
    public function getBreadCrumbs() : Breadcrumbs
    {
        return $this->getDecoratedOrOriginal(Breadcrumbs::class, $this->original->getBreadCrumbs());
    }


    /**
     * @inheritDoc
     */
    public function getLogo() : Image
    {
        return $this->getDecoratedOrOriginal(Image::class, $this->original->getLogo());
    }
}
