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

namespace ILIAS\UI\Implementation\Component\Layout\Page;

use ILIAS\UI\Component\Breadcrumbs\Breadcrumbs;
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\Layout\Page;
use ILIAS\Data\Link;
use ILIAS\UI\Component\MainControls;
use ILIAS\UI\Component\Toast\Container;
use ILIAS\UI\Component\Legacy\Content;

class Factory implements Page\Factory
{
    public function standard(
        array $content,
        ?MainControls\MetaBar $metabar = null,
        ?MainControls\MainBar $mainbar = null,
        ?Breadcrumbs $locator = null,
        ?Image $logo = null,
        ?Image $responsive_logo = null,
        string $favicon_path = '',
        ?Container $overlay = null,
        ?MainControls\Footer $footer = null,
        string $title = '',
        string $short_title = '',
        string $view_title = ''
    ): Standard {
        return new Standard(
            $content,
            $metabar,
            $mainbar,
            $locator,
            $logo,
            $responsive_logo,
            $favicon_path,
            $overlay,
            $footer,
            $title,
            $short_title,
            $view_title
        );
    }

    public function mail(
        string $stylesheet_path,
        string $logo_url,
        string $installation_title,
        Content $html_content,
        Link $footer_url,
    ): Mail {
        return new Mail(
            $stylesheet_path,
            $logo_url,
            $installation_title,
            $html_content,
            $footer_url,
        );
    }
}
