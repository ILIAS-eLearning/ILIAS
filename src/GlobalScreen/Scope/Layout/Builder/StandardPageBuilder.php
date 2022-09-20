<?php

declare(strict_types=1);
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

namespace ILIAS\GlobalScreen\Scope\Layout\Builder;

use ILIAS\DI\UIServices;
use ILIAS\GlobalScreen\Scope\Layout\MetaContent\MetaContent;
use ILIAS\GlobalScreen\Scope\Layout\Provider\PagePart\PagePartProvider;
use ILIAS\UI\Component\Layout\Page\Page;
use ILIAS\UI\Implementation\Component\Layout\Page\Standard;

/**
 * Interface PageBuilder
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class StandardPageBuilder implements PageBuilder
{
    protected UIServices $ui;
    protected MetaContent $meta;

    /**
     * StandardPageBuilder constructor.
     */
    public function __construct()
    {
        global $DIC;
        $this->ui = $DIC->ui();
        $this->meta = $DIC->globalScreen()->layout()->meta();
    }

    /**
     * @param PagePartProvider $parts
     * @return Page
     */
    public function build(PagePartProvider $parts): Page
    {
        $meta_bar = $parts->getMetaBar();
        $main_bar = $parts->getMainBar();
        $bread_crumbs = $parts->getBreadCrumbs();
        $header_image = $parts->getLogo();
        $responsive_header_image = $parts->getResponsiveLogo();
        $favicon_path = $parts->getFaviconPath();
        $footer = $parts->getFooter();
        $title = $parts->getTitle();
        $short_title = $parts->getShortTitle();
        $view_title = $parts->getViewTitle();

        $standard = $this->ui->factory()->layout()->page()->standard(
            [$parts->getContent()],
            $meta_bar,
            $main_bar,
            $bread_crumbs,
            $header_image,
            $responsive_header_image,
            $favicon_path,
            $this->ui->factory()->toast()->container(),
            $footer,
            $title,
            $short_title,
            $view_title
        );

        foreach ($this->meta->getMetaData()->getItems() as $meta_datum) {
            $standard = $standard->withAdditionalMetaDatum($meta_datum->getKey(), $meta_datum->getValue());
        }

        return $standard->withSystemInfos($parts->getSystemInfos())
                        ->withTextDirection($this->meta->getTextDirection() ?? Standard::LTR);
    }
}
