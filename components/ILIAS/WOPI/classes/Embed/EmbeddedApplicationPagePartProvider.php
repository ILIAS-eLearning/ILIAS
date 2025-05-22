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

namespace ILIAS\components\WOPI\Embed;

use ILIAS\UI\Component\MainControls\MainBar;
use ILIAS\UI\Component\MainControls\MetaBar;
use ILIAS\GlobalScreen\Scope\Layout\Provider\PagePart\PagePartProvider;
use ILIAS\UI\Component\Legacy\Content;
use ILIAS\UI\Component\Breadcrumbs\Breadcrumbs;
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\MainControls\Footer;
use ILIAS\UI\Component\Toast\Container;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class EmbeddedApplicationPagePartProvider implements PagePartProvider
{
    public function __construct(
        private readonly PagePartProvider $page_part_provider,
        private readonly EmbeddedApplication $embedded_application
    ) {
    }

    public function getContent(): ?Content
    {
        return $this->page_part_provider->getContent();
    }

    public function getMetaBar(): ?MetaBar
    {
        if ($this->embedded_application->isInline()) {
            return $this->page_part_provider->getMetaBar();
        }

        return null;
    }

    public function getMainBar(): ?MainBar
    {
        if ($this->embedded_application->isInline()) {
            return $this->page_part_provider->getMainBar();
        }
        return null;
    }

    public function getBreadCrumbs(): ?Breadcrumbs
    {
        if ($this->embedded_application->isInline()) {
            return $this->page_part_provider->getBreadCrumbs();
        }
        return null;
    }

    public function getLogo(): ?Image
    {
        return $this->page_part_provider->getLogo();
    }

    public function getResponsiveLogo(): ?Image
    {
        return $this->page_part_provider->getResponsiveLogo();
    }

    public function getFaviconPath(): string
    {
        return $this->page_part_provider->getFaviconPath();
    }

    public function getSystemInfos(): array
    {
        return $this->page_part_provider->getSystemInfos();
    }

    public function getFooter(): ?Footer
    {
        return null;
    }

    public function getTitle(): string
    {
        return $this->page_part_provider->getTitle();
    }

    public function getShortTitle(): string
    {
        return $this->page_part_provider->getShortTitle();
    }

    public function getViewTitle(): string
    {
        return $this->page_part_provider->getViewTitle();
    }

    public function getToastContainer(): ?Container
    {
        return null;
    }
}
