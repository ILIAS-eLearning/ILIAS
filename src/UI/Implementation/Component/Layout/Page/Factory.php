<?php declare(strict_types=1);

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
 
namespace ILIAS\UI\Implementation\Component\Layout\Page;

use ILIAS\UI\Component\Breadcrumbs\Breadcrumbs;
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\Layout\Page;
use ILIAS\UI\Component\MainControls;
use ILIAS\UI\Component\Toast\Container;

class Factory implements Page\Factory
{
    /**
     * @inheritdoc
     */
    public function standard(
        array $content,
        MainControls\MetaBar $metabar = null,
        MainControls\MainBar $mainbar = null,
        Breadcrumbs $locator = null,
        Image $logo = null,
        Image $responsive_logo = null,
        string $favicon_path = '',
        Container $overlay = null,
        MainControls\Footer $footer = null,
        string $title = '',
        string $short_title = '',
        string $view_title = ''
    ) : Page\Standard {
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
}
