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

namespace ILIAS\GlobalScreen\Scope\Layout\Provider\PagePart;

use Closure;
use ILIAS\UI\Component\Breadcrumbs\Breadcrumbs;
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\Legacy\Content;
use ILIAS\UI\Component\MainControls\Footer;
use ILIAS\UI\Component\MainControls\MainBar;
use ILIAS\UI\Component\MainControls\MetaBar;
use ILIAS\UI\Component\Toast\Container as TContainer;

/**
 * Class DecoratedPagePartProvider
 * @internal
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class DecoratedPagePartProvider implements PagePartProvider
{
    public const PURPOSE_TITLE = 'ptitle';
    public const PURPOSE_SHORTTITLE = 'stitle';
    public const PURPOSE_VIEWTITLE = 'vtitle';
    public const PURPOSE_LOGO = 'plogo';
    public const PURPOSE_RESPONSIVE_LOGO = 'prlogo';
    public const PURPOSE_FAVICON = 'pfavicon';

    /**
     * DecoratedPagePartProvider constructor.
     * @param PagePartProvider $original
     * @param Closure          $deco
     * @param string           $purpose
     */
    public function __construct(private readonly PagePartProvider $original, private Closure $deco, private readonly string $purpose)
    {
    }

    private function getDecoratedOrOriginal(string $purpose, Content|null|MetaBar|MainBar|Breadcrumbs|Image|string|Footer|TContainer $original)
    {
        if ($this->isDecorated($purpose)) {
            $deco = $this->deco;

            return $deco($original);
        }

        return $original;
    }

    private function isDecorated(string $purpose): bool
    {
        return $purpose === $this->purpose;
    }


    public function getContent(): ?Content
    {
        return $this->getDecoratedOrOriginal(Content::class, $this->original->getContent());
    }


    public function getMetaBar(): ?MetaBar
    {
        return $this->getDecoratedOrOriginal(MetaBar::class, $this->original->getMetaBar());
    }


    public function getMainBar(): ?MainBar
    {
        return $this->getDecoratedOrOriginal(MainBar::class, $this->original->getMainBar());
    }


    public function getBreadCrumbs(): ?Breadcrumbs
    {
        return $this->getDecoratedOrOriginal(Breadcrumbs::class, $this->original->getBreadCrumbs());
    }


    public function getLogo(): ?Image
    {
        return $this->getDecoratedOrOriginal(self::PURPOSE_LOGO, $this->original->getLogo());
    }


    public function getResponsiveLogo(): ?Image
    {
        return $this->getDecoratedOrOriginal(self::PURPOSE_RESPONSIVE_LOGO, $this->original->getResponsiveLogo());
    }

    public function getFaviconPath(): string
    {
        return $this->getDecoratedOrOriginal(self::PURPOSE_FAVICON, $this->original->getFaviconPath());
    }


    public function getSystemInfos(): array
    {
        return $this->original->getSystemInfos();
    }


    public function getFooter(): ?Footer
    {
        return $this->getDecoratedOrOriginal(Footer::class, $this->original->getFooter());
    }


    public function getTitle(): string
    {
        return $this->getDecoratedOrOriginal(self::PURPOSE_TITLE, $this->original->getTitle());
    }


    public function getShortTitle(): string
    {
        return $this->getDecoratedOrOriginal(self::PURPOSE_SHORTTITLE, $this->original->getShortTitle());
    }


    public function getViewTitle(): string
    {
        return $this->getDecoratedOrOriginal(self::PURPOSE_VIEWTITLE, $this->original->getViewTitle());
    }

    public function getToastContainer(): ?TContainer
    {
        return $this->getDecoratedOrOriginal(TContainer::class, $this->original->getToastContainer());
    }
}
