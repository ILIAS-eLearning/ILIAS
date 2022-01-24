<?php namespace ILIAS\GlobalScreen\Scope\Layout\Builder;

use ILIAS\DI\UIServices;
use ILIAS\GlobalScreen\Scope\Layout\MetaContent\MetaContent;
use ILIAS\GlobalScreen\Scope\Layout\Provider\PagePart\PagePartProvider;
use ILIAS\UI\Component\Layout\Page\Page;
use ILIAS\UI\Implementation\Component\Layout\Page\Standard;

/******************************************************************************
 * This file is part of ILIAS, a powerful learning management system.
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *****************************************************************************/

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
        $this->ui   = $DIC->ui();
        $this->meta = $DIC->globalScreen()->layout()->meta();
    }
    
    /**
     * @param PagePartProvider $parts
     * @return Page
     */
    public function build(PagePartProvider $parts) : Page
    {
        $header_image = $parts->getLogo();
        $main_bar     = $parts->getMainBar();
        $meta_bar     = $parts->getMetaBar();
        $bread_crumbs = $parts->getBreadCrumbs();
        $footer       = $parts->getFooter();
        $title        = $parts->getTitle();
        $short_title  = $parts->getShortTitle();
        $view_title   = $parts->getViewTitle();
        
        $standard = $this->ui->factory()->layout()->page()->standard(
            [$parts->getContent()],
            $meta_bar,
            $main_bar,
            $bread_crumbs,
            $header_image,
            $footer,
            $title,
            $short_title,
            $view_title
        );
        
        return $standard->withSystemInfos($parts->getSystemInfos())
                        ->withTextDirection($this->meta->getTextDirection() ?? Standard::LTR);
    }
}
