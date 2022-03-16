<?php namespace ILIAS\GlobalScreen\Scope\Layout\Provider\PagePart;

use Closure;
use ILIAS\GlobalScreen\Scope\Notification\Factory\AdministrativeNotification;
use ILIAS\UI\Component\Breadcrumbs\Breadcrumbs;
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\Legacy\Legacy;
use ILIAS\UI\Component\MainControls\Footer;
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
    const PURPOSE_TITLE = 'ptitle';
    const PURPOSE_SHORTTITLE = 'stitle';
    const PURPOSE_VIEWTITLE = 'vtitle';
    const PURPOSE_LOGO = 'plogo';
    const PURPOSE_RESPONSIVE_LOGO = 'prlogo';

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
    public function getContent() : ?Legacy
    {
        return $this->getDecoratedOrOriginal(Legacy::class, $this->original->getContent());
    }


    /**
     * @inheritDoc
     */
    public function getMetaBar() : ?MetaBar
    {
        return $this->getDecoratedOrOriginal(MetaBar::class, $this->original->getMetaBar());
    }


    /**
     * @inheritDoc
     */
    public function getMainBar() : ?MainBar
    {
        return $this->getDecoratedOrOriginal(MainBar::class, $this->original->getMainBar());
    }


    /**
     * @inheritDoc
     */
    public function getBreadCrumbs() : ?Breadcrumbs
    {
        return $this->getDecoratedOrOriginal(Breadcrumbs::class, $this->original->getBreadCrumbs());
    }


    /**
     * @inheritDoc
     */
    public function getLogo() : ?Image
    {
        return $this->getDecoratedOrOriginal(self::PURPOSE_LOGO, $this->original->getLogo());
    }


    public function getResponsiveLogo() : ?Image
    {
        return $this->getDecoratedOrOriginal(self::PURPOSE_RESPONSIVE_LOGO, $this->original->getResponsiveLogo());
    }

    /**
     * @inheritDoc
     */
    public function getSystemInfos() : array
    {
        return $this->original->getSystemInfos();
    }

    /**
     * @inheritDoc
     */
    public function getFooter() : ?Footer
    {
        return $this->getDecoratedOrOriginal(Footer::class, $this->original->getFooter());
    }

    /**
     * @inheritDoc
     */
    public function getTitle() : string
    {
        return $this->getDecoratedOrOriginal(self::PURPOSE_TITLE, $this->original->getTitle());
    }

    /**
     * @inheritDoc
     */
    public function getShortTitle() : string
    {
        return $this->getDecoratedOrOriginal(self::PURPOSE_SHORTTITLE, $this->original->getShortTitle());
    }

    /**
     * @inheritDoc
     */
    public function getViewTitle() : string
    {
        return $this->getDecoratedOrOriginal(self::PURPOSE_VIEWTITLE, $this->original->getViewTitle());
    }
}
