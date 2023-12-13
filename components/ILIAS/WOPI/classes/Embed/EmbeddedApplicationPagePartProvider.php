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

namespace ILIAS\Services\WOPI\Embed;

use ILIAS\UI\Component\MainControls\MainBar;
use ILIAS\UI\Component\MainControls\MetaBar;
use ILIAS\GlobalScreen\Scope\Layout\Provider\PagePart\PagePartProvider;
use ILIAS\UI\Component\Legacy\Legacy;
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
        private PagePartProvider $page_part_provider
    ) {
    }

    public function getContent(): ?Legacy
    {
        return $this->page_part_provider->getContent();
    }

    public function getMetaBar(): ?MetaBar
    {
        return null;
    }

    public function getMainBar(): ?MainBar
    {
        return null;
    }

    public function getBreadCrumbs(): ?Breadcrumbs
    {
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
        return [];
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
